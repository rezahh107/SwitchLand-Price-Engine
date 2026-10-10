<?php
// Native WP-CLI eval-file adapter. Test-only; never loaded by Production.
// Exact 5.1.0 provenance: actions/wp_ajax_wpai_run_preview_with_progress.php
// 1487-1525 uses PMXI_Import_Record::set()->save(), then getById/readback.
// Native CLI: classes/cli.php:35-71 gets saved ID via PMXI_Import_Record.
$repo = getenv('GITHUB_WORKSPACE');
$out = getenv('LAB_OUT');
$stage = getenv('LAB_STAGE');
if (!$repo || !$out || !in_array($stage, ['bootstrap', 'pre', 'post'], true)) {
    throw new RuntimeException('WPAI_LAB_INVALID_ENVIRONMENT');
}
$configPath = $repo . '/tests/wp-all-import-ci-lab/lab-config.json';
$config = json_decode(file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);
if (($config['consumer_config_classification'] ?? '') !== 'LAB_SYNTHETIC_WPAI_CONFIG') {
    throw new RuntimeException('WPAI_LAB_CONFIG_CLASSIFICATION_DRIFT');
}
$f = $config['fixture'];
$upload = wp_upload_dir();
if (!empty($upload['error'])) {
    throw new RuntimeException('WPAI_LAB_UPLOAD_DIRECTORY_ERROR');
}
$folder = $upload['basedir'] . '/wpallimport/files';
$file = $folder . '/lab-synthetic-products.xml';
$emit = static function (string $name, array $value) use ($out): void {
    if (!is_dir($out) || file_put_contents(
        $out . '/' . $name . '.json',
        wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
    ) === false) {
        throw new RuntimeException('WPAI_LAB_EVIDENCE_WRITE_FAILED: ' . $name);
    }
};
$importIdFile = $out . '/import-id.txt';
if ($stage === 'bootstrap') {
    if (!class_exists('PMXI_Import_Record') || !class_exists('PMXI_Plugin')
        || !class_exists('PMXI_File_Record') || !function_exists('wc_get_product')) {
        throw new RuntimeException('WPAI_AUTHENTIC_CONFIG_BOOTSTRAP_NOT_PROVEN: missing plugin classes');
    }
    if (!is_dir($folder) && !wp_mkdir_p($folder)) {
        throw new RuntimeException('WPAI_LAB_UPLOAD_PREPARE_FAILED');
    }
    $initial = $repo . '/tests/wp-all-import-ci-lab/fixtures/initial.xml';
    if (!copy($initial, $file)) {
        throw new RuntimeException('WPAI_LAB_INPUT_COPY_FAILED');
    }
    $options = PMXI_Plugin::get_default_import_options();
    if (class_exists('PMWI_Plugin') && is_callable(['PMWI_Plugin', 'get_default_import_options'])) {
        $options += PMWI_Plugin::get_default_import_options();
    }
    $mapping = [
        'wizard_type' => 'new',
        'custom_type' => 'product',
        'post_type' => 'product',
        'title' => '{title[1]}',
        'unique_key' => '{synthetic_key[1]}',
        'single_product_type' => 'simple',
        'single_product_sku' => '{sku[1]}',
        'single_product_regular_price' => '{regular_price[1]}',
    ];
    $options = array_replace($options, $mapping);
    $relPath = function_exists('wp_all_import_get_relative_path')
        ? wp_all_import_get_relative_path($file) : $file;
    $import = new PMXI_Import_Record();
    $import->set([
        'name' => 'LAB Synthetic Product Import',
        'friendly_name' => 'LAB Synthetic Product Import',
        'type' => 'upload',
        'feed_type' => '',
        'path' => $relPath,
        'root_element' => 'product',
        'xpath' => '/data/product',
        'options' => $options,
        'count' => 1,
        'parent_import_id' => 0,
        'queue_chunk_number' => 0,
        'triggered' => 0,
        'processing' => 0,
        'executing' => 0,
        'imported' => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'registered_on' => date('Y-m-d H:i:s'),
        'last_activity' => date('Y-m-d H:i:s'),
    ])->save();
    $id = (int) $import->id;
    if ($id <= 0) {
        throw new RuntimeException('WPAI_AUTHENTIC_CONFIG_BOOTSTRAP_NOT_PROVEN: no ID');
    }
    $history = new PMXI_File_Record();
    $history->set([
        'import_id' => $id,
        'name' => basename($file),
        'path' => $relPath,
        'registered_on' => date('Y-m-d H:i:s'),
    ])->save();
    $readback = new PMXI_Import_Record();
    $readback->getById($id);
    if ($readback->isEmpty() || $readback->xpath !== '/data/product'
        || ($readback->options['unique_key'] ?? '') !== $mapping['unique_key']
        || ($readback->options['single_product_regular_price'] ?? '') !== $mapping['single_product_regular_price']
        || !is_file($file)) {
        throw new RuntimeException('WPAI_AUTHENTIC_CONFIG_BOOTSTRAP_NOT_PROVEN: native readback mismatch');
    }
    if (file_put_contents($importIdFile, $id . "\n") === false) {
        throw new RuntimeException('WPAI_LAB_IMPORT_ID_WRITE_FAILED');
    }
    $emit('bootstrap', [
        'classification' => 'LAB_SYNTHETIC_WPAI_CONFIG',
        'bootstrap_mechanism' => 'PMXI_Import_Record::set/save + PMXI_File_Record + native getById',
        'import_id' => $id,
        'saved_import_readback' => true,
        'name' => $readback->name,
        'xpath' => $readback->xpath,
        'root_element' => $readback->root_element,
        'mapping' => $mapping,
        'config_sha256' => hash_file('sha256', $configPath),
        'input_initial_sha256' => hash_file('sha256', $initial),
        'input_updated_sha256' => hash_file('sha256', $repo . '/tests/wp-all-import-ci-lab/fixtures/updated.xml'),
        'effective_saved_options_sha256' => hash('sha256', serialize($readback->options)),
        'source_model_file_sha256' => hash_file('sha256', WP_PLUGIN_DIR . '/wp-all-import-pro/models/import/record.php'),
    ]);
    echo "WPAI_AUTHENTIC_LAB_SAVED_IMPORT_ID=$id\n";
    return;
}
$id = is_file($importIdFile) ? (int) trim(file_get_contents($importIdFile)) : 0;
if ($id <= 0) {
    throw new RuntimeException('WPAI_MISSING_SAVED_IMPORT_ID');
}
$import = new PMXI_Import_Record();
$import->getById($id);
if ($import->isEmpty()) {
    throw new RuntimeException('WPAI_MISSING_SAVED_IMPORT_RECORD');
}
$productId = (int) wc_get_product_id_by_sku($f['sku']);
$product = $productId > 0 ? wc_get_product($productId) : false;
if (!$product || !$product->is_type('simple') || $product->get_sku() !== $f['sku']) {
    throw new RuntimeException('WPAI_LAB_NATIVE_PRODUCT_NOT_FOUND');
}
$price = $product->get_regular_price();
if ($stage === 'pre') {
    if ($price !== $f['start_regular_price']) {
        throw new RuntimeException('WPAI_LAB_INITIAL_NATIVE_PRICE_UNEXPECTED: ' . $price);
    }
    // Fixture-only metadata seed; not the importer or intended field update.
    update_post_meta($productId, $f['sentinel_meta_key'], $f['sentinel_value']);
    $sentinel = get_post_meta($productId, $f['sentinel_meta_key'], true);
    if ($sentinel !== $f['sentinel_value']) {
        throw new RuntimeException('WPAI_LAB_SENTINEL_SEED_FAILED');
    }
    $emit('pre-state', ['product_id' => $productId, 'sku' => $product->get_sku(),
        'type' => $product->get_type(), 'title' => $product->get_name(),
        'regular_price' => $price, 'sentinel' => $sentinel,
        'source' => 'WooCommerce CRUD read-back after first native import']);
    $updated = $repo . '/tests/wp-all-import-ci-lab/fixtures/updated.xml';
    if (!copy($updated, $file) || hash_file('sha256', $updated) !== hash_file('sha256', $file)) {
        throw new RuntimeException('WPAI_LAB_INPUT_REPLACE_FAILED');
    }
    echo "WPAI_PRE_IMPORT_STATE_CAPTURED\n";
    return;
}
$sentinel = get_post_meta($productId, $f['sentinel_meta_key'], true);
$post = [
    'product_id' => $productId, 'sku' => $product->get_sku(),
    'type' => $product->get_type(), 'title' => $product->get_name(),
    'regular_price' => $price, 'sentinel' => $sentinel,
    'import_id' => $id,
    'native_import_counters' => [
        'imported' => (int) $import->imported, 'created' => (int) $import->created,
        'updated' => (int) $import->updated, 'skipped' => (int) $import->skipped,
    ],
];
$emit('post-state', $post);
if ($price !== $f['expected_regular_price'] || $sentinel !== $f['sentinel_value']
    || $productId !== (int) (json_decode(file_get_contents($out . '/pre-state.json'), true)['product_id'] ?? 0)) {
    throw new RuntimeException('WPAI_NATIVE_IMPORT_OUTCOME_ASSERTION_FAILED');
}
echo "WPAI_NATIVE_PRODUCT_UPDATE_READBACK_PASS\n";
