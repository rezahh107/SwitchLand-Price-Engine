### CLR-003 — رنگ پشتیبانی‌نشده و Precedence

`CLR-003` از Generic `AVL-004` تخصصی‌تر است.

اگر سیستم به‌صورت مثبت و Authoritative تعیین کند که:

- Series مربوطه در Source کامل وجود دارد؛
- Master Variant/Series identity قطعی است؛
- اما Color همان Master Variant در Price List جاری برای آن Series پشتیبانی نمی‌شود،

نتیجه:

`UNRESOLVED_COLOR_NOT_SUPPORTED` -> Blocker.

این وضعیت نباید Silent به `NOT_FOUND -> DEACTIVATED` تبدیل شود.

Generic `AVL-004` فقط وقتی اعمال می‌شود که هیچ Rule incompatibility تخصصی‌تر مانند `CLR-003` فعال نباشد.

### PN-001 — کاربرد Part Number Reference

Part Number Reference فقط برای:

- Matching
- Parsing Structure
- Validation
- Suggestion برای New Product پس از تأیید ایجاد

است.

Part Number Reference حق تعیین Price یا Availability جاری را ندارد.

### PN-002 — عدم Auto-Fill روی محصول موجود

Part Number Reference حق ندارد فیلدهای موجود Master را به‌صورت خودکار پر یا اصلاح کند.

### PN-003 — Conflict با Master

اگر semantics Part Number با Master در موضوعی که Matching/Validation را materially تغییر می‌دهد conflict داشته باشد:

`UNRESOLVED_PART_NUMBER_CONFLICT` -> توضیح دقیق Conflict -> Blocker.

### PN-004 — Missing Part Number

اگر Part Number خالی باشد ولی Match از روش دقیق دیگر قطعی باشد:

`WARNING_MISSING_PART_NUMBER` -> Non-Blocking.

اگر نبود Part Number باعث ابهام Match شود -> `UNRESOLVED`.

### PN-005 — Versioning مرجع Part Number

هر نسخه جدید Part Number Reference باید:

- Hash شود.
- Archive شود.
- با نسخه Active Diff شود.
- فقط پس از تأیید صریح Active شود.

Conflict نسخه جدید با Rulebook فعلی:

`PART_NUMBER_REFERENCE_CONFLICT`

تا زمان حل Conflict، نسخه قبلی Active می‌ماند.

این Conflict به‌خودی‌خود Import جاری را Block نمی‌کند اگر نسخه قبلی Active و Pin‌شده همچنان معتبر باشد؛ Activation نسخه جدید Block می‌شود.

### PN-006 — Mid-Run Version Change

Part Number Reference فعال در شروع Run Pin می‌شود.

اگر Version Active در میانه Run تغییر کند:

- ادامه Silent ممنوع است.
- اگر نسخه جدید Approved/Active باشد، Full Dataset Reprocess از Original Master Snapshot + Canonical Source IR انجام می‌شود.
- Final Output فقط یک `part_number_reference_version` دارد.

---

## 11. قوانین قیمت‌گذاری

### PRC-001 — واحد Source

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
