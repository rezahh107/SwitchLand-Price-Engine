# SLPE-DE003 — Controlled SKU Import Governance

- **Date:** 2026-09-21
- **Status:** RESOLVED_BY_OWNER_APPROVAL
- **Scope:** bounded governance successor for SKU updates through WP All Import
- **Owner decision:** `OD-051` / `SLPE-OWNER-DECISION-CONTROLLED-SKU-IMPORT-20260921-001`

## Why this decision escape exists

The previous root-cause hardening correctly introduced `SKU_TRANSITION_PREFLIGHT`, dependency ordering, selective-hash skip protection and mandatory Production read-back, but it kept `is_update_sku=0` and therefore still required a separate SKU repair path. The Owner explicitly decided that the root fix must allow SKU to remain enabled in the normal import workflow, provided deterministic preflight and post-import evidence prevent transient duplicate-SKU failures.

## Authorized change

Only the following persistent WP All Import update flag may change:

`matching_and_update_behavior.all_update_flags.is_update_sku: "0" -> "1"`

No other mapping, update flag, unique key, 95-column schema, row order, pricing formula, Current Master, PPS, or Production value is authorized to change by this governance activation.

## Mandatory safety gates

A SKU-enabled Import Contract is usable only when all of the following hold:

1. A verified WP All Import template/config export proves the only material template delta is `is_update_sku=1`.
2. Fresh Production identity read-back is obtained immediately before import.
3. `SKU_TRANSITION_PREFLIGHT` proves each target SKU is free, already held by the same target, or can be made free through an acyclic dependency order.
4. Current SKU holders move before dependent claimants.
5. Cycles or external holders block execution; no fabricated/temporary SKU may be invented automatically.
6. Corrective rows are exact approved Final rows, preserve all 95 columns, and are limited to the affected subjects.
7. `SKIPPED` is not treated as mutation evidence.
8. Post-import Production read-back verifies `ID, SKU, Regular price, Sale price, Visibility in catalog, Parent` before reconciliation closure or promotion.

## Pilot binding

Pilot: `SLPE-RUN-20260920-001_PILOT_CORRECTIVE_002.csv`

Subjects: WooCommerce Product IDs `34201, 22133, 22569, 43123, 43124, 43125`.

The latest persisted Production export showed no transient SKU dependency among these six, but that export is not fresh enough to satisfy the pilot-time preflight gate. The pilot therefore remains `PREPARED_NOT_IMPORT_READY` until fresh Production identity read-back and verified template/config export are supplied.

## Non-effects

- Current Master unchanged.
- PPS unchanged.
- Production unchanged by this governance work.
- Existing WP All Import template unchanged by this governance work.
- Historical Rulebook/Import Contract artifacts preserved.
- Python implementation capabilities remain `NOT_IMPLEMENTED / NOT_TESTED` until their owning Roadmap Plans are actually implemented and verified.

## Live Drive bindings

- OD-051: `1wzPZUb0tdGIFZaZ2HfPDCdR9a-GKHAco`, SHA256 `5c44c00344f147370aa08e67274571c9702fb7ac4a2bc932de316cbab304ff23`
- Rulebook v1.10.0: `1sPc41CcDeBSlhkD1MT7Eqywnf9ZJDEyj`, SHA256 `28fbe5859c631734617dccdbca0edc675d18075756c701b1a2184ebab1661e03`
- Import Contract v1.3.0 candidate: `1ejp5Ev8a2hIQRqqCFf4PFZUpzozv4XNM`, SHA256 `5c5da84f899bf164bf499396a9a36e5dbaf2cb389c5e96db7b54104a84e860df`
- Pilot CSV: `1gdjjr1YlCd-G6tWkjn_-isF9jucCvvjv`, SHA256 `2f6d353bd839e1cbefbc43b0d0ef863d165332c69482eac0529e1c088507e3f1`
- SKU preflight evidence: `1UEbT_gJzaY8rMmtoPVVeG1HsViXMGWyY`, SHA256 `208cfba126baac25ef1a9dac2a9efa69c132c662006c4c5e6113bec0f4254755`
- Pilot audit: `1JBp7MOlJZTP90uEF_ukJDfaVnBUQMpzZ`, SHA256 `fdb64e7fe7c99f260ba6562b950f23f3b4e46d509c1b0e685f461acab4b34830`
- Prepared successor CURRENT_SET: `1A-Cl7L1-vG3hn21x3481Go5u0JBMWIgd`, SHA256 `0c7998adff6a354b9526d4ccd34d5ac62244829b4832374b67517d1cbed38093`

The active Import Contract remains v1.2.0 until verified template/config read-back satisfies the activation gate for v1.3.0.