# AGENTS.md — Fresh-Agent Operating Entry Point

This repository is designed to be resumed by a capable LLM without historical chat context.

## Required read order

1. `README.md`
2. `PROJECT_STATE.json`
3. the exact frozen authorities referenced by `PROJECT_STATE.json`
4. `planning/PROJECT_SKELETON_SPECIFICATION.md`
5. `planning/MASTER_IMPLEMENTATION_ROADMAP.md`
6. the active Plan file, if `active_plan` is not null
7. the latest relevant Plan Closure/Handoff, if needed
8. only then the source/tests/fixtures directly relevant to the active Plan

Do not read old chats to reconstruct project truth.

## Authority model

- **Product/Business semantics:** `SLPE-MASTER-SPEC-001 v1.2.0`. Repository entrypoint: `authorities/master/SLPE-MASTER-SPEC-001_v1.2.0.parts/MANIFEST.json`. It defines an exact ordered byte reconstruction; verify its aggregate SHA-256 before relying on it. Individual part files are not separate authorities.
- **Architecture/technology/development/migration decisions:** the frozen contracts under `authorities/contracts/`
- **Repository skeleton:** `SLPE-PSS-001`
- **Roadmap ordering:** `SLPE-MIR-001`
- **Implementation status:** `PROJECT_STATE.json` only
- **Plan scope/evidence:** active Plan file
- **Legacy LLM materials:** migration/history reference only

README, AGENTS, PR prose, Handoffs, and chat responses are explanatory/evidence carriers, not competing implementation-status authorities.

## Scope vocabulary

- **FROZEN** — five frozen authorities + `SLPE-PSS-001` + `SLPE-MIR-001`; ordinary Plans do not rewrite them.
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
10. Legacy LLM Rulebook/outputs are not the active Python Runtime Rulebook and are never Golden truth by themselves.
11. Material business truth ultimately traces to the Master Specification and approved evidence.
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

The continuity bootstrap is not `SLPE-P001`. Unless `PROJECT_STATE.json` later proves otherwise, all implementation capabilities remain unimplemented and unverified. P001 is the next authorized implementation Plan, not an active Plan.
