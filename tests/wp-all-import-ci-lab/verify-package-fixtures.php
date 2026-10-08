<?php
declare(strict_types=1);

// The manifest is public metadata; package bytes must be supplied separately.
// There is deliberately no implicit repo-relative fixture or network fallback.
$manifestPath = $argv[1] ?? '';
$root = $argv[2] ?? '';

$fail = static function (string $message): never {
    fwrite(STDERR, "WPAI_PACKAGE_FIXTURE_FAILURE: {$message}\n");
    exit(1);
};

if ('' === $manifestPath || '' === $root) {
    $fail('usage: php verify-package-fixtures.php <public-manifest.json> <external-authorized-package-directory>');
}
$resolvedRoot = realpath($root);
$resolvedRepo = realpath(dirname(__DIR__, 2));
if (false === $resolvedRoot || ! is_dir($resolvedRoot)) {
    $fail('external authorized package directory is missing.');
}
if (false === $resolvedRepo) {
    $fail('repository root cannot be resolved.');
}
if ($resolvedRoot === $resolvedRepo || str_starts_with($resolvedRoot, $resolvedRepo . DIRECTORY_SEPARATOR)) {
    $fail('external package directory must not be inside the repository checkout.');
}
$root = $resolvedRoot;

$run = static function (array $args, ?string &$stdout = null) use ($fail): int {
    $command = implode(' ', array_map('escapeshellarg', $args));
    $lines = array();
    $status = 0;
    exec($command . ' 2>&1', $lines, $status);
    $stdout = implode("\n", $lines);
    return $status;
};

foreach (array('unzip', 'zipinfo') as $command) {
    $path = null;
    if (0 !== $run(array('sh', '-c', 'command -v ' . $command), $path) || ! is_string($path) || '' === trim($path)) {
        $fail("required command is unavailable: {$command}");
    }
}

if (! is_file($manifestPath)) {
    $fail('manifest.json is missing.');
}

try {
    $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    $fail('manifest.json is malformed: ' . $error->getMessage());
}

if (($manifest['schema_version'] ?? null) !== '1.0.0') {
    $fail('manifest schema_version mismatch.');
}
if (($manifest['fixture_family'] ?? null) !== 'WP_ALL_IMPORT_CI_LAB_V1_BINARY_FIXTURES') {
    $fail('fixture_family mismatch.');
}
if (($manifest['acquisition_policy'] ?? null) !== 'EXTERNAL_AUTHORIZED_EXACT_FIXTURES_NO_NETWORK_FALLBACK') {
    $fail('fixture acquisition policy mismatch.');
}
if (($manifest['production_equivalence'] ?? null) !== 'NOT_PROVEN') {
    $fail('fixture manifest must not assert Production equivalence.');
}
if (! is_array($manifest['packages'] ?? null)) {
    $fail('manifest packages must be an array.');
}

$expected = array(
    'wp-all-import-pro' => array(
        'id' => 'wp-all-import-pro',
        'classification' => 'OWNER_SUPPLIED_SOURCE_PACKAGE',
        'admission_role' => 'OWNER_SUPPLIED_LAB_CANDIDATE',
        'filename' => 'wp-all-import-pro.zip',
        'plugin_name' => 'WP All Import Pro',
        'version' => '5.1.0',
        'size_bytes' => 4370010,
        'sha256' => '8c38753da981293432c5ff2fff4124ccb9cf5c7f0a90e964f1fbb222c09772bb',
        'expected_zip_root' => 'wp-all-import-pro/',
        'expected_main_plugin_file' => 'wp-all-import-pro/wp-all-import-pro.php',
    ),
    'wpai-woocommerce-add-on' => array(
        'id' => 'wpai-woocommerce-add-on',
        'classification' => 'OWNER_SUPPLIED_SOURCE_PACKAGE',
        'admission_role' => 'OWNER_SUPPLIED_LAB_CANDIDATE',
        'filename' => 'wpai-woocommerce-add-on_4.0.6.zip',
        'plugin_name' => 'WP All Import - WooCommerce Import Add-On Pro',
        'version' => '4.0.6',
        'size_bytes' => 437099,
        'sha256' => '6959464478610568937cc51d118a832ec3515ed501b3bb083dbe68894f8bda6a',
        'expected_zip_root' => 'wpai-woocommerce-add-on/',
        'expected_main_plugin_file' => 'wpai-woocommerce-add-on/wpai-woocommerce-add-on.php',
    ),
);

