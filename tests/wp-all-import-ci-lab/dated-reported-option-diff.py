#!/usr/bin/env python3
"""Compare PRIVATELY VERIFIED dated-source allowlisted options against active contract.

CI consumes a sanitized attestation bound to exact source SHA-256, NOT source bytes.
It cannot independently re-hash or re-import the private original Bundle.
"""
import json
import os
from pathlib import Path

ROOT=Path(__file__).resolve().parents[2]
CONTRACT=ROOT/"references/current_package/v3.14.2/extracted/PROJECT_SOURCES/SLPE_WP_ALL_IMPORT_CONTRACT_v1.2.0.json"
ATTESTATION=Path(__file__).with_name("dated-20261004-static-attestation.json")
REPORT_NAME="dated-verified-options-comparison.json"
ZIP_SHA="6cb9f29fd4b798c38c8dcc05a3b28e01858c95f7847488fa9257bb91176a62c2"
CSV_SHA="626da8979ce64ec5703ff7172aa0870b46870bc6c6dd9fe72ca1dbe7332808f8"
TEMPLATE_SHA="fa5af37b144c0f32d4a052672d1c406520c44d95da0f331039b2c70ea1ce121a"

TARGET_NATIVE={
  "first_is_parent":"no",
  "matching_parent":"first_is_parent_id",
  "variable_sku":"{column2[1]}",
  "single_product_id_first_is_variation":"{column2[1]}",
  "is_update_sku":"0","is_update_title":"0",
  "is_update_custom_fields":"0","is_update_acf":"0",
  "is_multiple_product_type":"yes",
  "is_product_visibility":"xpath",
  "single_product_visibility":"{visibility[1]}",
  "product_visibility_xpath":"{visibility[1]}",
  "is_update_content":"0","is_update_categories":"0",
  "is_update_parent":"0","is_update_attributes":"0",
  "is_update_status":"0","is_update_product_type":"0",
  "is_update_attachments":"0",
}
ALLOWLIST={
 "unique_key","single_product_sku","single_product_id_first_is_parent_id",
 "single_product_first_is_parent_id_parent_sku","single_product_id_first_is_variation",
 "single_product_type","single_product_regular_price","single_product_sale_price",
 "first_is_parent","matching_parent","variable_sku","is_update_sku","is_update_title",
 "is_update_custom_fields","update_custom_fields_logic","is_update_acf",
 "update_acf_logic","is_multiple_product_type","multiple_product_type",
 "is_product_visibility","single_product_visibility","product_visibility_xpath",
 "is_using_new_product_import_options","is_update_products","is_update_price",
 "is_update_regular_price","is_update_sale_price","is_update_catalog_visibility",
 "is_update_content","is_update_categories","is_update_parent","is_update_attributes",
 "is_update_status","is_update_product_type","is_update_attachments",
 "update_all_data","grouping_indicator","create_new_product_if_no_parent",
 "woo_add_on_version"
}

