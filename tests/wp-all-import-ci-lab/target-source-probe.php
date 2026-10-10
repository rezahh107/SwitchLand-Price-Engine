<?php
// LAB-ONLY source/option inventory. Never reads Production or writes a plugin.
// Proprietary plugin text is not copied into the public repository or log.
$root = WP_PLUGIN_DIR;
$out = getenv('LAB_OUT');
if (!$out || !class_exists('PMXI_Plugin') || !class_exists('PMWI_Plugin')) {
    throw new RuntimeException('WPAI_TARGET_PROBE_RUNTIME_MISSING');
}
$names = [
    'first_is_parent', 'matching_parent', 'grouping_indicator', 'variable_sku',
    'parent_sku', 'variation_group', 'single_product_id_first_is_parent_id',
    'single_product_id_first_is_variation', 'single_product_first_is_parent_title_parent_sku',
    'create_new_product_if_no_parent', 'single_product_variable',
    'single_product_visibility', 'single_product_visibility_xpath', 'product_visibility', 'product_visibility_xpath',
    'is_product_type','multiple_product_type','single_product_type','is_multiple_grouping_product',
    'is_update_catalog_visibility', 'is_update_sale_price', 'is_update_sku',
    'single_product_regular_price', 'single_product_sale_price',
];
$result = [
    'schema_version' => '1.0.0',
    'classification' => 'INSTALLED_PLUGIN_SOURCE_AND_DEFAULTS_INVENTORY_ONLY',
    'packages' => ['core' => '5.1.0', 'woocommerce_addon' => '4.0.6'],
    'default_keys' => [],
    'seams' => [],
    'claims' => ['EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN', 'VARIATION_RUNTIME'=>'NOT_PROVEN'],
];
foreach (['core'=>PMXI_Plugin::get_default_import_options(),
           'woocommerce_addon'=>PMWI_Plugin::get_default_import_options()] as $package => $defaults) {
    if (!is_array($defaults)) throw new RuntimeException('WPAI_TARGET_PROBE_DEFAULTS_MISSING');
    foreach ($defaults as $key => $value) {
        foreach ($names as $needle) {
            if (stripos((string)$key, $needle) !== false) {
                $result['default_keys'][$package][$key] = is_scalar($value) ? $value : gettype($value);
                break;
            }
        }
    }
}
foreach (['core'=>'wp-all-import-pro', 'woocommerce_addon'=>'wpai-woocommerce-add-on'] as $package=>$slug) {
    $dir = realpath($root . '/' . $slug);
    if (!$dir || basename($dir) !== $slug) throw new RuntimeException('WPAI_TARGET_PROBE_PLUGIN_ROOT_MISSING');
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $entry) {
        if (!$entry->isFile() || strtolower($entry->getExtension()) !== 'php') continue;
        $lines = file($entry->getPathname());
        if ($lines === false) throw new RuntimeException('WPAI_TARGET_PROBE_SOURCE_UNREADABLE');
        foreach ($lines as $i => $line) {
        if (preg_match('/matching_parent|grouping_indicator|first_is_parent/i', $line)) {
            // Enumerate nearby quoted machine tokens, not licensed implementation text.
            for ($j=max(0,$i-2);$j<=min(count($lines)-1,$i+2);$j++) {
                if (!preg_match_all('/[\x27\x22]([a-z][a-z0-9_]{1,49})[\x27\x22]/i',$lines[$j],$tokens)) continue;
                foreach ($tokens[1] as $token) {
                    if (!isset($result['mode_tokens'][$token])) $result['mode_tokens'][$token]=[];
                    if (count($result['mode_tokens'][$token])>=5) continue;
                    $result['mode_tokens'][$token][]=['package'=>$package,'file'=>substr($entry->getPathname(),strlen($dir)+1),'line'=>$j+1];
                }
            }
        }
        // Extract quoted option identifiers only, never proprietary source text.
        if (stripos($line, 'visibility') !== false) {
            if (preg_match_all('/[\x27\x22]([a-zA-Z0-9_-]*visibility[a-zA-Z0-9_-]*)[\x27\x22]/i', $line, $quoted)) {
                foreach ($quoted[1] as $token) {
                    if (count($result['visibility_tokens'][$token] ?? []) >= 8) continue;
                    $result['visibility_tokens'][$token][] = [
                        'package'=>$package,
                        'file'=>substr($entry->getPathname(),strlen($dir)+1),
                        'line'=>$i+1,
                    ];
                }
            }
        }
            foreach ($names as $needle) {
                if (stripos($line, $needle) === false) continue;
                // Key presence and provenance only, not a semantic claim.
                if (count($result['seams'][$needle] ?? []) >= 28) continue;
                $result['seams'][$needle][] = [
                    'package' => $package,
                    'file' => substr($entry->getPathname(), strlen($dir)+1),
                    'line' => $i+1,
                ];
            }
        }
    }
}
$dest = $out . '/target-source-probe.json';
if (file_put_contents($dest, wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n") === false) {
    throw new RuntimeException('WPAI_TARGET_PROBE_WRITE_FAILURE');
}
echo "WPAI_TARGET_SOURCE_PROBE_AVAILABLE\n";
echo wp_json_encode(['default_keys'=>$result['default_keys'],
  'key_source_occurrences'=>array_map('count',$result['seams']),
  'visibility_tokens'=>array_keys($result['visibility_tokens'] ?? []),
  'mode_tokens'=>array_keys($result['mode_tokens'] ?? []),
  'source_file_sha256'=>hash_file('sha256',$dest)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
