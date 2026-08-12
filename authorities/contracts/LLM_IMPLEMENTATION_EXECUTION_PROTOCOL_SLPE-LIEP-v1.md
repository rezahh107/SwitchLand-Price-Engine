LLM_IMPLEMENTATION_EXECUTION_PROTOCOL_CONTRACT
Contract ID: SLPE-LIEP-v1
Status: FROZEN_FOR_IMPLEMENTATION_PLANNING

1. CORE METHOD
   Use a Risk-First Vertical-Slice Plan Train.

   Risk-First determines WHAT dependency-ready capability is implemented next.
   The Plan Protocol determines HOW that capability is implemented,
   tested, inspected, repaired, accepted, and handed off.

2. FROZEN AUTHORITIES
   Ordinary implementation Plans MUST NOT modify or reinterpret:
   - latest SwitchLand Master Specification
   - IMPLEMENTATION_ARCHITECTURE_CONTRACT
   - TECHNOLOGY_IMPLEMENTATION_CONTRACT
   - this LLM_IMPLEMENTATION_EXECUTION_PROTOCOL_CONTRACT

   A frozen decision may be reopened only through DECISION_ESCAPE_REQUIRED.

3. CANONICAL IMPLEMENTATION STATE
   PROJECT_STATE.json is the sole implementation-status authority.

   Human-readable Plans, PRs, Handoffs, Roadmaps and chat responses
   MUST NOT become competing status registries.

4. PROJECT STATE VOCABULARY
   Implementation:
   - NOT_IMPLEMENTED
   - IN_PROGRESS
   - IMPLEMENTED
   - FAILED
   - BLOCKED

   Verification:
   - NOT_TESTED
   - VERIFIED
   - FAILED
   - INSUFFICIENT_EVIDENCE

   IMPLEMENTED MUST NOT imply VERIFIED.

5. PLAN IDENTITY
   Normal Plans:
   SLPE-P###-<short-slug>

   Post-closure bounded repairs:
   SLPE-P###-R##-<short-slug>

6. PLAN UNIT
   One Plan MUST own one primary capability/risk outcome.
   It MUST end in a coherent repository state.
   It MUST NOT intentionally leave unrelated half-implementations.

   Split the Plan before execution when it contains multiple independent
   outcomes, risks, or unrelated write surfaces.

7. PLAN CONTRACT
   Every Plan MUST define:
   - plan_id
   - objective
   - starting_commit
   - required_contracts
   - dependencies
   - in_scope
   - non_goals
   - allowed_write_targets
   - required_outputs
   - required_tests
   - acceptance_gates
   - owner_input_if_required
   - decision_escape_conditions

8. STARTING-STATE VERIFICATION
   Before implementation:
   - resolve actual repository HEAD;
   - require equality with Plan starting_commit;
   - validate PROJECT_STATE;
   - verify dependency capabilities are VERIFIED as required;
   - verify required frozen contract versions.

   Mismatch makes the Plan STALE.
   A stale Plan MUST NOT execute until refreshed/rebased/reissued.

9. WRITE-SCOPE ENFORCEMENT
   Plan prose alone is not enforcement.

   At completion compare:
   git diff --name-status <starting_commit>...<candidate_head>

   against allowed_write_targets.

   Any unauthorized path is SCOPE_VIOLATION and blocks Plan closure.

   Frozen authority files are read-only except in an explicitly authorized
   Decision-Escape follow-up task.

10. IMPLEMENTATION AGENT
    One Implementer LLM owns each normal Plan.

    It MUST:
    - read only required project context;
    - implement within Plan scope;
    - create/update required tests and fixtures;
    - run all executable evidence available to it;
    - report failures honestly;
    - never change acceptance criteria to make work pass;
    - never ask the nontechnical owner to select technical patterns.

11. TEST METHOD
    Acceptance cases are defined before implementation.

    Code and tests may be implemented together.

    Regression-first is mandatory when the Plan repairs or changes:
    - deterministic business logic
    - serialization
    - source corruption behavior
    - promotion/recovery
    - previously failing behavior that can be reproduced by fixture/test.

12. EXISTING TEST PROTECTION
    Existing correctness tests MUST NOT be removed, weakened, skipped,
    or have expected values changed merely to obtain green results.

    Such changes require explicit Plan scope and must remain consistent
    with frozen authorities.

13. EVIDENCE
    Model claims are not completion evidence.

    Valid evidence includes:
    - executed tests
    - fixtures/golden comparisons
    - static checks
    - builds
    - Windows package smoke
    - exact artifact inspection
    - exact-head CI
    - bounded owner visual/business acceptance where genuinely required

    Missing required evidence => INSUFFICIENT_EVIDENCE.

14. CI
    Use one generic repository validation workflow.

    Every normal Plan PR runs the full automated core suite.

    Windows packaged smoke is additionally required for Plans affecting:
    - GUI
    - dependencies
    - packaging
    - runtime document-processing stack
    - application entrypoint
    and for release milestones.

    Do not create Prompt-specific duplicate CI workflows.