$seenIds = array();
$seenFilenames = array();
$packagesById = array();
foreach ($manifest['packages'] as $index => $package) {
    if (! is_array($package)) {
        $fail("package entry {$index} is malformed.");
    }
    $id = $package['id'] ?? null;
    $filename = $package['filename'] ?? null;
    if (! is_string($id) || '' === $id) {
        $fail("package entry {$index} has an invalid id.");
    }
    if (! is_string($filename) || '' === $filename || basename($filename) !== $filename) {
        $fail("package {$id} has an invalid filename.");
    }
    if (isset($seenIds[$id])) {
        $fail("duplicate package id: {$id}");
    }
    if (isset($seenFilenames[$filename])) {
        $fail("duplicate package filename: {$filename}");
    }
    $seenIds[$id] = true;
    $seenFilenames[$filename] = true;
    $packagesById[$id] = $package;
}

if (count($packagesById) !== count($expected) || array_diff_key($expected, $packagesById) || array_diff_key($packagesById, $expected)) {
    $fail('manifest required package set mismatch.');
}

$expectedZipFiles = array_column($expected, 'filename');
sort($expectedZipFiles, SORT_STRING);
$actualZipFiles = array_map('basename', glob($root . DIRECTORY_SEPARATOR . '*.zip') ?: array());
sort($actualZipFiles, SORT_STRING);
if ($actualZipFiles !== $expectedZipFiles) {
    $fail('fixture directory ZIP set mismatch.');
}

$parseHeader = static function (string $source, string $header): ?string {
    $pattern = '/^[ \t*#@\/]*' . preg_quote($header, '/') . '\s*:\s*(.+?)\s*$/mi';
    if (! preg_match($pattern, $source, $match)) {
        return null;
    }
    return trim($match[1]);
};

foreach ($expected as $id => $identity) {
    $package = $packagesById[$id];
    foreach ($identity as $field => $value) {
        if (($package[$field] ?? null) !== $value) {
            $fail("package {$id} manifest field mismatch: {$field}");
        }
    }

    $path = $root . DIRECTORY_SEPARATOR . $identity['filename'];
    if (! is_file($path)) {
        $fail("package {$id} is missing.");
    }
    if (filesize($path) !== $identity['size_bytes']) {
        $fail("package {$id} size mismatch.");
    }
    $actualSha = hash_file('sha256', $path);
    if (! is_string($actualSha) || ! hash_equals($identity['sha256'], $actualSha)) {
        $fail("package {$id} SHA-256 mismatch.");
    }

    $zipOutput = null;
    if (0 !== $run(array('unzip', '-tqq', $path), $zipOutput)) {
        $fail("package {$id} ZIP integrity failure: {$zipOutput}");
    }

    $listing = null;
    if (0 !== $run(array('zipinfo', '-1', $path), $listing) || ! is_string($listing)) {
        $fail("package {$id} ZIP listing failed.");
    }
    $entries = array_values(array_filter(preg_split('/\R/', $listing) ?: array(), static fn ($entry) => '' !== $entry));
    if (array() === $entries) {
        $fail("package {$id} ZIP is empty.");
    }
    foreach ($entries as $entry) {
        if (! str_starts_with($entry, $identity['expected_zip_root'])) {
            $fail("package {$id} has an unexpected ZIP root entry: {$entry}");
        }
    }
    if (! in_array($identity['expected_main_plugin_file'], $entries, true)) {
        $fail("package {$id} main plugin file is missing from ZIP.");
    }

    $pluginSource = null;
    if (0 !== $run(array('unzip', '-p', $path, $identity['expected_main_plugin_file']), $pluginSource) || ! is_string($pluginSource)) {
        $fail("package {$id} main plugin file could not be read.");
    }
    $pluginName = $parseHeader($pluginSource, 'Plugin Name');
    $version = $parseHeader($pluginSource, 'Version');
    if ($pluginName !== $identity['plugin_name']) {
        $fail("package {$id} plugin header name mismatch.");
    }
    if ($version !== $identity['version']) {
        $fail("package {$id} plugin version mismatch.");
    }
}

echo 'WPAI_PACKAGE_FIXTURES_PASS packages=' . count($expected) . ' acquisition=EXTERNAL_AUTHORIZED_EXACT_FIXTURES_NO_NETWORK_FALLBACK production_equivalence=NOT_PROVEN' . PHP_EOL;
