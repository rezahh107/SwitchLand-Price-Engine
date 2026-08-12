# Prompt System

This repository uses the frozen `SLPE-LIEP-v1` workflow. Prompt templates are shells that bind the active Plan and authorities; they do not duplicate policy text and they do not authorize work by themselves.

## Lifecycle

```text
Roadmap Plan Seed
        ↓
Full Plan Contract
        ↓
Implementer Prompt
        ↓
Implementation + executable evidence
        ↓
Scope/Diff Gate
        ↓
Fresh Independent Inspector Prompt
        ↓
PASS
or REPAIR_REQUIRED
        ↓
bounded Repair Prompt
        ↓
retest + fresh inspection
        ↓
Closure/Handoff
        ↓
PROJECT_STATE update
        ↓
next authorized Plan
```

## Implementer

The Implementer may modify only the active Plan's `allowed_write_targets`.

It must:

- bind the exact repository starting commit;
- load `PROJECT_STATE.json` and only relevant frozen authorities;
- verify dependencies and Plan freshness;
- implement only the Plan;
- create/update required tests and fixtures;
- run the executable evidence actually available;
- report failures honestly;
- never weaken acceptance merely to obtain green results;
- never mark itself VERIFIED.

## Independent Inspector

The Inspector is fresh and read-only.

It binds:

- repository;
- Plan starting commit;
- candidate head;
- active Plan Contract;
- frozen authorities;
- exact diff;
- actual test/build/CI evidence.

It checks frozen-decision preservation, scope, test/evidence completeness, regression risk, test weakening, unsupported claims, and architecture/technology drift.

Allowed verdicts only:

- `PASS`
- `REPAIR_REQUIRED`
- `BLOCKED`
- `INSUFFICIENT_EVIDENCE`

## Repair

Repair exists only after a concrete failure/finding.

A repair remains inside the same Plan only when the defect and write surfaces remain inside the existing Plan contract. It must preserve regression constraints and rerun affected gates. Material scope/interface expansion requires a new normal Plan; a frozen-decision conflict requires `DECISION_ESCAPE_REQUIRED`.

## Resolver

A fresh technical Resolver is exceptional only when Implementer and Inspector materially disagree and objective authority/evidence cannot resolve the dispute. The owner does not arbitrate the technical question.

## Authorization rule

A Handoff does not authorize the next Plan. Only `PROJECT_STATE.next_authorized_plan` does.

## Enforcement boundary

These templates are prompt-level controls. Deterministic enforcement begins only when the owning implementation Plan provides actual diff checks, validators, tests, CI, and Inspector evidence. The current continuity bootstrap does not claim those mechanisms are operational.
