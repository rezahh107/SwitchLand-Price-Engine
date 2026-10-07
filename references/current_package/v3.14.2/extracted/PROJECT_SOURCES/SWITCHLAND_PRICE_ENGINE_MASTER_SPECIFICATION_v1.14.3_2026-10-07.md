---
document_id: SLPE-MASTER-SPEC-001
system_name: SwitchLand Price Engine
version: 1.14.3
status: NORMATIVE_SUCCESSOR_ACTIVE_WHEN_SELECTED
issued_at: 2026-10-06
language: fa-IR
canonical_format: Markdown
supersedes: "SLPE-MASTER-SPEC-001 v1.14.2"
owner: SwitchLand
release_patch_type: SEMANTIC_ROOT_CAUSE_HARDENING
activation_model: WORKSPACE_MANIFEST_PLUS_CURRENT_SET
predecessor_resource_id: 1dFWORnaW9vsQw7jThucjOUP8U4yrcZM_
predecessor_sha256: cecd4b40de0add6d3323e87fb27f781466b69a87f614e580fadad4a8b04c1cfe
semantic_change: PROVIDER_NATIVE_DOWNSTREAM_NO_CODE_INTEGRITY_ROOT_CLOSURE
---

# سند مادر سیستم به‌روزرسانی قیمت SwitchLand
## SwitchLand Price Engine — Master Specification

**نسخه:** `1.14.3`  
**وضعیت:** `ACTIVE_CURRENT_SET_AUTHORITY`  
**تاریخ:** `2026-10-06`  
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

### 1.5 دامنه بازنگری v1.5.0

نسخه `v1.5.0` یک Root-Cause Governance Hardening است و هیچ Pricing Formula، Product Decision، Master data، Product Pricing State، WordPress/WooCommerce Production data یا Import behavior تجاری را تغییر نمی‌دهد. این نسخه دو نقص ساختاری را می‌بندد:

- Source completeness از سطح count/coverage به **حفظ یک‌به‌یک ردیف فیزیکی price-bearing** ارتقا می‌یابد؛
- `CURRENT_SET` به‌عنوان Live Selector یگانه‌ی Domain/Runtime Authority دارای **Conformance Lock** می‌شود و هیچ active/current projection حق نگهداری identity قدیمی ندارد.

همچنین Project Instructions فقط Orchestration، Safety، Evidence discipline، Routing و Interaction را اداره می‌کند و حق Override کردن Business/Domain semantics انتخاب‌شده توسط live `CURRENT_SET` را ندارد. Historical/change-log references فقط وقتی می‌توانند نسخه قدیمی را نگه دارند که نقش تاریخی آنها صریح باشد.

### 1.6 دامنه بازنگری v1.14.3

نسخه `v1.14.3` یک Root-Correct Runtime Integrity Hardening برای محیط
`CHATGPT PROJECT + LANGUAGE MODEL + CONNECTED APPS/PROVIDERS + NO CODE EXECUTION`
است. این نسخه model-computed SHA/fingerprint را از **normal runtime prerequisite** برای Current Master concurrency، Final Output binding، Product Pricing State lifecycle و provider-native CAS حذف می‌کند و safety proposition همان کنترل‌ها را با `resource_id + provider_revision_id + targeted read-back + expected-parent comparison` حفظ می‌کند.

Hashهای از قبل Persist/Build/History/Authority یا checksum خارجی واقعی همچنان Evidence معتبرند و حذف نمی‌شوند. این نسخه همچنین residueهای Source SHA در projectionهای فعال v1.13 را می‌بندد. Pricing، Matching، Availability، Discount، SKU/Variation business policy، Current Master data، committed PPS data، Import Contract behavior و Production state تغییر تجاری نمی‌کنند.

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

`SwitchLand_Current_Master` فقط پس از عبور از Promotion Gate باید به وضعیت جدید ارتقا یابد. مسیر عادی به موفقیت قطعی Import نیاز دارد. مسیر استثنایی محدود `RUN_SCOPED_PARTIAL_PROMOTION_EXCEPTION` فقط مطابق `GOV-007A` مجاز است. Draft، Import ناموفق، Import Partial فاقد استثنای معتبر یا Promotion Blocked حق تغییر Current Master را ندارد.

Promotion یک Commit منطقی واحد است و باید حداقل این موارد را هماهنگ کند:

- Current Master version/pointer؛
- `Product_Pricing_State` committed version؛
- Source baseline/chronology metadata؛
- Run promotion metadata.

اگر تمام اجزای Commit قابل تکمیل نباشند، Promotion کامل محسوب نمی‌شود و Recovery/Reconciliation الزامی است.

برای `NO_CHANGES`، پس از Validation کامل و تأیید کاربر، داده Current Master تغییر نمی‌کند؛ فقط Run History و Source baseline metadata مطابق Rules مربوطه پیش می‌روند.

### GOV-007A — استثنای محدود Promotion برای Import Partial

Partial Promotion عمومی همچنان ممنوع است. یک Run با `import_outcome=PARTIAL` فقط زمانی MAY Promote شود که تمام شرایط زیر هم‌زمان و قابل Audit باشند:

- Owner Decision نسخه‌دار و persisted دقیقاً به `run_id`، `run_version`، **exact Final Output provider artifact identity** (`provider + resource_id + provider_revision_id`) و فهرست بسته‌ی Subjectهای استثناء متصل باشد؛
- تمام Errorها/Warningهای اجرایی شناسایی و به Subjectهای همان فهرست بسته محدود باشند و خطای ناشناخته یا خارج از Scope وجود نداشته باشد؛
- Final Full Dataset، Artifact Binding، Expected Parent Master/PPS provider identities، Product Pricing State پیشنهادی و تمام Gateهای غیرمرتبط با Partial PASS باشند؛
- برای هر Subject، وضعیت مطلوب، وضعیت مشاهده‌شده، اقدام deferred و Reconciliation Queue ثبت شده باشد؛
- `import_outcome` همچنان `PARTIAL` باقی بماند و به‌اشتباه `SUCCESS` نام‌گذاری نشود؛
- Promotion با `promotion_status=PROMOTED_WITH_OPEN_RECONCILIATION` ثبت شود؛
- Queue باز در Current Set/Manifest/Run evidence حمل شود و در Runهای بعدی به‌عنوان known exact-subject exception ارزیابی شود؛ Subject یا Error جدید خودکار مشمول استثناء نیست؛
- هیچ حذف، Merge، تغییر هویت حدسی یا پنهان‌کردن Failure واقعی مجاز نباشد.

Historical Owner Decisions که قبلاً artifact hash را ثبت کرده‌اند immutable evidence باقی می‌مانند؛ normal no-code runtime برای Decision جدید نیازمند محاسبه hash تازه نیست. اگر exact provider artifact identity یا required parent identity قابل اثبات نباشد، Partial Promotion Block می‌شود.

این مسیر فقط وضعیت Dataset مصوب و Lineage را Promote می‌کند؛ به معنی اثبات اصلاح Subjectهای deferred در Production نیست.

### GOV-007B — یک تأیید انسانی ساده برای کل فرایند

هدف این کنترل، گرفتن یک تصمیم واقعی و قابل‌فهم از Owner است؛ نه انتقال کنترل‌های فنی سیستم به کاربر.

برای هر Workflow با یک هدف مشخص، قواعد زیر الزامی است:

- Read، بررسی، ممیزی، Validation، ساخت Draft و تهیه Preview به Approval جداگانه نیاز ندارند.
- دستور صریح کاربر برای اجرای همان کار، مانند «اجرا کن»، «اعمال کن»، «تأیید است» یا عبارت طبیعی هم‌معنا، همان **یک تأیید نهایی** محسوب می‌شود. Token، قالب فنی، Hash، Resource ID، CAS، Revision یا عبارت قراردادی خاص از کاربر مطالبه نمی‌شود.
- اگر Workflow پیش از اجرا نیازمند نمایش Summary باشد، Summary باید حداکثر در پنج نکته ساده و غیرتخصصی بگوید: هدف چیست، چه چیزهایی تغییر می‌کنند، چه چیزهایی تغییر نمی‌کنند، آیا حذف یا اثر غیرقابل‌بازگشت وجود دارد، و آیا نسخه قبلی حفظ می‌شود. سپس فقط یک سؤال ساده مانند «اجرا شود؟» مطرح می‌شود.
- همان تأیید، تمام عملیات غیرتخریبی لازم برای پایان همان هدف را پوشش می‌دهد؛ از جمله ساخت و Upload فایل‌های جانشین، Folder، Current Master، PPS، `CURRENT_SET`، Workspace Manifest، Receipt، Validation، Inventory، Bundle، ثبت شناسه‌های Provider، Read-back، Retry و بازسازی Artifactهای مشتق.
- جزئیات فنی مانند Mutation Plan، Hash، CAS، Expected Parent، Revision، Resource ID، Duplicate Detection و Read-back کنترل داخلی ماشین هستند. سیستم باید آن‌ها را خودکار بررسی و در Audit ثبت کند؛ اما نباید از Owner بخواهد صحت آن‌ها را تأیید کند.
- تغییر تعداد فایل‌های مشتق، اختصاص Resource ID توسط Provider، تغییر Hash ناشی از bind شدن همان IDها، شماره‌گذاری Successor و Retry فنی، تا وقتی هدف و اثر تجاری تغییر نکرده‌اند، Approval جدید نمی‌خواهند.
- Hash/CAS/Read-back failure مجوز ادامه نیست؛ سیستم باید در محدوده امن Retry یا Rebuild کند و در غیر این صورت متوقف شود و علت را به زبان ساده گزارش دهد. کاربر نباید برای پذیرش یک ناسازگاری فنی سؤال شود.
- تصمیم انسانی تازه فقط زمانی لازم است که اثر تجاری یا دامنه کار عوض شود، حذف/Trash/Overwrite تاریخی یا عمل غیرقابل‌بازگشت جدید مطرح شود، Write به Production خارج از Summary قبلی اضافه شود، یا ابهام واقعی کسب‌وکاری وجود داشته باشد. سؤال جدید نیز باید غیرتخصصی، مشخص و گزینه‌محور باشد.
- عملیات تخریبی خارج از تأیید عمومی باقی می‌مانند و فقط با یک توضیح ساده درباره اثر و امکان بازیابی، یک تأیید انسانی مستقل می‌خواهند.

Legacy/persisted `mutation_plan_sha256` از Runهای تاریخی MAY فقط به‌عنوان historical evidence نگهداری شود. برای plan جدید در normal no-code runtime، identity برابر structured Mutation Plan provider resource/revision + run/scope + targets + inventory + read-back است؛ hash تازه prerequisite نیست. هیچ token/Hash/Resource ID/CAS syntax از Owner مطالبه نمی‌شود.

### GOV-008 — محاسبات قطعی

برای قیمت، درصد تخفیف و تبدیل واحد پول از محاسبات Decimal/Integer قطعی استفاده شود؛ محاسبات Float باینری برای مقادیر پولی ممنوع است.

### GOV-009 — کنترل مجموع‌ها و Reconciliation

هر Run باید علاوه بر کنترل رکوردی، Control Totalهای مستقل و قابل Reconcile ثبت کند، حداقل:

- تعداد کل ردیف‌های Master در شروع؛
- تعداد کل ردیف‌های Output؛
- تعداد Priceهای معتبر/صفر/خالی/نامعتبر؛
- مجموع `Price` قبل و بعد؛
- تعداد `visible` و `hidden`؛
- تعداد Sale Price فعال؛
- تعداد `change_status`ها؛
- تعداد `availability_status`ها؛
- تعداد رکوردها بر اساس `max_issue_severity`؛
- تعداد Issue Eventها بر اساس Technical Code؛
- run-scoped Source binding و Source conservation/reconciliation state؛
- Current Master provider resource/revision start و fresh gate checks؛
- PPS committed/proposed provider identities و read-back state؛
- Final Output provider identity/read-back؛
- selected Rulebook/Import Contract authority identity.

Persisted/build/history hashes یا checksum خارجی واقعی MAY به‌عنوان Evidence اضافی ثبت شوند، اما normal no-code runtime نباید برای بستن این Audit hash جدیدی محاسبه کند.

Control Totalهای محورهای مستقل نباید به‌اشتباه با هم جمع شوند. برای مثال یک رکورد می‌تواند هم `UPDATED` و هم `ACTIVATED` و هم دارای `WARNING` باشد.

اختلاف غیرمنتظره در Control Total باید در Audit توضیح داده شود و اگر Reconciliation هویت/تعداد/مقادیر را نامطمئن کند، Blocker است.

### GOV-010 — عدم استفاده مجدد از شناسه شناخته‌شده

برای `Column1`، شناسه‌ای که در Current Master یا نسخه‌های آرشیوی شناخته‌شده استفاده شده است نباید عمداً دوباره استفاده شود.

Allocation شناسه جدید باید مطابق الگوریتم قطعی `NEW-005` انجام شود. اگر پوشش Archive کامل نباشد، محدودیت تضمین در Audit ثبت می‌شود؛ اگر مجموعه شناسه‌های شناخته‌شده قابل خواندن/اعتبارسنجی نباشد و Safe Generation قابل اثبات نباشد، تولید شناسه Block می‌شود.

### GOV-011 — CURRENT_SET Authority Coherence Lock

`CURRENT_SET` Live Selector یگانه برای Domain/Business/Runtime Authority است. هر Artifact یا Projection که خود را `ACTIVE`, `CURRENT`, `NORMATIVE_CURRENT`, `ACTIVE_CURRENT_SET_AUTHORITY` یا معادل آن معرفی می‌کند MUST با identity انتخاب‌شده در live `CURRENT_SET` منطبق باشد.

Conformance حداقل باید برای این Carrierها بررسی شود:

- filename/version identity؛
- `CURRENT_SET` selected version/resource identity و persisted authority hash فقط وقتی selector آن را واقعاً فراهم می‌کند؛
- `rulebook_identity.version` و immediate predecessor/supersedes identity؛
- `source_specification` identity؛
- active `normative_business_authority` projection؛
- active route/purpose version references؛
- Workspace Manifest current-state و next-phase projection؛
- package source bindings.

Mismatch در هر Carrier فعال/current برابر `CURRENT_AUTHORITY_IDENTITY_DRIFT` و Structural/Package Qualification = `FAIL` است. Count یا newest-looking filename جای identity binding را نمی‌گیرد. Referenceهای تاریخی، change-log و compilation provenance MAY نسخه قبلی را نگه دارند فقط اگر `historical_role` یا نقش تاریخی هم‌ارز به‌صورت صریح ثبت شده باشد.

Successor Authority باید ابتدا Persist شود، از Provider دوباره خوانده شود و resource/revision/content قابل مشاهده آن در حد capability واقعی verify شود؛ persisted/build hash موجود MAY به‌عنوان Evidence اضافی consume شود ولی normal runtime محاسبه hash تازه prerequisite نیست. سپس `CURRENT_SET` successor آن را انتخاب می‌کند. Artifact تاریخی committed بازنویسی نمی‌شود.

### GOV-012 — Project Instructions Authority Boundary

Project Instructions فقط این Scopeها را اداره می‌کند: Orchestration، Safety، Evidence discipline، Routing و Interaction behavior.

Live Domain/Business semantics از Authorityهایی می‌آید که live `CURRENT_SET` انتخاب کرده است. Project Instructions MUST NOT:

- Pricing، Matching، Availability، Discount، SKU/Part Number/BOM، Product State، Import semantics یا سایر Business Ruleها را به‌عنوان Shadow Authority تعریف کند؛
- نسخه یا خلاصه‌ی ثابت قدیمی را بر Master/Rulebook/Contract جدید منتخب `CURRENT_SET` مقدم کند؛
- در Conflict، semantic جاری منتخب `CURRENT_SET` را Override کند.

اگر متن توضیحی Domain در Project Instructions با Authority زنده تعارض داشت، متن Instructions برای Domain `NON_NORMATIVE_SHADOW_IGNORED` است و semantic زنده منتخب `CURRENT_SET` حاکم می‌ماند.

---

## 3. محدوده سیستم

### 3.1 داخل محدوده

سیستم برای این کارها طراحی می‌شود:

- دریافت PDF فهرست قیمت جدید NESTECH/تأمین‌کننده.
- بررسی تاریخ، Hash، کامل‌بودن، Chronology و قابلیت استخراج منبع.
- تولید Canonical Source Intermediate Representation.
- استخراج کد محصول، رنگ، قیمت و ساختارهای ترکیبی.
- تطبیق با Master Dataset فروشگاه.
- محاسبه قیمت مستقیم و ترکیبی.
- تبدیل ریال به تومان.
- مدیریت غیرفعال/فعال‌شدن محصولات تحت مدیریت تأمین‌کننده.
- مدیریت تخفیف از طریق Discount Preflight.
- مدیریت Manual Override و Excluded Products.
- نگهداری Product Pricing State نسخه‌دار.
- تشخیص محصولات جدید و اخذ تصمیم کاربر.
- تولید Full Dataset برای WP All Import.
- تولید گزارش Audit و Run Manifest.
- Versioning، Archive، Rollback، Recovery و Promotion.
- Regression/Acceptance Replay با Artifactهای Canonical.

### 3.2 خارج از محدوده پیش‌فرض

سیستم به‌صورت خودکار نباید:

- نام محصول را بازنویسی کند.
- توضیحات، Attributeها، Taxonomy، ACF یا محتوای بازاریابی را تغییر دهد.
- SKU را از روی نام حدس بزند.
- محصول جدید را بدون تأیید بسازد.
- Prime/White Prime را صرفاً به‌دلیل حضور در PDF وارد سایت کند.
- Margin، Markup، مالیات یا ضریب فروش اضافه کند مگر Rule جدید صریحاً تصویب شود.
- موجودی انبار را تغییر دهد.
- حذف فیزیکی محصول یا ردیف انجام دهد.
- State تجاری را صرفاً از Price جاری حدس بزند.
- Historical Rollback Payload را برای عبور از Validationهای جاری Silent Mutate کند.

---

## 4. اجزای داده و منابع مرجع

### SRC-001 — Current Master

منبع رسمی داده فروشگاهی در شروع هر Run فایل/Google Sheet پایدار با نام مفهومی:

`SwitchLand_Current_Master`

است. Current Master فقط نماینده آخرین وضعیت داده‌ای موفق و تأییدشده است و به‌تنهایی برای بازسازی Scope یا Manual Override کافی نیست.

### SRC-002 — Google Sheet موجود

Master فعلی از Google Sheet زیر منشأ گرفته است:

`https://docs.google.com/spreadsheets/d/1vV2oVN4rDt6zzTNUDZToumoXgo4nePH7rRfUPRH2JBQ/edit?usp=sharing`

نام مشاهده‌شده: `Import - Sheet1`.

### SRC-003 — Successful Import Fixture

فایل CSV موفق تاریخی و Log موفق WP All Import، Fixture مرجع برای Regression Test هستند. Fixture فعلی دارای:

- 95 Header واقعی
- 826 Data Row در نمونه موفق
- Encoding: `UTF-8 BOM`
- Delimiter: `;`

تعداد ردیف 826 یک **Baseline تاریخی** است، نه Contract دائمی. Contract اصلی 95 ستون و ترتیب/نام دقیق Headerها است.

### SRC-004 — Supplier Price List

PDF جاری تأمین‌کننده مرجع قیمت و عرضه جاری محصولات `SUPPLIER_MANAGED` است، فقط در حدود Evidence موجود در Source و با رعایت Source Completeness/Chronology Rules.

هیچ Deactivation مبتنی بر `NOT_FOUND` بدون `source_completeness_status=COMPLETE` مجاز نیست.

### SRC-005 — Part Number Reference

سند Part Number NESTECH فقط مرجع ساختار و معنی کدها برای Matching/Validation است. این سند:

- مرجع قیمت نیست.
- مرجع قطعی موجودبودن در Price List جاری نیست.
- حق Auto-Fill روی ردیف موجود Master ندارد.
- باید در هر Run با Version + provider resource identity و persisted authority hash در صورت وجود Pin شود.

Runtime حق ندارد برای Pin کردن این Authority hash تازه بسازد. Activation نسخه جدید فقط پس از persisted read-back، Diff قابل اتکا و تأیید صریح مجاز است.

### SRC-006 — WP All Import Mapping/Settings

تنظیمات WP All Import بخشی از Import Contract هستند. نگاشت‌های شناخته‌شده شامل:

- `Column1` به‌عنوان Unique Identifier
- `Column2` به‌عنوان WooCommerce SKU
- `Column27` برای نوع محصول
- `Column8` برای گروه‌بندی Variation
- `visibility` برای Catalog Visibility

قبل از بهره‌برداری Production، Mapping باید در Regression/Acceptance Test تثبیت، Versioned و Snapshot شود.

هر Run باید حداقل `import_contract_version`، `resource_id` و persisted selector hash در صورت وجود را ثبت کند. normal runtime برای این Pin نیازمند محاسبه hash تازه نیست. تغییر Import Contract در میانه Run بدون Revalidation ممنوع است.

### SRC-007 — Product Pricing State Authority

State تجاری پایدار Scope، Manual Override و Excluded Pricing در Artifact نسخه‌دار:

`Product_Pricing_State.json`

نگهداری می‌شود.

این Artifact، Authority وضعیت تجاری است؛ Current Master Authority مقادیر Data Row است. تعارض بین این دو باید مطابق `PPS-005` Fail-Closed شود.

### SRC-008 — Canonical Source Representation

ورودی Canonical Rule Engine از Supplier Source:

`Normalized_Source_Catalog.json`

است که مطابق `SRC-IR-*` ساخته، Versioned و به run-scoped Source evidence bind می‌شود. PDF همچنان Evidence اصلی است، اما Rule Engine برای Replay باید از Representation Canonical و Versioned استفاده کند.

Normal no-code Source Completeness/row identity به runtime-generated hash این Representation وابسته نیست. Independently-owned persisted/build checksum MAY برای integrity همان Artifact نگهداری شود، اما Source Completeness را self-certify نمی‌کند.

### SRC-009 — Authority Order by Decision Domain

Authority به‌صورت Domain-specific است و یک فهرست مطلق ساده نیست:

- Current Master: مقادیر فعلی Dataset و هویت ردیف.
- Product Pricing State: Scope و Manual/Excluded pricing lifecycle.
- Supplier Price List + Canonical Source IR: قیمت و Availability جاری `SUPPLIER_MANAGED`.
- Part Number Reference: ساختار/معنی کد برای Matching/Validation، نه Price/Availability.
- Permanent Mapping/Rulebook: Rules و Mappingهای صریح.
- User Decisions: تصمیم‌های Run-scoped یا Permanent مطابق Scope ثبت‌شده.
- Historical Successful Versions: Authority فقط برای Historical Audit/Rollback source candidate.
- Import Contract: Authority تفسیر Output توسط WP All Import.

اگر دو Authority معتبر در یک Domain تعارض داشته باشند و Rule صریح حل تعارض وجود نداشته باشد، سیستم نباید Priority عمومی اختراع کند و باید Conflict را Fail-Closed کند.

---

## 5. قرارداد Master Dataset

### MST-001 — Schema ثابت

خروجی عادی باید دقیقاً 95 ستون با نام و ترتیب Header مطابق Fixture موفق و `schema_version` فعال داشته باشد.

هر یک از موارد زیر موجب `MASTER_SCHEMA_MISMATCH` می‌شود:

- Header اضافه
- Header حذف‌شده
- تغییر نام Header
- تغییر ترتیب Header

تا زمان تأیید کاربر و ایجاد `schema_version` جدید، `technical_import_ready=false`.

### MST-002 — Unique Identifier

`Column1` شناسه داخلی یکتا است.

- Blank -> `MISSING_UNIQUE_IDENTIFIER` -> Blocker
- Duplicate -> `DUPLICATE_COLUMN1` -> Blocker
- پس از تخصیص، Immutable است مگر اصلاح صریح و تأییدشده.
- Allocation جدید فقط مطابق `NEW-005` مجاز است.

### MST-003 — SKU

`Column2` SKU است.

- Blank -> `MISSING_SKU` -> Blocker
- Duplicate -> `DUPLICATE_SKU` -> Blocker
- مقایسه یکتایی Case-Insensitive است.
- Case ذخیره‌شده حفظ می‌شود.

### MST-004 — Normalization حداقلی شناسه‌ها

برای بررسی یکتایی و Match فقط Trim ابتدا/انتها مجاز است. سیستم نباید Hyphen، Digit، Internal Space یا Case ذخیره‌شده را بازنویسی کند.

### MST-005 — ترتیب ردیف‌ها

ردیف‌های موجود باید در ترتیب فعلی حفظ شوند. Sort/Reorder ممنوع است. محصول جدید تأییدشده فقط در انتهای Dataset Append می‌شود.

### MST-006 — خروجی Full Dataset

خروجی عادی همیشه Full Dataset است، نه Delta.

### MST-007 — Canonical CSV Serialization

فایل CSV نهایی باید با قرارداد Canonical زیر Serialize شود:

- Encoding: `UTF-8` همراه با `UTF-8 BOM`.
- Delimiter: `;`
- Quote Character: `"`
- Record Line Ending: `LF` (`\n`).
- دقیقاً 95 ستون با Header و ترتیب `schema_version` فعال.
- Blank cell به‌صورت field خالی Serialize می‌شود و نباید به literalهایی مانند `null` یا `NULL` تبدیل شود.
- مقدار سلول خارج از Rules صریح نباید Trim، Unicode-normalize، Reformat یا بازنویسی شود.
- اگر field شامل `;`، `"`, `\r`, `\n` یا Leading/Trailing whitespace باشد، باید داخل Double Quote قرار گیرد.
- Double Quote داخل field باید به‌صورت `""` Escape شود.
- Embedded line break مجاز داخل field باید بدون تغییر محتوایی و داخل quoted field حفظ شود.
- Money fields در CSV بدون Separator نمایشی ذخیره می‌شوند.
- Integer Toman به‌صورت digits ساده Serialize می‌شود.
- Serializer با داده Semantic یکسان باید طبق همین قرارداد خروجی یکسان تولید کند.

این deterministic serialization یک **contract** است، نه ادعای byte-identical proof از خود LLM. normal no-code runtime نباید Canonical CSV SHA-256 محاسبه کند. پس از persist، exact Final Output با `PERSISTED_OUTPUT_PROVIDER_IDENTITY_V1` و provider read-back bind می‌شود. Checksum خارجی/build فقط وقتی واقعاً در دسترس است Evidence اضافی است.

### MST-008 — Price Blank

`Price = blank` با `Price = 0` برابر نیست.

اگر Price خالی باشد:

- ابتدا سیستم باید تلاش کند از Rule معتبر قیمت قطعی را بازیابی کند.
- اگر قابل بازیابی باشد -> `AUTO_FIX` + Audit.
- اگر قابل بازیابی نباشد -> `MISSING_PRICE` / `UNRESOLVED` -> Blocker.

### MST-009 — Price نامعتبر در Master

Final `Price` باید یک Integer Toman با دامنه `Price >= 0` باشد.

اگر Price در Master:

- غیرعددی؛
- منفی؛
- یا خارج از representation مجاز باشد،

ابتدا سیستم باید فقط در صورت Match/Source/Formula قطعی امکان بازسازی را بررسی کند:

- بازسازی قطعی -> `INVALID_MASTER_PRICE_AUTO_REPAIRED` و Run ادامه دارد.
- عدم امکان بازسازی قطعی -> `UNRESOLVED_INVALID_MASTER_PRICE` -> Blocker.

اگر Final Price هنوز UNRESOLVED باشد، `visibility` نباید بر اساس حدس Auto-Fix شود.

### MST-010 — Current Master Provider Identity and Concurrency Anchor

برای normal no-code runtime، هویت concurrency Current Master به‌صورت زیر است:

```text
CURRENT_MASTER_PROVIDER_IDENTITY_V1 = {
  provider,
  resource_id,
  provider_revision_id
}
```

در شروع Run این identity به‌عنوان Expected Parent ثبت می‌شود. پیش از Final Summary/Approval و بلافاصله پیش از Promotion باید provider revision تازه خوانده شود.

