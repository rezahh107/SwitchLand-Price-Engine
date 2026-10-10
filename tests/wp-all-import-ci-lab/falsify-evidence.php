<?php
declare(strict_types=1);

// Falsification tests for the actual successful evidence; never generates an artificial PASS.
$out = $argv[1] ?? '';
$validator = __DIR__ . '/validate-evidence.php';
$source = $out . '/evidence.json';
if (!is_file($source)) {
    fwrite(STDERR, "WPAI_NEGATIVE_CONTROLS_NEED_REAL_EVIDENCE\n");
    exit(1);
}
$e = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
$mutations = [
    'missing_import_id' => static function (&$v): void {unset($v['consumer_config']['import_id']);},
    'wrong_plugin_digest' => static function (&$v): void {$v['packages']['wp-all-import-pro']['zip_sha256'] = str_repeat('0', 64);},
    'unexecuted_native_command' => static function (&$v): void {$v['execution']['native_command'] = 'wp eval fake-import';},
    'missing_native_cli_help' => static function (&$v): void {unset($v['execution']['native_cli_help_sha256']);},
    'unupdated_product' => static function (&$v): void {$v['fixture']['after']['regular_price'] = $v['fixture']['before']['regular_price'];},
    'mutated_sentinel' => static function (&$v): void {$v['fixture']['after']['sentinel'] = 'UNRELATED-FIELD-CORRUPTED';},
    'missing_runtime_version' => static function (&$v): void {unset($v['environment']['php']);},
    'inflated_production_claim' => static function (&$v): void {$v['limits']['PRODUCTION_EQUIVALENCE'] = 'PROVEN';},
    'false_success_status' => static function (&$v): void {$v['assertions']['intended_regular_price_changed'] = false;},
];
$dir = sys_get_temp_dir() . '/wpai-negative-' . getmypid();
if (!mkdir($dir, 0700, true) && !is_dir($dir)) {
    fwrite(STDERR, "WPAI_NEGATIVE_CONTROLS_TEMP_FAILED\n");
    exit(1);
}
try {
    foreach ($mutations as $name => $alter) {
        $modified = $e;
        $alter($modified);
        $path = $dir . '/' . $name . '.json';
        file_put_contents($path, json_encode($modified, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($validator)
            . ' ' . escapeshellarg($path) . ' ' . escapeshellarg($out) . ' 2>&1';
        exec($command, $lines, $status);
        if ($status === 0 || !str_contains(implode("\n", $lines), 'WPAI_EVIDENCE_REJECTED:')) {
            throw new RuntimeException('WPAI_NEGATIVE_CONTROL_FAILED: ' . $name);
        }
        $lines = [];
        echo "WPAI_NEGATIVE_REJECTED $name\n";
    }
} finally {
    foreach (glob($dir . '/*.json') ?: [] as $file) unlink($file);
    rmdir($dir);
}
echo "WPAI_NEGATIVE_CONTROLS_PASS count=" . count($mutations) . "\n";
