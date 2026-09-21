# SLPE-DE002 — Master v1.4 Canonical Repository Sync

## escape_id
`SLPE-DE002`

## status
`RESOLVED`

## resolved_on
`2026-09-21`

## owner_authorization
Owner explicitly requested continuation of the cross-system synchronization, comparison of the repository Master baseline against v1.4.0, and synchronization of the repository if v1.4.0 was genuinely newer/better. The comparison condition was satisfied before mutation.

## affected_frozen_decision
Historical repository planning/frozen documents contain hard-coded active-Master references to `SLPE-MASTER-SPEC-001 v1.2.0` and a P001 canonical target named `authorities/master/SLPE-MASTER-SPEC-001_v1.2.0.md`.

`SLPE-DE001` already rebound current authority truth to the exact live Workspace-selected Master v1.4.0 by Drive identity/hash, without rewriting frozen planning history. DE002 addresses the remaining repository-representation gap: the exact official v1.4.0 bytes were not yet present as a reconstructable repository authority transport.

## contradictory_evidence
Fresh live Workspace resolution selects:

- document: `SLPE-MASTER-SPEC-001`
- version: `1.4.0`
- Drive resource: `19gYJsZ1wW5T5vuyRITCnRH6TLTFC8JOU`
- canonical size: `196278` bytes
- SHA-256: `6d32566bf9c66664d46a2c4012f932e315b0276d8584bbdbbc2658fcdacf9949`

The exact official v1.4.0 file independently matches that size/hash.

Its own frontmatter declares `NORMATIVE_SUCCESSOR_ACTIVE_WHEN_SELECTED` and supersedes v1.3.1. The change history shows a monotonic semantic chain beyond the repository's historical v1.2 baseline:

1. v1.2.0 closes Composite Rounding by explicit Business Owner policy and preserves the hardened architecture.
2. v1.3.0 adds a tightly bounded `RUN_SCOPED_PARTIAL_PROMOTION_EXCEPTION` with explicit open reconciliation; it does not create generic partial promotion.
3. v1.4.0 replaces technical token/hash owner approval with one simple natural-language approval while retaining Hash/CAS/Revision/Resource-ID/Read-back/Duplicate checks as machine-owned controls.
4. v1.4.0 explicitly preserves pricing, SKU, Part Number, Promotion, Evidence and Reconciliation rules outside that declared governance surface.

Therefore v1.4.0 is not selected merely because its version number is higher. It is the current semantic successor and contains the v1.2 commercial policy plus later bounded governance improvements.

## comparison_verdict
`STRICT_SUCCESSOR_FOR_CURRENT_PRODUCT_BUSINESS_AUTHORITY`

## smallest_decision_reopened
Only the stale active-Master **version/path/transport target** in the pre-P001 repository continuity baseline is superseded:

- active Master version: `1.2.0` → `1.4.0`
- pre-P001 repository transport: historical v1.2 transport remains preserved; a new exact reconstructable v1.4 transport becomes current
- P001 eventual canonical single-file target: `authorities/master/SLPE-MASTER-SPEC-001_v1.4.0.md`

No unrelated architecture, technology, Plan ordering, acceptance boundary or business algorithm is reopened by DE002.

## implementation
The exact official v1.4.0 bytes are stored as a pre-P001 ordered UTF-8 parts transport:

`authorities/master/SLPE-MASTER-SPEC-001_v1.4.0.parts/MANIFEST.json`

Transport contract:

- `ORDERED_UTF8_PARTS_CONCATENATE_EXACTLY_NO_SEPARATOR`
- 21 ordered parts
- reconstructed canonical size: `196278`
- reconstructed canonical SHA-256: `6d32566bf9c66664d46a2c4012f932e315b0276d8584bbdbbc2658fcdacf9949`
- source live Drive resource: `19gYJsZ1wW5T5vuyRITCnRH6TLTFC8JOU`

The split representation is continuity transport only. P001 still owns convergence to the canonical single-file repository target.

## affected_plans
- `SLPE-P001`: canonical authority placement target is v1.4.0 instead of stale v1.2.0.
- `SLPE-P003` onward: use active v1.4.0 semantics as the normative Master baseline.

No Plan order or dependency changes.

## non_effects
This Decision Escape and synchronization do **not**:

- modify Current Master data or pointer;
- modify Product Pricing State data or pointer;
- write to WooCommerce Production;
- modify the persistent WP All Import template configuration;
- change `is_update_sku=0` in the governed Import Contract;
- alter 95-column schema or row order;
- change any pricing formula;
- mark any Python capability implemented or verified;
- start P001;
- rewrite or delete the preserved historical v1.2 transport;
- rewrite frozen PSS/MIR bodies merely to remove historical literals.

## implementation_status_effect
None. `CAP-01..CAP-13` remain `NOT_IMPLEMENTED / NOT_TESTED`; `active_plan=null`; `SLPE-P001` remains the next authorized Plan.

## safe_interim_state
Repository and live Workspace identify the same current Product/Business Master v1.4.0 by exact identity/hash. The repository now also carries exact reconstructable official bytes. Live operations continue to resolve actual runtime authority through fresh Workspace Manifest → `CURRENT_SET`.

The Production reconciliation for `SLPE-RUN-20260920-001` remains `OPEN_BLOCKED_NOT_PROMOTED` and is not modified by this sync.
