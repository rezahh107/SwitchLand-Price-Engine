# WP All Import dated October 4 Bundle — qualification V1

**Status:** PRIVATE_STATIC_SOURCE_VERIFIED, LAB_NATIVE_CANDIDATE_PARTLY_VERIFIED, EXACT_PRODUCTION_CONSUMER_NOT_PROVEN  
**Repository baseline:** `main@a3e687a0fd1ca09915eb65e63027ef0907d21245`; qualification-only Draft PR #12 (unmerged).  
**Source class:** `OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE`, dated **2026-10-04**, **not** evidence of current active saved configuration on 2026-10-10.  
**Security:** original ZIP, original CSV rows, and original full 523-option template were examined only in an ephemeral private container. None was pushed to this public repository, nor to public CI/Actions artifacts. Only a limited **non-sensitive fingerprint/census/option allowlist** is published in [dated-20261004-static-attestation.json](dated-20261004-static-attestation.json).

## 1. Source identity and independently verified census

Observed **directly from exact Owner-supplied ZIP** (not from an earlier report or handoff):

| Check | Direct observed result |
|---|---|
| ZIP byte length | `77959` exactly |
| ZIP SHA-256 | `6cb9f29fd4b798c38c8dcc05a3b28e01858c95f7847488fa9257bb91176a62c2` |
| Embedded CSV SHA-256 | `626da8979ce64ec5703ff7172aa0870b46870bc6c6dd9fe72ca1dbe7332808f8` |
| Embedded WP All Import template SHA-256 | `fa5af37b144c0f32d4a052672d1c406520c44d95da0f331039b2c70ea1ce121a` |
| ZIP structure / integrity | 3 non-directory members (CSV, template, readme) + folder entry; ZIP CRC PASS; template valid JSON list with exactly 1 import and 523 option keys |
| Encoding/serialization | UTF-8 BOM; semicolon delimiter; LF; no CRLF; valid 95-wide CSV records |
| Dataset size | **95 columns, 876 records** |
| Product types | 53 simple, 823 variable |
| Zero and visibility | 355 `Price=0`, **all 355 `hidden`**; 0 nonzero-hidden source rows; 0 blank regular prices |
| Sale Price | **876 blank** `Column10` rows |
| Column1 identity | 0 blank and 0 duplicates |
| Parent SKU | 822 nonblank `Column28`, **all match** `PARENT_` + `Column8` |
| Exceptions | 1 variable row missing both Column8 and Column28; **1 simple row with nonblank Column8** (but no Parent SKU) |

`inspect-dated-bundle.py` is a **private, local-only** reusable checksum/census/template gate. It runs against original exact bytes when provided privately. The CI unit tests for the inspector use independently fabricated ZIP archives only and **do not** re-inspect the original ZIP in GitHub.

**Important schema discrepancy:** The actual third header is `Column۳` (Persian U+06F3), **not** `Column3` (ASCII U+0033). The exact exported option `single_product_id_first_is_variation={column3[1]}` references an ASCII digit. Also `tmp_unique_key` and `single_product_id_first_is_parent_title` use `{column3[1]}`. The private source has 281 distinct values under the Persian-digit header, so this is not safely interchangeable on raw CSV semantics. The exact WP All Import native parser may normalize field tokens; **do not assert runtime mismatch before testing that consumer**. A source-script mismatch exists regardless.

## 2. Source-backed option reconciliation

Unlike the initial PR #12 draft, the Oct 4 reported options have now been checked **directly against the exact embedded Template**. [The redacted 39-key allowlist](dated-20261004-static-attestation.json) binds these observations to the three verified file SHA-256s; `dated-reported-option-diff.py` reconciles this source-backed allowlist against active Import Contract v1.2.0 in CI. Values from omitted template options are **not** represented as exhaustive conformance.

### Positive correspondences to Owner target

| Exact exported option | Observed dated value |
|---|---|
| `unique_key` | `{column1[1]}` |
| `single_product_sku` | `{column2[1]}` |
| `single_product_id_first_is_parent_id` | `{column8[1]}` |
| `single_product_first_is_parent_id_parent_sku` | `{column28[1]}` |
| `single_product_regular_price` | `{price[1]}` |
| `single_product_sale_price` | `{column10[1]}` |
| `single_product_type` | `{column27[1]}` |
| `is_product_visibility`, `single_product_visibility` | `xpath`, `{visibility[1]}` |
| Native `grouping_indicator` | `xpath` |
| Template Add-On version field | `woo_add_on_version=4.0.6` (**template metadata only**, not installed Production plugin proof) |

### Material differences / hazardous effective modes

