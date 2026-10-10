# WP All Import CI Lab — exact repository-tracked fixtures

The public repository includes the two **exact, unchanged Owner-supplied** ZIPs
in `tests/fixtures/wp-all-import-packages/`. This is an explicit Owner distribution
decision; it is not a legal determination of third-party redistribution rights.

```sh
python3 tests/wp-all-import-ci-lab/check-public-boundary.py
php tests/wp-all-import-ci-lab/verify-package-fixtures.php
```

No Google Drive, credential, conversation attachment, package download, fallback,
or alternative version is required after checkout. The exact manifest locks version,
filename, size, SHA-256, root directory and plugin name/version headers. The Python
guard checks that both original Git blob IDs remain tracked, and allows only the
two exact WP All Import ZIPs plus the two pre-existing SwitchLand public archives.
The PHP verifier rejects missing, extra, corrupt or modified packages, manifest
drift, unexpected ZIP roots and incorrect plugin headers. CI verifies the real ZIPs
and exercises negative mutations without publishing additional artifacts.

The active Import Contract v1.2.0 remains unchanged; Add-On 4.0.6 remains an
`OWNER_SUPPLIED_LAB_CANDIDATE`, not Production version evidence (4.0.5 in the
contract). `production_equivalence = NOT_PROVEN`; C1/C2 remain NOT_PROVEN.
The WP All Import WordPress/WooCommerce CI Lab is not implemented by this change.

Historical PR #8 stays closed and unmerged; historical availability of its
objects remains `NOT_PROVEN_CLEARED`. This successor stays on PR #9's ancestry
and references only the identical existing Git binary blobs.
