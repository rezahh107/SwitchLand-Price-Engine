هیچ Drift خارج Mutation Authority رخ ندهد.

Fields خارج Allowlist فقط با Rule/Approval مستقل مجاز به تغییر هستند.

### NFR-004 — Safety

ابهام واقعی باعث توقف mutation پرریسک و Final readiness شود، نه توقف تحلیل سایر رکوردهای مستقل.

Mass Deactivation فقط از Source `COMPLETE` و Ruleهای صریح مجاز است.

### NFR-005 — Usability

کاربر برای فهم Summary یا Blocker نیاز به خواندن Technical Code نداشته باشد؛ توضیح ساده اجباری است.

Technical Detail برای Audit باید همزمان حفظ شود.

### NFR-006 — Recovery / Rollback

هر Historical Version دارای `IMPORT_SUCCESS + PROMOTED` یک `rollback_source_candidate` است.

Executable بودن Rollback تضمین‌شده نیست.

Rollback فقط وقتی `IMPORT_READY` می‌شود که Current Non-Mutating Safety Gates را Pass کند.

Historical payload برای عبور از Safety Gate نباید Silent Mutate شود.

اگر exact rollback unsafe باشد، Corrective/Migration Run با Approval صریح لازم است.

### NFR-007 — Concurrency Safety

تغییر Current Master/Product Pricing State/External Authority در میانه Run باید شناسایی شود.

Stale Run نباید Current Master جدیدتر را در Promotion overwrite کند.

### NFR-008 — Import Integrity

Output Format باید با exact Version/Hash Import Contract سازگار بماند.

Promotion باید به Artifact Import‌شده با قوی‌ترین Evidence در دسترس Bind شود و Assurance Level را صادقانه ثبت کند.

### NFR-009 — Replayability

Run باید با Archive Canonical و Authority snapshots قابل replay باشد، بدون نیاز به conversational memory یا re-interpretation PDF توسط Rule Engine.

### NFR-010 — State Integrity

Scope/Override/Excluded commercial state باید از Product Pricing State versioned خوانده شود و فقط از طریق commit semantics مصوب تغییر کند.

### NFR-011 — Chronology Integrity

Source ordering نباید از timestamps غیرSupplier یا حدس filename ساخته شود.

Unknown-date success باید Chronology state را تا Resolution/Re-anchor صریح کند.

### NFR-012 — No Partial Commit

Import Partial یا ambiguous Production mutation نباید Subsetی از Output را Current Master/State رسمی کند.

### NFR-013 — Canonical Serialization

Canonical CSV و Master Fingerprint باید independent از UI metadata و implementation-specific formatting غیرضروری باشند و با inputs یکسان Hash یکسان بدهند.

---

## 34. Readiness Statement برای Rulebook v1

پس از تصویب صریح Commercial Composite Arithmetic Policy در `v1.2.0`:

> **No known unresolved business-policy or deterministic control-semantic blocker remains for beginning Rulebook v1 implementation.**

Composite Rounding Policy اکنون Normative است:

`Final Toman = ceil(sum(component_rial_i) / 10)`

و Component-level Ceiling قبل از Summation ممنوع است.

Provenance این تصمیم باید صریح بماند:

- Historical Evidence: `NOT_PROVEN_BY_AVAILABLE_HISTORICAL_EVIDENCE`
- Policy Authority: `ALGORITHM_A_EXPLICITLY_APPROVED`
- First Normative Specification Version: `v1.2.0`

`UNRESOLVED_COMPOSITE_ROUNDING_POLICY` دیگر Active Runtime/Specification Blocker نیست و فقط در Historical/Deprecated provenance نگهداری می‌شود.

این Status فقط به معنی **آمادگی Specification برای آغاز Rulebook v1 implementation** است. این سند ادعا نمی‌کند Rulebook ساخته شده، Regression/Acceptance Suite PASS شده یا Production آماده است.

Architecture و Hardeningهای `v1.1.1` خارج از این Policy Surface بدون تغییر substantive معتبر می‌مانند.

## 35. برنامه مرحله بعد

با بسته‌شدن آخرین Specification-level Policy Blocker، مرحله بعد به Rulebook implementation بازمی‌گردد:

