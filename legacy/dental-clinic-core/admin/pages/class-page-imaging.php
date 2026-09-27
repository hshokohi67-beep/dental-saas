<?php
defined('ABSPATH') || exit;

class Dental_Page_Imaging {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $studies = class_exists('Dental_Imaging_Manager') ? Dental_Imaging_Manager::get_patient_studies($this->patient_id) : [];
        $modality_labels = Dental_Imaging_Manager::get_modality_labels();
        ?>
        <div style="margin-bottom:16px;">
            <button type="button" class="dc-btn dc-btn-primary dc-btn-sm" onclick="dcImOpenUpload()">📤 آپلود تصویر جدید</button>
            <button type="button" class="dc-btn dc-btn-secondary dc-btn-sm" onclick="dcImOpenCompare()">⚖️ مقایسه دو تصویر</button>
        </div>

        <?php if (empty($studies)): ?>
        <p style="text-align:center;color:var(--dc-neutral-400);padding:30px;">هنوز تصویری برای این بیمار ثبت نشده.</p>
        <?php else: foreach($studies as $study): ?>
        <div class="dc-card" style="margin-bottom:16px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="font-size:13px;">
                    🗂️ <?php echo esc_html($modality_labels[$study['modality']] ?? $study['modality']); ?>
                    — <?php echo esc_html(Dental_Jalali::to_jalali($study['study_date'],'Y/m/d')); ?>
                    <?php if ($study['doctor_name']): ?><span style="font-weight:400;color:var(--dc-neutral-500);"> — <?php echo esc_html($study['doctor_name']); ?></span><?php endif; ?>
                </h3>
                <?php if ($study['description']): ?><span style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($study['description']); ?></span><?php endif; ?>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;padding:14px;">
                <?php foreach($study['images'] as $img):
                    $thumb_url = add_query_arg('dental_imaging_file', $img['thumbnail_path'] ?: $img['file_path'], admin_url());
                    $tooth_label = $img['tooth_number'] ? Dental_Service_Catalog::describe_tooth_number((int)$img['tooth_number']) : '';
                ?>
                <div style="width:110px;cursor:pointer;" onclick="dcImOpenViewer(<?php echo (int)$img['id']; ?>)">
                    <div style="width:110px;height:110px;border-radius:8px;overflow:hidden;background:#000;border:2px solid <?php echo $img['before_after']==='before'?'#E05252':($img['before_after']==='after'?'#2ECC9A':'transparent'); ?>;">
                        <img src="<?php echo esc_url($thumb_url); ?>" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                    </div>
                    <div style="font-size:10px;text-align:center;margin-top:4px;color:var(--dc-neutral-500);">
                        <?php if($img['before_after']): ?><b style="color:<?php echo $img['before_after']==='before'?'#E05252':'#2ECC9A'; ?>;"><?php echo $img['before_after']==='before'?'قبل':'بعد'; ?></b><br><?php endif; ?>
                        <?php echo esc_html($tooth_label); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>

