IMPLEMENTATION_ARCHITECTURE_CONTRACT
Version: IAC-v1
Status: FROZEN_FOR_TECHNOLOGY_SELECTION

1. APPLICATION TOPOLOGY
   - One local application.
   - One process.
   - No server.
   - No API boundary.
   - No background workers or queues.

2. EXECUTION MODEL
   - Synchronous guided Run execution.
   - Run may pause/exit at human/manual boundaries.
   - Run must be resumable from durable Run state.

3. OPERATOR INTERFACE
   - Guided CLI for v1.
   - Must support intake, blocker resolution, decisions,
     final approval, import outcome and rollback initiation.

4. SOURCE AUTHORITY
   - Supplier PDF = AUTHORITATIVE_SUPPLIER_SOURCE.
   - Manually converted DOCX = DERIVED_EXTRACTION_INPUT.
   - Both bound to exact Run by identity/hash.

5. SOURCE PIPELINE
   - deterministic DOCX extraction;
   - lightweight deterministic PDF evidence verification;
   - fail closed on material mismatch/unresolved;
   - human resolution only where deterministic resolution is absent.

6. CANONICAL SOURCE BOUNDARY
   - Source Pipeline outputs Normalized_Source_Catalog.json.
   - Downstream Domain Core must never reread/reinterpret PDF/DOCX.
   - Canonical source must be hashable, replayable and independently testable.

7. APPLICATION STRUCTURE
   Logical modules:
   a. Run Coordinator
   b. Source Pipeline
   c. Repositories
   d. Deterministic Domain Engine
   e. Validation Engine
   f. Output & Audit
   g. Approval / Import / Promotion / Recovery

8. DEPENDENCY DIRECTION
   Documents/UI/Persistence must not leak into Domain Engine.
   Domain Engine receives immutable structured snapshots.
   Domain Engine returns candidate results/proposed state only.
   Side effects are owned outside Domain Engine.

9. STATE MODEL
   Separate:
   - Configuration/Rule State
   - Persistent Product Commercial State
   - Run State
   - Current Master/Source baseline state
   - Human Decisions
   - Historical artifacts

10. PERSISTENCE ARCHITECTURE
    - FILESYSTEM_CENTERED.
    - Versioned immutable artifacts for Master, Product State,
      Rulebook, source/output snapshots.
    - Run Manifest mutable only until close, written crash-safely.
    - No embedded database required in v1.

11. CURRENT STATE MODEL
    - One authoritative Current-State commit descriptor/pointer.
    - It identifies Current Master, committed Product Pricing State,
      source baseline/chronology and committed/promoted Run.
    - Separate current files may not be independently mutated.

12. PROMOTION ATOMICITY
    - All target versioned artifacts must be completely written,
      hashed and validated before Current mutation.
    - Promotion has one atomic Current commit point.
    - Before that point old Current remains authoritative.
    - After that point new commit descriptor is authoritative.
    - Interrupted promotion must be deterministically recoverable.

13. COMMERCIAL STATE
    - Runtime Product Pricing State changes are staged in Proposed State.
    - They become committed only after valid Import Success + Promotion.
    - Failed/Partial/Rejected/Superseded/Blocked Runs cannot commit them.

14. RUN LIFECYCLE
    - Use authoritative Master Specification run states.
    - DRAFT
      → VALIDATED
      → AWAITING_APPROVAL
      → IMPORT_READY
      → IMPORT_SUCCESS
      or approved failure/no-change paths.
    - No parallel unofficial lifecycle.

15. FINAL APPROVAL
    - Required before IMPORT_READY.
    - Bound to exact run_id, version and final output hash.
    - Any material change invalidates approval.

16. IMPORT OUTCOME
    - Manual WP All Import is allowed in v1.
    - Outcome must be explicitly recorded.
    - Exact artifact binding required at strongest available assurance.
    - IMPORT_FAILED: no promotion.
    - IMPORT_PARTIAL: no promotion + reconciliation required.

17. CONCURRENCY
    - No distributed locking.
    - Single active mutation Run guard.
    - Expected parent Master version/fingerprint pinned at start.
    - Recheck before approval and immediately before promotion.
    - Stale parent blocks promotion.

18. ROLLBACK
    - Rollback is always a new Run/version.
    - Use archived historical successful Master + Product State.
    - Historical payload is not silently recalculated.
    - Current non-mutating safety gates still apply.
    - Rollback uses normal approval/import/promotion pathway.

19. VALIDATION BOUNDARIES
    Required:
    - Source/evidence validation
    - Canonical Source validation
    - Authority/Master/State validation
    - Domain result validation
    - Full Dataset/mutation validation
    - Canonical output validation
    - Pre-approval/current-parent validation
    - Promotion/import-evidence validation

20. OUTPUT
    - Full Dataset, not delta.
    - Exact active 95-column schema/order.
    - Canonical deterministic serialization.
    - Exact final artifact hash.
    - Unauthorized fields preserved exactly.

21. AUDIT / ARTIFACTS
    Every Run keeps enough standalone evidence to reconstruct:
    source → canonical source → decisions → calculations
    → candidate → approval → import outcome → promotion.
    Closed Runs are immutable.

22. HUMAN REVIEW
    Human input only for:
    - source mismatch/unresolved;
    - business decisions explicitly required by Rulebook;
    - new product/scope/override/etc. decisions;
    - Final Approval;
    - import attestation/outcome;
    - production reconciliation when required.

23. TEST BOUNDARIES
    Independent fixtures required for:
    - Source Pipeline
    - Canonical IR
    - Matching/BOM/Pricing
    - Discount/Override/Availability
    - State lifecycle
    - Master/output preservation
    - canonical serialization
    - approval/import/promotion
    - failure/partial behavior
    - rollback
    - deterministic replay
    - historical end-to-end regression

24. PACKAGING
    - Local single-entry application.
    - Must work without server installation.
    - Runtime data/artifacts separate from application code.
    - Development may run from source.
    - Packaging technology is deferred.

25. LANGUAGE
    - No architecture-level evidence requires reopening the current
      Python direction.
    - Detailed language/library/package decisions belong to the
      technology-selection phase.

26. DEFERRED ARCHITECTURE
    - automated PDF-native extraction;
    - OCR/LLM QA;
    - GUI/TUI;
    - direct Google Sheets automation;
    - automatic WP import;
    - alternative source formats;
    - embedded transactional state store.

27. PROHIBITED / UNJUSTIFIED v1 ARCHITECTURE
    - microservices;
    - network service boundaries;
    - REST backend;
    - worker/queue architecture;
    - distributed transactions;
    - event sourcing;
    - generic workflow engine;
    - generic plugin framework;
    - multi-user/auth/RBAC infrastructure;
    - mandatory AI processing.

END CONTRACT
