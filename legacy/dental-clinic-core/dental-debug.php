<?php
$wp_load = dirname(__FILE__) . '/../../../wp-load.php';
if (!file_exists($wp_load)) die('wp-load not found');
require_once $wp_load;
if (!current_user_can('manage_options')) die('Access denied');

echo '<pre style="direction:ltr;text-align:left;font-size:12px;padding:20px;background:#111;color:#0f0;">';

$path = WP_PLUGIN_DIR . '/dental-clinic-core/admin/class-dental-admin.php';
$content = file_get_contents($path);
echo "آخرین تغییر: " . date('Y-m-d H:i:s', filemtime($path)) . "\n";
echo "حجم: " . filesize($path) . " بایت\n\n";

echo "=== آیا حاوی الگوی جدید (print_invoice + dental-dashboard) است؟ ===\n";
echo (strpos($content, "\$_GET['page'] !== 'dental-dashboard' || !isset(\$_GET['print_invoice'])")!==false ? '✅ بله' : '❌ نه، نسخه قدیمی') . "\n\n";

echo "=== آیا هنوز رد پای dental-invoice-print باقی مانده؟ (باید دیگه نباشه) ===\n";
echo (strpos($content,'dental-invoice-print')!==false ? '⚠️ هنوز هست، تعداد: ' . substr_count($content,'dental-invoice-print') : '✅ کاملاً حذف شده') . "\n\n";

echo "=== خط دقیق تابع maybe_print_invoice ===\n";
if (preg_match('/public function maybe_print_invoice.*?\n    \}/s', $content, $m)) {
    echo $m[0] . "\n";
} else {
    echo "❌ تابع پیدا نشد!\n";
}

echo "\n=== شبیه‌سازی مستقیم — آیا هوک واقعاً کار می‌کنه؟ ===\n";
$_GET['page'] = 'dental-dashboard';
$_GET['print_invoice'] = '1';
$_GET['patient_id'] = '45';

if (class_exists('Dental_Admin')) {
    $ref = new ReflectionClass('Dental_Admin');
    if ($ref->hasMethod('maybe_print_invoice')) {
        $method = $ref->getMethod('maybe_print_invoice');
        $method->setAccessible(true);
        $instance = new Dental_Admin();
        ob_start();
        try {
            $method->invoke($instance);
            $out = ob_get_clean();
            echo "متد بدون exit برگشت! (این خودش یه نشونه‌ست)\n";
            echo "خروجی: " . substr($out, 0, 300) . "\n";
        } catch (\Throwable $e) {
            ob_end_clean();
            echo "خطا: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ متد maybe_print_invoice روی کلاس وجود نداره!\n";
    }
}

echo '</pre>';