| Option / family | Exact exported value | Owner-approved intended behavior |
|---|---|---|
| `first_is_parent` | `yes` | `no`; do not implicitly treat first source row as parent |
| `matching_parent` | `first_is_parent_id` | Conceptual explicit Parent SKU, with native `first_is_parent_id` allowed only when combined with proper Column8 grouping and Column28 Parent SKU native mappings; **not** a simple string-mismatch decision |
| `variable_sku` | blank | `{column2[1]}` |
| `single_product_id_first_is_variation` | `{column3[1]}` | B native candidate uses `{column2[1]}`; raw Column3 token mismatch requires an authentic CSV parser test |
| `is_update_sku`, `is_update_title` | both `1` | both `0` for existing product |
| `is_update_custom_fields`, `update_custom_fields_logic` | `1`, `full_update` | `0`: preserve unrelated custom fields |
| `is_update_acf`, `update_acf_logic` | `1`, `full_update` | `0`: preserve unrelated ACF |
| `is_multiple_product_type`, `multiple_product_type` | `no`, `variable` | Source-backed native B candidate requires explicit product-type modes for synthetic simple versus grouped variations |
| `product_visibility_xpath` | **absent** | B explicitly binds `{visibility[1]}`; observed A synthetic simple-product output was nevertheless `hidden` |
| `is_update_content`, `is_update_categories`, `is_update_parent`, `is_update_attributes` | all `1` | all `0` for protected existing fields |
| `is_update_status`, `is_update_product_type`, `is_update_attachments` | all `1` | all `0` |

Additional Woo generalized option update flags in this export are broad. Exact effective update behavior must be resolved jointly with product modes and native plugin code. A few positive bindings do not make this template compliant.

## 3. Group-less variable row — real source classification

**Directly observed:** it is source data row **757** (CSV header excluded), not the first row; it has a nonblank unique SKU, positive regular price and `visible` status, but **no Column8 and no Column28**. Its SKU is neither prefixed `PARENT_` nor equal to any generated Parent SKU in the dated source. Therefore the dated row is **not an explicit parent anchor for the generated parent-SKU cohort**. It also does not meet the approved variation grouping invariant.

Business classification: `UNRESOLVED_UNGROUPED_VARIABLE_REQUIRES_POLICY_DECISION`. An existing legacy standalone variable product or explicit exception is still conceivable; no private catalogue/Production identity evidence authorizes declaring it invalid, silently deleting it, auto-grouping it or admitting it as a parent. A separate synthetic native A/B diagnostic found a group-less input converted to simple/no parent while grouped sibling variations attached correctly elsewhere. **That synthetic observation is not the original row's final classification.**

**Related source exception:** one `simple` row unexpectedly has a populated Column8 but blank Column28; static classification requires reviewing the exact intended product-type/group relation before treating every nonblank Column8 as a valid Variation.

## 4. Native Lab execution — what it proves

Existing Lab and pinned source: WordPress 6.8.3, PHP 8.2.34, WooCommerce 10.2.2, WP All Import Pro 5.1.0, Woo Add-On 4.0.6. The existing source-backed grouped parent/variation tests and synthetic 95-column shape checks remain.

A = **native saved-import reconstructed from the exact source's allowlisted dated options**, not raw 523-option Import restoration; B = Owner-approved target candidate. Both use disposable **synthetic 95-column-schema-derived XML**, not original CSV, not Production and not C1/C2 input. Native `wp all-import run <id>` executes first create and then update; independent Woo CRUD readback checks Product ID, SKU, independently edited title, price/sale, catalog visibility, custom/ACF-like metadata.

**Current, independently observed source-verified allowlist Lab CI:** [native lab run 38084424265](https://github.com/rezahh107/SwitchLand-Price-Engine/actions/runs/38084424265) **PASS**. The same exact-source allowlist A mapping was saved/read back, then executed through the native WP All Import CLI. A changed the SKU, overwrote independently edited title and cleared unrelated custom/ACF-like postmeta; B preserved all four protected properties. Both retained Product IDs, persisted price `0`/blank sale, and returned catalog `hidden`. This is **not** proof that exact 523-option dated Export configuration was activated or that original CSV was natively parsed.

The native CI also retained regression tests for two grouped variable parents/four children with repeated imports, 12 A/B falsification controls, 6 group-less controls and 5 inspector synthetic tests. [Public fixture boundary run 38084424205](https://github.com/rezahh107/SwitchLand-Price-Engine/actions/runs/38084424205) also **PASS**. Neither workflow had access to the private original ZIP.

**Unverified even after a PASS:** exact 523-option template runtime, authentic original ZIP import, true ACF mapping, exact `Column۳` parser normalization, all source product types/exception effects, real zero-priced variation inactive/display semantics, native provider comma CSV C1 and native XLSX C2.

## 5. Required claim ceiling, privacy and next step

```text
EXACT_SWITCHLAND_CONSUMER = NOT_PROVEN
C1_PROVIDER_NATIVE_COMMA_CSV = NOT_PROVEN
C2_PROVIDER_NATIVE_XLSX = NOT_PROVEN
OWNER_SERIALIZATION_DECISION_READY = NO
SUCCESSOR_IMPLEMENTATION = DO_NOT_START_YET
PRODUCTION_EQUIVALENCE = NOT_PROVEN
```

No source, public/Production import settings, active Contract v1.2.0, MST-007, Master/Rulebook/GWOC/CURRENT_SET or operational package changed. The main PR remains Draft.

**Smallest next step:** Reconcile the current active Import's *fresh saved export* and installed Core/Add-On version proof against this dated exact source. In a private disposable WP environment, exercise exact original Bundle ingestion and Column۳/Column3 mapping plus group-less/zero Variation outcome without exposing business records. Then separately test provider-native comma CSV C1; do not infer C1 from semicolon CSV parsing or XML-based Lab runs.
