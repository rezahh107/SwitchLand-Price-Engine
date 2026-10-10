# SwitchLand WP All Import — target configuration qualification V1

**Scope:** CI-only qualification on `rezahh107/SwitchLand-Price-Engine`, PR #11. No Production connection or Product/Master/Rulebook/Import Contract mutation.

## Evidence provenance: never collapse these sources

| Class | Artifact / authority | What it supports | What it cannot support |
|---|---|---|---|
| `HISTORICAL_EXPORTED_CONFIGURATION` | Contract v1.2.0 `source_evidence.user_supplied_bundle_zip`, `Import_Sheet1.csv.zip`, SHA-256 `58ac9909fd599451bb1d6df8ef4d4e007a07d519bcceb4b276d135b2591787dc` | Historical 95-column/826-row source and embedded template; former first-row-parent configuration | Current configuration, exact Production core version or target grouping |
| `HISTORICAL_EXPORTED_CONFIGURATION` | Embedded `WP All Import Template - Import_Sheet1.csv.txt`, SHA-256 `c441e83def367cf5e5a88338508b2f49bf8fec1330b46d72bfd5e3f9d432712b` | Earlier mapping and Add-On 4.0.5 version evidence | Target Add-On 4.0.6 runtime, fresh current template |
| `OWNER_APPROVED_TARGET_SEMANTICS` | Master v1.14.3, Rulebook v1.24.4, Contract v1.2.0 `candidate_target_template_configuration` | `Column1` unique ID, `Column2` SKU, `Column8` group, `Column28=PARENT_+Column8` for valid variation members; unique identifier grouping; selective price, sale, visibility updates; existing SKU protected | Actual persisted plugin option paths/values or Production equivalence |
| `LAB_SYNTHETIC_CONFIGURATION` | `target-source-probe.php` + `target-simple-consumer.php` + captured `target-simple-bootstrap.json` | Exact installed WP All Import 5.1.0 / Woo Add-On 4.0.6 option-key inventory; native saved/read-back LAB candidate; observed WooCommerce state if independent verifier accepts it | Historical Add-On 4.0.5 behavior, fresh target config, Production state |
| `LAB_SYNTHETIC_CONFIGURATION` | `target-fixture.php` | 95 ordered headers, BOM/semicolon/LF fixture, Persian/multiline/quoted field round-trip; fail-closed input identity controls | Native CSV acceptance, imported variable/variation parent-child attachment, C1 provider-native comma or XLSX C2 |
| `FRESH_TARGET_EXPORTED_CONFIGURATION` | **Not located** in repo/accessible Drive search at execution | None | **`EXACT_SWITCHLAND_CONSUMER = NOT_PROVEN`** |

**Version distinction:** Historical WooCommerce Add-On `4.0.5` is not the exact LAB `4.0.6` build. Historical Core version is `NOT_PROVEN_FROM_TEMPLATE_EXPORT`. No version migration or active contract change is made.

## Lab evidence and closed boundaries

- The original PR #10 WP All Import CI Lab V1 uses native saved `PMXI_Import_Record`, `PMXI_File_Record` and `wp all-import run <id>`. Its executed scenario is a **synthetic XML simple product**, not variation nor current target mapping.
- The additional synthetic 95-column fixture is explicitly not the source consumed by the native simple-product test. A fixture format check does not establish an importer behavior claim.
- The additional simple-product candidate uses native save/read-back, authentic WooCommerce CRUD before/mid/after snapshots, source re-pointing through the plugin history model, and independent negative controls. Its independent result is **PASS** on the exact pinned LAB versions. A passed CLI exit never overrides a divergent WooCommerce read-back.
- The installed-source probe inventories option names and file/line provenance. Finding an option *name* does not establish the values/modes or support for a specific grouping algorithm.
- The **LAB candidate** for variation grouping is now **native-runtime verified** on Add-On 4.0.6: two separate variable parents, four child variations with exact Parent SKU from Column28 semantics, zero-priced child still present, sibling variations still present, and all six identities preserved across re-import. The independent verifier also rejected its deliberate Parent SKU, group merging, duplicate parent, missing child, wrong attachment, missing required option and changed-ID mutations. This does **not** prove a fresh target configuration or Add-On 4.0.5.
- Native simple-product candidate verifies Product ID, mapped regular/sale prices, zero as numeric zero, clearing sale-price as blank, `catalog_visibility=hidden` on zero, stable protected SKU after source SKU changes, preserved independently edited product title, and unrelated sentinel persistence across real imports.
- **Source-backed selector findings:** `is_product_visibility=xpath` and `product_visibility_xpath={visibility[1]}` are both required; setting only `single_product_visibility` produced `visible` on a zero-price record and was correctly rejected in the earlier CI run. For variable products, `is_multiple_product_type=yes` and `multiple_product_type=variable` are required: setting `single_product_type=variable` alone created four **simple** products, not variations. Native grouping candidate uses `first_is_parent=no`, `matching_parent=first_is_parent_id`, `single_product_id_first_is_parent_id={column8[1]}`, `single_product_first_is_parent_id_parent_sku={column28[1]}`, and `variable_sku={column2[1]}`.
- **Actual CI runtime evidence:** [run 38073254974](https://github.com/rezahh107/SwitchLand-Price-Engine/actions/runs/38073254974), with native saved-options/read-back, actual WooCommerce before/after state, independent verifiers, five 95-column fixture negative controls, twelve simple-product native verifier negatives, ten grouping verifier negatives, and the original PR #10 regression.
- **Not yet established:** The zero-price child has regular_price `0` and WooCommerce status `publish` under the candidate; that by itself does not prove the authorized business meaning of an inactive/hidden variation or how the parent catalog should behave. No positive native test for a blank *regular* price (invalid for a final Master row) was run. Direct provider-native comma-separated CSV and XLSX were not exercised. The 95-column CSV fixture was round-tripped structurally, not imported through the XML native candidate.

- Zero and blank are semantically distinct; blank price cannot be silently treated as zero. `MST-008/009` and `AVL-001/002/006` remain the business source, not the September incident.

## Qualification gates

```text
EXACT_SWITCHLAND_CONSUMER = NOT_PROVEN
C1_PROVIDER_NATIVE_COMMA_CSV = NOT_PROVEN
C2_PROVIDER_NATIVE_XLSX = NOT_PROVEN
OWNER_SERIALIZATION_DECISION_READY = NO
SUCCESSOR_IMPLEMENTATION = DO_NOT_START_YET
PRODUCTION_EQUIVALENCE = NOT_PROVEN
```

**Next evidence required for exact target:** In WP All Import, select the active SwitchLand import and export its currently saved template/settings (after the Owner-approved Parent SKU and group settings are actually saved). The export must be tied to a specific installed plugin/add-on version and current saved import identity. It must be compared with the target semantics and tested against real native WooCommerce behavior **before** any Production equivalence claim.

No Production data or plugin source is published in this PR.
