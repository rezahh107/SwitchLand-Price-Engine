#!/usr/bin/env python3
"""Reject public admission of the two licensed-package identities, at any path."""
import hashlib
import json
import os
from pathlib import Path
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / "tests/fixtures/wp-all-import-packages/manifest.json"
FIXTURE_PREFIX = "tests/fixtures/wp-all-import-packages/"
LOCKED = {
    "wp-all-import-pro": ("wp-all-import-pro.zip", 4370010, "8c38753da981293432c5ff2fff4124ccb9cf5c7f0a90e964f1fbb222c09772bb"),
    "wpai-woocommerce-add-on": ("wpai-woocommerce-add-on_4.0.6.zip", 437099, "6959464478610568937cc51d118a832ec3515ed501b3bb083dbe68894f8bda6a"),
}

def main():
    errors = []
    try:
        manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
    except (OSError, ValueError) as exc:
        print("PUBLIC_FIXTURE_BOUNDARY_FAIL malformed or missing public manifest: " + str(exc), file=sys.stderr)
        return 1
    if manifest.get("production_equivalence") != "NOT_PROVEN":
        errors.append("production equivalence must remain NOT_PROVEN")
    if manifest.get("acquisition_policy") != "EXTERNAL_AUTHORIZED_EXACT_FIXTURES_NO_NETWORK_FALLBACK":
        errors.append("acquisition policy changed; do not silently authorize downloads")
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
    sizes = {entry[1]: entry[2] for entry in LOCKED.values()}
    paths = subprocess.check_output(["git", "-C", str(ROOT), "ls-files", "-z"]).split(b"\0")
    for raw in paths:
        if not raw:
            continue
        rel = os.fsdecode(raw)
        path = ROOT / rel
        if rel.startswith(FIXTURE_PREFIX) and (rel.lower().endswith((".zip", ".phar", ".7z", ".tar", ".tar.gz")) or path.is_symlink()):
            errors.append("tracked package archive or link in public fixture directory: " + rel)
        if not path.is_file() or path.is_symlink():
            continue
        try:
            if path.stat().st_size in sizes:
                with path.open("rb") as f:
                    actual_sha = hashlib.file_digest(f, "sha256").hexdigest()
                if actual_sha == sizes[path.stat().st_size]:
                    errors.append("original commercial binary reintroduced under tracked path: " + rel)
        except OSError as exc:
            errors.append("unreadable tracked path: " + rel + " (" + str(exc) + ")")
    for error in errors:
        print("PUBLIC_FIXTURE_BOUNDARY_FAIL " + error, file=sys.stderr)
    if errors:
        return 1
    print("PUBLIC_FIXTURE_BOUNDARY_PASS no tracked Pro binaries; identity locked; production_equivalence=NOT_PROVEN")
    return 0

if __name__ == "__main__":
    sys.exit(main())
