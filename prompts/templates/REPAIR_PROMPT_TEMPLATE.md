# REPAIR_PROMPT_TEMPLATE

Use only after `REPAIR_REQUIRED` or another concrete Plan-local failure.

## Bind
- Repository: `<owner/repo>`
- Same Plan: `<plan_id + path>`
- Current candidate head: `<exact SHA>`
- Inspector findings: `<exact findings>`
- Allowed repair scope: `<subset of existing Plan scope/write targets>`
- Frozen authorities: `<required IDs/versions>`
- Regression constraints: `<tests/fixtures that may not be weakened>`
- Gates to rerun: `<affected + required regression gates>`

## Rules
1. Repair only the confirmed defect.
2. Do not broaden scope, interfaces, architecture, technology, or business semantics.
3. Do not weaken tests or expected values to make the repair pass.
4. Rerun all affected gates and required regressions.
5. Produce a new candidate head for fresh Independent Inspector review.
6. If repair needs new write surfaces/material scope, stop and request a new normal Plan.
7. If repair requires changing a frozen decision, stop with `DECISION_ESCAPE_REQUIRED`.