def evaluate(contract, source):
    if contract.get("contract_version")!="1.2.0":
        raise ValueError("ACTIVE_IMPORT_CONTRACT_VERSION_DRIFT")
    if source.get("provenance")!="EXACT_OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE_PRIVATE_BYTE_INSPECTION":
        raise ValueError("DATED_SOURCE_CLASSIFICATION_DRIFT")
    if source.get("zip",{}).get("sha256")!=ZIP_SHA or source.get("zip",{}).get("size")!=77959:
        raise ValueError("DATED_SOURCE_IDENTITY_DRIFT")
    embedded=source.get("embedded",{})
    if embedded.get("csv_sha256")!=CSV_SHA or embedded.get("template_sha256")!=TEMPLATE_SHA:
        raise ValueError("DATED_EMBEDDED_IDENTITY_DRIFT")
    rows=source.get("csv",{})
    if (rows.get("columns"),rows.get("data_rows"),rows.get("simple_rows"),
        rows.get("variable_rows"),rows.get("zero_price_hidden_rows"),
        rows.get("nonblank_parent_sku_count"))!=(95,876,53,823,355,822):
        raise ValueError("DATED_SOURCE_CENSUS_DRIFT")
    opts=source.get("template_options_sanitized",{})
    if set(opts)!=ALLOWLIST:
        raise ValueError("DATED_TEMPLATE_ALLOWLIST_DRIFT")
    expected_source={"unique_key":"{column1[1]}","single_product_sku":"{column2[1]}",
     "single_product_id_first_is_parent_id":"{column8[1]}",
     "single_product_first_is_parent_id_parent_sku":"{column28[1]}",
     "single_product_regular_price":"{price[1]}",
     "single_product_sale_price":"{column10[1]}",
     "single_product_id_first_is_variation":"{column3[1]}",
     "woo_add_on_version":"4.0.6"}
    for key,value in expected_source.items():
        if opts.get(key)!=value:raise ValueError("DATED_MATERIAL_OPTION_DRIFT:"+key)
    flags=contract["matching_and_update_behavior"]["all_update_flags"]
    for key in ["is_update_sku","is_update_title","is_update_custom_fields",
                "is_update_acf","is_update_content","is_update_categories",
                "is_update_parent","is_update_attributes","is_update_status",
                "is_update_product_type","is_update_attachments"]:
        if str(flags.get(key))!="0":raise ValueError("ACTIVE_PROTECTED_FIELD_POLICY_DRIFT:"+key)
    if contract["matching_and_update_behavior"]["update_mode"]["first_is_parent"]!="no":
        raise ValueError("ACTIVE_PARENT_ID_POLICY_DRIFT")
    wc=contract["material_mappings"]["woocommerce"]
    bindings={
       "unique_key":"{column1[1]}",
       "single_product_sku":wc["sku"]["expression"],
       "single_product_id_first_is_parent_id":wc["variation_grouping"]["expression"],
       "single_product_first_is_parent_id_parent_sku":wc["parent_sku"]["expression"],
       "single_product_regular_price":wc["regular_price"]["expression"],
       "single_product_sale_price":wc["sale_price"]["expression"],
       "single_product_visibility":wc["catalog_visibility"]["expression"]
    }
    comparison=[]
    for key,value in opts.items():
        target=TARGET_NATIVE.get(key,bindings.get(key))
        if key=="matching_parent":
            target="NATIVE_FIRST_IS_PARENT_ID_WITH_EXPLICIT_GROUP_ID_AND_PARENT_SKU"
            finding="MODE_REQUIRES_COMBINED_OPTION_AND_RUNTIME_PROOF"
        elif key=="single_product_id_first_is_variation":
            finding="EXACT_EXPORT_REFERS_TO_ASCII_COLUMN3_ABSENT_FROM_RAW_CSV"
        elif key=="is_multiple_product_type":
            finding="MIXED_PRODUCT_TYPE_EFFECTIVE_MODE_REQUIRES_NATIVE_SOURCE_PROOF"
        elif key in ("update_custom_fields_logic","update_acf_logic"):
            target="NO_UPDATE_TO_EXISTING_PROTECTED_FIELDS"
            finding="SOURCE_FULL_UPDATE_RISK"
        elif key=="product_visibility_xpath":
            finding="EXACT_EXPORT_OPTION_ABSENT_NATIVE_VISIBILITY_TEST_REQUIRED"
        elif key=="woo_add_on_version":
            finding="DATED_TEMPLATE_VERSION_NOT_INSTALLED_RUNTIME_PROOF"
        else:
            finding="MATCHES_CONTRACT_OR_LAB_CANDIDATE" if target is not None and str(value)==str(target) else "DIFF_OR_NO_ACTIVE_CONTRACT_BINDING"
        comparison.append({
          "option":key,"dated_export_allowlisted_value":value,
          "target_or_lab_candidate":target,
          "finding":finding,
          "provenance":"EXACT_PRIVATE_ZIP_STATIC_READBACK_SHA256_BOUND",
        })
    if source["csv"].get("ascii_Column3_header_present") is not False or source["csv"].get("persian_digit_Column۳_header_present") is not True:
        raise ValueError("DATED_COLUMN3_HEADER_SCRIPT_IDENTITY_DRIFT")
    return {
      "classification":"OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE",
      "zip_sha256":ZIP_SHA,
      "source_values":"SOURCE_VERIFIED_ALLOWLIST_ONLY_ORIGINAL_NOT_IN_CI",
      "active_contract":"1.2.0",
      "option_comparison":comparison,
      "ascii_column3_mapping_anomaly":"STATIC_CONFIRMED_RUNTIME_EFFECT_NOT_PROVEN",
      "group_less_row":"SOURCE_VERIFIED_ROW_757_NOT_PARENT_BY_GENERATED_SKU_UNRESOLVED_POLICY",
      "EXACT_SWITCHLAND_CONSUMER":"NOT_PROVEN",
      "C1_PROVIDER_NATIVE_COMMA_CSV":"NOT_PROVEN",
      "C2_PROVIDER_NATIVE_XLSX":"NOT_PROVEN",
      "PRODUCTION_EQUIVALENCE":"NOT_PROVEN",
    }

def main():
    source=json.loads(ATTESTATION.read_text(encoding="utf-8"))
    contract=json.loads(CONTRACT.read_text(encoding="utf-8"))
    result=evaluate(contract,source)
    out=Path(os.environ.get("LAB_OUT","/tmp"))
    out.mkdir(parents=True,exist_ok=True)
    (out/REPORT_NAME).write_text(json.dumps(result,indent=2,ensure_ascii=False)+"\n",encoding="utf-8")
    print("DATED_PRIVATE_SOURCE_ALLOWLIST_CONTRACT_RECONCILIATION_PASS_NOT_NATIVE_CSV")

if __name__=="__main__":
    main()
