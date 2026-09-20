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

اگر دو Authority معتبر در یک Domain تعارض داشته باشند و Rule صریح حل تعارض وجود نداشته باشد، سیستم نباید Priority عمومی اختراع کند و باید Conflict را Fail-Closed کند.

---

## 5. قرارداد Master Dataset

### MST-001 — Schema ثابت

خروجی عادی باید دقیقاً 95 ستون با نام و ترتیب Header مطابق Fixture موفق و `schema_version` فعال داشته باشد.

هر یک از موارد زیر موجب `MASTER_SCHEMA_MISMATCH` می‌شود:

- Header اضافه
- Header حذف‌شده
- تغییر نام Header
- تغییر ترتیب Header

تا زمان تأیید کاربر و ایجاد `schema_version` جدید، `technical_import_ready=false`.

### MST-002 — Unique Identifier

`Column1` شناسه داخلی یکتا است.

- Blank -> `MISSING_UNIQUE_IDENTIFIER` -> Blocker
- Duplicate -> `DUPLICATE_COLUMN1` -> Blocker
- پس از تخصیص، Immutable است مگر اصلاح صریح و تأییدشده.
- Allocation جدید فقط مطابق `NEW-005` مجاز است.

### MST-003 — SKU

`Column2` SKU است.

- Blank -> `MISSING_SKU` -> Blocker
- Duplicate -> `DUPLICATE_SKU` -> Blocker
- مقایسه یکتایی Case-Insensitive است.
- Case ذخیره‌شده حفظ می‌شود.

### MST-004 — Normalization حداقلی شناسه‌ها

برای بررسی یکتایی و Match فقط Trim ابتدا/انتها مجاز است. سیستم نباید Hyphen، Digit، Internal Space یا Case ذخیره‌شده را بازنویسی کند.

### MST-005 — ترتیب ردیف‌ها

ردیف‌های موجود باید در ترتیب فعلی حفظ شوند. Sort/Reorder ممنوع است. محصول جدید تأییدشده فقط در انتهای Dataset Append می‌شود.

### MST-006 — خروجی Full Dataset

خروجی عادی همیشه Full Dataset است، نه Delta.

### MST-007 — Canonical CSV Serialization

فایل CSV نهایی باید با قرارداد Canonical زیر Serialize شود:

- Encoding: `UTF-8` همراه با `UTF-8 BOM`.
- Delimiter: `;`
- Quote Character: `"`
- Record Line Ending: `LF` (`\n`).
- دقیقاً 95 ستون با Header و ترتیب `schema_version` فعال.
- Blank cell به‌صورت field خالی Serialize می‌شود و نباید به literalهایی مانند `null` یا `NULL` تبدیل شود.
- مقدار سلول خارج از Rules صریح نباید Trim، Unicode-normalize، Reformat یا بازنویسی شود.
- اگر field شامل `;`، `"`, `\r`, `\n` یا Leading/Trailing whitespace باشد، باید داخل Double Quote قرار گیرد.
- Double Quote داخل field باید به‌صورت `""` Escape شود.
- Embedded line break مجاز داخل field باید بدون تغییر محتوایی و داخل quoted field حفظ شود.
- Money fields در CSV بدون Separator نمایشی ذخیره می‌شوند.
- Integer Toman به‌صورت digits ساده Serialize می‌شود.
- Serializer با داده Semantic یکسان باید bytes یکسان تولید کند.

Canonical CSV Hash روی bytes نهایی، شامل BOM، محاسبه می‌شود.

### MST-008 — Price Blank

`Price = blank` با `Price = 0` برابر نیست.

اگر Price خالی باشد:

- ابتدا سیستم باید تلاش کند از Rule معتبر قیمت قطعی را بازیابی کند.
- اگر قابل بازیابی باشد -> `AUTO_FIX` + Audit.
- اگر قابل بازیابی نباشد -> `MISSING_PRICE` / `UNRESOLVED` -> Blocker.

### MST-009 — Price نامعتبر در Master

Final `Price` باید یک Integer Toman با دامنه `Price >= 0` باشد.

اگر Price در Master:

- غیرعددی؛
- منفی؛
- یا خارج از representation مجاز باشد،

ابتدا سیستم باید فقط در صورت Match/Source/Formula قطعی امکان بازسازی را بررسی کند:

- بازسازی قطعی -> `INVALID_MASTER_PRICE_AUTO_REPAIRED` و Run ادامه دارد.
- عدم امکان بازسازی قطعی -> `UNRESOLVED_INVALID_MASTER_PRICE` -> Blocker.

اگر Final Price هنوز UNRESOLVED باشد، `visibility` نباید بر اساس حدس Auto-Fix شود.

### MST-010 — Canonical Master Fingerprint

Master Fingerprint باید از یک representation قطعی محاسبه شود:

```json
{
  "headers": ["...ordered exact header strings..."],
  "rows": [
    ["...exact cell string values in row order..."]
  ]
}
```

قواعد Canonicalization:

- Header sequence و Row sequence دقیقاً حفظ می‌شوند.
- هر row باید دقیقاً 95 cell داشته باشد.
- Cellها به‌عنوان string value دقیق Data Source نمایش داده می‌شوند؛ Blank به `""` تبدیل می‌شود.
- Trim، Case Fold، Unicode Normalization یا numeric presentation normalization برای Fingerprint ممنوع است.
- JSON باید UTF-8 بدون BOM و بدون whitespace غیرلازم به‌صورت deterministic serialize شود.
- Hash = `SHA-256(canonical_json_bytes)`.
- Spreadsheet formatting، color، width، formula-rendering metadata و UI metadata در Fingerprint دخالت ندارند.

Fingerprint شروع Run، Finalization و Promotion باید با همین الگوریتم ساخته شوند.

---

## 6. مدل نسخه‌گذاری، Run و Archive

### RUN-001 — Run مستقل

هر اجرای پردازش باید `run_id` یکتا داشته باشد.

Run دارای ابعاد مستقل است و نباید Type، Workflow Status و Result در یک Enum واحد مخلوط شوند.

#### `run_type`

مقادیر مجاز:

- `NORMAL`
- `RERUN_SAME_SOURCE`
- `ROLLBACK`
- `CORRECTIVE`

#### `run_status`

مقادیر مجاز:

- `DRAFT`
- `VALIDATED`
- `AWAITING_APPROVAL`
- `IMPORT_READY`
- `IMPORT_SUCCESS`
- `IMPORT_FAILED`
- `IMPORT_PARTIAL`
- `REJECTED`
- `SUPERSEDED`
- `APPROVED_NO_IMPORT_REQUIRED`

#### `run_result`

پس از تکمیل پردازش داده، یکی از:

- `CHANGES`
- `NO_CHANGES`

تا قبل از تعیین نتیجه می‌تواند unset باشد.

