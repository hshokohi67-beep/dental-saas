# نقشه‌راه پیاده‌سازی (Implementation Roadmap)

روش کار: **Vertical Slices** — هر فاز به‌طور کامل (دیتابیس + بک‌اند + مجوز + API + فرانت‌اند + تست + مستندسازی + verification) پیاده و تثبیت می‌شود، سپس فاز بعد شروع می‌شود. هیچ فازی هم‌زمان با فاز دیگر شروع نمی‌شود.

هر فاز پیش از شروعِ کدنویسی، طبق قاعده‌ی کار (بخش ۷۷ سند مادر) توضیح داده می‌شود، بعد از هر ویژگی معنادار تست/مهاجرت/API/مجوز/UI/edge case چک و در پایان فاز گزارش می‌شود.

---

## فاز ۰ — زیرساخت (Foundation)
**هدف:** یک اسکلت قابل اجرا و قابل تست، بدون هیچ منطق کسب‌وکاری دامنه‌محور.

- مخزن، Docker Compose (Laravel API + Next.js Web + PostgreSQL + Redis + Object Storage local)
- محیط `.env` نمونه
- اسکلت Next.js (App Router, RTL, فونت فارسی) و اسکلت Laravel (ساختار `Domain/Application/Infrastructure/Shared`)
- پایه‌ی احراز هویت (Sanctum) — بدون نقش/دامنه‌ی کسب‌وکاری هنوز
- Design System پایه (توکن‌ها، کامپوننت‌های اولیه RTL)
- زیرساخت تست (PHPUnit/Pest سمت بک‌اند، Vitest/Playwright سمت فرانت)
- CI (لینت، تایپ‌چک، تست) — طبق الگوی موفق پروژه‌ی مرجع داخلی (`php artisan test`, phpstan/larastan, pint, `npm run typecheck/test/lint/build`)

**معیار پذیرش:** `docker compose up` یک صفحه‌ی لاگین کارکرد را نشان می‌دهد؛ تست‌ها و لینت در CI سبزند.

---

## فاز ۱ — هسته‌ی SaaS
**ویژگی‌ها:** Tenant, Branch, Users, Staff, Roles, Permissions, Plans, Subscriptions, Configuration Override Engine, Super Admin پایه.

**وابستگی:** فاز ۰. **معیار پذیرش:** ساخت یک تنانت جدید با یک شعبه و حداقل دو نقش کاربری کار می‌کند؛ Tenant Isolation در `TenantIsolationTest` معادل پوشش داده شده.

---

## فاز ۲ — هسته‌ی بیمار (Patient Core)
**ویژگی‌ها:** Patient (سطح Tenant)، Patient 360، Medical History، Allergies، Medications، Clinical Alerts، Timeline، Duplicate Detection، Merge.

**نگاشت از legacy:** ماژول‌های `patient/`, `medical-history/` + CPT بیمار قدیمی → مدل جدید غیر-WP.
**معیار پذیرش:** ایجاد/جست‌وجوی بیمار، مشاهده‌ی Patient 360 با تب‌های حداقلی، merge دو بیمار تکراری.

---

## فاز ۳ — هسته‌ی دندانی (Dental Core)
**ویژگی‌ها:** Dentition (Permanent/Primary/Mixed)، Teeth، Surfaces، Conditions، Odontogram، Treatment Targets، Contextual Treatment Picker.

**نکته‌ی حیاتی:** سیستم شماره‌گذاری فعلیِ `assets/js/admin/dental-chart.js` legacy باید دقیقاً بررسی و با شماره‌گذاری «دیدگاه بیمار» الزام‌شده (بخش ۱۸ سند مادر — نه FDI به‌عنوان اصلی) تطبیق داده شود؛ جزئیات این تطبیق در `docs/migration/business-rules.md`.
**معیار پذیرش:** چارت دندانی برای دندان دائمی و شیری قابل نمایش/کلیک است؛ تست خودکار نگاشت شماره‌گذاری (permanent+primary، هر چهار ربع) وجود دارد.

---

## فاز ۴ — عملیات (Operations)
**ویژگی‌ها:** Appointments، Online Booking، پذیرش، Check-in، Queue، Rooms، Units، Shifts، Leave، Scheduling Engine.

**نگاشت از legacy:** پلاگین `dental-booking` (منطق شیفت هفتگی، سقف روزانه‌ی خدمت، محدودیت خدمت به‌ازای شیفت، پیامک‌های خودکار) به‌عنوان الزام کسب‌وکار حفظ می‌شود؛ **شکاف اصلی که باید پر شود: افزودن Room/Unit/Branch** که در legacy وجود نداشت.
**معیار پذیرش:** رزرو آنلاین با انتخاب پزشک/خدمت/تاریخ کار می‌کند؛ تست تداخل نوبت/اتاق/یونیت/شیفت سبز است.

---

## فاز ۵ — بالینی (Clinical)
**ویژگی‌ها:** Encounter، Diagnosis، Clinical Notes، Treatment، Prescription، Consent، Clinical Summary.

