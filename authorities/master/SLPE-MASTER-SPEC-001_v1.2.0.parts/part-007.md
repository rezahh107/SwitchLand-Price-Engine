- `excluded_pricing.price_toman` State تجاری پایدار و provenance آن را نگه می‌دارد.
- پس از Promotion این دو باید برابر باشند.

Price List Supplier نباید این Price را تغییر دهد یا Warning تغییر Supplier Price برای آن ایجاد کند.

### PPS-008 — Manual Override Authority

برای `SUPPLIER_MANAGED` با Override Active:

- `manual_override.status=ACTIVE`
- `manual_override.price_toman` قیمت تجاری دستی است.
- Availability همچنان از Supplier Source کنترل می‌شود.
- Rules `OVR-*` lifecycle را تعیین می‌کنند.

### PPS-009 — Promotion Commit

Promotion Run دارای Changes باید Proposed State و Output Dataset را با یک Parent مشترک Commit کند.

حداقل Manifest باید نشان دهد:

- Product Pricing State Start Version/Hash؛
- Proposed State Hash؛
- Committed State Version/Hash؛
- Parent Master Version؛
- Promoted Master Version.

اگر Dataset Promote شود ولی State commit قابل تکمیل نباشد، Promotion موفق اعلام نمی‌شود و `PRODUCTION_RECONCILIATION_REQUIRED` فعال می‌شود.

### PPS-010 — Historical State Availability

از `v1.1.0` به بعد هر Version موفق دارای Product Pricing State Snapshot قابل بازیابی است.

برای Historical Versionهای قدیمی که چنین State Artifactی ندارند، عدم وجود State باید صریحاً ثبت شود و Rollback مطابق `RLB-007` رفتار کند.

---

## 9. Matching Engine

### MAT-001 — اولویت Matching

ترتیب پایه:

1. `EXPLICIT_PERMANENT_MAPPING`
2. `EXACT_SUPPLIER_CODE`
3. `EXACT_COMPONENT_BOM_CODE`
4. `APPROVED_ALIAS`
5. `NAME_SUGGESTION_ONLY`

این Priority فقط بین Evidenceهای سازگار اعمال می‌شود و Conflict واقعی را Override نمی‌کند.

`EXACT_SKU` در Rulebook v1 یک Authoritative Pricing Match Method نیست. `v1.0.0` SKU را صریحاً به‌عنوان Evidence ممکن برای Scope assignment در `SCP-001` پذیرفته بود، اما آن را در Matching Priority قرار نداده بود. Historical evidence موجود نیز Supplier Source را در Supplier/Product Code domain نشان می‌دهد و اثبات نمی‌کند WooCommerce `Column2 / SKU` یک Source-side SKU domain مستقل و Authoritative برای Pricing Match است.

بنابراین Rulebook v1 MUST NOT با تطبیق صرف `Column2 / SKU` به یک Source identifier Price را Apply کند. افزودن `EXACT_SKU` به Authoritative Matching در آینده نیازمند Evidence یا Approval صریح و Specification Revision است.

### MAT-002 — Conflict بین شواهد قطعی

Priority نباید Conflict واقعی را پنهان کند. اگر Permanent Mapping و Evidence جدید قطعی با هم متناقض باشند:

`UNRESOLVED_MATCH_CONFLICT` -> Blocker.

همین رفتار برای هر دو Authoritative Match Evidence متناقض اعمال می‌شود.

### MAT-003 — Name Suggestion

نام Product فقط برای Suggestion مجاز است؛ Name-Only هرگز مجوز Auto Price، Scope assignment یا Product identity mutation نیست.

### MAT-004 — Supplier Code Normalization

برای Compare:

- Trim ابتدا/انتها
- Case-Insensitive
- حفظ Hyphen
- حفظ Digits
- حفظ Internal Spaces

سیستم نباید `i200` را با `i-200` برابر بداند مگر Mapping صریح وجود داشته باشد.

Stored code/case نباید با normalization مقایسه‌ای بازنویسی شود.

### MAT-005 — Source Code Duplicate با قیمت/Specification متفاوت

اگر یک normalized Supplier Code در Canonical Source IR چند Record داشته باشد و Effective Source Specification طبق `MAT-006` متفاوت باشد:

`UNRESOLVED_SOURCE_CONFLICT` -> هیچ قیمت انتخاب نمی‌شود -> Blocker.

این Rule شامل Duplicate Code با Price متفاوت است.

### MAT-006 — Source Code Duplicate یکسان / Effective Source Specification

