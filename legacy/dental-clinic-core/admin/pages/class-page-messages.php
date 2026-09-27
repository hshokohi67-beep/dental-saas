<?php
defined('ABSPATH') || exit;

class Dental_Page_Messages {

    public function render(): void {
        $cu = wp_get_current_user();
        $cu_id = $cu->ID;

        $partner_id = isset($_GET['with']) ? (int)$_GET['with'] : -1;
        $partners = Dental_Workspace_Manager::get_conversation_partners($cu_id);

        // اگه هیچ مخاطبی انتخاب نشده، اولین مخاطب (یا -1 = لیست همه کارکنان برای شروع گفتگوی جدید)
        if ($partner_id === -1 && !empty($partners)) $partner_id = $partners[0]['partner_id'];

        if ($partner_id >= 0) {
            Dental_Workspace_Manager::mark_conversation_read($cu_id, $partner_id);
        }

        // لیست همه کارکنان (برای شروع گفتگوی جدید)
        $all_staff = get_users(['role__in'=>['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'], 'exclude'=>[$cu_id], 'fields'=>['ID','display_name']]);
        $existing_partner_ids = array_column($partners, 'partner_id');
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <i data-lucide="message-circle" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                پیام‌های داخلی
            </h1>

            <div class="dc-card" style="overflow:hidden;">
                <div style="display:grid;grid-template-columns:280px 1fr;min-height:520px;">

                    <!-- ستون چپ: لیست مخاطبین -->
                    <div style="border-left:1px solid var(--dc-neutral-100);display:flex;flex-direction:column;">
                        <div style="padding:12px;border-bottom:1px solid var(--dc-neutral-100);">
                            <select id="dc-new-chat-select" class="dc-select" style="font-size:12px;" onchange="if(this.value)location.href='<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&msg_view=1&with=')); ?>'+this.value;">
                                <option value="">+ شروع گفتگوی جدید...</option>
                                <?php foreach($all_staff as $s): if(in_array($s->ID,$existing_partner_ids)) continue; ?>
                                <option value="<?php echo $s->ID; ?>"><?php echo esc_html($s->display_name); ?></option>
                                <?php endforeach; ?>
                                <?php if(!in_array(0,$existing_partner_ids)): ?>
                                <option value="0">📢 اعلان عمومی به همه</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="flex:1;overflow-y:auto;">
                            <?php if (empty($partners)): ?>
                            <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:30px 16px;">هنوز گفتگویی ندارید — از بالا شروع کنید.</p>
                            <?php else: foreach($partners as $p): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&msg_view=1&with='.$p['partner_id'])); ?>"
                               style="display:block;padding:12px 14px;border-bottom:1px solid var(--dc-neutral-50);text-decoration:none;color:inherit;
                                      <?php echo $p['partner_id']==$partner_id?'background:var(--dc-primary-light);':''; ?>">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;">
                                    <span style="font-size:13px;font-weight:700;color:var(--dc-neutral-900);"><?php echo esc_html($p['name']); ?></span>
                                    <?php if($p['unread']>0): ?><span style="background:var(--dc-danger);color:#fff;font-size:10px;font-weight:700;border-radius:10px;padding:1px 7px;"><?php echo $p['unread']; ?></span><?php endif; ?>
                                </div>
                                <div style="font-size:11px;color:var(--dc-neutral-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?php echo $p['last_from_me']?'شما: ':''; ?><?php echo esc_html(mb_substr($p['last_message'],0,40)); ?>
                                </div>
                            </a>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>

                    <!-- ستون راست: تاریخچه گفتگو -->
                    <div style="display:flex;flex-direction:column;">
                        <?php if ($partner_id < 0): ?>
                        <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--dc-neutral-400);font-size:13px;">
                            یه مخاطب رو از سمت چپ انتخاب کنید یا گفتگوی جدید شروع کنید.
                        </div>
                        <?php else:
                            $partner_name = $partner_id===0 ? '📢 اعلان عمومی به همه' : (get_userdata($partner_id) ? get_userdata($partner_id)->display_name : '—');
                            $conversation = Dental_Workspace_Manager::get_conversation($cu_id, $partner_id);
                        ?>
                        <div style="padding:14px 18px;border-bottom:1px solid var(--dc-neutral-100);font-weight:700;font-size:14px;">
                            <?php echo esc_html($partner_name); ?>
                        </div>
                        <div id="dc-conv-messages" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;max-height:400px;">
                            <?php if (empty($conversation)): ?>
                            <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;">هنوز پیامی رد و بدل نشده — اولین پیام رو بفرستید.</p>
                            <?php else: foreach($conversation as $m):
                                $is_mine = (int)$m['from_user_id'] === $cu_id;
                            ?>
                            <div style="display:flex;<?php echo $is_mine?'justify-content:flex-start;':'justify-content:flex-end;'; ?>" data-msg-id="<?php echo $m['id']; ?>">
                                <div style="max-width:70%;background:<?php echo $is_mine?'var(--dc-primary)':'var(--dc-neutral-100)'; ?>;color:<?php echo $is_mine?'#fff':'var(--dc-neutral-900)'; ?>;border-radius:14px;padding:10px 14px;position:relative;">
                                    <?php if($partner_id===0 && !$is_mine): ?><div style="font-size:10px;opacity:.7;margin-bottom:3px;"><?php echo esc_html($m['from_name']); ?></div><?php endif; ?>
                                    <div style="font-size:13px;line-height:1.6;"><?php echo nl2br(esc_html($m['message'])); ?></div>
                                    <div style="font-size:10px;opacity:.7;margin-top:4px;display:flex;gap:4px;align-items:center;justify-content:<?php echo $is_mine?'flex-start':'flex-end'; ?>;">
                                        <?php echo esc_html(date('H:i', strtotime($m['created_at']))); ?>
                                        <?php if($is_mine): ?><span><?php echo $m['is_read']?'✓✓':'✓'; ?></span><?php endif; ?>
                                        <?php if($is_mine): ?><span onclick="dcDeleteMessage(<?php echo $m['id']; ?>,this)" style="cursor:pointer;margin-right:4px;" title="حذف">🗑️</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <form onsubmit="dcSendConvMessage(event, <?php echo $partner_id; ?>)" style="padding:12px;border-top:1px solid var(--dc-neutral-100);display:flex;gap:8px;">
                            <input type="text" id="dc-conv-input" class="dc-input" placeholder="پیام..." style="flex:1;" autocomplete="off">
                            <button type="submit" class="dc-btn dc-btn-primary">ارسال</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        var dcMsgNonce = '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>';
        var dcMsgAjax  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

        function dcSendConvMessage(e, partnerId){
            e.preventDefault();
            var input = document.getElementById('dc-conv-input');
            var text = input.value.trim();
            if (!text) return;
            var fd = new FormData();
            fd.append('action','dental_send_message');
            fd.append('_wpnonce', dcMsgNonce);
            fd.append('to_user_id', partnerId);
            fd.append('message', text);
            fetch(dcMsgAjax, {method:'POST', body:fd}).then(function(){
                location.reload();
            });
        }
        function dcDeleteMessage(id, el){
            if (!confirm('این پیام حذف بشه؟')) return;
            var fd = new FormData();
            fd.append('action','dental_delete_message');
            fd.append('_wpnonce', dcMsgNonce);
            fd.append('message_id', id);
            fetch(dcMsgAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(res.success){
                    var row = el.closest('[data-msg-id]');
                    if(row) row.remove();
                }
            });
        }
        // اسکرول به آخرین پیام
        var convBox = document.getElementById('dc-conv-messages');
        if (convBox) convBox.scrollTop = convBox.scrollHeight;
        if(typeof lucide!=="undefined")lucide.createIcons();
        </script>
        <?php
    }
}
