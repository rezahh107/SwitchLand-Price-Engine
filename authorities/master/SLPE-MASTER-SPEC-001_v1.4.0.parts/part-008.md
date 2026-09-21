تمام قیمت‌های Price List NESTECH به‌عنوان **ریال** در نظر گرفته می‌شوند مگر خود Source خلاف آن را صریحاً اعلام کند.

Canonical IR باید `source_currency_unit` را ثبت کند.

اگر Source صریحاً Toman اعلام کند، برای Internal Supplier Arithmetic مقدار به‌صورت exact با `Toman * 10` به Rial پایه تبدیل می‌شود؛ هیچ Exchange Rate دیگری وجود ندارد.

### PRC-002 — تبدیل ریال به تومان

برای هر مبلغ ریالی که طبق Rule مربوطه به Toman تبدیل می‌شود، فرمول تبدیل:

`Toman = ceil(Rial / 10)`

است.

مثال برای یک مبلغ منفرد:

`12,345,678 Rial -> 1,234,567.8 Toman -> 1,234,568 Toman`

برای **Composite Products**، Business Owner در `v1.2.0` Commercial Arithmetic Policy زیر را صریحاً تصویب کرده است:

`policy_id = COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1`

1. همه Component Source Prices ابتدا در صورت نیاز به Internal Rial exact Normalize می‌شوند.
2. همه Component Rial amounts با Integer/Decimal exact جمع می‌شوند.
3. فقط Total نهایی Composite یک‌بار با `ceil(total_rial / 10)` به Toman تبدیل می‌شود.
4. Component-level Rial-to-Toman Ceiling قبل از Summation ممنوع است.
5. Binary Floating Point در این محاسبات ممنوع است.

بنابراین برای Composite:

`Final Toman = ceil(sum(component_rial_i) / 10)`

و این رفتار در `PRC-008` و `PRC-009` برای خانواده‌های Composite فعلی تخصصی می‌شود.

### PRC-003 — عدم ضریب اضافی

به‌صورت پیش‌فرض:

- Markup = 0
- Margin Adjustment = 0
- VAT Addition = 0
- Coefficient = 1

هر تغییر نیازمند Rule جدید است.

### PRC-004 — ورودی دستی پول

عدد دستی کاربر در Chat به‌صورت پیش‌فرض **تومان** است مگر کاربر صریحاً بگوید ریال.

اگر کاربر ریال اعلام کند:

`Toman = ceil(Rial / 10)`.

### PRC-005 — Normalization عدد

فرمت‌های قابل قبول، در صورت بدون ابهام بودن:

- `5,000,000`
- `5.000.000`
- `۵٬۰۰۰٬۰۰۰`
- `5000000`

Digits فارسی/عربی به English Digits Normalize می‌شوند. Format مبهم نباید حدس زده شود.

Normalization فقط برای Interpretation عدد است و raw source text در Canonical IR حفظ می‌شود.

### PRC-006 — Invalid Source Price

قیمت Source با Format غیرقابل تفسیر:

`UNRESOLVED_INVALID_PRICE_FORMAT` -> Blocker.

قیمت Numeric منفی Source:

`UNRESOLVED_INVALID_PRICE` -> Blocker.

`0`, Blank یا `---` در Price field معتبر مطابق `AVL-006` Inactive Signal است و Parse Error محسوب نمی‌شود.

### PRC-007 — Direct Pricing

محصولات Direct از Supplier/Product Code معتبر قیمت می‌گیرند. نمونه خانواده‌های شناخته‌شده:

- Legacy INNOVA
- KOMPAKT
- تجهیزات Smart Home مستقیم

برای Direct Product:

1. Source Record باید `VALID_PRICE` و Match Authoritative داشته باشد.
2. Supplier Price به Internal Rial exact تبدیل می‌شود.
3. Final Toman = `ceil(Rial / 10)`.
4. Trace طبق `PRC-012` ثبت می‌شود.

### PRC-008 — Composite Pricing: Evidente / Crystal / Metal

Approved Business Formula:

`Final Price = Frame + Mechanism`

Frame و Mechanism باید با BOM قطعی Resolve شوند و Source Price هر Component باید به Internal Rial exact تبدیل شود.

Commercial Arithmetic Policy مصوب `v1.2.0`:

`policy_id = COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1`

Operational arithmetic:

1. `frame_rial` و `mechanism_rial` به‌صورت exact Resolve شوند.
2. `total_rial = frame_rial + mechanism_rial`
3. `Final Toman = ceil(total_rial / 10)`

در نتیجه:

`Final Toman = ceil((frame_rial + mechanism_rial) / 10)`

Component-level Conversion/Ceiling قبل از Summation **MUST NOT** انجام شود. الگوریتم زیر برای Runtime Pricing ممنوع است:

`ceil(frame_rial / 10) + ceil(mechanism_rial / 10)`

Historical Regression طبق `DAT-003` این Policy را اثبات نکرده بود؛ Authority این Rule، **Explicit Business Policy Approval for v1.2.0** است.

### PRC-009 — Composite Pricing: New INNOVA / Trône

Approved Business Formula:

`Final Price = Digital Panel + MCE Mechanism`

Panel و MCE Mechanism باید با BOM قطعی Resolve شوند و Source Price هر Component باید به Internal Rial exact تبدیل شود.

