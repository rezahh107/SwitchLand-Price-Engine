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

Import Partial fixture باید:

- `run_status=IMPORT_PARTIAL`
- no Current Master promotion
- no Product Pricing State commit
- `PRODUCTION_RECONCILIATION_REQUIRED`

تولید کند.

تا Resolution، Promotion normal run جدید باید Block شود.

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

Hash همیشه روی representation تعریف‌شده همان Artifact محاسبه می‌شود؛ Hash مبهم روی «داده مشابه» مجاز نیست.

### DEF-004 — Immutable Closed Manifest

Manifest پس از Close Run بازنویسی نمی‌شود؛ Correction با Event/Run جدید ثبت می‌شود.

### DEF-005 — Deterministic Output

با Source Canonical IR، Master Snapshot، Product Pricing State، Rulebook، Schema، PN Reference، Import Contract و User Decisions یکسان، Output باید Deterministic باشد.

Canonical CSV bytes و Proposed State hash نیز باید deterministic باشند.

### DEF-006 — No Silent Recovery Outside Allowlist

اگر سیستم خطای واضحی در field خارج Allowlist ببیند، فقط گزارش می‌دهد؛ اصلاح خودکار ممنوع است.

### DEF-007 — Draft vs Posted State

Runهای `DRAFT/VALIDATED/AWAITING_APPROVAL` از committed/promoted state جدا نگه داشته می‌شوند، مشابه جداسازی Unposted/Posted در سیستم‌های مالی.

### DEF-008 — Compensating Correction

اصلاح بعد از Promotion با Corrective Run انجام می‌شود، نه ویرایش تاریخچه.

### DEF-009 — Control Totals

قبل و بعد از Output، Control Totals محورهای مستقل محاسبه و در Report ثبت می‌شوند تا خطای گسترده سریع دیده شود.

### DEF-010 — No Partial Commit

هیچ Subset از Import Partial به Current Master یا Product Pricing State Promote نمی‌شود.

### DEF-011 — Reprocess-All over Dependency Graph

برای Dataset فعلی، تغییر Authority Rulebook/PN که re-evaluation لازم دارد با Full Dataset Reprocess حل می‌شود.

ساخت dependency graph selective برای v1 لازم نیست و نباید جایگزین Full Reprocess شود.

### DEF-012 — No Hidden Chronology Guess

Filename date، upload time، filesystem timestamp یا conversational order به‌تنهایی Supplier Source Chronology ایجاد نمی‌کنند.

### DEF-013 — Canonical Blank Semantics

در Data Contract:

- Blank cell = empty string semantic value مگر field-specific Rule خلاف آن را بگوید.
- literal `"0"` با Blank یکسان نیست.
- `Price=0` و `Sale Price=0` semantics مطابق Rules تخصصی خودشان دارند.

### DEF-014 — Closed Run Inputs

پس از `IMPORT_READY`, تغییر Material Input/Decision/Authority نباید همان Run را silently mutate کند. Run Supersede و Version جدید default محافظه‌کارانه است.

---

## 29. State Registry و Technical Code Registry

### 29.1 Run State Axes

`run_type`:

- `NORMAL`
- `RERUN_SAME_SOURCE`
- `ROLLBACK`
- `CORRECTIVE`

`run_status`:

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

`run_result`:

- `CHANGES`
- `NO_CHANGES`

`promotion_status`:

- `NOT_REQUIRED`
- `PENDING`
- `PROMOTED`
- `BLOCKED`

### 29.2 Record State Axes

`change_status`:

- `UNCHANGED`
- `UPDATED`
- `ADDED`

`availability_status`:

- `ACTIVE`
- `INACTIVE`
- `ACTIVATED`
- `DEACTIVATED`
- `UNRESOLVED`

`scope_status`:

- `SUPPLIER_MANAGED`
- `EXCLUDED_FROM_PRICE_LIST`

`max_issue_severity`:

- `NONE`
- `INFO`
- `WARNING`
- `BLOCKER`

### 29.3 New Product Lifecycle

- `DETECTED`
- `USER_APPROVED_FOR_CREATION`
- `PENDING_COMPLETION`
- `READY_TO_APPEND`
- `SKIPPED_THIS_RUN`
- `REJECTED`

`NEW_PRODUCT_SKIPPED_THIS_RUN` یک user-facing/technical lifecycle label برای `SKIPPED_THIS_RUN` است و Error Code محسوب نمی‌شود.

### 29.4 Source State

`source_completeness_status`:

- `COMPLETE`
- `INCOMPLETE`
