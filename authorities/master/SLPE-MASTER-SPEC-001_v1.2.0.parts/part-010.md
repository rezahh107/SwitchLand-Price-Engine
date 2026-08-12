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
- Price List همچنان Authority Availability است.

### OVR-002 — Override بر Availability غلبه نمی‌کند

اگر محصول از Price List `COMPLETE` معتبر حذف شود یا Price Source `0`/Blank/`---` شود:

- محصول طبق Availability Rules Deactivate می‌شود.
- Override در Proposed State Terminate می‌شود.
- `Price = 0`
- `Sale Price = blank`
- `visibility = hidden`

Termination فقط پس از Promotion موفق Committed می‌شود.

### OVR-003 — بازگشت محصول

اگر محصولی که Override آن به‌دلیل Deactivation Terminate شده بود، بعداً با Price معتبر برگردد:

- Price جدید Price List اعمال می‌شود.
- Override قدیمی خودکار برنمی‌گردد.
- Sale Price قدیمی خودکار برنمی‌گردد.

### OVR-004 — Source Price جدید متفاوت با Override

اگر Override committed Active است و Product همچنان در Source معتبر موجود است ولی Final Supplier Price جدید با `manual_override.price_toman` متفاوت است:

`MANUAL_OVERRIDE_REVIEW_REQUIRED`

- Product `UNRESOLVED`
- Current Master values تا تصمیم کاربر حفظ می‌شود.
- سایر محصولات پردازش می‌شوند.
- `technical_import_ready=false`.

سیستم باید Manual Override Price، Supplier Price جدید، Source Trace و گزینه‌های تصمیم را نشان دهد.

### OVR-005 — Source برابر Override

اگر Final Supplier Price جدید دقیقاً برابر Override Active باشد:

- Override Active می‌ماند.
- Conflict ایجاد نمی‌شود.
- در Run بعدی اگر Source متفاوت شود `OVR-004` دوباره اجرا می‌شود.

### OVR-006 — Reason

Reason برای Override اختیاری است. در صورت نبود:

`reason = USER_NOT_SPECIFIED`

Decision ID و established Run ID همچنان الزامی‌اند.

### OVR-007 — لغو Override در میانه Run

اگر کاربر صریحاً Override را لغو کند:

- در Proposed State همان لحظه Inactive/Terminated محسوب می‌شود.
- همان Run Price Product از Price List + Rulebook جاری Recompute می‌شود.
- نیاز به انتظار تا Run بعدی نیست.
- اگر Current Source Price معتبر نباشد، Availability/Unresolved Rules عادی اعمال می‌شوند.
- authoritative committed Override فقط پس از Promotion موفق Terminate می‌شود.

اگر Run Promote نشود، committed Override قبلی برای Run آینده همچنان Active باقی می‌ماند؛ Decision/attempt در Manifest Run ناموفق حفظ می‌شود.

### OVR-008 — Manual Override ایجادشده در همان Run

اگر کاربر در همان Run با مشاهده/اطلاع از Supplier Price صریحاً Manual Override جدیدی تعیین کند:

- Override در Proposed State `ACTIVE` می‌شود.
- Price دستی همان Run اعمال می‌شود.
