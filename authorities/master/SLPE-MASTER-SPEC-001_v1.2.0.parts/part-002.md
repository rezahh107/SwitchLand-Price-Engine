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

تعداد ردیف 826 یک **Baseline تاریخی** است، نه Contract دائمی. Contract اصلی 95 ستون و ترتیب/نام دقیق Headerها است.

### SRC-004 — Supplier Price List

PDF جاری تأمین‌کننده مرجع قیمت و عرضه جاری محصولات `SUPPLIER_MANAGED` است، فقط در حدود Evidence موجود در Source و با رعایت Source Completeness/Chronology Rules.

هیچ Deactivation مبتنی بر `NOT_FOUND` بدون `source_completeness_status=COMPLETE` مجاز نیست.

### SRC-005 — Part Number Reference

سند Part Number NESTECH فقط مرجع ساختار و معنی کدها برای Matching/Validation است. این سند:

- مرجع قیمت نیست.
- مرجع قطعی موجودبودن در Price List جاری نیست.
- حق Auto-Fill روی ردیف موجود Master ندارد.
- باید در هر Run با Version/Hash دقیق Pin شود.

### SRC-006 — WP All Import Mapping/Settings

تنظیمات WP All Import بخشی از Import Contract هستند. نگاشت‌های شناخته‌شده شامل:

- `Column1` به‌عنوان Unique Identifier
- `Column2` به‌عنوان WooCommerce SKU
- `Column27` برای نوع محصول
- `Column8` برای گروه‌بندی Variation
- `visibility` برای Catalog Visibility

قبل از بهره‌برداری Production، Mapping باید در Regression/Acceptance Test تثبیت، Versioned و Snapshot شود.

هر Run باید حداقل `import_contract_version` و `import_contract_hash` مورد استفاده را ثبت کند. تغییر Import Contract در میانه Run بدون Revalidation ممنوع است.

### SRC-007 — Product Pricing State Authority

State تجاری پایدار Scope، Manual Override و Excluded Pricing در Artifact نسخه‌دار:

`Product_Pricing_State.json`

نگهداری می‌شود.

این Artifact، Authority وضعیت تجاری است؛ Current Master Authority مقادیر Data Row است. تعارض بین این دو باید مطابق `PPS-005` Fail-Closed شود.

### SRC-008 — Canonical Source Representation

ورودی Canonical Rule Engine از Supplier Source:

`Normalized_Source_Catalog.json`

است که مطابق `SRC-IR-*` ساخته و Hash می‌شود. PDF همچنان Evidence اصلی است، اما Rule Engine برای Replay باید از Representation Canonical و Versioned استفاده کند.

### SRC-009 — Authority Order by Decision Domain

Authority به‌صورت Domain-specific است و یک فهرست مطلق ساده نیست:

- Current Master: مقادیر فعلی Dataset و هویت ردیف.
- Product Pricing State: Scope و Manual/Excluded pricing lifecycle.
- Supplier Price List + Canonical Source IR: قیمت و Availability جاری `SUPPLIER_MANAGED`.
- Part Number Reference: ساختار/معنی کد برای Matching/Validation، نه Price/Availability.
- Permanent Mapping/Rulebook: Rules و Mappingهای صریح.
- User Decisions: تصمیم‌های Run-scoped یا Permanent مطابق Scope ثبت‌شده.
- Historical Successful Versions: Authority فقط برای Historical Audit/Rollback source candidate.
- Import Contract: Authority تفسیر Output توسط WP All Import.
