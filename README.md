# SwitchLand Price Engine

SwitchLand Price Engine v1 is a deterministic local Windows desktop application for turning an authoritative supplier price list into validated, auditable WooCommerce/WP All Import output without relying on LLM interpretation for production pricing.

**Repository continuity baseline:** `SLPE-REPOSITORY-CONTINUITY-BOOTSTRAP-v1`  
**Status:** `PRE_IMPLEMENTATION_CONTINUITY_BASELINE`

## Final v1 goal

The intended v1 will:

1. bind the authoritative supplier PDF and its manually derived DOCX extraction input;
2. deterministically extract and verify supplier data;
3. produce `Normalized_Source_Catalog.json`;
4. run deterministic matching, BOM, pricing, availability, discount, override, and Product Pricing State logic;
5. preserve the exact 95-column WooCommerce dataset and generate deterministic CSV + Audit XLSX;
6. guide Final Approval and manual WP All Import;
7. record Import Outcome;
8. promote state only after valid success;
9. block failed/partial promotion and support crash recovery;
10. perform rollback only as a new controlled Run.

## High-level architecture

`PySide6 / Qt Widgets` desktop GUI → application/run coordination → deterministic Domain Engine + source/validation/output boundaries → filesystem-centered versioned state.

The Domain Engine must not depend on PDF, DOCX, GUI, filesystem layout, or WP All Import.

### Operator-interface precedence note

`IAC-v1` contains an older `Guided CLI` / `GUI deferred` clause. The later frozen technology/planning authorities (`SLPE-TIC-v1` and `SLPE-PSS-001`) explicitly bind the v1 product to the PySide6 desktop GUI. Treat that as the approved interface supersession only; do not reopen unrelated `IAC-v1` boundaries.

## Authority hierarchy

1. `authorities/master/SLPE-MASTER-SPEC-001_v1.2.0.parts/MANIFEST.json` — entrypoint to the sole normative Product/Business authority. The exact canonical Master bytes are reconstructed from the ordered parts/literal separators and must match SHA-256 `4dcf348ef2b45225a14133f5c61a70861262edd01f698ae621b2df501290c846`.
2. `authorities/contracts/` — frozen architecture, technology, LLM execution, and legacy-migration authorities within their scopes.
3. `planning/PROJECT_SKELETON_SPECIFICATION.md` — frozen repository/planning structure (`SLPE-PSS-001 v1.0.0`).
4. `planning/MASTER_IMPLEMENTATION_ROADMAP.md` — frozen dependency/risk ordering (`SLPE-MIR-001 v1.0.0`).
5. `PROJECT_STATE.json` — **sole implementation-status authority**.
6. `plans/` — bounded work contracts and closure evidence; not project status authority.
7. `references/legacy_llm/` — historical/migration evidence only; never active Python authority.

Chat history, README text, and legacy LLM outputs are not Product/Business authority.

### Pre-P001 Master transport boundary

The current `SLPE-MASTER-SPEC-001_v1.2.0.parts/` + `MANIFEST.json` representation is **pre-P001 continuity transport only**. It does not amend `SLPE-PSS-001` or permanently replace its P001 canonical repository target. Before P001 closes, P001 must converge to the frozen target; if that target proves infeasible, the formal `DECISION_ESCAPE_REQUIRED` mechanism must be used rather than silently blessing the split transport as permanent.

## Current implementation status

At this continuity baseline, application implementation has **not started**. All capabilities in `PROJECT_STATE.json` remain `NOT_IMPLEMENTED` / `NOT_TESTED`.

- `active_plan`: `null`
- `last_verified_plan`: `null`
- next authorized implementation Plan: **`SLPE-P001 — Foundation / Delivery Spine Proof`**

This bootstrap is documentation/state continuity work and is **not** P001.

## 13-Plan path

| Plan | Outcome |
|---|---|
| P001 | Foundation / Delivery Spine Proof |
| P002 | Verified Source Catalog |
| P003 | Legacy Rule Migration & Trace Foundation |
| P004 | Current / Run Repository & Crash-Safe State Spine |
| P005 | Independent ACC / Golden Oracle Baseline |
| P006 | Deterministic Matching / BOM / Pricing Core |
| P007 | Commercial Policy & Product State Core |
| P008 | Exact Dataset / Validation / Output / Replay |
| P009 | Guided Run GUI & Final Approval |
| P010 | Import Outcome / Promotion / Partial Recovery |
| P011 | Rollback & Reconciliation Run |
| P012 | Full Migration / ACC / Regression Closure |
| P013 | Windows v1 Release Acceptance |

See `planning/MASTER_IMPLEMENTATION_ROADMAP.md` for dependencies, risks, evidence classes, and acceptance boundaries.

## Repository map

- `AGENTS.md` — fresh-agent operating entrypoint and read order.
- `PROJECT_STATE.json` — current implementation/verification truth and next authorized Plan.
- `authorities/` — active frozen authorities.
- `planning/` — frozen Skeleton and Roadmap.
- `plans/PLAN_TEMPLATE.md` — reusable Plan + Closure/Handoff contract.
- `prompts/PROMPT_SYSTEM.md` — Implementer → Inspector → Repair lifecycle.
- `prompts/templates/` — prompt shells only; no future Plan is pre-generated.
- `references/legacy_llm/` — legacy evidence identity/provenance, clearly non-authoritative for Python runtime.

## Resume After a Long Pause

1. Read `AGENTS.md`.
2. Read and validate `PROJECT_STATE.json`.
3. Verify the frozen authority identities and hashes recorded there. For the Master, reconstruct through its `MANIFEST.json` and verify the canonical aggregate hash.
4. Read the Skeleton and Roadmap.
5. Inspect the active Plan if one exists.
6. Inspect only code/tests/fixtures relevant to that Plan.
7. Report current repository HEAD, implementation/verification state, blockers, Decision Escape status, and next authorized Plan.
8. **Do not modify files until the next authorized work unit is explicitly confirmed.**

The owner should not be asked to choose Python patterns, review source code, interpret test logs, or arbitrate technical disagreements.
