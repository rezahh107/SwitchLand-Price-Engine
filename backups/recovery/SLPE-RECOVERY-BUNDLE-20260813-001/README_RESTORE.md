# SLPE Recovery Bundle SLPE-RECOVERY-BUNDLE-20260813-001

This is an append-only independent backup candidate for RECOVERY_REBASELINE.

- Snapshot: 2026-08-13T16:40:01Z
- Workspace: 1HgQlekyWQLiGnQ_UgV_SIzJVF6L2kymd
- Current Master revision: 3004
- Current Master CSV SHA-256: ab7d8eaf37ee5b4046f4b955822e6d63d1d147f83e6403e629425d5e9e469fd3
- Objects: 19
- Missing historical provider IDs are recorded as missing and are not restored/recreated.

Restore verification: reconstruct the tar.gz bytes from repository base64 parts, verify the tar.gz SHA-256 in REPOSITORY_BUNDLE_MANIFEST.json, extract, then verify every object SHA-256 against BUNDLE_INVENTORY.json.