- revision برابر ⇒ فقط proposition «provider revision از زمان pin تغییر نکرده» پشتیبانی می‌شود؛
- revision متفاوت ⇒ change محافظه‌کارانه محسوب می‌شود؛
- revision unavailable در gate الزامی ⇒ unchanged بودن اثبات نشده و Run باید `BLOCK/RESYNC` شود.

Revision identity اثبات byte-for-byte equality نیست. هرجا correctness به مقدار دقیق Master وابسته است، fresh targeted Sheet read-back مستقل الزامی است.

Historical/build Master fingerprints که قبلاً ثبت شده‌اند immutable evidence باقی می‌مانند، اما normal runtime حق ندارد Canonical Master SHA/fingerprint تازه را prerequisite کند.

## 6. مدل نسخه‌گذاری، Run و Archive

### RUN-001 — Run مستقل

هر اجرای پردازش باید `run_id` یکتا داشته باشد.

Run دارای ابعاد مستقل است و نباید Type، Workflow Status و Result در یک Enum واحد مخلوط شوند.

#### `run_type`

مقادیر مجاز:

- `NORMAL`
- `RERUN_SAME_SOURCE`
- `ROLLBACK`
- `CORRECTIVE`

#### `run_status`

مقادیر مجاز:

- `DRAFT`
- `VALIDATED`
- `AWAITING_APPROVAL`
- `IMPORT_READY`
- `IMPORT_SUCCESS`
- `IMPORT_FAILED`
- `IMPORT_PARTIAL`
- `REJECTED`
- `SUPERSEDED`
- `APPROVED_NO_IMPORT_REQUIRED`

#### `run_result`

پس از تکمیل پردازش داده، یکی از:

- `CHANGES`
- `NO_CHANGES`

تا قبل از تعیین نتیجه می‌تواند unset باشد.

`NO_CHANGES` یعنی هیچ Data Row و هیچ Product Pricing State committed mutation در آن Run لازم نیست؛ Metadata/History update به‌تنهایی آن را به `CHANGES` تبدیل نمی‌کند.

### RUN-002 — Version مستقل

هر Run یک Version جدید مصرف می‌کند، حتی اگر:

- Reject شود.
- Supersede شود.
- Import Failed شود.
- Import Partial شود.
- `NO_CHANGES` باشد.
- Rerun همان Source باشد.
- Rollback/Corrective باشد.

Version Number هرگز Reuse نمی‌شود.

### RUN-003 — نام‌گذاری

نمونه:

`SwitchLand_Price_Master_2026-08-11_v012`

پوشه Run:

`v012_2026-08-11_<run_id>/`

### RUN-004 — پوشه Archive

هر Run پوشه مستقل و Immutable پس از Close دارد.

حداقل محتوا:

- `Source Price List.pdf`
- `Normalized_Source_Catalog.json`
- `Master Snapshot`
- `Product_Pricing_State_Start.json`
- `Product_Pricing_State_Proposed.json` در صورت وجود State Transition
- `Output Full Dataset.csv` یا Draft معادل
- `Audit Report.xlsx`
- `Rulebook Snapshot.json`
- `Run Manifest.json`
- `Import_Contract_Snapshot.json`
- `WP All Import Log` در صورت وجود

Schema Snapshot و Part Number Reference Snapshot باید یا داخل Run Archive وجود داشته باشند یا Manifest به Artifact immutable قابل بازیابی با Version + SHA-256 اشاره کند.

### RUN-005 — Legal Run Transitions

مسیر عادی دارای Changes:

`DRAFT -> VALIDATED -> AWAITING_APPROVAL -> IMPORT_READY -> IMPORT_SUCCESS`

مسیر `NO_CHANGES`:

`DRAFT -> VALIDATED -> AWAITING_APPROVAL -> APPROVED_NO_IMPORT_REQUIRED`

مسیرهای Failure:

- `IMPORT_READY -> IMPORT_FAILED`
- `IMPORT_READY -> IMPORT_PARTIAL`

مسیرهای Abandonment:

- `DRAFT|VALIDATED|AWAITING_APPROVAL -> REJECTED`
- `DRAFT|VALIDATED|AWAITING_APPROVAL|IMPORT_READY -> SUPERSEDED`

اگر پس از `IMPORT_READY` هر Input، Decision، Authority Version یا Output bytes تغییر کند، Approval قبلی باطل است؛ Run باید `SUPERSEDED` شود و Run/Version جدید ساخته شود.

Transitionهای مستقیم مانند `DRAFT -> IMPORT_SUCCESS` یا `IMPORT_PARTIAL -> IMPORT_SUCCESS` ممنوع‌اند.

`VALIDATED` به معنی پایان اجرای Validation است، نه الزاماً بدون Blocker بودن. فقط اگر `technical_import_ready=true` باشد Run می‌تواند به `AWAITING_APPROVAL` برود.

`IMPORT_READY` فقط بعد از Final User Approval مجاز است. قبل از آن readiness فنی در Boolean مستقل `technical_import_ready` ثبت می‌شود.

### RUN-006 — Master Concurrency Guard

در شروع Run:

- Current Master provider resource + provider revision تازه Resolve می‌شود؛
- `CURRENT_MASTER_PROVIDER_IDENTITY_V1` به‌عنوان Expected Parent ثبت می‌شود؛
- Snapshot/read context لازم برای Audit ثبت می‌شود.

Master حداقل در دو Gate دوباره fresh-read می‌شود:

1. پیش از Final Summary/Approval؛
2. بلافاصله پیش از Promotion.

اگر resource/revision در Gate اول با Expected Parent برابر نباشد:

`MASTER_CHANGED_DURING_RUN` -> Blocker -> Resync/Reprocess.

اگر پس از Approval/Import و پیش از Promotion resource/revision دیگر همان Expected Parent نباشد:

`STALE_RUN_PROMOTION_CONFLICT` -> Promotion Blocked.

اگر revision evidence در Gate لازم قابل خواندن نباشد، unchanged بودن assume نمی‌شود. در این وضعیت سیستم نباید Current Master را با Run قدیمی overwrite کند و باید وارد Reconciliation/Corrective flow شود.

هیچ Master SHA/fingerprint تازه‌ای برای این concurrency gate در normal no-code runtime محاسبه نمی‌شود.

### RUN-007 — Atomic Promotion

برای Run دارای Changes، Current Master فقط پس از:

- `import_outcome=SUCCESS`، یا `import_outcome=PARTIAL` همراه با Closure کامل `GOV-007A`؛
- Artifact Binding معتبر مطابق `IMP-004`;
- Promotion-time Concurrency Guard;
- تمام Promotion Gateهای فعال

Promote می‌شود.

Promotion باید Current Master، Product Pricing State و Source control metadata را به‌عنوان یک Commit منطقی هماهنگ کند.

برای `NO_CHANGES` پس از Validation کامل و Final Approval:

- `run_status=APPROVED_NO_IMPORT_REQUIRED`
- Current Master data تغییر نمی‌کند.
- Product Pricing State data تغییر نمی‌کند.
- History/Manifest و Source baseline metadata طبق `NCH-*` به‌روزرسانی می‌شوند.

### RUN-008 — Promotion Status

Manifest باید وضعیت Promotion را مستقل ثبت کند:

- `NOT_REQUIRED`
- `PENDING`
- `PROMOTED`
- `BLOCKED`

برای Run دارای Changes:

- پس از Import Success و قبل از Commit: `PENDING`
- پس از Commit کامل: `PROMOTED`
- اگر Artifact/Concurrency/Reconciliation Gate Fail شود: `BLOCKED`

`IMPORT_SUCCESS` به‌تنهایی اثبات نمی‌کند که Current Master Promote شده است؛ Audit باید `promotion_status` را نیز بررسی کند.

### RUN-009 — Terminal Behavior

این Run Statusها برای همان Run Terminal هستند:

- `REJECTED`
- `SUPERSEDED`
- `IMPORT_FAILED`
- `IMPORT_PARTIAL`
- `APPROVED_NO_IMPORT_REQUIRED`

`IMPORT_SUCCESS` پس از ثبت نتیجه Import immutable است؛ Promotion نتیجه جداگانه دارد. اگر Promotion Block شود، همان Run بازنویسی نمی‌شود و Recovery با Run جدید انجام می‌شود.

### RUN-010 — Run Type Semantics

- `NORMAL`: Price List جدید در مسیر عادی، یا exact-same-source بودن اثبات نشده است.
- `RERUN_SAME_SOURCE`: exact same Source artifact با evidence کافی تحت policy فعال اثبات شده و کاربر Rerun را صریحاً تأیید کرده است.
- `ROLLBACK`: بازگردانی Dataset/State از Historical `IMPORT_SUCCESS` candidate مطابق `RLB-*`.
- `CORRECTIVE`: اصلاح وضعیت پس از Error، Partial Import، Promotion Conflict یا خطای کشف‌شده پس از Success.

`SAME_SEMANTIC_OR_COMMERCIAL_MATERIAL` به‌تنهایی `RERUN_SAME_SOURCE` نمی‌سازد. اگر exact source identity اثبات نشود، `exact_same_source=NOT_PROVEN` و Run می‌تواند `NORMAL` ادامه یابد و بعداً `NO_CHANGES` شود.

`run_reason` همچنان برای توضیح دقیق‌تر الزامی است و جایگزین `run_type` نیست.

### RUN-011 — Technical Readiness

`technical_import_ready=true` فقط زمانی است که:

- هیچ Blocker فعال وجود ندارد؛
- Schema/Identity/Source/Matching/Pricing/Discount/Trace/Reconciliation Validationها Pass شده‌اند؛
- Candidate Output و Proposed State deterministic هستند.

این Boolean هیچ Final Approval تجاری را جایگزین نمی‌کند.

### RUN-012 — External Authority Version Pinning

در شروع Run حداقل این Versionها Pin می‌شوند:

- `rulebook_version_start`
- `schema_version_start`
- `part_number_reference_version_start`
- `product_pricing_state_version_start`
- `import_contract_version_start`

پیش از Finalization، Active Versionهای فعلی دوباره بررسی می‌شوند.

اگر هر Authority خارج از Run تغییر کرده باشد:

`EXTERNAL_AUTHORITY_CHANGED_DURING_RUN`

ثبت می‌شود و ادامه Silent ممنوع است.

رفتار:

- Rulebook یا Part Number Reference جدید اگر Approved/Active باشد -> Full Dataset Reprocess از Snapshot/IR اصلی و Pin نسخه جدید.
- Schema change -> فقط با Schema Migration/Approval صریح در همان Run قابل ادامه است؛ در غیر این صورت Block.
- Product Pricing State change ناشی از Run دیگری -> Master/State concurrency conflict و Reprocess/Restart.
- Import Contract change -> Output/Import validation باید از نو انجام شود؛ Approval قبلی معتبر نیست.

Final Manifest باید Version دقیق Authorityهای واقعاً استفاده‌شده را ثبت کند و Final Output نباید از Versionهای مخلوط ساخته شده باشد. علاوه بر Version، identity فعال باید با live `CURRENT_SET` روی version/hash/resource identity reconcile شود؛ mismatch در active/current carrier مطابق `GOV-011` Blocker است.

---

## 7. دریافت و اعتبارسنجی Price List

### SRC-101 — Run-Scoped Source Artifact Binding

برای هر normal `ACTIVE_RUN_PRICE_LIST` باید `RUN_SCOPED_PROVIDER_ARTIFACT_BINDING_V1` ثبت شود:

- `run_id`
- `authority_role=ACTIVE_RUN_PRICE_LIST`
- `binding_origin=USER_SUPPLIED_FOR_CURRENT_RUN`
- `provider`
- `provider_file_ref`
- `filename`
- source-declared release/date/version وقتی موجود است
- Owner/current-run selection
- binding timestamp

`provider_file_ref` content hash نیست مگر provider evidence صریح چنین خاصیتی را ثابت کند. External/provider checksum MAY فقط وقتی واقعاً موجود است ثبت شود. مدل حق ساختن SHA/checksum ندارد.

### SRC-102 — تاریخ منبع

سیستم باید تاریخ Supplier Price List را فقط از Evidence خود Source یا Rule صریح معتبر استخراج کند و:

- متن اصلی تاریخ را حفظ کند.
- Calendar را مشخص کند: Jalali/Gregorian.
- برای مقایسه داخلی Normalized Date بسازد.
- Evidence/Location تاریخ را در Source Trace ثبت کند.

Upload Timestamp، File Creation Time یا زمان دریافت فایل نباید Supplier Price List Date فرض شود مگر Rule آینده صریحاً آن را مجاز کند.

### SRC-103 — Source قدیمی‌تر

اگر:

- `source_chronology_state=VERIFIED`
- تاریخ Source معتبر و قابل مقایسه باشد
- و Source Date از `last_processed_price_list_date` رسمی قدیمی‌تر باشد

آنگاه:

`SOURCE_PRICE_LIST_OLDER_THAN_BASELINE` -> پردازش قیمت متوقف می‌شود.

### SRC-104 — تاریخ یکسان، منبع مادی متفاوت

اگر Chronology Verified باشد و تاریخ Source با Baseline برابر ولی `MATERIAL_SOURCE_COMPARISON=DIFFERENT` باشد:

- Source جدید قابل پذیرش است؛
- به‌عنوان Revision/observation متفاوت همان تاریخ ثبت می‌شود؛
- run-scoped binding و evidence مقایسه در Audit حفظ می‌شود؛
- رفتار Pricing از محتوای Source جدید تبعیت می‌کند.

Hash difference prerequisite نیست. اگر material comparison قابل اثبات نباشد، chronology طبق fail-closed policy unresolved می‌ماند.

### SRC-105 — Source Date Unverified

اگر تاریخ یافت نشود یا قابل تأیید نباشد:

`SOURCE_DATE_UNVERIFIED` -> Warning، نه Blocker به‌خودی‌خود.

Run می‌تواند در صورت نبود Blocker دیگر ادامه یابد.

اگر چنین Sourceای با:

- `IMPORT_SUCCESS + promotion_status=PROMOTED`
- یا `APPROVED_NO_IMPORT_REQUIRED`

رسمی شود:

- source material baseline و run-scoped binding evidence Update می‌شود؛
- `last_processed_price_list_date` نباید به تاریخ حدسی تغییر کند؛
- `source_chronology_state` باید به `SOURCE_CHRONOLOGY_UNVERIFIED` تغییر کند؛
- آخرین accepted unknown-date Source binding باید برای chronology audit ثبت شود.

مدل برای این baseline hash تازه محاسبه نمی‌کند.

### SRC-106 — Source Date Conflict

اگر چند تاریخ در Source وجود داشته باشد و Rule deterministic نتواند Issue/Effective Date واقعی Price List را تعیین کند:

`UNRESOLVED_SOURCE_DATE_CONFLICT` -> Blocker.

هیچ تاریخ نباید با حدس انتخاب شود.

### SRC-107 — Source تکراری / exact same source

`DUPLICATE_SOURCE_FILE` فقط وقتی فعال می‌شود که **exact same Source artifact** با evidence کافی تحت exact-source policy فعال PROVEN باشد. Same filename، same extracted values یا same semantic/commercial material به‌تنهایی کافی نیست.

تا زمانی که تصمیم صریح کاربر درباره Rerun ثبت نشده است، exact-same-source Event یک Workflow Gate فعال با semantics زیر است:

- `default_severity=BLOCKER`
- `blocks_import=YES`
- `scope=SOURCE/RUN`
- `auto_fix_allowed=NO`
- `resolved=false`
- `technical_import_ready=false`

اگر exact same source PROVEN و کاربر Rerun را صریحاً تأیید کند:

- همان Issue Event در Audit حفظ می‌شود؛
- `resolved=true` می‌شود؛
- دیگر Run را Block نمی‌کند؛
- Run جدید و Version جدید ایجاد می‌شود؛
- `run_type=RERUN_SAME_SOURCE`
- `run_reason=RERUN_SAME_SOURCE`

اگر exact same source **اثبات نشود**:
`exact_same_source=NOT_PROVEN`; `DUPLICATE_SOURCE_FILE` و `RERUN_SAME_SOURCE` از شباهت مادی infer نمی‌شوند و Run می‌تواند در مسیر `NORMAL` ادامه یابد. نتیجه نهایی MAY `NO_CHANGES` باشد.

نتیجه قبلی نباید صرفاً reuse شود؛ Rerun تأییدشده با Current Master/State/Rulebook/Discount Decisions جاری محاسبه می‌شود.

### SRC-108 — Source Completeness State Model

هر Source پس از Extraction Validation دقیقاً یکی از Stateهای `COMPLETE`, `INCOMPLETE`, `UNRESOLVED` را می‌گیرد. Completeness باید از Evidence قابل Audit و **Source-Row Conservation** اثبات شود؛ count equality یا parser confidence به‌تنهایی Proof نیست.

#### Observable Checks

برای هر PDF/Source حداقل این Checkها Event/Result قابل Audit تولید می‌کنند:

1. `FILE_READABLE`
2. `ALL_PAGES_READABLE`
3. `PAGE_SEQUENCE_CHECK`
4. `PAGE_CLASSIFICATION_COVERAGE`
5. `PRICE_BEARING_EXTRACTION_COVERAGE`
6. `SOURCE_RECORD_NORMALIZATION_COVERAGE`
7. `FILE_DAMAGE_CHECK`
8. `PHYSICAL_PRICE_BEARING_ROW_INVENTORY`
9. `SOURCE_ROW_CONSERVATION_RECONCILIATION`

`PHYSICAL_PRICE_BEARING_ROW_INVENTORY` باید قبل از canonicalization ساخته شود و هر ردیف فیزیکی price-bearing را مستقل از extraction ordinal شناسایی کند. ردیف code-less که price-bearing است همچنان ردیف Source معتبر است.

#### State Evaluation

`COMPLETE` فقط وقتی مجاز است که:

- تمام Checkهای applicable دارای `PASS` باشند؛
- هیچ Check برابر `FAIL` یا `UNRESOLVED` نباشد؛
- هیچ Price-bearing row/region unresolved نمانده باشد؛
- `source_row_conservation_status=PROVEN` باشد؛
- missing physical source rows = `0`؛
- duplicate physical-row canonicalizations = `0`؛
- orphan canonical Source Records = `0`.

برابری `physical_price_bearing_row_count == canonical_source_record_count` فقط یک diagnostic است و MUST NOT Completeness را اثبات کند.

`INCOMPLETE` وقتی است که Evidence مثبت از فقدان/خرابی/عدم استخراج یا شکست conservation وجود داشته باشد، از جمله missing physical row، canonicalization چندباره‌ی یک physical row، orphan canonical record، missing page، unreadable page، corruption یا failed/partial extraction.

`UNRESOLVED` وقتی است که Evidence کافی برای `COMPLETE` وجود ندارد ولی شکست قطعی نیز قابل اثبات نیست یا یکی از Checkهای الزامی deterministic نیست.

کاهش count نسبت به Source قبلی به‌تنهایی Evidence نقص نیست؛ اما Proof همین Source باید مستقل و کامل باشد.

### SRC-108A — Authoritative Source-Row Conservation Invariant

Before consequential Absence/`NOT_FOUND`, Source extraction MUST provide bidirectional reconciliation:

`physical price-bearing source row -> canonical IR Source Record`

and

`canonical IR Source Record -> physical price-bearing source row`.

Required invariant:

1. Every physical price-bearing source row maps to exactly one canonical Source Record.
2. Every canonical Source Record maps back to exactly one physical price-bearing source row.
3. `physical_source_row_identity` is independent of extraction ordinal.
4. Code-less but price-bearing rows remain canonical Source Records.
5. Alias/alternate identity attaches to an existing canonical record and MUST NOT increase canonical count.
6. Count equality alone is not proof.
7. `PROVEN` requires missing=0, duplicate canonicalizations=0, orphan canonical records=0 and unresolved price-bearing rows/regions=0.

For the active Adobe/native no-code profile, physical-row identity is the structured source-visible object defined by `STRUCTURED_SOURCE_VISIBLE_ROW_IDENTITY_V1`: run-scoped `source_binding_ref`, table/section, code/model when present, description, role-preserving raw PRICE cells, and source-visible discriminator when needed. No model-computed SHA/fingerprint is required. If two physical rows cannot be truthfully distinguished, `UNRESOLVED_SOURCE_ROW_IDENTITY_COLLISION` blocks `COMPLETE`; merge is forbidden.

### SRC-109 — Source Incomplete/Unresolved

اگر `source_completeness_status=INCOMPLETE` باشد: `UNRESOLVED_SOURCE_INCOMPLETE` -> Blocker.

اگر `source_completeness_status=UNRESOLVED` باشد: `UNRESOLVED_SOURCE_COMPLETENESS` -> Blocker.

اگر `source_row_conservation_status != PROVEN` باشد: `UNRESOLVED_SOURCE_ROW_CONSERVATION` -> Blocker.

در هر حالت Blocker:

- رکوردهای قابل پردازش MAY تحلیل شوند؛
- Deactivation یا هر تصمیم consequential صرفاً بر اساس Absence/`NOT_FOUND` ممنوع است؛
- Final `technical_import_ready=false` می‌ماند تا Source completeness و Source-row conservation هر دو اثبات شوند.

وجود `source_completeness_status=COMPLETE` بدون Reconciliation proof معتبر کافی نیست و باید به `UNRESOLVED`/`INCOMPLETE` بازگردانده شود.
### SRC-110 — Source Chronology State

کنترل Chronology دارای State مستقل است:

- `VERIFIED`
- `SOURCE_CHRONOLOGY_UNVERIFIED`

`VERIFIED` یعنی آخرین ordering رسمی Sourceها تا یک verified-date anchor قابل اثبات است.

`SOURCE_CHRONOLOGY_UNVERIFIED` یعنی حداقل یک Source رسمی پس از آخرین verified-date anchor وجود دارد که Supplier Date آن تأیید نشده و ordering آن نسبت به Sourceهای بعدی صرفاً با Date Comparison قابل اثبات نیست.

### SRC-111 — Chronology Gate after Unknown-Date Success

اگر `source_chronology_state=SOURCE_CHRONOLOGY_UNVERIFIED` باشد و Source بعدی وارد شود، Source جدید نباید صرفاً به‌دلیل داشتن Date معتبر از Old-Source Check عبور کند.

اگر ordering Source جدید نسبت به آخرین accepted unknown-date Source از Evidence معتبر قابل اثبات نباشد:

`SOURCE_CHRONOLOGY_RESOLUTION_REQUIRED` -> Blocker تا تصمیم/مدرک Chronology.

راه‌های مجاز Resolution:

1. Supplier Version/Sequence/Supersession یا provider evidence که Rulebook صریحاً نحوه مقایسه آن را تعریف کرده باشد؛ یا
2. تأیید صریح کاربر درباره ordering Sourceها، با ثبت exact Source bindings و Decision ID در Manifest.

تأیید کاربر نباید یک Date جعلی برای Source بی‌تاریخ ایجاد کند.

کاربر MAY اجازه پردازش Source جدید را بدون Re-anchor بدهد؛ در این حالت Chronology همچنان `SOURCE_CHRONOLOGY_UNVERIFIED` باقی می‌ماند. Hash تازه prerequisite نیست.

### SRC-112 — Chronology Re-Anchor

Chronology فقط وقتی به `VERIFIED` بازمی‌گردد که:

- Source جدید دارای Date معتبر باشد؛
- و ordering آن به‌عنوان superseding تمام Sourceهای accepted بعد از آخرین verified anchor به‌طور deterministic یا با تأیید صریح کاربر اثبات شود؛
- و Run آن Source `IMPORT_SUCCESS + PROMOTED` یا `APPROVED_NO_IMPORT_REQUIRED` شود.

پس از Re-anchor:

- `last_processed_price_list_date` = verified date Source re-anchor؛
- `source_chronology_state=VERIFIED`;
- Evidence و Decision مربوط به Re-anchor در Manifest ثبت می‌شود.

### SRC-IR-001 — Canonical Normalized Source Catalog

پس از Source Validation/Extraction و قبل از Matching/Pricing، Source باید به Artifact Canonical زیر تبدیل شود:

`Normalized_Source_Catalog.json`

Rule Engine نباید برای اجرای همان Run مستقیماً از Interpretation دوباره PDF استفاده کند؛ همه Matching/Pricingهای Run باید به Canonical IR Pin‌شده ارجاع دهند.

### SRC-IR-002 — Canonical IR Metadata

Canonical IR metadata MUST record at least:

```json
{
  "source_artifact_binding": {},
  "source_filename": "...",
  "source_date_original": "...",
  "source_date_calendar": "...",
  "source_date_normalized": "...",
  "source_completeness_status": "COMPLETE|INCOMPLETE|UNRESOLVED",
  "source_row_conservation_status": "PROVEN|FAILED|UNRESOLVED",
  "exact_same_source_status": "PROVEN|NOT_PROVEN|DIFFERENT|UNRESOLVED",
  "physical_price_bearing_row_count": 0,
  "canonical_source_record_count": 0,
  "missing_physical_source_row_count": 0,
  "duplicate_physical_row_canonicalization_count": 0,
  "orphan_canonical_source_record_count": 0,
  "unresolved_price_bearing_row_count": 0,
  "unresolved_price_bearing_region_count": 0,
  "source_row_reconciliation": {
    "physical_to_canonical": [],
    "canonical_to_physical": []
  },
  "extractor_profile": "SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1",
  "records": []
}
```

Count equality is diagnostic only. Normal no-code Source completeness/row identity does not require runtime hashing of Canonical IR. If a provider/build process independently supplies an artifact checksum, it MAY be retained as separate integrity evidence but MUST NOT self-certify Source completeness.

### SRC-IR-003 — Canonical Source Record

Each Supplier Record MUST include:

- `source_record_identity` as a structured relationship;
- `physical_source_row_identity` as structured source-visible identity;
- `source_binding_ref`;
- table/section identity and source-visible discriminator when needed;
- role-preserving raw PRICE cells (`source_price_column_identity`, header/role, raw value);
- code/model when present; code MAY be blank;
- description and other source-visible matching material;
- `extraction_ordinal` for audit only;
- aliases/alternate identities as attachments to the same canonical record;
- normalized supplier/product fields, source currency/value state, component role/structure and normalization result.

Canonical record identity is structurally:

`{record_role=CANONICAL_SOURCE_RECORD, source_binding_ref, physical_source_row_identity}`.

A code-less price-bearing row still receives one canonical record. Alias attachment MUST NOT create another canonical record. Source-row occurrence identity remains separate from downstream product/master identity used by Matching.

### SRC-IR-004 — Physical Row and Source Record Identity

`physical_source_row_identity` uses `STRUCTURED_SOURCE_VISIBLE_ROW_IDENTITY_V1` and MUST be independently recreatable from the bound source-visible material:

Ordinary row:

`{source_binding_ref, table_or_section_identity, supplier_code_or_model, description, role_preserving_raw_price_cells, source_visible_discriminator_when_needed}`.

Code-less/model-less row:

`{source_binding_ref, table_or_section_identity, description, role_preserving_raw_price_cells, other_source_visible_discriminating_material}`.

`source_record_identity` structurally references `source_binding_ref + physical_source_row_identity`. Extraction ordinal is audit-only and never participates. Unordered PRICE numeric sets are forbidden; PRICE column/role swap MUST fail reconciliation. No cryptographic digest is required merely to make these identities meaningful.

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

Run Manifest MUST record:

- run-scoped Source artifact binding;
- Adobe/native evidence mode and replay/coverage states;
- explicit discovered/physical/canonical inventories;
- physical→canonical and canonical→physical mappings;
- missing/duplicate/orphan/unresolved counters;
- candidate→physical and physical→canonical conservation states;
- structured row-identity conformance and collision/mismatch count;
- ordinal-reorder equivalence result used in qualification;
- chronology and exact-same-source status.

Candidate/inventory/reconciliation hashes are not normal Source-completeness closure proofs. Independently-owned persisted/build/external checksums MAY remain in their own integrity domains and do not become Source-row identity authority.

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

### PPS-003 — Versioning / Provider Identity

Product Pricing State دارای حداقل:

- `product_pricing_state_version`
- `provider_resource_id`
- `provider_revision_id` وقتی Provider آن را عرضه می‌کند
- `based_on_master_resource_id`
- `based_on_master_revision_id`
- `committed_run_id`
- `committed_at`
- `authority_state=COMMITTED|PROPOSED`

است.

Historical persisted SHA-256 اگر قبلاً وجود دارد immutable evidence باقی می‌ماند، اما normal no-code runtime برای PPS hash تازه محاسبه نمی‌کند.

Versionهای قبلی Immutable و قابل بازیابی هستند.

### PPS-004 — Staged vs Committed State

در شروع Run:

- State committed فعلی Snapshot/Resolve می‌شود.
- Version + provider resource/revision آن Pin می‌شود.

هر Transition ناشی از:

- Source disappearance؛
- Reactivation؛
- Manual Override lifecycle؛
- Excluded manual price؛
- Scope transition؛

ابتدا فقط در یک **Proposed PPS provider artifact مستقل و non-authoritative** ثبت و read-back می‌شود.

Runtime-derived State Transition در Draft/Validated/Import-ready Run **نباید Authoritative شود** تا Run:

- واقعاً Import شود؛
- `import_outcome=SUCCESS` داشته باشد یا استثناء Partial معتبر مطابق `GOV-007A` داشته باشد؛
- و Promotion Gate کامل شود.

اگر Run `IMPORT_FAILED`, `IMPORT_PARTIAL` بدون استثناء معتبر، `REJECTED`, `SUPERSEDED` یا Promotion Blocked شود، Committed Product Pricing State قبلی دست‌نخورده می‌ماند.

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

- PPS Start Version + provider resource/revision؛
- Proposed PPS provider resource/revision + persisted read-back؛
- Committed PPS successor Version + provider resource/revision در صورت Promotion؛
- Expected Parent Master resource/revision؛
- Promoted Master resource/revision.

Immediately before commit، expected committed PPS identity و expected Master identity با fresh provider identities compare می‌شوند. هر mismatch Promotion را Block می‌کند.

Historical/build PPS hashes MAY بدون recompute حفظ شوند. اگر Dataset Promote شود ولی State commit قابل تکمیل نباشد، Promotion موفق اعلام نمی‌شود و `PRODUCTION_RECONCILIATION_REQUIRED` فعال می‌شود.

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

- Versioned شود؛
- به provider resource/revision یا equivalent persisted artifact identity Bind شود؛
- Archive شود؛
- با نسخه Active Diff شود؛
- فقط پس از persisted read-back و تأیید صریح Active شود.

Persisted/external checksum در صورت وجود MAY حفظ شود؛ normal runtime برای Activation hash تازه محاسبه نمی‌کند.

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

### PRC-010 — Component Missing/Inactive

برای Composite با BOM قطعی:

#### Case A — Component Record موجود ولی Inactive Signal

اگر Component لازم دارای `0`, Blank یا `---` باشد و Source `COMPLETE` و `source_row_conservation_status=PROVEN` باشد:

- محصول Deactivate می‌شود.
- `Price = 0`
- `Sale Price = blank`
- `visibility = hidden`
- این وضعیت به‌خودی‌خود `UNRESOLVED` نیست.

#### Case B — Component Code در Source کامل NOT_FOUND

اگر Component Code مورد انتظار در Source `COMPLETE` یافت نشود، `source_row_conservation_status=PROVEN` باشد و هیچ Evidence مثبت از تغییر BOM/Code/Structure وجود نداشته باشد:

- Component unavailable تلقی می‌شود.
- Composite Product Deactivate می‌شود.
- همان خروجی Case A اعمال می‌شود.

#### Case C — Source ناقص/Unresolved یا Conservation اثبات‌نشده

اگر Source Completeness برابر `INCOMPLETE` یا `UNRESOLVED` باشد **یا** `source_row_conservation_status != PROVEN` باشد، Missing Component/`NOT_FOUND` حق Deactivation ایجاد نمی‌کند و Source Blocker حاکم است.
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

- Source run-scoped artifact binding reference
- structured Source Record / physical-row identity
- Source Page/Section یا table/section identity
- Supplier Code وقتی موجود است
- role-bound raw Source Price + Unit
- normalized Rial
- Formula Type
- Rule ID

#### `SUPPLIER_COMPOSITE`

حداقل:

- هر Component Code/Identity
- Source Record identity هر Component
- Page/Section هر Component
- raw Source Price + Unit هر Component
- normalized Rial هر Component
- BOM ID/Mapping
- `total_rial`
- Composite Pricing Policy ID: `COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1`
- applicable Rule ID
- exact Final Formula
- Final Toman result

Runtime Provenance باید نشان دهد که Summation در Rial قبل از تنها Rounding Boundary انجام شده است. Component-level Ceiling قبل از Summation مجاز نیست.

#### `USER_MANUAL_OVERRIDE`

حداقل: Decision ID، User-entered amount/unit، normalized Final Toman، established Run ID و Rule ID.

#### `EXCLUDED_MANUAL_PRICE`

حداقل همان اطلاعات Manual Decision + Excluded scope evidence.

#### `ROLLBACK_HISTORICAL`

حداقل:

- source historical run_id/version
- historical dataset hash
- historical state hash در صورت وجود
- target row identity

Historical rollback hashes immutable evidence هستند و این v1.14 migration آن‌ها را حذف نمی‌کند.

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
3. `source_row_conservation_status=PROVEN` و bidirectional reconciliation دارای missing=0, duplicate=0, orphan=0, unresolved rows/regions=0 باشد.
4. Match result برای Product = `NOT_FOUND`.
5. هیچ Rule تخصصی‌تر incompatibility مانند `CLR-003` یا `PRC-011` فعال نباشد.
6. Absence ناشی از Parser/Normalization/Authority Conflict نباشد.

اگر همه برقرار باشند:

- `Price = 0`
- `Sale Price = blank`
- `visibility = hidden`
- availability transition مطابق `REC-002` ثبت می‌شود.
- Manual Override active مطابق `OVR-002` در Proposed State Terminate می‌شود.

هر `NOT_FOUND` در حالی‌که conservation proof اثبات نشده باشد حق Deactivation ایجاد نمی‌کند.
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

مجموعه `all_known_reserved_Column1` از Union این منابع ساخته می‌شود:

1. تمام `Column1`های Current Master؛
2. تمام `Column1`های موجود در Archive history شناخته‌شده و قابل خواندن؛
3. هر Identifier Registry/Reservation Artifact فعال در صورت وجود.

برای هر source مجموعه، provider resource/revision یا equivalent governed artifact identity ثبت می‌شود. تمام identifierهای معتبر قبل از محاسبه به Numeric integer domain مورد انتظار Validate می‌شوند؛ Stored values تغییر نمی‌کنند.

الگوریتم:

1. `candidate = max(all_known_reserved_Column1) + 1`
2. candidate با Current Master compare شود.
3. candidate با known Archive/Reservation set compare شود.
4. collision ⇒ `candidate = candidate + 1` و uniqueness check تکرار شود.
5. اولین Candidate که در هیچ شناسه شناخته‌شده وجود ندارد انتخاب شود.
6. شناسه تاریخی شناخته‌شده هرگز reuse نشود.
7. Audit باید enumerated source identities، known-set count/max، candidate sequence و collision/reuse checks را ثبت کند؛ reserved-set hash/fingerprint تازه لازم نیست.

با reserved set یکسان، الگوریتم باید Identifier یکسان تولید کند.

اگر complete known reserved-set enumeration قابل اثبات نباشد:

`UNRESOLVED_COLUMN1_GENERATION` -> `UNRESOLVED` -> Blocker.

`WARNING_COLUMN1_HISTORY_INCOMPLETE` فقط وقتی مجاز است که Guarantee صریحاً به known readable set محدود باشد و Business Rule اجازه همان bounded guarantee را بدهد؛ این Warning به‌تنهایی حق Global qualification NEW_PRODUCT نمی‌دهد.

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
- `change_status=ADDED` می‌گیرد.
- پس از Append باید کل Schema/Identity/Control Total validation دوباره Pass شود.

### NEW-009 — New Product Lifecycle

هر Candidate جدید در همان Run دقیقاً یکی از Stateهای زیر را دارد:

- `DETECTED`
- `USER_APPROVED_FOR_CREATION`
- `PENDING_COMPLETION`
- `READY_TO_APPEND`
- `SKIPPED_THIS_RUN`
- `REJECTED`

Transitions:

- detection -> `DETECTED`
- explicit create approval -> `USER_APPROVED_FOR_CREATION`
- incomplete required profile -> `PENDING_COMPLETION`
- complete + validated -> `READY_TO_APPEND`
- «فعلاً اضافه نکن» -> `SKIPPED_THIS_RUN`
- explicit abandonment/rejection of current creation attempt -> `REJECTED`

Stateها Run-scoped هستند مگر Rule دائمی جداگانه تصویب شود.

`REJECTED` به‌خودی‌خود Permanent blacklist ایجاد نمی‌کند.

### NEW-010 — Pending Completion Blocking Semantics

اگر User creation را تأیید کرده ولی required row data کامل نیست:

- Lifecycle = `PENDING_COMPLETION`
- Issue = `NEW_PRODUCT_PENDING`
- `technical_import_ready=false`
- Row ناقص نباید Append شود.
- Product نباید Silent Skip شود.

اگر کاربر بعداً برای همان Run آن را `SKIPPED_THIS_RUN` کند، Issue resolved و non-blocking می‌شود.

### NEW-011 — Schema-Driven Required Field Profile

هر `schema_version` باید Profile نسخه‌دار:

`new_product_required_fields`

داشته باشد.

برای هر Header حداقل مشخص شود:

- `required_for_new_product: true|false`
- `value_source_policy`
- `auto_generation_allowed`
- `validation_rule_ids`

Product فقط وقتی `READY_TO_APPEND` می‌شود که:

- تمام fieldهای required دارای مقدار معتبر باشند؛
- `Column1` و `Column2` uniqueness Pass شوند؛
- generated/suggested values مجوز لازم داشته باشند؛
- Price/Sale/visibility Valid باشند؛
- Row دقیقاً 95 field مطابق schema داشته باشد؛
- هیچ Blocker record-level فعال نباشد.

### NEW-012 — Cancellation after Approval

اگر کاربر پس از `USER_APPROVED_FOR_CREATION` یا `PENDING_COMPLETION` تصمیم بگیرد Product در همان Run اضافه نشود:

- `SKIPPED_THIS_RUN` برای intent «فعلاً اضافه نکن» استفاده می‌شود؛
- `REJECTED` برای abandon/reject current creation attempt قابل استفاده است؛
- proposed incomplete row Append نمی‌شود؛
- Rule دائمی ایجاد نمی‌شود مگر کاربر صریحاً Permanent Rule بخواهد.

---

## 18. AUTO_FIX / WARNING / BLOCKER

### VAL-201 — AUTO_FIX

فقط وقتی مجاز است که:

- نتیجه قطعی باشد.
- Rule معتبر وجود داشته باشد.
- فیلد در Mutation Allowlist باشد.
- Source/Match/Formula requirements همان Rule Pass شده باشند.

AUTO_FIX نیازمند تأیید موردی نیست ولی Audit اجباری است.

حداقل Audit:

`Old Value -> New Value -> Rule Applied -> Reason`

AUTO_FIX هرگز حق دورزدن Blocker را ندارد.

### VAL-202 — Mutation Allowlist

فعلاً Auto-Fix فقط روی:

- `Price`
- `Sale Price`
- `visibility`

مجاز است.

تشخیص خطا در Name، Description، Attribute، Part Number، SKU، Column1 و سایر fieldها حق Auto-Edit ایجاد نمی‌کند.

New Product row generation rules و explicit Schema migration از این Auto-Fix Allowlist جدا هستند و فقط با Approval/Rule مربوطه مجازند.

### VAL-203 — Price Auto-Fix

حتی Price فقط وقتی Auto-Fix می‌شود که Match/Source/Formula Authoritative و deterministic باشد.

Name-only، Suggestion، ambiguous alias یا Match احتمالی ممنوع است.

### VAL-204 — WARNING

Warning مانع Import نیست، مگر Evidence جدیدی نشان دهد condition در واقع Blocker است.

نمونه‌ها:

- تغییر Price بیش از 50٪
- Source Date Unverified در Chronology مجاز
- Missing Part Number در Match قطعی
- Duplicate Source Record کاملاً Consistent
- محدودیت Archive coverage برای Column1 در صورت safe known-set generation

Warning باید visible و auditable باشد ولی Final Approval را بی‌دلیل متوقف نمی‌کند.

### VAL-205 — BLOCKER

Blocker فقط برای ابهام یا خطر واقعی داده/Promotion است.

نمونه‌ها:

- Duplicate SKU/Column1
- Schema Mismatch
- Match Conflict
- Source Conflict
- Source Incomplete/Completeness Unresolved
- Source Chronology unresolved
- Duplicate Source rerun decision unresolved
- BOM Conflict
- Invalid Price/Discount
- Sale Price Conflict
- Multiple Source ambiguity
- Product Pricing State mismatch
- New Product approved but incomplete
- Rollback safety failure
- Stale Promotion
- Imported Artifact mismatch

وجود هر Blocker فعال:

`technical_import_ready=false`

و Artifact نباید Final Import-ready اعلام شود.

### VAL-206 — Issue Object

هر technical event باید حداقل این ساختار مفهومی را داشته باشد:

```json
{
  "code": "...",
  "issue_kind": "AUTO_FIX|WARNING|UNRESOLVED|ERROR",
  "severity": "INFO|WARNING|BLOCKER",
  "blocks_import": true,
  "scope": "RECORD|SOURCE|RUN|GOVERNANCE|PROMOTION",
  "rule_id": "...",
  "message": "...",
  "resolved": false
}
```

Technical Code default metadata از Registry بخش 29 می‌آید. Rule-specific context MAY severity را فقط در صورتی تغییر دهد که همان Rule صریحاً اجازه دهد.

### VAL-207 — Severity Aggregation

برای هر Record:

`max_issue_severity`

از unresolved issueهای همان Record به‌ترتیب زیر محاسبه می‌شود:

`NONE < INFO < WARNING < BLOCKER`

وجود `AUTO_FIX` event معمولاً Severity `INFO` دارد مگر Rule خلاف آن را صریحاً بگوید.

Run-level `technical_import_ready` از وجود هر unresolved `blocks_import=true` در تمام scopeها محاسبه می‌شود.

---

## 19. مدل Record State، گزارش و تجربه کاربر

### REC-001 — Orthogonal Record State

یک Enum واحد نباید Change، Availability، Scope و Issue را با هم مخلوط کند.

هر Existing/Output Record حداقل این محورهای مستقل را دارد:

#### `change_status`

- `UNCHANGED`
- `UPDATED`
- `ADDED`

`ADDED` فقط برای New Product appended است. حذف Row در Scope سیستم نیست.

#### `availability_status`

- `ACTIVE`
- `INACTIVE`
- `ACTIVATED`
- `DEACTIVATED`
- `UNRESOLVED`

#### `scope_status`

برای Existing Master Product:

- `SUPPLIER_MANAGED`
- `EXCLUDED_FROM_PRICE_LIST`

اگر Scope قابل تعیین نباشد field می‌تواند null باشد، اما Issue `UNRESOLVED_SCOPE` باید فعال شود.

#### `issues[]`

لیست Issue Objectهای `VAL-206`.

#### `max_issue_severity`

- `NONE`
- `INFO`
- `WARNING`
- `BLOCKER`

### REC-002 — Availability Status Calculation

برای Existing Product با Old/New Final Price معتبر:

- Old `>0`, New `>0` -> `ACTIVE`
- Old `0`, New `0` -> `INACTIVE`
- Old `0`, New `>0` -> `ACTIVATED`
- Old `>0`, New `0` -> `DEACTIVATED`

اگر Old یا New Price برای تعیین availability معتبر نباشد:

`UNRESOLVED`.

برای New Product `ADDED`:

- Final Price `>0` -> `ACTIVE`
- Final Price `=0` -> `INACTIVE`

### REC-003 — Change Status Calculation

`UNCHANGED` وقتی است که Output row در تمام 95 cell با Starting Master row برابر باشد.

`UPDATED` وقتی است که Existing row حداقل یک field مجاز مطابق Rule تغییر کرده باشد.

`ADDED` برای appended new row است.

Audit event یا Warning بدون Data Row mutation، `change_status` را به UPDATED تبدیل نمی‌کند.

Product Pricing State transition جداگانه در State Reconciliation گزارش می‌شود حتی اگر row data اتفاقاً تغییر نکرده باشد.

### REC-004 — Control Total Counting

Control Totalها باید Axis-specific باشند:

- `change_status_counts`: exclusive و جمع آن برابر Output row count است.
- `availability_status_counts`: exclusive و برای تمام records با evaluable state محاسبه می‌شود؛ `UNRESOLVED` نیز count دارد.
- `scope_status_counts`: Existing/Added records دارای committed/proposed scope؛ null scope جداگانه count می‌شود.
- `max_issue_severity_counts`: exclusive.
- `issue_event_counts_by_code`: تعداد eventها و MAY بیش از row count باشد.

سیستم نباید مجموع `UPDATED + ACTIVATED + WARNING` را به‌عنوان تعداد unique records گزارش کند.

### AUD-001 — دو لایه توضیح

هر Summary باید دو لایه داشته باشد:

1. **توضیح ساده و غیرتخصصی:** چه اتفاقی افتاده، چرا، و اثر آن چیست.
2. **جزئیات فنی Audit:** Rule ID، Match Method، Price Provenance/Source Trace، Error/Warning Code، Old/New Value و State Transition.

### AUD-002 — Summary قبل از Import

اگر `technical_import_ready=true`، Run وارد `AWAITING_APPROVAL` می‌شود و پیش از Final Import-ready شدن Summary کلی به کاربر نمایش داده می‌شود، شامل حداقل:

- تعداد `UPDATED`, `UNCHANGED`, `ADDED`
- تعداد `ACTIVATED`, `DEACTIVATED`, `ACTIVE`, `INACTIVE`
- تعداد Auto-Fix event
- تعداد Warning record/event
- تعداد New Product Skipped
- تعداد Discount Change
- Product Pricing State transitions
- Source Chronology state
- final Candidate Control Totals

برای هر دسته یک توضیح ساده اجباری است.

### AUD-003 — Final Approval

فایل نباید به‌عنوان «نسخه نهایی آماده Import» اعلام شود تا:

- Summary ارائه شود؛
- کاربر یک Final Global Approval بدهد؛
- Final Output به Provider persist و read-back شود؛
- Approval Decision در Manifest ثبت شود.

بعد از Approval:

- `run_status=IMPORT_READY`
- Approval به exact `run_id`, `run_version` و `PERSISTED_OUTPUT_PROVIDER_IDENTITY_V1` متصل می‌شود.

اگر Final Output resource/revision، material User Decision یا governing Authority بعد از Approval تغییر کند، Approval باطل و Run مطابق `RUN-005` Supersede می‌شود.

Existing external/build checksum MAY به binding اضافه شود؛ normal runtime hash computation prerequisite نیست.

### AUD-004 — Audit Report.xlsx

Workbook پیش‌فرض شامل Sheetهای زیر است:

- `خلاصه`
- `تغییرات`
- `هشدارها`
- `حل‌نشده‌ها`
- `خطاها`

`همه رکوردها` به‌صورت پیش‌فرض ایجاد نمی‌شود مگر کاربر درخواست کند.

### AUD-005 — تغییرات

Sheet `تغییرات` فقط Existing rows با `change_status=UPDATED` و New rows با `ADDED` را دارد.

State-only transitions که row data را تغییر نداده‌اند باید در Summary/State Audit ثبت شوند و نباید به‌اشتباه row UPDATED شمرده شوند.

### AUD-006 — Summary Metadata

حداقل:

- Price List Date و verification state
- Source Chronology State
- run-scoped Source artifact binding
- Source Completeness/reconciliation status
- Current Master provider identity Start/Final Check
- Product Pricing State committed/proposed provider identities
- Final Output provider identity در صورت وجود
- `rulebook_version`
- `part_number_reference_version`
- `schema_version`
- `import_contract_version`
- Run Timestamp
- `run_id`
- `run_version`
- `run_type`
- `run_status`
- `run_result`
- Final `technical_import_ready`
- Final Approval state
- Control Totals قبل/بعد

Optional provider/external/persisted checksums فقط وقتی واقعاً موجودند ثبت می‌شوند؛ مدل آن‌ها را جعل یا runtime-compute نمی‌کند.

### AUD-007 — زبان گزارش

Labelهای کاربر فارسی هستند و Technical Code داخل پرانتز English باقی می‌ماند.

مثال:

`هشدار تغییر قیمت بیش از ۵۰٪ (WARNING_PRICE_CHANGE_GT_50_PERCENT)`

### AUD-008 — Run Manifest Decision Log

هر تصمیم نیازمند تأیید صریح کاربر باید در Manifest ثبت شود، حداقل:

- Decision Topic
- Decision Code/ID
- Timestamp با Timezone
- Selected Outcome
- Scope: Current Run / Permanent Rule
- Effect on Run
- Rulebook Version در صورت Permanent بودن
- Approval Reference

ثبت Decision به‌تنهایی آن را Permanent Rule نمی‌کند.

### AUD-009 — Why Product X?

برای هر Product تغییرکرده یا State-transitioned، Archive باید برای این سؤال پاسخ deterministic داشته باشد:

«چرا Product X در Run Y این Price، Sale Price، visibility، Scope و Availability را گرفت؟»

پاسخ باید از Manifest/Audit/Canonical IR/Rulebook/State Snapshot بدون نیاز به memory مکالمه قابل بازسازی باشد.

---

## 20. Run Workflow استاندارد

### WF-001 — Boot

کاربر در Project پیام `شروع` می‌دهد.

### WF-002 — Intake

سیستم آخرین Price List را درخواست می‌کند و Run Context اولیه را می‌سازد.

در همین مرحله `WF-019 — Authority Pinning` باید قبل از Source/Business processing کامل شود.

### WF-003 — Source Validation

حداقل:

- run-scoped Source provider artifact binding
- exact-same-source status طبق active exact-source policy
- Duplicate Source Check فقط وقتی exact same artifact PROVEN است
- Source Date
- Source Chronology
- Source Completeness State
- Adobe/native Extractability/Coverage/Reconciliation results

Source SHA-256 در normal no-code PRICE_UPDATE prerequisite نیست. External/provider checksum فقط در صورت وجود Evidence اضافی است.

Source با Blocker MAY برای diagnosis ادامه پردازش شود، اما Final readiness false می‌ماند.

### WF-004 — Current Master Snapshot

حداقل:

- Read Current Master
- capture `CURRENT_MASTER_PROVIDER_IDENTITY_V1`
- Schema Validation
- Unique Identifier Validation
- SKU Validation
- Product Pricing State Validation
- Master/State Consistency Check
- Expected Parent Master resource/revision
- targeted authoritative Sheet read-back برای هر field/value تصمیم‌ساز

Canonical Master fingerprint تازه در normal no-code runtime prerequisite نیست.

### WF-005 — Discount Preflight

برای Runهای مشمول `DSC-002` قبل از Main Pricing اجرا می‌شود.

اگر Source Blocker فعلاً Main Pricing را ناممکن کرده است، Discount Preflight MAY تا رفع همان Source Blocker defer شود تا سؤال غیرضروری از کاربر پرسیده نشود؛ اما قبل از Main Pricing حتماً باید کامل شود.

Discount decisions به Run Decision Log اضافه می‌شوند.

### WF-006 — Source Extraction

Source باید حداقل این موارد را استخراج کند:

- Product Codes
- Component Codes/Roles
- Colors/Variants
- Prices/Inactive Signals
- Family/Series
- Sections/Pages
- raw record location

Extraction result باید برای Canonicalization `WF-007` قابل Audit باشد.

### WF-007 — Normalize

Normalization حداقل شامل:

- Digits
- Price Format/Unit
- Supplier Code Compare Rules
- Color Alias
- Variant/Family/Series fields
- Component Structure

است.

خروجی `WF-006 + WF-007` باید Artifact رسمی:

`Normalized_Source_Catalog.json`

مطابق `SRC-IR-*` باشد.

پس از ساخت Canonical IR، Matching/Pricing همان Run نباید PDF را دوباره reinterpret کند.

### WF-008 — Matching

Matching فقط روی Canonical Source IR و Authorityهای Pin‌شده انجام می‌شود.

اعمال:

- Priority Rules
- Conflict Rules
- Scope requirements
- Color/BOM/PN validation

### WF-009 — Pricing

ترتیب منطقی Pricing stage:

1. Resolve Match/BOM.
2. Resolve Supplier Availability.
3. Resolve Direct/Composite Supplier Price.
4. Apply Exclusion/Manual Override lifecycle Rules.
5. Determine Final Regular Price.
6. Determine Availability status.
7. Apply Discount decisions.
8. Synchronize visibility from valid Final Price.
9. Stage Product Pricing State transitions.

### WF-010 — Validation

حداقل:

- Final Price domain
- Sale Price
- visibility
- Schema
- Unique Identifier/SKU
- Source Trace/Price Provenance
- Scope/State consistency
- Source Completeness/Chronology
- Warning Threshold
- New Product required profile
- Control Totals/Reconciliation
- External Authority final-version check

### WF-011 — Exception Handling

سیستم:

- Auto-Fixهای مجاز را اعمال و Audit می‌کند.
- Warningها را ثبت می‌کند.
- تمام Blockerهای واقعی را مشخص می‌کند.
- سایر independent records را ادامه می‌دهد.

فقط Blockerهایی که واقعاً User Decision لازم دارند باید سؤال ایجاد کنند.

### WF-012 — Reprocess after Rule Change

اگر کاربر در میانه Run Permanent Rule جدیدی تصویب کند و `rulebook_version` Active تغییر کند:

1. Version جدید Pin می‌شود.
2. تمام derived results Rulebook قبلی discard می‌شوند.
3. Full Dataset از:
   - Original Master Snapshot
   - Original Product Pricing State Start Snapshot
   - Canonical Source IR
   - current valid Run-scoped User Decisions
   دوباره پردازش می‌شود.
4. همه Existing + New Candidate records Reprocess می‌شوند.
5. Final Output فقط یک Rulebook Version دارد.

Selective «potentially affected rows» و dependency graph در v1 مجاز نیست.

### WF-013 — Draft Output در صورت Blocker

در صورت وجود Blocker، سیستم MAY یک Draft Full Dataset برای Archive تولید کند، اما:

- Artifact label = `DRAFT_NOT_IMPORT_READY`
- `technical_import_ready=false`
- نباید به‌عنوان فایل Import-ready معرفی شود.
- Draft با provider artifact identity + run scope + read-back از Final Output احتمالی متمایز می‌شود.

Runtime Draft hash prerequisite نیست؛ external/build checksum در صورت وجود فقط Evidence اضافی است.

### WF-014 — Final Summary

اگر Blocker فعال وجود ندارد:

1. Candidate Dataset با Canonical CSV Serializer ساخته می‌شود.
2. Proposed Product Pricing State به‌عنوان provider artifact مستقل Persist و read-back می‌شود.
3. Final Output به‌عنوان provider artifact Persist و read-back می‌شود.
4. Final Control Totals/Reconciliation اجرا می‌شود.
5. Master/PPS/Authority concurrency Gate با fresh provider identities تکرار می‌شود.
6. `technical_import_ready=true`
7. `run_status=AWAITING_APPROVAL`
8. Summary دو‌لایه به کاربر ارائه می‌شود.

هیچ PPS/CSV hash تازه‌ای برای normal no-code runtime محاسبه نمی‌شود.

### WF-015 — User Approval

پس از Global Approval:

- Final Output provider artifact resource/revision باید همان read-back شده‌ی Final Summary باشد؛
- Approval به exact `run_id`, `run_version` و `PERSISTED_OUTPUT_PROVIDER_IDENTITY_V1` متصل می‌شود؛
- `run_status=IMPORT_READY`.

اگر Material Input/Decision/Authority یا Final Output resource/revision بعد از Approval تغییر کند، Approval معتبر نیست و Run باید Supersede شود.