        <!-- مودال آپلود -->
        <div id="dc-im-upload-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.85);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:420px;max-height:88vh;overflow-y:auto;">
                <div style="font-weight:700;font-size:14px;margin-bottom:14px;">📤 آپلود تصویر جدید</div>
                <form id="dc-im-upload-form" onsubmit="dcImSubmitUpload(event)">
                    <div class="dc-form-group" style="margin-bottom:10px;">
                        <label class="dc-label">نوع تصویر</label>
                        <select name="modality" class="dc-select" required>
                            <?php foreach($modality_labels as $k=>$l): ?>
                            <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="dc-form-group" style="margin-bottom:10px;">
                        <label class="dc-label">تاریخ گرفتن تصویر</label>
                        <input type="text" name="study_date" class="dc-input" value="<?php echo esc_attr(Dental_Jalali::today('Y/m/d')); ?>">
                    </div>
                    <div class="dc-grid dc-grid-2" style="gap:10px;margin-bottom:10px;">
                        <div>
                            <label class="dc-label">دندان مرتبط (اختیاری)</label>
                            <input type="number" name="tooth_number" class="dc-input" placeholder="مثلاً 36">
                        </div>
                        <div>
                            <label class="dc-label">قبل/بعد درمان</label>
                            <select name="before_after" class="dc-select">
                                <option value="">—</option>
                                <option value="before">قبل درمان</option>
                                <option value="after">بعد درمان</option>
                            </select>
                        </div>
                    </div>
                    <div class="dc-form-group" style="margin-bottom:14px;">
                        <label class="dc-label">توضیح (اختیاری)</label>
                        <input type="text" name="description" class="dc-input">
                    </div>
                    <div class="dc-form-group" style="margin-bottom:14px;">
                        <label class="dc-label">فایل تصویر (JPG/PNG/WEBP)</label>
                        <input type="file" name="file" accept="image/jpeg,image/png,image/webp" required style="width:100%;">
                    </div>
                    <div id="dc-im-upload-progress" style="font-size:12px;color:var(--dc-primary);display:none;margin-bottom:10px;">⏳ در حال آپلود...</div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="dc-btn dc-btn-primary" style="flex:1;">آپلود</button>
                        <button type="button" onclick="document.getElementById('dc-im-upload-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Viewer اصلی -->
        <div id="dc-im-viewer-modal" style="display:none;position:fixed;inset:0;background:#000;z-index:9999999;">
            <div style="display:flex;flex-direction:column;height:100%;">
                <div style="background:#1A1A1A;padding:8px 14px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;border-bottom:1px solid #333;">
                    <button onclick="dcImClose()" class="dc-iv-btn" title="بستن">✕</button>
                    <span style="width:1px;height:20px;background:#444;"></span>
                    <button onclick="dcImZoom(1.2)" class="dc-iv-btn" title="بزرگ‌نمایی">🔍+</button>
                    <button onclick="dcImZoom(0.8)" class="dc-iv-btn" title="کوچک‌نمایی">🔍−</button>
                    <button onclick="dcImFit()" class="dc-iv-btn" title="متناسب با صفحه">⛶</button>
                    <button onclick="dcImReset()" class="dc-iv-btn" title="بازنشانی">↺</button>
                    <button onclick="dcImRotate()" class="dc-iv-btn" title="چرخش">🔄</button>
                    <button onclick="dcImFullscreen()" class="dc-iv-btn" title="تمام‌صفحه">⛶⛶</button>
                    <span style="width:1px;height:20px;background:#444;"></span>
                    <label style="color:#aaa;font-size:11px;">روشنایی <input type="range" id="dc-im-brightness" min="50" max="200" value="100" oninput="dcImApplyFilter()" style="width:70px;vertical-align:middle;"></label>
                    <label style="color:#aaa;font-size:11px;">کنتراست <input type="range" id="dc-im-contrast" min="50" max="200" value="100" oninput="dcImApplyFilter()" style="width:70px;vertical-align:middle;"></label>
                    <button onclick="dcImInvert()" class="dc-iv-btn" title="معکوس رنگ">◐</button>
                    <span style="width:1px;height:20px;background:#444;"></span>
                    <select id="dc-im-tool" onchange="dcImSetTool(this.value)" class="dc-iv-select">
                        <option value="none">بدون ابزار (Pan)</option>
                        <option value="arrow">پیکان</option>
                        <option value="line">خط</option>
                        <option value="rect">مستطیل</option>
                        <option value="circle">دایره</option>
                        <option value="text">متن</option>
                        <option value="tooth_label">شماره دندان</option>
                        <option value="freehand">آزاد</option>
                        <option value="distance">اندازه‌گیری فاصله</option>
                        <option value="angle">اندازه‌گیری زاویه</option>
                    </select>
                    <input type="color" id="dc-im-color" value="#E05252" style="width:28px;height:28px;border:none;background:none;">
                    <button onclick="dcImUndo()" class="dc-iv-btn" title="حذف آخرین علامت">↩️</button>
                    <button onclick="dcImInfo()" class="dc-iv-btn" title="اطلاعات تصویر">ℹ️</button>
                    <span id="dc-im-info-box" style="color:#aaa;font-size:11px;margin-right:auto;"></span>
                </div>
                <div id="dc-im-canvas-wrap" style="flex:1;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    <canvas id="dc-im-canvas" style="cursor:grab;"></canvas>
                </div>
            </div>
        </div>

