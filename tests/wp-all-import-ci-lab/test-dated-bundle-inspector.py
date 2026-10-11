#!/usr/bin/env python3
"""Synthetic fail-closed tests for the PRIVATE dated-bundle inspector."""
import csv
import hashlib
import importlib.util
import io
import json
import os
from pathlib import Path
import tempfile
import unittest
import zipfile

module_path = Path(__file__).with_name("inspect-dated-bundle.py")
spec = importlib.util.spec_from_file_location("dated_inspector", module_path)
probe = importlib.util.module_from_spec(spec)
spec.loader.exec_module(probe)


class PrivateBundleInspectorTests(unittest.TestCase):
    def generate(self, folder, *, bad_parent=False, duplicate_uid=False):
        headers = ["Column1", "Column2", "Column۳", "Column8", "Column27", "Column28",
                   "Price", "visibility"] + [f"Synthetic_{i}" for i in range(87)]
        self.assertEqual(len(headers),95)
        out = io.StringIO(newline="")
        w=csv.writer(out,delimiter=";",lineterminator="\n")
        w.writerow(headers)
        rows = [
          ["id-1","sku-1","Synthetic Label","","simple","","13","visible"],
          ["id-2","sku-2","Synthetic Label","A","variable","PARENT_A","0","hidden"],
          ["id-2" if duplicate_uid else "id-3","sku-3","Synthetic Label","A","variable",
           "PARENT_X" if bad_parent else "PARENT_A","22","visible"],
          ["id-4","sku-4","Synthetic Label","","variable","","12","visible"],
        ]
        for row in rows:w.writerow(row+[""]*87)
        csv_data=b"\xef\xbb\xbf"+out.getvalue().encode("utf-8")
        template=json.dumps([{"synthetic":True,"options":{
          "unique_key":"{column1[1]}",
          "single_product_sku":"{column2[1]}",
          "single_product_id_first_is_parent_id":"{column8[1]}",
          "single_product_first_is_parent_id_parent_sku":"{column28[1]}",
          "single_product_id_first_is_variation":"{column3[1]}",
          "first_is_parent":"yes","matching_parent":"first_is_parent_id",
          "variable_sku":"","is_update_sku":"1","is_update_title":"1",
          "is_update_custom_fields":"1","update_custom_fields_logic":"full_update",
          "is_update_acf":"1","update_acf_logic":"full_update",
          "is_multiple_product_type":"no","multiple_product_type":"variable",
          "single_product_type":"{column27[1]}","is_product_visibility":"xpath",
          "single_product_visibility":"{visibility[1]}",
          "woo_add_on_version":"4.0.6",
        }}]).encode("utf-8")
        file=Path(folder)/"synthetic-test.zip"
        with zipfile.ZipFile(file,"w",compression=zipfile.ZIP_DEFLATED) as z:
            z.writestr("synthetic.csv",csv_data)
            z.writestr("synthetic-template.txt",template)
        return file,csv_data,template

    def run_probe(self,file,csv_data,template):
        saved=(probe.ZIP_SIZE,probe.ZIP_SHA,probe.CSV_SHA,probe.TEMPLATE_SHA)
        try:
            raw=file.read_bytes()
            probe.ZIP_SIZE=len(raw)
            probe.ZIP_SHA=hashlib.sha256(raw).hexdigest()
            probe.CSV_SHA=hashlib.sha256(csv_data).hexdigest()
            probe.TEMPLATE_SHA=hashlib.sha256(template).hexdigest()
            return probe.inspect(str(file))
        finally:
            (probe.ZIP_SIZE,probe.ZIP_SHA,probe.CSV_SHA,probe.TEMPLATE_SHA)=saved

    def test_positive_synthetic_and_orphan_classification(self):
        with tempfile.TemporaryDirectory() as d:
            f,c,t=self.generate(d)
            r=self.run_probe(f,c,t)
            self.assertEqual(r["csv_columns"],95)
            self.assertEqual(r["data_rows"],4)
            self.assertEqual(r["variable_without_group_and_parent_sku"],1)
            self.assertEqual(r["zero_price_rows"],1)
            self.assertEqual(r["populated_parent_sku"],2)
            self.assertEqual(r["parent_sku_relation_mismatches"],0)
            self.assertTrue(r["template_json_valid"])
            self.assertFalse(r["expected_counts_match"])
            self.assertEqual(r["active_import_on_2026_10_10"],"NOT_PROVEN")
            self.assertNotIn("sku-1",json.dumps(r))
            self.assertNotIn("synthetic.csv",json.dumps(r))

    def test_mismatching_zip_identity_fails_closed(self):
        with tempfile.TemporaryDirectory() as d:
            f,c,t=self.generate(d)
            with self.assertRaisesRegex(ValueError,"ZIP_SIZE_MISMATCH"):
                probe.inspect(str(f))
            with self.assertRaisesRegex(ValueError,"ZIP_SHA256_MISMATCH"):
                old=probe.ZIP_SIZE
                probe.ZIP_SIZE=f.stat().st_size
                try:probe.inspect(str(f))
                finally:probe.ZIP_SIZE=old

    def test_uncompressed_zip_bomb_rejected_before_integrity_scan(self):
        with tempfile.TemporaryDirectory() as d:
            file=Path(d)/"oversized.zip"
            with zipfile.ZipFile(file,"w",compression=zipfile.ZIP_DEFLATED) as z:
                z.writestr("very-large.bin",b"A"*(probe.MAX_MEMBER_BYTES+1))
            old=(probe.ZIP_SIZE,probe.ZIP_SHA)
            try:
                raw=file.read_bytes()
                probe.ZIP_SIZE=len(raw)
                probe.ZIP_SHA=hashlib.sha256(raw).hexdigest()
                with self.assertRaisesRegex(ValueError,"ZIP_MEMBER_UNSAFE_OR_ENCRYPTED"):
                    probe.inspect(str(file))
            finally:
                probe.ZIP_SIZE,probe.ZIP_SHA=old

    def test_ascii_column3_header_not_equivalent_to_persian_digit(self):
        with tempfile.TemporaryDirectory() as d:
            f,c,t=self.generate(d)
            changed=c.replace("Column۳".encode("utf-8"),b"Column3",1)
            with zipfile.ZipFile(f,"w",compression=zipfile.ZIP_DEFLATED) as z:
                z.writestr("synthetic.csv",changed)
                z.writestr("synthetic-template.txt",t)
            with self.assertRaisesRegex(ValueError,"DATED_THIRD_COLUMN_IDENTITY_DRIFT"):
                self.run_probe(f,changed,t)

    def test_duplicate_identity_and_parent_mismatch_detected(self):
        for kw,field in [(dict(duplicate_uid=True),"duplicate_column1"),
                          (dict(bad_parent=True),"parent_sku_relation_mismatches")]:
            with tempfile.TemporaryDirectory() as d:
                f,c,t=self.generate(d,**kw)
                self.assertEqual(self.run_probe(f,c,t)[field],1)


if __name__=="__main__":
    unittest.main()
