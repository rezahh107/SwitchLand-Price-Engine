- `component_structure`
- `extraction_status`
- `normalization_result`

`source_value_state` حداقل یکی از:

- `VALID_PRICE`
- `INACTIVE_SIGNAL`
- `NON_PRICE_RECORD`
- `PARSE_ERROR`

باشد.

`INACTIVE_SIGNAL` برای Source values معتبر مانند `0`, Blank یا `---` فقط در Contextی استفاده می‌شود که Rule آن field را به‌عنوان Price/Availability field شناخته باشد.

### SRC-IR-004 — Source Record Identity

`source_record_identity` باید replay-deterministic باشد.

الگوریتم:

`SHA256(source_file_hash || page_index || section_identity || record_ordinal_within_section || raw_supplier_code || raw_source_price_text)`

Concatenation باید با Separator ثابت و UTF-8 deterministic انجام شود.

### SRC-IR-005 — Replay Boundary

برای Replay Rule Engine، مجموعه Input Authority باید حداقل شامل:

- `Normalized_Source_Catalog.json`
- Master Snapshot
- `Product_Pricing_State_Start.json`
- Rulebook Snapshot/Version
- Schema Snapshot/Version
- Part Number Reference Snapshot/Version
- Import Contract Version
- Run-scoped User Decisions

باشد.

با این Inputs یکسان، Rule Engine باید Candidate Dataset و Proposed Product Pricing State یکسان تولید کند.

### SRC-IR-006 — Canonical IR Audit

Run Manifest باید ثبت کند:

- `normalized_source_catalog_hash`
- `extractor_version`
- `engine_version`
- Source completeness check results یا Artifact reference آنها.

Rule Engine و Source Extractor باید بتوانند مستقل Test شوند.

---

## 8. Scope محصولات و Product Pricing State

### SCP-001 — SUPPLIER_MANAGED

محصولی است که قیمت و Availability آن از Price List NESTECH مدیریت می‌شود.

تخصیص اولیه این Scope فقط در صورت اتصال قطعی از طریق یکی از این موارد مجاز است:

- Supplier/Product Code قطعی
- SKU قطعی
- BOM قطعی
- Permanent Mapping تأییدشده

Name-Only برای تخصیص Scope کافی نیست.

`SKU قطعی` در این Rule فقط Evidence مجاز برای **Scope assignment** است. این عبارت به‌تنهایی مجوز استفاده از WooCommerce `Column2 / SKU` به‌عنوان Authoritative Price List → Master Match Method ایجاد نمی‌کند. Pricing Matching فقط از Match Methodهای صریح `MAT-001` و `MAT-011` مجاز است.

### SCP-002 — EXCLUDED_FROM_PRICE_LIST

محصولی است که Regular Price آن به‌صورت دستی مدیریت می‌شود.

- Price List حق تغییر خودکار Price آن را ندارد.
- نبودن در PDF باعث Deactivation نمی‌شود.
- `visibility` همچنان از Rule سراسری Price پیروی می‌کند.
- Discount Preflight همچنان شامل آن می‌شود.

### SCP-003 — Prime / White Prime

در وضعیت فعلی، Prime/`WS / White Prime` در Scope ایجاد/قیمت‌گذاری خودکار سایت نیست و باید بدون Rule جدید نادیده گرفته شود.

### SCP-004 — تغییر Scope

تغییر `EXCLUDED_FROM_PRICE_LIST` به `SUPPLIER_MANAGED` فقط با دستور صریح کاربر ممکن است. در همان Run:

- Exclusion در Proposed State لغو می‌شود.
- قیمت دستی پایدار قبلی در Proposed State Terminate می‌شود.
- Match/Price با Price List و Rulebook جاری دوباره محاسبه می‌شود.
- ابهام -> `UNRESOLVED`.
- Commit State جدید فقط پس از Promotion موفق انجام می‌شود.

### PPS-001 — Product Pricing State Authority

Scope و Lifecycle قیمت دستی باید در Artifact نسخه‌دار:

`Product_Pricing_State.json`

نگهداری شوند.

Current Master `Price` به‌تنهایی نباید برای تشخیص این موارد استفاده شود:

- آیا محصول `SUPPLIER_MANAGED` است یا Excluded؛
- آیا Price فعلی Manual Override است؛
- آیا Override قبلاً Terminate شده است؛
- چرا قیمت دستی ایجاد شده است.

### PPS-002 — Minimum State Schema

State هر Master Product حداقل باید مفهوم زیر را داشته باشد:

```json
{
  "master_key": "Column1 exact value",
  "pricing_scope": "SUPPLIER_MANAGED|EXCLUDED_FROM_PRICE_LIST",
  "scope_established_run_id": "...",
  "scope_established_decision_id": "...",
  "scope_reason": "...",
  "manual_override": {
    "status": "NONE|ACTIVE|TERMINATED",
    "price_toman": null,
    "established_run_id": null,
    "established_decision_id": null,
    "terminated_run_id": null,
    "terminated_decision_id": null,
    "reason": null
  },
  "excluded_pricing": {
    "status": "INACTIVE|ACTIVE|TERMINATED",
    "price_toman": null,
    "established_run_id": null,
    "established_decision_id": null,
    "terminated_run_id": null,
    "terminated_decision_id": null,
    "reason": null
  },
  "provenance": {
    "last_committed_run_id": "...",
    "last_committed_at": "..."
  }
}
```

`master_key` همان `Column1` است و باید با Exact stored value مرتبط شود.

State format اجرایی MAY جزئیات بیشتری داشته باشد، اما نباید semantics فوق را حذف کند.

### PPS-003 — Versioning

Product Pricing State دارای:

- `product_pricing_state_version`
- Artifact `SHA-256`
- `based_on_master_version`
- `committed_run_id`
- `committed_at`

است.

Versionهای قبلی Immutable و قابل بازیابی هستند.

### PPS-004 — Staged vs Committed State

در شروع Run:

- State committed فعلی Snapshot می‌شود.
- Version/Hash آن Pin می‌شود.

هر Transition ناشی از:

- Source disappearance؛
- Reactivation؛
- Manual Override lifecycle؛
- Excluded manual price؛
- Scope transition؛

ابتدا فقط در `Product_Pricing_State_Proposed.json` ثبت می‌شود.

Runtime-derived State Transition در Draft/Validated/Import-ready Run **نباید Authoritative شود** تا Run:

- واقعاً Import شود؛
- `import_outcome=SUCCESS` داشته باشد؛
- و Promotion Gate کامل شود.

اگر Run `IMPORT_FAILED`, `IMPORT_PARTIAL`, `REJECTED`, `SUPERSEDED` یا Promotion Blocked شود، Committed Product Pricing State قبلی دست‌نخورده می‌ماند.

استثناء فقط Permanent Rule Decisionهای مشمول `RBK-003` هستند؛ آن‌ها Rulebook State هستند، نه Product Runtime State.

### PPS-005 — Master/State Consistency

در شروع Run، State committed باید با Current Master Validate شود.

حداقل:

- هر `master_key` State باید به حداکثر یک Master Row اشاره کند.
- Active Manual Override Price باید با Current Master `Price` همان محصول سازگار باشد.
- Active Excluded Manual Price باید با Current Master `Price` سازگار باشد.
- Scope نباید با Permanent Rule فعال متناقض باشد.

اگر State و Master درباره State تجاری قابل استفاده اختلاف داشته باشند و Rule deterministic برای Resolution وجود نداشته باشد:

`PRODUCT_PRICING_STATE_MASTER_MISMATCH` -> Blocker.

سیستم نباید Price را به‌تنهایی برای بازسازی State انتخاب کند.

### PPS-006 — Scope Completeness

برای هر Existing Master Product که ممکن است تحت این Engine قرار گیرد، committed `pricing_scope` باید معلوم باشد.

اگر Scope معتبر وجود ندارد و Exact Evidence مطابق `SCP-001` امکان تخصیص قطعی نمی‌دهد:

`UNRESOLVED_SCOPE` -> Blocker برای Final Import readiness.

سیستم می‌تواند سایر محصولات را پردازش کند.

### PPS-007 — Excluded Price Authority

برای `EXCLUDED_FROM_PRICE_LIST`:

- Current Master `Price` مقدار اجرایی فعلی است.
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
