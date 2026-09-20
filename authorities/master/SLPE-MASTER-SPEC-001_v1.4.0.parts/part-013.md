- Source baseline/chronology metadata update
- مسیر عادی: `promotion_status=PROMOTED` و `run_status=IMPORT_SUCCESS`.
- مسیر استثنایی Partial: `promotion_status=PROMOTED_WITH_OPEN_RECONCILIATION` و `run_status=IMPORT_PARTIAL`؛ Queue باز حفظ می‌شود.

اگر Gate Fail شود:

- Current Master Promote نمی‌شود.
- `promotion_status=BLOCKED`
- Recovery/Corrective flow در صورت Production mutation فعال می‌شود.

### WF-019 — Authority Pinning

این Rule از نظر ترتیب اجرا **بعد از WF-002 و قبل از WF-003** اجرا می‌شود.

در شروع Run:

- Current Master
- Product Pricing State
- Rulebook
- Schema
- Part Number Reference
- Import Contract

Snapshot/Version/Hash لازم Pin می‌شوند.

حداقل pinها مطابق `RUN-012` ثبت می‌شوند.

### WF-020 — External Authority Final Check

این Rule قبل از `WF-014` اجرا می‌شود.

Active Authority Versionها با Pinهای Run مقایسه می‌شوند.

اگر Authority خارج Run تغییر کرده باشد:

- مطابق `RUN-012` Full Reprocess/Revalidation؛
- یا Block/Restart

انجام می‌شود.

Silent mixed-version execution ممنوع است.

### WF-021 — Run Close and Archive

پس از terminal state:

- Manifest Close Timestamp ثبت می‌شود.
- Artifact Hashها نهایی می‌شوند.
- Closed Manifest مطابق `DEF-004` Immutable می‌شود.
- هر Correction بعدی با Run/Event جدید است.

---

## 21. رفتار Import Failure، Partial و Promotion Evidence

### IMP-001 — Import Failed

اگر WP All Import کامل Fail شود و Production mutation قابل‌توجهی انجام نشده باشد:

- `run_status=IMPORT_FAILED`
- Current Master تغییر نمی‌کند.
- Product Pricing State تغییر نمی‌کند.
- Retry یک Run/Version جدید است.

اگر Failure بعد از mutation نامعلوم رخ داده و وضعیت Production قابل اثبات نیست، سیستم باید به‌جای فرض «no change» Recovery را به‌صورت conservative با `PRODUCTION_RECONCILIATION_REQUIRED` علامت بزند.

### IMP-002 — Import Partial

Partial Promotion عمومی مجاز نیست. تنها استثناء، مسیر exact و run-scoped تعریف‌شده در `GOV-007A` است.

در `IMPORT_PARTIAL`:

- Run terminal است.
- بدون Closure کامل `GOV-007A`، Current Master و Product Pricing State committed pointer دست‌نخورده می‌مانند.
- `PRODUCTION_RECONCILIATION_REQUIRED` فعال می‌شود.
- بدون استثنای معتبر، Normal Price Update Promotion جدید تا Reconciliation ممنوع است. با استثنای معتبر، فقط exact carried-forward queue باز می‌ماند و unrelated valid work می‌تواند ادامه یابد.
- Run/Version اصلاحی جدید باید ساخته شود.

Production ممکن است واقعاً Subsetی از changes را دریافت کرده باشد. در مسیر استثنایی، Current Master نماینده Dataset مصوب است و Manifest باید صریحاً اعلام کند exact Subjectهای Queue هنوز در Production اثبات/اصلاح نشده‌اند؛ این وضعیت نباید به‌عنوان تطابق کامل Production نمایش داده شود.

### IMP-003 — اصلاح پس از Success

اگر بعد از Import Success/Promotion اشتباهی کشف شود:

- Version موفق قبلی بازنویسی نمی‌شود.
- Corrective Run جدید ساخته می‌شود.
- `run_type=CORRECTIVE`
- Provenance به Run موفق قبلی ثبت می‌شود.

### IMP-004 — Imported Artifact Binding

Promotion باید Evidence داشته باشد که Artifact Import‌شده همان Final Output Approved Run است.

Manifest باید حداقل داشته باشد:

- `final_csv_hash`
- `imported_artifact_hash` در صورت technically available
- `imported_filename`
- `imported_run_id`
- `imported_run_version`
- `import_confirmation_method`
- `import_assurance_level`

Assurance levels:

- `HASH_VERIFIED`
- `USER_ATTESTED`
- `NONE`

#### HASH_VERIFIED

وقتی Hash Artifactی که واقعاً Import شده independently available است:

`imported_artifact_hash == final_csv_hash`

باید برقرار باشد.

Mismatch:

`IMPORTED_ARTIFACT_MISMATCH` -> Promotion Blocked.

#### USER_ATTESTED

اگر WP All Import یا محیط در دسترس Hash فایل Import‌شده را مستقل ارائه نکند، کاربر باید به‌صورت صریح تأیید کند که این موارد exact بوده‌اند:

- `run_id`
- `run_version`
- `imported_filename`

و Import موفق همان Final Artifact بوده است.

Manifest:

