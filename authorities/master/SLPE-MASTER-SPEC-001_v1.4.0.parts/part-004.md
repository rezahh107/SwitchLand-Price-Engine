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

Transitionهای مستقیم مانند `DRAFT -> IMPORT_SUCCESS` یا `IMPORT_PARTIAL -> IMPORT_SUCCESS` ممنوع‌اند.

`VALIDATED` به معنی پایان اجرای Validation است، نه الزاماً بدون Blocker بودن. فقط اگر `technical_import_ready=true` باشد Run می‌تواند به `AWAITING_APPROVAL` برود.

`IMPORT_READY` فقط بعد از Final User Approval مجاز است. قبل از آن readiness فنی در Boolean مستقل `technical_import_ready` ثبت می‌شود.

### RUN-006 — Master Concurrency Guard

در شروع Run:

- Current Master Snapshot گرفته می‌شود.
- `master_fingerprint_start` مطابق `MST-010` ثبت می‌شود.
- Expected Parent Master Version ثبت می‌شود.

Master باید حداقل در دو Gate دوباره بررسی شود:

1. پیش از Final Summary/Approval؛
2. بلافاصله پیش از Promotion.

اگر در Gate اول تغییر کرده باشد:

`MASTER_CHANGED_DURING_RUN` -> Blocker -> Resync/Reprocess.

اگر پس از Approval/Import و پیش از Promotion، Current Master دیگر همان Expected Parent نباشد:

`STALE_RUN_PROMOTION_CONFLICT` -> Promotion Blocked.

در این وضعیت سیستم نباید Current Master را با Run قدیمی overwrite کند و باید وارد Reconciliation/Corrective flow شود.

### RUN-007 — Atomic Promotion

برای Run دارای Changes، Current Master فقط پس از:

- `import_outcome=SUCCESS`، یا `import_outcome=PARTIAL` همراه با Closure کامل `GOV-007A`؛
- Artifact Binding معتبر مطابق `IMP-004`;
- Promotion-time Concurrency Guard;
- تمام Promotion Gateهای فعال

Promote می‌شود.

Promotion باید Current Master، Product Pricing State و Source control metadata را به‌عنوان یک Commit منطقی هماهنگ کند.

برای `NO_CHANGES` پس از Validation کامل و Final Approval:

- `run_status=APPROVED_NO_IMPORT_REQUIRED`
- Current Master data تغییر نمی‌کند.
- Product Pricing State data تغییر نمی‌کند.
- History/Manifest و Source baseline metadata طبق `NCH-*` به‌روزرسانی می‌شوند.

### RUN-008 — Promotion Status

Manifest باید وضعیت Promotion را مستقل ثبت کند:

- `NOT_REQUIRED`
- `PENDING`
- `PROMOTED`
- `BLOCKED`

برای Run دارای Changes:

- پس از Import Success و قبل از Commit: `PENDING`
- پس از Commit کامل: `PROMOTED`
- اگر Artifact/Concurrency/Reconciliation Gate Fail شود: `BLOCKED`

`IMPORT_SUCCESS` به‌تنهایی اثبات نمی‌کند که Current Master Promote شده است؛ Audit باید `promotion_status` را نیز بررسی کند.

### RUN-009 — Terminal Behavior

این Run Statusها برای همان Run Terminal هستند:

- `REJECTED`
- `SUPERSEDED`
- `IMPORT_FAILED`
- `IMPORT_PARTIAL`
- `APPROVED_NO_IMPORT_REQUIRED`

`IMPORT_SUCCESS` پس از ثبت نتیجه Import immutable است؛ Promotion نتیجه جداگانه دارد. اگر Promotion Block شود، همان Run بازنویسی نمی‌شود و Recovery با Run جدید انجام می‌شود.

### RUN-010 — Run Type Semantics

- `NORMAL`: Price List جدید در مسیر عادی.
- `RERUN_SAME_SOURCE`: Hash Source تکراری است و کاربر Rerun را صریحاً تأیید کرده است.
- `ROLLBACK`: بازگردانی Dataset/State از Historical `IMPORT_SUCCESS` candidate مطابق `RLB-*`.
- `CORRECTIVE`: اصلاح وضعیت پس از Error، Partial Import، Promotion Conflict یا خطای کشف‌شده پس از Success.

`run_reason` همچنان برای توضیح دقیق‌تر الزامی است و جایگزین `run_type` نیست.

### RUN-011 — Technical Readiness

`technical_import_ready=true` فقط زمانی است که:

- هیچ Blocker فعال وجود ندارد؛
- Schema/Identity/Source/Matching/Pricing/Discount/Trace/Reconciliation Validationها Pass شده‌اند؛
- Candidate Output و Proposed State deterministic هستند.

این Boolean هیچ Final Approval تجاری را جایگزین نمی‌کند.

### RUN-012 — External Authority Version Pinning

در شروع Run حداقل این Versionها Pin می‌شوند:

- `rulebook_version_start`
- `schema_version_start`
- `part_number_reference_version_start`
- `product_pricing_state_version_start`
- `import_contract_version_start`

پیش از Finalization، Active Versionهای فعلی دوباره بررسی می‌شوند.

اگر هر Authority خارج از Run تغییر کرده باشد:

`EXTERNAL_AUTHORITY_CHANGED_DURING_RUN`

ثبت می‌شود و ادامه Silent ممنوع است.

رفتار:

- Rulebook یا Part Number Reference جدید اگر Approved/Active باشد -> Full Dataset Reprocess از Snapshot/IR اصلی و Pin نسخه جدید.
- Schema change -> فقط با Schema Migration/Approval صریح در همان Run قابل ادامه است؛ در غیر این صورت Block.
- Product Pricing State change ناشی از Run دیگری -> Master/State concurrency conflict و Reprocess/Restart.
- Import Contract change -> Output/Import validation باید از نو انجام شود؛ Approval قبلی معتبر نیست.

Final Manifest باید Version دقیق Authorityهای واقعاً استفاده‌شده را ثبت کند و Final Output نباید از Versionهای مخلوط ساخته شده باشد.

---

## 7. دریافت و اعتبارسنجی Price List

### SRC-101 — Hash

برای هر فایل Source باید `SHA-256` ثبت شود.

### SRC-102 — تاریخ منبع

سیستم باید تاریخ Supplier Price List را فقط از Evidence خود Source یا Rule صریح معتبر استخراج کند و:

- متن اصلی تاریخ را حفظ کند.
- Calendar را مشخص کند: Jalali/Gregorian.
- برای مقایسه داخلی Normalized Date بسازد.
- Evidence/Location تاریخ را در Source Trace ثبت کند.

Upload Timestamp، File Creation Time یا زمان دریافت فایل نباید Supplier Price List Date فرض شود مگر Rule آینده صریحاً آن را مجاز کند.

### SRC-103 — Source قدیمی‌تر

اگر:

- `source_chronology_state=VERIFIED`
- تاریخ Source معتبر و قابل مقایسه باشد
- و Source Date از `last_processed_price_list_date` رسمی قدیمی‌تر باشد

آنگاه:

`SOURCE_PRICE_LIST_OLDER_THAN_BASELINE` -> پردازش قیمت متوقف می‌شود.

### SRC-104 — تاریخ یکسان، محتوای متفاوت

اگر Chronology Verified باشد و تاریخ Source با Baseline برابر ولی Hash متفاوت باشد:

- Source جدید قابل پذیرش است.
- به‌عنوان Revision متفاوت همان تاریخ ثبت می‌شود.
- Hash جدید در Audit حفظ می‌شود.
- رفتار Pricing از محتوای Source جدید تبعیت می‌کند.

### SRC-105 — Source Date Unverified

اگر تاریخ یافت نشود یا قابل تأیید نباشد:

`SOURCE_DATE_UNVERIFIED` -> Warning، نه Blocker به‌خودی‌خود.

Run می‌تواند در صورت نبود Blocker دیگر ادامه یابد.

اگر چنین Sourceای با:

- `IMPORT_SUCCESS + promotion_status=PROMOTED`
- یا `APPROVED_NO_IMPORT_REQUIRED`

رسمی شود:

- `source_file_hash` رسمی به Hash جدید Update می‌شود.