**نگاشت از legacy:** `consent/`, `prescription-manager`, بخش‌های بالینی `patient-profile`.
**معیار پذیرش:** جریان کامل Appointment→Check-in→Encounter→Treatment ثبت می‌شود؛ Consent نسخه‌بندی‌شده و غیرقابل‌تغییر پس از پذیرش است.

---

## فاز ۶ — طرح‌ریزی درمان (Treatment Planning)
**ویژگی‌ها:** Treatment Plans، Plan Items، Treatment Acceptance، Treatment Pathways، Pathway Templates، Dependencies.

**نگاشت از legacy:** `pathway/class-pathway-manager.php`, `pathway/class-endodontic-manager.php`, `admin/pages/class-page-pathway-templates.php`.
**معیار پذیرش:** Plan Owner و Treatment Provider جدا حسابرسی می‌شوند (بخش ۲۷)؛ یک مسیر درمان نمونه (مثلاً RCT→Build-up→Post/Core→Crown) با وابستگی بین مراحل کار می‌کند.

---

## فاز ۷ — کسب‌وکار (Business)
**ویژگی‌ها:** Financial، Inventory، Smart Consumption، Laboratory، Imaging حرفه‌ای.

**نگاشت از legacy:** `ledger/`, `financial/class-installment-manager.php`, `pos/`, `inventory/` (۱۱۴۵ کالا) → با معماری «مصرف مرجع/تخمینی» جدید (بخش ۳۴) به‌جای مدیریت دستی رسپی مواد.
**معیار پذیرش:** صدور فاکتور/قسط/پرداخت با state machine بخش ۵۰؛ سفارش آزمایشگاهی با remake مرتبط به کیس اصلی؛ آپلود و مشاهده‌ی تصویر با Cornerstone3D.

---

## فاز ۸ — ارتباطات (Communication)
**ویژگی‌ها:** Notifications، Messaging، Tasks، SMS، Email، Recall، Follow-up.

**نگاشت از legacy:** `sms/class-sms-dispatcher.php`, `workspace/` (پیام داخلی)، `recall/`.
**معیار پذیرش:** رویداد `TreatmentCompleted` باعث Notification+Timeline+Follow-up می‌شود (event-driven، نه state polling).

---

## فاز ۹ — تحلیل (Analytics)
**ویژگی‌ها:** KPI Engine، گزارش‌ها، Dashboard Data Layer، Export، Scheduled Reports.

**نگاشت از legacy:** `analytics/class-analytics-manager.php`, `doctor-activity/`, `performance-report`.
**معیار پذیرش:** حداقل یک گزارش شیفت→بیمار→درمان→مصرف (بخش ۳۳) end-to-end کار می‌کند.

---

## فاز ۱۰ — هوش مصنوعی (AI)
**ویژگی‌ها:** AI Gateway، Providers، Models، Credits، Usage، AI Features، Prompt Management.

**معیار پذیرش:** حداقل یک قابلیت AI از پشت Gateway (نه صدای مستقیم) با متری‌سازی مصرف/هزینه کار می‌کند.

---

## فاز ۱۱ — تجربه (Experience)
**ویژگی‌ها:** مرکز آموزش، جست‌وجوی پیشرفته، Quick Actions، Command Center، خلاصه‌ی پرونده‌ی بیمار (📋)، UX پیشرفته.

**معیار پذیرش:** خلاصه‌ی پرونده مطابق بخش ۱۷ (چارت رنگی، فیلتر، تایم‌لاین کامل) از موجودیت‌های اصلی مشتق می‌شود، نه دیتابیس تکراری.

---

## فاز ۱۲ — وب‌سایت (Website)
**ویژگی‌ها:** وب‌سایت عمومی تنانت، قالب‌ها، تم‌ها، SEO، دامنه‌ی سفارشی، اتصال نوبت‌دهی.

**معیار پذیرش:** یک تنانت نمونه با دامنه‌ی سفارشی و صفحه‌ی عمومی که از همان API مرکزی داده می‌گیرد.

---

## ریسک‌های کلی روی کل نقشه‌راه

