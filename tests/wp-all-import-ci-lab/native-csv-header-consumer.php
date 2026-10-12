<?php
// Disposable Lab ONLY. WP All Import's OWN CSV parser prepares its internal source;
// there is no independent CSV-to-XML converter or synthetic product importer.
$repo = getenv('GITHUB_WORKSPACE');
$out = getenv('LAB_OUT');
$stage = getenv('LAB_STAGE');
$variant = getenv('LAB_CSV_VARIANT');
$expression = getenv('LAB_CSV_EXPRESSION');
$mode = getenv('LAB_CSV_MODE');
if (!$repo || !$out || !in_array($stage, ['bootstrap','first','second','clear'], true)
    || !in_array($variant, ['persian','ascii'], true)
    || !in_array($expression, ['ascii','persian'], true)
    || !in_array($mode, ['simple','variation'], true)) {
    throw new RuntimeException('NATIVE_CSV_PROBE_INVALID_ENVIRONMENT');
}
$key = $variant . '-' . $expression . '-' . $mode;
$fixture = json_decode((string) file_get_contents($out . '/native-csv-fixtures.json'), true, 512, JSON_THROW_ON_ERROR);
$rows = $fixture['expected_rows'];
$csvFile = $out . '/' . $fixture['variants'][$variant]['filename'];
if (hash_file('sha256', $csvFile) !== $fixture['variants'][$variant]['sha256']) {
    throw new RuntimeException('NATIVE_CSV_WRONG_VARIANT_OR_SOURCE_DIGEST');
}
$expectedExpression = $expression === 'ascii' ? '{column3[1]}' : "{column\u{06F3}[1]}";
$emit = static function (string $suffix, array $evidence) use ($out, $key): void {
    if (file_put_contents($out . '/native-csv-' . $key . '-' . $suffix . '.json',
        wp_json_encode($evidence, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n") === false) {
        throw new RuntimeException('NATIVE_CSV_EVIDENCE_WRITE_FAILED');
    }
};
$importIdFile = $out . '/native-csv-' . $key . '-id.txt';
$readImport = static function(int $id): PMXI_Import_Record {
    $record = new PMXI_Import_Record();
    $record->getById($id);
    if ($record->isEmpty()) throw new RuntimeException('NATIVE_CSV_SAVED_IMPORT_NOT_FOUND');
    return $record;
};
$knownSkus = array_column($rows, 1);
$parentSkus = ['PARENT_LAB-C3-G-A','PARENT_LAB-C3-G-B'];
$readProducts = static function() use ($rows, $parentSkus): array {
    $results = [];
    foreach ($rows as [$uid,$sku,$group,$value,$price,$colour]) {
        $id = (int) wc_get_product_id_by_sku($sku);
        $p = $id ? wc_get_product($id) : false;
        $parent = $p && $p->get_parent_id() ? wc_get_product($p->get_parent_id()) : false;
        $results[] = [
            'uid'=>$uid,'sku'=>$sku,'source_column3'=>$value,'source_group'=>$group,
            'product_id'=>$p ? $p->get_id() : 0,'type'=>$p ? $p->get_type() : null,
            'title'=>$p ? $p->get_name() : null,
            'parent_id'=>$p ? $p->get_parent_id() : 0,
            'actual_parent_sku'=>$parent ? $parent->get_sku() : null,
            'parent_type'=>$parent ? $parent->get_type() : null,
            'regular_price'=>$p ? $p->get_regular_price() : null,
        ];
    }
    $parents = [];
    foreach ($parentSkus as $sku) {
        $id = (int) wc_get_product_id_by_sku($sku);
        $p = $id ? wc_get_product($id) : false;
        $parents[] = ['sku'=>$sku,'product_id'=>$p ? $p->get_id() : 0,
            'type'=>$p ? $p->get_type() : null];
    }
    $allVariationIds = get_posts(['post_type'=>'product_variation','post_status'=>'any',
        'posts_per_page'=>-1,'fields'=>'ids']);
    $ownedVariationIds = [];
    foreach ($allVariationIds as $vId) {
        $p = wc_get_product((int) $vId);
        if ($p && in_array($p->get_sku(), array_column($rows, 1), true)) {
            $ownedVariationIds[] = (int) $vId;
        }
    }
    return ['records'=>$results,'parents'=>$parents,'owned_variation_count'=>count($ownedVariationIds),
        'owned_variation_ids'=>$ownedVariationIds];
};
if (!function_exists('wc_get_product') || !class_exists('PMXI_Import_Record')
    || !class_exists('PMWI_Plugin') || !class_exists('PMXI_File_Record')) {
    throw new RuntimeException('NATIVE_CSV_PINNED_PLUGIN_RUNTIME_MISSING');
}
if ($stage === 'clear') {
    // Cleanup is limited to four known synthetic SKUs and their two parents.
    foreach (array_merge($knownSkus, $parentSkus) as $sku) {
        $id = (int) wc_get_product_id_by_sku($sku);
        $p = $id ? wc_get_product($id) : false;
        if ($p) $p->delete(true);
    }
    $after = $readProducts();
    foreach (array_merge($after['records'], $after['parents']) as $record) {
        if (($record['product_id'] ?? 0) !== 0) throw new RuntimeException('NATIVE_CSV_CLEANUP_INCOMPLETE');
    }
    $emit('clear', ['classification'=>'LAB_SYNTHETIC_CLEANUP','cleared'=>true,'state'=>$after]);
    return;
}
if ($stage === 'bootstrap') {
    $before = $readProducts();
    foreach (array_merge($before['records'], $before['parents']) as $record) {
        if (($record['product_id'] ?? 0) !== 0) {
            throw new RuntimeException('NATIVE_CSV_DIRTY_PRODUCT_BASELINE');
        }
    }
    $folder = wp_upload_dir()['basedir'] . '/wpallimport/files';
    if (!is_dir($folder) && !wp_mkdir_p($folder)) {
        throw new RuntimeException('NATIVE_CSV_UPLOAD_DIRECTORY_FAILED');
    }
    $nativeCsv = $folder . '/native-header-' . $key . '.csv';
    if (!copy($csvFile, $nativeCsv) || hash_file('sha256',$nativeCsv) !== hash_file('sha256',$csvFile)) {
        throw new RuntimeException('NATIVE_CSV_UPLOADED_INPUT_MISMATCH');
    }
    $pluginParser = WP_PLUGIN_DIR . '/wp-all-import-pro/libraries/XmlImportCsvParse.php';
    if (!is_file($pluginParser)) throw new RuntimeException('NATIVE_CSV_PINNED_PARSER_SOURCE_MISSING');
    require_once $pluginParser;
    if (!class_exists('PMXI_CsvParser')) throw new RuntimeException('NATIVE_CSV_NATIVE_PARSER_CLASS_MISSING');
    // Exact native plugin parser used by its administrative CSV upload wizard.
    $parser = new PMXI_CsvParser(['filename'=>$nativeCsv,'xpath'=>'','delimiter'=>';',
        'encoding'=>'UTF-8','targetDir'=>$folder]);
    $internalPath = $parser->xml_path;
    if (!is_string($internalPath) || !is_file($internalPath)) {
        throw new RuntimeException('NATIVE_CSV_PARSER_OUTPUT_MISSING');
    }
    $dom = new DOMDocument('1.0','UTF-8');
    if (!$dom->load($internalPath)) throw new RuntimeException('NATIVE_CSV_PARSER_INTERNAL_DOCUMENT_INVALID');
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//node');
    if (!$nodes || $nodes->length !== count($rows)) {
        throw new RuntimeException('NATIVE_CSV_PARSER_RECORD_COUNT_OR_XPATH_DRIFT');
    }
    $parserFields = [];
    $observedValues = [];
    foreach ($nodes as $index=>$node) {
        $fields = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) $fields[$child->nodeName] = $child->textContent;
        }
        if ($index === 0) $parserFields = array_keys($fields);
        $observedValues[] = ['uid'=>$fields['column1'] ?? null,
            'ascii_token'=>$fields['column3'] ?? null,
            'persian_token'=>$fields["column\u{06F3}"] ?? null,
            'stripped_token'=>$fields['column'] ?? null];
        if (($fields['column1'] ?? null) !== $rows[$index][0]
            || ($fields['column2'] ?? null) !== $rows[$index][1]) {
            throw new RuntimeException('NATIVE_CSV_PARSER_ROW_IDENTITY_OR_ORDER_INVALID');
        }
    }
    $core = PMXI_Plugin::get_default_import_options();
    $core += PMWI_Plugin::get_default_import_options();
    $common = [
        'wizard_type'=>'new','custom_type'=>'product','post_type'=>'product',
        'unique_key'=>'{column1[1]}',
        'single_product_sku'=>'{column2[1]}',
        'single_product_regular_price'=>'{price[1]}',
        'single_product_sale_price'=>'{column10[1]}',
        'single_product_visibility'=>'{visibility[1]}',
        'update_all_data'=>'no','is_update_custom_fields'=>0,
        'is_update_categories'=>0,'is_update_images'=>0,'is_update_content'=>0,
        'is_update_title'=>0,'is_update_sku'=>0,'is_update_product_type'=>0,
        'is_update_products'=>1,'is_update_price'=>1,'is_update_regular_price'=>1,
        'is_using_new_product_import_options'=>1
    ];
    if ($mode === 'simple') {
        $mapping = array_replace($common, ['title'=>$expectedExpression,
            'single_product_type'=>'simple','is_multiple_product_type'=>'yes',
            'multiple_product_type'=>'simple']);
    } else {
        $mapping = array_replace($common, [
            'title'=>'{column1[1]}','single_product_type'=>'variable',
            'is_multiple_product_type'=>'yes','multiple_product_type'=>'variable',
            'first_is_parent'=>'no','matching_parent'=>'first_is_parent_id',
            'grouping_indicator'=>'xpath',
            'single_product_id_first_is_parent_id'=>'{column8[1]}',
            'single_product_first_is_parent_id_parent_sku'=>'{column28[1]}',
            'single_product_id_first_is_variation'=>$expectedExpression,
            'variable_sku'=>'{column2[1]}',
            'create_new_product_if_no_parent'=>0,
            'attribute_name'=>['{column13[1]}'],
            'attribute_value'=>['{column14[1]}'],
            'in_variations'=>['1'],'is_taxonomy'=>['0'],'is_visible'=>['1']
        ]);
    }
    $options = array_replace($core, $mapping);
    $relativePath = wp_all_import_get_relative_path($internalPath);
    $import = new PMXI_Import_Record();
    $import->set(['name'=>'LAB Native Header '.$key,'friendly_name'=>'LAB Native Header '.$key,
        'type'=>'upload','feed_type'=>'','path'=>$relativePath,
        'root_element'=>'node','xpath'=>'//node','options'=>$options,'count'=>count($rows),
        'parent_import_id'=>0,'queue_chunk_number'=>0,'triggered'=>0,'processing'=>0,
        'executing'=>0,'imported'=>0,'created'=>0,'updated'=>0,'skipped'=>0,
        'registered_on'=>date('Y-m-d H:i:s'),'last_activity'=>date('Y-m-d H:i:s')])->save();
    $id = (int) $import->id;
    if ($id <= 0) throw new RuntimeException('NATIVE_CSV_IMPORT_SAVE_FAILED');
    $history = new PMXI_File_Record();
    $history->set(['import_id'=>$id,'name'=>basename($internalPath),
        'path'=>$relativePath,'registered_on'=>date('Y-m-d H:i:s')])->save();
    $saved = $readImport($id);
    foreach ($mapping as $field=>$expected) {
        if (!array_key_exists($field, $saved->options) || $saved->options[$field] !== $expected) {
            throw new RuntimeException('NATIVE_CSV_PERSISTED_MAPPING_DRIFT:'.$field);
        }
    }
    if ($saved->path !== $relativePath || $saved->xpath !== '//node') {
        throw new RuntimeException('NATIVE_CSV_PERSISTED_SOURCE_PATH_DRIFT');
    }
    file_put_contents($importIdFile, $id."\n");
    $emit('bootstrap', [
        'classification'=>'PINNED_LAB_NATIVE_CSV_SAVED_IMPORT',
        'case'=>$key,'variant'=>$variant,'expression_kind'=>$expression,'mode'=>$mode,
        'import_id'=>$id,'saved_readback'=>true,'effective_saved_options_sha256'=>hash('sha256',serialize($saved->options)),
        'mapping'=>$mapping,'selected_expression'=>$expectedExpression,'csv_sha256'=>hash_file('sha256',$nativeCsv),
        'native_parser_source_sha256'=>hash_file('sha256',$pluginParser),
        'native_parser_internal_source_sha256'=>hash_file('sha256',$internalPath),
        'native_parser_root'=>$dom->documentElement->nodeName,
        'native_parser_fields'=>$parserFields,'native_parser_values'=>$observedValues,
        'saved_import_path'=>$saved->path,'saved_import_xpath'=>$saved->xpath,
        'before_woocommerce'=>$before,'csv_fixture_header'=>$fixture['variants'][$variant]['third_header'],
        'csv_fixture_third_header_hex'=>$fixture['variants'][$variant]['third_header_utf8_hex'],
        'native_preparation'=>'PMXI_CsvParser (WP All Import plugin itself)',
        'source_is_private_owner_bundle'=>false,'production_equivalence'=>'NOT_PROVEN'
    ]);
    echo "NATIVE_CSV_SAVED_IMPORT_ID=".$id." CASE=".$key."\n";
    return;
}
$id = is_file($importIdFile) ? (int) trim(file_get_contents($importIdFile)) : 0;
if ($id <= 0) throw new RuntimeException('NATIVE_CSV_EXECUTION_ID_NOT_FOUND');
$import = $readImport($id);
$stageState = [
    'classification'=>'AUTHENTIC_WOOCOMMERCE_NATIVE_CSV_READBACK',
    'case'=>$key,'phase'=>$stage,'import_id'=>$id,
    'woocommerce'=>$readProducts(),
    'counters'=>['created'=>(int)$import->created,'updated'=>(int)$import->updated,
        'skipped'=>(int)$import->skipped,'imported'=>(int)$import->imported],
    'native_saved_path'=>$import->path,'effective_saved_options_sha256'=>hash('sha256',serialize($import->options))
];
$emit($stage, $stageState);
echo "NATIVE_CSV_WOO_READBACK_".strtoupper($stage)." ".$key."\n";
