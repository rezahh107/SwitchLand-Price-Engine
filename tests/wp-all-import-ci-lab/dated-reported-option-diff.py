#!/usr/bin/env python3
"""Version-bound, REDACTED comparison of Owner-REPORTED dated options to active contract.

No original dated Bundle is read here. This never promotes reported option values
to verified exported facts. The native LAB is responsible for measured behavior.
"""
import json
import os
from pathlib import Path

ROOT=Path(__file__).resolve().parents[2]
CONTRACT=ROOT/"references/current_package/v3.14.2/extracted/PROJECT_SOURCES/SLPE_WP_ALL_IMPORT_CONTRACT_v1.2.0.json"
REPORT_NAME="dated-reported-options-comparison.json"

# Value source: user-stated October 4 observations, not inspected ZIP.
REPORTED={
  "unique_key":"{column1[1]}",
  "single_product_sku":"{column2[1]}",
  "single_product_id_first_is_parent_id":"{column8[1]}",
  "single_product_first_is_parent_id_parent_sku":"{column28[1]}",
  "single_product_regular_price":"{price[1]}",
  "single_product_sale_price":"{column10[1]}",
  "first_is_parent":"yes",
  "matching_parent":"first_is_parent_id",
  "variable_sku":"",
  "is_update_sku":1,
  "is_update_title":1,
  "is_update_custom_fields":1,
  "custom_fields_update_logic":"full_update",
  "is_update_acf":1,
  "acf_update_logic":"full_update",
  "is_multiple_product_type":"no",
  "is_product_visibility":"xpath",
  "single_product_visibility":"{visibility[1]}",
  "product_visibility_xpath":None,
}
# Native LAB option values are distinct from Owner target conceptual expressions.
LAB_TARGET={
  "first_is_parent":"no",
  "matching_parent":"first_is_parent_id", # exact add-on mode, not conceptual contract enum
  "variable_sku":"{column2[1]}",
  "is_update_sku":0,
  "is_update_title":0,
  "is_update_custom_fields":0,
  "is_update_acf":0,
  "is_multiple_product_type":"yes", # fixed product type in separate simple/variable synthetic jobs
  "is_product_visibility":"xpath",
  "single_product_visibility":"{visibility[1]}",
  "product_visibility_xpath":"{visibility[1]}",
}

def evaluate(contract):
    if contract.get("contract_version")!="1.2.0":
        raise ValueError("ACTIVE_IMPORT_CONTRACT_VERSION_DRIFT")
    mu=contract["matching_and_update_behavior"]
    mm=contract["material_mappings"]
    flags=mu["all_update_flags"]
    expected_flags={"is_update_sku":"0","is_update_title":"0",
                    "is_update_custom_fields":"0","is_update_acf":"0",
                    "is_update_catalog_visibility":"1","is_update_regular_price":"1",
                    "is_update_sale_price":"1"}
    for k,v in expected_flags.items():
        if str(flags.get(k))!=v:
            raise ValueError("ACTIVE_IMPORT_UPDATE_POLICY_DRIFT:"+k)
    if mu["update_mode"]["first_is_parent"]!="no":
        raise ValueError("ACTIVE_FIRST_ROW_PARENT_POLICY_DRIFT")
    if mm["unique_identifier"]["expression"]!="{column1[1]}" or (
       mm["woocommerce"]["parent_sku"]["expression"]!="{column28[1]}"
    ) or mm["woocommerce"]["variation_grouping"]["expression"]!="{column8[1]}":
        raise ValueError("ACTIVE_IDENTITY_MAPPING_POLICY_DRIFT")
    wc=mm["woocommerce"]
    expected_correspondences={
      "unique_key":"{column1[1]}",
      "single_product_sku":wc["sku"]["expression"],
      "single_product_id_first_is_parent_id":wc["variation_grouping"]["expression"],
      "single_product_first_is_parent_id_parent_sku":wc["parent_sku"]["expression"],
      "single_product_regular_price":wc["regular_price"]["expression"],
      "single_product_sale_price":wc["sale_price"]["expression"],
      "single_product_visibility":wc["catalog_visibility"]["expression"],
    }
    rows=[]
    for key, value in REPORTED.items():
        target=LAB_TARGET.get(key,expected_correspondences.get(key))
        if key=="matching_parent":
            target="conceptual=explicit_parent_sku;native=first_is_parent_id_WITH_PARENT_SKU_BINDING"
            disposition="NATIVE_MODE_SEMANTIC_NOT_STRING_EQUALITY"
        elif key in ("custom_fields_update_logic","acf_update_logic"):
            target="NOT_USED_FOR_PROTECTED_EXISTING_UPDATES"
            disposition="RISKY_FULL_UPDATE_REPORTED_NOT_NATIVE_ACF_PROVEN"
        elif key=="is_multiple_product_type":
            disposition="PRODUCT_TYPE_SELECTOR_REQUIRES_RUNTIME_QUALIFICATION"
        elif key=="is_product_visibility" or key=="single_product_visibility":
            disposition="POSITIVE_CORRESPONDENCE_REQUIRES_EFFECTIVE_MODE_CHECK"
        elif key=="product_visibility_xpath":
            disposition="REPORTED_MISSING_MAPPING_REQUIRES_RUNTIME_CHECK"
        else:
            disposition="REPORTED_EQUALS_CANDIDATE" if value==target else "REPORTED_DIFFERS_FROM_TARGET"
        rows.append({"option":key,"reported_dated_value":value,"candidate_or_contract_target":target,
                     "disposition":disposition,"source_classification":"OWNER_REPORTED_NOT_ZIP_VERIFIED"})
    return {
       "classification":"OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE",
       "input_bytes":"UNAVAILABLE_IN_THIS_EXECUTION",
       "source_of_observed_values":"OWNER_REPORTED_NOT_EXACT_EXPORT_READBACK",
       "active_contract_version":"1.2.0",
       "comparison":rows,
       "runtime_evidence":"SEE_INDEPENDENT_NATIVE_A_B_CI_REPORT",
       "group_less_variable_row_classification":"NOT_PROVEN_FROM_OWNER_REPORTED_COUNTS_ALONE",
       "EXACT_SWITCHLAND_CONSUMER":"NOT_PROVEN",
       "C1_PROVIDER_NATIVE_COMMA_CSV":"NOT_PROVEN",
       "C2_PROVIDER_NATIVE_XLSX":"NOT_PROVEN",
    }

def main():
    contract=json.loads(CONTRACT.read_text(encoding="utf-8"))
    output=evaluate(contract)
    outdir=Path(os.environ.get("LAB_OUT","/tmp"))
    outdir.mkdir(parents=True,exist_ok=True)
    (outdir/REPORT_NAME).write_text(json.dumps(output,indent=2,ensure_ascii=False)+"\n",encoding="utf-8")
    print("DATED_REPORTED_OPTIONS_RECONCILED_ONLY_NO_EXPORT_BYTES")

if __name__=="__main__":main()
