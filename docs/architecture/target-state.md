# معماری هدف (Target Architecture) — Dental Clinic SaaS

این سند بر اساس دستورالعمل معماریِ تأییدشده توسط کارفرما نوشته شده (نه حدس). جزئیات کامل‌تر هرکدام از تصمیم‌ها را می‌توان از سند اصلی پروژه («DENTAL SaaS — MASTER DEVELOPMENT PROMPT») استخراج کرد؛ این‌جا خلاصه‌ی ساخت‌یافته و قابل‌ارجاع آن آمده است.

## ۱. دیاگرام معماری سطح بالا

```
                ┌─────────────────────────┐
                │      Next.js Frontend    │   (React + TypeScript، RTL، فارسی/شمسی)
                │  Experience Layer only   │
                └────────────┬─────────────┘
                             │  REST API (JSON)
                ┌────────────▼─────────────┐
                │      Laravel Backend     │   (Application + Domain Layer)
                │   منطق کسب‌وکار این‌جاست  │
                └────────────┬─────────────┘
                             │
             ┌───────────────┼────────────────┐
             │               │                │
        PostgreSQL         Redis        Object Storage (S3-compatible)
     (داده رابطه‌ای)    (کش/صف)      (تصاویر/DICOM/فایل‌ها — signed URL)
```

سبک معماری: **Modular Monolith**. میکروسرویس معرفی نمی‌شود مگر نیاز آینده اثبات‌شده باشد.

قانون بنیادین: **منطق کسب‌وکار هرگز در Next.js نیست.** فرانت‌اند فقط لایه‌ی تجربه‌ی کاربری است؛ Laravel لایه‌ی برنامه/دامنه است.

## ۲. مرزهای دامنه (Domain Boundaries)

هفده دامنه در `backend/app/Domain/*`: Platform · Tenancy · Identity · Patients · Dental · Clinical · Operations · Financial · Inventory · Laboratory · Imaging · Communication · Analytics · AI · Files · Training · Website.

قانون وابستگی: هر دامنه مسئولیت مشخص و مرز واضح دارد؛ به‌جای یک اپلیکیشن/جدول/کنترلر غول‌پیکر. وقتی دامنه‌ای پایین‌دست به چیزی از دامنه‌ی بالادست نیاز دارد، یک contract/interface در دامنه‌ی پایین‌دست تعریف و در provider دامنه‌ی بالادست bind می‌شود (الگویی که در پروژه‌ی مرجع داخلی -کافه‌یار- هم برای جهت‌دهی وابستگی ماژول‌ها استفاده شده و در عمل جواب داده است).

لایه‌بندی درخواست: `Controller (نازک) → Application Action → Domain Service/Business Rule → Repository/Model → Database`. منطق پیچیده هرگز داخل کنترلر نیست.

## ۳. سلسله‌مراتب SaaS/Tenancy

```
Platform → Tenant → Branch → Room → Unit
```

- **Patient یک موجودیت سطح Tenant است**، نه سطح Branch (تکرار نمی‌شود به‌ازای هر شعبه؛ شعبه فقط context است). این تفاوت اساسی با پیاده‌سازی legacy است (که اصلاً مفهوم چندشعبه‌ای نداشت).
- **جداسازی مستأجر (Tenant Isolation) اجباری و در سطح سرور enforce می‌شود** — هرگز به `tenant_id` ارسالی از فرانت‌اند اعتماد نمی‌شود.
- **موتور Configuration Override:** `Global → Plan → Tenant → Branch → User`؛ تغییر تنظیمات هرگز رکوردهای بالینیِ تاریخی را تغییر نمی‌دهد. این منطق باید متمرکز باشد (نه تکرار در هر ماژول) — دقیقاً نقطه‌ضعفی که در legacy دیده شد (تنظیمات global option پراکنده).

## ۴. هویت و مجوز (Identity & Authorization)

User، Staff، Role، Permission، Dashboard Access، Patient Access Policy از هم جدا هستند (Role ≠ Dashboard Access ≠ Patient Access ≠ Permission). **مجوزدهی متمرکز و سمت سرور روی هر endpoint API enforce می‌شود** — پنهان‌کردن در UI امنیت محسوب نمی‌شود. این مستقیماً نقطه‌ضعف legacy را جبران می‌کند (که کنترل دسترسی را پراکنده داخل رندر هر صفحه انجام می‌داد، نه یک لایه‌ی authorization متمرکز و API-level).

Patient scope: Own / Assigned / Branch / Tenant. Clinical permissions (View/Create/Edit Own/Edit Any/Delete Own/Delete Any/Archive/Cancel/Perform) از Financial و Inventory و AI permissions جدا هستند.

## ۵. استراتژی دیتابیس

- PostgreSQL، جدول‌های دامنه‌محور (نه یک جدول غول‌پیکر چندمنظوره مثل بسیاری از الگوهای legacy).
- کلید خارجی، ایندکس، unique constraint، و uniqueness در سطح tenant.
- فیلدهای مشترک: `id, created_at, updated_at, created_by, updated_by` + فیلدهای آرشیو در جای لازم.
- **بدون حذف سخت (hard delete) برای رکوردهای بالینی/مالی/رضایت‌نامه/حسابرسی/تراکنش انبار** — اصلاح فقط از طریق reversal/adjustment/refund/void/archive.

## ۶. استراتژی API و فرانت‌اند

