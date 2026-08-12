## 20. Risk Register for Roadmap Ordering

| RiskDependencyEarliest Proof PointFailure Consequence |                            |      |                                                |
| ----------------------------------------------------- | -------------------------- | ---- | ---------------------------------------------- |
| R0 Delivery stack fails on Windows                    | none                       | P001 | کل stack ممکن است دیرهنگام fail شود            |
| R1 PDF/DOCX fidelity                                  | P001                       | P002 | silent supplier Price/Code corruption          |
| R2 Rule migration semantic drift                      | P001                       | P003 | Python behavior از Master منحرف می‌شود         |
| R5 Current/crash consistency                          | P001                       | P004 | ambiguous commercial Current state             |
| R3 Self-confirming test truth                         | P003 + source fixtures     | P005 | wrong Rule + wrong test both PASS              |
| R-FIN Matching/BOM/Pricing                            | P002/P003/P005             | P006 | wrong monetary output                          |
| R-STATE Commercial state policies                     | P006                       | P007 | override/scope/availability drift              |
| R4 CSV/replay/mutation                                | P006/P007                  | P008 | valid calculations but corrupt import artifact |
| R7 Operator/GUI workflow                              | prior application services | P009 | user can misuse otherwise correct engine       |
| R6 Promotion/Partial                                  | P004/P008/P009             | P010 | bad state promoted                             |
| R-RLB Rollback safety                                 | P010                       | P011 | historical state corrupted/recalculated        |
| R-MIG Legacy drift remains unresolved                 | P006–P011                  | P012 | migration closes with missing Rules            |
| R-REL Packaged release integration                    | all                        | P013 | source code passes but user app fails          |

---

## 21. Roadmap Ordering Rationale

Ordering rule:

```
all unfinished capabilities
        ↓
dependencies satisfied?
        ↓
dependency-ready set
        ↓
highest silent-correctness / integration / recovery uncertainty
        ↓
next Plan

```

### چرا P002 زود است؟

چون اگر Source fidelity غلط باشد، هیچ pricing correctness downstream ارزشی ندارد. Source Completeness نیز باید observable باشد و confidence-based نباشد.

### چرا P003 قبل از business handlers است؟

برای اینکه Python handlerها قبل از تعیین Master→Rulebook→code trace ساخته نشوند.

### چرا P004 قبل از full Domain است؟

Atomic Current/crash recovery یک risk مستقل و high-impact است و prerequisites کمی دارد؛ نباید تا پایان پروژه مخفی بماند.

### چرا P005 قبل از P006 است؟

برای شکستن self-confirmation loop:

```
Legacy Rulebook
→ code
→ expected test
→ green

```

Golden/ACC truth باید قبل از business handlers path مستقل داشته باشد.

### چرا GUI کامل در P009 است، نه P001؟

P001 فقط technology/delivery spine را اثبات می‌کند. GUI workflow واقعی زمانی ساخته می‌شود که application semantics واقعی برای نمایش وجود داشته باشد.

### عمداً postponed

- UI polish؛
- automatic WP Import؛
- Google Sheets automation؛
- OCR؛
- native PDF extractor؛
- generic DSL؛
- installer/updater؛
- multi-format adapters.

---

# 22. MASTER\_IMPLEMENTATION\_ROADMAP

```
Artifact ID: SLPE-MIR-001
Version: 1.0.0
Status: FROZEN_FOR_PLAN_GENERATION
Ordering: Dependency-Ordered Risk-First
Plan Protocol: SLPE-LIEP-v1

```

| Plan IDNameObjectiveDependenciesPrimary Risk RetiredMajor Scope BoundaryRequired Evidence ClassAcceptance BoundaryOwner CheckpointWhy Now |                                                   |                                                                                                                                                             |                        |                                  |                                                      |                                                                                      |                                                                                                                  |                     |                                                                          |
| ----------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------- | -------------------------------- | ---------------------------------------------------- | ------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------- | ------------------- | ------------------------------------------------------------------------ |
| **SLPE-P001**                                                                                                                             | **Foundation / Delivery Spine Proof**             | Prove repository, frozen authorities, state protocol, PySide6/Qt, thread, CI and Windows package spine                                                      | none                   | R0                               | no business logic                                    | static checks, tests, Qt smoke, CI, Windows package smoke                            | skeleton/state validated; GUI launches RTL/LTR; workspace/thread/package smoke pass                              | **UX**              | frozen first milestone; all later work depends on viable delivery spine  |
| **SLPE-P002**                                                                                                                             | Verified Source Catalog Slice                     | Real PDF+DOCX → deterministic verified `Normalized_Source_Catalog`                                                                                          | P001                   | R1                               | source only; no pricing                              | real fixture, corruption fixtures, deterministic replay                              | actual source pair fully classified/verified or fails closed; known corruptions detected                         | NONE                | first correctness input boundary                                         |
| **SLPE-P003**                                                                                                                             | Legacy Rule Migration & Trace Foundation          | Audit legacy Rulebook, establish Runtime Rulebook projection + Rule Trace Matrix                                                                            | P001                   | R2                               | no business handlers yet                             | hash/structure audit, Master conformance, trace validation                           | all legacy rules classified; high-risk families audited; candidate runtime projection traceable                  | NONE                | prevents handler implementation on ambiguous migration basis             |
| **SLPE-P004**                                                                                                                             | Current / Run Repository & Crash-Safe State Spine | Prove versioned filesystem state, Manifest atomicity, Current replacement, lock, stale-parent primitives                                                    | P001                   | R5                               | storage mechanics only; no business promotion        | filesystem tests, failure injection, restart/recovery tests                          | old/new Current never ambiguous across tested failure points                                                     | NONE                | high-impact recovery risk is independently dependency-ready              |
| **SLPE-P005**                                                                                                                             | Independent ACC / Golden Oracle Baseline          | Establish Master-derived expected truth and ACC inventory independent of legacy Rulebook implementation                                                     | P002, P003             | R3                               | fixtures/oracles only; no production handlers        | Master-derived Golden fixtures, synthetic discriminating fixtures, provenance checks | critical expected values have independent authority path; ACC coverage inventory established                     | NONE                | must exist before production business code                               |
| **SLPE-P006**                                                                                                                             | Deterministic Matching / BOM / Pricing Core       | Implement explicit deterministic MAT/PRC handlers and bindings                                                                                              | P002, P003, P005       | financial correctness            | pure domain; excludes discount/override/state commit | parameterized rule tests, BOM fixtures, Golden pricing, selected legacy differential | direct/composite/match/conflict fixtures reproduce Master-authorized results exactly                             | **BUSINESS**        | highest financial-risk business logic after independent oracle exists    |
| **SLPE-P007**                                                                                                                             | Commercial Policy & Product State Core            | Implement scope, availability, discounts, override, excluded pricing, new products, Proposed Product State                                                  | P004, P005, P006       | state/policy drift               | pure/proposed commercial state; no promotion         | state transition fixtures, negative/blocker cases, business evidence                 | all covered policies deterministic; no Draft state becomes committed                                             | **BUSINESS**        | pricing result now exists; state semantics can be layered safely         |
| **SLPE-P008**                                                                                                                             | Exact Dataset / Validation / Output / Replay      | Build full Master mutation boundary, validators, canonical JSON/CSV, Audit XLSX and deterministic replay                                                    | P005, P006, P007       | R4                               | no live import/promotion                             | 95-column Golden bytes, mutation tests, replay hashes, Audit content                 | full dataset preserved; exact CSV bytes/hash and Proposed State replay identical                                 | NONE                | converts correct domain results into safe import artifacts               |
| **SLPE-P009**                                                                                                                             | Guided Run GUI & Final Approval                   | Integrate intake→processing→review→approval/resume in Persian desktop GUI                                                                                   | P002, P004, P007, P008 | R7                               | ends at `IMPORT_READY`; no Promotion                 | Qt tests, workflow integration, packaged smoke, approval hash checks                 | nontechnical operator can complete guided Run and approval is bound to exact output                              | **UX**              | real application semantics now exist for GUI integration                 |
| **SLPE-P010**                                                                                                                             | Import Outcome / Promotion / Partial Recovery     | Implement Success/Failed/Partial/No-change, artifact assurance, Promotion Gate and atomic Current commit                                                    | P004, P008, P009       | R6                               | no rollback yet                                      | lifecycle fixtures, failure injection, stale-parent tests, ACC-025/027/028           | failed/partial never promote; success promotes Master+State coherently                                           | NONE                | Candidate/approval/output and atomic repository mechanics are now proven |
| **SLPE-P011**                                                                                                                             | Rollback & Reconciliation Run                     | Implement controlled rollback candidate/safety/import/promotion path                                                                                        | P010                   | rollback corruption              | rollback/reconciliation only                         | safe/unsafe historical fixtures, missing-state tests                                 | rollback never silently recalculates historical payload; unsafe candidate blocked                                | NONE                | rollback requires final Promotion semantics                              |
| **SLPE-P012**                                                                                                                             | Full Migration / ACC / Regression Closure         | Close Rule Trace, full applicable ACC suite, historical regression, end-to-end replay; run bounded legacy LLM differential; retire active legacy comparison | P002–P011              | residual semantic migration risk | no feature expansion                                 | full ACC, historical regression, trace coverage, differential evidence               | no mandatory Rule missing; applicable ACC pass; no unresolved migration discrepancy; legacy differential retired | **BUSINESS**        | only now can migration completeness be truthfully assessed               |
| **SLPE-P013**                                                                                                                             | Windows v1 Release Acceptance                     | Produce release-grade onedir build and final packaged acceptance on fresh/Persian path                                                                      | P012                   | release integration              | release stabilization only                           | full CI, package smoke, fresh-workspace E2E, owner acceptance                        | exact release artifact passes technical suite and owner final acceptance                                         | **FINAL\_APPROVAL** | final gate after semantics/migration are closed                          |

---

## 23. Critical Risk Coverage Check

| Critical RiskRetired By            |                                                          |
| ---------------------------------- | -------------------------------------------------------- |
| Windows delivery spine             | **P001**, final confirmation P013                        |
| PDF/DOCX fidelity                  | **P002**                                                 |
| Rulebook migration                 | **P003**, closure P012                                   |
| Independent oracle                 | **P005**                                                 |
| Pricing/matching silent corruption | **P006**                                                 |
| Deterministic CSV/replay           | **P008**                                                 |
| Current/recovery                   | **P004**, integration P010                               |
| Import/Promotion/Partial           | **P010**                                                 |
| Rollback                           | **P011**                                                 |
| GUI/operator usability             | P001 technical smoke, **P009 real workflow**, P013 final |
| Legacy residual discrepancy        | **P012**                                                 |
| Packaged release                   | **P013**                                                 |

هیچ R1–R7 بدون Plan نمانده است.

---

## 24. Legacy Migration Coverage Check

| Frozen Migration ObligationRoadmap Coverage |                                                       |
| ------------------------------------------- | ----------------------------------------------------- |
| Freeze exact legacy artifact                | **P001**                                              |
| Rulebook↔Master structural audit            | **P003**                                              |
| classify Rule structures                    | **P003**                                              |
| Runtime Rulebook projection                 | **P003**                                              |
| handler binding mechanism                   | P003; handlers in **P006–P011**                       |
| Rule Trace Matrix                           | **P003**, updated P006–P011, closed P012              |
| independent Master-derived truth            | **P005**                                              |
| ACC migration                               | P005 inventory; relevant Plans; **P012 full closure** |
| Golden fixtures                             | **P005**, expanded by P006–P011                       |
| temporary legacy LLM differential           | selected P006 cases + **P012 closure**                |
| differential disagreement adjudication      | P006/P012 using Master/evidence                       |
| legacy LLM retirement                       | **P012**                                              |
| legacy historical archive                   | P001 onward reference-only                            |

`ACC-*` runtime Rules نمی‌شوند؛ Migration Contract صریحاً آنها را build/acceptance controls می‌داند.

---

## 25. First Milestone Definition

Milestone:

> **Foundation / Delivery Spine Proof**
هدفش اثبات Price Engine نیست.

هدفش اثبات این است که infrastructure حداقلی انتخاب‌شده واقعاً قابل توسعه، تست، review، resume و package است.

### باید اثبات کند

```
Repository skeleton
+
frozen authorities in correct locations
+
PROJECT_STATE authority
+
locked Python environment
+
generic tests/CI
+
Plan scope machinery
+
PySide6 app shell
+
Persian RTL + technical LTR
+
same-process QThread responsiveness
+
filesystem Workspace creation
+
Windows onedir build
+
packaged launch under Persian path

```

### صریحاً خارج Milestone

- supplier parsing production logic؛
- PDF verification production logic؛
- `Normalized_Source_Catalog` full pipeline؛
- Runtime Rulebook migration؛
- matching؛
- pricing؛
- discount؛
- Product State business semantics؛
- exact 95-column output؛
- import/promotion؛
- rollback.

LIEP همین Foundation/Delivery Spine proof را first milestone فریز کرده است.

---

## 26. SLPE-P001 PLAN SEED

### Identity

```
SLPE-P001 — Foundation / Delivery Spine Proof

```

### Objective

ایجاد و اثبات حداقل development/delivery spine که همه Planهای بعدی روی آن قابل اجرا باشند، بدون ورود به Price Engine business behavior.

### Dependencies

```
None

```

External required authorities:

```
SLPE-MASTER-SPEC-001 v1.2.0
IAC-v1
SLPE-TIC-v1
SLPE-LIEP-v1
SLPE-LLM-PY-MIGRATION-v1
current repo HEAD
legacy package v1.1.3

```

### Scope

- repository skeleton minimum؛
- frozen authority placement؛
- exact legacy reference archive؛
- `PROJECT_STATE.json` initial implementation؛
- locked Python environment؛
- minimal Python package/entrypoint؛
- PySide6 shell؛
- Persian RTL/LTR smoke surface؛
- same-process QThread task smoke؛
- minimal Workspace creation؛
- generic tests؛
- generic CI؛
- Plan scope/diff validation mechanism؛
- PyInstaller onedir/windowed smoke.

### Non-goals

- DOCX business extraction؛
- PDF evidence verifier؛
- Canonical Source production model؛
- Runtime Rulebook migration؛
- matching؛
- BOM؛
- pricing؛
- discounts/override/availability؛
- 95-column output؛
- Final Approval business semantics؛
- import/promotion؛
- rollback.

### Expected artifacts after Plan

```
PROJECT_STATE.json
pyproject.toml
uv.lock
.python-version

authorities/master/...
authorities/contracts/...

references/legacy_llm/...

plans/SLPE-P001-foundation-delivery-spine.md

src/switchland_price_engine/
  minimal application/ui/repositories shell

tests/
  foundation smoke coverage

tools/
  state validation
  scope/diff validation

.github/workflows/validate.yml

packaging/
  frozen PyInstaller build definition

```

### Required evidence classes

```
ENVIRONMENT_LOCK_EVIDENCE
PROJECT_STATE_VALIDATION
STATIC_QUALITY
AUTOMATED_TESTS
QT_RTL_LTR_SMOKE
QTHREAD_RESPONSIVENESS_SMOKE
WORKSPACE_FILESYSTEM_SMOKE
EXACT_HEAD_CI
WINDOWS_PACKAGE_BUILD
WINDOWS_PACKAGE_LAUNCH
PERSIAN_PATH_PACKAGE_SMOKE
INDEPENDENT_INSPECTOR
SCOPE_DIFF_CHECK

```

