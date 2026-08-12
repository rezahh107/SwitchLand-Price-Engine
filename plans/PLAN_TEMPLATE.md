# PLAN_TEMPLATE

Plan files are bounded work contracts plus closure/evidence records. They are **not** the project implementation-status authority. `PROJECT_STATE.json` remains authoritative for project status.

Normal identity: `SLPE-P###-<slug>`  
Bounded post-closure repair identity: `SLPE-P###-R##-<slug>`

# PLAN CONTRACT

## plan_id
`<required>`

## objective
<one primary capability/risk outcome>

## starting_commit
`<exact commit SHA at Plan authorization>`

## required_contracts
- `<authority id/version/path>`

## dependencies
- `<required VERIFIED capability/Plan>`

## in_scope
- `<bounded work>`

## non_goals
- `<explicit exclusions>`

## allowed_write_targets
- `<path or bounded glob>`

## required_outputs
- `<observable repository artifact>`

## required_tests
- `<test/fixture/build/static/package evidence that must actually execute>`

## acceptance_gates
- `<binary/observable closure conditions>`

## owner_input_if_required
`NONE | BUSINESS | UX | FINAL_APPROVAL`
<exact bounded question/acceptance if not NONE>

## decision_escape_conditions
- `<evidence that would require DECISION_ESCAPE_REQUIRED>`

# PLAN CLOSURE / HANDOFF

## plan_id
`<same Plan ID>`

## outcome
`PASS | REPAIR_REQUIRED | BLOCKED | INSUFFICIENT_EVIDENCE | FAILED`

## final_candidate_head
`<exact candidate commit SHA>`

## scope_completed
- `<what was actually completed>`

## actual_changed_files
- `<actual diff paths>`

## tests_executed
- `<exact commands/checks actually run>`

## test_results
- `<actual results; never model claims>`

## ci_evidence
`<exact-head CI reference or NOT_AVAILABLE>`

## scope_diff_result
`PASS | SCOPE_VIOLATION | INSUFFICIENT_EVIDENCE`

## inspector_verdict
`PASS | REPAIR_REQUIRED | BLOCKED | INSUFFICIENT_EVIDENCE | NOT_RUN`

## unresolved_items
- `<remaining item or NONE>`

## owner_input_result
`<actual bounded owner input/acceptance or NONE>`

## decision_escape_reference
`<path/ID or NONE>`

## final_closure_result
`VERIFIED | NOT_VERIFIED | BLOCKED`

`VERIFIED` is permitted only after required outputs/evidence, scope/diff, Independent Inspector, and any required owner acceptance satisfy `SLPE-LIEP-v1`. The Plan file itself does not authorize the next Plan.
