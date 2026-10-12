# Native CSV `Column۳` / `{column3[1]}` resolution — pinned Lab qualification

**Date:** 2026-10-12  
**Task:** `WPAI_NATIVE_COLUMN3_CSV_PARSER_QUALIFICATION_V1`  
**Disposition:** **FAIL — material native header/field-expression lookup mismatch**, proven **only** for the exact pinned synthetic WP All Import CSV Lab.  
**Draft PR:** [#13](https://github.com/rezahh107/SwitchLand-Price-Engine/pull/13). Do not merge/release without Owner approval.

## Evidence and exact environment

- Starting clean `main@d25c1d9a6597c6573e623353563050066241fe2b`, with active destination, `PROJECT_STATE.json`, active Import Contract v1.2.0 and PR #10–#12 checks preserved.
- Successful **real native CI** on tested code Head `50a3d04995bd5ec9e69d5c92c423f54ac26ee52f`: [run 38254241122](https://github.com/rezahh107/SwitchLand-Price-Engine/actions/runs/38254241122); temporary artifact `wpai-consumer-lab-v1-evidence`, ID `11748484924` (includes `native-csv-qualification.json`, exact-saved-mapping bootstrap JSON, parser field/value snapshots, initial/repeat Woo CRUD readbacks, individual CLI logs). [Public package boundary run 38254241170](https://github.com/rezahh107/SwitchLand-Price-Engine/actions/runs/38254241170) also succeeded. Code plus existing PR #10–#12 regression checks passed. Subsequent documentation/state commits must be verified separately; a passing code Head is not evidence for an unexecuted future Head.
- Pinned environment: Ubuntu 24.04, PHP **8.2.34**, WordPress **6.8.3**, WooCommerce **10.2.2**, WP All Import Pro **5.1.0**, WooCommerce Add-On Pro **4.0.6**, MariaDB **11.4.8**, WP-CLI **2.12.0**. Workflow verifies exact tracked plugin package identities before installing. These are Lab pins, NOT installed Production version evidence.
- Exact 95 ordered CSV headers read from the **unchanged** active Import Contract. Synthetic four-row / two-parent dataset, unique UID, SKU and third-column marker per row. Pair differs **only in third header bytes**: `Column۳` (U+06F3) or `Column3` (U+0033); identical semantic data. Both inputs use **UTF-8 BOM + semicolon + LF**, checked before and after parse.
- Native **`PMXI_CsvParser`**, loaded from the pinned installed WP All Import plugin, prepares that plugin's own intermediary format from the actual CSV. There is **no external CSV-to-XML serializer or alternate import engine**. Native `PMXI_Import_Record::set/save`, `PMXI_File_Record`, persisted mapping read-back and **`wp all-import run <id>`** (twice per case) exercise the original plugin. The saved path references WP All Import's internal parser-produced representation. This is **not** proof of the raw historical 523-option template being restored.

## Direct observations

| Actual native CSV header | Parser field name | Expression | Real simple-product outcome |
|---|---|---|---|
| `Column3` | `column3` | `{column3[1]}` | **4 created**, expected four distinct titles, values and SKUs; repeat has stable IDs |
| `Column۳` | `column` | `{column3[1]}` | **0 created, 4 skipped**, even though CLI exit is `0` |
| `Column۳` | `column` | `{column۳[1]}` | **0 created, 4 skipped** |
| `Column3` | `column3` | `{column۳[1]}` | **0 created, 4 skipped** |
| Either header | `column` / `column3` | `{nonexistent_sentinel_field[1]}` | **0 created, 4 skipped** per variant |

The parser's header transformation is the **observed cause**. Rewriting only the expression using the Persian numeral does not help on this version, because the native field token has lost that numeral. A `Success: Import completed.` / exit-0 is explicitly insufficient: skipped source rows were observed via native counters and zero Woo products.

### Variation / grouping limits

With native `single_product_id_first_is_variation` bound to the expression under test, `unique_key={column1[1]}`, `single_product_sku={column2[1]}`, and explicit `Column8` group / `Column28` Parent SKU intact:

- All four source/header expression combinations created **two distinct variable parents and four distinct variation SKUs**, with observed correct parent-child relations and numeric regular prices (including zero); first pass created four children, second pass skipped existing four.
- Actual Woo product IDs and parent IDs remained stable over the repeat; no duplicate variation product or cross-group parent was observed in these **bounded synthetic cases**.
- However, the native parser field referenced by `{column3[1]}` is **absent** for the Persian-header case, even when WooCommerce grouping survives on other mappings. **Do not infer** that the saved variation-ID expression resolved correctly, that WP All Import matched by that identity, or that changed/duplicate real products are safe. `variation_expression_resolves=false` and `variation_grouping_and_sku_based_product_creation_observed=true` are deliberately separate results. This is a material lookup defect, **not** harmless normalization.

### Negative / falsification controls

Checks verify exact 95-column order, BOM/semicolon/LF and pair-only header change; expected row/value identities; exact source CSV hash and variant; parser output and row count; native saved import mapping and per-run SHA/path readback; correct source-to-SKU, product ID, parent type and parent association; independent Woo before/after states, no duplicates and complete cleanup. All negative source-expression controls explicitly require no blank/wrong product creation and observed native `skipped=4`.

The independent validator rejects intentional mutations for wrong variant, blank valid control title, collapsed product identity, cross-group parent, missing readback, falsely asserted native saved readback, altered persisted variation expression, altered native source path, and silent creation under the deliberately missing expression. These are **9 falsifications** and do not substitute for the actual native run. An earlier test-harness state-shape bug causing false-positive acceptance was corrected; only the later genuinely successful CI run supports this finding.

## Claim boundary and unresolved work

This is **pinned Lab native semicolon-CSV parser behavior**, not the historical source file being executed and not current Production behavior. No private Owner ZIP, original 876 business rows, actual SKUs/prices, or full 523-option export were committed/uploaded. Historic October 4 hashes remain identity evidence only.

Exact active saved import configuration, installed Production plugin versions, unusual/group-less dated records, real duplicates/reimports and changes of Variation identity remain outside this qualification. This does not close the separate provider-native comma CSV C1/XLSX C2/equivalence gates:

```text
EXACT_SWITCHLAND_CONSUMER = NOT_PROVEN
C1_PROVIDER_NATIVE_COMMA_CSV = NOT_PROVEN
C2_PROVIDER_NATIVE_XLSX = NOT_PROVEN
OWNER_SERIALIZATION_DECISION_READY = NO
SUCCESSOR_IMPLEMENTATION = DO_NOT_START_YET
PRODUCTION_EQUIVALENCE = NOT_PROVEN
```

No active Import Contract, `MST-007`, Master, Rulebook, GWOC, `CURRENT_SET`, PPS, current operational package, live Workspace, or Production site was modified.

**Smallest missing Production evidence:** fresh read-back of the actual active saved import configuration and installed WP All Import/Add-On versions from the live site (without exposing private business data). A separate authorized, private active-site-equivalent test would still be required for Production identity consequences.
