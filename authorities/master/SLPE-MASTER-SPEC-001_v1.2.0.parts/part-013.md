- `run_status=IMPORT_READY`
- `final_csv_hash` Lock می‌شود.

اگر Output bytes یا material User Decision بعد از Approval تغییر کند، Approval باطل و Run مطابق `RUN-005` Supersede می‌شود.

### AUD-004 — Audit Report.xlsx

Workbook پیش‌فرض شامل Sheetهای زیر است:

- `خلاصه`
- `تغییرات`
- `هشدارها`
- `حل‌نشده‌ها`
- `خطاها`

`همه رکوردها` به‌صورت پیش‌فرض ایجاد نمی‌شود مگر کاربر درخواست کند.

### AUD-005 — تغییرات

Sheet `تغییرات` فقط Existing rows با `change_status=UPDATED` و New rows با `ADDED` را دارد.

State-only transitions که row data را تغییر نداده‌اند باید در Summary/State Audit ثبت شوند و نباید به‌اشتباه row UPDATED شمرده شوند.

### AUD-006 — Summary Metadata

حداقل:

- Price List Date و verification state
- Source Chronology State
- `source_file_hash`
- `normalized_source_catalog_hash`
- Master Fingerprint Start/Final Check
- Product Pricing State Version/Hash Start/Proposed
- `rulebook_version`
- `part_number_reference_version`
- `schema_version`
- `import_contract_version`
- Run Timestamp
- `run_id`
- `run_version`
- `run_type`
- `run_status`
- `run_result`
- Final `technical_import_ready`
- Final Approval state
- Control Totals قبل/بعد

### AUD-007 — زبان گزارش

Labelهای کاربر فارسی هستند و Technical Code داخل پرانتز English باقی می‌ماند.

مثال:

`هشدار تغییر قیمت بیش از ۵۰٪ (WARNING_PRICE_CHANGE_GT_50_PERCENT)`

### AUD-008 — Run Manifest Decision Log

هر تصمیم نیازمند تأیید صریح کاربر باید در Manifest ثبت شود، حداقل:

- Decision Topic
- Decision Code/ID
- Timestamp با Timezone
- Selected Outcome
- Scope: Current Run / Permanent Rule
- Effect on Run
- Rulebook Version در صورت Permanent بودن
- Approval Reference

ثبت Decision به‌تنهایی آن را Permanent Rule نمی‌کند.

### AUD-009 — Why Product X?

برای هر Product تغییرکرده یا State-transitioned، Archive باید برای این سؤال پاسخ deterministic داشته باشد:

«چرا Product X در Run Y این Price، Sale Price، visibility، Scope و Availability را گرفت؟»

پاسخ باید از Manifest/Audit/Canonical IR/Rulebook/State Snapshot بدون نیاز به memory مکالمه قابل بازسازی باشد.

---

## 20. Run Workflow استاندارد

### WF-001 — Boot

کاربر در Project پیام `شروع` می‌دهد.

### WF-002 — Intake

سیستم آخرین Price List را درخواست می‌کند و Run Context اولیه را می‌سازد.

در همین مرحله `WF-019 — Authority Pinning` باید قبل از Source/Business processing کامل شود.

### WF-003 — Source Validation

حداقل:

- Source SHA-256
- Duplicate Source Check
- Source Date
- Source Chronology
- Source Completeness State
- Extractability Check Results

Source با Blocker MAY برای diagnosis ادامه پردازش شود، اما Final readiness false می‌ماند.

### WF-004 — Current Master Snapshot

حداقل:

- Read Current Master
- Canonical Master Fingerprint
- Schema Validation
- Unique Identifier Validation
- SKU Validation
- Product Pricing State Validation
- Master/State Consistency Check
- Expected Parent Master Version

### WF-005 — Discount Preflight

برای Runهای مشمول `DSC-002` قبل از Main Pricing اجرا می‌شود.

اگر Source Blocker فعلاً Main Pricing را ناممکن کرده است، Discount Preflight MAY تا رفع همان Source Blocker defer شود تا سؤال غیرضروری از کاربر پرسیده نشود؛ اما قبل از Main Pricing حتماً باید کامل شود.

Discount decisions به Run Decision Log اضافه می‌شوند.

### WF-006 — Source Extraction

Source باید حداقل این موارد را استخراج کند:

- Product Codes
- Component Codes/Roles
- Colors/Variants
- Prices/Inactive Signals
- Family/Series
- Sections/Pages
- raw record location