- `import_assurance_level=USER_ATTESTED`
- `imported_artifact_hash=null` اگر قابل دسترسی نیست.

در این حالت سیستم نباید ادعای Cryptographic Proof کند.

#### NONE

اگر نه Hash verification و نه explicit Run-bound user attestation وجود داشته باشد:

`IMPORT_ASSURANCE_INSUFFICIENT` -> Promotion Blocked.

### IMP-005 — Promotion-time Parent Check

Artifact Binding به‌تنهایی کافی نیست.

قبل از Commit:

- `master_fingerprint_current`
- `current_master_version`

باید با Expected Parent ثبت‌شده Run برابر باشند.

Mismatch:

`STALE_RUN_PROMOTION_CONFLICT` -> Promotion Blocked.

### IMP-006 — Production Reconciliation Guard

وقتی:

- Import Partial؛
- imported artifact mismatch پس از mutation؛
- promotion blocked بعد از confirmed Production change؛
- یا failure با mutation uncertainty

رخ دهد:

`PRODUCTION_RECONCILIATION_REQUIRED`

فعال می‌شود.

تا Resolution:

- Normal Run MAY برای diagnosis آماده شود؛
- اما هیچ Normal Run جدیدی نباید Promote شود.
- Recovery باید با `CORRECTIVE` یا `ROLLBACK` Run و Evidence وضعیت Production انجام شود.

Resolution باید در Manifest Run اصلاحی ثبت شود.

### IMP-007 — No Partial Commit

هیچ Subset از Changes، حتی اگر قابل شناسایی باشد، نباید به‌عنوان Current Master جدید Promote شود.

هدف Recovery بازگرداندن Production و committed Current State به یک Version کامل و شناخته‌شده است.

---

## 22. NO_CHANGES

### NCH-001 — Run معتبر حتی بدون تغییر

اگر Price List بررسی شود ولی هیچ Data Row یا Product Pricing State mutation لازم نباشد:

`run_result=NO_CHANGES`

Run همچنان باید:

- Run ID/Version
- Archive
- Canonical Source IR
- Audit Report
- Manifest
- Validation Result

داشته باشد.

### NCH-002 — Approval

اگر:

- `technical_import_ready=true`
- `run_result=NO_CHANGES`

باشد، Final Summary ارائه می‌شود.

پس از Final User Approval:

`run_status=APPROVED_NO_IMPORT_REQUIRED`

WP All Import لازم نیست.

### NCH-003 — Baseline Metadata

در NO_CHANGES تأییدشده:

- `source_file_hash` رسمی Update می‌شود.
- اگر Source Date معتبر و Chronology قابل Re-anchor/Verified باشد، `last_processed_price_list_date` Update می‌شود.
- اگر Source Date Unverified باشد، Date baseline حدسی Update نمی‌شود و `source_chronology_state=SOURCE_CHRONOLOGY_UNVERIFIED` می‌شود.
- اگر Chronology مطابق `SRC-112` Re-anchor شود، State/Date طبق آن Rule Update می‌شوند.
- Current Master data بدون تغییر می‌ماند.
- Product Pricing State committed data بدون تغییر می‌ماند.
- `promotion_status=NOT_REQUIRED`.

### NCH-004 — No-Changes Integrity

وجود Warning مانع NO_CHANGES Approval نیست، ولی:

- هر Blocker فعال؛
- unresolved Source Chronology Gate؛
- Reconciliation Required؛
- یا Authority mismatch

مانع `APPROVED_NO_IMPORT_REQUIRED` است.

---

## 23. Rollback

### RLB-001 — Rollback یک Run جدید است

Rollback نباید Version قبلی را Restore-in-place کند.

مثال:

`v020 = ROLLBACK_FROM_v016`

Rollback:

`run_type=ROLLBACK`

است و Version جدید مصرف می‌کند.

### RLB-002 — Rollback Source Candidate

هر Historical Version که سابقه:

`IMPORT_SUCCESS + promotion_status=PROMOTED`

دارد، یک:

`rollback_source_candidate`

است.

Candidate بودن به معنی executable/import-ready بودن Rollback نیست.

### RLB-003 — داده دقیق تاریخی

Rollback Payload باید Data values همان Historical Successful Version را Restore کند.

نباید با Rulebook جدید:

- Price
- Sale Price
- visibility
- BOM-derived output

دوباره محاسبه یا Silent اصلاح شود.

Rollback target Dataset باید از Archived successful artifact/data reconstruct شود و provenance آن ثبت شود.

### RLB-004 — Current Non-Mutating Safety Gate

قبل از `IMPORT_READY` شدن Rollback، Validationهای فعلی فقط به‌صورت **Non-Mutating** اجرا می‌شوند.

حداقل Gates:

- target artifact readable/parseable؛
- current Import Contract compatibility؛
- Schema compatibility؛
- 95-column/active schema requirements؛
- Duplicate/Blank `Column1` validation؛
- Duplicate/Blank SKU validation؛
- row structural integrity؛
- Product Pricing State availability/compatibility؛
- Promotion/reconciliation safety؛
- Current Master parent/concurrency requirements برای Rollback Run.

Validation جاری حق ندارد Historical Price/Sale/visibility را Auto-Fix کند.
