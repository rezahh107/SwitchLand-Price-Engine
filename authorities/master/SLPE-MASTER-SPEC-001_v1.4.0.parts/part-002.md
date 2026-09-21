- Queue باز در Current Set/Manifest/Run evidence حمل شود و در Runهای بعدی به‌عنوان known exact-subject exception ارزیابی شود؛ Subject یا Error جدید خودکار مشمول استثناء نیست؛
- هیچ حذف، Merge، تغییر هویت حدسی یا پنهان‌کردن Failure واقعی مجاز نباشد.

این مسیر فقط وضعیت Dataset مصوب و Lineage را Promote می‌کند؛ به معنی اثبات اصلاح Subjectهای deferred در Production نیست.

### GOV-007B — یک تأیید انسانی ساده برای کل فرایند

هدف این کنترل، گرفتن یک تصمیم واقعی و قابل‌فهم از Owner است؛ نه انتقال کنترل‌های فنی سیستم به کاربر.

برای هر Workflow با یک هدف مشخص، قواعد زیر الزامی است:

- Read، بررسی، ممیزی، Validation، ساخت Draft و تهیه Preview به Approval جداگانه نیاز ندارند.
- دستور صریح کاربر برای اجرای همان کار، مانند «اجرا کن»، «اعمال کن»، «تأیید است» یا عبارت طبیعی هم‌معنا، همان **یک تأیید نهایی** محسوب می‌شود. Token، قالب فنی، Hash، Resource ID، CAS، Revision یا عبارت قراردادی خاص از کاربر مطالبه نمی‌شود.
- اگر Workflow پیش از اجرا نیازمند نمایش Summary باشد، Summary باید حداکثر در پنج نکته ساده و غیرتخصصی بگوید: هدف چیست، چه چیزهایی تغییر می‌کنند، چه چیزهایی تغییر نمی‌کنند، آیا حذف یا اثر غیرقابل‌بازگشت وجود دارد، و آیا نسخه قبلی حفظ می‌شود. سپس فقط یک سؤال ساده مانند «اجرا شود؟» مطرح می‌شود.
- همان تأیید، تمام عملیات غیرتخریبی لازم برای پایان همان هدف را پوشش می‌دهد؛ از جمله ساخت و Upload فایل‌های جانشین، Folder، Current Master، PPS، `CURRENT_SET`، Workspace Manifest، Receipt، Validation، Inventory، Bundle، ثبت شناسه‌های Provider، Read-back، Retry و بازسازی Artifactهای مشتق.
- جزئیات فنی مانند Mutation Plan، Hash، CAS، Expected Parent، Revision، Resource ID، Duplicate Detection و Read-back کنترل داخلی ماشین هستند. سیستم باید آن‌ها را خودکار بررسی و در Audit ثبت کند؛ اما نباید از Owner بخواهد صحت آن‌ها را تأیید کند.
- تغییر تعداد فایل‌های مشتق، اختصاص Resource ID توسط Provider، تغییر Hash ناشی از bind شدن همان IDها، شماره‌گذاری Successor و Retry فنی، تا وقتی هدف و اثر تجاری تغییر نکرده‌اند، Approval جدید نمی‌خواهند.
- Hash/CAS/Read-back failure مجوز ادامه نیست؛ سیستم باید در محدوده امن Retry یا Rebuild کند و در غیر این صورت متوقف شود و علت را به زبان ساده گزارش دهد. کاربر نباید برای پذیرش یک ناسازگاری فنی سؤال شود.
- تصمیم انسانی تازه فقط زمانی لازم است که اثر تجاری یا دامنه کار عوض شود، حذف/Trash/Overwrite تاریخی یا عمل غیرقابل‌بازگشت جدید مطرح شود، Write به Production خارج از Summary قبلی اضافه شود، یا ابهام واقعی کسب‌وکاری وجود داشته باشد. سؤال جدید نیز باید غیرتخصصی، مشخص و گزینه‌محور باشد.
- عملیات تخریبی خارج از تأیید عمومی باقی می‌مانند و فقط با یک توضیح ساده درباره اثر و امکان بازیابی، یک تأیید انسانی مستقل می‌خواهند.

بنابراین `CONFIRM_DRIVE_BATCH_MUTATION`، `mutation_plan_sha256` و Gateهای per-file ممکن است در Audit داخلی نگهداری شوند، اما **رابط تأیید کاربر نیستند** و ارائه یا تکرار آن‌ها برای Owner ممنوع است.

### GOV-008 — محاسبات قطعی

برای قیمت، درصد تخفیف و تبدیل واحد پول از محاسبات Decimal/Integer قطعی استفاده شود؛ محاسبات Float باینری برای مقادیر پولی ممنوع است.

### GOV-009 — کنترل مجموع‌ها و Reconciliation

هر Run باید علاوه بر کنترل رکوردی، Control Totalهای مستقل و قابل Reconcile ثبت کند، حداقل:

- تعداد کل ردیف‌های Master در شروع؛
- تعداد کل ردیف‌های Output؛
- تعداد Priceهای معتبر/صفر/خالی/نامعتبر؛
- مجموع `Price` قبل و بعد؛
- تعداد `visible` و `hidden`؛
- تعداد Sale Price فعال؛
- تعداد `change_status`ها؛
- تعداد `availability_status`ها؛
- تعداد رکوردها بر اساس `max_issue_severity`؛
- تعداد Issue Eventها بر اساس Technical Code؛
- Hash فایل ورودی، Canonical Source IR، Master Snapshot، Product Pricing State Snapshot، Rulebook و Output.

Control Totalهای محورهای مستقل نباید به‌اشتباه با هم جمع شوند. برای مثال یک رکورد می‌تواند هم `UPDATED` و هم `ACTIVATED` و هم دارای `WARNING` باشد.

اختلاف غیرمنتظره در Control Total باید در Audit توضیح داده شود و اگر Reconciliation هویت/تعداد/مقادیر را نامطمئن کند، Blocker است.

### GOV-010 — عدم استفاده مجدد از شناسه شناخته‌شده

برای `Column1`، شناسه‌ای که در Current Master یا نسخه‌های آرشیوی شناخته‌شده استفاده شده است نباید عمداً دوباره استفاده شود.

Allocation شناسه جدید باید مطابق الگوریتم قطعی `NEW-005` انجام شود. اگر پوشش Archive کامل نباشد، محدودیت تضمین در Audit ثبت می‌شود؛ اگر مجموعه شناسه‌های شناخته‌شده قابل خواندن/اعتبارسنجی نباشد و Safe Generation قابل اثبات نباشد، تولید شناسه Block می‌شود.

---

## 3. محدوده سیستم

### 3.1 داخل محدوده

سیستم برای این کارها طراحی می‌شود:

- دریافت PDF فهرست قیمت جدید NESTECH/تأمین‌کننده.
- بررسی تاریخ، Hash، کامل‌بودن، Chronology و قابلیت استخراج منبع.
- تولید Canonical Source Intermediate Representation.
- استخراج کد محصول، رنگ، قیمت و ساختارهای ترکیبی.
- تطبیق با Master Dataset فروشگاه.
- محاسبه قیمت مستقیم و ترکیبی.
- تبدیل ریال به تومان.
- مدیریت غیرفعال/فعال‌شدن محصولات تحت مدیریت تأمین‌کننده.
- مدیریت تخفیف از طریق Discount Preflight.
- مدیریت Manual Override و Excluded Products.
- نگهداری Product Pricing State نسخه‌دار.
- تشخیص محصولات جدید و اخذ تصمیم کاربر.
- تولید Full Dataset برای WP All Import.
- تولید گزارش Audit و Run Manifest.
- Versioning، Archive، Rollback، Recovery و Promotion.
- Regression/Acceptance Replay با Artifactهای Canonical.

### 3.2 خارج از محدوده پیش‌فرض

سیستم به‌صورت خودکار نباید:

- نام محصول را بازنویسی کند.
- توضیحات، Attributeها، Taxonomy، ACF یا محتوای بازاریابی را تغییر دهد.
- SKU را از روی نام حدس بزند.
- محصول جدید را بدون تأیید بسازد.
- Prime/White Prime را صرفاً به‌دلیل حضور در PDF وارد سایت کند.
- Margin، Markup، مالیات یا ضریب فروش اضافه کند مگر Rule جدید صریحاً تصویب شود.
- موجودی انبار را تغییر دهد.
- حذف فیزیکی محصول یا ردیف انجام دهد.
- State تجاری را صرفاً از Price جاری حدس بزند.
- Historical Rollback Payload را برای عبور از Validationهای جاری Silent Mutate کند.

---

## 4. اجزای داده و منابع مرجع

### SRC-001 — Current Master

منبع رسمی داده فروشگاهی در شروع هر Run فایل/Google Sheet پایدار با نام مفهومی:

`SwitchLand_Current_Master`

است. Current Master فقط نماینده آخرین وضعیت داده‌ای موفق و تأییدشده است و به‌تنهایی برای بازسازی Scope یا Manual Override کافی نیست.

### SRC-002 — Google Sheet موجود

Master فعلی از Google Sheet زیر منشأ گرفته است:

`https://docs.google.com/spreadsheets/d/1vV2oVN4rDt6zzTNUDZToumoXgo4nePH7rRfUPRH2JBQ/edit?usp=sharing`

نام مشاهده‌شده: `Import - Sheet1`.

### SRC-003 — Successful Import Fixture

فایل CSV موفق تاریخی و Log موفق WP All Import، Fixture مرجع برای Regression Test هستند. Fixture فعلی دارای:

- 95 Header واقعی
- 826 Data Row در نمونه موفق
- Encoding: `UTF-8 BOM`
- Delimiter: `;`

