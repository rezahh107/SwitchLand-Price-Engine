---
document_id: SLPE-MASTER-SPEC-001
system_name: SwitchLand Price Engine
version: 1.2.0
status: READY_FOR_RULEBOOK_IMPLEMENTATION
issued_at: 2026-08-12
language: fa-IR
canonical_format: Markdown
supersedes: "SLPE-MASTER-SPEC-001 v1.1.1"
owner: SwitchLand
---

# سند مادر سیستم به‌روزرسانی قیمت SwitchLand
## SwitchLand Price Engine — Master Specification

**نسخه:** `1.2.0`  
**وضعیت:** `READY_FOR_RULEBOOK_IMPLEMENTATION`  
**تاریخ:** `2026-08-12`  
**شناسه سند:** `SLPE-MASTER-SPEC-001`

---

## 1. هدف سند

این سند مرجع مادر، نسخه‌دار و قابل استناد برای طراحی، پیاده‌سازی، آزمون و بهره‌برداری از **SwitchLand Price Engine** است. هدف سیستم این است که فهرست قیمت جدید تأمین‌کننده/NESTECH را دریافت کند، آن را به‌صورت کنترل‌شده و قابل حسابرسی با Master Dataset فروشگاه تطبیق دهد، قیمت‌های مستقیم و ترکیبی را محاسبه کند، وضعیت فعال/غیرفعال و تخفیف را مطابق قوانین مصوب تعیین کند و در نهایت فایل کامل سازگار با WP All Import و گزارش حسابرسی تولید کند.

این سند نتیجه تجمیع تصمیم‌های تأییدشده در جلسات طراحی و Hardening ممیزی پیش از پیاده‌سازی است. جزئیات کم‌ریسک و غیرتجاری که هنوز صریحاً تعیین نشده‌اند، باید با رویکرد محافظه‌کارانه و مشابه کنترل‌های نرم‌افزارهای مالی/حسابداری انتخاب شوند؛ اما هیچ تصمیمی که بتواند قیمت، تخفیف، هویت محصول، ساختار Master، Scope، Availability، State تجاری یا Promotion را به‌صورت مبهم تغییر دهد، نباید حدس زده شود.

### 1.1 جایگاه این سند

این سند:

- مرجع الزامات و رفتار سیستم است.
- مبنای ساخت `SWITCHLAND_PRICE_ENGINE_RULEBOOK.json` خواهد بود.
- مبنای Regression Test و Acceptance Test است.
- مرجع تعیین State Ownership، Run Semantics، Archive، Promotion و Rollback است.
- برای نگهداری در پایگاه دانش مناسب است.
- جایگزین Rulebook اجرایی نیست؛ Rulebook باید الزامات این سند را به قواعد ماشین‌خوان تبدیل کند.

### 1.2 زبان الزامی

در این سند:

- **MUST / الزامی:** سیستم حق عدول ندارد مگر با تغییر نسخه‌دار و تأییدشده سند/Rulebook.
- **MUST NOT / ممنوع:** رفتار به‌طور صریح ممنوع است.
- **SHOULD / توصیه‌شده:** رفتار پیش‌فرض است مگر دلیل مستند برای استثناء وجود داشته باشد.
- **MAY / مجاز:** رفتار اختیاری و غیرمسدودکننده است.

### 1.3 دامنه بازنگری v1.1.0

نسخه `v1.1.0` سیاست‌های تجاری تأییدشده `v1.0.0` را حفظ می‌کند و برای حذف ابهام اجرایی این موارد را Operational می‌کند:

- Source Completeness و Source Chronology؛
- Product Pricing State پایدار؛
- Run/Record Stateهای Orthogonal؛
- Canonical Source Intermediate Representation؛
- Authority Version Pinning؛
- Deterministic Column1 Allocation؛
- Rollback Safety Gate؛
- Promotion Artifact Binding؛
- Canonical CSV Serialization و Master Fingerprint؛
- Acceptance Criteria قابل آزمون و Replay.

### 1.4 دامنه بازنگری v1.2.0

