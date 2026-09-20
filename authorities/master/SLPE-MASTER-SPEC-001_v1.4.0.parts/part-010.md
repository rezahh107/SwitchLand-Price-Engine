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
- تفاوت همین Override تازه‌تصویب‌شده با Supplier Price همان Run نباید بلافاصله `MANUAL_OVERRIDE_REVIEW_REQUIRED` ایجاد کند.

`OVR-004` از اولین Source Evaluation بعدی که خارج از همان Decision context است اعمال می‌شود.

### OVR-009 — Override Trace

هر Active/Terminated Override باید حداقل ثبت کند:

- master_key
- override price
- input unit
- normalized Toman
- established Decision ID
- established Run ID
- terminated Decision/Run ID در صورت خاتمه
- reason
- status
- source price observed at establishment در صورت وجود

Override نباید صرفاً با مشاهده Price فعلی Master بازسازی شود.

---

## 16. محصولات EXCLUDED_FROM_PRICE_LIST

### EXC-001 — Price دستی پایدار

اگر کاربر برای Product Excluded قیمت دستی جدید بدهد:

- تغییر مجاز است.
- Price جدید در Proposed State به‌عنوان Excluded Manual Price پایدار ثبت می‌شود.
- پس از Promotion موفق، Price جدید و Product Pricing State جدید Committed می‌شوند.
- Runهای بعدی بدون سؤال آن را حفظ می‌کنند.
- Price List مبنای Warning یا Override Review آن Product نیست.

### EXC-002 — Scope همچنان Excluded

تغییر Price دستی، Product را از `EXCLUDED_FROM_PRICE_LIST` خارج نمی‌کند.

### EXC-003 — visibility و Discount

با وجود Exclusion:

- `Price <-> visibility` Rule سراسری اجرا می‌شود.
- Discount Preflight اجرا می‌شود.
- Master/State Integrity Validation اجرا می‌شود.

Exclusion فقط Supplier-driven Regular Price/Availability logic را محدود می‌کند؛ Ruleهای عمومی Integrity حذف نمی‌شوند.

### EXC-004 — بازگشت به Supplier Managed

اگر کاربر صریحاً Product را به `SUPPLIER_MANAGED` برگرداند، در همان Run:

- Scope transition در Proposed State ثبت می‌شود.
- Excluded Manual Pricing state Terminate می‌شود.
- Repricing کامل با Price List + Rulebook جاری انجام می‌شود.
- Match/Price/BOM ambiguity -> `UNRESOLVED`.
- Commit transition فقط پس از Promotion موفق است.

### EXC-005 — Excluded State Consistency

برای Product Excluded با `excluded_pricing.status=ACTIVE`:

`excluded_pricing.price_toman`

باید با Current Master `Price` سازگار باشد.

تعارض حل‌نشده -> `PRODUCT_PRICING_STATE_MASTER_MISMATCH` -> Blocker.

---

## 17. New Product Workflow

### NEW-001 — عدم Auto-Create

Productی که در Price List وجود دارد ولی در Current Master نیست، خودکار ایجاد نمی‌شود.

### NEW-002 — تصمیم کاربر

برای New Product Candidate، سیستم باید از کاربر بپرسد آیا Product اضافه شود.

Lifecycle جزئی مطابق `NEW-009` است.

### NEW-003 — Skip فقط همان Run

اگر کاربر بگوید «فعلاً اضافه نکن»:

`NEW_PRODUCT_SKIPPED_THIS_RUN`

- Lifecycle -> `SKIPPED_THIS_RUN`
- فقط تا پایان همان Run معتبر است.
- Rule دائمی ساخته نمی‌شود.
- Run بعدی اگر Product همچنان جدید باشد دوباره سؤال می‌شود.
- مانع `technical_import_ready` نیست.

### NEW-004 — روش تکمیل Product جدید

پس از تأیید اضافه‌شدن، یکی از دو روش مجاز است:

1. تکمیل fieldهای لازم در Chat، مرحله‌به‌مرحله؛
2. Upload فایل Excel/CSV کامل توسط کاربر.

در هر دو روش، `new_product_required_fields` profile از `schema_version` فعال معیار Completion است.

اگر data ناقص باشد، `NEW-010` حاکم است.

### NEW-005 — Column1 جدید / Deterministic Allocation

سیستم فقط در Workflow تأییدشده و زمانی که کاربر اجازه Generation را داده است می‌تواند `Column1` تولید کند.

مجموعه:

`all_known_reserved_Column1`

از Union این منابع ساخته می‌شود:

1. تمام `Column1`های Current Master؛
2. تمام `Column1`های موجود در Archive history شناخته‌شده و قابل خواندن؛
3. هر Identifier Registry/Reservation Artifact فعال در صورت وجود.

تمام identifierهای معتبر باید قبل از محاسبه به Numeric integer domain مورد انتظار Validate شوند. Stored values تغییر نمی‌کنند.

الگوریتم دقیق:

1. `candidate = max(all_known_reserved_Column1) + 1`
2. candidate با Current Master compare شود.
3. candidate با known Archive/Reservation set compare شود.
4. اگر collision وجود داشت، `candidate = candidate + 1` و uniqueness check تکرار شود.
5. اولین Candidate که در هیچ شناسه شناخته‌شده وجود ندارد انتخاب شود.
6. شناسه تاریخی شناخته‌شده هرگز reuse نشود.
7. نتیجه و reserved-set fingerprint در Audit ثبت شود.

با reserved set یکسان، الگوریتم باید همیشه Identifier یکسان تولید کند.

اگر Archive history کامل نیست ولی تمام history شناخته‌شده قابل خواندن است:

`WARNING_COLUMN1_HISTORY_INCOMPLETE` ثبت می‌شود و Guarantee فقط نسبت به known reserved set اعلام می‌شود.

اگر:

- reserved set مورد انتظار قابل خواندن نباشد؛
- Identifier domain ناسازگار/نامعلوم باشد؛
- max قابل تعیین deterministic نباشد؛
- یا uniqueness قابل اثبات نباشد،

`UNRESOLVED_COLUMN1_GENERATION` -> `UNRESOLVED` -> Blocker.

### NEW-006 — SKU جدید

`Column2 / SKU` نباید خودکار ساخته شود مگر:

- کاربر SKU بدهد، یا
- Rule تولید SKU را صریحاً تأیید کند.

تا آن زمان Product approved برای creation کامل محسوب نمی‌شود.

### NEW-007 — Part Number Suggestion

Part Number Reference می‌تواند برای Product جدید تأییدشده مقدار fieldها را Suggest کند، اما هر field پیشنهادی نیازمند تأیید است.

Suggestion بدون Approval حق Mutation Row ندارد.

### NEW-008 — Append Only

فقط Row کامل، تأییدشده و دارای Lifecycle `READY_TO_APPEND` می‌تواند Append شود.

Row جدید:

- فقط در انتهای Dataset قرار می‌گیرد.
