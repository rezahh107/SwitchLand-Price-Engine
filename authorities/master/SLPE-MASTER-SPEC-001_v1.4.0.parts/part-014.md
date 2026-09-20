اگر Historical data برای عبور از Gate نیاز به mutation داشته باشد:

- Rollback exact execution Block می‌شود.
- Conflict توضیح داده می‌شود.
- Migration/Corrective handling فقط با Approval صریح و Run جدید انجام می‌شود.

### RLB-005 — Discount تاریخی

Sale Price Historical target دقیقاً Restore می‌شود مگر کاربر صریحاً تغییر جدیدی بخواهد.

اگر کاربر تغییر Discount جدید بخواهد، آن عمل دیگر «exact historical payload» نیست و باید به‌عنوان approved transformation در همان Rollback Run یا یک Corrective Run با Decision Trace ثبت شود؛ بدون Decision صریح ممنوع است.

### RLB-006 — Historical Product Pricing State

برای Versionهای موفقی که Product Pricing State Snapshot دارند، Rollback candidate شامل:

- Historical Master Dataset
- Historical Product Pricing State

است.

State نباید از Current Master Price حدس زده شود.

### RLB-007 — Legacy Rollback State Gap

برای Historical Successful Versionهای قبل از State Archive که `Product_Pricing_State` قابل بازیابی ندارند:

- Version همچنان `rollback_source_candidate` است.
- اگر State لازم برای safe future behavior deterministic از Archived evidence قابل reconstruct نباشد:

`ROLLBACK_STATE_UNAVAILABLE` -> Blocker.

سیستم نباید State را حدس بزند.

کاربر می‌تواند Corrective/Migration Run جداگانه را تأیید کند، ولی Historical Version rewrite نمی‌شود.

### RLB-008 — Schema/Identity Safety Failure

اگر Historical target با Schema/Identity Contract فعلی unsafe باشد:

- Schema incompatible -> `ROLLBACK_SCHEMA_INCOMPATIBLE`
- Duplicate/identity unsafe -> `ROLLBACK_IDENTITY_CONFLICT`

Rollback `technical_import_ready=false`.

Historical payload Silent تغییر نمی‌کند.

### RLB-009 — Rulebook Semantics

Rollback historical Price را با Rulebook فعلی Recompute نمی‌کند.

Rulebook فعلی فقط:

- workflow controls؛
- non-mutating safety validation؛
- audit/promotion rules

را برای Rollback اعمال می‌کند.

اگر Historical Product Pricing State با یک Permanent Rule فعلی تعارض material داشته باشد و exact rollback semantics را نامطمئن کند، Conflict باید Block شود و تصمیم/migration explicit لازم است.

### RLB-010 — Rollback Promotion

Rollback نیز:

- Final Summary
- User Approval
- Import Artifact Binding
- Import Success
- Promotion-time concurrency
- Product Pricing State commit

را نیاز دارد.

فقط بعد از Pass:

`promotion_status=PROMOTED`

و Current Master به Rollback Run جدید Promote می‌شود.

### RLB-011 — Rollback Readiness Terminology

سیستم نباید بگوید «هر Version موفق قابل Rollback است» به معنی guaranteed executable.

عبارت دقیق:

> هر Version موفق یک rollback source candidate است؛ اجرای Rollback فقط پس از Pass کردن Current Non-Mutating Safety Gates مجاز است.

---

## 24. Rulebook Governance

### RBK-001 — Rulebook Versioning

هر تغییر دائمی Rulebook باید Version را افزایش دهد.

Versioning:

- `MAJOR` — تغییر ناسازگار در رفتار/Contract
- `MINOR` — Rule جدید سازگار یا Capability جدید
- `PATCH` — Clarification غیررفتاری/اصلاح نگارشی

### RBK-002 — Change Log

هر Rulebook Version باید حداقل ثبت کند:

- Rule/Mapping تغییرکرده
- Old State
- New State
- Date/Time
- Approval Source
- Reason در صورت وجود

### RBK-003 — Rule تصویب‌شده مستقل از نتیجه Run

اگر در یک Run کاربر Rule دائمی را تصویب کند، آن Rule حتی اگر خود Run بعداً Reject/Fail شود، Active باقی می‌ماند؛ مگر کاربر صریحاً آن Decision را Current-Run-Only اعلام کرده باشد.

این Rule فقط Permanent Rulebook decisions را پوشش می‌دهد و نباید Runtime Product Pricing State transition را مستقل از Promotion Commit کند.

### RBK-004 — لغو Rule دائمی

Rule قبلی حذف تاریخی نمی‌شود. Rulebook Version جدید باید Rule قبلی را `deprecated/inactive` کند و Replacement را ثبت کند.

### RBK-005 — Single Rulebook Version per Output

Final Output یک Run نباید ترکیبی از چند Rulebook Version باشد.

اگر Active Rulebook Version در میانه Run به‌دلیل Permanent Decision تغییر کند:

- Full Dataset Reprocess مطابق `WF-012` الزامی است.
- Original Master Snapshot، Product Pricing State Start و Canonical Source IR مبنای Reprocess هستند.
- Selective reprocessing بر اساس «potentially affected rows» در v1 ممنوع است.
- Final Manifest فقط Version نهایی استفاده‌شده را به‌عنوان `rulebook_version_used` ثبت می‌کند و Start Version را نیز برای provenance نگه می‌دارد.

### RBK-006 — Rulebook Determinism Contract

هر Rule ماشینی که Mutation/Blocker ایجاد می‌کند باید حداقل مشخص کند:

- `rule_id`
- trigger predicate
- required evidence
- precedence/conflict behavior
- mutation authority
- issue code/severity
- output/state effect
- audit fields
- acceptance test reference

عبارت‌هایی مانند `certain`, `safe`, `reliable` نباید در Rulebook بدون operational predicate باقی بمانند.

### RBK-007 — Permanent Mapping Governance

Permanent Mapping:

- Rulebook versioned data است.
- Mapping ID یکتا دارد.
- Approval Decision ID دارد.
- Conflict با Source Evidence قطعی مطابق `MAT-002` Block می‌شود.
- Mapping priority حق مخفی‌کردن conflict را ندارد.

---

## 25. Schema Governance

### SCH-001 — schema_version

Schema باید Version مستقل داشته باشد.

هر schema version حداقل باید:

- exact ordered headers
- column count
- field identity metadata
- New Product required-field profile
- canonical serialization compatibility

را مشخص کند.

### SCH-002 — Change Approval

هر تغییر Header/Order/Column Count باید قبل از پردازش خودکار:

- Diff شود.
- به کاربر توضیح داده شود.
- تأیید شود.
- در `schema_version` جدید ثبت شود.
- با Import Contract و Rulebook Compatibility Validation شود.

تا آن زمان:

`MASTER_SCHEMA_MISMATCH` -> Blocker.

### SCH-003 — History

نسخه‌های قبلی Schema و Change Summary باید Immutable حفظ شوند.

### SCH-004 — New Product Required Field Profile

هر Schema فعال باید Profile:

`new_product_required_fields`

را داشته باشد.

برای هر field حداقل:

```json
{
  "header": "...",
  "required_for_new_product": true,
  "value_source_policy": "USER|EXPLICIT_GENERATOR|APPROVED_SUGGESTION|PRESCRIBED_DEFAULT",
  "auto_generation_allowed": false,
  "validation_rule_ids": []
}
```

Profile بخشی از `schema_version` است.

System حق ندارد صرفاً بر اساس اینکه Existing Rows بعضی fieldها را Blank دارند، required status New Product را حدس بزند.

### SCH-005 — Mid-Run Schema Change

Schema Version در Run Pin می‌شود.

اگر Schema Active در میانه Run تغییر کند:

- ادامه Silent ممنوع است.
- Final output با schema version قدیمی و جدید مخلوط نمی‌شود.
- فقط اگر Migration/Approval صریح برای همان Run وجود داشته باشد، Full Reprocess/Revalidation ممکن است.
- در غیر این صورت Run Block/Supersede می‌شود.

### SCH-006 — Import Contract Compatibility

هر Schema Version که برای Production استفاده می‌شود باید Compatibility آن با `import_contract_version` فعال در Acceptance Test اثبات شود.

Schema approval به‌تنهایی Import Contract compatibility را فرض نمی‌کند.

---

## 26. Known Approved Data Corrections

### DAT-001 — Historical Duplicate Column1

Duplicate تاریخی `Column1 = 533570909` باعث Cross-Contamination در Import شده بود.

تصمیم تأییدشده:

- `533570909` متعلق به «کلید لمسی هوشمند روشنایی یک پل تبدیل Zigbee | سری INNOVA» است.
- «پریز کابینتی کشویی مولتی فانکشن» به `533570920` اصلاح شده است.

این مورد باید به‌عنوان Regression Fixture برای Duplicate Identifier حفظ شود.

### DAT-002 — Regression نه Byte-Equality کور

Fixture موفق تاریخی شامل برخی ایرادهای Data Integrity شناخته‌شده بوده است. هدف Regression Test:

1. بازتولید منطق قیمت‌گذاری صحیح تاریخی.
2. تشخیص خطاهای تاریخی شناخته‌شده.
3. اثبات اینکه خروجی Rulebook جدید فقط در اصلاحات تأییدشده از Fixture قدیمی تفاوت دارد.

### DAT-003 — Composite Rounding Regression Evidence

برای Micro-Hardening `v1.1.1`، Historical Successful CSV و Old Supplier Price List برای تفاوت بین دو Candidate زیر بررسی شدند:

- Algorithm A: `ceil(sum(component_rial) / 10)`
- Algorithm B: `sum(ceil(component_rial / 10))`

