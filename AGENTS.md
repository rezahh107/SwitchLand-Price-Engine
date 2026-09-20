# AGENTS.md — Fresh-Agent Operating Entry Point

This repository is designed to be resumed by a capable LLM without historical chat context.

## Required read order

1. `README.md`
2. `PROJECT_STATE.json`
3. the exact frozen authorities referenced by `PROJECT_STATE.json`
4. the resolved Decision Escape referenced by `PROJECT_STATE.json`, when present
5. `planning/PROJECT_SKELETON_SPECIFICATION.md`
6. `planning/MASTER_IMPLEMENTATION_ROADMAP.md`
7. the active Plan file, if `active_plan` is not null
8. the latest relevant Plan Closure/Handoff, if needed
9. only then the source/tests/fixtures directly relevant to the active Plan

Do not read old chats to reconstruct project truth.

## Authority model

- **Product/Business semantics:** the exact `SLPE-MASTER-SPEC-001` selected by `PROJECT_STATE.json`. After resolved `SLPE-DE001`, the current repository baseline is v1.4.0 via `references/live_workspace/LIVE_AUTHORITY_BINDINGS_20260921.json` (exact live Drive resource/hash binding), SHA-256 `6d32566bf9c66664d46a2c4012f932e315b0276d8584bbdbbc2658fcdacf9949`.
- **Live Workspace runtime governance:** resolve fresh from Workspace Manifest → `CURRENT_SET`. Exact live Rulebook/Import Contract bindings are recorded in `PROJECT_STATE.json` / `references/live_workspace/` as migration/continuity inputs until their owning Python implementation Plans execute.
- **Architecture/technology/development/migration decisions:** the frozen contracts under `authorities/contracts/`, qualified only by an explicit resolved Decision Escape recorded in `PROJECT_STATE.json`.
- **Repository skeleton:** `SLPE-PSS-001`
- **Roadmap ordering:** `SLPE-MIR-001`
- **Implementation status:** `PROJECT_STATE.json` only
- **Plan scope/evidence:** active Plan file
- **Legacy LLM materials:** migration/history reference only

README, AGENTS, PR prose, Handoffs, and chat responses are explanatory/evidence carriers, not competing implementation-status authorities.

## Pre-P001 Master transport boundary

The historical `SLPE-MASTER-SPEC-001_v1.2.0.parts/` + `MANIFEST.json` representation remains preserved as pre-P001 continuity history. Resolved `SLPE-DE001` rebaselines the current active Master binding to exact v1.4.0 identity/version/hash in the live Workspace without rewriting that history. P001 must converge the repository to its then-current frozen canonical payload target before closure; if a frozen target proves infeasible, use `DECISION_ESCAPE_REQUIRED` rather than silently substituting another representation.

## Scope vocabulary

- **FROZEN** — current Master selected by `PROJECT_STATE.json` + five frozen contracts + `SLPE-PSS-001` + `SLPE-MIR-001`, as qualified by explicit resolved Decision Escape(s); ordinary Plans do not rewrite them.
- **CURRENT** — `PROJECT_STATE.json`.
- **NEXT** — `PROJECT_STATE.next_authorized_plan`.
- **DEFERRED** — later Roadmap Plans and explicit `NOT_PROVEN` items.
- **PROHIBITED** — work outside the active Plan; silent frozen-decision changes; generic Rule DSL/interpreter; permanent LLM runtime; architecture expansion without Decision Escape.

## Operating rules

1. Never reconstruct project state from conversation memory.
2. Never treat README/AGENTS as higher authority than frozen authorities.
3. Never silently reopen a frozen decision.
4. Never start any Plan except `PROJECT_STATE.next_authorized_plan`.
5. `IMPLEMENTED != VERIFIED`.
6. Never modify paths outside the active Plan's `allowed_write_targets`.
7. Prompt wording is not enforcement; future scope closure requires actual diff/test/CI/Inspector evidence according to `SLPE-LIEP-v1`.
8. If repository evidence conflicts with `PROJECT_STATE` or frozen docs, stop and report the conflict.
9. If a frozen decision prevents safe implementation, return `DECISION_ESCAPE_REQUIRED`; do not work around it silently.
10. Legacy/live LLM Rulebook outputs are not automatically the active Python Runtime Rulebook and are never Golden truth by themselves.
11. Material business truth ultimately traces to the active Master Specification and approved evidence.
12. The owner is not a technical arbitrator and must not be asked to choose implementation patterns, resolve merge conflicts, or interpret stack traces.
13. An Implementer cannot mark its own work VERIFIED.
14. The Independent Inspector is read-only.
15. Only `PROJECT_STATE.next_authorized_plan` authorizes the next normal Plan.

## Interface supersession note

`IAC-v1` contains an older Guided-CLI/deferred-GUI clause. The later frozen `SLPE-TIC-v1` and `SLPE-PSS-001` bind v1 to a PySide6/Qt Widgets desktop GUI. Apply that approved supersession only to the operator-interface choice; preserve all other IAC boundaries unless a formal Decision Escape says otherwise.

## When the owner says: "Inspect the repository and tell me where we are."

Do **not** begin implementation.

First return a concise recovery report containing:

- repository HEAD inspected;
- current capability implementation/verification state;
- active Plan;
- last verified Plan;
- next authorized Plan;
- blockers;
- active Decision Escape, if any;
- required owner input, if any;
- exact recommended next action.

If the repository has moved since a Plan was authored, treat the Plan as stale according to `SLPE-LIEP-v1`; do not execute it blindly.

## Plan / prompt lifecycle

`Roadmap seed → Full Plan Contract → Implementer → executable evidence → scope/diff gate → fresh Independent Inspector → bounded Repair if required → retest/reinspect → Closure/Handoff → PROJECT_STATE update → next authorized Plan`

See `prompts/PROMPT_SYSTEM.md` for the reusable prompt roles.

## Current pre-implementation condition

The continuity bootstrap/rebaseline is not `SLPE-P001`. Unless `PROJECT_STATE.json` later proves otherwise, all implementation capabilities remain unimplemented and unverified. P001 is the next authorized implementation Plan, not an active Plan. The 2026-09-21 sync updates authority truth and future implementation obligations only.