این binding provider-native است و ادعای byte-identical/cryptographic equality نمی‌کند مگر external evidence مستقل آن را ثابت کند.

### WF-016 — WP All Import

کاربر exact Final CSV مربوط به Run را Import می‌کند.

System نباید صرف «وجود یک Import موفق» را معادل Import همین Artifact بداند.

### WF-017 — Import Outcome

Outcome evidence:

- Failure -> `run_status=IMPORT_FAILED`
- Partial -> `run_status=IMPORT_PARTIAL`
- Success -> Import Outcome Success و ورود به Promotion Gate

Run فقط بعد از Promotion موفق `run_status=IMPORT_SUCCESS` می‌گیرد.

### WF-018 — Promotion

Promotion فقط بعد از Pass این Gateها مجاز است:

1. Import Outcome = Success؛ یا Import Outcome = Partial همراه با exact run-scoped Owner Exception و Closure کامل `GOV-007A`.
2. Artifact Binding مطابق `IMP-004`.
3. Import Assurance sufficient.
4. Expected Current Master provider identity == fresh Current Master provider identity.
5. Expected committed PPS provider identity == fresh current committed PPS provider identity.
6. `PRODUCTION_RECONCILIATION_REQUIRED` فعال نیست مگر مسیر Partial استثنایی صریحاً آن Queue را حمل کند.
7. Proposed Product Pricing State valid و persisted/read-back است.
8. selected Authority identities با live `CURRENT_SET` coherent هستند.

Persisted/build/history hashes MAY به‌عنوان Evidence اضافی reconcile شوند؛ normal runtime hash generation gate نیست.

پس از Pass:

- Current Master Sync/Promote
- Product Pricing State Commit
- Source baseline/chronology metadata update
- مسیر عادی: `promotion_status=PROMOTED` و `run_status=IMPORT_SUCCESS`.
- مسیر استثنایی Partial: `promotion_status=PROMOTED_WITH_OPEN_RECONCILIATION` و `run_status=IMPORT_PARTIAL`؛ Queue باز حفظ می‌شود.

اگر Gate Fail شود:

- Current Master Promote نمی‌شود.
- `promotion_status=BLOCKED`
- Recovery/Corrective flow در صورت Production mutation فعال می‌شود.

### WF-019 — Authority Pinning

این Rule از نظر ترتیب اجرا **بعد از WF-002 و قبل از WF-003** اجرا می‌شود.

در شروع Run:

- Current Master
- Product Pricing State
- Rulebook
- Schema
- Part Number Reference
- Import Contract

با Version + provider resource/revision یا selector identity موجود Pin می‌شوند. Persisted authority hash اگر live selector آن را عرضه کند MAY consume شود؛ runtime recomputation لازم نیست.

حداقل pinها مطابق `RUN-012` ثبت می‌شوند.

### WF-020 — External Authority Final Check

این Rule قبل از `WF-014` اجرا می‌شود.

Active Authority Versionها با Pinهای Run مقایسه می‌شوند.

اگر Authority خارج Run تغییر کرده باشد:

- مطابق `RUN-012` Full Reprocess/Revalidation؛
- یا Block/Restart

انجام می‌شود.

Silent mixed-version execution ممنوع است.

### WF-021 — Run Close and Archive

پس از terminal state:

- Manifest Close Timestamp ثبت می‌شود.
- provider artifact identities/revisions و read-back states نهایی می‌شوند.
- هر externally supplied/build/persisted checksum موجود بدون recompute ثبت می‌شود.
- Closed Manifest مطابق `DEF-004` Immutable می‌شود.
- هر Correction بعدی با Run/Event جدید است.

## 21. رفتار Import Failure، Partial و Promotion Evidence

### IMP-001 — Import Failed

اگر WP All Import کامل Fail شود و Production mutation قابل‌توجهی انجام نشده باشد:

- `run_status=IMPORT_FAILED`
- Current Master تغییر نمی‌کند.
- Product Pricing State تغییر نمی‌کند.
- Retry یک Run/Version جدید است.

اگر Failure بعد از mutation نامعلوم رخ داده و وضعیت Production قابل اثبات نیست، سیستم باید به‌جای فرض «no change» Recovery را به‌صورت conservative با `PRODUCTION_RECONCILIATION_REQUIRED` علامت بزند.

### IMP-002 — Import Partial

Partial Promotion عمومی مجاز نیست. تنها استثناء، مسیر exact و run-scoped تعریف‌شده در `GOV-007A` است.

در `IMPORT_PARTIAL`:

- Run terminal است.
- بدون Closure کامل `GOV-007A`، Current Master و Product Pricing State committed pointer دست‌نخورده می‌مانند.
- `PRODUCTION_RECONCILIATION_REQUIRED` فعال می‌شود.
- بدون استثنای معتبر، Normal Price Update Promotion جدید تا Reconciliation ممنوع است. با استثنای معتبر، فقط exact carried-forward queue باز می‌ماند و unrelated valid work می‌تواند ادامه یابد.
- Run/Version اصلاحی جدید باید ساخته شود.

Production ممکن است واقعاً Subsetی از changes را دریافت کرده باشد. در مسیر استثنایی، Current Master نماینده Dataset مصوب است و Manifest باید صریحاً اعلام کند exact Subjectهای Queue هنوز در Production اثبات/اصلاح نشده‌اند؛ این وضعیت نباید به‌عنوان تطابق کامل Production نمایش داده شود.

### IMP-003 — اصلاح پس از Success

اگر بعد از Import Success/Promotion اشتباهی کشف شود:

- Version موفق قبلی بازنویسی نمی‌شود.
- Corrective Run جدید ساخته می‌شود.
- `run_type=CORRECTIVE`
- Provenance به Run موفق قبلی ثبت می‌شود.

### IMP-004 — Imported Artifact Binding

Promotion باید Evidence داشته باشد که Import Outcome به Final Output Approved Run مربوط است.

Final Output قبل از handoff باید با `PERSISTED_OUTPUT_PROVIDER_IDENTITY_V1` شامل provider/resource/revision/run_id/run_version/filename/read-back bind شود.

Manifest حداقل دارد:

- Final Output provider identity
- `imported_filename`
- `imported_run_id`
- `imported_run_version`
- `import_confirmation_method`
- `import_assurance_level`
- `imported_artifact_hash` فقط اگر واقعاً independently available است
- final artifact checksum فقط اگر independently available است

Assurance branches:

#### `HASH_VERIFIED`
فقط وقتی checksum Artifact واقعاً Import‌شده و checksum همان Final Output independently available باشند، equality آن‌ها باید برقرار باشد. این branch external evidence را **consume** می‌کند؛ LLM checksum تولید نمی‌کند. Mismatch:
`IMPORTED_ARTIFACT_MISMATCH` -> Promotion Blocked.

#### `USER_ATTESTED`
اگر WP All Import/محیط Import checksum یا machine-readable artifact receipt ندهد و active Import Contract این fallback را مجاز کند، کاربر باید به‌صورت صریح تأیید کند:

- exact `run_id`
- exact `run_version`
- `imported_filename`
- Import موفق مربوط به Final Artifact همان Run بوده است.

در این حالت:
- `import_assurance_level=USER_ATTESTED`
- `imported_artifact_hash=null` اگر unavailable
- هیچ ادعای byte verification، cryptographic proof یا mechanically proven equality مجاز نیست.

Provider-bound Final Output identity فقط تا handoff boundary PROVEN است.

#### `NONE`
اگر نه external verification و نه explicit Run-bound user attestation مجاز وجود داشته باشد:

`IMPORT_ASSURANCE_INSUFFICIENT` -> Promotion Blocked.

### IMP-005 — Promotion-time Parent Check

Artifact Binding به‌تنهایی کافی نیست.

Immediately before Commit:

- Expected Current Master provider identity؛
- fresh Current Master provider identity؛
- Expected committed PPS provider identity؛
- fresh current committed PPS provider identity

باید pairwise برابر باشند.

Master mismatch:
`STALE_RUN_PROMOTION_CONFLICT` -> Promotion Blocked.

PPS mismatch:
`PPS_CHANGED_DURING_RUN` یا equivalent active concurrency blocker -> Promotion Blocked/Resync.

اگر provider revision evidence در Gate قابل خواندن نباشد، unchanged بودن assume نمی‌شود. Master/PPS SHA تازه برای CAS محاسبه نمی‌شود.

### IMP-006 — Production Reconciliation Guard

وقتی:

- Import Partial؛
- imported artifact mismatch پس از mutation؛
- promotion blocked بعد از confirmed Production change؛
- یا failure با mutation uncertainty

رخ دهد:

`PRODUCTION_RECONCILIATION_REQUIRED`

فعال می‌شود.

تا Resolution:

- Normal Run MAY برای diagnosis آماده شود؛
- اما هیچ Normal Run جدیدی نباید Promote شود.
- Recovery باید با `CORRECTIVE` یا `ROLLBACK` Run و Evidence وضعیت Production انجام شود.

Resolution باید در Manifest Run اصلاحی ثبت شود.

### IMP-007 — No Partial Commit

هیچ Subset از Changes، حتی اگر قابل شناسایی باشد، نباید به‌عنوان Current Master جدید Promote شود.

هدف Recovery بازگرداندن Production و committed Current State به یک Version کامل و شناخته‌شده است.

---

## 22. NO_CHANGES

### NCH-001 — Run معتبر حتی بدون تغییر

اگر Price List بررسی شود ولی هیچ Data Row یا Product Pricing State mutation لازم نباشد:

`run_result=NO_CHANGES`

Run همچنان باید:

- Run ID/Version
- Archive
- Canonical Source IR
- Audit Report
- Manifest
- Validation Result

داشته باشد.

### NCH-002 — Approval

اگر:

- `technical_import_ready=true`
- `run_result=NO_CHANGES`

باشد، Final Summary ارائه می‌شود.

پس از Final User Approval:

`run_status=APPROVED_NO_IMPORT_REQUIRED`

WP All Import لازم نیست.

### NCH-003 — Baseline Metadata

در NO_CHANGES تأییدشده:

- source material baseline و run-scoped source binding evidence Update می‌شود؛
- اگر Source Date معتبر و Chronology قابل Re-anchor/Verified باشد، `last_processed_price_list_date` Update می‌شود؛
- اگر Source Date Unverified باشد، Date baseline حدسی Update نمی‌شود و `source_chronology_state=SOURCE_CHRONOLOGY_UNVERIFIED` می‌شود؛
- اگر Chronology مطابق `SRC-112` Re-anchor شود، State/Date طبق آن Rule Update می‌شوند؛
- Current Master data بدون تغییر می‌ماند؛
- Product Pricing State committed data بدون تغییر می‌ماند؛
- `promotion_status=NOT_REQUIRED`.

NO_CHANGES نیازمند model-computed source hash نیست و exact-same-source بودن را infer نمی‌کند.

### NCH-004 — No-Changes Integrity

وجود Warning مانع NO_CHANGES Approval نیست، ولی:

- هر Blocker فعال؛
- unresolved Source Chronology Gate؛
- Reconciliation Required؛
- یا Authority mismatch

مانع `APPROVED_NO_IMPORT_REQUIRED` است.

---

## 23. Rollback

### RLB-001 — Rollback یک Run جدید است

Rollback نباید Version قبلی را Restore-in-place کند.

مثال:

`v020 = ROLLBACK_FROM_v016`

Rollback:

`run_type=ROLLBACK`

است و Version جدید مصرف می‌کند.

### RLB-002 — Rollback Source Candidate

هر Historical Version که سابقه:

`IMPORT_SUCCESS + promotion_status=PROMOTED`

دارد، یک:

`rollback_source_candidate`

است.

Candidate بودن به معنی executable/import-ready بودن Rollback نیست.

### RLB-003 — داده دقیق تاریخی

Rollback Payload باید Data values همان Historical Successful Version را Restore کند.

نباید با Rulebook جدید:

- Price
- Sale Price
- visibility
- BOM-derived output

دوباره محاسبه یا Silent اصلاح شود.

Rollback target Dataset باید از Archived successful artifact/data reconstruct شود و provenance آن ثبت شود.

### RLB-004 — Current Non-Mutating Safety Gate

قبل از `IMPORT_READY` شدن Rollback، Validationهای فعلی فقط به‌صورت **Non-Mutating** اجرا می‌شوند.

حداقل Gates:

- target artifact readable/parseable؛
- current Import Contract compatibility؛
- Schema compatibility؛
- 95-column/active schema requirements؛
- Duplicate/Blank `Column1` validation؛
- Duplicate/Blank SKU validation؛
- row structural integrity؛
- Product Pricing State availability/compatibility؛
- Promotion/reconciliation safety؛
- Current Master parent/concurrency requirements برای Rollback Run.

Validation جاری حق ندارد Historical Price/Sale/visibility را Auto-Fix کند.

اگر Historical data برای عبور از Gate نیاز به mutation داشته باشد:

- Rollback exact execution Block می‌شود.
- Conflict توضیح داده می‌شود.
- Migration/Corrective handling فقط با Approval صریح و Run جدید انجام می‌شود.

### RLB-005 — Discount تاریخی

Sale Price Historical target دقیقاً Restore می‌شود مگر کاربر صریحاً تغییر جدیدی بخواهد.

اگر کاربر تغییر Discount جدید بخواهد، آن عمل دیگر «exact historical payload» نیست و باید به‌عنوان approved transformation در همان Rollback Run یا یک Corrective Run با Decision Trace ثبت شود؛ بدون Decision صریح ممنوع است.

### RLB-006 — Historical Product Pricing State

برای Versionهای موفقی که Product Pricing State Snapshot دارند، Rollback candidate شامل:

- Historical Master Dataset
- Historical Product Pricing State

است.

State نباید از Current Master Price حدس زده شود.

### RLB-007 — Legacy Rollback State Gap

برای Historical Successful Versionهای قبل از State Archive که `Product_Pricing_State` قابل بازیابی ندارند:

- Version همچنان `rollback_source_candidate` است.
- اگر State لازم برای safe future behavior deterministic از Archived evidence قابل reconstruct نباشد:

`ROLLBACK_STATE_UNAVAILABLE` -> Blocker.

سیستم نباید State را حدس بزند.

کاربر می‌تواند Corrective/Migration Run جداگانه را تأیید کند، ولی Historical Version rewrite نمی‌شود.

### RLB-008 — Schema/Identity Safety Failure

اگر Historical target با Schema/Identity Contract فعلی unsafe باشد:

- Schema incompatible -> `ROLLBACK_SCHEMA_INCOMPATIBLE`
- Duplicate/identity unsafe -> `ROLLBACK_IDENTITY_CONFLICT`

Rollback `technical_import_ready=false`.

Historical payload Silent تغییر نمی‌کند.

### RLB-009 — Rulebook Semantics

Rollback historical Price را با Rulebook فعلی Recompute نمی‌کند.

Rulebook فعلی فقط:

- workflow controls؛
- non-mutating safety validation؛
- audit/promotion rules

را برای Rollback اعمال می‌کند.

اگر Historical Product Pricing State با یک Permanent Rule فعلی تعارض material داشته باشد و exact rollback semantics را نامطمئن کند، Conflict باید Block شود و تصمیم/migration explicit لازم است.

### RLB-010 — Rollback Promotion

Rollback نیز:

- Final Summary
- User Approval
- Import Artifact Binding
- Import Success
- Promotion-time concurrency
- Product Pricing State commit

را نیاز دارد.

فقط بعد از Pass:

`promotion_status=PROMOTED`

و Current Master به Rollback Run جدید Promote می‌شود.

### RLB-011 — Rollback Readiness Terminology

سیستم نباید بگوید «هر Version موفق قابل Rollback است» به معنی guaranteed executable.

عبارت دقیق:

> هر Version موفق یک rollback source candidate است؛ اجرای Rollback فقط پس از Pass کردن Current Non-Mutating Safety Gates مجاز است.

---

## 24. Rulebook Governance

### RBK-001 — Rulebook Versioning

هر تغییر دائمی Rulebook باید Version را افزایش دهد.

Versioning:

- `MAJOR` — تغییر ناسازگار در رفتار/Contract
- `MINOR` — Rule جدید سازگار یا Capability جدید
- `PATCH` — Clarification غیررفتاری/اصلاح نگارشی

### RBK-002 — Change Log

هر Rulebook Version باید حداقل ثبت کند:

- Rule/Mapping تغییرکرده
- Old State
- New State
- Date/Time
- Approval Source
- Reason در صورت وجود

### RBK-003 — Rule تصویب‌شده مستقل از نتیجه Run

اگر در یک Run کاربر Rule دائمی را تصویب کند، آن Rule حتی اگر خود Run بعداً Reject/Fail شود، Active باقی می‌ماند؛ مگر کاربر صریحاً آن Decision را Current-Run-Only اعلام کرده باشد.

این Rule فقط Permanent Rulebook decisions را پوشش می‌دهد و نباید Runtime Product Pricing State transition را مستقل از Promotion Commit کند.

### RBK-004 — لغو Rule دائمی

Rule قبلی حذف تاریخی نمی‌شود. Rulebook Version جدید باید Rule قبلی را `deprecated/inactive` کند و Replacement را ثبت کند.

### RBK-005 — Single Rulebook Version per Output

Final Output یک Run نباید ترکیبی از چند Rulebook Version باشد.

اگر Active Rulebook Version در میانه Run به‌دلیل Permanent Decision تغییر کند:

- Full Dataset Reprocess مطابق `WF-012` الزامی است.
- Original Master Snapshot، Product Pricing State Start و Canonical Source IR مبنای Reprocess هستند.
- Selective reprocessing بر اساس «potentially affected rows» در v1 ممنوع است.
- Final Manifest فقط Version نهایی استفاده‌شده را به‌عنوان `rulebook_version_used` ثبت می‌کند و Start Version را نیز برای provenance نگه می‌دارد.

### RBK-006 — Rulebook Determinism Contract

هر Rule ماشینی که Mutation/Blocker ایجاد می‌کند باید حداقل مشخص کند:

- `rule_id`
- trigger predicate
- required evidence
- precedence/conflict behavior
- mutation authority
- issue code/severity
- output/state effect
- audit fields
- acceptance test reference

عبارت‌هایی مانند `certain`, `safe`, `reliable` نباید در Rulebook بدون operational predicate باقی بمانند.

### RBK-007 — Permanent Mapping Governance

Permanent Mapping:

- Rulebook versioned data است.
- Mapping ID یکتا دارد.
- Approval Decision ID دارد.
- Conflict با Source Evidence قطعی مطابق `MAT-002` Block می‌شود.
- Mapping priority حق مخفی‌کردن conflict را ندارد.

---

## 25. Schema Governance

### SCH-001 — schema_version

Schema باید Version مستقل داشته باشد.

هر schema version حداقل باید:

- exact ordered headers
- column count
- field identity metadata
- New Product required-field profile
- canonical serialization compatibility

را مشخص کند.

### SCH-002 — Change Approval

هر تغییر Header/Order/Column Count باید قبل از پردازش خودکار:

- Diff شود.
- به کاربر توضیح داده شود.
- تأیید شود.
- در `schema_version` جدید ثبت شود.
- با Import Contract و Rulebook Compatibility Validation شود.

تا آن زمان:

`MASTER_SCHEMA_MISMATCH` -> Blocker.

### SCH-003 — History

نسخه‌های قبلی Schema و Change Summary باید Immutable حفظ شوند.

### SCH-004 — New Product Required Field Profile

هر Schema فعال باید Profile:

`new_product_required_fields`

را داشته باشد.

برای هر field حداقل:

```json
{
  "header": "...",
  "required_for_new_product": true,
  "value_source_policy": "USER|EXPLICIT_GENERATOR|APPROVED_SUGGESTION|PRESCRIBED_DEFAULT",
  "auto_generation_allowed": false,
  "validation_rule_ids": []
}
```

Profile بخشی از `schema_version` است.

System حق ندارد صرفاً بر اساس اینکه Existing Rows بعضی fieldها را Blank دارند، required status New Product را حدس بزند.

### SCH-005 — Mid-Run Schema Change

Schema Version در Run Pin می‌شود.

اگر Schema Active در میانه Run تغییر کند:

- ادامه Silent ممنوع است.
- Final output با schema version قدیمی و جدید مخلوط نمی‌شود.
- فقط اگر Migration/Approval صریح برای همان Run وجود داشته باشد، Full Reprocess/Revalidation ممکن است.
- در غیر این صورت Run Block/Supersede می‌شود.

### SCH-006 — Import Contract Compatibility

هر Schema Version که برای Production استفاده می‌شود باید Compatibility آن با `import_contract_version` فعال در Acceptance Test اثبات شود.

Schema approval به‌تنهایی Import Contract compatibility را فرض نمی‌کند.

---

## 26. Known Approved Data Corrections

### DAT-001 — Historical Duplicate Column1

Duplicate تاریخی `Column1 = 533570909` باعث Cross-Contamination در Import شده بود.

تصمیم تأییدشده:

- `533570909` متعلق به «کلید لمسی هوشمند روشنایی یک پل تبدیل Zigbee | سری INNOVA» است.
- «پریز کابینتی کشویی مولتی فانکشن» به `533570920` اصلاح شده است.

این مورد باید به‌عنوان Regression Fixture برای Duplicate Identifier حفظ شود.

### DAT-002 — Regression نه Byte-Equality کور

Fixture موفق تاریخی شامل برخی ایرادهای Data Integrity شناخته‌شده بوده است. هدف Regression Test:

1. بازتولید منطق قیمت‌گذاری صحیح تاریخی.
2. تشخیص خطاهای تاریخی شناخته‌شده.
3. اثبات اینکه خروجی Rulebook جدید فقط در اصلاحات تأییدشده از Fixture قدیمی تفاوت دارد.

### DAT-003 — Composite Rounding Regression Evidence

برای Micro-Hardening `v1.1.1`، Historical Successful CSV و Old Supplier Price List برای تفاوت بین دو Candidate زیر بررسی شدند:

- Algorithm A: `ceil(sum(component_rial) / 10)`
- Algorithm B: `sum(ceil(component_rial / 10))`

محدوده بررسی Strict شامل Composite rows تاریخی از familyهای قابل شناسایی `FR2 + MC2` و `MCE + TR5` بود.

نتیجه Evidence Review:

- Target-family Composite rows observed: `381`
- Rows با Component pair قابل Resolve مستقیم و strict از Old Supplier Price List: `206`
- Discriminating rows که `Algorithm A != Algorithm B`: `0`
- Rows که `A == B`: `206`
- از این 206، Historical Successful `Price` برای `196` row با هر دو Candidate برابر بود.
- `10` row دارای Component pair resolvable بودند ولی Historical Price با هیچ‌یک از Candidateها برابر نبود؛ بنابراین برای Rounding Policy Evidence معتبر محسوب نمی‌شوند و نیازمند بررسی مستقل Mapping/Historical fixture هستند.
- `175` Target-family row با Evidence موجود برای Strict component-price trace کامل Resolve نشدند.
- Support انحصاری برای Algorithm A: `0`
- Support انحصاری برای Algorithm B: `0`

علت اصلی Non-Discrimination این است که Component Rial amounts موجود در testable historical pairs در نقطه‌ای بودند که Ceiling جداگانه و Ceiling پس از Sum نتیجه یکسان ایجاد می‌کرد.

حکم Historical Evidence:

`NOT_PROVEN_BY_AVAILABLE_HISTORICAL_EVIDENCE`

بنابراین Historical Regression **هیچ‌یک** از Algorithm A/B را انتخاب یا اثبات نکرد.

در `v1.2.0` ambiguity نه از طریق Historical inference، بلکه با **Explicit Business Policy Approval** بسته شد:

`ALGORITHM_A_EXPLICITLY_APPROVED`

Policy مصوب:

`Final Toman = ceil(sum(component_rial_i) / 10)`

یعنی Componentها ابتدا در Rial exact جمع می‌شوند و تنها یک Final Ceiling پس از Summation اعمال می‌شود. این Decision از این پس Normative Authority است؛ Historical Regression همچنان Non-Discriminating باقی می‌ماند.

## 27. Acceptance Criteria برای Rulebook v1

Rulebook v1 فقط زمانی Conformant است که همه Acceptance Criteria الزامی این بخش PASS شوند. هر Criterion باید با Fixture یا Artifact قابل مشاهده ارزیابی شود؛ عبارت‌های صرفاً تفسیری کافی نیستند.

### ACC-001 — Rule Traceability

PASS فقط اگر:

- همه Ruleهای normative این Specification که MUST/MUST NOT دارند در Rulebook به Rule ID/Control ماشینی یا explicit non-executable governance control Map شده باشند؛
- Traceability Matrix هیچ Rule mandatory بدون implementation/test reference نداشته باشد.

### ACC-002 — 95-Column Schema Preservation

PASS فقط اگر روی Successful Fixture:

- Header count = 95؛
- exact Header names برابر expected fixture باشند؛
- exact Header order برابر expected fixture باشد؛
- هر output row دقیقاً 95 field داشته باشد.

### ACC-003 — Historical Pricing Regression

Old Price List باید روی Historical Fixture با Rulebook v1 اجرا شود.

PASS فقط اگر:

- Expected pricing/availability values تعریف‌شده در Regression Expected Manifest بازتولید شوند؛
- هر Difference از Historical CSV یا در Known Approved Correction list باشد یا به‌عنوان unresolved regression failure ثبت شود؛
- Historical known bugs به‌عنوان expected correct behavior بازتولید نشوند؛
- برای هر Historical Policy comparison، Fixtureهایی که Candidate Algorithmها خروجی یکسان دارند به‌عنوان **non-discriminating evidence** ثبت شوند و به نفع هیچ Candidate شمرده نشوند؛
- `DAT-003` صادقانه به‌عنوان `NOT_PROVEN_BY_AVAILABLE_HISTORICAL_EVIDENCE` باقی بماند.

Historical Regression منبع اثبات Algorithm A نیست. Normative authority برای Composite Rounding در Rulebook v1، **Explicit Business Policy Approval in Master Specification v1.2.0** و `PRC-002/008/009` است.

### ACC-004 — Historical Duplicate Identifier

Fixture دارای Duplicate تاریخی `Column1 = 533570909` باید:

- `DUPLICATE_COLUMN1` تولید کند؛
- `technical_import_ready=false` کند؛
- هیچ Import-ready artifact تولید نکند.

### ACC-005 — Mutation Boundary

روی Fixture حاوی تغییرات مجاز و fieldهای غیرمجاز:

PASS فقط اگر:

- تمام fieldهای خارج Mutation authority byte/semantic-equivalent به Master start باقی بمانند؛
- فقط fieldهای دارای Rule صریح تغییر کنند؛
- هر Auto-Fix Audit Event داشته باشد.

### ACC-006 — Price Provenance Completeness

برای هر row با Price changed:

PASS فقط اگر یک `price_provenance_type` معتبر و تمام required trace fields همان Type موجود باشد.

هر missing required trace باید `UNRESOLVED_SOURCE_TRACE_MISSING` ایجاد کند.

### ACC-007 — Composite BOM Calculation and Rounding Policy

برای Fixtureهای همه خانواده‌های Composite شناخته‌شده، PASS فقط اگر:

1. BOM Component identities برابر Expected Mapping باشند؛
2. raw Component Source Prices و Units درست Trace شوند؛
3. normalized Component Rial values دقیق باشند؛
4. `total_rial = sum(component_rial_i)` با Integer/Decimal exact محاسبه شود؛
5. دقیقاً یک Final `ceil(total_rial / 10)` اعمال شود؛
6. هیچ Component-level Rial-to-Toman Ceiling قبل از Summation اعمال نشود؛
7. Final Toman برابر Expected Fixture باشد؛
8. Provenance تمام fieldهای `SUPPLIER_COMPOSITE` در `PRC-012` را داشته باشد.

Acceptance Suite باید حداقل یک **Synthetic Discriminating Arithmetic Fixture** داشته باشد که صرفاً semantics Rule را آزمون کند و Historical Evidence محسوب نشود.

Fixture الزامی نمونه:

- `fixture_type = SYNTHETIC_ACCEPTANCE_ONLY`
- `component_1_rial = 1,000,001`
- `component_2_rial = 1,000,001`
- Approved Algorithm A: `ceil(2,000,002 / 10) = 200,001 Toman`
- Prohibited Algorithm B: `ceil(1,000,001 / 10) + ceil(1,000,001 / 10) = 200,002 Toman`
- Expected Final Toman = `200,001`

اگر implementation نتیجه `200,002` یا هر نتیجه ناشی از Component-level Ceiling تولید کند، `ACC-007` FAIL است.

این Synthetic Fixture فقط برای اثبات Conformance arithmetic semantics است و MUST NOT به‌عنوان Historical یا Commercial transaction evidence معرفی شود.

### ACC-008 — Incomplete Source Mass-Deactivation Guard

با Source fixture دارای extraction/page failure:

PASS فقط اگر:

- Source state `INCOMPLETE` شود؛
- `UNRESOLVED_SOURCE_INCOMPLETE` ایجاد شود؛
- هیچ Product صرفاً به‌دلیل `NOT_FOUND` Deactivate نشود؛
- `technical_import_ready=false`.

### ACC-009 — Discount and Manual Override Lifecycle

Transition fixtures باید حداقل این حالات را Assert کنند:

- preserved discount valid؛
- preserved Sale Price >= new Regular Price -> Blocker؛
- Deactivation clears Sale Price؛
- Reactivation does not restore old Sale Price؛
- Active Manual Override + changed supplier price -> `MANUAL_OVERRIDE_REVIEW_REQUIRED`؛
- Override + product unavailable -> Deactivate and stage termination؛
- reactivation after terminated override -> Supplier Price, no old override؛
- same-run newly approved override does not self-trigger review.

PASS فقط اگر همه expected state/outputها exact باشند.

### ACC-010 — Warning-Only Technical Readiness

Fixture با Warning و بدون Blocker:

- `technical_import_ready=true`
- `run_status=AWAITING_APPROVAL` قبل از User Approval

باید باشد.

پس از explicit Final Approval:

- `run_status=IMPORT_READY`.

Warning نباید خودکار Block کند.

### ACC-011 — Blocker Prevents Import-Ready

برای هر Fixture با active blocking issue:

- `technical_import_ready=false`
- Run نباید به `AWAITING_APPROVAL` یا `IMPORT_READY` برود.
- Artifact نهایی نباید `IMPORT_READY` label داشته باشد.
- Draft در صورت تولید باید `DRAFT_NOT_IMPORT_READY` باشد.

### ACC-012 — Two-Layer Final Summary

PASS فقط اگر Summary برای هر دسته material:

- توضیح ساده فارسی «چه شد/چرا/اثر چیست» داشته باشد؛
- Technical Rule/Code/Old-New/Trace reference داشته باشد.

### ACC-013 — Manifest Reconciliation

PASS فقط اگر:

- تمام required Artifact Hashها در Manifest قابل resolve باشند؛
- Master/Source/Rulebook/State/Output hashes با Artifact bytes یا canonical representation match کنند؛
- Decision IDs به Decision Log موجود اشاره کنند؛
- Control Totals از Archive artifacts قابل recompute و برابر باشند.

### ACC-014 — Deterministic Source Completeness

حداقل سه Source fixture:

1. fully readable/covered؛
2. positively incomplete؛
3. completeness not deterministically decidable

باید به‌ترتیب exact states:

- `COMPLETE`
- `INCOMPLETE`
- `UNRESOLVED`

بدهند.

اجرای تکراری با extractor_version یکسان باید classification یکسان بدهد.

### ACC-015 — Persistent Product Pricing State

Fixture دو Run متوالی باید اثبات کند:

- Scope/Override state از committed Product Pricing State خوانده می‌شود، نه inference از Price؛
- Draft state transition قبل از Promotion committed نمی‌شود؛
- Import Failed/Partial committed state را تغییر نمی‌دهد؛
- successful Promotion proposed state را commit می‌کند.

### ACC-016 — Unknown-Date Chronology

سناریو:

1. verified dated source committed؛
2. unverified-date source accepted/promoted؛
3. later different dated source arrives.

PASS فقط اگر:

- Step 2 state را `SOURCE_CHRONOLOGY_UNVERIFIED` کند؛
- Step 3 بدون deterministic ordering/user chronology approval `SOURCE_CHRONOLOGY_RESOLUTION_REQUIRED` ایجاد کند؛
- upload timestamp به Supplier Date تبدیل نشود؛
- Re-anchor فقط مطابق `SRC-112` رخ دهد.

### ACC-017 — Rollback Candidate vs Execution Readiness

Historical `IMPORT_SUCCESS` version باید candidate باشد.

Fixture unsafe rollback باید اثبات کند:

- candidate status حفظ می‌شود؛
- current non-mutating safety gate Fail می‌شود؛
- historical payload Silent Mutate نمی‌شود؛
- `technical_import_ready=false`.

Fixture safe rollback باید بدون recalculating historical Price/Sale pass شود.

### ACC-018 — Deterministic Column1 Allocation

با reserved set ثابت:

- candidate اولیه = `max(reserved)+1`
- collision sequence طبق `NEW-005`
- output ID در چند replay یکسان

باشد.

Known historical ID reuse نباید رخ دهد.

Unusable reservation set باید `UNRESOLVED_COLUMN1_GENERATION` ایجاد کند.

### ACC-019 — Run Dimension and Transition Contract

State-machine tests باید Assert کنند:

- `run_type`, `run_status`, `run_result` مستقل‌اند؛
- legal paths `RUN-005` قبول؛
- direct `DRAFT -> IMPORT_SUCCESS` رد؛
- `IMPORT_READY` قبل از Final Approval غیرممکن؛
- `NO_CHANGES` path به `APPROVED_NO_IMPORT_REQUIRED` ختم شود.

### ACC-020 — Orthogonal Record State and Counting

Fixture باید Productی داشته باشد که هم:

- `change_status=UPDATED`
- `availability_status=ACTIVE|ACTIVATED`
- Warning issue

دارد.

PASS فقط اگر:

- هر محور مستقل صحیح باشد؛
- exclusive counts هر axis درست باشند؛
- issue event count باعث double-count row total نشود.

### ACC-021 — Canonical Source IR Replay

با `Normalized_Source_Catalog.json` archived و سایر authority snapshots ثابت:

PASS فقط اگر Rule Engine بدون re-reading PDF:

- همان Matching results؛
- همان Price/Availability results؛
- همان Proposed Product Pricing State؛
- همان Candidate semantic dataset

را تولید کند.

برای Rulebook v1، Matching fixture باید همچنین اثبات کند که equality یا resemblance بین WooCommerce `Column2 / SKU` و Source identifier به‌تنهایی `EXACT_SKU` Pricing Match ایجاد نمی‌کند. SKU Evidence فقط در حد مجاز `SCP-001` می‌تواند Scope assignment را پشتیبانی کند؛ Pricing Match باید یکی از Classهای Authoritative `MAT-011` باشد.

### ACC-022 — Mid-Run Rulebook Full Reprocess

اگر Permanent Rule در میانه Run نسخه Rulebook را تغییر دهد:

PASS فقط اگر:

- همه prior derived row results discard شوند؛
- Full Dataset از original snapshots + canonical IR دوباره پردازش شود؛
- Final Output فقط یک Rulebook Version داشته باشد؛
- هیچ mixed-version record وجود نداشته باشد.

### ACC-023 — External Authority Pinning

اگر Rulebook/PN/Schema/Product State/Import Contract active version در میانه Run تغییر کند:

PASS فقط اگر سیستم تغییر را تشخیص دهد و:

- reprocess/revalidate طبق Rule؛
- یا Block/Supersede

کند.

Silent continuation Fail است.

### ACC-024 — New Product Pending Blocker

New Product:

`DETECTED -> USER_APPROVED_FOR_CREATION -> PENDING_COMPLETION`

با missing required field باید:

- `NEW_PRODUCT_PENDING`
- `technical_import_ready=false`
- no appended row

ایجاد کند.

پس از تکمیل profile و validations:

- `READY_TO_APPEND`
- Append at end
- `change_status=ADDED`

شود.

`SKIPPED_THIS_RUN` باید non-blocking باشد.

### ACC-025 — Promotion Artifact Binding

Acceptance cases:

1. External imported-artifact checksum/receipt independently available and matches the approved Final Output evidence within its actual proof scope -> external-verification branch passes.
2. No external checksum/receipt + active Import Contract permits exact run-bound explicit `USER_ATTESTED` -> lower-assurance branch passes without cryptographic claim.
3. External verification mismatch -> `IMPORTED_ARTIFACT_MISMATCH` and Promotion blocked.
4. Neither admissible external proof nor permitted run-bound attestation -> `IMPORT_ASSURANCE_INSUFFICIENT` and Promotion blocked.

Before handoff, Final Output MUST already be bound to `PERSISTED_OUTPUT_PROVIDER_IDENTITY_V1`. Successful import alone is not proof of exact provider artifact bytes.

### ACC-026 — Deterministic End-to-End Replay

With the same semantic Inputs—Canonical Source IR, Master Snapshot, PPS Snapshot, Rulebook, Schema, Part Number Reference, Import Contract and User Decisions—two independent executions MUST produce the same semantic business result and conform to the same Canonical CSV serialization contract and PPS state-transition contract.

Byte-identical CSV or checksum equality MAY only be asserted when an actual independent byte-comparison/checksum executor is available and invoked. The normal no-code Runtime MUST NOT compute `final_csv_hash` or Proposed State hash merely to close replay.

For each Composite Repricing case with valid BOM/Component Evidence, both executions MUST reproduce the approved `COMPOSITE_ROUNDING_SUM_RIAL_THEN_CEIL_V1` policy, component provenance, `total_rial`, applicable Rule ID and Final Toman. Synthetic fixture `ACC-007` must discriminate approved Algorithm A from prohibited Algorithm B.

### ACC-027 — No Partial Promotion and Recovery Gate

Import Partial بدون Owner Exception باید:

- `run_status=IMPORT_PARTIAL`
- no Current Master promotion
- no Product Pricing State commit
- `PRODUCTION_RECONCILIATION_REQUIRED`

تولید کند.

تا Resolution، Promotion normal run جدید باید Block شود.

Import Partial با exact Owner Exception معتبر باید:

- `run_status=IMPORT_PARTIAL` را حفظ کند؛
- `promotion_status=PROMOTED_WITH_OPEN_RECONCILIATION` تولید کند؛
- فقط Subjectهای دقیق استثناء را در Queue نگه دارد؛
- Current Master/PPS/Current Set/Manifest را به Full Dataset مصوب و Queue باز bind کند؛
- Error جدید، Scope جدید، provider-identity/CAS mismatch یا externally supplied checksum mismatch را Block کند.

### ACC-028 — Stale Run Promotion Guard

دو Concurrent Run با Parent Master یکسان ایجاد شوند.

اگر Run B زودتر Promote شود، Run A در Promotion Gate باید:

`STALE_RUN_PROMOTION_CONFLICT`

بگیرد و حق overwrite Current Master را نداشته باشد.

### ACC-029 — Canonical Master Fingerprint

**Active v1.14.3 disposition: provider-native concurrency acceptance.**

Fixture A: two fresh reads of the unchanged governed Current Master MUST return the same `resource_id + provider_revision_id`.

Fixture B: expected revision != fresh current revision MUST yield `MASTER_CHANGED_DURING_RUN` or `STALE_RUN_PROMOTION_CONFLICT` according to gate position.

Fixture C: when correctness depends on an exact cell value, targeted Sheet read-back MUST verify the actual authoritative value; revision identity alone is insufficient.

Historical Master fingerprints may remain immutable evidence but are not recomputed by the normal no-code runtime.

### ACC-030 — Color Precedence

Fixture که Series/Variant قطعی وجود دارد ولی Color Master در current complete Source unsupported است باید:

`UNRESOLVED_COLOR_NOT_SUPPORTED`

ایجاد کند و Generic `NOT_FOUND` Deactivation نباید اعمال شود.

### ACC-031 — Component Absence vs BOM Conflict

دو Fixture:

1. expected component code absent in COMPLETE source, no replacement evidence -> Deactivate.
2. source positively indicates different component code/structure -> `UNRESOLVED_BOM_CONFLICT`.

هر دو باید outcomes متفاوت deterministic داشته باشند.

### ACC-032 — Final Price Domain

Fixtures شامل:

- valid positive
- zero
- blank
- nonnumeric
- negative

باید outcomes `MST-008/009` و `PRC-013` را exact تولید کنند. Negative نباید visibility mutation با حدس ایجاد کند.

### ACC-033 — Canonical CSV Serialization

Dataset fixture با:

- delimiter داخل text؛
- double quote؛
- embedded newline؛
- blank fields؛
- leading/trailing whitespace

باید در replayهای مستقل bytes و SHA-256 یکسان تولید کند و پس از CSV parse exact 95-field semantic values را حفظ کند.

### ACC-034 — Import Contract Compatibility

Rulebook v1 Production Acceptance فقط وقتی PASS است که:

- `import_contract_version/hash` تثبیت شده باشد؛
- canonical output fixture توسط همان WP All Import Mapping Contract با intended fields تفسیر شود؛
- mismatch contract/version قابل تشخیص باشد.

### ACC-035 — Product State / Master Reconciliation

Fixture با Active Override یا Excluded Price که Product Pricing State price با Current Master Price conflict دارد باید:

`PRODUCT_PRICING_STATE_MASTER_MISMATCH`

و `technical_import_ready=false`

ایجاد کند؛ سیستم حق inference silent از یکی به نفع دیگری ندارد.

---

## 28. کنترل‌های محافظه‌کارانه منتخب برای موارد تعیین‌نشده

بر اساس اختیار داده‌شده برای انتخاب جزئیات استاندارد و محافظه‌کارانه، این Defaults اعمال می‌شوند مگر در آینده Rule دیگری تصویب شود:

### DEF-001 — Money Arithmetic

همه محاسبات پولی با Decimal/Integer و Rounding صریح انجام می‌شود.

Binary floating point در Pricing/Discount/Percentage comparison ممنوع است.

### DEF-002 — Timestamp

Timestampهای Audit با ISO 8601 و Timezone ذخیره می‌شوند.

### DEF-003 — Cryptographic Evidence Boundary

`SHA-256` همچنان الگوریتم پیش‌فرض persisted/build/history checksumهای موجود سیستم است، اما normal no-code runtime فقط checksumی را می‌تواند consume کند که Provider، external system یا governed build artifact واقعاً ارائه کرده باشد.

مدل حق ندارد SHA/checksum را از bytes یا canonical representation محاسبه یا جعل کند مگر یک executor واقعی و مجاز صریحاً در همان Runtime available و invoked باشد.

هر checksum باید به representation تعریف‌شده همان Artifact مربوط باشد؛ hash مبهم روی «داده مشابه» معتبر نیست.

### DEF-004 — Immutable Closed Manifest

Manifest پس از Close Run بازنویسی نمی‌شود؛ Correction با Event/Run جدید ثبت می‌شود.

### DEF-005 — Deterministic Output Contract

با Source Canonical IR، Master Snapshot، Product Pricing State، Rulebook، Schema، PN Reference، Import Contract و User Decisions یکسان، Output باید طبق serialization/business contracts یکسان باشد.

این یک semantic/serialization conformance requirement است. ادعای byte-identical replay یا identical hash فقط وقتی مجاز است که executor/provider مستقل واقعاً bytes/checksum را compare کرده باشد.

Normal no-code runtime برای Final CSV یا Proposed PPS hash تازه محاسبه نمی‌کند؛ persisted provider identity/read-back safety semantics را حمل می‌کند.

### DEF-006 — No Silent Recovery Outside Allowlist

اگر سیستم خطای واضحی در field خارج Allowlist ببیند، فقط گزارش می‌دهد؛ اصلاح خودکار ممنوع است.

### DEF-007 — Draft vs Posted State

Runهای `DRAFT/VALIDATED/AWAITING_APPROVAL` از committed/promoted state جدا نگه داشته می‌شوند، مشابه جداسازی Unposted/Posted در سیستم‌های مالی.

### DEF-008 — Compensating Correction

اصلاح بعد از Promotion با Corrective Run انجام می‌شود، نه ویرایش تاریخچه.

### DEF-009 — Control Totals

قبل و بعد از Output، Control Totals محورهای مستقل محاسبه و در Report ثبت می‌شوند تا خطای گسترده سریع دیده شود.

### DEF-010 — No Partial Commit

هیچ Subset از Import Partial Promote نمی‌شود. مسیر `GOV-007A` در صورت مجازبودن، فقط **Full Dataset مصوب** را همراه با exact open-reconciliation queue Promote می‌کند؛ Promotion یک Subset یا پنهان‌کردن Failure همچنان ممنوع است.

### DEF-011 — Reprocess-All over Dependency Graph

برای Dataset فعلی، تغییر Authority Rulebook/PN که re-evaluation لازم دارد با Full Dataset Reprocess حل می‌شود.

ساخت dependency graph selective برای v1 لازم نیست و نباید جایگزین Full Reprocess شود.

### DEF-012 — No Hidden Chronology Guess

Filename date، upload time، filesystem timestamp یا conversational order به‌تنهایی Supplier Source Chronology ایجاد نمی‌کنند.

### DEF-013 — Canonical Blank Semantics

در Data Contract:

- Blank cell = empty string semantic value مگر field-specific Rule خلاف آن را بگوید.
- literal `"0"` با Blank یکسان نیست.
- `Price=0` و `Sale Price=0` semantics مطابق Rules تخصصی خودشان دارند.

### DEF-014 — Closed Run Inputs

پس از `IMPORT_READY`, تغییر Material Input/Decision/Authority نباید همان Run را silently mutate کند. Run Supersede و Version جدید default محافظه‌کارانه است.

---

## 29. State Registry و Technical Code Registry

### 29.1 Run State Axes

`run_type`:

- `NORMAL`
- `RERUN_SAME_SOURCE`
- `ROLLBACK`
- `CORRECTIVE`

`run_status`:

- `DRAFT`
- `VALIDATED`
- `AWAITING_APPROVAL`
- `IMPORT_READY`
- `IMPORT_SUCCESS`
- `IMPORT_FAILED`
- `IMPORT_PARTIAL`
- `REJECTED`
- `SUPERSEDED`
- `APPROVED_NO_IMPORT_REQUIRED`

`run_result`:

- `CHANGES`
- `NO_CHANGES`

`promotion_status`:

- `NOT_REQUIRED`
- `PENDING`
- `PROMOTED`
- `BLOCKED`

### 29.2 Record State Axes

`change_status`:

- `UNCHANGED`
- `UPDATED`
- `ADDED`

`availability_status`:

- `ACTIVE`
- `INACTIVE`
- `ACTIVATED`
- `DEACTIVATED`
- `UNRESOLVED`

`scope_status`:

- `SUPPLIER_MANAGED`
- `EXCLUDED_FROM_PRICE_LIST`

`max_issue_severity`:

- `NONE`
- `INFO`
- `WARNING`
- `BLOCKER`

### 29.3 New Product Lifecycle

- `DETECTED`
- `USER_APPROVED_FOR_CREATION`
- `PENDING_COMPLETION`
- `READY_TO_APPEND`
- `SKIPPED_THIS_RUN`
- `REJECTED`

`NEW_PRODUCT_SKIPPED_THIS_RUN` یک user-facing/technical lifecycle label برای `SKIPPED_THIS_RUN` است و Error Code محسوب نمی‌شود.

### 29.4 Source State

`source_completeness_status`:

- `COMPLETE`
- `INCOMPLETE`
- `UNRESOLVED`

`source_chronology_state`:

- `VERIFIED`
- `SOURCE_CHRONOLOGY_UNVERIFIED`

### ERR-001 — Technical Code Metadata Contract

هر Technical Code باید metadata زیر داشته باشد:

- `code`
- `category`
- `default_severity`
- `blocks_import`
- `scope`
- `auto_fix_allowed`

Categories مجاز حداقل:

- `DATA_INTEGRITY`
- `SOURCE_VALIDATION`
- `MATCHING`
- `PRICING`
- `DISCOUNT`
- `GOVERNANCE`
- `WORKFLOW`
- `AUTO_FIX`
- `AUDIT`
- `ROLLBACK`
- `PROMOTION`

اگر Code جدیدی در Rulebook اضافه شود، قبل از استفاده Production باید در Registry Versioned ثبت شود.

### ERR-002 — Technical Code Registry

| Code | Category | Default Severity | Blocks Import | Scope | Auto-Fix Allowed | Semantics |
|---|---|---:|---:|---|---:|---|
| `DUPLICATE_COLUMN1` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | NO | Duplicate Unique Identifier |
| `DUPLICATE_SKU` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | NO | Duplicate SKU under case-insensitive uniqueness |
| `MISSING_UNIQUE_IDENTIFIER` | DATA_INTEGRITY | BLOCKER | YES | RECORD | NO | Blank Column1 |
| `MISSING_SKU` | DATA_INTEGRITY | BLOCKER | YES | RECORD | NO | Blank Column2 |
| `MISSING_PRICE` | PRICING | BLOCKER | YES | RECORD | NO | Price blank and not deterministically recoverable |
| `UNRESOLVED_INVALID_MASTER_PRICE` | PRICING | BLOCKER | YES | RECORD | NO | Master Price invalid and not deterministically repairable |
| `INVALID_MASTER_PRICE_AUTO_REPAIRED` | AUTO_FIX | INFO | NO | RECORD | YES | Invalid Master Price repaired from authoritative source/rule |
| `UNRESOLVED_INVALID_PRICE` | PRICING | BLOCKER | YES | RECORD/SOURCE | NO | Numeric price violates allowed domain, such as negative |
| `MASTER_SCHEMA_MISMATCH` | DATA_INTEGRITY | BLOCKER | YES | RUN | NO | Master schema differs from active schema contract |
| `MASTER_CHANGED_DURING_RUN` | WORKFLOW | BLOCKER | YES | RUN | NO | Current Master provider resource/revision differs from expected identity before finalization |
| `STALE_RUN_PROMOTION_CONFLICT` | PROMOTION | BLOCKER | YES | PROMOTION | NO | Fresh Current Master provider identity differs from expected parent at promotion |
| `SOURCE_PRICE_LIST_OLDER_THAN_BASELINE` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | NO | Verified source date older than verified baseline |
| `SOURCE_DATE_UNVERIFIED` | SOURCE_VALIDATION | WARNING | NO | SOURCE | NO | Supplier source date cannot be verified |
| `UNRESOLVED_SOURCE_DATE_CONFLICT` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | Multiple candidate source dates cannot be deterministically resolved |
| `SOURCE_CHRONOLOGY_UNVERIFIED` | SOURCE_VALIDATION | WARNING | NO | SOURCE/RUN | Accepted unknown-date source has broken verified chronology chain |
| `SOURCE_CHRONOLOGY_RESOLUTION_REQUIRED` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE/RUN | New source ordering cannot be proven while chronology is unverified |
| `DUPLICATE_SOURCE_FILE` | WORKFLOW | BLOCKER | YES | SOURCE/RUN | Exact same Source artifact identity is PROVEN; explicit rerun decision required while unresolved; same semantic material alone must not trigger this code |
| `UNRESOLVED_SOURCE_INCOMPLETE` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | Positive evidence source is incomplete |
| `UNRESOLVED_SOURCE_COMPLETENESS` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE | Completeness cannot be deterministically classified as complete |
| `UNRESOLVED_INVALID_PRICE_FORMAT` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE/RECORD | Price text cannot be parsed unambiguously |
| `UNRESOLVED_SOURCE_CONFLICT` | SOURCE_VALIDATION | BLOCKER | YES | SOURCE/RECORD | Same source code has materially conflicting effective specification |
| `DUPLICATE_SOURCE_RECORD_CONSISTENT` | SOURCE_VALIDATION | WARNING | NO | SOURCE | Duplicate source records are semantically identical and deduplicated logically |
| `UNRESOLVED_MATCH_CONFLICT` | MATCHING | BLOCKER | YES | RECORD | Authoritative match evidence conflicts |
| `UNRESOLVED_SOURCE_TO_MULTIPLE_PRODUCTS` | MATCHING | BLOCKER | YES | RECORD/RUN | Direct source code maps to multiple products without explicit one-to-many rule |
| `UNRESOLVED_MULTIPLE_SOURCES_FOR_PRODUCT` | MATCHING | BLOCKER | YES | RECORD | Multiple source codes map to one product without primary/alias/BOM rule |
| `UNRESOLVED_COLOR_NOT_SUPPORTED` | MATCHING | BLOCKER | YES | RECORD | Series/variant exists but Master color is unsupported in current source |
| `UNRESOLVED_PART_NUMBER_CONFLICT` | MATCHING | BLOCKER | YES | RECORD | Part Number semantics conflict with Master/match evidence |
| `WARNING_MISSING_PART_NUMBER` | MATCHING | WARNING | NO | RECORD | Part Number missing but authoritative match exists through other evidence |
| `PART_NUMBER_REFERENCE_CONFLICT` | GOVERNANCE | WARNING | NO | GOVERNANCE | New PN reference conflicts with active rulebook; new reference activation blocked, prior active remains |
| `UNRESOLVED_BOM_CONFLICT` | PRICING | BLOCKER | YES | RECORD | Source positively conflicts with stored BOM/component structure |
| `UNRESOLVED_SOURCE_TRACE_MISSING` | AUDIT | BLOCKER | YES | RECORD | Required price provenance trace is incomplete |
| `WARNING_PRICE_CHANGE_GT_50_PERCENT` | PRICING | WARNING | NO | RECORD | Exact positive-to-positive price change exceeds 50% |
| `PRICE_VISIBILITY_SYNC` | AUTO_FIX | INFO | NO | RECORD | visibility synchronized from valid final Price |
| `UNRESOLVED_INVALID_DISCOUNT` | DISCOUNT | BLOCKER | YES | RECORD | Discount percent outside allowed range |
| `UNRESOLVED_INVALID_DISCOUNT_PRECISION` | DISCOUNT | BLOCKER | YES | RECORD | Discount percent has more than two decimal places |
| `UNRESOLVED_INVALID_SALE_PRICE` | DISCOUNT | BLOCKER | YES | RECORD | Sale Price malformed/negative/invalid |
| `UNRESOLVED_SALE_PRICE_CONFLICT` | DISCOUNT | BLOCKER | YES | RECORD | Sale Price greater than or equal to Final Regular Price |
| `UNRESOLVED_DISCOUNT_DEFINITION_CONFLICT` | DISCOUNT | BLOCKER | YES | RECORD | Percentage and direct Sale Price resolve to different values |
| `DISCOUNT_DEFINITIONS_CONSISTENT` | DISCOUNT | INFO | NO | RECORD | Two explicit discount definitions resolve to same final Sale Price |
| `MANUAL_OVERRIDE_REVIEW_REQUIRED` | PRICING | BLOCKER | YES | RECORD | Active committed override differs from new valid supplier price |
| `PRODUCT_PRICING_STATE_MASTER_MISMATCH` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | Persistent commercial state conflicts with Current Master |
| `UNRESOLVED_SCOPE` | GOVERNANCE | BLOCKER | YES | RECORD | Product pricing scope cannot be deterministically established |
| `WARNING_COLUMN1_HISTORY_INCOMPLETE` | DATA_INTEGRITY | WARNING | NO | RUN/RECORD | Identifier generation guarantee limited to known readable history |
| `UNRESOLVED_COLUMN1_GENERATION` | DATA_INTEGRITY | BLOCKER | YES | RECORD/RUN | Deterministic safe identifier allocation cannot be established |
| `NEW_PRODUCT_PENDING` | WORKFLOW | BLOCKER | YES | RECORD/RUN | User approved creation but schema-required row data is incomplete |
| `EXTERNAL_AUTHORITY_CHANGED_DURING_RUN` | GOVERNANCE | BLOCKER | YES | RUN | Pinned authority version changed and requires reprocess/revalidation |
| `IMPORTED_ARTIFACT_MISMATCH` | PROMOTION | BLOCKER | YES | PROMOTION | Imported artifact hash differs from approved final CSV hash |
| `IMPORT_ASSURANCE_INSUFFICIENT` | PROMOTION | BLOCKER | YES | PROMOTION | Neither hash verification nor run-bound explicit user attestation is available |
| `PRODUCTION_RECONCILIATION_REQUIRED` | WORKFLOW | BLOCKER | YES | RUN/PROMOTION | Production may not match committed Current Master after partial/uncertain import |
| `ROLLBACK_STATE_UNAVAILABLE` | ROLLBACK | BLOCKER | YES | RUN | Historical pricing state required for safe rollback cannot be reconstructed |
| `ROLLBACK_SCHEMA_INCOMPATIBLE` | ROLLBACK | BLOCKER | YES | RUN | Historical payload cannot be imported safely under current schema/import contract |
| `ROLLBACK_IDENTITY_CONFLICT` | ROLLBACK | BLOCKER | YES | RUN | Historical rollback payload violates current identity safety gates |

#### Historical / Deprecated Code — Non-Active

`UNRESOLVED_COMPOSITE_ROUNDING_POLICY` یک Code تاریخی است که در `v1.1.1` برای Block کردن Policy نامشخص Composite Rounding استفاده شد.

- `deprecated_in = 1.2.0`
- Active Technical Code Registry: **NO**
- Rulebook v1 conforming to `v1.2.0` MUST NOT emit this Code.
- Historical Manifest/Change Log references MAY retain it for provenance.
- Runtime Composite failures باید با Codeهای واقعی مربوط به علت، مانند `UNRESOLVED_BOM_CONFLICT`, `UNRESOLVED_SOURCE_TRACE_MISSING`, Source Price errors یا Match conflicts گزارش شوند.

### ERR-003 — Resolved Issue Semantics

`blocks_import=YES` یعنی Code در حالت **active/unresolved** مانع readiness است.

پس از Resolution:

- Issue Event در Audit حفظ می‌شود.
- `resolved=true` می‌شود.
- دیگر readiness را Block نمی‌کند مگر Rule مربوطه state blocking دیگری ایجاد کند.

نمونه صریح: `DUPLICATE_SOURCE_FILE` پیش از Rerun Approval یک `BLOCKER` فعال است؛ پس از Approval مطابق `SRC-107` همان Event با `resolved=true` حفظ می‌شود و دیگر Blocking نیست. برای حل این Workflow Gate نباید Severity آن به `WARNING` تغییر داده شود.

### ERR-004 — No Duplicate Semantics

Rulebook نباید برای یک condition واحد دو Code مختلف با outcome یکسان ایجاد کند مگر تفاوت Domain/Recovery واقعاً لازم باشد.

اگر Code جدید overlap دارد، Registry update باید precedence و دلیل تمایز را ثبت کند.

---

## 30. Run Manifest — حداقل Schema مفهومی

Manifest باید برای هر Run standalone provenance کافی داشته باشد تا Execution Context، Decisions، Output، Import و Promotion قابل Reconcile باشند.

### Identity

حداقل:

- `run_id`
- `run_version`
- `run_type`
- `run_reason`
- `run_status`
- `run_result`
- `created_at`
- `closed_at`
- `technical_import_ready`
- `promotion_status`

### Source

حداقل:

- `source_filename`
- `source_artifact_binding`
- `source_date_original`
- `source_date_calendar`
- `source_date_normalized`
- `source_date_verification_status`
- `source_completeness_status`
- `source_chronology_state_start`
- `source_chronology_state_final`
- `last_processed_price_list_date_start`
- `source_reconciliation_evidence_ref`
- `extractor_version`
- `engine_version`
- Source coverage/conservation checks and optional external checksum when actually supplied

### Master

حداقل:

- `master_version_start`
- `expected_parent_master_resource_id`
- `expected_parent_master_revision_id`
- `master_provider_identity_start`
- `master_provider_identity_final_check`
- `master_provider_identity_promotion_check`
- `schema_version_start`
- `schema_version_used`
- `row_count_start`
- `row_count_output`

### Product Pricing State

حداقل:

- `product_pricing_state_version_start`
- `product_pricing_state_provider_identity_start`
- `product_pricing_state_proposed_provider_identity`
- `product_pricing_state_committed_version` در صورت Promotion
- `product_pricing_state_committed_provider_identity` در صورت Promotion
- State transition counts

### Rule Engine / External Authorities

حداقل:

- `rulebook_version_start`
- `rulebook_version_used`
- `rulebook_snapshot_hash`
- `part_number_reference_version_start`
- `part_number_reference_version_used`
- `part_number_reference_hash`
- `schema_version_start`
- `schema_version_used`
- `import_contract_version_start`
- `import_contract_version_used`
- `import_contract_hash`

اگر Authority در میانه Run تغییر کرده باشد، Manifest باید:

- old version
- new version
- change detection timestamp
- resolution method
- full reprocess indicator

را ثبت کند.

### Reconciliation

حداقل:

- Record Counts
- `change_status_counts`
- `availability_status_counts`
- `scope_status_counts`
- `max_issue_severity_counts`
- `issue_event_counts_by_code`
- Control Totals Before/After
- Total Price Before/After
- Active Sale Price count Before/After
- visible/hidden count Before/After
- Reconciliation PASS/FAIL
- explanation برای هر unexpected delta

### Decisions

برای هر User Decision:

- `decision_id`
- Topic
- Decision Code
- Timestamp + Timezone
- Outcome
- Scope: `CURRENT_RUN|PERMANENT_RULE`
- Effect on Run
- Approval Reference
- Related Product/Source/Rule IDs
- Rulebook Version در صورت Permanent Rule

Chronology، Discount، New Product، Manual Override، Scope Change، Final Approval و Import Attestation باید در صورت وقوع Decision records صریح داشته باشند.

### Output

حداقل:

- Candidate/Draft artifact names در صورت وجود
- Draft provider artifact identity در صورت وجود
- `final_csv_filename`
- `final_output_provider_identity` (`provider + resource_id + provider_revision_id + run_id + run_version + read_back`)
- Audit Report provider artifact identity؛ external/build checksum فقط اگر واقعاً supplied
- Rulebook selected version/resource identity + persisted selected hash when supplied
- `source_reconciliation_evidence_ref`
- `product_pricing_state_proposed_provider_identity`
- Final `technical_import_ready`
- Final Approval Decision ID
- Final Approval Timestamp

### Import

حداقل:

- WP All Import Start/End در صورت موجود
- `import_outcome`
- Import Log Hash در صورت موجود
- `imported_filename`
- `imported_artifact_hash` در صورت موجود
- `imported_run_id`
- `imported_run_version`
- `import_confirmation_method`
- `import_assurance_level`
- User Confirmation Timestamp
- Import Evidence Reference

### Promotion

حداقل:

- `promotion_status`
- Promotion Gate results
- Parent Master check result
- Artifact binding result
- `promoted_master_version` در صورت موفقیت
- `promoted_master_provider_identity`
- committed Product Pricing State version/provider identity
- source baseline metadata before/after
- chronology state before/after
- promotion timestamp
- failure/block reason در صورت Blocked

### Recovery / Rollback

اگر Run از نوع `CORRECTIVE` یا `ROLLBACK` باشد، حداقل:

- source/related historical run ID
- target historical version در Rollback
- rollback source candidate evidence
- rollback safety gate results
- production reconciliation state start/end
- corrective reason
- related failed/partial/promotion-blocked run IDs

### Manifest Referential Integrity

هر Version/Provider Identity/Decision ID/Artifact Reference الزامی و هر persisted/external Hash مصرف‌شده باید:

- به Artifact یا record موجود اشاره کند؛
- یا صریحاً با reason `NOT_AVAILABLE` و Rule مجاز مشخص شود.

Manifest نباید با reference جعلی یا unresolved silent close شود.

---

## 31. سیاست توضیح خطا برای کاربر

سیستم نباید فقط Error Code نمایش دهد. قالب توضیح برای Blocker:

1. **چه محصولی؟**
2. **چه چیزی انتظار می‌رفت؟**
3. **چه چیز متفاوتی پیدا شد؟**
4. **این اطلاعات از کجا آمده؟**
5. **چرا سیستم خودش تصمیم نمی‌گیرد؟**
6. **اگر گزینه A انتخاب شود چه می‌شود؟**
7. **اگر گزینه B انتخاب شود چه می‌شود؟**
8. **پیشنهاد سیستم چیست و Confidence چقدر است؟**
9. **دقیقاً چه پاسخی از کاربر لازم است؟**

این الگو برای جلوگیری از Technical Overload و در عین حال حفظ Auditability الزامی است.

---

## 32. Boot Behavior پیشنهادی برای Project

هنگامی که کاربر می‌نویسد `شروع`، سیستم باید به‌صورت کوتاه و عملی:

1. اعلام کند آماده اجرای Price Update Run است.
2. آخرین Price List را درخواست کند.
3. Current Master، Product Pricing State و Authority Versionها را Load/Pin کند.
4. Source binding/Date/Chronology/Completeness را Validate کند.
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

Output Format باید با exact active Import Contract identity (`version + resource_id + persisted selector identity/hash when supplied`) سازگار بماند. Runtime برای اثبات این identity hash تازه محاسبه نمی‌کند.

Promotion باید به Artifact Import‌شده با قوی‌ترین Evidence واقعاً در دسترس Bind شود و Assurance Level را صادقانه ثبت کند. `USER_ATTESTED` هرگز cryptographic/byte proof نیست.

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

Canonical CSV serialization و Master value model باید independent از UI metadata و implementation-specific formatting غیرضروری باشند.

با inputs یکسان، serializer باید طبق contract نتیجه یکسان تولید کند؛ اما normal no-code runtime بدون byte/checksum executor حق ادعای identical bytes/hash ندارد. Current Master concurrency از provider resource/revision و exact value correctness از targeted read-back تأمین می‌شود.

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

این Revision Architecture یا Approved Pricing/Discount/Availability Policy جدیدی معرفی نمی‌کند. دو ambiguity/semantic defect را با بازگشت به Authority قبلی اصلاح می‌کند و در مورد Composite Rounding از انتخاب تجاری بدون Evidence خودداری می‌کند. چون Rounding Policy جدید تصویب نشده است، Revision یک Capability تجاری جدید ایجاد نمی‌کند؛ بلکه Claim نادرست readiness را تصحیح و Gate لازم را اضافه می‌کند.

#### Micro-Hardeningهای این Revision

- **Issue 1 / CLARIFICATION:** `DUPLICATE_SOURCE_FILE` در Registry از `WARNING + blocks_import=YES` به `BLOCKER + blocks_import=YES` اصلاح شد. بعد از explicit Rerun Approval همان Audit Event با `resolved=true` باقی می‌ماند و Non-Blocking می‌شود؛ `WARNING` globally Non-Blocking باقی ماند.
- **Issue 2 / POLICY GUARD:** `EXACT_SKU` از `MAT-001` و `MAT-011` به‌عنوان Authoritative Pricing Match Method حذف شد. `SCP-001` همچنان SKU قطعی را فقط به‌عنوان Scope-assignment Evidence حفظ می‌کند. Historical evidence موجود Source-side WooCommerce SKU domain مستقلی را برای Pricing Match اثبات نکرد.
- **Issue 3 / EVIDENCE GUARD:** Operational choice `sum Rial -> one ceil` از `PRC-008/009` برداشته شد، زیرا `v1.0.0` ترتیب عملیات را تصویب نکرده بود و Historical regression هیچ discriminating fixture ارائه نکرد. `UNRESOLVED_COMPOSITE_ROUNDING_POLICY` به‌عنوان Blocker اضافه شد.
- **Regression Evidence:** `DAT-003` نتایج مقایسه A/B را ثبت کرد: 381 target-family rows، 206 strict-resolvable pairs، 0 discriminating case، 196 historical matches for both، 10 neither، 175 insufficient strict trace.
- **Acceptance:** `ACC-003`, `ACC-007`, `ACC-021`, `ACC-026` فقط در محدوده سه Issue فوق اصلاح شدند.
- **Readiness:** Status از `READY_FOR_RULEBOOK_IMPLEMENTATION` به `HARDENED_BUT_NOT_RULEBOOK_READY` تغییر کرد تا Resolution تجاری Composite Rounding انجام شود.

#### Protected Architecture

تمام Hardeningهای unrelated `v1.1.0`، از جمله Product Pricing State، Source Completeness/Chronology، Canonical Source IR، Authority Pinning، Run/Record state separation، Full Reprocess، Rollback safety، Import Artifact Binding، No Partial Promotion، Production Reconciliation Guard، Canonical CSV/Fingerprint و Acceptance architecture بدون تضعیف حفظ شدند.

### v1.1.0 — 2026-08-11

**وضعیت:** `READY_FOR_RULEBOOK_IMPLEMENTATION`

**نوع Version:** MINOR

دلیل MINOR:

این Revision سیاست تجاری اصلی `v1.0.0` را تغییر نمی‌دهد، اما State Modelها، Canonical Artifactها، Governance/Promotion controls و Acceptance obligations جدید و backward-compatible اضافه می‌کند؛ بنابراین فقط PATCH نیست و تغییر ناسازگار Business Policy برای MAJOR نیز رخ نداده است.

#### Hardeningهای اصلی

- **F-001 / HARDENING:** `SRC-108..109` از معیار subjective completeness به State/Check model قطعی `COMPLETE|INCOMPLETE|UNRESOLVED` تبدیل شد.
- **F-002 / BEHAVIORAL_CONTRACT:** `PPS-*` برای persistent Product Pricing State، staged/committed semantics و provenance اضافه شد.
- **F-003 / HARDENING:** `RLB-*` rollback candidate را از execution readiness جدا کرد و current safety validation را non-mutating ساخت.
- **F-006 / BEHAVIORAL_CONTRACT:** `SRC-110..112` unknown-date chronology state و re-anchor contract را تعریف کرد.
- **F-008 / CLARIFICATION:** `NEW-005` به الگوریتم replay-deterministic `max(all_known_reserved_Column1)+1` تبدیل شد.
- **F-004 / HARDENING:** Run model به `run_type`, `run_status`, `run_result` و Promotion status مستقل تفکیک شد.
- **F-005 / HARDENING:** Record state به محورهای `change_status`, `availability_status`, `scope_status`, `issues[]` و `max_issue_severity` تفکیک شد.
- **F-007 / AUDITABILITY:** `Normalized_Source_Catalog.json`, extractor/engine versions و Canonical Source replay اضافه شد.
- **F-010 / CLARIFICATION:** `CLR-003` بر Generic NOT_FOUND precedence صریح گرفت.
- **F-011 / HARDENING:** Rulebook change mid-run اکنون Full Dataset Reprocess است؛ selective dependency logic حذف شد.
- **F-012 / HARDENING:** Rulebook/Schema/PN/Product State/Import Contract authority versions در Run Pin و قبل از Finalization verify می‌شوند.
- **F-009 / BEHAVIORAL_CONTRACT:** New Product lifecycle و `NEW_PRODUCT_PENDING` blocking semantics تعریف شد.
- **F-013 / AUDITABILITY:** Final CSV به Import outcome با Hash verification یا Run-bound user attestation Bind شد.
- **F-014 / TESTABILITY:** Technical Code Registry با category/severity/blocking/auto-fix metadata تعریف شد.
- **MST-007 / HARDENING:** Canonical CSV serialization contract اضافه شد.
- **MST-010 / HARDENING:** Canonical Master fingerprint دقیق تعریف شد.
- **VAL-101 / CLARIFICATION:** percentage-change formula و exact `>50%` comparison مشخص شد.
- **MAT-006 / CLARIFICATION:** Effective Source Specification fields برای duplicate consistency مشخص شدند.
- **Pricing Determinism / CLARIFICATION (superseded by v1.1.1):** Composite arithmetic در v1.1.0 به `sum in Rial -> one final ceil(Rial/10)` operational شده بود؛ v1.1.1 این انتخاب را به‌دلیل نبود Evidence/Approval کافی به‌عنوان Business Policy معتبر نمی‌پذیرد.
- **Availability/BOM / CLARIFICATION:** Component absence از positive BOM structure conflict تفکیک شد.
- **Price Domain / HARDENING:** Negative/invalid Final Price behavior بسته شد.
- **Audit Trace / AUDITABILITY:** Price provenance به source/manual/excluded/rollback/auto-repair types گسترش یافت.
- **Concurrency / HARDENING:** Promotion-time stale-parent guard اضافه شد.
- **Partial Import / HARDENING:** `PRODUCTION_RECONCILIATION_REQUIRED` به‌عنوان Recovery Gate تعریف شد.
- **Acceptance / TESTABILITY:** ACCها از 13 criterion عمومی به 35 criterion binary/observable گسترش یافتند.

#### Business Policies حفظ‌شده

بدون تغییر حفظ شدند:

- `GOV-001` Preserve by default
- `GOV-002` Least Mutation
- `GOV-003` AUTO_FIX/WARNING/BLOCKER
- `GOV-004` No Commercial Guessing
- Immutable History
- Transactional Promotion intent
- Decimal/Integer Money Arithmetic
- Matching Priority + Conflict Detection
- Rial Source / `ceil(Rial/10)` / No Hidden Markup
- Price controls visibility globally
- Discount Preflight
- Manual Override does not override Availability
- EXCLUDED supplier-price behavior
- New Product no-auto-create / run-scoped skip
- No Partial Promotion
- NO_CHANGES as auditable Run
- Rollback through new Version
- Known approved data correction `DAT-001`

### v1.0.0 — 2026-08-11

**وضعیت تاریخی:** Baseline Approved for Rulebook Implementation

شامل:

- معماری Source of Truth و Current Master
- Versioning و Immutable Run Archive
- Schema Contract 95 ستونی
- Unique Identifier/SKU Integrity
- Supplier Source Validation
- Matching Priority و Conflict Handling
- Color/Part Number Rules
- Direct/Composite Pricing
- Rial->Toman + Ceiling
- Global Price->Visibility Rule
- Discount Preflight
- Manual Price Override Lifecycle
- Excluded Product Policy
- New Product Workflow و Run-Scoped Skip
- AUTO_FIX/WARNING/BLOCKER Severity Model
- Mutation Allowlist
- Final Summary + Global Approval
- Audit Report/Manifest
- Import Success/Failed/Partial
- NO_CHANGES
- Rollback
- Rulebook/Schema Governance
- Conservative accounting-grade control defaults


## 25. Normative Successor Addendum — v1.6.0 RC-A2 Closure

> **HISTORICAL SUPERSESSION NOTICE — v1.11.0:** اصول anti-omission، code-less preservation، ordinal-independence و two-stage conservation این بخش همچنان فعال‌اند؛ اما هر جمله‌ای در این Addendum که `deterministic/programmatic enumerator` یا packaged validator را شرط runtime `COMPLETE` می‌داند، در target LLM-only runtime توسط `SLPE_LLM_NATIVE_PDF_SOURCE_CENSUS_V1` supersede شده و شرط فعال runtime نیست.

این Addendum فقط defect-class باقی‌مانده‌ی Source Completeness را می‌بندد و هیچ Pricing Formula، Product Decision، Current Master data، Product Pricing State، WooCommerce Production data یا active Import Contract behavior را تغییر نمی‌دهد. RC-B و مرز Project Instructions از v1.5.0 بدون تضعیف حفظ می‌شوند.

### SRC-108B — Pre-Inventory Source Structural Row Discovery [SUPERSEDED_FOR_ACTIVE_ADOBE_NATIVE_PROFILE_BY_v1.13]

قبل از ساخت `PHYSICAL_PRICE_BEARING_ROW_INVENTORY`، سیستم MUST برای **exact Source snapshot** و هر `price_bearing_region_identity` یک `SOURCE_STRUCTURAL_ROW_CANDIDATE_INVENTORY` replay-deterministic بسازد. این inventory حق ندارد از downstream physical inventory مشتق شود.

هر structural candidate MUST دقیقاً یکی از stateهای زیر را داشته باشد:

- `PRICE_BEARING_ROW`
- `NON_PRICE_ROW`
- `UNRESOLVED`

برای claim کردن `COMPLETE`، candidate discovery باید با exact source snapshot bytes/hash bind شده باشد و traversal تمام regionهای price-bearing به‌صورت deterministic اثبات شود. اگر representation نتواند row/span candidateها را deterministic و کامل enumerate کند، `source_completeness_status=UNRESOLVED` و `pre_inventory_discovery_status=UNRESOLVED` است؛ سیستم حق ندارد با اعتماد به inventory downstream آن را `COMPLETE` اعلام کند.

#### Supported source-row representation boundary

Boundary ماشین‌خوان این successor با شناسه `SLPE_SOURCE_ROW_DISCOVERY_REPRESENTATION_V1` تعریف می‌شود:

1. `STRUCTURED_SOURCE_ROWS_V1` — **PROOF_CAPABLE / supported by the packaged validator**. Exact source snapshot یک UTF-8 JSON snapshot versioned است که regionهای price-bearing را صریحاً نگه می‌دارد و هر region دارای source-native row object با `source_native_row_key`, `row_role`, `raw_cells` و `raw_source_price_text` است. Validator exact snapshot bytes را hash می‌کند و rowها را مستقیم از همان snapshot enumerate می‌کند؛ inventory گزارش‌شده از بیرون trust boundary نیست.
2. `PDF_TABLE_ROW_V1` — representation class مجاز فقط وقتی `PROVEN_VERSIONED_ENUMERATOR` برای exact PDF bytes، page/table traversal و row locators وجود داشته باشد. در نبود چنین enumerator اثبات‌شده، status MUST `UNRESOLVED` بماند.
3. `PDF_GEOMETRY_TEXT_SPAN_ROW_V1` — representation class مجاز فقط وقتی `PROVEN_VERSIONED_ENUMERATOR` برای exact PDF bytes و page/geometry/span traversal وجود داشته باشد. در نبود آن، status MUST `UNRESOLVED` بماند.

Package validator این release فقط `STRUCTURED_SOURCE_ROWS_V1` را به‌صورت مستقیم proof-capable اجرا می‌کند؛ اجرای Production PDF/table/geometry enumerator در این governance/package successor ادعا نمی‌شود.

Classification برای `STRUCTURED_SOURCE_ROWS_V1` deterministic است: در regionی که `price_column_required_for_data_rows=true` دارد، row با `row_role=DATA` و `raw_source_price_text` غیرخالی `PRICE_BEARING_ROW` است؛ row غیر-DATA بدون price، `NON_PRICE_ROW` است؛ DATA row بدون price یا ساختار ناسازگار `UNRESOLVED` است. `raw_supplier_code` می‌تواند خالی باشد و این موضوع price-bearing بودن را از بین نمی‌برد.

### SRC-108C — Two-Stage Source Conservation

Source Completeness فقط وقتی `PROVEN` است که هر دو conservation stage مستقل بسته شوند:

`SOURCE_STRUCTURAL_ROW_CANDIDATE -> PHYSICAL_PRICE_BEARING_ROW_INVENTORY -> CANONICAL_SOURCE_RECORD`

Stage 1 — Candidate ↔ Physical:

- هر discovered candidate با state=`PRICE_BEARING_ROW` دقیقاً یک physical row داشته باشد؛
- هیچ discovered `NON_PRICE_ROW` نباید physical price-bearing row بسازد؛
- هر `UNRESOLVED` candidate مانع COMPLETE است؛
- physical row بدون discovered candidate معتبر orphan است؛
- duplicate physical materialization از یک candidate ممنوع است؛
- omission قبل از ساخت physical inventory با مقایسه‌ی discovered candidate set و physical inventory MUST قابل کشف باشد.

Stage 2 — Physical ↔ Canonical همان invariant v1.5.0 را حفظ می‌کند:

- هر physical price-bearing row دقیقاً یک canonical Source Record؛
- هر canonical Source Record دقیقاً یک physical row؛
- alias/alternate identity فقط به canonical record موجود attach می‌شود و count را افزایش نمی‌دهد؛
- code-less price-bearing row همچنان canonical record است؛
- missing/duplicate/orphan/unresolved باید صفر باشند؛
- count equality در هیچ stage به‌تنهایی proof نیست.

`COMPLETE` requires:

- `pre_inventory_discovery_status=PROVEN`
- `candidate_to_physical_conservation_status=PROVEN`
- `physical_to_canonical_conservation_status=PROVEN`
- `source_row_conservation_status=PROVEN`
- zero unresolved region/candidate/row counters
- all other observable completeness checks pass.

هر `NOT_FOUND`/deactivation consequential تا وقتی هر دو conservation stage اثبات نشده‌اند BLOCKED می‌ماند.

### SRC-IR-004A — Mechanical Identity Derivation Conformance

**Disposition for active no-code Adobe/native profile: `SUPERSEDED_MECHANICAL_HASH_DERIVATION`.**

The active conformance requirement is independent recreation of the structured source-visible row identity components by fresh Adobe Pass A and Pass B over the same run-scoped bound artifact.

Required:

- same table/section identity;
- same code/model when present;
- same description;
- same role-preserving PRICE cells;
- same code-less discriminator material;
- extraction-order changes do not change semantic row identity;
- changing a role-bound source PRICE cell changes affected row/material evidence;
- collision/component mismatch => `UNRESOLVED_SOURCE_ROW_IDENTITY_COLLISION` or replay mismatch and blocks `COMPLETE`.

Legacy `SHA256(CANONICAL_JSON(raw_cells))`, source-snapshot SHA-derived row IDs and byte-for-byte validator recomputation are explicitly **not active prerequisites** for this profile.

### SRC-IR-006A — Discovery/Conservation Audit Chain

For the active no-code Source profile, Run/validation evidence MUST include:

- run-scoped bound Source provider artifact identity;
- native whole-source coverage state;
- fresh independent Adobe Pass A and B invocation/result identities;
- A/B reconciliation;
- native↔Adobe section/row coverage;
- explicit discovered candidate inventory;
- candidate classification counters;
- candidate→physical mapping/counters;
- physical→canonical mapping/counters;
- structured row-identity conformance;
- ordinal-reorder result;
- final discovery/conservation states.

No source snapshot SHA, candidate inventory hash, reconciliation hash or model-computed mechanical row-identity hash is required. If an external/provider checksum actually exists it MAY be recorded as additional evidence under its true proof scope.

### GOV-013 — RC-B Preservation During RC-A2 Successor Generation

این successor MUST تمام `CURRENT_SET` conformance lock، active/current carrier sweep، dynamic/fresh selector resolution، historical-reference exception و Project Instructions domain-authority boundary نسخه v1.5.0 را حفظ کند. RC-A2 حق ایجاد shadow authority یا stale active identity carrier ندارد.

---


## 26. Normative Successor Addendum — v1.7.0 RC-A3 / RC-B2 Closure

این Addendum فقط دو defect-class باقی‌مانده‌ی governance/package را می‌بندد و هیچ Pricing Formula، Product Decision، Current Master data، Product Pricing State، WooCommerce Production data، active Import Contract behavior یا open Production reconciliation semantic را تغییر نمی‌دهد. تمام کنترل‌های RC-A/RC-A2 و RC-B نسخه‌های قبل بدون تضعیف حفظ می‌شوند.

### SRC-108D — Structured Source-Native Classification Basis

برای `STRUCTURED_SOURCE_ROWS_V1`، classifier MUST تصمیم خود را از **exact source-native representation** بگیرد، نه از summary/derived metadata قابل‌تکرار. هر price-bearing region MUST یک `classification_schema` نسخه‌دار داشته باشد که حداقل این موارد را صریحاً تعیین کند:

- `price_field` — نام field منبعی داخل `raw_cells` که Price را حمل می‌کند؛
- `supplier_code_field` — نام field منبعی داخل `raw_cells` که Supplier/Product Code را حمل می‌کند و MAY خالی باشد؛
- `price_parser_profile` — parser deterministic و نسخه‌دار برای تشخیص price-bearing text؛
- `metadata_conformance_policy` — الزام مقایسه‌ی summaryهای مشتق‌شده با raw source.

