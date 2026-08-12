LEGACY_LLM_TO_PYTHON_MIGRATION_CONTRACT
Contract ID: SLPE-LLM-PY-MIGRATION-v1
Status: FROZEN_FOR_PROJECT_PLANNING

1. CANONICAL NORMATIVE AUTHORITY

   SLPE-MASTER-SPEC-001 v1.2.0 is the sole normative
   authority for product/business semantics.

   Neither legacy Project Instructions, legacy Rulebook,
   legacy LLM output, Python code, nor tests may override it.

2. LEGACY PACKAGE

   SwitchLand_Price_Engine_GPT_Project_Package_v1.1.3
   is immutable migration/history evidence.

   Preserve its exact artifact hash and internal file hashes.

   It is not an active Python runtime package.

3. LEGACY RULEBOOK CLASSIFICATION

   SWITCHLAND_PRICE_ENGINE_RULEBOOK_v1.1.3.json is:

   AUDITED_MIGRATION_AND_TRACEABILITY_SOURCE

   It is not:
   - direct Python executable authority;
   - Golden expected-value authority;
   - production behavioral oracle.

4. LEGACY RULEBOOK VALUE TO PRESERVE

   Preserve/reuse where validated:
   - rule_id
   - Master section identity
   - source_body_sha256
   - family/domain classification
   - rule version/provenance
   - issue-code metadata
   - state enums
   - policy IDs
   - approved mappings
   - explicit precedence
   - typed triggers/branches as test/trace inputs
   - ACC mapping

5. LEGACY RULEBOOK CONTENT NOT DIRECTLY EXECUTED

   Do not dynamically interpret:
   - prose steps
   - normative_clauses
   - conversational routes
   - LLM closure semantics
   - arbitrary formulas through eval
   - generic predicate/mutation DSL

6. PYTHON RUNTIME RULEBOOK

   Python v1 must retain a versioned Runtime Rulebook
   because the frozen design and Master require versioned
   operational Rulebook authority.

   The production Runtime Rulebook must be a new
   Python-oriented projection derived under Master authority.

   Legacy v1.1.3 must not be overwritten.

   If the Runtime Rulebook contract is incompatible with
   v1.1.3, issue a new MAJOR Rulebook version according
   to RBK-001 rather than pretending compatibility.

7. PYTHON EXECUTION BINDING

   Each material active runtime Rule must map to:

   Master Rule ID
   → Runtime Rulebook entry
   → explicit deterministic Python implementation binding
   → tests/fixtures

   Python business handlers are explicit code.

   Do not build a generic Rule DSL engine solely to execute
   legacy Rulebook records.

8. DIRECT DECLARATIVE RUNTIME DATA

   The Python Runtime Rulebook may directly provide only
   data whose semantics are closed and deterministic, such as:

   - activation/version metadata
   - approved mappings
   - policy IDs/constants
   - state/issue registries
   - exact precedence tables
   - other explicitly validated declarative configuration

   Business algorithms remain deterministic Python code.

9. MASTER/RULEBOOK CONFLICT

   If Master and any Rulebook materially conflict:

   - Master wins normatively;
   - affected runtime decision is not silently executed;
   - affected Rulebook must be corrected/versioned;
   - required tests must be rerun.

10. PROJECT INSTRUCTIONS

    02_PROJECT_INSTRUCTIONS.md is legacy LLM orchestration
    evidence, not Python product authority.

    Classify its content as:
    - Master-backed product semantics → use Master instead;
    - useful UX/workflow concept → transform to GUI requirements;
    - LLM orchestration → retire;
    - validation/evidence concept → migrate only if independently
      required by frozen contracts/Master;
    - unsupported feature → defer.

    Exact prompt wording must not enter Domain logic.

11. OUTPUT_AUDIT

    Legacy OUTPUT_AUDIT mode has no confirmed Master authority
    in the inspected v1.2.0 Specification.

    It must not automatically enter Python v1.

    It remains deferred/reference-only unless separately approved.

12. LEGACY VERSION DRIFT

    The supplied v1.1.3 package contains Project Instructions
    referring to Rulebook v1.1.2.

    Therefore Project Instructions cannot be treated as
    reliable active authority without version validation.

