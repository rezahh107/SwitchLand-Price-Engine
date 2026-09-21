# SLPE-DE004 — Variable Product Import Hardening

Date: 2026-09-21
Status: RESOLVED_FOR_TECHNICAL_GUARDS; BUSINESS_POLICY_GAP_OPEN
Repository role: PRE_IMPLEMENTATION continuity / migration binding

## Trigger

Production evidence from `SLPE-RUN-20260920-001` exposed two gaps not fully represented by DE003:

1. A WooCommerce `variable` row can have `Column2` populated while explicit Parent SKU `Column28` is blank. With the generic WP All Import mapping, the same SKU can be attempted on both parent and variation. Exact observed subject: `Column1=533570693`, parent Product ID `22569`, parent SKU `10999`, candidate child Product ID `42419`.
2. For zero-price variations, an import log entry `catalog_visibility=hidden` did not persist as `hidden` in Production read-back for Product IDs `43123`, `43124`, and `43125`.

## Resolution

The repository records the following fail-closed obligations without changing business pricing policy:

- Add `VARIABLE_PARENT_CHILD_SKU_GUARD` before any SKU-enabled import.
- For `variable + Column28 blank + Column2 nonblank`, never permit `Column2` to be applied as both parent SKU and variation SKU. Exact parent/child identity and mapping must be proven first.
- Require Parent-aware Production read-back for variable products; parent-only Regular Price blanks do not by themselves prove a pricing failure when effective price is variation-scoped.
- Add `VARIATION_AVAILABILITY_SEMANTICS_GATE`: an import log that says `catalog_visibility=hidden` is not Production proof for a variation.
- Do not silently choose how `Price=0 -> hidden` should be represented for a zero-price variation whose parent has active siblings. That remains an explicit Owner business-policy decision.
- `SLPE-RUN-20260920-001_CORRECTIVE_IMPORT_003.csv` is retained as history but marked `SUPERSEDED_DO_NOT_IMPORT`.

## Live authority bindings

- Master Specification: `v1.4.0`, unchanged.
- Rulebook successor: `v1.11.0`, Drive `1GNXj1u6VUMb5sbgpNuC45H-j0c-clMtf`, SHA256 `44225f1efebdfa8bec0a9d9dce449f0661b3e215214cf643fe26312d8cf59c9f`.
- Active WP All Import Contract remains `v1.2.0`; it is not changed by this decision escape.
- Candidate WP All Import Contract: `v1.4.0_CANDIDATE`, Drive `1qk82T6yCIy6HY56SePY6kFIC-0_ErYDE`, SHA256 `bd29b4b4989017921c05995e71113f035eeae75e46d8ffc319b39e1226066523`; not active.
- Root-cause evidence: Drive `1DCYbBodILlaHQOLr3DvSp_3DqIOJWNv7`.
- Reconciliation 006: Drive `1lOEZtKJL1ADVCbkR-1Virbncl-p6CkYp`.

## Planning impact

This escape does not advance implementation status. All `CAP-01..CAP-13` remain `NOT_IMPLEMENTED / NOT_TESTED`.

Owning plan obligations:

- `SLPE-P003`: migrate/trace Rulebook v1.11.0 controls.
- `SLPE-P008`: validate 95-column preservation and effective variable-product output/read-back semantics.
- `SLPE-P010`: implement parent/variation SKU preflight, Parent-aware Production read-back, import-template drift checks, and the variation availability gate.

## Non-effects

No Current Master or PPS mutation is authorized by this document. No pricing formula, 95-column schema/order, WooCommerce Production data, or WP All Import template is changed by this repository update. Immutable history is preserved.
