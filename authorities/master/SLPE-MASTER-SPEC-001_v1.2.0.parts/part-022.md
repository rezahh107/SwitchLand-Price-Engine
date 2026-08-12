این Revision Architecture یا Approved Pricing/Discount/Availability Policy جدیدی معرفی نمی‌کند. دو ambiguity/semantic defect را با بازگشت به Authority قبلی اصلاح می‌کند و در مورد Composite Rounding از انتخاب تجاری بدون Evidence خودداری می‌کند. چون Rounding Policy جدید تصویب نشده است، Revision یک Capability تجاری جدید ایجاد نمی‌کند؛ بلکه Claim نادرست readiness را تصحیح و Gate لازم را اضافه می‌کند.

#### Micro-Hardeningهای این Revision

- **Issue 1 / CLARIFICATION:** `DUPLICATE_SOURCE_FILE` در Registry از `WARNING + blocks_import=YES` به `BLOCKER + blocks_import=YES` اصلاح شد. بعد از explicit Rerun Approval همان Audit Event با `resolved=true` باقی می‌ماند و Non-Blocking می‌شود؛ `WARNING` globally Non-Blocking باقی ماند.
- **Issue 2 / POLICY GUARD:** `EXACT_SKU` از `MAT-001` و `MAT-011` به‌عنوان Authoritative Pricing Match Method حذف شد. `SCP-001` همچنان SKU قطعی را فقط به‌عنوان Scope-assignment Evidence حفظ می‌کند. Historical evidence موجود Source-side WooCommerce SKU domain مستقلی را برای Pricing Match اثبات نکرد.
- **Issue 3 / EVIDENCE GUARD:** Operational choice `sum Rial -> one ceil` از `PRC-008/009` برداشته شد، زیرا `v1.0.0` ترتیب عملیات را تصویب نکرده بود و Historical regression هیچ discriminating fixture ارائه نکرد. `UNRESOLVED_COMPOSITE_ROUNDING_POLICY` به‌عنوان Blocker اضافه شد.
- **Regression Evidence:** `DAT-003` نتایج مقایسه A/B را ثبت کرد: 381 target-family rows، 206 strict-resolvable pairs، 0 discriminating case، 196 historical matches for both، 10 neither، 175 insufficient strict trace.
- **Acceptance:** `ACC-003`, `ACC-007`, `ACC-021`, `ACC-026` فقط در محدوده سه Issue فوق اصلاح شدند.
- **Readiness:** Status از `READY_FOR_RULEBOOK_IMPLEMENTATION` به `HARDENED_BUT_NOT_RULEBOOK_READY` تغییر کرد تا Resolution تجاری Composite Rounding انجام شود.

#### Protected Architecture

تمام Hardeningهای unrelated `v1.1.0`، از جمله Product Pricing State، Source Completeness/Chronology، Canonical Source IR، Authority Pinning، Run/Record state separation، Full Reprocess، Rollback safety، Import Artifact Binding، No Partial Promotion، Production Reconciliation Guard، Canonical CSV/Fingerprint و Acceptance architecture بدون تضعیف حفظ شدند.

### v1.1.0 — 2026-08-11

**وضعیت:** `READY_FOR_RULEBOOK_IMPLEMENTATION`

**نوع Version:** MINOR

دلیل MINOR:

این Revision سیاست تجاری اصلی `v1.0.0` را تغییر نمی‌دهد، اما State Modelها، Canonical Artifactها، Governance/Promotion controls و Acceptance obligations جدید و backward-compatible اضافه می‌کند؛ بنابراین فقط PATCH نیست و تغییر ناسازگار Business Policy برای MAJOR نیز رخ نداده است.

#### Hardeningهای اصلی

