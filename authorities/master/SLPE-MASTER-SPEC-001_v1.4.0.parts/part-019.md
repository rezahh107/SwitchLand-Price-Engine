1. اعلام کند آماده اجرای Price Update Run است.
2. آخرین Price List را درخواست کند.
3. Current Master، Product Pricing State و Authority Versionها را Load/Pin کند.
4. Source Hash/Date/Chronology/Completeness را Validate کند.
5. در صورت وجود تخفیف قبلی و Run مشمول Discount Preflight، پس از Load Master وارد `DISCOUNT_PREFLIGHT` شود.
6. از کاربر سؤال فرضی یا غیرمسدودکننده نپرسد.
7. فقط در صورت Blocker واقعی یا Decision تجاری الزام‌آور تصمیم بخواهد.
8. Blocker را با توضیح ساده + Technical Detail طبق بخش 31 ارائه دهد.
9. سؤال‌های Runtime را تا حد ممکن فقط به Decisionی محدود کند که بدون آن Outcome material نامعلوم می‌ماند.

وجود Warning نباید به سؤال غیرضروری تبدیل شود.

---

## 33. معیارهای کیفیت غیرعملکردی

### NFR-001 — Determinism

با Execution Context Canonical یکسان، Output semantic، Canonical CSV bytes، Proposed Product Pricing State و Technical outcomes باید قابل تکرار باشند.

### NFR-002 — Auditability

هر تغییر Price/State/Availability/Discount باید از Artifactهای Archive تا Source/Decision/Rule قابل ردیابی باشد.

### NFR-003 — Data Preservation

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

### v1.4.0 — 2026-08-20

**وضعیت:** `NORMATIVE_SUCCESSOR_ACTIVE_WHEN_SELECTED`

- مدل Approval فنی مبتنی بر Token/Hash با یک تأیید انسانی ساده برای کل Workflow جایگزین شد.
- دستور طبیعی و صریح کاربر برای اجرا، همان Approval کافی است و نیاز به قالب خاص ندارد.
- تمام عملیات غیرتخریبی مشتق‌شده تا پایان همان هدف، بدون تأیید مجدد پوشش داده می‌شوند.
- Hash، CAS، Revision، Resource ID، Read-back و Duplicate Detection به کنترل داخلی و Audit ماشینی تبدیل شدند.
- Approval تازه فقط برای تغییر مادی اثر تجاری، عملیات تخریبی/غیرقابل‌بازگشت، Production write اعلام‌نشده یا ابهام واقعی کسب‌وکاری لازم است.
- تمام قواعد قیمت، SKU، Part Number، Promotion، Evidence و Reconciliation خارج از این سطح بدون تغییر حفظ شدند.

### v1.3.0 — 2026-08-16

**وضعیت:** `READY_FOR_RULEBOOK_IMPLEMENTATION`

- مسیر محدود `RUN_SCOPED_PARTIAL_PROMOTION_EXCEPTION` افزوده شد؛ Import Outcome همچنان `PARTIAL` می‌ماند و Promotion فقط برای Full Dataset مصوب همراه با exact open-reconciliation queue مجاز است.
- وضعیت `PROMOTED_WITH_OPEN_RECONCILIATION` افزوده شد تا Promotion استثنایی با Success کامل اشتباه نشود.
- `CONFIRM_DRIVE_BATCH_MUTATION` به‌عنوان یک Approval تجمیعی متصل به immutable `mutation_plan_sha256` افزوده شد.
- Resource IDهای provider-assigned و Artifactهای مشتق deterministic داخل Plan می‌توانند بدون Approval مجدد bind شوند.
- CAS، Expected-parent، Read-back، Hash verification، immutable history و ممنوعیت عملیات تخریبی خارج از Plan بدون تغییر حفظ شدند.
- استثناء عمومی Partial Promotion ایجاد نشد؛ Scope جدید، خطای جدید، Hash mismatch، CAS conflict یا Mutation خارج از Plan همچنان Approval/Resolution جدید می‌خواهد.

### v1.2.0 — 2026-08-12

**وضعیت:** `READY_FOR_RULEBOOK_IMPLEMENTATION`

**نوع Version:** MINOR

دلیل MINOR:

این Revision یک Commercial Arithmetic Policy صریح و دائمی را تصویب می‌کند که Final Price واقعی Composite Products را تعیین می‌کند؛ بنابراین صرفاً Clarification/PATCH نیست. Core architecture و سایر Business Policies ناسازگار نشده‌اند، پس MAJOR لازم نیست.

#### Final Policy Resolution