1. **حجم زیاد کاتالوگ/انبار (۷۰۰+ گره کاتالوگ درختی / ۱۱۴۵ قلم انبار):** استراتژی import باید از فاز ۷ (Inventory) و بخشی از فاز ۲/۳ (کاتالوگ خدمات به‌عنوان پایه‌ی Treatment Target) طراحی شود، نه در پایان.
2. **نبود Room/Unit/Branch در booking قدیمی:** باید به‌صراحت به کاربران/کلینیک‌های فعلی اطلاع داده شود که رفتار رزرو در فاز ۴ تغییر می‌کند (افزودن انتخاب اتاق/یونیت). توجه: شیفت‌بندی داخلی اتاق‌ها (`dental_shift_assignments`) از قبل مدل نسبتاً غنی‌ای دارد (اتاق+نوبت صبح/عصر+اسلات+دوپزشکی) ولی با نوبت‌دهی آنلاین sync نیست — این دو باید در فاز ۴ به یک مدل واحد ادغام شوند.
3. **شماره‌گذاری دندان — ریسک کمتر از حد انتظار، تأیید شد:** کد قدیمی از استاندارد واقعی FDI (quadrant×10+n) به‌عنوان ذخیره‌سازی و از شماره‌ی دیدگاه بالینی (mirrored، نه دیدگاه خودِ بیمار در آینه) برای نمایش استفاده می‌کند — دقیقاً همان الگویی که سند مادر می‌خواهد (FDI داخلی + نمایش patient/clinician-perspective). ریسک واقعی جای دیگری است: ستون `tooth_surface` در دیتابیس **سطح تشریحی واقعی ذخیره نمی‌کند** (در عمل آرایه‌ی کد درمان است)، و رنگ/وضعیت دندان همیشه سمت کلاینت از روی درمان‌ها محاسبه می‌شود نه از ستون‌های دیتابیس (`condition_code`/`condition_color` عملاً مرده‌اند). فاز ۳ باید سطح‌بندی واقعی (mesial/distal/occlusal/...) را از صفر اضافه کند، نه از کد قدیمی وام بگیرد.
4. **مهاجرت داده‌ی واقعی از نصب‌های وردپرسی موجود** (در صورت وجود کلینیک‌های فعال روی legacy) نیازمند یک اسکریپت ETL جداگانه است؛ باید از **دو منبع موازی** merge کند: `dental_tooth_conditions`+postmeta `_chart_done_map` (نسل قدیمی) و `dental_catalog_treatments` (نسل جدیدتر) — وگرنه تاریخچه‌ی درمان بیماران واقعی ناقص مهاجرت می‌شود. جداگانه scope شود.
5. **ریسک حقوقی رضایت‌نامه:** متن نهایی رندرشده‌ی رضایت‌نامه در لحظه‌ی امضا snapshot نمی‌شود (فقط پارامترها + تصویر امضا)؛ اگر قالب بعداً عوض شود، چاپ مجدد متن جدید را با امضای قدیمی نشان می‌دهد. سیستم جدید باید این را اصلاح کند (بخش ۴۹ سند مادر: رضایت‌نامه باید نسخه‌بندی‌شده و غیرقابل‌تغییر پس از پذیرش باشد) — دقیقاً یک نمونه‌ی واقعی از چیزی که باید «الزام کسب‌وکار» گرفته شود ولی «راه‌حل فنی» آن اصلاح شود.
6. **دو پیاده‌سازی RBAC ناسازگار و موازی در کد پیدا شد** (`Dental_Installer::create_roles()` با ۶ نقش در برابر `class-roles-manager.php` با ۴ نقش متفاوت) — قبل از فاز ۱، باید تأیید شود کدام واقعاً در نصب زنده فعال است (نصب زنده‌ی بررسی‌شده نقش‌های سند فنی/Installer را نشان داد)، تا نقشه‌ی مجوز جدید بر مبنای رفتار واقعی باشد نه کد مرده.
7. **ناسازگاری نمایندگی پول:** بعضی جدول مالی legacy عدد صحیح (`bigint`) و بعضی اعشاری (`decimal`) هستند. طراحی جدید باید طبق قانون سند مادر (پول = عدد صحیح ریال) کاملاً یکدست شود، نه از این ناهماهنگی الگو بگیرد.
8. **کارتخوان (POS) چندترمینال legacy ناقص است:** فقط پیکربندی IP/پورت هرترمینال ذخیره می‌شود؛ پروتکل واقعی بانک به‌صورت placeholder ناتمام (socket خام) است و اصلاً برای محیط SaaS ابری (بدون دسترسی مستقیم به IP داخل شبکه‌ی کلینیک) کار نمی‌کند — این بخش باید کاملاً از نو و متناسب با معماری ابری طراحی شود، نه صرفاً پورت.
9. **کیف‌پول بیمار در postmeta:** موجودی کیف‌پول در یک فیلد meta تکی نگه‌داری می‌شود (نه مشتق از SUM تراکنش‌ها)؛ اگرچه با UPDATE اتمیک از race condition جلوگیری شده، اما در معماری جدید باید موجودی همیشه از ledger تراکنش‌ها مشتق یا با double-entry دقیق کنترل شود تا منبع حقیقت واحد باشد.

## معیار پذیرش فاز ۰ (تکرار برای شفافیت طبق بخش ۷۹)

- مخزن Git راه‌اندازی شده با ساختار مصوب.
- `docker-compose up` بدون خطا بالا می‌آید.
- صفحه‌ی لاگین (خالی از منطق دامنه) در Next.js رندر می‌شود و به Laravel API متصل است.
- حداقل یک تست بک‌اند و یک تست فرانت‌اند در CI سبز است.
- این نقشه‌راه و اسناد migration توسط کارفرما تأیید شده باشد.
