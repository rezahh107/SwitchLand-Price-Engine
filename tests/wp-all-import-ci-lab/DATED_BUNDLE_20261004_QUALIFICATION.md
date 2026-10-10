# WP All Import — Owner-supplied dated bundle qualification V1

**Repository:** `rezahh107/SwitchLand-Price-Engine`  
**Starting merged main:** `a3e687a0fd1ca09915eb65e63027ef0907d21245` (merged PR #11)  
**PR:** #12, qualification-only Draft; **not merged**  
**Source classification:** `OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE`  
**Dated filename:** `SLPE_RUN_20261004_001_FULL_DATASET_CORRECTED.csv.zip` (2026-10-04, NOT a live-site revision assertion)  
**Source access:** `ORIGINAL_INPUT_UNAVAILABLE`. Neither the current conversation mounts, Library file search, nor accessible named Drive search exposed the exact file. A sanitized handoff or the request's observations are **not** byte evidence.

## 1. Private input identity gate — blocked, never substituted

The following values are **Owner-expected, NOT executor-observed**:

| Artifact | Expected SHA-256 / size | Execution result |
|---|---|---|
| Original dated ZIP | `6cb9f29fd4b798c38c8dcc05a3b28e01858c95f7847488fa9257bb91176a62c2`; 77,959 bytes | **NOT VERIFIED — exact bytes unavailable** |
| Embedded CSV | `626da8979ce64ec5703ff7172aa0870b46870bc6c6dd9fe72ca1dbe7332808f8` | NOT VERIFIED |
| Embedded template | `fa5af37b144c0f32d4a052672d1c406520c44d95da0f331039b2c70ea1ce121a` | NOT VERIFIED |

Expected, not independently recalculated: 95 columns, 876 rows, 53 simple, 823 variable, 355 zero-price rows carrying `hidden`, no duplicate Column1, one variable row with both Column8 and Column28 blank, 822 nonblank Column28 matching `PARENT_` + Column8. **Do not mark these observed.**

`inspect-dated-bundle.py` checks source ZIP identity, member integrity/bounds, both exact embedded digests, UTF-8 BOM / semicolon / LF, parsed 95-wide rows, identity collisions, zero/hidden, Parent SKU relation, group-less census and template JSON parse status. It emits aggregate-only evidence. Source bytes remain local/private; **no original archive/rows/settings may be passed to GitHub Actions or committed**. Synthetic archive mutation tests exercise checker behavior, not the original source.

## 2. Authority and data provenance

Read `GOAL_AUTHORITY/CURRENT_DESTINATION_2026-10-07.md`, `PROJECT_STATE.json`, `references/current_package/v3.14.2/INDEX.md`, active Import Contract `1.2.0`, and previous `TARGET_CONFIG_QUALIFICATION.md`.

Four non-interchangeable classes:
1. **`OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE`**: original Oct 4 bytes unobserved; supplied checksums/counts unverified. Reported template Woo Add-On 4.0.6 is dated **template evidence only** and does not prove what is installed today, or the installed Core version.
2. **`OWNER_REPORTED_OPTION_VALUES_NOT_ZIP_VERIFIED`**: material option observations from the Owner's task. These are the only basis for reconstructed variant A.
3. **`OWNER_APPROVED_TARGET_SEMANTICS`**: current Master `1.14.3`, Rulebook `1.24.4`, Import Contract `1.2.0`, and fixed `MST-007`.
4. **`LAB_SYNTHETIC_RECONSTRUCTED_CONFIGURATION`**: independent native saved-import/readback on **WP All Import Pro 5.1.0 + Woo Add-On 4.0.6**, pinned WooCommerce 10.2.2. Not a Production export, and not the original dated Bundle.

The machine-readable CI artifact `dated-reported-options-comparison.json` stores redacted, source-labeled comparisons and checks active contract policy; `dated-ab-comparison.json` and `dated-group-less-evidence.json` store synthetic native run outcomes only.

## 3. Material Owner-reported exported-vs-target differences

**These A observations have not been verified against original ZIP bytes.** B values refer to the actual verified LAB candidate (not an activated Production config).

| Material key / effective mode | A — Owner-reported Oct 4 | Owner target / qualified B LAB | Risk or finding |
|---|---|---|---|
| `first_is_parent` | `yes` | `no` | First-row-parent is not authorized target identity |
| `matching_parent` | `first_is_parent_id` | Conceptual policy = explicit Parent SKU; native Add-On still uses `first_is_parent_id` **with explicit Column8 group & Column28 Parent SKU bindings** | String comparison alone does not prove mode |
| `variable_sku` | blank | `{column2[1]}` | Child SKU identity needs explicit binding |
| `is_update_sku` | 1 | 0 | A native import changed protected SKU |
| `is_update_title` | 1 | 0 | A native import overwrote independently edited title |
| `is_update_custom_fields` and update logic | 1, full update | 0 for existing record | A native test cleared custom sentinel; no unsanitized custom mappings used |
| `is_update_acf` and update logic | 1, full update | 0 for existing record | A cleared ACF-*like* meta; actual ACF plugin/mapping path NOT exercised |
| `is_multiple_product_type` | `no` | Native candidate uses explicit per-scenario `yes` + `multiple_product_type=simple/variable` | Actual product type depends on combined options, not one flag |
| `is_product_visibility` | `xpath` | `xpath` | Selector alone insufficient for a universal claim |
| `single_product_visibility` | `{visibility[1]}` | same | Positive binding correspondence |
| `product_visibility_xpath` | reportedly absent | `{visibility[1]}` | B source-backed mode; **A did return hidden** for tested simple zero despite missing selector, so do not assert universal A visibility failure |
| `unique_key` | `{column1[1]}` | same | Positive correspondence, tested for synthetic native matching |
| `single_product_sku` | `{column2[1]}` | same | Positive correspondence |
| Variation Group ID | `{column8[1]}` | same | Positive correspondence in native grouped B case |
| Parent SKU | `{column28[1]}` | same expression (native `single_product_first_is_parent_id_parent_sku`) | Positive reported expression; actual dated raw setting not inspected |
| Regular Price | `{price[1]}` | same | Positive correspondence |
| Sale Price | `{column10[1]}` | same | Positive correspondence |

**Limit:** This is all material *reported* options, not proof that every effective exported option has been enumerated. The exact template export is needed for exhaustive static reconciliation. No flag was normalized or silently changed in reconstructed A. Unknown original option paths are not invented.

## 4. Native paired experiment (distinct synthetic product identities)

The original existing CI Lab was extended rather than duplicated. A and B use equivalent source semantics and isolated synthetic identities in the same disposable WordPress/WooCommerce runner, not two separately restored DB snapshots.

Each variant uses a **synthetic 95-column schema projection to XML** because exact dated ZIP input is inaccessible. Saved import options are read back through `PMXI_Import_Record`; first run creates a product; owner title and metadata are seeded; source changes SKU, title, regular to zero, sale to blank, visibility to hidden; second native `wp all-import run <id>` processes; Woo CRUD and metadata are read back by Product ID.

| Measured outcome | Reconstructed A | Approved B |
|---|---|---|
| Native CLI executed, Product ID stable | PASS | PASS |
| Regular `0`, empty Sale | PASS | PASS |
| Catalog `hidden` | PASS | PASS |
| Incoming changed SKU applied? | **Yes — protected-field mutation** | No — preserved |
| Independently edited title preserved? | **No — overwritten** | Yes |
| Custom metadata sentinel preserved? | **No — cleared** | Yes |
| ACF-like post-meta sentinel preserved? | **No — cleared** | Yes |

**Evidence scope:** These are real native WP All Import and WooCommerce operations on local synthetic fixtures. Clearing `_dated_lab_acf_like_sentinel` is not an authentic ACF plugin integration test. The full multi-option dated template, plugin licensing/runtime parity and full 876-row original CSV were not exercised.

An independent verifier rejects deliberate mismatched protected SKU, title, visibility, meta, Product ID, numeric zero, missing Sale clear, unexecuted update, mislabeled A source, wrong flag, and missing B XPath: **12 falsification controls**. Previous PR #10/#11 native Lab regression remains in the CI workflow.

## 5. Group-less Variable row — separately tested, not discarded

The original reported single variable row with Column8 and Column28 blank is **not accessible**. Its exact role remains:

`UNRESOLVED_ORPHAN_VS_EXPLICIT_PARENT_ANCHOR_VS_LEGITIMATE_EXCEPTION`

An independent synthetic 95-column-derived XML fixture includes an explicit group-less `variable` row **followed by two grouped variation rows**. Separate A and B native imports were executed. In both, the group-less candidate was created as a **simple** WooCommerce product with no Parent ID; its two group members attached to a distinct variable parent with matching `PARENT_<group>` SKU. This measured synthetic behavior **does not classify the actual dated row**. A successful Import CLI run is not a policy admission for an orphan or implicit first-row parent.

Independent group-less evidence checks reject six fabricated identity, missing-row, invalid-mode and fake business-admission mutations.

## 6. Security and claim ceiling

- No original private ZIP, real product/price row, raw template, live site identifier, credential, database dump or proprietary plugin source was committed to the public PR/CI artifact.
- Original source is never needed on the public CI runner. The local private inspector refuses wrong size/hash and unsafe ZIP member paths.
- There was no Production connection, Import configuration activation, Master/PPS/Rulebook/GWOC/CURRENT_SET or operational package mutation.
- `MST-007` and Import Contract `1.2.0` unchanged.

```text
EXACT_SWITCHLAND_CONSUMER = NOT_PROVEN
C1_PROVIDER_NATIVE_COMMA_CSV = NOT_PROVEN
C2_PROVIDER_NATIVE_XLSX = NOT_PROVEN
OWNER_SERIALIZATION_DECISION_READY = NO
SUCCESSOR_IMPLEMENTATION = DO_NOT_START_YET
PRODUCTION_EQUIVALENCE = NOT_PROVEN
```

## 7. Exact blockers and smallest next step toward C1

1. **Exact dated ZIP unavailable**: provide the complete original ZIP in a private ephemeral context; run `inspect-dated-bundle.py` locally, compare every exported material option, and examine the group-less variable row's actual surrounding source/parent semantics without publishing its raw values.
2. **Fresh active-site configuration unproved**: obtain a new *currently saved* WP All Import export and independent installed Core + WooCommerce Add-On version proof, including native saved-options readback. The dated October 4 artifact is not enough.
3. **Native CSV direct consumption/C1 remains open**: only after verified current settings and private-source review, execute the approved 95-column CSV through the exact consumer in a protected disposable WooCommerce instance and verify readback. Provider-native comma/XLSX must be separately tested; never infer them from synthetic XML or structural CSV round-trips.
4. **ACF & zero-priced variation admission**: real ACF field mapping, blank-vs-zero final CSV, inactive child visibility/parent-catalog behavior, and group-less exception semantics require exact evidence.

**No successor implementation or serialization change is authorized by this qualification.** Keep Draft PR open for review; do not merge without Owner authorization.
