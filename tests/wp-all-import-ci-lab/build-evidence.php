<?php
declare(strict_types=1);

// Compile only measured ephemeral native-consumer results. Never assume a PASS.
$out = $argv[1] ?? '';
$repo = dirname(__DIR__, 2);
$fail = static function (string $s): never {fwrite(STDERR, "WPAI_EVIDENCE_BUILD_FAILURE: $s\n"); exit(1);};
$read = static function (string $path) use ($fail): array {
    if (!is_file($path)) $fail('required file missing: ' . basename($path));
    try {$data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);}
    catch (Throwable $e) {$fail('invalid JSON: ' . basename($path));}
    if (!is_array($data)) $fail('not an object: ' . basename($path));
    return $data;
};
$configPath = $repo . '/tests/wp-all-import-ci-lab/lab-config.json';
$config = $read($configPath);
$runtime = $read($out . '/runtime.json');
$bootstrap = $read($out . '/bootstrap.json');
$pre = $read($out . '/pre-state.json');
$post = $read($out . '/post-state.json');
$initialLog = $out . '/native-initial-run.log';
$updateLog = $out . '/native-update-run.log';
if (!is_file($initialLog) || !is_file($updateLog)
    || !is_file($out . '/native-import-list.txt')) $fail('native execution files absent');
$idPath = $out . '/import-id.txt';
if (!is_file($idPath)) $fail('saved Import ID absent');
$id = (int) trim((string) file_get_contents($idPath));
$f = $config['fixture'];
$checks = [
    'exact_repository' => ($runtime['repository']['full_name'] ?? null) === 'rezahh107/SwitchLand-Price-Engine',
    'exact_head' => (bool) preg_match('/^[a-f0-9]{40}$/', (string)($runtime['repository']['exact_head_sha'] ?? '')),
    'pinned_environment' => ($runtime['environment_class'] ?? null) === 'LAB_INFRASTRUCTURE_SMOKE_NOT_PRODUCTION_BOUND'
        && ($runtime['environment']['php'] ?? null) === $config['versions']['php']
        && ($runtime['environment']['wordpress'] ?? null) === $config['versions']['wordpress']
        && ($runtime['environment']['woocommerce'] ?? null) === $config['versions']['woocommerce']
        && ($runtime['environment']['wp_cli'] ?? null) === $config['versions']['wp_cli'],
    'config_identity' => ($runtime['config_sha256'] ?? null) === hash_file('sha256', $configPath)
        && ($bootstrap['config_sha256'] ?? null) === hash_file('sha256', $configPath),
    'input_identity' => ($bootstrap['input_initial_sha256'] ?? null) === hash_file('sha256', $repo . '/tests/wp-all-import-ci-lab/fixtures/initial.xml')
        && ($bootstrap['input_updated_sha256'] ?? null) === hash_file('sha256', $repo . '/tests/wp-all-import-ci-lab/fixtures/updated.xml'),
    'authentic_saved_import' => $id > 0 && ($bootstrap['import_id'] ?? null) === $id
        && ($bootstrap['saved_import_readback'] ?? null) === true
        && ($post['import_id'] ?? null) === $id
        && ($bootstrap['classification'] ?? null) === 'LAB_SYNTHETIC_WPAI_CONFIG',
    'native_initial_execution' => strpos((string) file_get_contents($initialLog), 'Success: Import completed.') !== false,
    'native_update_execution' => strpos((string) file_get_contents($updateLog), 'Success: Import completed.') !== false,
    'pre_existing_simple_product' => ($pre['sku'] ?? null) === $f['sku']
        && ($pre['type'] ?? null) === 'simple' && ($pre['regular_price'] ?? null) === $f['start_regular_price'],
    'intended_regular_price_changed' => ($post['sku'] ?? null) === $f['sku']
        && ($post['regular_price'] ?? null) === $f['expected_regular_price']
        && ($pre['regular_price'] ?? null) !== ($post['regular_price'] ?? null),
    'same_product_identity' => (int) ($pre['product_id'] ?? 0) > 0
        && ($pre['product_id'] ?? null) === ($post['product_id'] ?? null),
    'unrelated_sentinel_preserved' => ($pre['sentinel'] ?? null) === $f['sentinel_value']
        && ($post['sentinel'] ?? null) === $f['sentinel_value'],
    'native_update_counter' => (int) ($post['native_import_counters']['updated'] ?? 0) > 0,
];
$manifest = $read($repo . '/tests/fixtures/wp-all-import-packages/manifest.json');
$packageOK = count($runtime['packages'] ?? []) === count($manifest['packages']);
foreach ($manifest['packages'] as $p) {
    $got = $runtime['packages'][$p['id']] ?? [];
    $packageOK = $packageOK
        && ($got['version'] ?? null) === $p['version']
        && ($got['runtime_version'] ?? null) === $p['version']
        && ($got['zip_size_bytes'] ?? null) === $p['size_bytes']
        && ($got['zip_sha256'] ?? null) === $p['sha256'];
}
$checks['exact_packages'] = $packageOK;
$checks['workflow_identity'] = ($runtime['workflow']['path'] ?? null) === '.github/workflows/wp-all-import-ci-lab.yml'
    && ($runtime['workflow']['sha256'] ?? null) === hash_file('sha256', $repo . '/.github/workflows/wp-all-import-ci-lab.yml')
    && (string)($runtime['workflow']['run_id'] ?? '') !== ''
    && (string)($runtime['workflow']['run_attempt'] ?? '') !== ''
    && (string)($runtime['workflow']['event'] ?? '') !== '';
