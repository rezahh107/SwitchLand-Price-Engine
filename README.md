# SwitchLand Price Engine

SwitchLand Price Engine v1 is a deterministic local Windows desktop application for turning an authoritative supplier price list into validated, auditable WooCommerce/WP All Import output without relying on LLM interpretation for production pricing.

**Repository continuity baseline:** `SLPE-REPOSITORY-CONTINUITY-BOOTSTRAP-v1` + resolved `SLPE-DE001`  
**Status:** `PRE_IMPLEMENTATION_CONTINUITY_BASELINE_REBASED_20260921`

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

1. `references/live_workspace/LIVE_AUTHORITY_BINDINGS_20260921.json` (exact live Drive resource/hash binding) — current repository Product/Business authority after resolved `SLPE-DE001`; exact SHA-256 `6d32566bf9c66664d46a2c4012f932e315b0276d8584bbdbbc2658fcdacf9949`.
2. `authorities/contracts/` — frozen architecture, technology, LLM execution, and legacy-migration authorities within their scopes, qualified only by explicit resolved Decision Escape(s) in `PROJECT_STATE.json`.
3. `planning/PROJECT_SKELETON_SPECIFICATION.md` — frozen repository/planning structure (`SLPE-PSS-001 v1.0.0`).
4. `planning/MASTER_IMPLEMENTATION_ROADMAP.md` — frozen dependency/risk ordering (`SLPE-MIR-001 v1.0.0`).
5. `PROJECT_STATE.json` — **sole implementation-status authority**.
6. `plans/` — bounded work contracts, Decision Escapes and closure evidence; not a competing project-status authority.
7. `references/live_workspace/` — exact identity/hash live Workspace bindings and migration inputs; actual live runtime authority is always resolved fresh through Workspace Manifest → `CURRENT_SET`.
8. `references/legacy_llm/` — historical/migration evidence only; never active Python authority.

Chat history, README text, and legacy/live LLM outputs are not independent Product/Business authority.

### Pre-P001 Master transport boundary

The historical `SLPE-MASTER-SPEC-001_v1.2.0.parts/` + `MANIFEST.json` representation remains preserved as continuity history. `SLPE-DE001` rebaselines the current active Product/Business authority to exact Master v1.4.0. It does not rewrite planning history or silently alter unrelated frozen architecture/technology decisions.

## Root-cause hardening synchronized on 2026-09-21

The governed live audit resolved Decisions 001–012. Their exact business outcomes are already permanent in live Rulebook v1.8.0 and preserved unchanged in v1.9.0. `OD-050` adds only import-execution safety controls: transient SKU transition preflight/order, no fabricated temporary SKU, selective-hash/skip guard, independent Production read-back before Promotion, Import Contract drift blocking, and exact 95-column least-mutation corrective execution.

The persistent WP All Import template configuration is **not changed** by this sync; Import Contract v1.2.0 preserves `is_update_sku=0` and all existing mappings/update flags. Current Master, Product Pricing State, WooCommerce Production, schema, row order, and pricing formulas are not changed by this governance synchronization.

These live governance controls become Python implementation/test obligations in their owning future Plans (P003/P006/P008/P010). They are not marked implemented merely because the live Workspace knows them.

## Current implementation status

At this continuity baseline, application implementation has **not started**. All capabilities in `PROJECT_STATE.json` remain `NOT_IMPLEMENTED` / `NOT_TESTED`.

- `active_plan`: `null`
- `last_verified_plan`: `null`
- next authorized implementation Plan: **`SLPE-P001 — Foundation / Delivery Spine Proof`**

This rebaseline is documentation/authority continuity work and is **not** P001.

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
- `plans/decision-escapes/` — explicit frozen-decision reopening evidence.
- `plans/PLAN_TEMPLATE.md` — reusable Plan + Closure/Handoff contract.
- `prompts/PROMPT_SYSTEM.md` — Implementer → Inspector → Repair lifecycle.
- `prompts/templates/` — prompt shells only; no future Plan is pre-generated.
- `references/live_workspace/` — hash-bound live Workspace authority bindings/migration inputs.
- `references/legacy_llm/` — legacy evidence identity/provenance, clearly non-authoritative for Python runtime.

## Resume After a Long Pause

1. Read `AGENTS.md`.
2. Read and validate `PROJECT_STATE.json`.
3. Verify the frozen authority identities and hashes recorded there.
4. Read any resolved Decision Escape referenced there.
5. Read the Skeleton and Roadmap.
6. Inspect the active Plan if one exists.
7. Inspect only code/tests/fixtures relevant to that Plan.
8. For live operations, fresh-read Workspace Manifest → `CURRENT_SET`; never infer live state from repository staleness or chat memory.
9. Report current repository HEAD, implementation/verification state, blockers, Decision Escape status, and next authorized Plan.
10. **Do not modify files until the next authorized work unit is explicitly confirmed.**

The owner should not be asked to choose Python patterns, review source code, interpret test logs, or arbitrate technical disagreements.