برای packaged validator این successor، parser profile برابر `SLPE_INTEGER_PRICE_TEXT_V1` است: Unicode Persian/Arabic digits به ASCII normalize می‌شوند، separatorهای متعارف هزارگان حذف می‌شوند، و فقط integer numeric text معتبر price-bearing محسوب می‌شود. مقدار `0` نیز price-bearing source value است. Header/label text مانند `Price` numeric price نیست.

Classifier MUST این values را مستقیماً از `raw_cells` derive کند:

`derived_raw_source_price_text = STRING(raw_cells[classification_schema.price_field])`

`derived_raw_supplier_code = STRING(raw_cells[classification_schema.supplier_code_field])` یا empty string اگر code خالی باشد.

`raw_source_price_text` و `raw_supplier_code` در row object فقط audit summaries هستند. قبل از هر اثرگذاری بر classification، validator MUST equality آنها را با derived values mechanically verify کند.

`row_role` نیز authority مستقل نیست. اگر source-native price field با parser profile معتبر price-bearing باشد، row MUST به‌عنوان price-bearing structural candidate شناخته شود؛ هر `row_role != DATA` در این حالت metadata conflict است. اگر `row_role=DATA` ولی source-native price field price-bearing نباشد و region price را برای data rows الزامی کرده باشد، row `UNRESOLVED` است.

هر contradiction میان `raw_cells` و classification metadata MUST حداقل issue زیر را تولید کند:

`STRUCTURED_SOURCE_ROW_METADATA_CONFLICT`

و candidate/result مربوط MUST `UNRESOLVED` یا `FAILED` شود. چنین conflictی هرگز نباید به `NON_PRICE_ROW` و سپس `COMPLETE` تبدیل شود.

### SRC-108E — Classification-Conformance Gate Before Conservation

دو-stage conservation فقط بعد از `structured_classification_conformance_status=PROVEN` معتبر است. `COMPLETE` علاوه بر شروط v1.6.0 requires:

- `structured_classification_schema_status=PROVEN`؛
- `structured_classification_conformance_status=PROVEN`؛
- `structured_source_metadata_conflicts=0`؛
- تمام `PRICE_BEARING_ROW` candidateها از source-native raw representation derive شده باشند.

اگر classification schema غایب/ناشناخته باشد، source-native price/code field resolve نشود، parser profile unsupported باشد، یا summary metadata با raw source تعارض داشته باشد، Source Completeness MUST fail closed و `COMPLETE` ممنوع است.

### SRC-IR-004B — Classification Metadata Is Audit-Only Unless Conformant

`raw_source_price_text`, `raw_supplier_code`, `row_role` and similar summaries MUST NOT by themselves determine source identity or price-bearing classification.

For the active Adobe/native profile, row identity is reconstructed from source-visible structured components under `STRUCTURED_SOURCE_VISIBLE_ROW_IDENTITY_V1`; no runtime `raw_row_fingerprint` hash is required. Any legacy `raw_row_fingerprint` field from predecessor evidence is historical/superseded implementation material and MUST NOT satisfy active identity conformance.

Code-less price-bearing rows remain canonical Source Records; empty code does not alter price-bearing classification. Alias attachment does not increase canonical count. Extraction ordinal remains audit-only.

### GOV-014 — Active Authority Representation Closure

Live `CURRENT_SET` همچنان تنها selector Domain/Runtime Authority است. هر active/current identity-bearing representation MUST با selected identities همان CURRENT_SET mechanically conform باشد؛ این الزام شامل structured fields و free-text active projections است.

Carrierهای active/current شامل حداقل این سطوح هستند:

- Rulebook identity و active/runtime projections؛
- Master active header/projection؛
- GWOC authority bindings/business authority/current-state projection؛
- CURRENT_SET selected/direct identity fields؛
- Workspace Manifest selected/resource/next-phase projections؛
- Project Instructions اگر current identities را pin کند؛
- Package Manifest و selected authority bindings.

Free-text active statement که version/resource identity را تکرار می‌کند MUST از canonical selected fields derive شود یا validator exact conformance آن را recompute کند. Hash consistency هرگز جای carrier semantic conformance را نمی‌گیرد.

Historical/change-log/provenance carrier فقط وقتی می‌تواند predecessor/ancestor identity را نگه دارد که نقش تاریخی آن در همان structured carrier صریح باشد. Unclassified old identity در active/current context defect است.

### GOV-015 — Rulebook Baseline Role Truthfulness

`rulebook_identity.source_rulebook_baseline` MUST نقش خود را دقیق توصیف کند:

- اگر `historical_role=IMMEDIATE_PREDECESSOR_BUILD_BASELINE` است، filename/version/hash/resource MUST با actual immediate predecessor همان release و `supersedes`/activation provenance یکسان باشد؛
- اگر baseline یک ancestor غیرمستقیم است، MUST نقش صریحی مانند `NON_IMMEDIATE_ANCESTOR_BUILD_BASELINE` داشته باشد و نباید به‌عنوان immediate predecessor معرفی شود.

Mislabeling role یا identity mismatch qualification failure است.

### GOV-016 — Explicit Historical Provenance Classification

Rule-level `source_section`، compiler basis، change-log و origin metadata که نسخه‌ی قدیمی را برای provenance نگه می‌دارند MUST دارای role صریح historical/provenance باشند. این references active selector نیستند و validator فقط در صورت role صریح آنها را از current-identity equality exempt می‌کند.

---

# ضمیمه A — خلاصه رفتار در یک جمله

**SwitchLand Price Engine یک موتور قیمت‌گذاری نسخه‌دار، قابل حسابرسی و محافظه‌کار است که فقط با Evidence قطعی داده تجاری را تغییر می‌دهد، همه تغییرات را ردیابی می‌کند، هیچ تاریخچه‌ای را بازنویسی نمی‌کند و Current Master را پس از نتیجه موفق یا استثنای دقیق run-scoped با Queue آشکار و تأییدشده ارتقا می‌دهد.**

# ضمیمه B — اصل نهایی تصمیم‌گیری

اگر سیستم بتواند با Rule معتبر و Evidence قطعی تصمیم بگیرد، باید کار را بدون مزاحمت اضافه ادامه دهد و نتیجه را Audit کند. اگر تصمیم نیازمند حدس باشد و حدس بتواند داده تجاری را اشتباه تغییر دهد، باید آن مورد را Fail-Closed کند، سایر رکوردها را ادامه دهد و فقط همان تصمیم ضروری را با توضیح ساده از کاربر بخواهد.


---

## 24. Root-Cause Successor v1.8.0 — RC-A4 / RC-B3

این بخش successor فقط مرزهای اثبات و governance را سخت‌تر می‌کند و هیچ Pricing Formula، Product Decision، Current Master data، Product Pricing State، WooCommerce Production state، active Import Contract behavior، Owner business decision یا open Production reconciliation semantics را تغییر نمی‌دهد.

### SRC-108F — Independent Structured Source Schema Authority

برای هر representation از نوع `STRUCTURED_SOURCE_ROWS_V1` که می‌خواهد `SOURCE COMPLETENESS = COMPLETE` را اثبات کند، semantic roleهای فیلدها MUST از یک **source-native field inventory** مستقل از row classification summaries به‌صورت مکانیکی مشتق شوند.

الزامات:

1. exact source-native artifact identity MUST با digest بازپخش‌پذیر از payload source-native اثبات شود؛
2. source-native field inventory MUST پیش از row classification enumerate شود؛
3. semantic role `PRICE` MUST با `SLPE_STRUCTURED_FIELD_ROLE_BINDING_V1` از field inventory مشتق شود؛
4. semantic role `SUPPLIER_CODE` MUST با همان binding profile از field inventory مشتق شود؛
5. derived projectionهایی مانند `classification_schema.price_field` و `classification_schema.supplier_code_field` فقط audit projection هستند و MUST با derivation مستقل برابر باشند؛
6. `price_column_required_for_data_rows` MUST از canonical representation authority profile تعیین شود و source snapshot حق self-certify کردن مقدار دیگری ندارد؛
7. ambiguity، missing binding یا contradiction MUST به `STRUCTURED_SOURCE_SCHEMA_AUTHORITY_UNRESOLVED` یا `STRUCTURED_SOURCE_SCHEMA_ROLE_MISMATCH` منجر شود و COMPLETE را مسدود کند.

### SRC-108G — Independent Structural Region Coverage

برای proof-capable structured representation، inventory منطقه‌های قابل‌اعمال MUST از `source_native_artifact.structural_regions` به‌صورت deterministic enumerate شود. هر `regions` projection یا region summary فقط projection مشتق‌شده است و authority مستقل ندارد.

COMPLETE فقط وقتی مجاز است که:

- exact set of source-native structural regions enumerate شده باشد؛
- projection region inventory دقیقاً همان set را بدون missing/duplicate/orphan بازنمایی کند؛
- هر region identity و page/location binding conform باشد؛
- region coverage status برابر `PROVEN` باشد.

اگر source-native enumeration واقعاً صفر region برگرداند و exact artifact identity، field-role binding و enumerator contract همه PROVEN باشند، zero-applicable-region نتیجه معتبر است و صرفاً به دلیل count=0 نباید رد شود.

حذف region projection در حالی که source-native artifact همان region را دارد MUST با `STRUCTURAL_REGION_COVERAGE_MISMATCH` fail closed شود.

### SRC-108H — Structured Source Representation Authority Contract

Canonical proof chain برای `STRUCTURED_SOURCE_ROWS_V1` از این پس چنین است:

`EXACT_SOURCE_NATIVE_ARTIFACT`
→ `SOURCE_NATIVE_FIELD_INVENTORY`
→ `MECHANICAL_FIELD_ROLE_BINDING`
→ `SOURCE_NATIVE_STRUCTURAL_REGION_ENUMERATION`
→ `CONFORMANT_SCHEMA_AND_REGION_PROJECTIONS`
→ `SOURCE_STRUCTURAL_ROW_CANDIDATE_INVENTORY`
→ `PHYSICAL_PRICE_BEARING_ROW_INVENTORY`
→ `CANONICAL_SOURCE_RECORDS`

Schema/region projectionهای مصرف‌شده در classification MUST NOT همان trust boundary باشند که correctness آنها را اعلام می‌کند.

### GOV-017 — Typed Active Identity Carrier Class

هر active/current authority identity projection که version/resource/hash/current-set identity را نگه می‌دارد MUST یکی از این دو وضعیت typed را داشته باشد:

- `carrier_role = ACTIVE_CURRENT_IDENTITY_PROJECTION` یا role فعال معادل تعریف‌شده در Rulebook؛
- explicit historical/change-log/provenance role که آن carrier را از current conformance خارج کند.

Package/runtime qualification MUST active identity carrierها را بر اساس **carrier class/role** به‌صورت recursive کشف کند، نه با JSON path whitelist.

هر typed active carrier در هر محل جدید یا منتقل‌شده MUST با authority منتخب live `CURRENT_SET` conform باشد. carrier فعال دارای identity literal بدون typed role MUST fail شود اگر از semantic context فعال/current قابل تشخیص باشد.

### GOV-018 — Carrier-Class Discovery Completeness

Qualification MUST حداقل این invariantها را enforce کند:

1. هر dict/object با `carrier_role` فعال در تمام Rulebook/GWOC/CURRENT_SET/Workspace Manifest/Package bindings recursively کشف شود؛
2. current identity fields داخل carrier فعال MUST با selected authority identity مقایسه شوند؛
3. current-version literals در projectionهایی که `must_match_live_current_set=true` یا active/current authority status دارند، بدون typed carrier role مجاز نیستند؛
4. historical identities فقط با explicit historical role معاف‌اند؛
5. اضافه یا جابه‌جا کردن active carrier به JSON path جدید نباید qualification را دور بزند؛
6. hash-consistency یا rebuilt package hashes هرگز جای semantic carrier conformance را نمی‌گیرد.

### GOV-019 — RC-A4 / RC-B3 Falsification Gates

هر successor package MUST علاوه بر Regression A-L، حداقل M-P را اجرا کند:

- M: wrong-but-valid classification schema substitution → FAIL/UNRESOLVED;
- N: structural region omission → FAIL و legitimate independently-proven zero-region → PASS;
- O: stale active profile identity با rebuilt complete hash chain → FAIL;
- P: relocated/unregistered active identity representation در path جدید → typed discovery یا derivation؛ stale value → FAIL.

GREEN فقط پس از same-root adversarial sweep و ZIP re-extraction qualification مجاز است.

---

## 25. Root-Cause Successor v1.9.0 — RC-A-ROOT / RC-B-ROOT

> **HISTORICAL SUPERSESSION NOTICE — v1.11.0:** هدف ضد self-certification و exact-source anchoring این بخش حفظ می‌شود، ولی extractor-specific execution/implementation/invocation requirements آن برای runtime فعلی historical/superseded است. در active runtime، استقلال proof از طریق دو census مستقل از خود source و reconciliation اجباری تأمین می‌شود.

این successor فقط مرزهای اثبات Source Completeness و Current Authority Identity را سخت‌تر می‌کند. هیچ Pricing Formula، Product Decision، Current Master data، Product Pricing State، WooCommerce Production state، active Import Contract behavior، Owner business decision یا open Production reconciliation semantic را تغییر نمی‌دهد.

### SRC-108I — Exact Source Artifact Authority Anchor [SUPERSEDED_FOR_ACTIVE_ADOBE_NATIVE_PROFILE_BY_v1.13]

برای هر representation که می‌خواهد `SOURCE COMPLETENESS = COMPLETE` را اثبات کند، proof chain MUST از **exact bound source artifact bytes** آغاز شود، نه از inventory/schema/region declaration داخل derived snapshot.

الزام canonical:

`EXACT_SOURCE_ARTIFACT_BYTES`
→ `VERIFIED_EXACT_SOURCE_SHA256`
→ `QUALIFIED_EXTRACTOR_INVOCATION`
→ `DETERMINISTIC_SOURCE_NATIVE_RESULT`
→ `SOURCE_NATIVE_SCHEMA / FIELD INVENTORY`
→ `COMPLETE APPLICABLE REGION + ROW ENUMERATION`
→ `SEMANTIC FIELD ROLE BINDING`
→ `CLASSIFICATION`
→ `SOURCE_STRUCTURAL_CANDIDATES`
→ `PHYSICAL PRICE-BEARING ROWS`
→ `CANONICAL SOURCE RECORDS`.

Derived `source_native_artifact`, `classification_schema`, `regions`, row summaries و self-hashهای داخل snapshot فقط projection/audit evidence هستند و **MUST NOT** مرز authority باشند.

اگر exact source artifact قابل read/hash نباشد، یا exact input binding به extractor اثبات نشود، `SOURCE_COMPLETENESS=COMPLETE` ممنوع و نتیجه `UNRESOLVED`/fail-closed است.

### SRC-108J — Qualified Extractor Boundary [SUPERSEDED_FOR_ACTIVE_ADOBE_NATIVE_PROFILE_BY_v1.13]

مدل proof-capable بستهٔ فعلی برای structured source از `SLPE_QUALIFIED_STRUCTURED_EXTRACTOR_V1` استفاده می‌کند.

Extractor qualified MUST:

1. exact source bytes را مستقیماً consume کند؛
2. exact input SHA-256 را ثبت و دوباره محاسبه کند؛
3. extractor identity/version و implementation SHA-256 مشخص داشته باشد؛
4. invocation identity deterministic داشته باشد؛
5. output source-native inventory deterministic و canonical-result SHA-256 داشته باشد؛
6. field/schema role semantics را از **extractor profile + exact source structure** derive کند، نه از derived snapshot declaration؛
7. structural regions و rows را مستقیماً از exact source enumerate کند؛
8. derived snapshot projections را فقط برای conformance با extractor output مقایسه کند؛
9. representation خارج از support boundary را `UNRESOLVED` نگه دارد.

در packaged validator، `SLPE_EXACT_STRUCTURED_TABLE_V1` تنها exact structured source format است که extractor یادشده proof-capable می‌داند. PDF/table/geometry source بدون extractor versioned/qualified همچنان `UNRESOLVED` است.

### SRC-108K — Exact-Source Zero Result Truthfulness

صفر applicable region یا صفر price-bearing row فقط وقتی می‌تواند COMPLETE باشد که **qualified extractor روی exact source bytes** همان zero result را deterministic تولید کرده باشد.

وجود `regions=[]`، `source_native_artifact.structural_regions=[]` یا countهای صفر در derived snapshot هیچ proof مستقلی ایجاد نمی‌کند.

### SRC-108L — Derived Source-Native Projection Conformance

هر derived source-native projection MUST با output extractor دقیقاً conform باشد. حداقل:

- field inventory exact-set equality؛
- structural-region identity/page/locator exact-set equality؛
- source-native row key/raw-cell exact equality؛
- derived row summaries و classification metadata conformance؛
- extractor invocation/result identity conformance.

تناقض به یکی از `EXACT_SOURCE_EXTRACTOR_BINDING_MISMATCH`, `SOURCE_NATIVE_EXTRACTOR_RESULT_MISMATCH`, `STRUCTURED_SOURCE_ROW_METADATA_CONFLICT` یا equivalent fail-closed code منجر می‌شود و COMPLETE را مسدود می‌کند.

### GOV-020 — Semantic Active/Current Authority Carrier Classification

Qualification MUST هر object را **از نظر معنا** طبقه‌بندی کند، نه فقط از روی JSON path یا legacy field names.

برای هر recursively visited object باید تعیین شود:

1. آیا object ادعای `ACTIVE`/`CURRENT` authority semantics دارد؛
2. آیا یکی از authority kindهای governed را از طریق stable authority identifier، explicit `authority_kind`/`authority_ref` یا semantic equivalent شناسایی می‌کند؛
3. آیا identity material مانند version/resource/hash/current-set identity حمل می‌کند؛
4. آیا typed carrier contract معتبر دارد؛
5. آیا identity آن با live `CURRENT_SET` selected authority conform است.

Generic fields مانند `version`, `resource_id`, `sha256` در object فعال، وقتی object با `document_id=SLPE-MASTER-SPEC-001`, `rulebook_id=SLPE-RULEBOOK-001`, `contract_id=SLPE-GWOC-001` یا equivalent stable identifier authority kind را معرفی می‌کند، MUST به‌عنوان authority identity material شناخته شوند؛ نبود `master_version`/`rulebook_version` exemption نیست.

### GOV-021 — Explicit Authority Reference Contract

Active/current projectionهای جدید SHOULD از contract زیر استفاده کنند:

- `carrier_role` = supported active carrier role؛
- `authority_kind` = governed authority kind؛
- `authority_ref = CURRENT_SET_SELECTED`؛
- duplicated version/resource/hash فقط در صورت نیاز audit و با mechanical conformance.

Object فعال/current که semantic authority identity حمل می‌کند ولی typed carrier contract ندارد، `UNTYPED_ACTIVE_IDENTITY_CARRIER` و qualification failure است.

Historical/provenance object فقط وقتی exempt است که explicit historical role داشته باشد **و هم‌زمان active/current claim نداشته باشد**. ترکیب historical exemption با active/current claim برابر `HISTORICAL_ACTIVE_AUTHORITY_ROLE_CONFLICT` و FAIL است.

### GOV-022 — Semantic Carrier Discovery Completeness

Carrier discovery MUST recursive و path-independent باشد و حداقل این signals را ترکیب کند:

- active/current status/role semantics؛
- stable governed authority identifiers؛
- explicit `authority_kind` / `authority_ref`؛
- generic identity field aliases و identity-shaped values؛
- typed carrier role.

یک valid typed carrier در کنار stale generic untyped active carrier نباید bypass را mask کند؛ هر active/current carrier مستقلاً باید conform باشد.

### GOV-023 — RC-A-ROOT / RC-B-ROOT Falsification Gates

هر successor Package MUST علاوه بر Regression A-P، Q-V را اجرا کند:

- Q self-certified field inventory rewrite با exact source ثابت → FAIL؛
- R total source-native region omission با exact source non-empty → FAIL؛ exact-source truthful zero → PASS؛
- S generic untyped active Master carrier با generic identity fields و rebuilt hash chain → FAIL؛
- T همان برای Rulebook و GWOC → FAIL؛
- U explicit historical/provenance negative control بدون active/current claim → PASS؛
- V valid typed + stale generic untyped active carrier در یک artifact → FAIL.

GREEN فقط پس از complete coherent-hash-chain adversarial mutation، same-root sweep و ZIP re-extraction qualification مجاز است.

---

# Addendum v1.11.0 — RC-A6 LLM-Only Runtime Source Completeness

> **HISTORICAL SUPERSESSION NOTICE — v1.12.0:** برای `OFFICIAL_SUPPLIER_PDF` در deployment منتخب، قواعد `SRC-108Q/R/S/T` و `GOV-026/027` دیگر runtime authority فعال نیستند، چون Project-file text retrieval به‌تنهایی page/table/row↔price geometry را اثبات نمی‌کند. این قواعد فقط predecessor provenance هستند. مسیر structured source مستقل `SRC-108U` برای ورودی structured واقعاً readable حفظ می‌شود. Runtime فعال PDF توسط Addendum v1.12.0 و profile `SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1` اداره می‌شود.


این Addendum Root Cause بازشده‌ی `RC-A6 — LLM_ONLY_RUNTIME_CAPABILITY_MISMATCH` را می‌بندد. target یک GPT Project مدل‌محور و **بدون code-execution runtime** است. هیچ Pricing Formula، Product Decision، Current Master data، Product Pricing State، WooCommerce/Production state، active Import Contract behavior، Owner Decision، RC-B carrier control، SKU/variation policy یا open Production reconciliation تغییر نمی‌کند.

## Runtime supersession map

در active runtime، معماری `DRIVE_HOSTED_VERSIONED_RUNTIME_ENUMERATOR` و تمام prerequisiteهای executable آن **HISTORICAL_SUPERSEDED** هستند. `SRC-108J/K/L` فقط در بخش extractor-specific خود و `SRC-108M/N/O/P`, `GOV-024/025` نسخه‌ی v1.10.x دیگر runtime authority نیستند. predecessor artifacts و Drive `.py` evidence حذف یا بازنویسی نمی‌شوند؛ فقط از active prerequisite خارج‌اند.

Active runtime MUST NOT require or assume Python، Data Analysis/Code Interpreter، PyMuPDF/MuPDF، `.py` execution، shell/notebook/package installation، executable payload retrieval یا hidden code executor. Drive فقط در صورت capability واقعاً AVAILABLE برای retrieval/persistence است و code executor نیست.

### SRC-108Q — [HISTORICAL/SUPERSEDED FOR NORMAL SUPPLIER PDF] LLM-Native PDF Source Census Authority

> `historical_role=SUPERSEDED_RUNTIME_RULE; selected_as_active=false; superseded_by=SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`.

در predecessor v1.11.0 برای `OFFICIAL_SUPPLIER_PDF`، evidence mode `SLPE_LLM_NATIVE_PDF_SOURCE_CENSUS_V1` بود؛ این statement اکنون historical/superseded است. Authority input همان PDF واقعی همین Run است. مدل MUST کل source را inspect کند، نه صرفاً snippet/flattened text.

Pass A برای هر page ثبت می‌کند: page index، readable status، section/title/date markers، candidate price-bearing regions/tables و geometry/readability status. برای هر applicable region: page identity، bounded region identity، header inventory، semantic roles، **تمام PRICE columns**، supplier-code/model column هرجا موجود، description و visible row number. برای هر price-bearing row: page/region، visible row number، supplier code/model raw، description raw، همه raw price values، code-less status و ambiguity status.

Flattened text به‌تنهایی relationship ردیف/سلول را اثبات نمی‌کند. اگر geometry یا association قابل اطمینان نباشد: `UNRESOLVED_SOURCE_GEOMETRY`; هیچ guess مجاز نیست.

### SRC-108R — [HISTORICAL/SUPERSEDED FOR NORMAL SUPPLIER PDF] Independent Second Source Pass and Census Reconciliation

> `historical_role=SUPERSEDED_RUNTIME_RULE; selected_as_active=false; superseded_by=SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`.

پس از Pass A، مدل MUST original PDF را دوباره و مستقل inspect کند؛ Pass B از census A مشتق نمی‌شود. Pass B دوباره page coverage، applicable regions، price-bearing rows، code-less rows، semantic field bindings و price-column inventory را می‌سازد.

Reconciliation باید روی page set، region set، source-visible row identities، supplier/model association، raw price values و code-less set agreement داشته باشد. هر mismatch => `UNRESOLVED_SOURCE_CENSUS_MISMATCH` و `COMPLETE` ممنوع. Count equality به‌تنهایی proof نیست؛ omit-one/duplicate-another باید fail شود.

### SRC-108S — [HISTORICAL/SUPERSEDED FOR NORMAL SUPPLIER PDF] LLM-Native Source Row Identity and Two-Stage Conservation

> `historical_role=SUPERSEDED_RUNTIME_RULE; selected_as_active=false; superseded_by=SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`.

Extraction ordinal identity نیست. Source row identity باید از source-visible evidence derive شود، با اولویت:

1. page + table/section + visible source row number؛
2. page + table/section + supplier code/model + description؛
3. برای code-less: page + table/section + visible row number + description + raw price values.

اگر دو ردیف materially distinct قابل تفکیک نباشند: `UNRESOLVED_SOURCE_ROW_IDENTITY_COLLISION`; merge ممنوع.

Two-stage conservation بدون تضعیف حفظ است:
`SOURCE_CENSUS_PRICE_BEARING_ROW -> PHYSICAL_PRICE_BEARING_ROW_INVENTORY -> CANONICAL_SOURCE_RECORD`.
هر census row price-bearing دقیقاً یک physical row و هر physical row دقیقاً یک canonical record؛ orphan/missing/duplicate/unresolved صفر. code-less price-bearing rows canonical هستند و alias count را inflate نمی‌کند.

### SRC-108T — [HISTORICAL/SUPERSEDED FOR NORMAL SUPPLIER PDF] LLM-Native Completeness Admission

> `historical_role=SUPERSEDED_RUNTIME_RULE; selected_as_active=false; superseded_by=SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`.

در predecessor، `source_completeness_evidence_mode = LLM_NATIVE_PDF_SOURCE_CENSUS_V1` کنار status ثبت می‌شد؛ این requirement اکنون historical/superseded است. `source_completeness_status=COMPLETE` فقط وقتی مجاز است که:

- every source page inspectable؛
- page/region census complete؛
- تمام material tables/regions readable؛
- Pass A و Pass B complete؛
- reconciliation PASS؛
- unresolved schema/geometry/identity/price association = 0؛
- candidate→physical conservation = PROVEN؛
- physical→canonical conservation = PROVEN؛
- material source-access gap = 0.

Legitimate zero-region/zero-row فقط وقتی COMPLETE است که **هر دو pass روی تمام source pages** zero applicable regions/rows را independently نتیجه دهند و reconciliation PASS باشد. Derived snapshot/normalization نمی‌تواند completeness را self-certify کند.

این evidence **mechanical/deterministic parser proof نیست**؛ claim ceiling باید صریحاً `LLM_NATIVE_EVIDENCE_DRIVEN` بماند.

### SRC-108U — LLM-Native Structured Source Path

اگر structured input مانند CSV/XLSX/JSON table مستقیماً برای مدل readable باشد، profile `SLPE_LLM_NATIVE_STRUCTURED_SOURCE_CENSUS_V1` مجاز است: exact supplied artifact identity، complete sheet/table/row census، two independent reads، semantic column binding، reconciliation و همان two-stage conservation. هیچ Python extractor یا executable implementation prerequisite نیست. Representation مبهم/ناخوانا => `UNRESOLVED`.

### GOV-026 — [HISTORICAL/SUPERSEDED FOR NORMAL SUPPLIER PDF] Absence / Destructive Action Confirmation

> `historical_role=SUPERSEDED_RUNTIME_RULE; selected_as_active=false; superseded_by=SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`.

`NOT_FOUND` فقط وقتی می‌تواند برای deactivation/removal/zeroing یا availability destructive decision eligible شود که:

A. predecessor `COMPLETE` تحت `LLM_NATIVE_PDF_SOURCE_CENSUS_V1`؛ [HISTORICAL/SUPERSEDED]
B. Pass A/B reconciliation = PASS؛
C. **third focused absence recheck** روی original PDF برای همان supplier code/model/product identity؛
D. هیچ unresolved page/region/schema/identity condition مرتبط وجود نداشته باشد.

