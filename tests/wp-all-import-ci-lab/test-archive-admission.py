#!/usr/bin/env python3
"""Exercise content-aware archive admission through the real Git index."""
import gzip
import io
import lzma
from pathlib import Path
import subprocess
import sys
import tarfile
import unittest
import zipfile

ROOT = Path(__file__).resolve().parents[2]
GUARD = ROOT / "tests/wp-all-import-ci-lab/check-public-boundary.py"
FIXTURE_DIR = "tests/fixtures/wp-all-import-packages"
TEST_DIR = "tests/wp-all-import-ci-lab"


def archive_bytes(kind):
    if kind == "zip":
        result = io.BytesIO()
        with zipfile.ZipFile(result, "w", compression=zipfile.ZIP_DEFLATED) as archive:
            archive.writestr("proof.txt", "synthetic archived fixture")
        return result.getvalue()
    if kind.startswith("tar"):
        result = io.BytesIO()
        mode = {"tar": "w", "tar-gz": "w:gz", "tar-xz": "w:xz", "tar-bz2": "w:bz2"}[kind]
        with tarfile.open(fileobj=result, mode=mode) as archive:
            data = b"synthetic tar member"
            info = tarfile.TarInfo("proof.txt")
            info.size = len(data)
            archive.addfile(info, io.BytesIO(data))
        return result.getvalue()
    if kind == "gzip":
        return gzip.compress(b"synthetic gzip stream")
    if kind == "xz":
        return lzma.compress(b"synthetic xz stream")
    if kind == "7z-signature":
        return b"7z\xbc\xaf\x27\x1c" + b"\x00" * 32
    if kind == "rar4-signature":
        return b"Rar!\x1a\x07\x00" + b"\x00" * 32
    if kind == "rar5-signature":
        return b"Rar!\x1a\x07\x01\x00" + b"\x00" * 32
    raise ValueError(kind)


