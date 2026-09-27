<?php
defined('ABSPATH') || exit;

/**
 * اتصال به کارتخوان شبکه‌ای (PC-POS)
 * ─────────────────────────────────────────────────────────────
 * نکته مهم: چون این پلاگین در کلینیک‌های مختلف با بانک‌های مختلف
 * نصب می‌شود، این کلاس یک لایه عمومی (Socket روی TCP/IP) فراهم
 * می‌کند. فرمت دقیق پیام ارسالی/دریافتی برای هر بانک با هم فرق
 * دارد و باید طبق مستندات SDK همان بانک تکمیل شود — جاهایی که
 * نیاز به این اطلاعات هست با کامنت «⚠️ اینجا را با مستندات بانک
 * خودتان تکمیل کنید» مشخص شده.
 */
class Dental_POS_Gateway {

    private string $provider;
    private string $ip;
    private int    $port;
    private string $merchant_id;
    private string $terminal_id;
    private string $device_id;

    public function __construct(string $device_id = '') {
        $device = self::get_device($device_id);
        $this->device_id   = $device['id'] ?? '';
        $this->provider    = $device['provider'] ?? 'generic';
        $this->ip          = $device['ip'] ?? '';
        $this->port        = (int)($device['port'] ?? 0);
        $this->merchant_id = $device['merchant_id'] ?? '';
        $this->terminal_id = $device['terminal_id'] ?? '';
    }

    // ─── همه دستگاه‌های فعال (برای دراپ‌داون انتخاب توی پذیرش/مالی) ──
    public static function get_active_devices(): array {
        $devices = get_option('dental_pos_devices', []);
        return array_values(array_filter($devices, fn($d) => !empty($d['enabled'])));
    }

    // ─── یه دستگاه مشخص، یا اگه خالی بود اولین دستگاه فعال ─────────
    private static function get_device(string $device_id = ''): ?array {
        $devices = get_option('dental_pos_devices', []);
        if ($device_id) {
            foreach ($devices as $d) { if ($d['id'] === $device_id) return $d; }
        }
        foreach ($devices as $d) { if (!empty($d['enabled'])) return $d; }
        return $devices[0] ?? null;
    }

    public static function is_enabled(): bool {
        return count(self::get_active_devices()) > 0;
    }

    // ─── تست ساده اتصال — فقط چک می‌کند پورت باز است یا نه ────────
    public function test_connection(): array {
        if (!$this->ip || !$this->port) {
            return ['success' => false, 'message' => 'آی‌پی یا پورت تنظیم نشده است.'];
        }
        $errno = 0; $errstr = '';
        $conn = @fsockopen($this->ip, $this->port, $errno, $errstr, 4);
        if (!$conn) {
            return ['success' => false, 'message' => "اتصال برقرار نشد: {$errstr} (کد {$errno})"];
        }
        fclose($conn);
        return ['success' => true, 'message' => 'اتصال به دستگاه با موفقیت برقرار شد.'];
    }