15. GIT
    Each normal Plan uses one branch and one PR.

    Branch:
    plan/SLPE-P###-<slug>

    Direct-to-main implementation is prohibited.

    After verification, prefer one squash merge per Plan.

    Git/PR history is evidence; the owner is not required to review diffs.

16. INDEPENDENT INSPECTOR
    Every normal implementation Plan requires one fresh LLM Inspector
    before closure.

    Inspector is read-only.

    Inspector checks:
    - frozen decision preservation
    - exact diff and scope
    - test/evidence completeness
    - regression risk
    - test weakening
    - unsupported completion claims
    - architecture/technology drift

    Inspector verdict:
    - PASS
    - REPAIR_REQUIRED
    - BLOCKED
    - INSUFFICIENT_EVIDENCE

17. REPAIR
    Scope-contained repair before Plan closure stays inside the same Plan.

    After any code repair:
    - affected gates rerun;
    - required regression gates rerun;
    - the final candidate head is reinspected.

    Tiny post-closure correctness repair:
    SLPE-P###-R##

    Material scope/interface expansion requires a new normal Plan.

18. REVIEW DISAGREEMENT
    The human owner MUST NOT arbitrate technical disagreement.

    If Implementer and Inspector materially disagree and objective evidence
    does not resolve it, invoke one fresh technical Resolver.

    Resolver is exceptional, not a permanent third agent.

19. DECISION ESCAPE
    If frozen architecture/technology/specification prevents a safe
    implementation, stop and return:

    DECISION_ESCAPE_REQUIRED

    with:
    - frozen decision
    - contradictory evidence
    - reproducible failing gate
    - why implementation cannot safely continue
    - smallest decision to reopen
    - affected Plans/capabilities

    The current Plan becomes BLOCKED.
    No silent workaround is allowed.

20. PLAN CLOSURE / HANDOFF
    Each Plan file contains one Closure/Handoff section.

    Minimum closure fields:
    - plan_id
    - outcome
    - scope_completed
    - implementation_reviewed_head
    - tests_executed
    - test_results
    - CI evidence
    - Inspector verdict
    - unresolved items
    - decision_escape reference if any
    - owner input/result if any

    Do not create a second implementation-status ledger.

21. PROJECT STATE UPDATE
    PROJECT_STATE may advance a capability to VERIFIED only after:
    - required outputs exist;
    - required tests/evidence pass;
    - scope diff passes;
    - independent Inspector passes;
    - required owner business/visual acceptance, if any, is complete.

22. NEXT-PLAN AUTHORIZATION
    The next Plan is not authorized by an Implementer Handoff.

    PROJECT_STATE.next_authorized_plan is the authority.

    Only one normal implementation Plan is active at a time.

23. ORDERING
    Among dependency-ready work, select the capability with the highest:
    - silent correctness risk;
    - integration uncertainty;
    - irreversible/recovery risk;
    - technology/package uncertainty.

    Prefer a thin end-to-end slice that proves the risky boundary.

24. FIRST MILESTONE
    First milestone is a Foundation / Delivery Spine Proof.

    It must prove:
    - repository/project-state protocol
    - test/CI loop
    - Windows GUI build/launch
    - Persian RTL/LTR smoke
    - filesystem workspace
    - responsive same-process task smoke
    - Windows packaging smoke

    It must not yet implement the full Price Engine.

25. FIXTURE-DRIVEN AREAS
    Fixtures/golden tests are required especially for:
    - PDF/DOCX source pipeline
    - Canonical Source IR
    - matching
    - BOM/composite pricing
    - money/discount
    - exact CSV
    - Product Pricing State
    - promotion/partial/crash behavior
    - rollback
    - deterministic replay

26. OWNER BOUNDARY
    The owner may be asked only for:
    - business decisions
    - source artifacts
    - understandable visible UX feedback
    - explicit business/final acceptance where specified
    - authorization to proceed where desired

    The owner MUST NOT be asked to:
    - choose Python patterns
    - review code
    - resolve merge conflicts
    - interpret tests/tracebacks
    - arbitrate Implementer/Inspector disputes

    Human approval is never labeled technical verification.

27. RECOVERY
    Verified main is the last proven project state.

    A failed Plan remains isolated on its branch.
    The project MUST NOT reconstruct technical truth from chat history.

28. CONTEXT POLICY FOR FRESH AGENTS
    A fresh agent should normally need only:
    - PROJECT_STATE.json
    - relevant frozen contracts
    - active Plan
    - directly affected source/tests/fixtures
    - latest Plan closure only if needed

    Reading all historical chats or all old Plans is not required.

29. PROHIBITED PROCESS COMPLEXITY
    Do not introduce:
    - multiple status ledgers
    - generic multi-agent orchestration platforms
    - permanent Test Agent
    - enterprise GitFlow
    - Scrum/RACI/release-train ceremony
    - security review bureaucracy
    - Prompt-specific duplicate CI
    - mandatory technical owner review
    - vector-memory systems
    - autonomous unbounded agent loops
    - handoff-document proliferation
    - ADRs for ordinary implementation details

END CONTRACT
