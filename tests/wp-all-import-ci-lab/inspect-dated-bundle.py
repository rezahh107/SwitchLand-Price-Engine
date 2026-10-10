#!/usr/bin/env python3
"""Private, read-only dated WP All Import bundle inspector.

No extraction, network access or source row/template publication. Accept original
ZIP in an ephemeral local environment; JSON stdout contains aggregates only.
"""
import argparse
import csv
import hashlib
import io
import json
import os
import sys
import zipfile
from collections import Counter

ZIP_SHA = "6cb9f29fd4b798c38c8dcc05a3b28e01858c95f7847488fa9257bb91176a62c2"
CSV_SHA = "626da8979ce64ec5703ff7172aa0870b46870bc6c6dd9fe72ca1dbe7332808f8"
TEMPLATE_SHA = "fa5af37b144c0f32d4a052672d1c406520c44d95da0f331039b2c70ea1ce121a"
ZIP_SIZE = 77959
MAX_MEMBER_BYTES = 12 * 1024 * 1024


def sha256(value):
    return hashlib.sha256(value).hexdigest()


def inspect(path):
    if not os.path.isfile(path):
        raise ValueError("ORIGINAL_DATED_BUNDLE_NOT_AVAILABLE")
    if os.path.getsize(path) != ZIP_SIZE:
        raise ValueError("ZIP_SIZE_MISMATCH")
    with open(path, "rb") as fh:
        raw = fh.read()
    if sha256(raw) != ZIP_SHA:
        raise ValueError("ZIP_SHA256_MISMATCH")
    with zipfile.ZipFile(io.BytesIO(raw)) as archive:
        # Bound decompression *before* testzip() or read(): fail closed on bombs.
        members = [i for i in archive.infolist() if not i.is_dir()]
        if not members or len(members) > 10:
            raise ValueError("ZIP_MEMBER_COUNT_INVALID")
        for item in members:
            if item.file_size > MAX_MEMBER_BYTES or item.flag_bits & 1:
                raise ValueError("ZIP_MEMBER_UNSAFE_OR_ENCRYPTED")
            if item.filename.startswith("/") or any(seg == ".." for seg in item.filename.replace("\\", "/").split("/")):
                raise ValueError("ZIP_MEMBER_PATH_UNSAFE")
        if sum(i.file_size for i in members) > MAX_MEMBER_BYTES * 2:
            raise ValueError("ZIP_TOTAL_UNCOMPRESSED_SIZE_TOO_LARGE")
        if len({i.filename for i in members}) != len(members):
            raise ValueError("ZIP_DUPLICATE_MEMBER_NAME")
        errors = archive.testzip()
        if errors:
            raise ValueError("ZIP_MEMBER_INTEGRITY_ERROR")
        entries = [(i.filename, archive.read(i)) for i in members]
    candidates = {sha256(blob): (name, blob) for name, blob in entries}
    if CSV_SHA not in candidates or TEMPLATE_SHA not in candidates:
        raise ValueError("EMBEDDED_CSV_OR_TEMPLATE_SHA256_MISMATCH")
    csv_name, csv_bytes = candidates[CSV_SHA]
    template_name, template_bytes = candidates[TEMPLATE_SHA]
    if not csv_bytes.startswith(b"\xef\xbb\xbf") or b"\r\n" in csv_bytes:
        raise ValueError("CSV_ENCODING_OR_EOL_MISMATCH")
    # Decode strictly; quoted multi-line fields are preserved by Python csv.reader.
    reader = csv.reader(io.StringIO(csv_bytes.decode("utf-8-sig"), newline=""), delimiter=";", quotechar='"')
    header = next(reader, None)
    if not header or len(header) != 95 or len(set(header)) != 95:
        raise ValueError("CSV_95_COLUMN_SCHEMA_INVALID")
    expected = {"Column1", "Column2", "Column8", "Column27", "Column28", "Price", "visibility"}
    if not expected.issubset(set(header)):
        raise ValueError("CSV_REQUIRED_HEADERS_MISSING")
    records = []
    for row in reader:
        if len(row) != len(header):
            raise ValueError("CSV_DATA_ROW_WIDTH_INVALID")
        records.append(dict(zip(header, row)))
    ids = [r["Column1"] for r in records]
    blank_ids = sum(not v.strip() for v in ids)
    duplicate_ids = len(ids) - len(set(ids))
    zero = [r for r in records if r["Price"].strip() == "0"]
    inconsistent_zero = sum(r["visibility"].strip() != "hidden" for r in zero)
    is_variable = lambda r: r["Column27"].strip().lower() in {"variable", "variation"}
    variables = [r for r in records if is_variable(r)]
    simple = [r for r in records if r["Column27"].strip().lower() == "simple"]
    group_less = [r for r in variables if not r["Column8"].strip() and not r["Column28"].strip()]
    bad_parent = sum(
        bool(r["Column28"].strip()) and r["Column28"] != "PARENT_" + r["Column8"]
        for r in records
    )
    populated_parent = sum(bool(r["Column28"].strip()) for r in records)
    # The exact WP All Import bundle is JSON: a one-item array with options.
    # Inspect only an allowlist; NEVER emit raw exported template fields or rows.
    template_json = json.loads(template_bytes.decode("utf-8-sig"))
    if (not isinstance(template_json,list) or len(template_json)!=1
        or not isinstance(template_json[0],dict)
        or not isinstance(template_json[0].get("options"),dict)):
        raise ValueError("DATED_TEMPLATE_EXPORT_STRUCTURE_INVALID")
    options=template_json[0]["options"]
    material={
       "unique_key":"{column1[1]}",
       "single_product_sku":"{column2[1]}",
       "single_product_id_first_is_parent_id":"{column8[1]}",
       "single_product_first_is_parent_id_parent_sku":"{column28[1]}",
       "single_product_id_first_is_variation":"{column3[1]}",
       "first_is_parent":"yes",
       "matching_parent":"first_is_parent_id",
       "variable_sku":"",
       "is_update_sku":"1",
       "is_update_title":"1",
       "is_update_custom_fields":"1",
       "update_custom_fields_logic":"full_update",
       "is_update_acf":"1",
       "update_acf_logic":"full_update",
       "is_multiple_product_type":"no",
       "multiple_product_type":"variable",
       "single_product_type":"{column27[1]}",
       "is_product_visibility":"xpath",
       "single_product_visibility":"{visibility[1]}",
       "woo_add_on_version":"4.0.6",
    }
    verified_material = all(options.get(k)==v for k,v in material.items())
    if "Column3" in header or "Column۳" not in header:
        raise ValueError("DATED_THIRD_COLUMN_IDENTITY_DRIFT")
    orphan = group_less[0] if len(group_less)==1 else None
    orphan_data_row = (records.index(orphan)+1) if orphan is not None else None
    orphan_has_generated_parent_identity=(
        bool(orphan) and orphan["Column2"].startswith("PARENT_")
        and orphan["Column2"] in {r["Column28"] for r in records if r["Column28"]}
    )
    result = {
        "source_classification": "OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE",
        "date_provenance": "2026-10-04_FROM_FILENAME_NOT_CURRENT_ACTIVE_PROOF",
        "zip_sha256_verified": True,
        "zip_size_bytes": len(raw),
        "member_count": len(entries),
        "embedded_csv_sha256_verified": True,
        "embedded_template_sha256_verified": True,
        "template_json_valid": True,
        "template_json_type": "list",
        "template_option_count": len(options),
        "reported_woo_addon_template_version": options.get("woo_add_on_version"),
        "dated_material_options_match_whitelist": verified_material,
        "ascii_column3_header_present": "Column3" in header,
        "persian_digit_column3_header_present": "Column۳" in header,
        "third_column_binding_source_mismatch": (
             options.get("single_product_id_first_is_variation")=="{column3[1]}"
             and "Column3" not in header and "Column۳" in header
        ),
        "third_column_binding_runtime_impact": "NOT_PROVEN",
        "group_less_variable_data_row_ordinal": orphan_data_row,
        "group_less_variable_not_first": orphan_data_row!=1,
        "group_less_variable_has_generated_parent_identity": orphan_has_generated_parent_identity,
        "csv_utf8_bom_semicolon_lf": True,
        "csv_columns": len(header),
        "data_rows": len(records),
        "simple_rows": len(simple),
        "variable_rows": len(variables),
        "other_product_type_rows": len(records) - len(simple) - len(variables),
        "zero_price_rows": len(zero),
        "zero_price_visibility_mismatch": inconsistent_zero,
        "missing_column1": blank_ids,
        "duplicate_column1": duplicate_ids,
        "variable_without_group_and_parent_sku": len(group_less),
        "populated_parent_sku": populated_parent,
        "parent_sku_relation_mismatches": bad_parent,
        "expected_counts_match": (
            len(records)==876 and len(simple)==53 and len(variables)==823
            and len(zero)==355 and inconsistent_zero==0 and blank_ids==0
            and duplicate_ids==0 and len(group_less)==1 and populated_parent==822
            and bad_parent==0 and verified_material
            and orphan_data_row==757 and not orphan_has_generated_parent_identity
        ),
        "active_import_on_2026_10_10": "NOT_PROVEN",
        "installed_core_version": "NOT_PROVEN",
        "production_equivalence": "NOT_PROVEN",
    }
    return result


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("private_bundle_zip", help="private path, never commit or publish")
    args = parser.parse_args()
    try:
        result=inspect(args.private_bundle_zip)
        print(json.dumps(result, ensure_ascii=False, indent=2))
        return 0 if result["expected_counts_match"] else 1
    except (OSError, ValueError, zipfile.BadZipFile, RuntimeError) as exc:
        print(json.dumps({"classification":"OWNER_SUPPLIED_DATED_WPAI_IMPORT_BUNDLE",
                          "status":"NOT_VERIFIED","reason":str(exc)},ensure_ascii=False))
        return 2


if __name__ == "__main__":
    sys.exit(main())