برای تشخیص Duplicate Supplier Record سازگار، Effective Source Specification دقیقاً از این fieldهای normalized تشکیل می‌شود:

- `normalized_supplier_code`
- `family`
- `series`
- `normalized_variant`
- `normalized_color`
- `source_currency_unit`
- `source_value_state`
- `normalized_source_price_rial` برای `VALID_PRICE`
- `component_role`
- Canonical `component_structure` signature

Location fields مانند Page/Section و raw formatting بخشی از Effective Specification نیستند و می‌توانند بین Duplicateهای معنایی متفاوت باشند.

اگر Code و تمام fieldهای Effective Source Specification یکسان باشند:

`DUPLICATE_SOURCE_RECORD_CONSISTENT` -> Logical Deduplication -> Non-Blocking Audit.

اگر هر field مؤثر متفاوت باشد:

`UNRESOLVED_SOURCE_CONFLICT` -> Blocker.

Rulebook باید Canonicalization `component_structure` را deterministic تعریف کند.

### MAT-007 — یک Source Code برای چند Master SKU

بدون One-to-Many Mapping صریح:

`UNRESOLVED_SOURCE_TO_MULTIPLE_PRODUCTS` -> Blocker.

Shared Component Code در چند BOM مستقل، One-to-Many Direct Product Mapping محسوب نمی‌شود اگر نقش Component در BOMهای صریح تعریف شده باشد.

### MAT-008 — چند Source Code برای یک Master SKU

بدون Primary/Alias یا BOM صریح:

`UNRESOLVED_MULTIPLE_SOURCES_FOR_PRODUCT` -> Blocker.

### MAT-009 — توضیح Conflict

هر Conflict باید علاوه بر Technical Code، توضیح ساده شامل این موارد داشته باشد:

- کدام Product
- انتظار سیستم چه بوده
- چه داده‌های متناقضی پیدا شده
- منبع هر Evidence چیست
- چرا سیستم نمی‌تواند تصمیم بگیرد
- اثر هر انتخاب چیست
- دقیقاً چه تصمیمی از کاربر لازم است
- پیشنهاد سیستم با Confidence: `High/Medium/Low`، در صورت امکان

Confidence فقط برای Recommendation انسانی است و حق Mutation ایجاد نمی‌کند.

پیشنهاد سیستم بدون تأیید نباید اعمال شود.

### MAT-010 — Match Result State

هر Existing Product در Matching stage باید یک `match_result` مشخص داشته باشد:

- `MATCHED`
- `NOT_FOUND`
- `SUGGESTION_ONLY`
- `CONFLICT`
- `UNRESOLVED`

`NOT_FOUND` فقط یعنی هیچ Match معتبر در Canonical Source IR پیدا نشده است؛ به‌تنهایی مجوز Deactivation نیست. Deactivation نیازمند Scope، Source Completeness و precedence Rules است.

### MAT-011 — Authoritative Match Evidence

عبارت «Match قطعی» فقط زمانی معتبر است که Match Method یکی از Classهای machine-defined و Rulebook-whitelisted باشد.

Classهای Authoritative مجاز برای Rulebook v1:

- `EXPLICIT_PERMANENT_MAPPING`
- `EXACT_SUPPLIER_CODE`
- `EXACT_COMPONENT_BOM_CODE`
- `APPROVED_ALIAS` فقط با Alias mapping صریح

`EXACT_SKU` برای Pricing Match در Rulebook v1 **غیرفعال و غیرمجاز** است؛ SKU همچنان می‌تواند مطابق `SCP-001` Evidence تخصیص Scope باشد.

`NAME_SUGGESTION_ONLY` هرگز Authoritative نیست.

هر Match Event باید ثبت کند:

- Match Method
- Master Key/SKU
- Source Record Identity یا Mapping ID
- Rule ID
- Evidence trace

---

## 10. رنگ و Part Number

### CLR-001 — Color Aliases

Mapping دائمی Matching:

- `W` -> سفید
- `B` -> مشکی
- `G` -> Beige / طلایی
- `S` -> نقره‌ای
- `A` -> آنتراسیت
- `WS / White Prime` -> خارج از Scope فعلی

این Mapping فقط برای Matching/Variant Interpretation است و متن ذخیره‌شده Master را بازنویسی نمی‌کند.

### CLR-002 — رنگ قابل عرضه

اگر Part Number Reference رنگی را از نظر ساختاری مجاز بداند ولی Price List جاری آن رنگ را برای آن Series ارائه نکند، Part Number نباید باعث Active شدن Variant شود. Price List جاری مرجع Availability فعلی `SUPPLIER_MANAGED` است.
