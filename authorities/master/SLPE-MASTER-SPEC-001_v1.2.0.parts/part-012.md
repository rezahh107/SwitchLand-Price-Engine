- تمام fieldهای required دارای مقدار معتبر باشند؛
- `Column1` و `Column2` uniqueness Pass شوند؛
- generated/suggested values مجوز لازم داشته باشند؛
- Price/Sale/visibility Valid باشند؛
- Row دقیقاً 95 field مطابق schema داشته باشد؛
- هیچ Blocker record-level فعال نباشد.

### NEW-012 — Cancellation after Approval

اگر کاربر پس از `USER_APPROVED_FOR_CREATION` یا `PENDING_COMPLETION` تصمیم بگیرد Product در همان Run اضافه نشود:

- `SKIPPED_THIS_RUN` برای intent «فعلاً اضافه نکن» استفاده می‌شود؛
- `REJECTED` برای abandon/reject current creation attempt قابل استفاده است؛
- proposed incomplete row Append نمی‌شود؛
- Rule دائمی ایجاد نمی‌شود مگر کاربر صریحاً Permanent Rule بخواهد.

---

## 18. AUTO_FIX / WARNING / BLOCKER

### VAL-201 — AUTO_FIX

فقط وقتی مجاز است که:

- نتیجه قطعی باشد.
- Rule معتبر وجود داشته باشد.
- فیلد در Mutation Allowlist باشد.
- Source/Match/Formula requirements همان Rule Pass شده باشند.

AUTO_FIX نیازمند تأیید موردی نیست ولی Audit اجباری است.

حداقل Audit:

`Old Value -> New Value -> Rule Applied -> Reason`

AUTO_FIX هرگز حق دورزدن Blocker را ندارد.

### VAL-202 — Mutation Allowlist

فعلاً Auto-Fix فقط روی:

- `Price`
- `Sale Price`
- `visibility`

مجاز است.

تشخیص خطا در Name، Description، Attribute، Part Number، SKU، Column1 و سایر fieldها حق Auto-Edit ایجاد نمی‌کند.

New Product row generation rules و explicit Schema migration از این Auto-Fix Allowlist جدا هستند و فقط با Approval/Rule مربوطه مجازند.

### VAL-203 — Price Auto-Fix

حتی Price فقط وقتی Auto-Fix می‌شود که Match/Source/Formula Authoritative و deterministic باشد.

Name-only، Suggestion، ambiguous alias یا Match احتمالی ممنوع است.

### VAL-204 — WARNING

Warning مانع Import نیست، مگر Evidence جدیدی نشان دهد condition در واقع Blocker است.

نمونه‌ها:

- تغییر Price بیش از 50٪
- Source Date Unverified در Chronology مجاز
- Missing Part Number در Match قطعی
- Duplicate Source Record کاملاً Consistent
- محدودیت Archive coverage برای Column1 در صورت safe known-set generation

Warning باید visible و auditable باشد ولی Final Approval را بی‌دلیل متوقف نمی‌کند.

### VAL-205 — BLOCKER

Blocker فقط برای ابهام یا خطر واقعی داده/Promotion است.

نمونه‌ها:

- Duplicate SKU/Column1
- Schema Mismatch
- Match Conflict
- Source Conflict
- Source Incomplete/Completeness Unresolved
- Source Chronology unresolved
- Duplicate Source rerun decision unresolved
- BOM Conflict
- Invalid Price/Discount
- Sale Price Conflict
- Multiple Source ambiguity
- Product Pricing State mismatch
- New Product approved but incomplete
- Rollback safety failure
- Stale Promotion
- Imported Artifact mismatch

وجود هر Blocker فعال:

`technical_import_ready=false`

و Artifact نباید Final Import-ready اعلام شود.

### VAL-206 — Issue Object

هر technical event باید حداقل این ساختار مفهومی را داشته باشد:

```json
{
  "code": "...",
  "issue_kind": "AUTO_FIX|WARNING|UNRESOLVED|ERROR",
  "severity": "INFO|WARNING|BLOCKER",
  "blocks_import": true,
  "scope": "RECORD|SOURCE|RUN|GOVERNANCE|PROMOTION",
  "rule_id": "...",
  "message": "...",
  "resolved": false
}
```

Technical Code default metadata از Registry بخش 29 می‌آید. Rule-specific context MAY severity را فقط در صورتی تغییر دهد که همان Rule صریحاً اجازه دهد.

### VAL-207 — Severity Aggregation

برای هر Record:

`max_issue_severity`

از unresolved issueهای همان Record به‌ترتیب زیر محاسبه می‌شود:

`NONE < INFO < WARNING < BLOCKER`

