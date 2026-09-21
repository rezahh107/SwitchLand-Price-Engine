محدوده بررسی Strict شامل Composite rows تاریخی از familyهای قابل شناسایی `FR2 + MC2` و `MCE + TR5` بود.

نتیجه Evidence Review:

- Target-family Composite rows observed: `381`
- Rows با Component pair قابل Resolve مستقیم و strict از Old Supplier Price List: `206`
- Discriminating rows که `Algorithm A != Algorithm B`: `0`
- Rows که `A == B`: `206`
- از این 206، Historical Successful `Price` برای `196` row با هر دو Candidate برابر بود.
- `10` row دارای Component pair resolvable بودند ولی Historical Price با هیچ‌یک از Candidateها برابر نبود؛ بنابراین برای Rounding Policy Evidence معتبر محسوب نمی‌شوند و نیازمند بررسی مستقل Mapping/Historical fixture هستند.
- `175` Target-family row با Evidence موجود برای Strict component-price trace کامل Resolve نشدند.
- Support انحصاری برای Algorithm A: `0`
- Support انحصاری برای Algorithm B: `0`

علت اصلی Non-Discrimination این است که Component Rial amounts موجود در testable historical pairs در نقطه‌ای بودند که Ceiling جداگانه و Ceiling پس از Sum نتیجه یکسان ایجاد می‌کرد.

حکم Historical Evidence:

`NOT_PROVEN_BY_AVAILABLE_HISTORICAL_EVIDENCE`

بنابراین Historical Regression **هیچ‌یک** از Algorithm A/B را انتخاب یا اثبات نکرد.

در `v1.2.0` ambiguity نه از طریق Historical inference، بلکه با **Explicit Business Policy Approval** بسته شد:

`ALGORITHM_A_EXPLICITLY_APPROVED`

Policy مصوب:

`Final Toman = ceil(sum(component_rial_i) / 10)`

یعنی Componentها ابتدا در Rial exact جمع می‌شوند و تنها یک Final Ceiling پس از Summation اعمال می‌شود. این Decision از این پس Normative Authority است؛ Historical Regression همچنان Non-Discriminating باقی می‌ماند.

## 27. Acceptance Criteria برای Rulebook v1

Rulebook v1 فقط زمانی Conformant است که همه Acceptance Criteria الزامی این بخش PASS شوند. هر Criterion باید با Fixture یا Artifact قابل مشاهده ارزیابی شود؛ عبارت‌های صرفاً تفسیری کافی نیستند.

### ACC-001 — Rule Traceability

PASS فقط اگر:

- همه Ruleهای normative این Specification که MUST/MUST NOT دارند در Rulebook به Rule ID/Control ماشینی یا explicit non-executable governance control Map شده باشند؛
- Traceability Matrix هیچ Rule mandatory بدون implementation/test reference نداشته باشد.

### ACC-002 — 95-Column Schema Preservation

PASS فقط اگر روی Successful Fixture:

- Header count = 95؛
- exact Header names برابر expected fixture باشند؛
- exact Header order برابر expected fixture باشد؛
- هر output row دقیقاً 95 field داشته باشد.

### ACC-003 — Historical Pricing Regression

Old Price List باید روی Historical Fixture با Rulebook v1 اجرا شود.

PASS فقط اگر:

- Expected pricing/availability values تعریف‌شده در Regression Expected Manifest بازتولید شوند؛
- هر Difference از Historical CSV یا در Known Approved Correction list باشد یا به‌عنوان unresolved regression failure ثبت شود؛
- Historical known bugs به‌عنوان expected correct behavior بازتولید نشوند؛
- برای هر Historical Policy comparison، Fixtureهایی که Candidate Algorithmها خروجی یکسان دارند به‌عنوان **non-discriminating evidence** ثبت شوند و به نفع هیچ Candidate شمرده نشوند؛
- `DAT-003` صادقانه به‌عنوان `NOT_PROVEN_BY_AVAILABLE_HISTORICAL_EVIDENCE` باقی بماند.

Historical Regression منبع اثبات Algorithm A نیست. Normative authority برای Composite Rounding در Rulebook v1، **Explicit Business Policy Approval in Master Specification v1.2.0** و `PRC-002/008/009` است.

### ACC-004 — Historical Duplicate Identifier

Fixture دارای Duplicate تاریخی `Column1 = 533570909` باید:

- `DUPLICATE_COLUMN1` تولید کند؛
- `technical_import_ready=false` کند؛
- هیچ Import-ready artifact تولید نکند.

### ACC-005 — Mutation Boundary

روی Fixture حاوی تغییرات مجاز و fieldهای غیرمجاز:

PASS فقط اگر:

- تمام fieldهای خارج Mutation authority byte/semantic-equivalent به Master start باقی بمانند؛
- فقط fieldهای دارای Rule صریح تغییر کنند؛
- هر Auto-Fix Audit Event داشته باشد.

### ACC-006 — Price Provenance Completeness

برای هر row با Price changed:

PASS فقط اگر یک `price_provenance_type` معتبر و تمام required trace fields همان Type موجود باشد.

هر missing required trace باید `UNRESOLVED_SOURCE_TRACE_MISSING` ایجاد کند.

### ACC-007 — Composite BOM Calculation and Rounding Policy

برای Fixtureهای همه خانواده‌های Composite شناخته‌شده، PASS فقط اگر:

1. BOM Component identities برابر Expected Mapping باشند؛
2. raw Component Source Prices و Units درست Trace شوند؛
3. normalized Component Rial values دقیق باشند؛
4. `total_rial = sum(component_rial_i)` با Integer/Decimal exact محاسبه شود؛
5. دقیقاً یک Final `ceil(total_rial / 10)` اعمال شود؛
6. هیچ Component-level Rial-to-Toman Ceiling قبل از Summation اعمال نشود؛
7. Final Toman برابر Expected Fixture باشد؛
8. Provenance تمام fieldهای `SUPPLIER_COMPOSITE` در `PRC-012` را داشته باشد.

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
