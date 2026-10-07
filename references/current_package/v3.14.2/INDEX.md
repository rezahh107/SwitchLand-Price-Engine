# Current Package Baseline — v3.14.2

Status: `CURRENT_REPOSITORY_BASELINE_IDENTITY`

This index binds the repository governance rebaseline to the exact Owner-supplied package inspected on 2026-10-07.

## Package identity

`SwitchLand_Price_Engine_GPT_Project_Package_v3.14.2(1).zip`

SHA-256: `23c89b374b2728bf2606a3770f2a6ff6bb16387294f83194a33b0d8b9b084c06`

Observed size: `502115` bytes.

## Extracted file integrity

See `SHA256SUMS` for the exact extracted-file inventory. The actual supplied ZIP was verified before this rebaseline: ZIP SHA and every listed extracted-file SHA matched the Owner-provided expected values, and all five JSON sources parsed successfully.

## Known v3.14.2 Instructions regressions

Recorded, not repaired by this governance task:

1. Prepared Project Start Card regression.
2. One Simple Human Approval interaction kernel is too dependent on source retrieval.

Owner preference is to avoid a standalone Instructions-only successor and coordinate these repairs with the successor after consumer qualification unless they become a blocker.

## Publication status / tooling deviation

The execution environment could read and hash the Owner-supplied ZIP locally, but the available GitHub write connector has no file-path/binary-upload seam from that local sandbox. Therefore this PR records the exact verified package identity and inventory but does **not** claim that the ZIP/extracted payload bytes were embedded in Git.

This is a tooling limitation, not a package mismatch. No substitute archive, re-encoded binary, or silently edited package source is committed.

Until exact archive bytes are added through a byte-preserving repository upload path, repository package-byte archival is `NOT_EMBEDDED_BUT_EXACT_SOURCE_IDENTITY_VERIFIED`. Live operations still resolve Workspace Manifest -> live `CURRENT_SET`.