        <!-- مودال مقایسه -->
        <div id="dc-im-compare-modal" style="display:none;position:fixed;inset:0;background:#000;z-index:9999998;">
            <div style="display:flex;flex-direction:column;height:100%;">
                <div style="background:#1A1A1A;padding:10px 14px;display:flex;align-items:center;gap:10px;">
                    <button onclick="document.getElementById('dc-im-compare-modal').style.display='none'" class="dc-iv-btn">✕ بستن</button>
                    <select id="dc-im-compare-1" class="dc-iv-select"></select>
                    <span style="color:#666;">در برابر</span>
                    <select id="dc-im-compare-2" class="dc-iv-select"></select>
                    <button onclick="dcImLoadCompare()" class="dc-iv-btn" style="background:#1A6B8A;">بارگذاری</button>
                </div>
                <div style="flex:1;display:flex;">
                    <div style="flex:1;border-left:1px solid #333;position:relative;overflow:hidden;"><canvas id="dc-im-compare-canvas-1"></canvas></div>
                    <div style="flex:1;position:relative;overflow:hidden;"><canvas id="dc-im-compare-canvas-2"></canvas></div>
                </div>
            </div>
        </div>

        <style>
        .dc-iv-btn { background:#2A2A2A; color:#fff; border:1px solid #444; border-radius:6px; padding:6px 10px; font-size:13px; cursor:pointer; }
        .dc-iv-btn:hover { background:#3A3A3A; }
        .dc-iv-select { background:#2A2A2A; color:#fff; border:1px solid #444; border-radius:6px; padding:6px 8px; font-size:12px; font-family:Vazirmatn,Tahoma; }
        </style>

        <script>
        var dcImAllImages = <?php
            $flat = [];
            foreach (($studies ?: []) as $s) { foreach ($s['images'] as $i) { $flat[] = ['id'=>(int)$i['id'], 'label'=>($modality_labels[$s['modality']]??'').' — '.$s['study_date']]; } }
            echo wp_json_encode($flat);
        ?>;
        var dcImCurrentPatient = <?php echo (int)$this->patient_id; ?>;
        var dcImNonce = '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>';
        var dcImAjax = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        var dcImFileBase = '<?php echo esc_js(admin_url()); ?>';

        function dcImOpenUpload(){ document.getElementById('dc-im-upload-modal').style.display = 'flex'; }
        function dcImSubmitUpload(e){
            e.preventDefault();
            var form = document.getElementById('dc-im-upload-form');
            var fd = new FormData(form);
            fd.append('action', 'dental_imaging_upload');
            fd.append('_wpnonce', dcImNonce);
            fd.append('patient_id', dcImCurrentPatient);
            document.getElementById('dc-im-upload-progress').style.display = 'block';
            fetch(dcImAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                document.getElementById('dc-im-upload-progress').style.display = 'none';
                if (res.success) { location.reload(); }
                else { alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ناموفق')); }
            });
        }

        var dcImState = { imageId:0, img:null, scale:1, offsetX:0, offsetY:0, rotation:0, invert:false, tool:'none', annotations:[], drawing:false, startX:0, startY:0, measurePoints:[] };
        var canvas, ctx;

        function dcImOpenViewer(imageId){
            dcImState = { imageId:imageId, img:null, scale:1, offsetX:0, offsetY:0, rotation:0, invert:false, tool:'none', annotations:[], drawing:false, startX:0, startY:0, measurePoints:[] };
            document.getElementById('dc-im-viewer-modal').style.display = 'block';
            canvas = document.getElementById('dc-im-canvas');
            ctx = canvas.getContext('2d');
            var wrap = document.getElementById('dc-im-canvas-wrap');
            canvas.width = wrap.clientWidth; canvas.height = wrap.clientHeight;

            fetch(dcImAjax + '?action=dental_imaging_get_meta&image_id=' + imageId + '&_wpnonce=' + dcImNonce)
                .then(r=>r.json()).then(function(res){
                    if (!res.success) return;
                    var img = new Image();
                    img.onload = function(){
                        dcImState.img = img;
                        document.getElementById('dc-im-info-box').textContent = img.naturalWidth + '×' + img.naturalHeight + ' — ' + (res.data.modality||'') + ' — ' + (res.data.study_date||'');
                        dcImState.annotations = res.data.annotations || [];
                        dcImFit();
                    };
                    img.src = dcImFileBase + '?dental_imaging_file=' + res.data.file_path;
                });

            bindCanvasEvents();
        }
        function dcImClose(){ document.getElementById('dc-im-viewer-modal').style.display = 'none'; }

        function dcImDraw(){
            if (!ctx || !dcImState.img) return;
            ctx.clearRect(0,0,canvas.width,canvas.height);
            ctx.save();
            ctx.translate(canvas.width/2 + dcImState.offsetX, canvas.height/2 + dcImState.offsetY);
            ctx.rotate(dcImState.rotation * Math.PI/180);
            ctx.scale(dcImState.scale, dcImState.scale);
            var b = document.getElementById('dc-im-brightness').value;
            var c = document.getElementById('dc-im-contrast').value;
            ctx.filter = 'brightness(' + b + '%) contrast(' + c + '%)' + (dcImState.invert ? ' invert(1)' : '');
            var iw = dcImState.img.naturalWidth, ih = dcImState.img.naturalHeight;
            ctx.drawImage(dcImState.img, -iw/2, -ih/2, iw, ih);
            ctx.filter = 'none';

            dcImState.annotations.forEach(function(a){
                ctx.strokeStyle = a.color || '#E05252'; ctx.fillStyle = a.color || '#E05252'; ctx.lineWidth = 2 / dcImState.scale;
                var d = a.data;
                if (a.type === 'arrow' || a.type === 'line') {
                    ctx.beginPath();
                    ctx.moveTo((d.x1-0.5)*iw, (d.y1-0.5)*ih);
                    ctx.lineTo((d.x2-0.5)*iw, (d.y2-0.5)*ih);
                    ctx.stroke();
                } else if (a.type === 'rect') {
                    ctx.strokeRect((d.x1-0.5)*iw, (d.y1-0.5)*ih, (d.x2-d.x1)*iw, (d.y2-d.y1)*ih);
                } else if (a.type === 'circle') {
                    var cx=(d.x1-0.5)*iw, cy=(d.y1-0.5)*ih, r=Math.hypot((d.x2-d.x1)*iw,(d.y2-d.y1)*ih);
                    ctx.beginPath(); ctx.arc(cx,cy,r,0,2*Math.PI); ctx.stroke();
                } else if (a.type === 'text' || a.type === 'tooth_label') {
                    ctx.font = (16/dcImState.scale) + 'px Tahoma';
                    ctx.fillText(d.text||'', (d.x1-0.5)*iw, (d.y1-0.5)*ih);
                } else if (a.type === 'freehand' && d.points) {
                    ctx.beginPath();
                    d.points.forEach(function(p,i){ var x=(p.x-0.5)*iw, y=(p.y-0.5)*ih; i===0?ctx.moveTo(x,y):ctx.lineTo(x,y); });
                    ctx.stroke();
                }
            });
            ctx.restore();
        }

        function dcImZoom(f){ dcImState.scale *= f; dcImDraw(); }
        function dcImFit(){
            if (!dcImState.img) return;
            var wrap = document.getElementById('dc-im-canvas-wrap');
            var s = Math.min(wrap.clientWidth/dcImState.img.naturalWidth, wrap.clientHeight/dcImState.img.naturalHeight) * 0.9;
            dcImState.scale = s; dcImState.offsetX = 0; dcImState.offsetY = 0; dcImState.rotation = 0;
            dcImDraw();
        }
        function dcImReset(){ dcImFit(); document.getElementById('dc-im-brightness').value=100; document.getElementById('dc-im-contrast').value=100; dcImState.invert=false; dcImDraw(); }
        function dcImRotate(){ dcImState.rotation = (dcImState.rotation + 90) % 360; dcImDraw(); }
        function dcImInvert(){ dcImState.invert = !dcImState.invert; dcImDraw(); }
        function dcImApplyFilter(){ dcImDraw(); }
        function dcImFullscreen(){
            var el = document.getElementById('dc-im-viewer-modal');
            if (el.requestFullscreen) el.requestFullscreen();
        }
        function dcImSetTool(t){ dcImState.tool = t; dcImState.measurePoints = []; }
        function dcImInfo(){ alert('اطلاعات تصویر:\n' + document.getElementById('dc-im-info-box').textContent + '\n\nنکته: چون این تصویر DICOM نیست، اندازه‌گیری فقط بر حسب پیکسل نسبیه، نه میلی‌متر واقعی.'); }
        function dcImUndo(){
            var last = dcImState.annotations.pop();
            if (last && last.id) {
                var fd = new FormData();
                fd.append('action','dental_imaging_delete_annotation');
                fd.append('_wpnonce', dcImNonce);
                fd.append('id', last.id);
                fetch(dcImAjax, {method:'POST', body:fd});
            }
            dcImDraw();
        }

        function bindCanvasEvents(){
            var panning = false, panStartX, panStartY;
            canvas.onmousedown = function(e){
                var rect = canvas.getBoundingClientRect();
                var mx = e.clientX - rect.left, my = e.clientY - rect.top;
                if (dcImState.tool === 'none') { panning = true; panStartX = mx - dcImState.offsetX; panStartY = my - dcImState.offsetY; return; }
                dcImState.drawing = true;
                var rel = dcImScreenToRel(mx, my);
                dcImState.startX = rel.x; dcImState.startY = rel.y;
                if (dcImState.tool === 'distance' || dcImState.tool === 'angle') {
                    dcImState.measurePoints.push(rel);
                }
                if (dcImState.tool === 'text' || dcImState.tool === 'tooth_label') {
                    var txt = prompt(dcImState.tool==='tooth_label' ? 'شماره دندان:' : 'متن:');
                    if (txt) { dcImSaveAnnotation(dcImState.tool, {x1:rel.x, y1:rel.y, text:txt}); }
                    dcImState.drawing = false;
                }
                if (dcImState.tool === 'freehand') { dcImState.freehandPoints = [rel]; }
            };
            canvas.onmousemove = function(e){
                var rect = canvas.getBoundingClientRect();
                var mx = e.clientX - rect.left, my = e.clientY - rect.top;
                if (panning) { dcImState.offsetX = mx - panStartX; dcImState.offsetY = my - panStartY; dcImDraw(); return; }
                if (dcImState.drawing && dcImState.tool === 'freehand') {
                    var rel = dcImScreenToRel(mx, my);
                    dcImState.freehandPoints.push(rel);
                    dcImDraw();
                    dcImDrawTempFreehand();
                }
            };
            canvas.onmouseup = function(e){
                panning = false;
                if (!dcImState.drawing) return;
                dcImState.drawing = false;
                var rect = canvas.getBoundingClientRect();
                var mx = e.clientX - rect.left, my = e.clientY - rect.top;
                var rel = dcImScreenToRel(mx, my);
                var color = document.getElementById('dc-im-color').value;

                if (['arrow','line','rect','circle'].includes(dcImState.tool)) {
                    dcImSaveAnnotation(dcImState.tool, {x1:dcImState.startX, y1:dcImState.startY, x2:rel.x, y2:rel.y}, color);
                } else if (dcImState.tool === 'freehand' && dcImState.freehandPoints) {
                    dcImSaveAnnotation('freehand', {points: dcImState.freehandPoints}, color);
                    dcImState.freehandPoints = null;
                } else if (dcImState.tool === 'distance' && dcImState.measurePoints.length === 2) {
                    var p1=dcImState.measurePoints[0], p2=dcImState.measurePoints[1];
                    var iw = dcImState.img.naturalWidth, ih = dcImState.img.naturalHeight;
                    var dist = Math.hypot((p2.x-p1.x)*iw, (p2.y-p1.y)*ih);
                    dcImSaveMeasurement('distance', [p1,p2], dist);
                    alert('فاصله: ' + dist.toFixed(1) + ' پیکسل (بدون DICOM، میلی‌متر واقعی موجود نیست)');
                    dcImState.measurePoints = [];
                } else if (dcImState.tool === 'angle' && dcImState.measurePoints.length === 3) {
                    dcImSaveMeasurement('angle', dcImState.measurePoints, 0);
                    dcImState.measurePoints = [];
                }
            };
            canvas.onwheel = function(e){
                e.preventDefault();
                dcImZoom(e.deltaY < 0 ? 1.1 : 0.9);
            };
        }
        function dcImScreenToRel(mx, my){
            var iw = dcImState.img.naturalWidth, ih = dcImState.img.naturalHeight;
            var x = (mx - canvas.width/2 - dcImState.offsetX) / dcImState.scale + iw/2;
            var y = (my - canvas.height/2 - dcImState.offsetY) / dcImState.scale + ih/2;
            return { x: x/iw, y: y/ih };
        }
        function dcImDrawTempFreehand(){
            if (!dcImState.freehandPoints) return;
            ctx.save();
            ctx.translate(canvas.width/2 + dcImState.offsetX, canvas.height/2 + dcImState.offsetY);
            ctx.rotate(dcImState.rotation * Math.PI/180);
            ctx.scale(dcImState.scale, dcImState.scale);
            var iw = dcImState.img.naturalWidth, ih = dcImState.img.naturalHeight;
            ctx.strokeStyle = document.getElementById('dc-im-color').value; ctx.lineWidth = 2/dcImState.scale;
            ctx.beginPath();
            dcImState.freehandPoints.forEach(function(p,i){ var x=(p.x-0.5)*iw, y=(p.y-0.5)*ih; i===0?ctx.moveTo(x,y):ctx.lineTo(x,y); });
            ctx.stroke();
            ctx.restore();
        }
        function dcImSaveAnnotation(type, data, color){
            var fd = new FormData();
            fd.append('action','dental_imaging_save_annotation');
            fd.append('_wpnonce', dcImNonce);
            fd.append('image_id', dcImState.imageId);
            fd.append('type', type);
            fd.append('data', JSON.stringify(data));
            fd.append('color', color || document.getElementById('dc-im-color').value);
            fetch(dcImAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if (res.success) { dcImState.annotations.push({id:res.data.id, type:type, data:data, color:color}); dcImDraw(); }
            });
        }
        function dcImSaveMeasurement(type, points, value){
            var fd = new FormData();
            fd.append('action','dental_imaging_save_measurement');
            fd.append('_wpnonce', dcImNonce);
            fd.append('image_id', dcImState.imageId);
            fd.append('type', type);
            fd.append('points', JSON.stringify(points));
            fd.append('value', value);
            fetch(dcImAjax, {method:'POST', body:fd});
        }

        function dcImOpenCompare(){
            var s1 = document.getElementById('dc-im-compare-1'), s2 = document.getElementById('dc-im-compare-2');
            s1.innerHTML = s2.innerHTML = dcImAllImages.map(function(i){return '<option value="'+i.id+'">'+i.label+'</option>';}).join('');
            document.getElementById('dc-im-compare-modal').style.display = 'block';
        }
        function dcImLoadCompare(){
            ['1','2'].forEach(function(n){
                var imgId = document.getElementById('dc-im-compare-'+n).value;
                var cv = document.getElementById('dc-im-compare-canvas-'+n);
                var cctx = cv.getContext('2d');
                cv.width = cv.parentElement.clientWidth; cv.height = cv.parentElement.clientHeight;
                fetch(dcImAjax + '?action=dental_imaging_get_meta&image_id=' + imgId + '&_wpnonce=' + dcImNonce)
                    .then(r=>r.json()).then(function(res){
                        var img = new Image();
                        img.onload = function(){
                            var s = Math.min(cv.width/img.naturalWidth, cv.height/img.naturalHeight) * 0.9;
                            cctx.clearRect(0,0,cv.width,cv.height);
                            cctx.drawImage(img, cv.width/2 - img.naturalWidth*s/2, cv.height/2 - img.naturalHeight*s/2, img.naturalWidth*s, img.naturalHeight*s);
                        };
                        img.src = dcImFileBase + '?dental_imaging_file=' + res.data.file_path;
                    });
            });
        }
        </script>
        <?php
    }
}