1. `SWITCHLAND_PRICE_ENGINE_RULEBOOK.json` v1 از Master Specification `v1.2.0` ساخته شود.
2. exact Schema 95 ستونی و `new_product_required_fields` profile تثبیت شود.
3. WP All Import Contract Version/Snapshot شود.
4. bootstrap `Product_Pricing_State.json` فقط از Evidence قطعی ساخته شود.
5. Canonical Source Extractor Contract و `Normalized_Source_Catalog.json` fixtures/contracts ساخته شوند.
6. Regression/Acceptance fixtures ساخته شوند، شامل Synthetic Discriminating Composite Arithmetic Fixture طبق `ACC-007`.
7. Historical Regression اجرا شود؛ `DAT-003` به‌عنوان evidence non-discriminating حفظ شود.
8. Mapping/BOM فقط بر اساس Evidence Authoritative تکمیل و Validate شود.
9. Full Acceptance Suite اجرا شود، از جمله deterministic replay طبق `ACC-026`.
10. Live Price List فقط پس از PASS شدن Baseline و Acceptance Gate پردازش شود.

این سند ادعا نمی‌کند Rulebook، Regression Suite یا Production Run در این مرحله اجرا شده‌اند.

## 36. Change Log سند مادر

### v1.2.0 — 2026-08-12

**وضعیت:** `READY_FOR_RULEBOOK_IMPLEMENTATION`

**نوع Version:** MINOR

دلیل MINOR:

این Revision یک Commercial Arithmetic Policy صریح و دائمی را تصویب می‌کند که Final Price واقعی Composite Products را تعیین می‌کند؛ بنابراین صرفاً Clarification/PATCH نیست. Core architecture و سایر Business Policies ناسازگار نشده‌اند، پس MAJOR لازم نیست.

#### Final Policy Resolution

- Composite Rounding تنها Specification-level blocker باقی‌مانده در `v1.1.1` بود.
- Historical Regression در `DAT-003` Algorithm A و B را از هم تفکیک نکرد: 381 target-family rows، 206 strict-resolvable pairs، 0 discriminating rows، 196 historical rows matching both، 10 neither، 175 insufficient strict trace، و 0 exclusive support برای هر Algorithm.
- بنابراین Historical Evidence همچنان `NOT_PROVEN_BY_AVAILABLE_HISTORICAL_EVIDENCE` است و بازنویسی نشده است.
- Business Owner در `v1.2.0` Algorithm A را صریحاً تصویب کرد: `ALGORITHM_A_EXPLICITLY_APPROVED`.
- Policy Normative: `sum exact Rial components -> one final ceil(total_rial / 10)`.
- Component-level Rial-to-Toman Conversion/Ceiling قبل از Summation ممنوع است.
- `PRC-002`, `PRC-008`, `PRC-009`, `PRC-012`, `PRC-013`, `DAT-003`, `ACC-003`, `ACC-007`, `ACC-026`, Technical Code Registry, Readiness و Next Stage فقط در سطح لازم اصلاح شدند.
- `UNRESOLVED_COMPOSITE_ROUNDING_POLICY` از Active Technical Code Registry بازنشسته شد؛ `deprecated_in=1.2.0` و فقط Historical provenance آن حفظ می‌شود. Rulebook v1 conforming to v1.2.0 نباید این Code را emit کند.
- `ACC-007` یک Synthetic Discriminating Acceptance Fixture اضافه می‌کند که Algorithm A و B را واقعاً از هم متمایز می‌کند، بدون اینکه به‌عنوان Historical Evidence معرفی شود.
- Status به `READY_FOR_RULEBOOK_IMPLEMENTATION` بازگشت. این Status به معنی Production readiness یا PASS شدن Regression/Acceptance نیست.

#### Protected Architecture

تمام Hardeningهای unrelated `v1.1.1` از جمله Preserve-by-default، Least Mutation، Severity separation، Product Pricing State، Source Completeness/Chronology، Canonical Source IR، Authority Pinning، Run/Record orthogonal state، EXACT_SKU pricing restriction، Full Reprocess، Rollback Safety، Import Artifact Binding، Production Reconciliation Guard، No Partial Promotion، Canonical CSV/Fingerprint و Acceptance architecture بدون تغییر substantive حفظ شدند.

### v1.1.1 — 2026-08-12

**وضعیت:** `HARDENED_BUT_NOT_RULEBOOK_READY`

**نوع Version:** PATCH

دلیل PATCH:

