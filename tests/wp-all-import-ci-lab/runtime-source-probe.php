<?php

// Called via WP-CLI eval-file, after activating the exact verified packages.
require_once ABSPATH . 'wp-admin/includes/plugin.php';
// Probes the real native plugin classes without creating fake import state.
$required = ['PMXI_Plugin', 'PMXI_Import_Record', 'PMXI_Cli'];
foreach ($required as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, "WPAI_NATIVE_SEAM_MISSING: $class\n");
        exit(1);
    }
}
if (!method_exists('PMXI_Plugin', 'get_default_import_options')) {
    fwrite(STDERR, "WPAI_NATIVE_SEAM_MISSING: default import options\n");
    exit(1);
}
$options = PMXI_Plugin::get_default_import_options();
if (!is_array($options) || !$options) {
    fwrite(STDERR, "WPAI_NATIVE_SEAM_INVALID: default import options\n");
    exit(1);
}
$model = new PMXI_Import_Record();
foreach (['set', 'save', 'getById', 'isEmpty'] as $method) {
    if (!is_callable([$model, $method])) {
        fwrite(STDERR, "WPAI_NATIVE_SEAM_MISSING: import model method $method\n");
        exit(1);
    }
}
$probe = [
    'schema_version' => '1.0.0',
    'classification' => 'EXACT_NATIVE_CLASSES_PROBED_NOT_SAVED_IMPORT',
    'plugin_versions' => [
        'core' => get_plugin_data(WP_PLUGIN_DIR . '/wp-all-import-pro/wp-all-import-pro.php', false, false)['Version'] ?? null,
        'woocommerce_addon' => get_plugin_data(WP_PLUGIN_DIR . '/wpai-woocommerce-add-on/wpai-woocommerce-add-on.php', false, false)['Version'] ?? null,
    ],
    'native_classes' => $required,
    'import_model_methods' => ['set', 'save', 'getById', 'isEmpty'],
    'default_option_keys' => array_values(array_intersect(
        array_keys($options),
        [
            'wizard_type', 'custom_type', 'unique_key', 'title',
            'single_product_regular_price', 'single_product_sku',
            'is_update', 'is_update_all', 'update_all_data',
            'update_products', 'is_update_categories',
            'single_product_type', 'post_type',
        ]
    )),
    'candidate_default_values' => array_intersect_key($options, array_flip([
        'wizard_type', 'custom_type', 'unique_key', 'title', 'single_product_regular_price',
        'single_product_sku', 'single_product_type', 'is_update', 'is_update_all',
        'update_all_data', 'update_products', 'is_update_categories',
        'post_type', 'is_update_post', 'update_post_title', 'update_post_meta',
    ])),
    'addon_defaults' => class_exists('PMWI_Plugin') && method_exists('PMWI_Plugin', 'get_default_import_options')
        ? array_intersect_key(PMWI_Plugin::get_default_import_options(), array_flip([
            'single_product_regular_price', 'single_product_sku', 'single_product_type'
        ])) : 'NOT_AVAILABLE',
    'bootstrap_disposition' => 'WPAI_AUTHENTIC_CONFIG_BOOTSTRAP_NOT_PROVEN',
];
$out = getenv('LAB_OUT') . '/runtime-source-probe.json';
if (file_put_contents($out, wp_json_encode($probe, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n") === false) {
    fwrite(STDERR, "WPAI_NATIVE_PROBE_WRITE_FAILURE\n");
    exit(1);
}
echo wp_json_encode($probe, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