13. TEST SOURCE HIERARCHY

    Test inventory may be seeded by legacy Rulebook structure.

    Expected truth must come from, in order:

    1. explicit Master normative semantics;
    2. independently Master-derived verified fixture;
    3. approved business mapping/decision evidence;
    4. verified historical correct output;
    5. audited Rulebook candidate expectation;
    6. legacy LLM result as differential signal only.

14. SELF-CONFIRMATION PROHIBITION

    A Rulebook record may not simultaneously be the unverified
    source of:

    - Python behavior;
    - expected test value;

    for a correctness-critical case.

    At least one independent Master/evidence-derived oracle is
    required for material Rule behavior.

15. ACC HANDLING

    ACC-001..ACC-035 are build/acceptance controls.

    They must become:
    - acceptance tests;
    - integration tests;
    - replay/golden tests;
    - release gates where applicable.

    They must never become live mutation/pricing Rules.

16. RULE TRACE MATRIX

    Maintain one lightweight Rule Trace Matrix.

    Minimum fields:
    - rule_id
    - Master version/section/body hash
    - active Runtime Rulebook identity
    - Python implementation binding
    - test IDs
    - implementation status
    - verification status

    No enterprise requirements platform is required.

17. RULE COVERAGE

    All material Master Rules must be classified as one of:

    - runtime implemented
    - governance/control implemented
    - build-only ACC
    - not implemented
    - blocked
    - not applicable with authority

    No mandatory Rule may silently disappear.

18. GOLDEN FIXTURE POLICY

    Legacy LLM output is never Golden by itself.

    Historical outputs are regression evidence, not automatically
    normative truth.

    Known historical bugs must not become expected behavior.

19. LEGACY LLM DIFFERENTIAL POLICY

    Legacy LLM runtime status:

    KEEP_TEMPORARILY

    Permitted use:
    read-only differential signal on selected migration fixtures.

    Prohibited use:
    - production pricing authority
    - test oracle
    - Promotion authority
    - Rulebook override
    - acceptance truth

20. DIFFERENTIAL DISAGREEMENT

    If Python and legacy LLM disagree:

    do not vote;
    do not average;
    do not prefer legacy behavior.

    Adjudicate using:
    Master Specification
    + approved authoritative evidence
    + independently validated fixtures.

21. LLM RETIREMENT

    Remove active differential LLM use after:

    - material Rule Trace coverage is complete;
    - applicable ACC suite passes;
    - deterministic replay passes;
    - historical regression passes;
    - end-to-end Golden workflow passes;
    - no unresolved migration discrepancy remains.

    Preserve the legacy package only as historical reference.

22. LEGACY ARTIFACT PLACEMENT

    Conceptually:

    authorities/
      Master Specification
      active Python Runtime Rulebook

    references/legacy_llm/
      original v1.1.3 package or immutable extracted references
      Project Instructions
      legacy Rulebook

    The legacy Rulebook must not be stored where it can be
    mistaken for the active production Rulebook.

23. VERSION CHANGE PROTOCOL

    Business-policy change:
      Master version
      → Runtime Rulebook version
      → Python implementation
      → tests.

    Approved mapping/config change allowed by Master:
      new Runtime Rulebook version
      → provenance/approval
      → affected tests.

    Python implementation-only correction:
      code/test revision without semantic authority change.

    Test expected-value changes require explicit authority/evidence.

24. EVIDENCE VOCABULARY

    Use:
    CONFIRMED
    DERIVED
    PROPOSED
    NOT_PROVEN
    CONTRADICTED

    Build test success must not be promoted into production proof.

25. PROHIBITED MIGRATION SHORTCUTS

    Do not:
    - execute legacy prose dynamically;
    - auto-generate all Python business logic from Rulebook;
    - auto-generate implementation and expected tests from the
      same unverified Rulebook record;
    - use LLM output as Golden truth;
    - maintain two permanent pricing engines;
    - create generic DSL/compiler infrastructure solely for legacy reuse;
    - create duplicate Master authority;
    - allow Project Instructions to override Master;
    - silently promote legacy extra features into Python v1.

26. DECISION ESCAPE

    If migration reveals a verified contradiction with a frozen
    architecture/technology/authority contract, do not silently
    modify the frozen decision.

    Return:

    DECISION_ESCAPE_REQUIRED

    with:
    - affected frozen decision
    - exact contradictory evidence
    - smallest required reopening
    - affected migration/implementation work.

END CONTRACT
