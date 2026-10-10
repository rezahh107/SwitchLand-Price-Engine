<?php
declare(strict_types=1);

// Inspects only the verified plugin extracted inside the disposable CI workspace.
// Does not execute or fabricate an import.
if ($argc !== 3) {
    fwrite(STDERR, "usage: php source-audit.php <plugin-root> <output.json>\n");
    exit(2);
}
$root = realpath($argv[1]);
if ($root === false || !is_dir($root) || basename($root) !== 'wp-all-import-pro') {
    fwrite(STDERR, "WPAI_SOURCE_AUDIT_FAILURE: invalid exact plugin root\n");
    exit(1);
}
$patterns = [
    'cli_command_registration' => '/WP_CLI::add_command\s*\(\s*[\x27\x22]all-import[\x27\x22]/',
    'saved_import_record_model' => '/class\s+PMXI_Import_Record\b/',
    'default_import_options' => '/function\s+get_default_import_options\s*\(/',
    'import_model_save' => '/->set\s*\(\s*(?:array\s*\(|\[)/',
    'native_cli_run_handler' => '/function\s+run\s*\(/',
];
$matches = array_fill_keys(array_keys($patterns), []);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }
    $source = file_get_contents($file->getPathname());
    if ($source === false) {
        fwrite(STDERR, "WPAI_SOURCE_AUDIT_FAILURE: unreadable PHP source\n");
        exit(1);
    }
    foreach ($patterns as $name => $regex) {
        if (!preg_match_all($regex, $source, $hits, PREG_OFFSET_CAPTURE)) {
            continue;
        }
        foreach ($hits[0] as $hit) {
            // Bounded provenance; never dump complete proprietary source to CI logs.
            if (count($matches[$name]) >= 12) {
                break;
            }
            $matches[$name][] = [
                'file' => substr($file->getPathname(), strlen($root) + 1),
                'line' => substr_count($source, "\n", 0, $hit[1]) + 1,
                'matched' => trim($hit[0]),
            ];
        }
    }
}
// Exact method provenance only. Never publish bundled third-party source excerpts.
$recordSourcePath = $root . '/models/import/record.php';
$recordSource = file_get_contents($recordSourcePath);
if ($recordSource === false || !preg_match('/function\\s+execute\\s*\\(/', $recordSource, $executeMatch, PREG_OFFSET_CAPTURE)) {
    fwrite(STDERR, "WPAI_SOURCE_AUDIT_FAILURE: native execute method missing\\n");
    exit(1);
}
$executeStarts = [substr_count($recordSource, "\\n", 0, $executeMatch[0][1]) + 1];
$result = [
    'schema_version' => '1.0.0',
    'classification' => 'EXACT_INSTALLED_SOURCE_AUDIT_NOT_RUNTIME_PROOF',
    'plugin_root' => basename($root),
    'seams' => $matches,
    'native_execute_definition_lines' => $executeStarts,
    'bootstrap_disposition' => 'NATIVE_SAVED_IMPORT_BOOTSTRAP_VERIFIED_SEPARATELY',
    'claims' => [
        'LAB_NATIVE_WPAI_EXECUTION' => 'NOT_PROVEN',
        'EXACT_SWITCHLAND_CONSUMER' => 'NOT_PROVEN',
        'C1_PROVIDER_NATIVE_COMMA_CSV' => 'NOT_PROVEN',
        'C2_PROVIDER_NATIVE_XLSX' => 'NOT_PROVEN',
    ],
];
$out = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
if (file_put_contents($argv[2], $out) === false) {
    fwrite(STDERR, "WPAI_SOURCE_AUDIT_FAILURE: output write failed\n");
    exit(1);
}
foreach (['cli_command_registration', 'saved_import_record_model', 'default_import_options'] as $required) {
    if (!$matches[$required]) {
        fwrite(STDERR, "WPAI_SOURCE_AUDIT_FAILURE: required source seam missing: $required\n");
        exit(1);
    }
}
echo "WPAI_SOURCE_AUDIT_SEAMS_LOCATED\n";