وجود `AUTO_FIX` event معمولاً Severity `INFO` دارد مگر Rule خلاف آن را صریحاً بگوید.

Run-level `technical_import_ready` از وجود هر unresolved `blocks_import=true` در تمام scopeها محاسبه می‌شود.

---

## 19. مدل Record State، گزارش و تجربه کاربر

### REC-001 — Orthogonal Record State

یک Enum واحد نباید Change، Availability، Scope و Issue را با هم مخلوط کند.

هر Existing/Output Record حداقل این محورهای مستقل را دارد:

#### `change_status`

- `UNCHANGED`
- `UPDATED`
- `ADDED`

`ADDED` فقط برای New Product appended است. حذف Row در Scope سیستم نیست.

#### `availability_status`

- `ACTIVE`
- `INACTIVE`
- `ACTIVATED`
- `DEACTIVATED`
- `UNRESOLVED`

#### `scope_status`

برای Existing Master Product:

- `SUPPLIER_MANAGED`
- `EXCLUDED_FROM_PRICE_LIST`

اگر Scope قابل تعیین نباشد field می‌تواند null باشد، اما Issue `UNRESOLVED_SCOPE` باید فعال شود.

#### `issues[]`

لیست Issue Objectهای `VAL-206`.

#### `max_issue_severity`

- `NONE`
- `INFO`
- `WARNING`
- `BLOCKER`

### REC-002 — Availability Status Calculation

برای Existing Product با Old/New Final Price معتبر:

- Old `>0`, New `>0` -> `ACTIVE`
- Old `0`, New `0` -> `INACTIVE`
- Old `0`, New `>0` -> `ACTIVATED`
- Old `>0`, New `0` -> `DEACTIVATED`

اگر Old یا New Price برای تعیین availability معتبر نباشد:

`UNRESOLVED`.

برای New Product `ADDED`:

- Final Price `>0` -> `ACTIVE`
- Final Price `=0` -> `INACTIVE`

### REC-003 — Change Status Calculation

`UNCHANGED` وقتی است که Output row در تمام 95 cell با Starting Master row برابر باشد.

`UPDATED` وقتی است که Existing row حداقل یک field مجاز مطابق Rule تغییر کرده باشد.

`ADDED` برای appended new row است.

Audit event یا Warning بدون Data Row mutation، `change_status` را به UPDATED تبدیل نمی‌کند.

Product Pricing State transition جداگانه در State Reconciliation گزارش می‌شود حتی اگر row data اتفاقاً تغییر نکرده باشد.

### REC-004 — Control Total Counting

Control Totalها باید Axis-specific باشند:

- `change_status_counts`: exclusive و جمع آن برابر Output row count است.
- `availability_status_counts`: exclusive و برای تمام records با evaluable state محاسبه می‌شود؛ `UNRESOLVED` نیز count دارد.
- `scope_status_counts`: Existing/Added records دارای committed/proposed scope؛ null scope جداگانه count می‌شود.
- `max_issue_severity_counts`: exclusive.
- `issue_event_counts_by_code`: تعداد eventها و MAY بیش از row count باشد.

سیستم نباید مجموع `UPDATED + ACTIVATED + WARNING` را به‌عنوان تعداد unique records گزارش کند.

### AUD-001 — دو لایه توضیح

هر Summary باید دو لایه داشته باشد:

1. **توضیح ساده و غیرتخصصی:** چه اتفاقی افتاده، چرا، و اثر آن چیست.
2. **جزئیات فنی Audit:** Rule ID، Match Method، Price Provenance/Source Trace، Error/Warning Code، Old/New Value و State Transition.

### AUD-002 — Summary قبل از Import

اگر `technical_import_ready=true`، Run وارد `AWAITING_APPROVAL` می‌شود و پیش از Final Import-ready شدن Summary کلی به کاربر نمایش داده می‌شود، شامل حداقل:

- تعداد `UPDATED`, `UNCHANGED`, `ADDED`
- تعداد `ACTIVATED`, `DEACTIVATED`, `ACTIVE`, `INACTIVE`
- تعداد Auto-Fix event
- تعداد Warning record/event
- تعداد New Product Skipped
- تعداد Discount Change
- Product Pricing State transitions
- Source Chronology state
- final Candidate Control Totals

برای هر دسته یک توضیح ساده اجباری است.

### AUD-003 — Final Approval

فایل نباید به‌عنوان «نسخه نهایی آماده Import» اعلام شود تا:

- Summary ارائه شود؛
- کاربر یک Final Global Approval بدهد؛
- Approval Decision در Manifest ثبت شود.

بعد از Approval:
