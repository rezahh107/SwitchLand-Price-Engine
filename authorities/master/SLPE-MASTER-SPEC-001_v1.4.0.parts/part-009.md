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

اگر Match، Source و Formula Authoritative/قطعی باشند، بزرگی تغییر قیمت حتی اگر چند برابر باشد به‌تنهایی Blocker نیست.

### VAL-103 — Activation/Deactivation

برای Existing Product با Price معتبر:

- `0 -> >0` = `ACTIVATED`
- `>0 -> 0` = `DEACTIVATED`
- `>0 -> >0` = `ACTIVE`
- `0 -> 0` = `INACTIVE`

برای Activation/Deactivation Warning درصدی `VAL-101` اعمال نمی‌شود.

اگر Old یا New Price به‌دلیل Blocker معتبر نباشد، Availability status = `UNRESOLVED`.

---

## 14. Discount / Sale Price

### DSC-001 — جداسازی Regular و Sale

`Price` = Regular Price  
`Sale Price` = Discounted Price

Sale Price پیش‌فرض Blank است.

### DSC-002 — Discount Preflight اجباری

قبل از هر Run عادی Price List، شامل `NORMAL`, `RERUN_SAME_SOURCE` و Price-list-based `CORRECTIVE`، باید `DISCOUNT_PREFLIGHT` انجام شود.

اگر Master دارای Sale Price فعال است:

- فهرست محصولات تخفیف‌دار و Sale Price فعلی نمایش داده شود.
- یک تأیید Global برای حفظ/لغو تخفیف‌ها گرفته شود.
- Exception محصولی مجاز باشد.

اگر تخفیف فعال وجود ندارد:

- سؤال شود آیا تخفیف جدیدی برای این Run وجود دارد.

`ROLLBACK` عادی از Discount Preflight مستثنی است و `DSC-011` حاکم است.

### DSC-003 — تخفیف Rule دائمی نیست

تصمیم تخفیف وضعیت تجاری موقت است و به Rulebook دائمی تبدیل نمی‌شود. در Run عادی بعدی Preflight دوباره انجام می‌شود.

### DSC-004 — روش‌های تعریف تخفیف

دو روش:

1. Direct Sale Price
2. Percentage Discount

### DSC-005 — Percentage Discount

درصد روی **Final Regular Price همان Run** اعمال می‌شود، پس از تعیین نهایی Price.

- Decimal تا حداکثر 2 رقم اعشار مجاز است.
- `0%` -> بدون تخفیف -> Blank
- `<0%` یا `>=100%` -> `UNRESOLVED_INVALID_DISCOUNT`
- بیش از 2 رقم اعشار -> `UNRESOLVED_INVALID_DISCOUNT_PRECISION`

Arithmetic:

`raw_sale_price = final_regular_price * (1 - discount_percent / 100)`

با Decimal exact.

Final Sale Price:

`ceil(raw_sale_price)` به Integer Toman.

### DSC-006 — Direct Sale Price

- Fractional Toman -> Ceiling
- `0` -> Blank
- `<0` -> `UNRESOLVED_INVALID_SALE_PRICE`
- `Sale Price >= Final Regular Price` -> `UNRESOLVED_SALE_PRICE_CONFLICT`

ورودی مالی کاربر مطابق `PRC-004` به‌صورت پیش‌فرض Toman است مگر صریحاً Rial اعلام شود.

### DSC-007 — دو تعریف همزمان

اگر Percentage و Direct Sale Price هر دو داده شوند:

- نتیجه Final Toman یکسان -> `DISCOUNT_DEFINITIONS_CONSISTENT`
- نتیجه متفاوت -> `UNRESOLVED_DISCOUNT_DEFINITION_CONFLICT` -> Blocker

### DSC-008 — Sale Price قدیمی در برابر Regular جدید

اگر Sale Price حفظ‌شده پس از تغییر Regular Price، بزرگ‌تر یا مساوی Regular Price شود:

`UNRESOLVED_SALE_PRICE_CONFLICT` -> Blocker -> تصمیم کاربر لازم است.

System حق ندارد Sale Price را خودکار کاهش دهد تا conflict رفع شود.

### DSC-009 — Deactivation

هنگام Deactivation:

- `Sale Price = blank`
- Sale Price قبلی نباید خودکار در Reactivation بعدی برگردد.

### DSC-010 — Auto-Fix محدود Sale Price

Auto-Fix فقط برای اصلاحات ساختاری قطعی مجاز است، مانند:

- `Sale Price = 0 -> blank`
- Deactivation -> `Sale Price = blank`

سیستم حق ندارد بدون تصمیم صریح کاربر تخفیف تجاری جدید بسازد یا مقدار تخفیف را تغییر دهد.

### DSC-011 — Rollback

Rollback عادی Discount Preflight ندارد و Sale Price تاریخی Version مقصد را دقیقاً Restore می‌کند، مگر کاربر صریحاً تغییر تخفیف را درخواست کند.

Current discount Rules نباید Historical Sale Price را Silent Mutate کنند. Rollback Safety فقط مطابق `RLB-*` Non-Mutating است.

### DSC-012 — Existing Sale Price Validation

در Intake/Validation:

- Blank = no active discount.
- Numeric `0` = structural no-discount value و می‌تواند به Blank Auto-Fix شود.
- Negative -> `UNRESOLVED_INVALID_SALE_PRICE`.
- Nonnumeric/malformed -> `UNRESOLVED_INVALID_SALE_PRICE`.
- Positive Sale Price باید پس از Final Regular Price resolution کمتر از Price باشد.

اگر Regular Price خود UNRESOLVED است، Sale Price conflict validation تا resolution آن Pending می‌ماند و Final readiness Blocked است.

---

## 15. Manual Price Override

### OVR-001 — ماهیت Override

برای `SUPPLIER_MANAGED`، کاربر می‌تواند Price دستی تعیین کند.

Override:

- فقط با تصمیم صریح کاربر ایجاد می‌شود.
- در `Product_Pricing_State` به‌صورت پایدار نگهداری می‌شود.
- به‌صورت پیش‌فرض تا زمان لغو یا رویدادهای تعریف‌شده زیر Active است.
