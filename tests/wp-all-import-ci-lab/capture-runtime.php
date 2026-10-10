<?php
// Runs inside the actual ephemeral WordPress instance via WP-CLI eval-file.
$workspace = getenv('GITHUB_WORKSPACE');
$out = getenv('LAB_OUT');
$head = getenv('LAB_HEAD_SHA');
if (!$workspace || !$out || !preg_match('/^[a-f0-9]{40}$/', (string)$head)) {
    throw new RuntimeException('WPAI_RUNTIME_IDENTITY_MISSING');
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';
global $wpdb;
$manifest = json_decode(
    file_get_contents($workspace . '/tests/fixtures/wp-all-import-packages/manifest.json'),
    true, 512, JSON_THROW_ON_ERROR
);
$config = json_decode(
    file_get_contents($workspace . '/tests/wp-all-import-ci-lab/lab-config.json'),
    true, 512, JSON_THROW_ON_ERROR
);
$packages = [];
foreach ($manifest['packages'] as $p) {
    $file = $workspace . '/tests/fixtures/wp-all-import-packages/' . $p['filename'];
    if (!is_file($file)) {
        throw new RuntimeException('WPAI_RUNTIME_PACKAGE_MISSING');
    }
    $packages[$p['id']] = [
        'version' => $p['version'],
        'runtime_version' => get_plugin_data(
            WP_PLUGIN_DIR . '/' . $p['expected_main_plugin_file'], false, false
        )['Version'],
        'zip_sha256' => hash_file('sha256', $file),
        'zip_size_bytes' => filesize($file),
        'manifest_sha256' => hash_file('sha256', $workspace . '/tests/fixtures/wp-all-import-packages/manifest.json'),
    ];
    if ($packages[$p['id']]['runtime_version'] !== $p['version']
        || $packages[$p['id']]['zip_sha256'] !== $p['sha256']
        || $packages[$p['id']]['zip_size_bytes'] !== $p['size_bytes']) {
        throw new RuntimeException('WPAI_RUNTIME_PACKAGE_IDENTITY_MISMATCH');
    }
}
$environment = [
    'php' => PHP_VERSION,
    'wordpress' => get_bloginfo('version'),
    'db_product_version' => $wpdb->get_var('SELECT VERSION()'),
    'wp_cli' => defined('WP_CLI_VERSION') ? WP_CLI_VERSION : 'UNBOUND',
    'woocommerce' => WC()->version,
];
$expected = $config['versions'];
foreach (['php', 'wordpress', 'woocommerce', 'wp_cli'] as $key) {
    if ($environment[$key] !== $expected[$key]) {
        throw new RuntimeException('WPAI_RUNTIME_VERSION_MISMATCH: ' . $key . ' expected ' . $expected[$key] . ' got ' . $environment[$key]);
    }
}
if (strpos($environment['db_product_version'], $expected['mariadb'] . '-MariaDB') !== 0) {
    throw new RuntimeException('WPAI_RUNTIME_DB_VERSION_MISMATCH');
}
$result = [
    'schema_version' => '1.0.0',
    'repository' => ['full_name' => getenv('GITHUB_REPOSITORY'), 'exact_head_sha' => $head],
    'workflow' => [
        'path' => '.github/workflows/wp-all-import-ci-lab.yml',
        'sha256' => hash_file('sha256', $workspace . '/.github/workflows/wp-all-import-ci-lab.yml'),
        'run_id' => getenv('GITHUB_RUN_ID'),
        'run_attempt' => getenv('GITHUB_RUN_ATTEMPT'),
        'event' => getenv('GITHUB_EVENT_NAME'),
        'ref' => getenv('GITHUB_REF'),
    ],
    'environment_class' => $config['environment_class'],
    'environment' => $environment,
    'packages' => $packages,
    'config_sha256' => hash_file('sha256', $workspace . '/tests/wp-all-import-ci-lab/lab-config.json'),
    'fixture_source_sha256' => [
        'initial' => hash_file('sha256', $workspace . '/tests/wp-all-import-ci-lab/fixtures/initial.xml'),
        'updated' => hash_file('sha256', $workspace . '/tests/wp-all-import-ci-lab/fixtures/updated.xml'),
    ],
];
if ($result['repository']['full_name'] !== 'rezahh107/SwitchLand-Price-Engine') {
    throw new RuntimeException('WPAI_RUNTIME_REPOSITORY_MISMATCH');
}
if (!file_put_contents($out . '/runtime.json', wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n")) {
    throw new RuntimeException('WPAI_RUNTIME_CAPTURE_FAILED');
}
echo "WPAI_RUNTIME_IDENTITY_CAPTURED\n";