$ok = !in_array(false, $checks, true);
$evidence = [
    'artifact_type' => 'WP_ALL_IMPORT_NATIVE_CONSUMER_CI_LAB',
    'schema_version' => '1.0.0',
    'environment_class' => $config['environment_class'],
    'repository' => $runtime['repository'],
    'workflow' => $runtime['workflow'],
    'environment' => $runtime['environment'],
    'packages' => $runtime['packages'],
    'consumer_config' => [
        'classification' => 'LAB_SYNTHETIC_WPAI_CONFIG',
        'bootstrap_method' => $bootstrap['bootstrap_mechanism'],
        'import_id' => $id,
        'config_sha256' => $bootstrap['config_sha256'],
        'effective_saved_options_sha256' => $bootstrap['effective_saved_options_sha256'],
        'source_model_file_sha256' => $bootstrap['source_model_file_sha256'],
    ],
    'fixture' => [
        'sku' => $f['sku'],
        'initial_xml_sha256' => $bootstrap['input_initial_sha256'],
        'updated_xml_sha256' => $bootstrap['input_updated_sha256'],
        'before' => $pre,
        'after' => $post,
    ],
    'execution' => [
        'native_command' => 'wp all-import run ' . $id,
        'initial_cli_stdout_sha256' => hash_file('sha256', $initialLog),
        'update_cli_stdout_sha256' => hash_file('sha256', $updateLog),
        'import_list_sha256' => hash_file('sha256', $out . '/native-import-list.txt'),
        'native_import_counters' => $post['native_import_counters'],
    ],
    'assertions' => $checks,
    'mechanics_claim' => $ok ? 'PROVEN_IN_REPRODUCIBLE_WPAI_LAB' : 'NOT_PROVEN',
    'limits' => [
        'EXACT_SWITCHLAND_CONSUMER' => 'NOT_PROVEN',
        'C1_PROVIDER_NATIVE_COMMA_CSV' => 'NOT_PROVEN',
        'C2_PROVIDER_NATIVE_XLSX' => 'NOT_PROVEN',
        'EXACT_IMPORT_CONFIGURATION' => 'PARTIALLY_PROVEN_BUT_MATERIALLY_INCOMPLETE',
        'OWNER_SERIALIZATION_DECISION_READY' => 'NO',
        'SUCCESSOR_IMPLEMENTATION' => 'DO_NOT_START_YET',
        'PRODUCTION_EQUIVALENCE' => 'NOT_PROVEN',
        'VARIABLE_PRODUCT_CONSUMER_PATH' => 'NOT_PROVEN_IN_V1',
    ],
    'overall_status' => $ok ? 'PASS' : 'FAIL',
];
$json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
if (file_put_contents($out . '/evidence.json', $json . "\n") === false) $fail('write evidence failed');
if (!$ok) {
    $fail('actual scenario failed: ' . implode(', ', array_keys(array_filter($checks, static fn($v) => $v === false))));
}
echo 'WPAI_EVIDENCE_BUILT_SHA256=' . hash_file('sha256', $out . '/evidence.json') . PHP_EOL;
