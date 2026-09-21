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
- Runtime Composite failures باید با Codeهای واقعی مربوط به علت، مانند `UNRESOLVED_BOM_CONFLICT`, `UNRESOLVED_SOURCE_TRACE_MISSING`, Source Price errors یا Match conflicts گزارش شوند.

### ERR-003 — Resolved Issue Semantics

`blocks_import=YES` یعنی Code در حالت **active/unresolved** مانع readiness است.

پس از Resolution:

- Issue Event در Audit حفظ می‌شود.
- `resolved=true` می‌شود.
- دیگر readiness را Block نمی‌کند مگر Rule مربوطه state blocking دیگری ایجاد کند.

نمونه صریح: `DUPLICATE_SOURCE_FILE` پیش از Rerun Approval یک `BLOCKER` فعال است؛ پس از Approval مطابق `SRC-107` همان Event با `resolved=true` حفظ می‌شود و دیگر Blocking نیست. برای حل این Workflow Gate نباید Severity آن به `WARNING` تغییر داده شود.

### ERR-004 — No Duplicate Semantics

Rulebook نباید برای یک condition واحد دو Code مختلف با outcome یکسان ایجاد کند مگر تفاوت Domain/Recovery واقعاً لازم باشد.

اگر Code جدید overlap دارد، Registry update باید precedence و دلیل تمایز را ثبت کند.

---

## 30. Run Manifest — حداقل Schema مفهومی

Manifest باید برای هر Run standalone provenance کافی داشته باشد تا Execution Context، Decisions، Output، Import و Promotion قابل Reconcile باشند.

### Identity

حداقل:

- `run_id`
- `run_version`
- `run_type`
- `run_reason`
- `run_status`
- `run_result`
- `created_at`
- `closed_at`
- `technical_import_ready`
- `promotion_status`

### Source

حداقل:

- `source_filename`
- `source_file_hash`
- `source_date_original`
- `source_date_calendar`
- `source_date_normalized`
- `source_date_verification_status`
- `source_completeness_status`
- `source_chronology_state_start`
- `source_chronology_state_final`
- `last_processed_price_list_date_start`
- `normalized_source_catalog_hash`
- `extractor_version`
- `engine_version`
- Reference/Hash Source completeness checks

### Master

حداقل:

- `master_version_start`
- `expected_parent_master_version`
- `master_fingerprint_start`
- `master_fingerprint_final_check`
- `master_fingerprint_promotion_check`
- `schema_version_start`
- `schema_version_used`
- `row_count_start`
- `row_count_output`

### Product Pricing State

حداقل:

- `product_pricing_state_version_start`
- `product_pricing_state_hash_start`
- `product_pricing_state_proposed_hash`
- `product_pricing_state_committed_version` در صورت Promotion
- `product_pricing_state_committed_hash` در صورت Promotion
- State transition counts

### Rule Engine / External Authorities

حداقل:

- `rulebook_version_start`
- `rulebook_version_used`
- `rulebook_snapshot_hash`
- `part_number_reference_version_start`
- `part_number_reference_version_used`
- `part_number_reference_hash`
- `schema_version_start`
- `schema_version_used`
- `import_contract_version_start`
- `import_contract_version_used`
- `import_contract_hash`

اگر Authority در میانه Run تغییر کرده باشد، Manifest باید:

- old version
- new version
- change detection timestamp
- resolution method
- full reprocess indicator

را ثبت کند.

### Reconciliation

حداقل:

- Record Counts
- `change_status_counts`
- `availability_status_counts`
- `scope_status_counts`
- `max_issue_severity_counts`
- `issue_event_counts_by_code`
- Control Totals Before/After
- Total Price Before/After
- Active Sale Price count Before/After
- visible/hidden count Before/After
- Reconciliation PASS/FAIL
- explanation برای هر unexpected delta

### Decisions

برای هر User Decision:

- `decision_id`
- Topic
- Decision Code
- Timestamp + Timezone
- Outcome
- Scope: `CURRENT_RUN|PERMANENT_RULE`
- Effect on Run
- Approval Reference
- Related Product/Source/Rule IDs
- Rulebook Version در صورت Permanent Rule

Chronology، Discount، New Product، Manual Override، Scope Change، Final Approval و Import Attestation باید در صورت وقوع Decision records صریح داشته باشند.

### Output

حداقل:

- Candidate/Draft artifact names در صورت وجود
- Draft Hash در صورت وجود
- `final_csv_filename`
- `final_csv_hash`
- `audit_report_hash`
- `rulebook_snapshot_hash`
- `normalized_source_catalog_hash`
- `product_pricing_state_proposed_hash`
- Final `technical_import_ready`
- Final Approval Decision ID
- Final Approval Timestamp

### Import

حداقل:

- WP All Import Start/End در صورت موجود
- `import_outcome`
- Import Log Hash در صورت موجود
- `imported_filename`
- `imported_artifact_hash` در صورت موجود
- `imported_run_id`
- `imported_run_version`
- `import_confirmation_method`
- `import_assurance_level`
- User Confirmation Timestamp
- Import Evidence Reference

### Promotion

حداقل:

- `promotion_status`
- Promotion Gate results
- Parent Master check result
- Artifact binding result
- `promoted_master_version` در صورت موفقیت
- `promoted_master_fingerprint`
- committed Product Pricing State version/hash
- source baseline metadata before/after
- chronology state before/after
- promotion timestamp
- failure/block reason در صورت Blocked

### Recovery / Rollback

اگر Run از نوع `CORRECTIVE` یا `ROLLBACK` باشد، حداقل:

- source/related historical run ID
- target historical version در Rollback
- rollback source candidate evidence
- rollback safety gate results
- production reconciliation state start/end
- corrective reason
- related failed/partial/promotion-blocked run IDs

### Manifest Referential Integrity

هر Hash/Version/Decision ID/Artifact Reference الزامی باید:

- به Artifact یا record موجود اشاره کند؛
- یا صریحاً با reason `NOT_AVAILABLE` و Rule مجاز مشخص شود.

Manifest نباید با reference جعلی یا unresolved silent close شود.

---

## 31. سیاست توضیح خطا برای کاربر

سیستم نباید فقط Error Code نمایش دهد. قالب توضیح برای Blocker:

1. **چه محصولی؟**
2. **چه چیزی انتظار می‌رفت؟**
3. **چه چیز متفاوتی پیدا شد؟**
4. **این اطلاعات از کجا آمده؟**
5. **چرا سیستم خودش تصمیم نمی‌گیرد؟**
6. **اگر گزینه A انتخاب شود چه می‌شود؟**
7. **اگر گزینه B انتخاب شود چه می‌شود؟**
8. **پیشنهاد سیستم چیست و Confidence چقدر است؟**
9. **دقیقاً چه پاسخی از کاربر لازم است؟**

این الگو برای جلوگیری از Technical Overload و در عین حال حفظ Auditability الزامی است.

---

## 32. Boot Behavior پیشنهادی برای Project

هنگامی که کاربر می‌نویسد `شروع`، سیستم باید به‌صورت کوتاه و عملی:
