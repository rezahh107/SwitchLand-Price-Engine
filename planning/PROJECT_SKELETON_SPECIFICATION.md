# 8. PROJECT\_SKELETON\_SPECIFICATION

```
Artifact ID: SLPE-PSS-001
Version: 1.0.0
Status: FROZEN_FOR_P001_PLAN_GENERATION
Target Repository: rezahh107/SwitchLand-Price-Engine
Baseline HEAD: 8b843c97ca27bfc1bf54b1eace25a18df1e484e1
Architecture: IAC-v1 with explicit Desktop-GUI amendment
Technology: SLPE-TIC-v1
Execution Protocol: SLPE-LIEP-v1
Migration Contract: SLPE-LLM-PY-MIGRATION-v1

```

### Repository purpose

Repository مالک این موارد است:

- Python source؛
- version-controlled authorities baseline؛
- deterministic business handlers؛
- source adapters؛
- GUI؛
- repository/persistence implementation؛
- tests/fixtures؛
- implementation Plans؛
- build/CI definitions؛
- traceability artifacts.

Repository مالک live production Workspace نیست.

### Authority hierarchy

```
Master Specification
        ↓
Python Runtime Rulebook / approved runtime authorities
        ↓
Explicit Python handlers
        ↓
Tests / trace evidence

Legacy LLM package
= reference/migration evidence only

```

### Application organization

```
src/switchland_price_engine/
    application/
    domain/
    source/
    repositories/
    validation/
    output/
    ui/

```

### Boundary responsibilities

**`application/`**

- Run Coordinator؛
- application commands؛
- lifecycle orchestration؛
- Final Approval flow؛
- Import Outcome؛
- Promotion/Rollback coordination؛
- authority pinning orchestration.

**`domain/`**

- matching؛
- BOM؛
- pricing؛
- discount؛
- override؛
- availability؛
- scope/state business transitions؛
- deterministic handlers.

No external side effects.

**`source/`**

- DOCX preflight/extraction؛
- PDF evidence verification؛
- Source Completeness؛
- construction of Canonical Source.

**`repositories/`**

- filesystem layout؛
- Master snapshots؛
- Product State versions؛
- Run artifacts؛
- Manifest؛
- Current pointer؛
- atomic writes؛
- Win32 mutex/ReplaceFileW wrappers.

**`validation/`**

- cross-boundary deterministic validators؛
- readiness gates؛
- mutation preservation؛
- schema/output/promotion checks.

**`output/`**

- canonical JSON؛
- exact CSV؛
- Audit XLSX؛
- output hashes.

**`ui/`**

- Qt views؛
- presenters/controllers؛
- Qt table models؛
- background-task bridge؛
- Persian RTL/LTR presentation.

### Runtime Workspace boundary

خارج از repo:

```
Workspace/
├── current/
│   └── CURRENT.json
├── authorities/
├── masters/
├── product_state/
└── runs/

```

Domain حق دانستن این layout را ندارد.

---

## 9. Canonical Repository Tree

Legend:

- `P001_CREATE` — باید در Plan اول ایجاد/تثبیت شود.
- `LATER_CREATE` — فقط وقتی Plan مالک آن capability اجرا شد.
- `REFERENCE_ONLY` — هیچ Runtime authority ندارد.

```
SwitchLand-Price-Engine/
│
├── .gitattributes                         [EXISTING]
├── .gitignore                             [P001_CREATE]
├── README.md                              [EXISTING / P001_UPDATE]
├── .python-version                        [P001_CREATE]
├── pyproject.toml                         [P001_CREATE]
├── uv.lock                                [P001_CREATE]
├── PROJECT_STATE.json                     [P001_CREATE]
│
├── authorities/                           [P001_CREATE]
│   ├── master/
│   │   └── SLPE-MASTER-SPEC-001_v1.2.0.md
│   │                                        [P001_CREATE]
│   │
│   ├── contracts/
│   │   ├── IMPLEMENTATION_ARCHITECTURE_CONTRACT_IAC-v1.md
│   │   ├── TECHNOLOGY_IMPLEMENTATION_CONTRACT_SLPE-TIC-v1.md
│   │   ├── LLM_IMPLEMENTATION_EXECUTION_PROTOCOL_SLPE-LIEP-v1.md
│   │   └── LEGACY_LLM_TO_PYTHON_MIGRATION_SLPE-LLM-PY-MIGRATION-v1.md
│   │                                        [P001_CREATE]
│   │
│   └── runtime/
│       ├── rulebook/                       [LATER_CREATE]
│       ├── schema/                         [LATER_CREATE]
│       ├── import_contract/                [LATER_CREATE]
│       └── part_number_reference/          [LATER_CREATE]
│
├── references/
│   └── legacy_llm/
│       └── SwitchLand_Price_Engine_GPT_Project_Package_v1.1.3.zip
│                                            [P001_CREATE][REFERENCE_ONLY]
│
├── traceability/
│   └── RULE_TRACE_MATRIX.json              [LATER_CREATE]
│
├── plans/
│   ├── SLPE-P001-foundation-delivery-spine.md
│   │                                        [P001_CREATE]
│   └── decision-escapes/                    [LATER_CREATE]
│
├── src/
│   └── switchland_price_engine/
│       ├── __init__.py                      [P001_CREATE]
│       ├── main.py                          [P001_CREATE]
│       │
│       ├── application/                     [P001_CREATE minimal]
│       ├── ui/                              [P001_CREATE minimal]
│       ├── repositories/                    [P001_CREATE minimal]
│       │
│       ├── source/                          [LATER_CREATE]
│       ├── domain/                          [LATER_CREATE]
│       ├── validation/                      [LATER_CREATE]
│       └── output/                          [LATER_CREATE]
│
├── tests/
│   ├── fixtures/                            [P001_CREATE minimal]
│   │   ├── source/                          [LATER_CREATE]
│   │   ├── business/                        [LATER_CREATE]
│   │   ├── lifecycle/                       [LATER_CREATE]
│   │   └── golden/                          [LATER_CREATE]
│   │
│   ├── acceptance/                          [LATER_CREATE]
│   └── <foundation smoke tests>             [P001_CREATE]
│
├── tools/
│   ├── <PROJECT_STATE validation>           [P001_CREATE]
│   └── <Plan scope/diff check>              [P001_CREATE]
│
├── packaging/
│   └── <PyInstaller build definition>       [P001_CREATE]
│
└── .github/
    └── workflows/
        └── validate.yml                     [P001_CREATE]

```

### دلیل top-levelها

- `authorities/`: جلوگیری از ambiguity درباره active normative/operational inputs.
- `references/`: جلوگیری از اشتباه گرفتن legacy با authority.
- `traceability/`: جلوگیری از silent missing Rules.
- `plans/`: fresh-agent continuation و scope lock.
- `src/`: executable implementation.
- `tests/`: executable truth/evidence.
- `tools/`: machine enforcement Plan/state checks.
- `packaging/`: Windows build concern بدون آلودگی Domain.
- `.github/`: exact-head CI evidence.

`docs/`, `infrastructure/`, `services/`, `adapters/`, `core/`, `common/`, `utils/` از ابتدا ساخته نمی‌شوند.

---

## 10. Dependency Rules

High-level direction:

```
ui
 │
 ▼
application
 │
 ├──────────────► source
 │
 ├──────────────► validation
 │
 ├──────────────► output
 │
 ├──────────────► repositories
 │
 ▼
domain

```

اما dependency دقیق‌تر:

```
source
→ Canonical Source Models

repositories
→ snapshots / persisted boundary models

application
→ domain + source + validation + output + repositories

ui
→ application only

domain
→ Python/Pydantic domain values
→ NO ui
→ NO source adapters
→ NO repositories
→ NO filesystem

```

Domain ممنوع است به:

```
PySide6
python-docx
PyMuPDF
pathlib-based workspace layout
WP All Import

```

وابسته باشد.

Master نیز صریحاً می‌خواهد بعد از Canonical IR، Rule Engine PDF را reread نکند و Source Extractor و Rule Engine مستقل test شوند.

---

## 11. Authority and Legacy Placement

### Active authority

```
authorities/master/
    SLPE-MASTER-SPEC-001_v1.2.0.md

authorities/contracts/
    IAC-v1
    SLPE-TIC-v1
    SLPE-LIEP-v1
    SLPE-LLM-PY-MIGRATION-v1

authorities/runtime/rulebook/
    Python-oriented Runtime Rulebook

authorities/runtime/schema/
    active Master/output schema versions

authorities/runtime/import_contract/
    WP All Import contract versions

authorities/runtime/part_number_reference/
    approved reference versions

```

### Traceability

```
traceability/RULE_TRACE_MATRIX.json

```

این business authority نیست؛ Rule coverage authority است.

### Legacy

```
references/legacy_llm/
    SwitchLand_Price_Engine_GPT_Project_Package_v1.1.3.zip

```

هیچ legacy Rulebook extracted copy زیر `authorities/` قرار نمی‌گیرد.

Migration Contract دقیقاً active Master/Runtime Rulebook را از legacy reference جدا می‌کند.

### Legacy artifact identity

P001 باید exact ZIP bytes را با Hash فعلی bind کند:

```
a4be3f7a2b5eae4dffc3a8fede16dba88bf37722d7417711622788d398251800

```

ولی این Hash در P001 دوباره از artifact موجود محاسبه می‌شود؛ Planning فعلی به‌تنهایی enforcement نیست.

---

## 12. PROJECT\_STATE v1 Specification

`PROJECT_STATE.json` تنها implementation-status authority است، همان‌طور که `SLPE-LIEP-v1` الزام کرده است.

### Minimum fields

```
{
  "schema_version": "slpe-project-state.v1",
  "project_id": "switchland-price-engine",

  "frozen_contracts": {
    "master_specification": {},
    "implementation_architecture": {},
    "technology_implementation": {},
    "llm_execution_protocol": {},
    "legacy_migration": {}
  },

  "capabilities": {},

  "active_plan": null,
  "last_verified_plan": null,

  "blockers": [],

  "active_decision_escape": null,

  "next_authorized_plan": {}
}

```

هر frozen contract reference حداقل:

```
authority_id
version
repository_path
content_hash

```

را بعد از P001 نگه می‌دارد.

### Capability entry

```
{
  "implementation_status": "NOT_IMPLEMENTED",
  "verification_status": "NOT_TESTED",
  "evidence_refs": []
}

```

Implementation statuses:

```
NOT_IMPLEMENTED
IN_PROGRESS
IMPLEMENTED
FAILED
BLOCKED

```

Verification:

```
NOT_TESTED
VERIFIED
FAILED
INSUFFICIENT_EVIDENCE

```

### Invariants

1. `VERIFIED` فقط اگر implementation = `IMPLEMENTED`.
2. `IMPLEMENTED` به‌تنهایی Plan dependency را satisfied نمی‌کند؛ dependency باید در صورت نیاز `VERIFIED` باشد.
3. حداکثر یک `active_plan`.
4. `active_decision_escape != null` ⇒ `next_authorized_plan = null`.
5. Plan بعدی فقط از `next_authorized_plan` می‌آید.
6. Roadmap نمی‌تواند capability را VERIFIED اعلام کند.
7. PR/Handoff/chat status authority نیست.
8. Git HEAD داخل خود JSON ذخیره نمی‌شود؛ Git repository identity authority است.
9. `evidence_refs` باید به Plan closure/CI/fixture evidence واقعی اشاره کند، نه model claim.

### Update authority

- Plan branch: `IN_PROGRESS` می‌تواند ثبت شود.
- Plan closure: فقط بعد از Tests + Diff Gate + Inspector، capability به `VERIFIED` ارتقا می‌یابد.
- merge verified Plan: `main` تبدیل به آخرین proven state می‌شود.

---

## 13. Plan Storage / Closure Model

Canonical path:

```
plans/SLPE-P###-<slug>.md

```

Repair:

```
plans/SLPE-P###-R##-<slug>.md

```

هر فایل فقط دو بخش اصلی دارد:

```
# PLAN CONTRACT
...

# PLAN CLOSURE / HANDOFF
...

```

Plan Contract همان fields فریز‌شده LIEP را خواهد داشت.

Closure:

```
plan_id
outcome
scope_completed
implementation_reviewed_head
tests_executed
test_results
ci_evidence
inspector_verdict
unresolved_items
decision_escape_ref
owner_input_result

```

Plan file status authority نیست؛ `PROJECT_STATE` authority می‌ماند. LIEP همین one-file closure model را الزام کرده است.

---

## 14. Decision Escape Representation

مسیر:

```
plans/decision-escapes/SLPE-DE###-<slug>.md

```

حداقل:

```
escape_id
affected_frozen_decision
affected_contract
contradictory_evidence
reproducible_failed_gate
why_implementation_cannot_continue
smallest_decision_to_reopen
affected_plans
safe_interim_state

```

در `PROJECT_STATE` فقط:

```
"active_decision_escape": {
  "escape_id": "SLPE-DE001",
  "path": "plans/decision-escapes/SLPE-DE001-....md"
}

```

قرار می‌گیرد.

هیچ ADR system یا Decision database لازم نیست.

---

## 15. Rule Trace Matrix Specification

مسیر:

```
traceability/RULE_TRACE_MATRIX.json

```

هدف:

```
Master Rule
→ Runtime Rulebook entry
→ Python binding
→ tests

```

Minimum record:

```
{
  "rule_id": "PRC-008",
  "master": {
    "version": "1.2.0",
    "section": "PRC-008",
    "body_sha256": "..."
  },
  "runtime_rulebook": {
    "rule_id": "PRC-008",
    "version": "..."
  },
  "implementation_binding": "domain.pricing.composite",
  "test_ids": [
    "TEST-PRC-008-001",
    "ACC-007"
  ],
  "implementation_status": "IMPLEMENTED",
  "verification_status": "VERIFIED"
}

```

### مهم: عدم ایجاد status authority دوم

این statusها **Rule-level trace coverage** هستند.

آنها اجازه:

- Plan authorization؛
- project completion declaration؛
- capability status؛

نمی‌دهند.

`PROJECT_STATE` همچنان تنها project implementation-status authority است.

`ACC-001` نیز explicit implementation/test reference برای تمام mandatory rules می‌خواهد.

---

## 16. Python Runtime Rulebook Planning Boundary

محل:

```
authorities/runtime/rulebook/

```

این artifact:

> **Versioned operational projection under Master authority**

است.

### می‌تواند شامل شود

- rule identity/version؛
- Master rule/hash reference؛
- active/deprecated status؛
- policy IDs؛
- approved mappings؛
- deterministic constants؛
- issue/state registries؛
- exact precedence data؛
- implementation binding identifiers؛
- test/ACC references؛
- provenance.

### نباید شامل execution machinery عمومی شود

- prose execution؛
- arbitrary predicate DSL؛
- mutation DSL؛
- `eval` formula execution؛
- generated Python source؛
- LLM prompts/routes؛
- ACC runtime mutation.

Migration Contract همین separation را فریز کرده است.

### Plan ownership

**P003**:

- schema/boundary را finalize می‌کند؛
- legacy → candidate Runtime Rulebook transformation را می‌سازد؛
- initial bindings/trace structure را ایجاد می‌کند.

**P006–P011**:

- explicit deterministic handlers را implement/bind می‌کنند.

**P012**:

- full trace/ACC conformance را می‌بندد و release-valid Runtime Rulebook را اثبات می‌کند.

---

## 17. Test and Fixture Architecture

حداقل سه surface تست کافی است.

### A. Ordinary automated tests

مسیر:

```
tests/test_*.py

```

برای:

- pure domain rules؛
- source parsing؛
- repositories؛
- validators؛
- serializers؛
- Qt component behavior.

Separate `unit/` و `integration/` directory از ابتدا لازم نیست.

### B. Acceptance tests

```
tests/acceptance/

```

برای:

- `ACC-*`;
- deterministic replay؛
- historical regression؛
- end-to-end Golden flow؛
- Promotion/Rollback critical acceptance.

این directory distinct است چون release/conformance meaning متفاوت دارد.

### C. Fixtures

```
tests/fixtures/

```

زیرگروه‌های لازم فقط زمانی ایجاد شوند که Plan مربوطه برسد:

```
source/
business/
lifecycle/
golden/

```

### Failure coverage

| FamilyFailure caught |                                       |
| -------------------- | ------------------------------------- |
| Real PDF/DOCX        | parser/source drift                   |
| Corrupted source     | silent Code/Price corruption          |
| Business fixtures    | pricing/matching/BOM/policy errors    |
| Golden output        | semantic/byte regression              |
| Lifecycle            | promotion/partial/rollback corruption |
| Failure injection    | crash/atomic-state errors             |
| Qt smoke             | workflow/RTL/event-thread errors      |
| Package smoke        | missing runtime dependency            |

Self-confirmation ممنوع می‌ماند: Rulebook-derived behavior و expected value نمی‌توانند تنها منبع یک تست critical باشند. Migration Contract آن را صریحاً منع می‌کند.

---

## 18. CI / Validation Skeleton

**همه موارد این بخش** **`PROPOSED`** **هستند. هیچ CI فعلی در repo وجود ندارد.**

یک فایل:

```
.github/workflows/validate.yml

```

### Core PR gates

برای هر normal Plan PR:

```
locked environment validation
PROJECT_STATE validation
Plan starting-state/scope metadata validation
Ruff
mypy
full automated Python test suite
scope/diff allowlist check

```

### Windows packaged smoke

همان generic Workflow، job دوم که برای تغییرات این surfaces لازم می‌شود:

```
GUI
dependencies
entrypoint
document stack
packaging
release milestone

```

Evidence باید به exact candidate Head متصل باشد.

هیچ:

```
prompt-01.yml
prompt-02.yml
...

```

ساخته نمی‌شود.

LIEP نیز one generic workflow و selective Windows package smoke را الزام کرده است.

---

## 19. Capability Model

`PROJECT_STATE` فقط این 13 capability را track کند:

```
CAP-01 delivery_spine
CAP-02 verified_source_catalog
CAP-03 rule_migration_trace
CAP-04 state_repository_recovery
CAP-05 independent_acceptance_oracle
CAP-06 matching_bom_pricing
CAP-07 commercial_policy_state
CAP-08 deterministic_output_validation
CAP-09 guided_run_gui_approval
CAP-10 import_promotion
CAP-11 rollback_recovery
CAP-12 migration_acceptance
CAP-13 release_acceptance

```

299 Master Rule heading در اینجا replicate نمی‌شود.

آنها در `RULE_TRACE_MATRIX` پوشش داده می‌شوند.

---
