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
| `UNRESOLVED_SALE_PRICE_CONFLICT` | DISCOUNT | BLOCKER | YES | RECORD | Sale Price greater than or equal to Final Regular Price |
| `UNRESOLVED_DISCOUNT_DEFINITION_CONFLICT` | DISCOUNT | BLOCKER | YES | RECORD | Percentage and direct Sale Price resolve to different values |
| `DISCOUNT_DEFINITIONS_CONSISTENT` | DISCOUNT | INFO | NO | RECORD | Two explicit discount definitions resolve to same final Sale Price |
| `MANUAL_OVERRIDE_REVIEW_REQUIRED` | PRICING | BLOCKER | YES | RECORD | Active committed override differs from new valid supplier price |
| `PRODUCT_PRICING_STATE_MASTER_MISMATCH` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | Persistent commercial state conflicts with Current Master |
| `UNRESOLVED_SCOPE` | GOVERNANCE | BLOCKER | YES | RECORD | Product pricing scope cannot be deterministically established |
| `WARNING_COLUMN1_HISTORY_INCOMPLETE` | DATA_INTEGRITY | WARNING | NO | RUN/RECORD | Identifier generation guarantee limited to known readable history |
| `UNRESOLVED_COLUMN1_GENERATION` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | Deterministic safe identifier allocation cannot be established |
| `NEW_PRODUCT_PENDING` | WORKFLOW | BLOCKER | YES | RECORD/RUN | User approved creation but schema-required row data is incomplete |
| `EXTERNAL_AUTHORITY_CHANGED_DURING_RUN` | GOVERNANCE | BLOCKER | YES | RUN | Pinned authority version changed and requires reprocess/revalidation |
| `IMPORTED_ARTIFACT_MISMATCH` | PROMOTION | BLOCKER | YES | PROMOTION | Imported artifact hash differs from approved final CSV hash |
| `IMPORT_ASSURANCE_INSUFFICIENT` | PROMOTION | BLOCKER | YES | PROMOTION | Neither hash verification nor run-bound explicit user attestation is available |
| `PRODUCTION_RECONCILIATION_REQUIRED` | WORKFLOW | BLOCKER | YES | RUN/PROMOTION | Production may not match committed Current Master after partial/uncertain import |
| `ROLLBACK_STATE_UNAVAILABLE` | ROLLBACK | BLOCKER | YES | RUN | Historical pricing state required for safe rollback cannot be reconstructed |
| `ROLLBACK_SCHEMA_INCOMPATIBLE` | ROLLBACK | BLOCKER | YES | RUN | Historical payload cannot be imported safely under current schema/import contract |
| `ROLLBACK_IDENTITY_CONFLICT` | ROLLBACK | BLOCKER | YES | RUN | Historical rollback payload violates current identity safety gates |

#### Historical / Deprecated Code — Non-Active

`UNRESOLVED_COMPOSITE_ROUNDING_POLICY` یک Code تاریخی است که در `v1.1.1` برای Block کردن Policy نامشخص Composite Rounding استفاده شد.

- `deprecated_in = 1.2.0`
- Active Technical Code Registry: **NO**
- Rulebook v1 conforming to `v1.2.0` MUST NOT emit this Code.
- Historical Manifest/Change Log references MAY retain it for provenance.
