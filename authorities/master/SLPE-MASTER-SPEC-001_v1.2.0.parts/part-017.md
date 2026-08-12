Acceptance Suite باید حداقل یک **Synthetic Discriminating Arithmetic Fixture** داشته باشد که صرفاً semantics Rule را آزمون کند و Historical Evidence محسوب نشود.

Fixture الزامی نمونه:

- `fixture_type = SYNTHETIC_ACCEPTANCE_ONLY`
- `component_1_rial = 1,000,001`
- `component_2_rial = 1,000,001`
- Approved Algorithm A: `ceil(2,000,002 / 10) = 200,001 Toman`
- Prohibited Algorithm B: `ceil(1,000,001 / 10) + ceil(1,000,001 / 10) = 200,002 Toman`
- Expected Final Toman = `200,001`

اگر implementation نتیجه `200,002` یا هر نتیجه ناشی از Component-level Ceiling تولید کند، `ACC-007` FAIL است.

این Synthetic Fixture فقط برای اثبات Conformance arithmetic semantics است و MUST NOT به‌عنوان Historical یا Commercial transaction evidence معرفی شود.

### ACC-008 — Incomplete Source Mass-Deactivation Guard

با Source fixture دارای extraction/page failure:

PASS فقط اگر:

- Source state `INCOMPLETE` شود؛
- `UNRESOLVED_SOURCE_INCOMPLETE` ایجاد شود؛
- هیچ Product صرفاً به‌دلیل `NOT_FOUND` Deactivate نشود؛
- `technical_import_ready=false`.

### ACC-009 — Discount and Manual Override Lifecycle

Transition fixtures باید حداقل این حالات را Assert کنند:

- preserved discount valid؛
- preserved Sale Price >= new Regular Price -> Blocker؛
- Deactivation clears Sale Price؛
- Reactivation does not restore old Sale Price؛
- Active Manual Override + changed supplier price -> `MANUAL_OVERRIDE_REVIEW_REQUIRED`؛
- Override + product unavailable -> Deactivate and stage termination؛
- reactivation after terminated override -> Supplier Price, no old override؛
- same-run newly approved override does not self-trigger review.

PASS فقط اگر همه expected state/outputها exact باشند.

### ACC-010 — Warning-Only Technical Readiness

Fixture با Warning و بدون Blocker:

- `technical_import_ready=true`
- `run_status=AWAITING_APPROVAL` قبل از User Approval

باید باشد.

پس از explicit Final Approval:

- `run_status=IMPORT_READY`.

Warning نباید خودکار Block کند.

### ACC-011 — Blocker Prevents Import-Ready

برای هر Fixture با active blocking issue:

- `technical_import_ready=false`
- Run نباید به `AWAITING_APPROVAL` یا `IMPORT_READY` برود.
- Artifact نهایی نباید `IMPORT_READY` label داشته باشد.
- Draft در صورت تولید باید `DRAFT_NOT_IMPORT_READY` باشد.

### ACC-012 — Two-Layer Final Summary

PASS فقط اگر Summary برای هر دسته material:

- توضیح ساده فارسی «چه شد/چرا/اثر چیست» داشته باشد؛
- Technical Rule/Code/Old-New/Trace reference داشته باشد.

### ACC-013 — Manifest Reconciliation

PASS فقط اگر:

- تمام required Artifact Hashها در Manifest قابل resolve باشند؛
- Master/Source/Rulebook/State/Output hashes با Artifact bytes یا canonical representation match کنند؛
- Decision IDs به Decision Log موجود اشاره کنند؛
- Control Totals از Archive artifacts قابل recompute و برابر باشند.

### ACC-014 — Deterministic Source Completeness

حداقل سه Source fixture:

1. fully readable/covered؛
2. positively incomplete؛
3. completeness not deterministically decidable

باید به‌ترتیب exact states:

- `COMPLETE`
- `INCOMPLETE`
- `UNRESOLVED`

بدهند.

اجرای تکراری با extractor_version یکسان باید classification یکسان بدهد.

### ACC-015 — Persistent Product Pricing State

Fixture دو Run متوالی باید اثبات کند:

- Scope/Override state از committed Product Pricing State خوانده می‌شود، نه inference از Price؛
- Draft state transition قبل از Promotion committed نمی‌شود؛
- Import Failed/Partial committed state را تغییر نمی‌دهد؛
- successful Promotion proposed state را commit می‌کند.

### ACC-016 — Unknown-Date Chronology

سناریو:

1. verified dated source committed؛
2. unverified-date source accepted/promoted؛
3. later different dated source arrives.

PASS فقط اگر:

- Step 2 state را `SOURCE_CHRONOLOGY_UNVERIFIED` کند؛
- Step 3 بدون deterministic ordering/user chronology approval `SOURCE_CHRONOLOGY_RESOLUTION_REQUIRED` ایجاد کند؛
- upload timestamp به Supplier Date تبدیل نشود؛
- Re-anchor فقط مطابق `SRC-112` رخ دهد.

### ACC-017 — Rollback Candidate vs Execution Readiness

Historical `IMPORT_SUCCESS` version باید candidate باشد.

Fixture unsafe rollback باید اثبات کند:

- candidate status حفظ می‌شود؛
- current non-mutating safety gate Fail می‌شود؛
- historical payload Silent Mutate نمی‌شود؛
- `technical_import_ready=false`.

Fixture safe rollback باید بدون recalculating historical Price/Sale pass شود.

### ACC-018 — Deterministic Column1 Allocation

با reserved set ثابت:

- candidate اولیه = `max(reserved)+1`
- collision sequence طبق `NEW-005`
- output ID در چند replay یکسان

باشد.

Known historical ID reuse نباید رخ دهد.

Unusable reservation set باید `UNRESOLVED_COLUMN1_GENERATION` ایجاد کند.

### ACC-019 — Run Dimension and Transition Contract

State-machine tests باید Assert کنند:

- `run_type`, `run_status`, `run_result` مستقل‌اند؛
- legal paths `RUN-005` قبول؛
- direct `DRAFT -> IMPORT_SUCCESS` رد؛
- `IMPORT_READY` قبل از Final Approval غیرممکن؛
- `NO_CHANGES` path به `APPROVED_NO_IMPORT_REQUIRED` ختم شود.

### ACC-020 — Orthogonal Record State and Counting

Fixture باید Productی داشته باشد که هم:

- `change_status=UPDATED`
- `availability_status=ACTIVE|ACTIVATED`
- Warning issue

دارد.

PASS فقط اگر:

- هر محور مستقل صحیح باشد؛
- exclusive counts هر axis درست باشند؛
- issue event count باعث double-count row total نشود.

### ACC-021 — Canonical Source IR Replay

با `Normalized_Source_Catalog.json` archived و سایر authority snapshots ثابت:

PASS فقط اگر Rule Engine بدون re-reading PDF:

- همان Matching results؛
- همان Price/Availability results؛
- همان Proposed Product Pricing State؛
- همان Candidate semantic dataset

را تولید کند.

برای Rulebook v1، Matching fixture باید همچنین اثبات کند که equality یا resemblance بین WooCommerce `Column2 / SKU` و Source identifier به‌تنهایی `EXACT_SKU` Pricing Match ایجاد نمی‌کند. SKU Evidence فقط در حد مجاز `SCP-001` می‌تواند Scope assignment را پشتیبانی کند؛ Pricing Match باید یکی از Classهای Authoritative `MAT-011` باشد.

### ACC-022 — Mid-Run Rulebook Full Reprocess

اگر Permanent Rule در میانه Run نسخه Rulebook را تغییر دهد:

PASS فقط اگر:

- همه prior derived row results discard شوند؛
- Full Dataset از original snapshots + canonical IR دوباره پردازش شود؛
- Final Output فقط یک Rulebook Version داشته باشد؛
- هیچ mixed-version record وجود نداشته باشد.

### ACC-023 — External Authority Pinning

اگر Rulebook/PN/Schema/Product State/Import Contract active version در میانه Run تغییر کند:

PASS فقط اگر سیستم تغییر را تشخیص دهد و:

- reprocess/revalidate طبق Rule؛
- یا Block/Supersede

کند.

Silent continuation Fail است.

### ACC-024 — New Product Pending Blocker

New Product:

`DETECTED -> USER_APPROVED_FOR_CREATION -> PENDING_COMPLETION`

با missing required field باید:

- `NEW_PRODUCT_PENDING`
- `technical_import_ready=false`
- no appended row

ایجاد کند.

پس از تکمیل profile و validations:

- `READY_TO_APPEND`
- Append at end
- `change_status=ADDED`

شود.

`SKIPPED_THIS_RUN` باید non-blocking باشد.

### ACC-025 — Promotion Artifact Binding

دو Fixture:

1. hash available and matches -> `HASH_VERIFIED` passes.
2. hash unavailable + exact run-bound explicit user attestation -> `USER_ATTESTED` passes with lower assurance.

Mismatch hash باید:

`IMPORTED_ARTIFACT_MISMATCH`

و عدم هر دو evidence باید:

`IMPORT_ASSURANCE_INSUFFICIENT`