Commercial Arithmetic Policy مصوب `v1.2.0` و `PRC-002` اعمال می‌شود:

1. `panel_rial` و `mce_mechanism_rial` به‌صورت exact Resolve شوند.
2. `total_rial = panel_rial + mce_mechanism_rial`
3. `Final Toman = ceil(total_rial / 10)`

در نتیجه:

`Final Toman = ceil((panel_rial + mce_mechanism_rial) / 10)`

Component-level Conversion/Ceiling قبل از Summation **MUST NOT** انجام شود. الگوریتم زیر برای Runtime Pricing ممنوع است:

`ceil(panel_rial / 10) + ceil(mce_mechanism_rial / 10)`

Policy ID همان `COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1` است و تا زمانی که Rule خانوادگی صریح و نسخه‌دار دیگری در آینده تصویب نشود، بر این خانواده حاکم است.

### PRC-010 — Component Missing/Inactive

برای Composite با BOM قطعی:

#### Case A — Component Record موجود ولی Inactive Signal

اگر Component لازم دارای `0`, Blank یا `---` باشد و Source `COMPLETE` باشد:

- محصول Deactivate می‌شود.
- `Price = 0`
- `Sale Price = blank`
- `visibility = hidden`
- این وضعیت به‌خودی‌خود `UNRESOLVED` نیست.

#### Case B — Component Code در Source کامل NOT_FOUND

اگر Component Code مورد انتظار در Source `COMPLETE` یافت نشود و هیچ Evidence مثبت از تغییر BOM/Code/Structure وجود نداشته باشد:

- Component unavailable تلقی می‌شود.
- Composite Product Deactivate می‌شود.
- همان خروجی Case A اعمال می‌شود.

#### Case C — Source ناقص/Unresolved

اگر Source Completeness برابر `INCOMPLETE` یا `UNRESOLVED` باشد، Missing Component حق Deactivation ایجاد نمی‌کند و Source Blocker حاکم است.

### PRC-011 — BOM Conflict

اگر Source Evidence مثبت نشان دهد که برای همان Product/Variant:

- Component Code جدید جایگزین Code ذخیره‌شده شده؛
- Component Role/Structure تغییر کرده؛
- یا BOM ذخیره‌شده با Structure اعلام‌شده Source تناقض دارد،

این وضعیت صرفاً Component NOT_FOUND نیست:

`UNRESOLVED_BOM_CONFLICT` -> Blocker.

سیستم نباید BOM جدید را از روی شباهت حدس بزند یا BOM قدیمی را کورکورانه اعمال کند.

### PRC-012 — Price Provenance / Source Trace

هر Price تغییرکرده باید `price_provenance_type` و Trace کامل داشته باشد.

Types مجاز حداقل:

- `SUPPLIER_DIRECT`
- `SUPPLIER_COMPOSITE`
- `USER_MANUAL_OVERRIDE`
- `EXCLUDED_MANUAL_PRICE`
- `ROLLBACK_HISTORICAL`
- `AUTO_REPAIR`

#### `SUPPLIER_DIRECT`

حداقل:

- Source File Hash
- Source Record Identity
- Source Page/Section
- Supplier Code
- Source Price + Unit
- normalized Rial
- Formula Type
- Rule ID

#### `SUPPLIER_COMPOSITE`

حداقل:

- هر Component Code/Identity
- Source Record Identity هر Component
- Page/Section هر Component
- raw Source Price + Unit هر Component
- normalized Rial هر Component
- BOM ID/Mapping
- `total_rial`
- Composite Pricing Policy ID: `COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1`
- applicable Rule ID (`PRC-008` یا `PRC-009` یا Rule خانوادگی صریح آینده)
- exact Final Formula
- Final Toman result

Runtime Provenance باید نشان دهد که Summation در Rial قبل از تنها Rounding Boundary انجام شده است. Component-level Ceiling قبل از Summation مجاز نیست.

Algorithm A/B comparison MAY فقط در Historical Regression یا Diagnostic Test به‌عنوان analysis technique ثبت شود؛ چنین Comparisonی Runtime Pricing Behavior نیست و هیچ alternative Candidate نباید جای Policy مصوب را بگیرد.

#### `USER_MANUAL_OVERRIDE`

حداقل:

- Decision ID
- User-entered amount
- User-entered unit
- normalized Final Toman
- established Run ID
- Rule ID

#### `EXCLUDED_MANUAL_PRICE`

حداقل همان اطلاعات Manual Decision + Excluded scope evidence.

#### `ROLLBACK_HISTORICAL`

حداقل:

- source historical run_id/version
- historical dataset hash
- historical state hash در صورت وجود
- target row identity

#### `AUTO_REPAIR`

باید Trace underlying authoritative pricing source را به‌علاوه Old Invalid Value و Auto-Fix Rule ثبت کند.

اگر Trace الزامی برای provenance type مربوطه قابل ثبت نباشد:

`UNRESOLVED_SOURCE_TRACE_MISSING` -> Blocker.

### PRC-013 — Final Price Domain

Final `Price` قبل از visibility/discount validation باید:

- Integer Toman
- `>= 0`

باشد.

Negative Final Price یا arithmetic overflow/invalid representation -> Blocker.

`Price=0` یک Availability State معتبر است، نه missing price.

