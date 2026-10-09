#!/usr/bin/env python3
"""Allow only exact repository-tracked WP All Import fixtures and known archives."""
import hashlib
import json
import os
from pathlib import Path
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / "tests/fixtures/wp-all-import-packages/manifest.json"
FIXTURE_PREFIX = "tests/fixtures/wp-all-import-packages/"
POLICY = "REPOSITORY_TRACKED_EXACT_FIXTURES_NO_NETWORK_FALLBACK"
ORIGINAL_BLOBS = {
    "wp-all-import-pro.zip": "7d22a2f2fc2c9b682457585cb95eb21ed1ddac5f",
    "wpai-woocommerce-add-on_4.0.6.zip": "43fc604ddf48e035ff90a9bdfd9f16744128dd46",
}
EXISTING_ARCHIVES = {
    "backups/recovery/SLPE-RECOVERY-BUNDLE-20260813-001/SLPE-RECOVERY-BUNDLE-20260813-001.tar.gz",
    "references/current_package/v3.14.2/SwitchLand_Price_Engine_GPT_Project_Package_v3.14.2(1).zip",
}
ARCHIVE_SUFFIXES = (".zip", ".phar", ".7z", ".tar", ".tar.gz")
LOCKED = {
    "wp-all-import-pro": ("wp-all-import-pro.zip", 4370010, "8c38753da981293432c5ff2fff4124ccb9cf5c7f0a90e964f1fbb222c09772bb"),
    "wpai-woocommerce-add-on": ("wpai-woocommerce-add-on_4.0.6.zip", 437099, "6959464478610568937cc51d118a832ec3515ed501b3bb083dbe68894f8bda6a"),
}

def main():
    errors = []
    try:
        manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
    except (OSError, ValueError) as exc:
        print("WPAI_FIXTURE_ADMISSION_FAIL malformed or missing public manifest: " + str(exc), file=sys.stderr)
        return 1
    if manifest.get("production_equivalence") != "NOT_PROVEN":
        errors.append("production equivalence must remain NOT_PROVEN")
    if manifest.get("acquisition_policy") != POLICY:
        errors.append("repository-tracked no-network acquisition policy drift")
    actual = manifest.get("packages")
    if not isinstance(actual, list) or len(actual) != len(LOCKED):
        errors.append("manifest package set mismatch")
    else:
        for package in actual:
            if not isinstance(package, dict) or package.get("id") not in LOCKED:
                errors.append("unexpected manifest package")
                continue
            fname, size, sha = LOCKED[package["id"]]
            if (package.get("filename"), package.get("size_bytes"), package.get("sha256")) != (fname, size, sha):
                errors.append("package identity changed for " + package["id"])
        if {p.get("id") for p in actual if isinstance(p, dict)} != set(LOCKED):
            errors.append("manifest IDs missing or duplicated")
    expected = {FIXTURE_PREFIX + data[0]: (data[1], data[2], ORIGINAL_BLOBS[data[0]]) for data in LOCKED.values()}
    allowed_archives = set(expected) | EXISTING_ARCHIVES
    required_paths = set(expected) | {FIXTURE_PREFIX + "manifest.json"}
    sizes = {v[0]: v[1] for v in expected.values()}
    try:
        tracked = [os.fsdecode(value) for value in subprocess.check_output(["git", "-C", str(ROOT), "ls-files", "-z"]).split(b"\0") if value]
    except (OSError, subprocess.CalledProcessError) as exc:
        errors.append("git index is not available: " + str(exc))
        tracked = []

    for rel in set(expected) - set(tracked):
        errors.append("required ZIP missing from Git index: " + rel)

    for rel in tracked:
        path = ROOT / rel
        if rel.startswith(FIXTURE_PREFIX) and rel not in required_paths:
            errors.append("unexpected tracked fixture: " + rel)
        if rel.lower().endswith(ARCHIVE_SUFFIXES) and rel not in allowed_archives:
            errors.append("archive outside explicit allowlist: " + rel)
        if rel.startswith(FIXTURE_PREFIX) and path.is_symlink():
            errors.append("fixture symlink forbidden: " + rel)
        if rel in expected:
            if not path.is_file():
                errors.append("required fixture absent: " + rel)
                continue
            size, sha, blob = expected[rel]
            try:
                if path.stat().st_size != size:
                    errors.append("fixture size drift: " + rel)
                    continue
                with path.open("rb") as handle:
                    actual = hashlib.file_digest(handle, "sha256").hexdigest()
                if actual != sha:
                    errors.append("fixture SHA drift: " + rel)
                tracked_blob = subprocess.check_output(["git", "-C", str(ROOT), "rev-parse", ":" + rel], text=True).strip()
                if tracked_blob != blob:
                    errors.append("fixture Git blob drift: " + rel)
            except (OSError, subprocess.CalledProcessError) as exc:
                errors.append("unreadable fixture: " + rel + " (" + str(exc) + ")")
        elif path.is_file() and not path.is_symlink():
            try:
                if path.stat().st_size in sizes:
                    with path.open("rb") as handle:
                        actual = hashlib.file_digest(handle, "sha256").hexdigest()
                    if actual == sizes[path.stat().st_size]:
                        errors.append("exact commercial ZIP duplicated outside fixture path: " + rel)
            except OSError as exc:
                errors.append("unreadable tracked file: " + rel + " (" + str(exc) + ")")
    for error in errors:
        print("WPAI_FIXTURE_ADMISSION_FAIL " + error, file=sys.stderr)
    if errors:
        return 1
    print("WPAI_FIXTURE_ADMISSION_PASS tracked_exact_packages=2 archive_allowlist=4 production_equivalence=NOT_PROVEN")
    return 0

if __name__ == "__main__":
    sys.exit(main())