    /**
     * ارسال درخواست پرداخت به کارتخوان
     * @param float $amount مبلغ به تومان
     * @param int $patient_id شناسه بیمار (برای ثبت در دفتر روزانه بعد از موفقیت)
     * @return array ['success'=>bool,'message'=>string,'ref_id'=>string|null]
     */
    public function send_payment_request(float $amount, int $patient_id = 0): array {
        if (!self::is_enabled()) {
            return ['success' => false, 'message' => 'کارتخوان فعال نیست. از تنظیمات ← کارتخوان بررسی کنید.'];
        }

        $errno = 0; $errstr = '';
        $conn = @fsockopen($this->ip, $this->port, $errno, $errstr, 8);
        if (!$conn) {
            Dental_Dev_Logger::log('ERROR', 'اتصال به کارتخوان برقرار نشد', ['ip'=>$this->ip,'port'=>$this->port,'error'=>$errstr]);
            return ['success' => false, 'message' => "اتصال به کارتخوان برقرار نشد: {$errstr}"];
        }

        // ⚠️ اینجا را با مستندات بانک خودتان تکمیل کنید ─────────────
        // هر بانک فرمت پیام خودش رو داره (معمولاً XML یا رشته‌ای با
        // جداکننده خاص، شامل مبلغ + شماره پذیرنده/ترمینال + نوع تراکنش).
        // اینجا فقط یک نمونه ساده و عمومی گذاشته شده که باید عوض بشه:
        $request = $this->build_request($amount);
        fwrite($conn, $request);

        // ⚠️ اینجا هم مطابق مستندات بانک: معمولاً باید منتظر پاسخ
        // با یک timeout مشخص بمونیم (بعضی بانک‌ها چند ثانیه طول می‌کشه
        // چون کاربر باید کارت بکشه و رمز بزنه)
        stream_set_timeout($conn, 90); // فرصت کافی برای کشیدن کارت و زدن رمز
        $response = '';
        while (!feof($conn)) {
            $response .= fgets($conn, 1024);
        }
        fclose($conn);

        $result = $this->parse_response($response);

        // ─── در صورت موفقیت، خودکار در دفتر روزانه ثبت شود ─────────
        if ($result['success'] && $patient_id && class_exists('Dental_Ledger_Manager')) {
            Dental_Ledger_Manager::create([
                'patient_id'      => $patient_id,
                'treatment_title' => 'پرداخت کارتخوان' . ($result['ref_id'] ? ' — پیگیری: ' . $result['ref_id'] : ''),
                'amount_charged'  => $amount,
                'amount_received' => $amount,
                'payment_method'  => 'card',
                'notes'           => 'ثبت خودکار از کارتخوان شبکه‌ای',
            ]);
        }

        Dental_Dev_Logger::log('POS', 'نتیجه تراکنش کارتخوان', $result);
        return $result;
    }

    // ⚠️ اینجا را با مستندات بانک خودتان تکمیل کنید ─────────────────
    // این فقط یک نمونه عمومی و ساده است (رشته‌ی جداشده با کاما).
    // اکثر بانک‌ها (به‌پرداخت ملت، سامان، پاسارگاد و ...) فرمت
    // اختصاصی خودشون رو دارن که باید دقیقاً طبق SDK رعایت بشه.
    private function build_request(float $amount): string {
        switch ($this->provider) {
            case 'behpardakht':
                // ⚠️ فرمت واقعی به‌پرداخت ملت را اینجا جایگزین کنید
                return "AMOUNT={$amount};MID={$this->merchant_id};TID={$this->terminal_id}\r\n";
            case 'saman':
                // ⚠️ فرمت واقعی سامان کیش را اینجا جایگزین کنید
                return "AMOUNT={$amount};MERCHANT={$this->merchant_id}\r\n";
            default:
                // فرمت عمومی/نمونه
                return "SALE,{$amount},{$this->merchant_id},{$this->terminal_id}\r\n";
        }
    }

    // ⚠️ اینجا را با مستندات بانک خودتان تکمیل کنید ─────────────────
    // باید مشخص کنید پاسخ موفق/ناموفق/انصراف کاربر دقیقاً با چه
    // رشته یا کدی از کارتخوان برمی‌گرده تا اینجا درست تشخیص داده بشه.
    private function parse_response(string $raw): array {
        $raw = trim($raw);
        if ($raw === '') {
            return ['success' => false, 'message' => 'پاسخی از دستگاه دریافت نشد (timeout).', 'ref_id' => null];
        }

        // نمونه ساده: اگر پاسخ با "OK" شروع بشه یعنی موفق، با "CANCEL" یعنی
        // کاربر انصراف داده، در غیر این صورت ناموفق. این را با فرمت واقعی
        // بانک خودتان جایگزین کنید.
        if (stripos($raw, 'OK') === 0) {
            preg_match('/REF[:=]?\s*(\w+)/i', $raw, $m);
            return ['success' => true, 'message' => 'تراکنش با موفقیت انجام شد.', 'ref_id' => $m[1] ?? null];
        }
        if (stripos($raw, 'CANCEL') === 0) {
            return ['success' => false, 'message' => 'تراکنش توسط کاربر لغو شد.', 'ref_id' => null];
        }
        return ['success' => false, 'message' => 'تراکنش ناموفق بود.', 'ref_id' => null];
    }
}
