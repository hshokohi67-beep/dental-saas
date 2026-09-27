<?php
/**
 * Dental Clinic — فایل تنظیمات محیط
 *
 * این فایل را در پوشه پلاگین نگه‌دارید و مستقیم ادیت کنید.
 * برای محیط production تمام DEV ثابت‌ها را false کنید.
 *
 * ⚠️  این فایل را هرگز در Git commit نکنید — به .gitignore اضافه کنید.
 */

defined( 'ABSPATH' ) || exit;

// ─── حالت توسعه / لوکال ──────────────────────────────────────────────────────
// true  = Mock Mode: هیچ پیامکی ارسال نمی‌شود، کد OTP در debug.log نوشته می‌شود
// false = Production: ارسال واقعی پیامک و درگاه پرداخت
if ( ! defined( 'DENTAL_DEV_MODE' ) ) {
    define('DENTAL_DEV_MODE', false);
}

// کد OTP ثابت برای تست لوکال (فقط در DEV_MODE اعمال می‌شود)
if ( ! defined( 'DENTAL_DEV_OTP_CODE' ) ) {
    define( 'DENTAL_DEV_OTP_CODE', '123456' );
}

// نمایش Console Log در مرورگر (Dev Mode)
if ( ! defined( 'DENTAL_DEV_CONSOLE_LOG' ) ) {
    define( 'DENTAL_DEV_CONSOLE_LOG', true );
}

// لاگ کردن تمام درخواست‌های API خارجی
if ( ! defined( 'DENTAL_LOG_API_CALLS' ) ) {
    define( 'DENTAL_LOG_API_CALLS', true );
}

// ─── OTP Settings ─────────────────────────────────────────────────────────────
if ( ! defined( 'DENTAL_OTP_EXPIRY_MINUTES' ) ) {
    define( 'DENTAL_OTP_EXPIRY_MINUTES', 5 );        // انقضای کد OTP
}
if ( ! defined( 'DENTAL_OTP_MAX_ATTEMPTS' ) ) {
    define( 'DENTAL_OTP_MAX_ATTEMPTS', 3 );           // حداکثر تلاش اشتباه
}
if ( ! defined( 'DENTAL_OTP_RESEND_COOLDOWN' ) ) {
    define( 'DENTAL_OTP_RESEND_COOLDOWN', 90 );       // ثانیه بین ارسال مجدد
}
if ( ! defined( 'DENTAL_OTP_RATE_LIMIT' ) ) {
    define( 'DENTAL_OTP_RATE_LIMIT', 5 );             // حداکثر OTP در ساعت
}
if ( ! defined( 'DENTAL_OTP_LENGTH' ) ) {
    define( 'DENTAL_OTP_LENGTH', 6 );                 // طول کد OTP
}

// ─── Session Settings ─────────────────────────────────────────────────────────
if ( ! defined( 'DENTAL_SESSION_LIFETIME' ) ) {
    define( 'DENTAL_SESSION_LIFETIME', 30 * DAY_IN_SECONDS );
}

// ─── Encryption ───────────────────────────────────────────────────────────────
// کلید رمزنگاری — در production یک کلید قوی ۳۲ کاراکتری تنظیم کنید
if ( ! defined( 'DENTAL_ENCRYPTION_KEY' ) ) {
    define( 'DENTAL_ENCRYPTION_KEY', get_option( 'dental_encryption_key', '' ) );
}
