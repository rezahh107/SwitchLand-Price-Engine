<?php
declare(strict_types=1);

// Independent fail-closed evidence admission guard. Checks raw artifacts again.
$evidenceFile = $argv[1] ?? '';
$out = $argv[2] ?? dirname($evidenceFile);
$repo = dirname(__DIR__, 2);
$reject = static function(string $reason): never {
    fwrite(STDERR, 'WPAI_EVIDENCE_REJECTED: ' . $reason . PHP_EOL);
    exit(1);
};
$read = static function(string $file) use ($reject): array {
    if (!is_file($file)) $reject('missing ' . basename($file));
    try {$value = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);}
    catch (Throwable $e) {$reject('invalid JSON ' . basename($file));}
    if (!is_array($value)) $reject('invalid object ' . basename($file));
    return $value;
};
$e = $read($evidenceFile);
$c = $read($repo . '/tests/wp-all-import-ci-lab/lab-config.json');
$r = $read($out . '/runtime.json');
$b = $read($out . '/bootstrap.json');
$before = $read($out . '/pre-state.json');
$after = $read($out . '/post-state.json');
$switched = $read($out . '/updated-source.json');
$manifest = $read($repo . '/tests/fixtures/wp-all-import-packages/manifest.json');
$req = static function(bool $valid, string $reason) use ($reject): void {
    if (!$valid) $reject($reason);
};
$req(($e['artifact_type'] ?? '') === 'WP_ALL_IMPORT_NATIVE_CONSUMER_CI_LAB', 'wrong artifact class');
$req(($e['schema_version'] ?? '') === '1.0.0', 'wrong schema version');
$req(($e['overall_status'] ?? '') === 'PASS', 'not a completed passing run');
$req(($e['mechanics_claim'] ?? '') === 'PROVEN_IN_REPRODUCIBLE_WPAI_LAB', 'mechanics unproven');
$req(($e['environment_class'] ?? '') === 'LAB_INFRASTRUCTURE_SMOKE_NOT_PRODUCTION_BOUND', 'unexpected environment claim');
$req(($e['repository'] ?? null) === ($r['repository'] ?? null)
    && ($e['workflow'] ?? null) === ($r['workflow'] ?? null)
    && ($e['environment'] ?? null) === ($r['environment'] ?? null)
    && ($e['packages'] ?? null) === ($r['packages'] ?? null), 'runtime provenance detached');
$req(($r['repository']['full_name'] ?? '') === 'rezahh107/SwitchLand-Price-Engine'
    && preg_match('/^[a-f0-9]{40}$/', (string)($r['repository']['exact_head_sha'] ?? '')) === 1, 'repository HEAD not exact');
$head = (string) file_get_contents($out . '/head.txt');
$req(trim($head) === $r['repository']['exact_head_sha'], 'checked-out SHA mismatch');
$req(($r['workflow']['sha256'] ?? '') === hash_file('sha256', $repo . '/.github/workflows/wp-all-import-ci-lab.yml'),
    'workflow bytes mismatch');
$req(($r['workflow']['path'] ?? '') === '.github/workflows/wp-all-import-ci-lab.yml'
    && (string)($r['workflow']['run_id'] ?? '') !== ''
    && (string)($r['workflow']['run_attempt'] ?? '') !== ''
    && (string)($r['workflow']['event'] ?? '') !== ''
    && (string)($r['workflow']['ref'] ?? '') !== '', 'missing run identity');
foreach (['php','wordpress','woocommerce','wp_cli'] as $key) {
    $req(($r['environment'][$key] ?? null) === ($c['versions'][$key] ?? null), 'version mismatch ' . $key);
}
$req(strpos((string)($r['environment']['db_product_version'] ?? ''), '11.4.8-MariaDB') === 0, 'database version mismatch');
$req(count($e['packages'] ?? []) === 2 && count($manifest['packages'] ?? []) === 2, 'package count');
foreach ($manifest['packages'] as $p) {
    $actual = $e['packages'][$p['id']] ?? null;
    $path = $repo . '/tests/fixtures/wp-all-import-packages/' . $p['filename'];
    $req(is_array($actual) && is_file($path), 'package absent');
    $req(($actual['version'] ?? '') === $p['version'] && ($actual['runtime_version'] ?? '') === $p['version']
        && ($actual['zip_sha256'] ?? '') === $p['sha256']
        && ($actual['zip_size_bytes'] ?? -1) === $p['size_bytes']
        && hash_file('sha256', $path) === $p['sha256']
        && filesize($path) === $p['size_bytes'], 'package identity mismatch');
}
$req(($r['config_sha256'] ?? null) === hash_file('sha256', $repo . '/tests/wp-all-import-ci-lab/lab-config.json')
    && ($e['consumer_config']['config_sha256'] ?? null) === $r['config_sha256']
    && ($b['config_sha256'] ?? null) === $r['config_sha256'], 'config digest mismatch');
$req(($e['consumer_config']['classification'] ?? '') === 'LAB_SYNTHETIC_WPAI_CONFIG'
    && ($b['classification'] ?? '') === 'LAB_SYNTHETIC_WPAI_CONFIG'
    && ($b['saved_import_readback'] ?? null) === true, 'authentic saved import record not read back');
