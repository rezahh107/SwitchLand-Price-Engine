# IMPLEMENTER_PROMPT_TEMPLATE

Use this shell only after a full active Plan Contract exists and `PROJECT_STATE.next_authorized_plan` authorizes it.

## Bind
- Repository: `<owner/repo>`
- Starting commit: `<exact SHA>`
- Active Plan: `<path + plan_id>`
- `PROJECT_STATE.json`: `<exact path/state read>`
- Frozen authorities required by Plan: `<exact IDs/versions/paths/hashes>`
- Dependencies: `<required VERIFIED capabilities>`
- Allowed write targets: `<from Plan>`
- Non-goals: `<from Plan>`
- Required outputs/tests/acceptance gates: `<from Plan>`

## Execution rules
1. Verify actual HEAD equals `starting_commit`; otherwise return `STALE_PLAN`.
2. Validate current project state and dependencies before editing.
3. Modify only `allowed_write_targets`.
4. Do not reopen frozen decisions.
5. Implement the bounded objective and create/update required tests/fixtures.
6. Run required executable evidence; list exact commands/results.
7. Do not weaken tests/expected values to obtain green results unless explicitly authorized by the Plan and frozen authority.
8. Inspect the actual diff for scope.
9. Do not mark the Plan VERIFIED; prepare evidence for an Independent Inspector.
10. If a frozen decision blocks safe implementation, stop with `DECISION_ESCAPE_REQUIRED`.
