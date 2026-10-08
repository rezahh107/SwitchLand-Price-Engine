# WP All Import CI Lab — exact fixture boundary

This public repository contains only metadata and verification logic for two exact
Owner-supplied WP All Import Pro binaries. It **does not** include or distribute
their ZIP bytes and does not establish any redistribution entitlement.

The current production-equivalence state is **NOT_PROVEN**. The active Import
Contract v1.2.0 and C1/C2 status are not changed by this fixture preparation.
The future CI Lab and the Production consumer have **not** been exercised here.

## Authorized external input

An authorized operator must provision **both** exact packages in an access-controlled
directory **outside** the repository checkout, with filenames given in
`tests/fixtures/wp-all-import-packages/manifest.json`. Do not add those files
to Git, CI artifacts or publicly reachable download URLs.

Run, in an environment authorized to hold/process those bytes:

```sh
php tests/wp-all-import-ci-lab/verify-package-fixtures.php \
  tests/fixtures/wp-all-import-packages/manifest.json \
  /absolute/path/to/authorized-package-directory
```

The verifier rejects absent/mismatching packages, unexpected ZIPs, wrong SHA/size,
malformed ZIP roots and missing/mismatching main-plugin headers. It requires
`unzip`, `zipinfo` and PHP 8.1+ (for `never` and `str_starts_with`).

There is **no** default local package path, network fetch, vendor credential use,
`latest` substitution or automatic alternative-version fallback.

## Public CI boundary

`.github/workflows/wpai-public-fixture-boundary.yml` verifies the tracked
repository does not contain either exact package, even under a renamed path,
rejects ZIPs in the fixture directory, checks metadata identity, checks PHP syntax,
requires missing external input to fail, and falsifies the admission guard with
a synthetic staged ZIP. These are public-only checks, **not** evidence that a
private positive package verification or live WooCommerce/WordPress lab passed.

Historical binary exposure from the former PR #8 branch is a separate
remediation problem. A clean successor PR does **not** prove that old GitHub
objects, PR refs, forks, caches or clones are unavailable. Preserve that
distinction in all completion claims.