$idFile = $out . '/import-id.txt';
$req(is_file($idFile), 'missing ID file');
$id = (int) trim((string) file_get_contents($idFile));
$req($id > 0 && ($e['consumer_config']['import_id'] ?? null) === $id
    && ($b['import_id'] ?? null) === $id && ($after['import_id'] ?? null) === $id, 'import identity mismatch');
$req(($e['consumer_config']['bootstrap_method'] ?? '') === 'PMXI_Import_Record::set/save + PMXI_File_Record + native getById', 'unverified bootstrap');
$req(($e['consumer_config']['effective_saved_options_sha256'] ?? '') === ($b['effective_saved_options_sha256'] ?? null)
    && ($e['consumer_config']['source_model_file_sha256'] ?? '') === ($b['source_model_file_sha256'] ?? null)
    && preg_match('/^[a-f0-9]{64}$/', (string)($b['addon_price_gate_source_sha256'] ?? '')) === 1
    && ($e['consumer_config']['addon_price_gate_source_sha256'] ?? '') === ($b['addon_price_gate_source_sha256'] ?? null),
    'native configuration/source digest mismatch');
foreach (['initial'=>'initial', 'updated'=>'updated'] as $name=>$fixture) {
    $hash = hash_file('sha256', $repo . '/tests/wp-all-import-ci-lab/fixtures/' . $fixture . '.xml');
    $req(($e['fixture'][$name . '_xml_sha256'] ?? '') === $hash
        && ($r['fixture_source_sha256'][$name] ?? '') === $hash, 'source input digest mismatch');
}
$req(($e['fixture']['before'] ?? null) === $before && ($e['fixture']['after'] ?? null) === $after, 'WooCommerce read-back detached');
$req(($e['fixture']['updated_source_record'] ?? null) === $switched
    && ($switched['native_import_record_readback'] ?? null) === true
    && ($switched['source_sha256'] ?? null) === $e['fixture']['updated_xml_sha256']
    && ($switched['source_fixture_sha256'] ?? null) === $e['fixture']['updated_xml_sha256']
    && ($switched['import_id'] ?? null) === $id, 'updated saved source read-back detached');
$f = $c['fixture'];
$req(($before['sku'] ?? null) === $f['sku'] && ($after['sku'] ?? null) === $f['sku']
    && ($before['type'] ?? null) === 'simple' && ($after['type'] ?? null) === 'simple'
    && ($before['product_id'] ?? 0) > 0
    && ($before['product_id'] ?? null) === ($after['product_id'] ?? null), 'product identity mismatch');
$req(($before['regular_price'] ?? null) === $f['start_regular_price']
    && ($after['regular_price'] ?? null) === $f['expected_regular_price']
    && ($before['regular_price'] ?? null) !== ($after['regular_price'] ?? null), 'intended field mutation missing');
$req(($before['sentinel'] ?? null) === $f['sentinel_value']
    && ($after['sentinel'] ?? null) === $f['sentinel_value'], 'sentinel mutation');
$req((int)($after['native_import_counters']['updated'] ?? 0) > 0, 'no native updated records');
$req(($e['execution']['native_command'] ?? '') === 'wp all-import run ' . $id, 'not native run');
$cliHelpPath = $out . '/native-cli-help.txt';
$req(is_file($cliHelpPath) &&
    ($e['execution']['native_cli_help_sha256'] ?? null) === hash_file('sha256', $cliHelpPath)
    && preg_match('/^\\s+run\\b/m', (string)file_get_contents($cliHelpPath)) === 1
    && preg_match('/^\\s+list\\b/m', (string)file_get_contents($cliHelpPath)) === 1,
    'native CLI run/list availability detached');
foreach (['initial'=>'native-initial-run.log', 'update'=>'native-update-run.log'] as $phase => $file) {
    $path = $out . '/' . $file;
    $req(is_file($path), 'missing native execution log');
    $req(($e['execution'][$phase . '_cli_stdout_sha256'] ?? '') === hash_file('sha256', $path)
        && strpos((string)file_get_contents($path), 'Success: Import completed.') !== false, 'native CLI execution not proven: ' . $phase);
}
$req(($e['execution']['import_list_sha256'] ?? '') === hash_file('sha256', $out . '/native-import-list.txt'),
    'native import listing detached');
$expectedLimits = [
    'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN',
    'C1_PROVIDER_NATIVE_COMMA_CSV'=>'NOT_PROVEN',
    'C2_PROVIDER_NATIVE_XLSX'=>'NOT_PROVEN',
    'EXACT_IMPORT_CONFIGURATION'=>'PARTIALLY_PROVEN_BUT_MATERIALLY_INCOMPLETE',
    'OWNER_SERIALIZATION_DECISION_READY'=>'NO',
    'SUCCESSOR_IMPLEMENTATION'=>'DO_NOT_START_YET',
    'PRODUCTION_EQUIVALENCE'=>'NOT_PROVEN',
    'VARIABLE_PRODUCT_CONSUMER_PATH'=>'NOT_PROVEN_IN_V1',
];
$req(($e['limits'] ?? null) === $expectedLimits, 'authority/claim ceiling mismatch');
$checks = $e['assertions'] ?? null;
$req(is_array($checks) && count($checks) >= 13 && !in_array(false, $checks, true)
    && count(array_filter($checks, static fn($v) => $v === true)) === count($checks), 'assertions not all true');
echo 'WPAI_EVIDENCE_VERIFIED_SHA256=' . hash_file('sha256', $evidenceFile) . PHP_EOL;