نسخه `v1.2.0` تنها Blocker سیاستی باقی‌مانده در `v1.1.1` را با **تصمیم صریح Business Owner** می‌بندد: برای همه Composite Products، Componentها ابتدا به Internal Rial exact Normalize و در Rial جمع می‌شوند؛ سپس کل مبلغ Composite دقیقاً یک‌بار با `ceil(total_rial / 10)` به Toman تبدیل می‌شود. این تصمیم از Historical Regression استنتاج نشده است؛ `DAT-003` همچنان Non-Discriminating بودن Evidence تاریخی را ثبت می‌کند. سایر Architecture و Hardeningهای `v1.1.1` بدون تغییر substantive حفظ می‌شوند.

---

## 2. فلسفه طراحی و اصول حاکم

### GOV-001 — حفظ داده به‌صورت پیش‌فرض

اصل پایه سیستم:

> **PRESERVE EVERYTHING UNLESS AN EXPLICIT RULE AUTHORIZES A CHANGE.**

هر فیلدی که مجوز تغییر صریح ندارد باید دقیقاً حفظ شود.

### GOV-002 — کمینه‌سازی سطح تغییر

سیستم باید از اصل **Least Mutation** استفاده کند. در حالت عادی، تنها فیلدهای مجاز برای تغییر عبارت‌اند از:

- `Price`
- `Sale Price`
- `visibility`
- ردیف جدید، فقط پس از تأیید صریح و تکمیل داده‌های لازم
- تغییر Schema، فقط پس از تأیید صریح

هر تغییر دیگری ممنوع است مگر Rule مستقل و تأییدشده وجود داشته باشد.

### GOV-003 — Fail-Closed فقط در ابهام واقعی

سیستم نباید برای هر ناهنجاری Run را متوقف کند. رفتار سه‌سطحی الزامی است:

1. `AUTO_FIX` — نتیجه قطعی است؛ سیستم اصلاح می‌کند و Audit می‌نویسد.
2. `WARNING` — مورد غیرعادی است ولی تصمیم اشتباه محتمل نیست؛ Run ادامه می‌یابد.
3. `BLOCKER / UNRESOLVED` — اطلاعات کافی برای تصمیم قطعی وجود ندارد یا خطر ورود داده غلط واقعی است؛ `technical_import_ready=false`.

وجود Blocker نباید مانع پردازش سایر رکوردهای مستقل شود؛ اما تا رفع همه Blockerها هیچ Artifact نباید `IMPORT_READY` اعلام شود.

### GOV-004 — عدم حدس در تصمیم‌های تجاری

نام مشابه، شباهت معنایی، حدس از روی توضیحات یا قرینه ناقص نباید به‌تنهایی باعث تغییر Price، Sale Price، SKU، Unique Identifier، BOM، Scope، Availability یا Product Pricing State شود.

### GOV-005 — ردپای حسابرسی کامل

هر قیمت محاسبه‌شده یا تغییر تجاری باید قابل ردیابی تا منبع، Rule، فرمول، State و تصمیم کاربر باشد.

### GOV-006 — تاریخچه تغییرناپذیر

نسخه‌ها، Runها، Rulebookهای قبلی، Product Pricing Stateهای قبلی و خروجی‌های قبلی نباید بازنویسی تاریخی شوند. اصلاحات با نسخه/Run جدید انجام می‌شوند.

### GOV-007 — ارتقای تراکنشی Current Master

`SwitchLand_Current_Master` فقط پس از موفقیت قطعی Import و عبور از Promotion Gate باید به وضعیت جدید ارتقا یابد. Draft، Import ناموفق، Import Partial یا Promotion Blocked حق تغییر Current Master را ندارد.

Promotion یک Commit منطقی واحد است و باید حداقل این موارد را هماهنگ کند:

- Current Master version/pointer؛
- `Product_Pricing_State` committed version؛
- Source baseline/chronology metadata؛
- Run promotion metadata.

اگر تمام اجزای Commit قابل تکمیل نباشند، Promotion کامل محسوب نمی‌شود و Recovery/Reconciliation الزامی است.

برای `NO_CHANGES`، پس از Validation کامل و تأیید کاربر، داده Current Master تغییر نمی‌کند؛ فقط Run History و Source baseline metadata مطابق Rules مربوطه پیش می‌روند.

### GOV-008 — محاسبات قطعی

برای قیمت، درصد تخفیف و تبدیل واحد پول از محاسبات Decimal/Integer قطعی استفاده شود؛ محاسبات Float باینری برای مقادیر پولی ممنوع است.

### GOV-009 — کنترل مجموع‌ها و Reconciliation
