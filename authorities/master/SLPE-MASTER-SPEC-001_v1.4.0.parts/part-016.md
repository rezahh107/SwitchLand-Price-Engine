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

ایجاد کند و Promotion را Block کند.

### ACC-026 — Deterministic End-to-End Replay

با Inputs یکسان:

- Canonical Source IR
- Master Snapshot
- Product Pricing State Snapshot
- Rulebook
- Schema
- Part Number Reference
- Import Contract
- User Decisions

دو اجرای مستقل باید:

- same semantic Output؛
- same canonical CSV bytes؛
- same `final_csv_hash`;
- same Proposed State hash

تولید کنند.

برای هر Composite Repricing case که BOM و Component Evidence معتبر است، هر دو implementation باید دقیقاً Policy مصوب `COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1` را اجرا کنند:

`total_rial = sum(component_rial_i)`

سپس:

`Final Toman = ceil(total_rial / 10)`

Replay باید همان Component provenance، `total_rial`, applicable Rule ID و Final Toman را بازتولید کند. Synthetic Discriminating Fixture تعریف‌شده در `ACC-007` نیز باید در replay نتیجه مصوب Algorithm A را تولید کند و Algorithm B را FAIL کند.

### ACC-027 — No Partial Promotion and Recovery Gate

Import Partial بدون Owner Exception باید:

- `run_status=IMPORT_PARTIAL`
- no Current Master promotion
- no Product Pricing State commit
- `PRODUCTION_RECONCILIATION_REQUIRED`

تولید کند.

تا Resolution، Promotion normal run جدید باید Block شود.

Import Partial با exact Owner Exception معتبر باید:

- `run_status=IMPORT_PARTIAL` را حفظ کند؛
- `promotion_status=PROMOTED_WITH_OPEN_RECONCILIATION` تولید کند؛
- فقط Subjectهای دقیق استثناء را در Queue نگه دارد؛
- Current Master/PPS/Current Set/Manifest را به Full Dataset مصوب و Queue باز bind کند؛
- Error جدید، Scope جدید یا Hash/CAS mismatch را Block کند.

### ACC-028 — Stale Run Promotion Guard

دو Concurrent Run با Parent Master یکسان ایجاد شوند.

اگر Run B زودتر Promote شود، Run A در Promotion Gate باید:

`STALE_RUN_PROMOTION_CONFLICT`

بگیرد و حق overwrite Current Master را نداشته باشد.

### ACC-029 — Canonical Master Fingerprint

دو Master با Cell values/order/header یکسان ولی Spreadsheet UI metadata متفاوت باید Fingerprint یکسان بدهند.

هر تغییر actual cell/header/row order باید Fingerprint متفاوت بدهد.

### ACC-030 — Color Precedence

Fixture که Series/Variant قطعی وجود دارد ولی Color Master در current complete Source unsupported است باید:

`UNRESOLVED_COLOR_NOT_SUPPORTED`

ایجاد کند و Generic `NOT_FOUND` Deactivation نباید اعمال شود.

### ACC-031 — Component Absence vs BOM Conflict

دو Fixture:

1. expected component code absent in COMPLETE source, no replacement evidence -> Deactivate.
2. source positively indicates different component code/structure -> `UNRESOLVED_BOM_CONFLICT`.

هر دو باید outcomes متفاوت deterministic داشته باشند.

### ACC-032 — Final Price Domain

Fixtures شامل:

- valid positive
- zero
- blank
- nonnumeric
- negative

باید outcomes `MST-008/009` و `PRC-013` را exact تولید کنند. Negative نباید visibility mutation با حدس ایجاد کند.

### ACC-033 — Canonical CSV Serialization

Dataset fixture با:

- delimiter داخل text؛
- double quote؛
- embedded newline؛
- blank fields؛
- leading/trailing whitespace

باید در replayهای مستقل bytes و SHA-256 یکسان تولید کند و پس از CSV parse exact 95-field semantic values را حفظ کند.

### ACC-034 — Import Contract Compatibility

Rulebook v1 Production Acceptance فقط وقتی PASS است که:

- `import_contract_version/hash` تثبیت شده باشد؛
- canonical output fixture توسط همان WP All Import Mapping Contract با intended fields تفسیر شود؛
- mismatch contract/version قابل تشخیص باشد.

### ACC-035 — Product State / Master Reconciliation

Fixture با Active Override یا Excluded Price که Product Pricing State price با Current Master Price conflict دارد باید:

`PRODUCT_PRICING_STATE_MASTER_MISMATCH`

و `technical_import_ready=false`

ایجاد کند؛ سیستم حق inference silent از یکی به نفع دیگری ندارد.

---

## 28. کنترل‌های محافظه‌کارانه منتخب برای موارد تعیین‌نشده

بر اساس اختیار داده‌شده برای انتخاب جزئیات استاندارد و محافظه‌کارانه، این Defaults اعمال می‌شوند مگر در آینده Rule دیگری تصویب شود:

### DEF-001 — Money Arithmetic

همه محاسبات پولی با Decimal/Integer و Rounding صریح انجام می‌شود.

Binary floating point در Pricing/Discount/Percentage comparison ممنوع است.

### DEF-002 — Timestamp

Timestampهای Audit با ISO 8601 و Timezone ذخیره می‌شوند.

### DEF-003 — Hash Algorithm

Hash پیش‌فرض Artifactها `SHA-256` است.
