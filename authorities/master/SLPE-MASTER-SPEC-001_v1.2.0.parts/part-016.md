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
