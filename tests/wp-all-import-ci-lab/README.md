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
The separately scoped consumer CI laboratory now lives in
`.github/workflows/wp-all-import-ci-lab.yml` with its synthetic specification in
`lab-config.json`. It remains in **draft qualification**, not a production
consumer equivalence or C1/C2 certificate. The workflow pins a disposable
WordPress/WooCommerce/MariaDB/PHP/WP-CLI runtime, verifies repository-tracked
WP All Import binaries, reads back native saved import state, runs the plugin's
own `wp all-import run <id>`, reads WooCommerce results, and rejects incomplete
or contradictory evidence rather than inferring success from CLI exit status.

## Exact host seams exercised

The LAB-only bootstrap uses the exact WP All Import Pro 5.1.0
`PMXI_Import_Record::set()->save()` and native `getById()` read-back,
together with `PMXI_File_Record` for the source file history. The source-backed
precedent is `actions/wp_ajax_wpai_run_preview_with_progress.php`;
`classes/cli.php` exposes the actual `all-import run` command, which calls
`PMXI_Import_Record::execute()`. The `PMXI_Chunk` reader operates on
individual `product` elements, so the synthetic XPath is `//product`.

WooCommerce Add-On 4.0.6 chooses its field-scoped product update behavior in
`src/XmlImportWooCommerceService.php` using the native
`is_using_new_product_import_options` option. V1 enables that option,
selectively enables regular-price updates, and disables unrelated custom-field
updates. Neither a successful CLI exit nor its Updated counter is accepted
without the WooCommerce before/after read-back.

The first synthetic input is XML to isolate mechanical native execution. It
does not establish provider-native comma CSV equivalence, XLSX parsing, exact
SwitchLand Import configuration or Parent SKU/variation grouping.
`LAB_NATIVE_WPAI_EXECUTION` may be raised only after the full CI read-back,
sentinel-preservation, independent validator and negative-control tests pass
on the same Head. Failing runs preserve bounded diagnostics and never claim
native execution proof.

A validated artifact (when actually produced) includes runtime, package,
fixture, native execution and WooCommerce read-back JSON. Never upload
plugin ZIP copies, Production data, credentials or WordPress DB dumps.

Historical PR #8 stays closed and unmerged; historical availability of its
objects remains `NOT_PROVEN_CLEARED`. This successor stays on PR #9's ancestry
and references only the identical existing Git binary blobs.

## Bounded target configuration candidate qualification (PR #11)

See [TARGET_CONFIG_QUALIFICATION.md](TARGET_CONFIG_QUALIFICATION.md) for historical/template/Owner/lab/fresh-export provenance boundaries, native simple and variation read-backs, intentional corruption controls, exact pinned versions and the remaining Production-equivalence/zero-variation gaps. This is CI-only test infrastructure. It does **not** activate the candidate mapping or qualify the exact current SwitchLand consumer.