- **F-001 / HARDENING:** `SRC-108..109` از معیار subjective completeness به State/Check model قطعی `COMPLETE|INCOMPLETE|UNRESOLVED` تبدیل شد.
- **F-002 / BEHAVIORAL_CONTRACT:** `PPS-*` برای persistent Product Pricing State، staged/committed semantics و provenance اضافه شد.
- **F-003 / HARDENING:** `RLB-*` rollback candidate را از execution readiness جدا کرد و current safety validation را non-mutating ساخت.
- **F-006 / BEHAVIORAL_CONTRACT:** `SRC-110..112` unknown-date chronology state و re-anchor contract را تعریف کرد.
- **F-008 / CLARIFICATION:** `NEW-005` به الگوریتم replay-deterministic `max(all_known_reserved_Column1)+1` تبدیل شد.
- **F-004 / HARDENING:** Run model به `run_type`, `run_status`, `run_result` و Promotion status مستقل تفکیک شد.
- **F-005 / HARDENING:** Record state به محورهای `change_status`, `availability_status`, `scope_status`, `issues[]` و `max_issue_severity` تفکیک شد.
- **F-007 / AUDITABILITY:** `Normalized_Source_Catalog.json`, extractor/engine versions و Canonical Source replay اضافه شد.
- **F-010 / CLARIFICATION:** `CLR-003` بر Generic NOT_FOUND precedence صریح گرفت.
- **F-011 / HARDENING:** Rulebook change mid-run اکنون Full Dataset Reprocess است؛ selective dependency logic حذف شد.
- **F-012 / HARDENING:** Rulebook/Schema/PN/Product State/Import Contract authority versions در Run Pin و قبل از Finalization verify می‌شوند.
- **F-009 / BEHAVIORAL_CONTRACT:** New Product lifecycle و `NEW_PRODUCT_PENDING` blocking semantics تعریف شد.
- **F-013 / AUDITABILITY:** Final CSV به Import outcome با Hash verification یا Run-bound user attestation Bind شد.
- **F-014 / TESTABILITY:** Technical Code Registry با category/severity/blocking/auto-fix metadata تعریف شد.
- **MST-007 / HARDENING:** Canonical CSV serialization contract اضافه شد.
- **MST-010 / HARDENING:** Canonical Master fingerprint دقیق تعریف شد.
- **VAL-101 / CLARIFICATION:** percentage-change formula و exact `>50%` comparison مشخص شد.
- **MAT-006 / CLARIFICATION:** Effective Source Specification fields برای duplicate consistency مشخص شدند.
- **Pricing Determinism / CLARIFICATION (superseded by v1.1.1):** Composite arithmetic در v1.1.0 به `sum in Rial -> one final ceil(Rial/10)` operational شده بود؛ v1.1.1 این انتخاب را به‌دلیل نبود Evidence/Approval کافی به‌عنوان Business Policy معتبر نمی‌پذیرد.
- **Availability/BOM / CLARIFICATION:** Component absence از positive BOM structure conflict تفکیک شد.
- **Price Domain / HARDENING:** Negative/invalid Final Price behavior بسته شد.
- **Audit Trace / AUDITABILITY:** Price provenance به source/manual/excluded/rollback/auto-repair types گسترش یافت.
- **Concurrency / HARDENING:** Promotion-time stale-parent guard اضافه شد.
- **Partial Import / HARDENING:** `PRODUCTION_RECONCILIATION_REQUIRED` به‌عنوان Recovery Gate تعریف شد.
- **Acceptance / TESTABILITY:** ACCها از 13 criterion عمومی به 35 criterion binary/observable گسترش یافتند.

#### Business Policies حفظ‌شده

بدون تغییر حفظ شدند:

- `GOV-001` Preserve by default
- `GOV-002` Least Mutation
- `GOV-003` AUTO_FIX/WARNING/BLOCKER
- `GOV-004` No Commercial Guessing
- Immutable History
- Transactional Promotion intent
- Decimal/Integer Money Arithmetic
- Matching Priority + Conflict Detection
- Rial Source / `ceil(Rial/10)` / No Hidden Markup
- Price controls visibility globally
- Discount Preflight
- Manual Override does not override Availability
- EXCLUDED supplier-price behavior
- New Product no-auto-create / run-scoped skip
- No Partial Promotion
- NO_CHANGES as auditable Run
- Rollback through new Version
- Known approved data correction `DAT-001`

### v1.0.0 — 2026-08-11

**وضعیت تاریخی:** Baseline Approved for Rulebook Implementation

شامل:

- معماری Source of Truth و Current Master
- Versioning و Immutable Run Archive
- Schema Contract 95 ستونی
- Unique Identifier/SKU Integrity
- Supplier Source Validation
- Matching Priority و Conflict Handling
- Color/Part Number Rules
- Direct/Composite Pricing
- Rial->Toman + Ceiling
- Global Price->Visibility Rule
- Discount Preflight
- Manual Price Override Lifecycle
- Excluded Product Policy
- New Product Workflow و Run-Scoped Skip
- AUTO_FIX/WARNING/BLOCKER Severity Model
- Mutation Allowlist
- Final Summary + Global Approval
- Audit Report/Manifest
- Import Success/Failed/Partial
- NO_CHANGES
- Rollback
- Rulebook/Schema Governance
- Conservative accounting-grade control defaults

---

# ضمیمه A — خلاصه رفتار در یک جمله