- REST API با Laravel Sanctum (یا معادل امن SPA).
- ولیدیشن ورودی، Resource برای خروجی، خطاها Exception اختصاصی با پیام فارسی + کد پایدار.
- فرانت‌اند: Next.js App Router، RTL-first، پشتیبانی فارسی/شمسی؛ منطق دامنه در `features/services/hooks/stores/types`، نه در کامپوننت.

## ۷. مرزهای امنیتی و جداسازی مستأجر

Tenant isolation، RBAC، Patient Access Policy، rate limiting، CSRF/XSS protection، signed file URL، audit log، session security، secrets management، backup، معماری 2FA. هیچ‌کدام از `tenant_id/branch_id/patient_id/permissions` ارسالی از کلاینت بدون اعتبارسنجی سمت سرور پذیرفته نمی‌شود.

## ۸. معماری ذخیره‌سازی فایل

جدا کردن متادیتای دیتابیس از باینری/آبجکت استوریج (S3-compatible). پیش‌فرض خصوصی (Private) + signed URL که فقط بعد از احراز مجوز صادر می‌شود. نسخه‌بندی، آرشیو، بازیابی، retention، بکاپ، لاگ دسترسی.

## ۹. معماری AI

هیچ ماژول کسب‌وکاری مستقیماً AvalAI (یا هر Provider) را صدا نمی‌زند — همه از پشت **AI Gateway** عبور می‌کنند (provider-agnostic؛ Provider/Model/Feature Registry/Prompt Management/Context Builder/Permissions/Privacy Rules/Credits/Usage Metering/Cost Tracking/Limits/Alerts/Logs/Fallback). AI فقط دستیار است؛ بدون workflow تأییدشده‌ی صریح نمی‌تواند رأساً تشخیص بدهد، نسخه بنویسد، یا رکورد بالینی/مالی را تغییر دهد.

## ۱۰. معماری تصویربرداری حرفه‌ای

`Patient → Imaging Study → Series → Images`، ویوئر Cornerstone3D (`@cornerstonejs/core|tools|dicom-image-loader`)، سازگاری آینده با DICOMweb/Orthanc/PACS بدون معرفی زودهنگام و غیرضروری آن زیرساخت.

## ۱۱. معماری وب‌سایت عمومی تنانت

یک بک‌اند SaaS مرکزی؛ هر تنانت وب‌سایت عمومیِ خودش را از طریق دامنه‌ی سفارشی و APIهای همان SaaS مرکزی می‌گیرد (Base Template → Tenant Data → Theme → Sections → SEO → Rendered Website) — بدون کدبیس مجزا به‌ازای هر تنانت.

## ۱۲. چرا این تصمیم‌ها؟ (ADR خلاصه — تفصیل در `docs/decisions/`)

| تصمیم | چرا |
|---|---|
| PostgreSQL به‌جای MySQL/MariaDB legacy | یکپارچگی داده رابطه‌ای قوی‌تر، constraint غنی‌تر، مناسب SaaS چندمستأجری |
| Modular Monolith به‌جای میکروسرویس | تیم/مقیاس فعلی نیازمند microservices نیست؛ پیچیدگی عملیاتی غیرضروری |
| Next.js جدا از Laravel به‌جای رندر PHP/وردپرس | جداسازی تجربه از منطق کسب‌وکار؛ legacy این جداسازی را نداشت و همین باعث می‌شد امنیت (permission) و UI در هم تنیده شوند |
| Patient سطح Tenant، نه سطح Branch | جلوگیری از تکرار پرونده‌ی بیمار بین شعبه‌های یک تنانت |
| Plan Owner ≠ Treatment Provider (بخش ۲۷ سند مادر) | یک پزشک می‌تواند طرح‌درمانِ پزشک دیگر را اجرا کند؛ هر دو باید جدا حسابرسی شوند |
| بدون میکروسرویس برای هر تنانتِ وب‌سایت | یک بک‌اند مرکزی، مقیاس‌پذیرتر و قابل‌نگهداری‌تر از کدبیس مجزا به‌ازای هر مشتری |

## ۱۳. نگاشت migration کلی (خلاصه؛ جزئیات کامل در `docs/migration/feature-inventory.md`)

| موضوع Legacy | دامنه‌ی جدید |
|---|---|
| بیمار (WP Post) + پرونده/چارت دندانی | Patients + Dental + Clinical |
| کاتالوگ خدمات (۳۸۸ خدمت) | Financial (Price Lists/Services) + Dental (Treatment Target routing) |
| پذیرش، شیفت‌بندی اتاق، حضور و غیاب | Operations |
| مالی (دفتر روزانه، اقساط، کارتخوان) | Financial |
| انبار (۱۱۴۵ کالا) | Inventory (+ Smart Consumption به‌جای مدیریت دستی رسپی) |
| نوبت‌دهی آنلاین (`dental-booking`) | Operations (Appointments/Online Booking) — نیازمند افزودن مفهوم Room/Unit/Branch که در legacy نبود |
| پیامک/پیام داخلی | Communication |
| بیمه، رضایت‌نامه، نسخه‌نویسی | Clinical + Financial (تسویه بیمه) |
| مسیر درمان/اندو | Clinical (Treatment Pathway + Endodontic specialty entity) |
| تصویربرداری پایه | Imaging (ارتقا به معماری حرفه‌ای Study/Series/Image + Cornerstone3D) |
| لاگ حسابرسی/بکاپ/چنج‌لاگ | System (Audit) + Platform |
| — (وجود ندارد در legacy) | Website (وب‌سایت عمومی تنانت) — ماژول کاملاً جدید |
| — (وجود ندارد در legacy) | AI Gateway — ماژول کاملاً جدید |
