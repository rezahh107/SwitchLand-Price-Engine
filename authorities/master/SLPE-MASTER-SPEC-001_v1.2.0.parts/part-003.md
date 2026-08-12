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

`NO_CHANGES` یعنی هیچ Data Row و هیچ Product Pricing State committed mutation در آن Run لازم نیست؛ Metadata/History update به‌تنهایی آن را به `CHANGES` تبدیل نمی‌کند.

### RUN-002 — Version مستقل

هر Run یک Version جدید مصرف می‌کند، حتی اگر:

- Reject شود.
- Supersede شود.
- Import Failed شود.
- Import Partial شود.
- `NO_CHANGES` باشد.
- Rerun همان Source باشد.
- Rollback/Corrective باشد.

Version Number هرگز Reuse نمی‌شود.

### RUN-003 — نام‌گذاری

نمونه:

`SwitchLand_Price_Master_2026-08-11_v012`

پوشه Run:

`v012_2026-08-11_<run_id>/`

### RUN-004 — پوشه Archive

هر Run پوشه مستقل و Immutable پس از Close دارد.

حداقل محتوا:

- `Source Price List.pdf`
- `Normalized_Source_Catalog.json`
- `Master Snapshot`
- `Product_Pricing_State_Start.json`
- `Product_Pricing_State_Proposed.json` در صورت وجود State Transition
- `Output Full Dataset.csv` یا Draft معادل
- `Audit Report.xlsx`
- `Rulebook Snapshot.json`
- `Run Manifest.json`
- `Import_Contract_Snapshot.json`
- `WP All Import Log` در صورت وجود

Schema Snapshot و Part Number Reference Snapshot باید یا داخل Run Archive وجود داشته باشند یا Manifest به Artifact immutable قابل بازیابی با Version + SHA-256 اشاره کند.

### RUN-005 — Legal Run Transitions

مسیر عادی دارای Changes:

`DRAFT -> VALIDATED -> AWAITING_APPROVAL -> IMPORT_READY -> IMPORT_SUCCESS`

مسیر `NO_CHANGES`:

`DRAFT -> VALIDATED -> AWAITING_APPROVAL -> APPROVED_NO_IMPORT_REQUIRED`

مسیرهای Failure:

- `IMPORT_READY -> IMPORT_FAILED`
- `IMPORT_READY -> IMPORT_PARTIAL`

مسیرهای Abandonment:

- `DRAFT|VALIDATED|AWAITING_APPROVAL -> REJECTED`
- `DRAFT|VALIDATED|AWAITING_APPROVAL|IMPORT_READY -> SUPERSEDED`

اگر پس از `IMPORT_READY` هر Input، Decision، Authority Version یا Output bytes تغییر کند، Approval قبلی باطل است؛ Run باید `SUPERSEDED` شود و Run/Version جدید ساخته شود.