Failure => `UNRESOLVED_ABSENCE_CONFIRMATION`; destructive action ممنوع. Positive rows found به third absence-only pass نیاز ندارند.

### GOV-027 — [HISTORICAL/SUPERSEDED FOR NORMAL SUPPLIER PDF] Runtime Capability, Claim Ceiling and RC-A6 Closure

> `historical_role=SUPERSEDED_RUNTIME_RULE; selected_as_active=false; superseded_by=SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`.

Runtime Source knowledge/authority است، executable code نیست. Optional tool/app فقط پس از observed capability استفاده می‌شود؛ `UNKNOWN != AVAILABLE`. Normal `PRICE_UPDATE` correctness نباید به tool اختیاری وابسته باشد. اگر PDF همین Run به Project داده شده و صفحات قابل inspection‌اند، flow فعال است:

`OFFICIAL_SUPPLIER_PDF -> SOURCE_ACCESS_QUALIFICATION -> PASS_A_PAGE/REGION/ROW_CENSUS -> PASS_B_INDEPENDENT_CENSUS -> RECONCILIATION -> BOUNDED_NORMALIZATION -> CLASSIFICATION -> TWO_STAGE_CONSERVATION -> PRICING/MATCHING/AVAILABILITY -> DOWNSTREAM_READINESS`.

ادعاهای `mechanically enumerated`, `deterministic parser proof`, `Python-verified`, `runtime code-enforced` یا معادل آن برای این evidence mode ممنوع است.

RC-A6 GREEN فقط با هر دو شرط مجاز است:
1. **SAFETY:** access/geometry/schema/identity/reconciliation gaps fail closed و destructive absence guard برقرار؛
2. **LLM-ONLY NORMAL PRICE_UPDATE PROGRESS:** یک positive row واقعی از PDF با model-only census بتواند بدون code execution به classification/conservation/pricing route برسد.

# Patch v1.12.1 — Migration Carrier Truthfulness

این patch هیچ business/runtime outcome جدیدی نسبت به Adobe/native successor نمی‌افزاید. فقط `SRC-108Q/R/S/T` و `GOV-026/027` predecessor را در همان carrierها صریحاً `HISTORICAL/SUPERSEDED` می‌کند تا هیچ pure-LLM PDF visual/geometry statement به‌صورت active/current باقی نماند. `SRC-108U` structured-source path مستقل همچنان active است.

# Addendum v1.12.0 — RC-A6 Adobe Acrobat Structured PDF Census + Native Text Coverage

این successor فقط Source Completeness evidence ownership/runtime capability و migration-carrier truthfulness را اصلاح می‌کند. هیچ Pricing Formula، Product Decision، Current Master business data، Product Pricing State business state، WooCommerce/Production state، active Import Contract behavior، OD decision، RC-B carrier control، SKU/variation policy یا open Production reconciliation semantic را تغییر نمی‌دهد.

برای `OFFICIAL_SUPPLIER_PDF` در deployment منتخب، profile فعال:

`SLPE_ADOBE_ACROBAT_STRUCTURED_PDF_CENSUS_WITH_NATIVE_TEXT_COVERAGE_V1`

است. این روش **QUALIFIED MODEL-ORCHESTRATED STRUCTURED PDF EXTRACTION + INDEPENDENT REPLAY + SOURCE COVERAGE RECONCILIATION** است؛ deterministic parser proof، executable proof، code-enforced enumeration یا byte-level structural parsing نیست.

### SRC-108V — Dual-Channel PDF Evidence Authority

Source Completeness برای PDF فقط از دو channel مکمل تشکیل می‌شود:

A. `NATIVE_SOURCE_TEXT_COVERAGE`: source/release identity، whole-document lexical/section inventory، price-list section discovery و source-visible code/model/description material. Native text حق bind کردن row↔price را جایی که geometry مهم است ندارد.

B. `ADOBE_ACROBAT_STRUCTURED_EXTRACTION`: table/header/row/cell association، supplier code/model، description، **تمام PRICE columns به‌صورت جداگانه**، raw price cells، code-less/model-less state و source-visible row material.

هیچ channel به‌تنهایی Source Completeness را self-certify نمی‌کند. Derived normalized catalog نیز completeness authority نیست.

### SRC-108W — Adobe Capability Precheck and Native Coverage Obligations

قبل از هر PDF Source Completeness، connected Adobe Acrobat structured-extraction capability MUST واقعاً AVAILABLE باشد. اگر unavailable/unconnected بود:

`source_completeness_status = INCOMPLETE | UNRESOLVED`
و named gap:
`REQUIRED_STRUCTURED_PDF_EXTRACTION_CAPABILITY_UNAVAILABLE`.

fallback به flattened-text row↔price guessing، Python، hidden code یا historical executable payload ممنوع است.

Native source coverage MUST کل source را برای release/date، section/table title، family/category، source-visible row identifiers، code/model tokens و descriptions پوشش دهد و همه‌ی price-list sectionهای محتمل را به coverage obligation تبدیل کند.

### SRC-108X — Independent Adobe Replay and Coverage Reconciliation

`ADOBE_PASS_A` و `ADOBE_PASS_B` MUST دو invocation مستقل و تازه روی همان original authoritative PDF باشند؛ Pass B از Pass A ساخته نمی‌شود.

هر Pass برای هر price table MUST حفظ کند:
- section/table heading؛
- complete header cells؛
- all extracted rows؛
- code/model cell؛
- description cell؛
- every PRICE column separately؛
- raw PRICE values؛
- code-less/model-less state؛
- source-visible row-number material when available.

A/B MUST در table set، headers/schema، row set، code/model، description، all price cells و code-less rows reconcile شوند. mismatch:
`UNRESOLVED_STRUCTURED_EXTRACTION_REPLAY_MISMATCH`.

همچنین:
- هر native price-list section باید structured section متناظر داشته باشد؛ نبود آن => `UNRESOLVED_SOURCE_SECTION_EXTRACTION_GAP`.
- هر material native row identity باید در structured extraction باشد مگر evidence نشان دهد non-price row است.
- هر Adobe price-bearing row باید به source-visible material trace شود.
- materially unmatched row => `UNRESOLVED_SOURCE_ROW_COVERAGE_GAP`.

### SRC-108Y — Composite Row Identity, Code-Less Preservation and Multi-Price Integrity

Row identity MUST extraction-ordinal-independent باشد و فقط به Persian row-number متکی نباشد؛ RTL reversal مانند `10 -> ۰۱` به‌تنهایی identity authority نیست.

Preferred composite identity:
`TABLE/SECTION + SUPPLIER_CODE_OR_MODEL + DESCRIPTION + RAW_PRICE_CELL_SET`.

Code-less/model-less row:
`TABLE/SECTION + DESCRIPTION + RAW_PRICE_CELL_SET + OTHER_SOURCE_VISIBLE_DISCRIMINATING_MATERIAL`.

اگر دو row قابل تفکیک نباشند:
`UNRESOLVED_SOURCE_ROW_IDENTITY_COLLISION`; merge ممنوع.

Blank/`---`/`-----` supplier code/model هرگز price-bearing row را حذف نمی‌کند. همه‌ی source-visible PRICE columns تا بعد از Source Completeness/classification جدا می‌مانند؛ early scalar collapse ممنوع است.

### SRC-108Z — Adobe/Native Completeness Admission and Table-Family Boundary

`source_completeness_status = COMPLETE` فقط وقتی مجاز است که همه‌ی موارد زیر برقرار باشند:

1. exact current-run PDF authority established؛
2. Adobe capability AVAILABLE؛
3. whole-source native coverage COMPLETE؛
4. Adobe Pass A COMPLETE؛
5. Adobe Pass B COMPLETE؛
6. A/B reconciliation PASS؛
7. native-section ↔ Adobe-table coverage PASS؛
8. source-row coverage PASS؛
9. all PRICE columns preserved؛
10. code-less rows preserved؛
11. zero unresolved table/schema/cell/identity association gaps؛
12. candidate→physical conservation PROVEN؛
13. physical→canonical conservation PROVEN.

Qualification فقط family مشاهده‌شده‌ی NESTECH PDF را که Adobe structured tables قابل‌شناسایی می‌دهد پوشش می‌دهد. materially changed headers/schema، ambiguous cell association، unmatched native section، A/B disagreement یا row identity collision => `UNRESOLVED` و bounded qualification جدید.

PDF-properties endpoint success شرط Source Completeness نیست.

### GOV-028 — Adobe App Availability, Owner Experience and Claim Ceiling

Adobe Acrobat یک connected runtime app/capability است، نه Project Source.

بعد از one-time connection، normal PRICE_UPDATE نباید Owner را مجبور کند Acrobat را دستی اجرا کند، Excel export کند، rowها را inspect کند، count/hash بسازد، PDF convert کند یا A/B را دستی compare کند. مدل invocation و reconciliation را مالک است؛ Owner فقط supplier release را supply/select می‌کند و business approvals عادی را می‌دهد.

Claim ceiling:
`QUALIFIED_MODEL_ORCHESTRATED_STRUCTURED_PDF_EXTRACTION_WITH_INDEPENDENT_REPLAY_AND_SOURCE_COVERAGE_RECONCILIATION`.

هر claim از نوع deterministic parser/program/code enforcement ممنوع است.

### GOV-029 — Adobe/Native Absence and Destructive-Action Guard

پیش از هر `NOT_FOUND` که می‌تواند deactivation/zeroing/deletion یا availability action مخرب بسازد، الزامی است:

- Source Completeness = COMPLETE تحت profile جدید؛
- Adobe A/B reconciliation = PASS؛
- native↔Adobe coverage reconciliation = PASS؛
- zero relevant unresolved gap؛
- focused third recheck برای identity غایب علیه هر دو:
  A. original/native source material؛
  B. Adobe structured result.

Failure:
`UNRESOLVED_ABSENCE_CONFIRMATION`.
هیچ destructive action مجاز نیست.

---

# Addendum v1.13.0 — RC-A6 No-Code Source Artifact / Source Row Identity Root Closure

این successor فقط **Source Artifact binding، Source-row occurrence identity، chronology/duplicate-source projection، Canonical Source IR provenance و Source Completeness evidence semantics** را برای normal `PRICE_UPDATE` در runtime منتخب اصلاح می‌کند. Pricing Formula، Product Decision، Current Master business data، Product Pricing State business state، Import Contract، WooCommerce/Production state، SKU/variation policy، RC-B authority-carrier controls و open Production reconciliation بدون تغییر substantive حفظ می‌شوند.

Runtime هدف همان است:
`CHATGPT PROJECT + LANGUAGE MODEL + CONNECTED ADOBE ACROBAT + NO CODE EXECUTION`.
Normal `PRICE_UPDATE` حق ندارد Python، shell/notebook، hidden deterministic executor، runtime SHA-256 computation، `CANONICAL_JSON` hashing یا cryptographic row-ID recomputation را prerequisite کند.

## RC-A6 identity method selected

برای `OFFICIAL_SUPPLIER_PDF` روش فعال Source identity/completeness عبارت است از:

`RUN_SCOPED_PROVIDER_ARTIFACT_BINDING_V1`
+
`STRUCTURED_SOURCE_VISIBLE_ROW_IDENTITY_V1`
+
`INDEPENDENT_ADOBE_REPLAY_RECONCILIATION`
+
`TWO_STAGE_SOURCE_CONSERVATION`.

Cryptographic evidence فقط وقتی معتبر است که provider/governed artifact واقعاً آن را عرضه کند. مدل حق ساختن SHA/checksum یا نامیدن evidence معنایی به‌عنوان cryptographic/byte-level proof را ندارد.

### SRC-101 — Run-Scoped Source Artifact Binding [v1.13 override]

هر normal `PRICE_UPDATE` باید یک binding صریح برای Source همان Run داشته باشد، حداقل با این semantics:
- `run_id`؛
- `authority_role=ACTIVE_RUN_PRICE_LIST`؛
- `binding_origin=USER_SUPPLIED_FOR_CURRENT_RUN`؛
- `provider`؛
- `provider_file_ref`؛
- `filename`؛
- source-declared release/date/version وقتی واقعاً موجود است؛
- Owner/current-run selection؛
- binding timestamp.

`provider_file_ref` یک **opaque run-scoped provider reference** است؛ content hash یا immutable-byte identity نیست مگر provider evidence آن خاصیت را ثابت کند. Native source coverage، Adobe Pass A و Adobe Pass B MUST به همان `source_binding_ref` برگردند. Drive auto-selection، filename، modified-time یا inferred recency حق ساختن `ACTIVE_RUN_PRICE_LIST` را ندارند.

### SRC-104 / SRC-105 / SRC-107 / SRC-111 — Chronology and duplicate-source semantics [v1.13 override]

`EXACT_SAME_SOURCE != SAME_SEMANTIC_OR_COMMERCIAL_MATERIAL`.

- `RERUN_SAME_SOURCE` فقط وقتی eligible است که exact same artifact با evidence کافی ثابت باشد، مانند still-valid provider artifact identity با semantics کافی برای exact reuse، immutable provider revision، external/provider checksum، یا equivalent method صریحاً qualified؛ و existing explicit rerun authorization نیز باید وجود داشته باشد.
- semantic/material equality به‌تنهایی exact same artifact را ثابت نمی‌کند. اگر exact identity اثبات نشود: `exact_same_source=NOT_PROVEN`; normal/new Run مجاز است اگر سایر gateها پاس باشند و ممکن است بعداً `run_result=NO_CHANGES` شود.
- same declared date + `MATERIAL_SOURCE_COMPARISON=DIFFERENT` یک distinct revision/source observation است؛ unresolved comparison fail-closed chronology semantics را حفظ می‌کند.
- unknown-date history همچنان `SOURCE_CHRONOLOGY_UNVERIFIED` است تا evidence/Owner decision مجاز آن را resolve کند. تاریخ یا ordering از شباهت artifact جعل نمی‌شود.
- NO_CHANGES هرگز به‌تنهایی exact-same-source proof تولید نمی‌کند.

### SRC-108A/B/C — Discovery and bidirectional conservation [v1.13 override]

Discovery از **bound source artifact** و dual-channel Adobe/native evidence آغاز می‌شود، نه از یک derived catalog و نه از hash آن. یک explicit Source Structural Candidate Inventory ساخته می‌شود. هر candidate دقیقاً `PRICE_BEARING_ROW | NON_PRICE_ROW | UNRESOLVED` است.

Invariant الزام‌آور:
`SOURCE_STRUCTURAL_CANDIDATE -> PHYSICAL_PRICE_BEARING_ROW -> CANONICAL_SOURCE_RECORD`.

هر physical price-bearing row دقیقاً یک canonical Source Record دارد و هر canonical record دقیقاً به یک physical row برمی‌گردد. Alias به record موجود attach می‌شود و count را افزایش نمی‌دهد. COMPLETE حداقل نیاز دارد:
`missing=0`, `duplicate_canonicalization=0`, `orphan_canonical=0`, `unresolved=0` و هر دو reconciliation stage = PROVEN. Count equality به‌تنهایی non-probative است؛ omit-one + duplicate-another حتی با count مساوی MUST FAIL.

### SRC-108D/E/F/G/H — No-code classification/schema/region authority [v1.13 override]

برای PDF profile، field role و region/table authority از **source-visible native coverage + Adobe structured table/header/cell evidence + independent replay** حاصل می‌شود؛ نه از mechanical parser یا self-declared classification summary. Derived schema/summary authority مستقل نیست. اختلاف header/role/region/table family یا ambiguous cell association => `UNRESOLVED`.

برای directly-readable structured source path نیز دو independent model reads + semantic column binding + conservation لازم است؛ ادعای deterministic program validation ممنوع است.

### SRC-108I — Bound artifact authority anchor [v1.13 override]

Bound original artifact برای current Run الزامی است، اما normal Adobe/native profile **exact source bytes/SHA recomputation را admission prerequisite نمی‌کند**. Run-scoped provider binding authority را برای invocation همان Run نگه می‌دارد. Cross-run exact byte identity بدون provider checksum/revision یا equivalent exact-artifact evidence = `NOT_PROVEN`.

### SRC-108J/K/L — Replay / zero-result / derived-projection boundary [v1.13 override]

Independent evidence باید مستقیماً همان bound artifact را reinspect کند. Legitimate zero فقط با whole-source native coverage + دو Adobe pass مستقل + reconciliation قابل COMPLETE است. Derived census/normalization/classification projection حق self-certification ندارد و فقط پس از conformance با source-visible evidence مصرف می‌شود.

### SRC-108Y — Structured Source-Visible Row Identity [v1.13 override]

برای ordinary price-bearing row:

```text
{
  source_binding_ref,
  table_or_section_identity,
  supplier_code_or_model,
  description,
  role_preserving_raw_price_cells,
  source_visible_discriminator_when_needed
}
```

`role_preserving_raw_price_cells` یک unordered set نیست. هر PRICE cell حداقل شامل:
`{source_price_column_identity, source_header_or_role, raw_value}` است. چند PRICE column مستقل می‌مانند. جابه‌جایی دو مقدار بین دو PRICE role متفاوت MUST identity/reconciliation mismatch بسازد حتی اگر unordered numeric set ثابت باشد.

برای code-less/model-less row:
`{source_binding_ref, table_or_section_identity, description, role_preserving_raw_price_cells, other_source_visible_discriminating_material}`.

Extraction ordinal identity authority نیست. RTL-sensitive row number sole authority نیست. اگر دو physical row صادقانه قابل تمایز نباشند: `UNRESOLVED_SOURCE_ROW_IDENTITY_COLLISION`; merge ممنوع.

Source-row occurrence identity از downstream product/master identity جدا است. تغییر یک source PRICE cell می‌تواند row occurrence/material evidence را تغییر دهد، اما supplier/product identity برای Matching را silently redefine نمی‌کند. Matching همچنان از permanent mapping، exact supplier/component/BOM code، approved alias و سایر روش‌های مجاز موجود پیروی می‌کند.

### SRC-108Z — Adobe/native completeness admission [v1.13 override]

`COMPLETE` نیاز دارد: run-scoped source artifact binding PROVEN؛ connected Adobe capability AVAILABLE؛ whole-source native coverage COMPLETE؛ fresh independent Adobe A/B COMPLETE؛ A/B reconciliation PASS؛ native↔Adobe section/row coverage PASS؛ all PRICE roles preserved؛ code-less rows preserved؛ zero unresolved table/schema/cell/identity gap؛ candidate→physical PROVEN؛ physical→canonical PROVEN.

`page_census_required_for_pdf_complete=true` و equivalent page-level census gate برای این profile:
`SUPERSEDED_NOT_ACTIVE_FOR_ADOBE_NATIVE_PROFILE`.
Whole-source native text coverage + Adobe table/section coverage + row coverage همچنان mandatory هستند. PDF-properties/page-metadata endpoint success admission gate نیست.

### SRC-IR-002/003/004/004A/004B/006/006A — Canonical Source IR [v1.13 override]

Canonical IR metadata MUST record `source_artifact_binding`, evidence mode, chronology state، exact-same-source status، material comparison when used، explicit inventories، conservation counters و bidirectional mappings.

Canonical Source Record identity structurally قابل بیان است:
`{record_role=CANONICAL_SOURCE_RECORD, source_binding_ref, physical_source_row_identity}`.
No digest required merely to make that relationship meaningful.

Normal no-code PDF profile supersedes these old closure prerequisites:
- mandatory `source.sha256` / `source_file_hash` for Source Completeness؛
- `SHA256(CANONICAL_JSON(raw_cells))`؛
- SHA-derived physical row / Source Record identity؛
- exact-source SHA recomputation؛
- candidate inventory hash as closure proof؛
- reconciliation hash as closure proof؛
- byte-for-byte identity validator recomputation.

Instead require explicit structured inventories، independently recreated row identity components، A/B reconciliation، native↔Adobe coverage، explicit physical↔canonical mappings، zero collision/unresolved counts، and ordinal-reorder semantic equivalence. Canonical IR final artifact MAY retain independently-owned artifact integrity hash where generated by a governed external/package process, but such hash MUST NOT become the normal Source-row identity/completeness proof.

### SRC-ACTIVE-RUN-PRICE-LIST-001 / PRC-012 / AUD-006 / NCH-003 [v1.13 override]

Active run source binding is user-supplied/current-run only. Price provenance for supplier-derived price records `source_binding_ref`, structured Source Record/physical-row identity, table/section, supplier code when present, role-bound raw source price + unit, normalized Rial, formula/rule and existing downstream trace. Historical rollback dataset/state hashes remain valid rollback/history evidence.

Audit summary records source binding, declared release/date/version, exact-same-source status, material source comparison when performed, Source Completeness mode, conservation counters/status and normal run state. Optional provider checksum may be recorded only when actually supplied.

Approved NO_CHANGES updates **source material baseline / binding evidence** rather than requiring a model-computed source hash. Verified date/re-anchor semantics remain unchanged; unknown date never receives fabricated date/order. NO_CHANGES does not infer exact source identity.

### Hash-domain separation

دامنه v1.13 فقط normal `ACTIVE_RUN_PRICE_LIST / SOURCE ROW` identity را migrate کرد و سایر domains را دست‌نخورده گذاشت. **v1.14 normal-runtime mechanics** برای Current Master/PPS/Final Output/Promotion را به provider-native identity/revision/read-back/CAS migrate می‌کند، اما این hash domains همچنان به‌عنوان persisted/external/build/history evidence معتبر باقی می‌مانند:
- authority artifact identity / CURRENT_SET conformance؛
- package/build/archive integrity؛
- rollback/history evidence؛
- externally supplied checksums؛
- historical Current Master/PPS/output hashes.

هیچ successor حق blanket hash removal از persisted/history evidence را ندارد و هیچ LLM runtime حق جعل یا محاسبه checksum بدون executor واقعی را ندارد.

### Claim ceiling

عبارات معتبر برای profile فعال:
`RUN-SCOPED SOURCE ARTIFACT BINDING`؛
`STRUCTURED SOURCE-VISIBLE ROW IDENTITY`؛
`ROLE-PRESERVING SOURCE PRICE CELLS`؛
`INDEPENDENT REPLAY RECONCILIATION`؛
`BIDIRECTIONAL SOURCE ROW CONSERVATION`.

بدون exact evidence، ادعاهای `SHA-verified`, `cryptographic`, `byte-level`, `mechanically recomputed`, `deterministic-program validated` ممنوع‌اند.

---

---


## v1.14.3 post-activation carrier-label corrective closure

`v1.14.2` was selected and provider-read-back qualified, but final active-carrier sweep found one stale active self-version label in `ACC-029` (`Active v1.14.1 disposition`). `v1.14.3` changes only that active carrier label and the current Addendum self-version; provider-native downstream integrity, Source/Adobe controls, RC-B, pricing, matching, Import Contract, Current Master, PPS and Production semantics remain unchanged.

## v1.14.1 corrective pre-promotion closure

`v1.14.1` was a never-selected corrective pre-promotion candidate. `v1.14.2` became the selected predecessor and corrected its self-version/current-runtime-source projections before activation; `v1.14.3` supersedes it only for the final ACC-029/Addendum active-carrier label correction.

`v1.14.0` was a never-selected pre-promotion candidate. v1.14.1 directly repairs stale active SHA/fingerprint carriers in the base Source IR/conservation and acceptance/audit sections instead of relying on an addendum override alone. No business/Production semantic changes are introduced.

# Addendum v1.14.3 — Provider-Native Downstream No-Code Integrity Root Closure

این successor روش فعال زیر را برای downstream runtime integrity انتخاب می‌کند:

`SLPE_PROVIDER_NATIVE_DOWNSTREAM_INTEGRITY_V1`

شامل:

1. `CURRENT_MASTER_PROVIDER_IDENTITY_V1`
2. `TARGETED_AUTHORITATIVE_VALUE_READBACK_V1`
3. `PERSISTED_OUTPUT_PROVIDER_IDENTITY_V1`
4. `PPS_PROVIDER_IDENTITY_V1`
5. `PROVIDER_NATIVE_EXPECTED_PARENT_CAS_V1`
6. `BOUNDED_IMPORT_HANDOFF_ASSURANCE_V1`

## Current Master

Current Master concurrency با `provider + resource_id + provider_revision_id` Pin می‌شود. Gateهای pre-final و pre-promotion fresh revision read دارند. هر revision change = change محافظه‌کارانه. Revision equality byte equality نیست. Exact field correctness همیشه با targeted Sheet read-back جداگانه اثبات می‌شود.

## Final Output

پس از ساخت Candidate/Final CSV، Artifact باید Persist و Provider read-back شود. Identity حداقل:
`provider, resource_id, provider_revision_id, run_id, run_version, filename, mime_type, provider_size_when_available, persisted_read_back`.
Approval به همین identity bind می‌شود. same filename کافی نیست؛ resource یا revision متفاوت binding قدیمی را invalid می‌کند.

## PPS

Committed PPS با Version + provider resource/revision + parent Master identity Pin می‌شود. Proposed PPS یک Artifact جدا و non-authoritative است و فقط بعد از Promotion معتبر Commit می‌شود. Failed/Rejected/Blocked Run حق انتخاب Proposed PPS را ندارد.

## Provider-native CAS

Immediately before consequential commit:
`expected Master identity == fresh Current Master identity`
و
`expected committed PPS identity == fresh current PPS identity`.
Mismatch یا unavailable revision evidence ⇒ Block/Resync؛ stale overwrite ممنوع.

## WP All Import claim ceiling

Provider-bound Final Output فقط تا handoff boundary اثبات می‌شود. اگر WP All Import/external environment checksum/receipt مستقل بدهد، آن Evidence مصرف می‌شود. در غیر این صورت فقط branch مجاز `USER_ATTESTED` با assurance پایین‌تر معتبر است و هرگز byte verification/cryptographic proof نامیده نمی‌شود.

## Conditional paths

- `NEW-005`: complete known reserved-set enumeration + provider identities + max+1 + collision/reuse checks؛ نبود enumeration کامل ⇒ `UNRESOLVED_COLUMN1_GENERATION`.
- Partial Promotion: exact Run + Final Output provider resource/revision + exact subject set + reconciliation queue + expected Master/PPS identities؛ هر identity drift ⇒ Block.
- Mutation Plan: provider resource/revision + run/scope + target identities + mutation inventory + read-back؛ plan revision change binding قبلی را invalid می‌کند.
- `MASTER_DATA_EDIT`: expected Master resource/revision + fresh revision + fresh target value + expected-before compare + approved final value + post-write read-back.
- Draft/output binding: provider artifact identity، نه runtime hash.

## Source SHA same-root closure

`WF-003`, `RUN-010`, `SRC-101/104/105/107/111`, `ERR-002` و compiled `SRC-ACTIVE-RUN-PRICE-LIST-001` باید یک semantics واحد داشته باشند:
`EXACT_SAME_SOURCE != SAME_SEMANTIC_OR_COMMERCIAL_MATERIAL`.
`DUPLICATE_SOURCE_FILE` و `RERUN_SAME_SOURCE` فقط وقتی exact same artifact PROVEN است فعال می‌شوند. در غیر این صورت `exact_same_source=NOT_PROVEN` و NORMAL processing MAY ادامه یابد.

## Cryptographic evidence boundary

Persisted authority/build/history hashes و external checksums حذف نمی‌شوند. normal runtime LLM فقط آن‌ها را consume می‌کند. Model-computed SHA, canonical-byte hashing, fingerprint generation یا ادعای byte-level proof بدون executor واقعی ممنوع است.

## Claim ceiling

عبارات معتبر:
`PROVIDER-NATIVE RESOURCE/REVISION IDENTITY`,
`FRESH PROVIDER READ-BACK`,
`TARGETED AUTHORITATIVE VALUE READ-BACK`,
`EXPECTED-PARENT PROVIDER CAS`,
`BOUNDED IMPORT HANDOFF ASSURANCE`.

بدون executor مستقل، عبارات `cryptographically verified`, `byte-identical`, `hash-recomputed`, `mechanically validated` ممنوع‌اند.

---
