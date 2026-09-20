# SLPE-DE001 — Live Workspace Authority Rebaseline

## Identity
- `escape_id`: `SLPE-DE001`
- `status`: `RESOLVED_OWNER_AUTHORIZED_REBASELINE`
- `date`: `2026-09-21`
- `starting_commit`: `f643c74cc1d80f39a675d119f7f3d650b610b399`

## affected_frozen_decision
The repository continuity baseline hard-coded `SLPE-MASTER-SPEC-001 v1.2.0` as the active Product/Business authority, while the live governed Workspace `CURRENT_SET` selects `SLPE-MASTER-SPEC-001 v1.4.0` and successor live governance artifacts. The stale version literal appears in repository continuity/frozen planning material and cannot be silently rewritten under `SLPE-LIEP-v1`.

## exact_contradictory_evidence
- Live Workspace Manifest: `1Kk3XaJwaRJWl2UrzoZJA5-YvuQCRTSQB`.
- Predecessor live Current Set: `SLPE-CURRENT-SET-GOVERNANCE-20260920-001`, resource `1vEx4-bPV6sptVzP1JdMLHuo__3-esdZu`, SHA-256 `27596a3e7c20c27f36ecb6bc069eea7d025b01a12c36bca423f3d98b2aea3f39`.
- Active live Master Specification: v1.4.0, resource `19gYJsZ1wW5T5vuyRITCnRH6TLTFC8JOU`, SHA-256 `6d32566bf9c66664d46a2c4012f932e315b0276d8584bbdbbc2658fcdacf9949`.
- Staged successor Rulebook v1.9.0: resource `18wTBbFQDS--3UgI0y3qvLNZUexBmEJrA`, SHA-256 `e48867a6888eb14bbaac0f2654fecd8ba72faa9163220958fe310e689d9fbf4b`.
- Staged successor WP All Import Contract v1.2.0: resource `19PFly7-marww54yOaHmX0JKt-U_RYUlX`, SHA-256 `57cb1f07361e2c6a0fa980fbd63242fc0bc526268747bc48b0ea9e250dbfd5ba`.
- Owner root-cause hardening decision: `SLPE-OWNER-DECISION-ROOT-CAUSE-HARDENING-20260921-001` / `OD-050`, resource `1S_VOKI_vYlOYTmndPs4vOnMgdXwiMFJg`, SHA-256 `82cd2bf659e2faf1a9c6164d00e2d53793df35aa92aed662b92f0cad2a38c67f`.

## reproducible_failed_gate
A fresh agent following `AGENTS.md` + `PROJECT_STATE.json` would pin Master v1.2.0, while a fresh live Workspace read pins Master v1.4.0. Both cannot be the same active Product/Business authority. Continuing without explicit rebaseline would create authority ambiguity and violate fail-closed/current-state rules.

## smallest_decision_to_reopen
Only the stale **active authority version/binding** is reopened:
1. repository canonical Product/Business Master baseline becomes exact v1.4.0 bytes/hash;
2. current live Rulebook/Import Contract/Owner Decision identities are hash-bound as migration inputs and live Workspace authorities when selected by `CURRENT_SET`;
3. the new root-cause execution-safety decision `OD-050` is carried into future Python migration/implementation obligations.

No other frozen architecture, technology, Plan ordering, UI boundary, persistence model, 95-column schema, pricing formula, or capability status is reopened.

## root_cause_hardening_carried_forward
The live audit resolved Decisions 001–012. Rulebook v1.8.0 already encoded their permanent exact-scope business outcomes (`OD-043..OD-049` plus existing `OD-006/007/009`). v1.9.0 preserves them byte-for-byte as structured prior values and adds only these execution controls:

- `SKU_TRANSITION_PREFLIGHT`: detect transient SKU collisions against current holder state before import.
- `SKU_TRANSITION_DEPENDENCY_ORDERING`: release an approved moving holder before assigning its SKU; block cycles/external holders; never fabricate temporary SKU.
- `SELECTIVE_HASH_SKIP_GUARD`: `SKIPPED / Record data unchanged` is not Production proof after prior mismatch/error/partial/corrective state.
- `PRODUCTION_READBACK_GATE`: read back `ID, SKU, Regular price, Sale price, Visibility in catalog, Parent` before Promotion.
- `IMPORT_CONTRACT_DRIFT_GUARD`: screenshots/symptoms cannot silently change the active WP All Import Contract; require verified export + successor contract.
- `CORRECTIVE_IMPORT_LEAST_MUTATION`: derive exact affected rows from approved Final full dataset and preserve all 95 columns/unrelated values.

The WP All Import persistent template is explicitly **not changed**. In particular `is_update_sku=0` remains preserved; protected SKU repair uses a separate exact-subject governed path.

## affected_plans_capabilities
- `SLPE-P003`: migrate/trace `OD-043..OD-050` and root controls into the Python-oriented Runtime Rulebook projection.
- `SLPE-P006`: implement/test exact identity/BOM/pricing mappings with independent fixtures.
- `SLPE-P008`: implement/test exact 95-column preservation and corrective least-mutation validation.
- `SLPE-P010`: implement/test SKU transition preflight/order, selective-hash skip guard, production read-back gate, contract-drift blocking, and corrective execution evidence.

No capability is advanced by this Decision Escape. `CAP-01..CAP-13` remain `NOT_IMPLEMENTED / NOT_TESTED`. `SLPE-P001` remains the next authorized Plan.

## planning_literal_supersession_boundary
`planning/PROJECT_SKELETON_SPECIFICATION.md`, `planning/MASTER_IMPLEMENTATION_ROADMAP.md`, `AGENTS.md`, and the frozen migration contract contain v1.2.0-era literals. DE001 supersedes **only** literals that identify the currently active Master version. The planning structure, Roadmap ordering, architecture, technology, execution protocol, migration method, and all unrelated frozen decisions remain unchanged. The frozen planning/contract files themselves are not rewritten merely to erase history.

## safe_interim_state
- Current Master unchanged.
- Current Product Pricing State unchanged.
- WP All Import persistent template settings unchanged.
- WooCommerce Production unchanged by this governance sync.
- `SLPE-RUN-20260920-001` Production reconciliation remains open/blocked/not promoted.
- Live successor authorities may be staged and activated only through a new immutable `CURRENT_SET` plus persisted Manifest read-back.

## owner_authorization
The Owner explicitly requested that all resolved issues and root-cause protections be made permanent and that repository/project/Drive remain synchronized, with unrelated content left untouched. This satisfies the required human reopening decision for the bounded authority rebaseline.
