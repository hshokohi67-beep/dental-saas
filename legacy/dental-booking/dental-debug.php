<?php
$wp_load = dirname(__FILE__) . '/../../../wp-load.php';
if (!file_exists($wp_load)) die('wp-load not found');
require_once $wp_load;
if (!current_user_can('manage_options')) die('Access denied');

echo '<pre style="direction:ltr;text-align:left;font-size:13px;padding:20px;background:#1a1a1a;color:#0f0;">';

$file = WP_PLUGIN_DIR . '/dental-clinic-core/assets/js/dental-datepicker.js';

if (!file_exists($file)) {
    echo "❌ FILE DOES NOT EXIST: $file\n";
} else {
    $content = file_get_contents($file);
    echo "File size: " . strlen($content) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n\n";
    echo "Has 'open-months' (v4 feature): " . (strpos($content,'open-months')!==false ? '✅ YES — v4 نصب شده' : '❌ NO — نسخه قدیمی روی سرور است') . "\n";
    echo "Has 'ddp-htitle': " . (strpos($content,'ddp-htitle')!==false ? '✅ YES' : '❌ NO') . "\n";
    echo "Has 'drawMonths': " . (strpos($content,'drawMonths')!==false ? '✅ YES' : '❌ NO') . "\n";
}

echo '</pre>';
