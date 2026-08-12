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

برای Composite Product، پس از اینکه BOM معتبر، Source Evidence معتبر و تمام Component Priceها Resolve شدند، Final Price به‌صورت deterministic طبق `PRC-002` و Rule خانوادگی `PRC-008/PRC-009` محاسبه می‌شود. پس از Establish شدن این Final Price، downstream Rules شامل Availability، `visibility`, Discount validation، Warning threshold و Price Provenance به‌صورت عادی اجرا می‌شوند.

## 12. Availability و visibility

### AVL-001 — Price منبع حقیقت visibility

Rule سراسری برای **همه محصولات** پس از تعیین یک Final Price معتبر:

- `Price > 0 -> visibility = visible`
- `Price = 0 -> visibility = hidden`

اگر Final Price Blocked/Unresolved باشد، سیستم نباید visibility را از روی حدس تغییر دهد.

### AVL-002 — مقادیر مجاز visibility

خروجی فقط این دو مقدار را می‌پذیرد:

- `visible`
- `hidden`

هر مقدار Blank، `catalog`، `search` یا مقدار دیگر، فقط وقتی Final Price معتبر است بر اساس `AVL-001` Auto-Fix می‌شود.

### AVL-003 — Mixed State

ناسازگاری Final Price معتبر با visibility با `PRICE_VISIBILITY_SYNC` Auto-Fix می‌شود و در Audit ثبت می‌شود.

این Auto-Fix صرفاً synchronization است و حق تعیین Price را ایجاد نمی‌کند.

### AVL-004 — Deactivation بر اساس عدم حضور

Generic `NOT_FOUND` Deactivation فقط وقتی مجاز است که تمام شروط زیر برقرار باشند:

1. Product committed Scope = `SUPPLIER_MANAGED`.
2. `source_completeness_status=COMPLETE`.
3. Match result برای Product = `NOT_FOUND`.
4. هیچ Rule تخصصی‌تر incompatibility مانند `CLR-003` یا `PRC-011` فعال نباشد.
5. Absence ناشی از Parser/Normalization/Authority Conflict نباشد.

اگر همه برقرار باشند:

- `Price = 0`
- `Sale Price = blank`
- `visibility = hidden`
- availability transition مطابق `REC-002` ثبت می‌شود.
- Manual Override active مطابق `OVR-002` در Proposed State Terminate می‌شود.

### AVL-005 — Reactivation

اگر محصول `SUPPLIER_MANAGED` قبلاً غیرفعال باشد و اکنون Price معتبر با Match Authoritative در Price List داشته باشد:

- Price جدید Price List اعمال می‌شود.
- `visibility = visible`.
- availability = `ACTIVATED`.
- Sale Price قبلی خودکار برنمی‌گردد.
- Manual Override قبلی که به‌دلیل Deactivation Terminate شده بود خودکار برنمی‌گردد.

### AVL-006 — Source Zero/Blank/---

برای Variant یا Component معتبر، `0`, Blank یا `---` در Price field شناخته‌شده، `INACTIVE_SIGNAL` است، نه Parse Error.

برای Existing `SUPPLIER_MANAGED` Product یا Composite affected by such signal:

- Deactivation rules اعمال می‌شوند.
- Source Trace باید exact inactive record/component را نشان دهد.

### AVL-007 — Availability Precedence

Precedence برای تصمیم Availability:

1. Explicit specialized incompatibility/conflict Rule مانند `CLR-003`, `PRC-011`.
2. Explicit Source `INACTIVE_SIGNAL`.
3. Exact Component unavailable rule `PRC-010`.
4. Generic Product `NOT_FOUND` مطابق `AVL-004`.
5. Reactivation مطابق `AVL-005`.

Rule با Severity Blocker باید قبل از mutation availability حل شود؛ Generic NOT_FOUND حق مخفی‌کردن Conflict تخصصی را ندارد.

---

## 13. هشدار تغییر قیمت

### VAL-101 — Threshold و Formula

Warning تغییر قیمت فقط وقتی محاسبه می‌شود که:

- `old_price > 0`
- `new_price > 0`

Formula با Decimal exact:

`percentage_change = abs(new_price - old_price) / old_price * 100`

Threshold روی مقدار Decimal محاسبه‌شده قبل از هر formatting نمایشی اعمال می‌شود.

اگر:

`percentage_change > 50`

آنگاه:

`WARNING_PRICE_CHANGE_GT_50_PERCENT`

ثبت می‌شود.

این Warning باید Old Price، New Price و Percentage را نشان دهد.

`50%` دقیق Warning نیست؛ فقط **بیش از 50٪** Warning است.

### VAL-102 — Non-Blocking بودن تغییر بزرگ