Extraction result باید برای Canonicalization `WF-007` قابل Audit باشد.

### WF-007 — Normalize

Normalization حداقل شامل:

- Digits
- Price Format/Unit
- Supplier Code Compare Rules
- Color Alias
- Variant/Family/Series fields
- Component Structure

است.

خروجی `WF-006 + WF-007` باید Artifact رسمی:

`Normalized_Source_Catalog.json`

مطابق `SRC-IR-*` باشد.

پس از ساخت Canonical IR، Matching/Pricing همان Run نباید PDF را دوباره reinterpret کند.

### WF-008 — Matching

Matching فقط روی Canonical Source IR و Authorityهای Pin‌شده انجام می‌شود.

اعمال:

- Priority Rules
- Conflict Rules
- Scope requirements
- Color/BOM/PN validation

### WF-009 — Pricing

ترتیب منطقی Pricing stage:

1. Resolve Match/BOM.
2. Resolve Supplier Availability.
3. Resolve Direct/Composite Supplier Price.
4. Apply Exclusion/Manual Override lifecycle Rules.
5. Determine Final Regular Price.
6. Determine Availability status.
7. Apply Discount decisions.
8. Synchronize visibility from valid Final Price.
9. Stage Product Pricing State transitions.

### WF-010 — Validation

حداقل:

- Final Price domain
- Sale Price
- visibility
- Schema
- Unique Identifier/SKU
- Source Trace/Price Provenance
- Scope/State consistency
- Source Completeness/Chronology
- Warning Threshold
- New Product required profile
- Control Totals/Reconciliation
- External Authority final-version check

### WF-011 — Exception Handling

سیستم:

- Auto-Fixهای مجاز را اعمال و Audit می‌کند.
- Warningها را ثبت می‌کند.
- تمام Blockerهای واقعی را مشخص می‌کند.
- سایر independent records را ادامه می‌دهد.

فقط Blockerهایی که واقعاً User Decision لازم دارند باید سؤال ایجاد کنند.

### WF-012 — Reprocess after Rule Change

اگر کاربر در میانه Run Permanent Rule جدیدی تصویب کند و `rulebook_version` Active تغییر کند:

1. Version جدید Pin می‌شود.
2. تمام derived results Rulebook قبلی discard می‌شوند.
3. Full Dataset از:
   - Original Master Snapshot
   - Original Product Pricing State Start Snapshot
   - Canonical Source IR
   - current valid Run-scoped User Decisions
   دوباره پردازش می‌شود.
4. همه Existing + New Candidate records Reprocess می‌شوند.
5. Final Output فقط یک Rulebook Version دارد.

Selective «potentially affected rows» و dependency graph در v1 مجاز نیست.

### WF-013 — Draft Output در صورت Blocker

در صورت وجود Blocker، سیستم MAY یک Draft Full Dataset برای Archive تولید کند، اما:

- Artifact label = `DRAFT_NOT_IMPORT_READY`
- `technical_import_ready=false`
- نباید به‌عنوان فایل Import-ready معرفی شود.
- Draft Hash از `final_csv_hash` متمایز است.

### WF-014 — Final Summary

اگر Blocker فعال وجود ندارد:

1. Candidate Dataset با Canonical CSV Serializer ساخته می‌شود.
2. Proposed Product Pricing State Hash محاسبه می‌شود.
3. Final Control Totals/Reconciliation اجرا می‌شود.
4. Master/Authority concurrency Gate تکرار می‌شود.
5. `technical_import_ready=true`
6. `run_status=AWAITING_APPROVAL`
7. Summary دو‌لایه به کاربر ارائه می‌شود.

### WF-015 — User Approval

پس از Global Approval:

- bytes Candidate CSV Lock می‌شوند.
- `final_csv_hash` ثبت می‌شود.
- Approval به exact `run_id`, `run_version`, `final_csv_hash` متصل می‌شود.
- `run_status=IMPORT_READY`.

اگر Material Input/Decision/Authority یا Output bytes بعد از Approval تغییر کند، Approval معتبر نیست و Run باید Supersede شود.

### WF-016 — WP All Import

کاربر exact Final CSV مربوط به Run را Import می‌کند.

System نباید صرف «وجود یک Import موفق» را معادل Import همین Artifact بداند.

### WF-017 — Import Outcome

Outcome evidence:

- Failure -> `run_status=IMPORT_FAILED`