### Acceptance boundary

P001 فقط وقتی `VERIFIED` است که:

- project state معتبر باشد؛
- frozen authorities version-bound باشند؛
- legacy artifact authority-confusion ایجاد نکند؛
- environment lock reproduce شود؛
- app shell بدون CLI requirement launch شود؛
- RTL/LTR smoke pass شود؛
- چندثانیه worker smoke UI را hung نکند؛
- Workspace test pass شود؛
- generic CI exact-head pass شود؛
- Windows `onedir + windowed` build launch شود؛
- package از path فارسی smoke شود؛
- scope diff مجاز باشد؛
- Independent Inspector `PASS` دهد.

هیچ‌کدام اکنون اجرا نشده‌اند.

### Owner checkpoint

**`UX`**

فقط برای تأیید ساده اینکه Persian RTL و technical LTR display قابل‌فهم هستند؛ Owner technical verification انجام نمی‌دهد.

---

## 27. Owner Interaction Map

| PlanOwner CheckpointLegitimate Owner Role |                     |                                                                                       |
| ----------------------------------------- | ------------------- | ------------------------------------------------------------------------------------- |
| P001                                      | **UX**              | خوانایی RTL/LTR basic shell                                                           |
| P002                                      | NONE                | source files قبلاً evidence هستند؛ فقط mismatch واقعی ممکن است جداگانه سؤال ایجاد کند |
| P003                                      | NONE                | migration/trace technical                                                             |
| P004                                      | NONE                | filesystem/recovery technical                                                         |
| P005                                      | NONE                | Master-derived test evidence                                                          |
| P006                                      | **BUSINESS**        | فقط unresolved authoritative BOM/mapping/business evidence                            |
| P007                                      | **BUSINESS**        | bootstrap Scope/Product State/override decisions اگر evidence کافی نیست               |
| P008                                      | NONE                | deterministic technical output                                                        |
| P009                                      | **UX**              | guided workflow/review usability                                                      |
| P010                                      | NONE                | lifecycle correctness با fixtures                                                     |
| P011                                      | NONE                | rollback technical semantics                                                          |
| P012                                      | **BUSINESS**        | فقط اگر migration discrepancy واقعاً نیازمند new business authority باشد              |
| P013                                      | **FINAL\_APPROVAL** | packaged v1 usability/business release acceptance                                     |

Owner هیچ Planی را به‌دلیل بررسی code/diff/test log approve نمی‌کند.

---

## 28. WHAT\_NOT\_TO\_BUILD

v1 Planning عمداً اینها را حذف می‌کند:

- database / ORM؛
- server/backend/API؛
- web frontend؛
- Electron؛
- microservices؛
- worker queue؛
- multiprocessing platform؛
- Celery/RQ؛
- DI container؛
- generic repository framework؛
- `Clean Architecture` ceremony؛
- generic Rule DSL/interpreter؛
- generic Rule code generator؛
- 264-rule auto-codegen؛
- LLM production runtime؛
- OCR؛
- vector memory؛
- agent orchestration platform؛
- second project status ledger؛
- `STATUS.md` authority؛
- separate Handoff files؛
- Prompt-specific CI؛
- one Plan per Rule؛
- one Plan per class/file؛
- ADR per coding decision؛
- enterprise GitFlow؛
- Scrum/RACI؛
- security governance؛
- automatic WP Import؛
- automatic Google Sheets integration؛
- multi-format source plugin system؛
- full GUI theme/design system before workflow proves stable.

---

## 29. NOT\_PROVEN / Deferred Decisions

هیچ‌کدام Planning selection را Block نمی‌کنند.

### 1. Full 133-record PDF evidence route

`NOT_PROVEN`

Resolved by:

> **P002**

Technology Contract نیز همین مورد را implementation acceptance uncertainty می‌داند.

### 2. Semantic correctness all legacy Rulebook compiled semantics

`NOT_PROVEN`

Structural Rule→Master hashes قبلاً قوی‌اند، اما semantic compilation همه Rules ثابت نشده است.

Resolved progressively:

> **P003 + P006–P012**

Migration Contract نیز high-risk Rule audit را الزام آینده می‌داند.

### 3. Exact Python Runtime Rulebook schema

`NOT_PROVEN`

Architecture role frozen است؛ exact field subset هنوز implementation detail است.

Resolved by:

> **P003**

### 4. Product Pricing State bootstrap content

`NOT_PROVEN`

Resolved by:

> **P007**

با authoritative evidence/business checkpoint؛ Price به‌تنهایی نباید State را حدس بزند. Master نیز Master/State mismatch را blocker می‌داند.

### 5. Exact Current Master acquisition mechanism

`NOT_PROVEN`

Repository boundary frozen است ولی v1 actual provider هنوز final نشده.

Resolved inside:

> **P008/P009**

بدون تغییر Domain boundary.

### 6. Strongest available imported-artifact evidence

`NOT_PROVEN`

WP All Import ممکن است hash مستقل بدهد یا فقط Run-bound user attestation ممکن باشد.

Resolved by:

> **P010**

Master هر دو assurance path را از قبل تعریف کرده است.

### 7. Packaged full-stack compatibility

`NOT_PROVEN`

اولین bounded proof:

> **P001**

Final proof:

> **P013**

---

## 30. Planning Consistency Audit

| CheckResult                                                     |                             |
| --------------------------------------------------------------- | --------------------------- |
| Five frozen authorities bound                                   | **PASS**                    |
| Repository actual HEAD verified                                 | **PASS**                    |
| GUI frozen                                                      | **PASS**                    |
| stale CLI clause explicitly identified/superseded               | **PASS**                    |
| PROJECT\_STATE sole project-status authority                    | **PASS**                    |
| Rule Trace does not authorize project Plans                     | **PASS**                    |
| Legacy package separated from active authority                  | **PASS**                    |
| Active Python Runtime Rulebook has distinct location            | **PASS**                    |
| No generic Rule DSL/interpreter introduced                      | **PASS**                    |
| Independent Golden/ACC path exists before domain implementation | **PASS**                    |
| ACC remains test/build control                                  | **PASS**                    |
| Source risk appears early                                       | **PASS — P002**             |
| Rule migration appears early                                    | **PASS — P003**             |
| Crash/Current risk appears early                                | **PASS — P004**             |
| Self-confirmation risk closed before handlers                   | **PASS — P005 before P006** |
| Pricing/matching/BOM covered                                    | **PASS — P006**             |
| Product State/discount/override/availability covered            | **PASS — P007**             |
| exact 95-column/CSV/replay covered                              | **PASS — P008**             |
| GUI mandatory integration covered                               | **PASS — P009**             |
| Final Approval covered                                          | **PASS — P009**             |
| Import Success/Failed/Partial covered                           | **PASS — P010**             |
| Promotion/current commit covered                                | **PASS — P010**             |
| Rollback covered                                                | **PASS — P011**             |
| Historical regression covered                                   | **PASS — P012**             |
| temporary Legacy LLM differential exists                        | **PASS — P006/P012**        |
| Legacy LLM retirement exists                                    | **PASS — P012**             |
| Windows final package acceptance exists                         | **PASS — P013**             |
| Domain independent of GUI/PDF/DOCX/filesystem/WP                | **PASS**                    |
| No DB/server/microservice added                                 | **PASS**                    |
| Every Plan has earlier dependencies only                        | **PASS**                    |
| Owner technical burden minimized                                | **PASS**                    |
| No implementation/tests/CI falsely claimed executed             | **PASS**                    |

Master's required end-to-end determinism—including same semantic output، CSV bytes/hash و Proposed State hash—در P008/P012 صریحاً پوشش داده شده است.

Partial non-promotion، stale-parent و recovery نیز در P010 دقیقاً Plan شده‌اند.

بنابراین planning baseline از Empty Repository تا Reliable v1 کامل و dependency-valid است.

---
