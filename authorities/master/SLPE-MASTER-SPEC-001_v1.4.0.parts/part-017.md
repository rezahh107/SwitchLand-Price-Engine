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

هیچ Subset از Import Partial Promote نمی‌شود. مسیر `GOV-007A` در صورت مجازبودن، فقط **Full Dataset مصوب** را همراه با exact open-reconciliation queue Promote می‌کند؛ Promotion یک Subset یا پنهان‌کردن Failure همچنان ممنوع است.

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
- `UNRESOLVED`

`source_chronology_state`:

- `VERIFIED`
- `SOURCE_CHRONOLOGY_UNVERIFIED`

### ERR-001 — Technical Code Metadata Contract

هر Technical Code باید metadata زیر داشته باشد:

- `code`
- `category`
- `default_severity`
- `blocks_import`
- `scope`
- `auto_fix_allowed`

Categories مجاز حداقل:

- `DATA_INTEGRITY`
- `SOURCE_VALIDATION`
- `MATCHING`
- `PRICING`
- `DISCOUNT`
- `GOVERNANCE`
- `WORKFLOW`
- `AUTO_FIX`
- `AUDIT`
- `ROLLBACK`
- `PROMOTION`

اگر Code جدیدی در Rulebook اضافه شود، قبل از استفاده Production باید در Registry Versioned ثبت شود.

### ERR-002 — Technical Code Registry

| Code | Category | Default Severity | Blocks Import | Scope | Auto-Fix Allowed | Semantics |
|---|---|---:|---:|---|---:|---|
| `DUPLICATE_COLUMN1` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | NO | Duplicate Unique Identifier |
| `DUPLICATE_SKU` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | NO | Duplicate SKU under case-insensitive uniqueness |
| `MISSING_UNIQUE_IDENTIFIER` | DATA_INTEGRITY | BLOCKER | YES | RECORD | NO | Blank Column1 |
| `MISSING_SKU` | DATA_INTEGRITY | BLOCKER | YES | RECORD | NO | Blank Column2 |
| `MISSING_PRICE` | PRICING | BLOCKER | YES | RECORD | NO | Price blank and not deterministically recoverable |
| `UNRESOLVED_INVALID_MASTER_PRICE` | PRICING | BLOCKER | YES | RECORD | NO | Master Price invalid and not deterministically repairable |
| `INVALID_MASTER_PRICE_AUTO_REPAIRED` | AUTO_FIX | INFO | NO | RECORD | YES | Invalid Master Price repaired from authoritative source/rule |
| `UNRESOLVED_INVALID_PRICE` | PRICING | BLOCKER | YES | RECORD/SOURCE | NO | Numeric price violates allowed domain, such as negative |
| `MASTER_SCHEMA_MISMATCH` | DATA_INTEGRITY | BLOCKER | YES | RUN | NO | Master schema differs from active schema contract |
| `MASTER_CHANGED_DURING_RUN` | WORKFLOW | BLOCKER | YES | RUN | NO | Current Master fingerprint changed before finalization |
| `STALE_RUN_PROMOTION_CONFLICT` | PROMOTION | BLOCKER | YES | PROMOTION | NO | Expected Parent Master no longer current at promotion |
| `SOURCE_PRICE_LIST_OLDER_THAN_BASELINE` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | NO | Verified source date older than verified baseline |
| `SOURCE_DATE_UNVERIFIED` | SOURCE_VALIDATION | WARNING | NO | SOURCE | NO | Supplier source date cannot be verified |
| `UNRESOLVED_SOURCE_DATE_CONFLICT` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | Multiple candidate source dates cannot be deterministically resolved |
| `SOURCE_CHRONOLOGY_UNVERIFIED` | SOURCE_VALIDATION | WARNING | NO | SOURCE/RUN | Accepted unknown-date source has broken verified chronology chain |
| `SOURCE_CHRONOLOGY_RESOLUTION_REQUIRED` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE/RUN | New source ordering cannot be proven while chronology is unverified |
| `DUPLICATE_SOURCE_FILE` | WORKFLOW | BLOCKER | YES | SOURCE/RUN | Source hash already known; explicit rerun decision required while unresolved; after approval same event becomes resolved and non-blocking |
| `UNRESOLVED_SOURCE_INCOMPLETE` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | Positive evidence source is incomplete |
| `UNRESOLVED_SOURCE_COMPLETENESS` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | Completeness cannot be deterministically classified as complete |
| `UNRESOLVED_INVALID_PRICE_FORMAT` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE/RECORD | Price text cannot be parsed unambiguously |
| `UNRESOLVED_SOURCE_CONFLICT` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE/RECORD | Same source code has materially conflicting effective specification |
| `DUPLICATE_SOURCE_RECORD_CONSISTENT` | SOURCE_VALIDATION | WARNING | NO | SOURCE | Duplicate source records are semantically identical and deduplicated logically |
| `UNRESOLVED_MATCH_CONFLICT` | MATCHING | BLOCKER | YES | RECORD | Authoritative match evidence conflicts |
| `UNRESOLVED_SOURCE_TO_MULTIPLE_PRODUCTS` | MATCHING | BLOCKER | YES | RECORD/RUN | Direct source code maps to multiple products without explicit one-to-many rule |
| `UNRESOLVED_MULTIPLE_SOURCES_FOR_PRODUCT` | MATCHING | BLOCKER | YES | RECORD | Multiple source codes map to one product without primary/alias/BOM rule |
| `UNRESOLVED_COLOR_NOT_SUPPORTED` | MATCHING | BLOCKER | YES | RECORD | Series/variant exists but Master color is unsupported in current source |
| `UNRESOLVED_PART_NUMBER_CONFLICT` | MATCHING | BLOCKER | YES | RECORD | Part Number semantics conflict with Master/match evidence |
| `WARNING_MISSING_PART_NUMBER` | MATCHING | WARNING | NO | RECORD | Part Number missing but authoritative match exists through other evidence |
| `PART_NUMBER_REFERENCE_CONFLICT` | GOVERNANCE | WARNING | NO | GOVERNANCE | New PN reference conflicts with active rulebook; new reference activation blocked, prior active remains |
| `UNRESOLVED_BOM_CONFLICT` | PRICING | BLOCKER | YES | RECORD | Source positively conflicts with stored BOM/component structure |
| `UNRESOLVED_SOURCE_TRACE_MISSING` | AUDIT | BLOCKER | YES | RECORD | Required price provenance trace is incomplete |
| `WARNING_PRICE_CHANGE_GT_50_PERCENT` | PRICING | WARNING | NO | RECORD | Exact positive-to-positive price change exceeds 50% |
| `PRICE_VISIBILITY_SYNC` | AUTO_FIX | INFO | NO | RECORD | visibility synchronized from valid final Price |
| `UNRESOLVED_INVALID_DISCOUNT` | DISCOUNT | BLOCKER | YES | RECORD | Discount percent outside allowed range |
| `UNRESOLVED_INVALID_DISCOUNT_PRECISION` | DISCOUNT | BLOCKER | YES | RECORD | Discount percent has more than two decimal places |
| `UNRESOLVED_INVALID_SALE_PRICE` | DISCOUNT | BLOCKER | YES | RECORD | Sale Price malformed/negative/invalid |