class ArchiveAdmissionTests(unittest.TestCase):
    def run_guard(self):
        return subprocess.run(
            [sys.executable, str(GUARD)], cwd=ROOT, capture_output=True, text=True,
            check=False,
        )

    def stage_and_run(self, relative_path, contents):
        path = ROOT / relative_path
        self.assertFalse(path.exists(), f"test path already exists: {relative_path}")
        path.write_bytes(contents)
        try:
            subprocess.run(["git", "add", "-f", "--", relative_path], cwd=ROOT, check=True)
            return self.run_guard()
        finally:
            subprocess.run(["git", "reset", "-q", "HEAD", "--", relative_path], cwd=ROOT, check=True)
            path.unlink(missing_ok=True)

    def assert_rejected(self, name, kind, directory=TEST_DIR):
        relative = f"{directory}/_synthetic_{name}"
        result = self.stage_and_run(relative, archive_bytes(kind))
        self.assertNotEqual(result.returncode, 0, name)
        self.assertIn("WPAI_FIXTURE_ADMISSION_FAIL", result.stderr, name)
        self.assertIn("archive", result.stderr.lower(), name)
        self.assertIn(relative, result.stderr, name)

    def test_authorized_archives(self):
        result = self.run_guard()
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertIn("tracked_exact_packages=2 archive_allowlist=4", result.stdout)

    def test_tgz(self):
        self.assert_rejected("extra.tgz", "tar-gz")

    def test_tar_xz(self):
        self.assert_rejected("extra.tar.xz", "tar-xz")

    def test_zip_renamed_unrelated_suffix(self):
        self.assert_rejected("renamed.unrelated", "zip")

    def test_extensionless_zip(self):
        self.assert_rejected("extensionless", "zip")

    def test_archive_inside_fixture_directory(self):
        self.assert_rejected("other.payload", "zip", FIXTURE_DIR)

    def test_extensionless_tar(self):
        self.assert_rejected("plain_tar", "tar")

    def test_gzip_no_zip_suffix(self):
        self.assert_rejected("gzip_payload.dat", "gzip")

    def test_xz_no_zip_suffix(self):
        self.assert_rejected("xz_payload.dat", "xz")

    def test_bzip2_compressed_tar(self):
        self.assert_rejected("bz2_payload.dat", "tar-bz2")

    def test_7z_signature(self):
        self.assert_rejected("7z_payload.dat", "7z-signature")

    def test_rar4_signature(self):
        self.assert_rejected("rar4_payload.dat", "rar4-signature")

    def test_rar5_signature(self):
        self.assert_rejected("rar5_payload.dat", "rar5-signature")

    def test_ordinary_tracked_file(self):
        result = self.stage_and_run(f"{TEST_DIR}/_synthetic_ordinary.dat", b"ordinary file, not an archive")
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_tar_magic_with_invalid_checksum_is_ordinary(self):
        data = bytearray(512)
        data[257:263] = b"xtarXX"
        data[148:156] = b"0000000\x00"
        result = self.stage_and_run(f"{TEST_DIR}/_synthetic_non_tar.dat", bytes(data))
        self.assertEqual(result.returncode, 0, result.stderr)


    # The two pre-existing allowlisted archives must be immutable at the
    # admission boundary, not merely permitted because their paths match.
    LEGACY_PROJECT_ZIP = (
        "references/current_package/v3.14.2/"
        "SwitchLand_Price_Engine_GPT_Project_Package_v3.14.2(1).zip"
    )
    LEGACY_RECOVERY_TAR = (
        "backups/recovery/SLPE-RECOVERY-BUNDLE-20260813-001/"
        "SLPE-RECOVERY-BUNDLE-20260813-001.tar.gz"
    )

    def mutate_legacy_and_assert_rejected(self, relative, replacement=None, *, untrack=False):
        archive = ROOT / relative
        original = archive.read_bytes()
        original_index = subprocess.check_output(
            ["git", "rev-parse", ":" + relative], cwd=ROOT, text=True,
        ).strip()
        self.assertEqual(self.run_guard().returncode, 0, "original four archives must pass")
        try:
            if untrack:
                subprocess.run(["git", "rm", "--cached", "-q", "--", relative], cwd=ROOT, check=True)
            elif replacement is None:
                archive.unlink()
            else:
                archive.write_bytes(replacement)
                subprocess.run(["git", "add", "-f", "--", relative], cwd=ROOT, check=True)
            observed = self.run_guard()
            self.assertNotEqual(observed.returncode, 0, relative)
            self.assertIn("WPAI_FIXTURE_ADMISSION_FAIL", observed.stderr, relative)
            self.assertIn(relative, observed.stderr, relative)
        finally:
            archive.write_bytes(original)
            subprocess.run(["git", "add", "-f", "--", relative], cwd=ROOT, check=True)
            restored_index = subprocess.check_output(
                ["git", "rev-parse", ":" + relative], cwd=ROOT, text=True,
            ).strip()
            self.assertEqual(restored_index, original_index, "Git index archive identity restored")
            self.assertEqual(
                subprocess.run(["git", "diff", "--quiet", "--", relative], cwd=ROOT).returncode,
                0, "working tree archive identity restored",
            )
            self.assertEqual(
                subprocess.run(["git", "diff", "--cached", "--quiet", "--", relative], cwd=ROOT).returncode,
                0, "staged archive identity restored",
            )

    def test_legacy_project_replaced_valid_zip(self):
        # Other three admitted archives remain unchanged: same-root bypass check.
        self.mutate_legacy_and_assert_rejected(self.LEGACY_PROJECT_ZIP, archive_bytes("zip"))

    def test_legacy_recovery_replaced_valid_tar_gz(self):
        # Other three admitted archives remain unchanged: inverse bypass check.
        self.mutate_legacy_and_assert_rejected(self.LEGACY_RECOVERY_TAR, archive_bytes("tar-gz"))

    def test_legacy_project_changed_bytes_same_path(self):
        path = ROOT / self.LEGACY_PROJECT_ZIP
        self.mutate_legacy_and_assert_rejected(self.LEGACY_PROJECT_ZIP, path.read_bytes() + b"changed")

    def test_legacy_recovery_changed_bytes_same_path(self):
        path = ROOT / self.LEGACY_RECOVERY_TAR
        self.mutate_legacy_and_assert_rejected(self.LEGACY_RECOVERY_TAR, path.read_bytes() + b"changed")

    def test_legacy_project_missing(self):
        self.mutate_legacy_and_assert_rejected(self.LEGACY_PROJECT_ZIP)

    def test_legacy_recovery_missing(self):
        self.mutate_legacy_and_assert_rejected(self.LEGACY_RECOVERY_TAR)

    def test_legacy_project_missing_from_index(self):
        self.mutate_legacy_and_assert_rejected(self.LEGACY_PROJECT_ZIP, untrack=True)

    def test_legacy_recovery_missing_from_index(self):
        self.mutate_legacy_and_assert_rejected(self.LEGACY_RECOVERY_TAR, untrack=True)


    def test_zzz_all_mutations_restored_clean_checkout(self):
        status = subprocess.run(
            ["git", "status", "--porcelain", "--untracked-files=all"],
            cwd=ROOT, capture_output=True, text=True, check=True,
        )
        self.assertEqual(
            status.stdout, "",
            "mutation tests must restore all tracked, staged and untracked paths",
        )


if __name__ == "__main__":
    unittest.main(verbosity=2)
