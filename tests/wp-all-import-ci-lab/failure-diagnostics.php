<?php
// Fail-closed diagnostic, called only after authentic native behavior did not match.
$repo = getenv('GITHUB_WORKSPACE');
$out = getenv('LAB_OUT');
$idPath = $out . '/import-id.txt';
$id = is_file($idPath) ? (int) trim(file_get_contents($idPath)) : 0;
$data = [
    'classification' => 'FAILED_NATIVE_EXECUTION_DIAGNOSTIC',
    'import_id' => $id,
    'products_count' => count(get_posts(['post_type' => 'product', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids'])),
];
if ($id > 0) {
    $import = new PMXI_Import_Record();
    $import->getById($id);
    if (!$import->isEmpty()) {
        $path = (string) $import->path;
        $absolute = function_exists('wp_all_import_get_absolute_path')
            ? wp_all_import_get_absolute_path($path) : $path;
        $chunkData = [];
        if (class_exists('PMXI_Chunk') && is_file($absolute)) {
            try {
                $chunk = new PMXI_Chunk($absolute, ['element' => (string)$import->root_element]);
                $xml = $chunk->read();
                if (is_string($xml)) {
                    $dom = new DOMDocument();
                    if (@$dom->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml)) {
                        $xpathProbe = new DOMXPath($dom);
                        $chunkData = [
                            'chunk_document_element' => $dom->documentElement->nodeName,
                            'saved_xpath_matches' => $xpathProbe->query((string)$import->xpath)->length,
                            'alternative_xpath_matches' => $xpathProbe->query('//product')->length,
                        ];
                    }
                }
                $chunkData += [
                    'first_chunk_bytes' => is_string($xml) ? strlen($xml) : null,
                    'first_chunk_sha256' => is_string($xml) ? hash('sha256', $xml) : null,
                    'chunk_root' => $import->root_element,
                ];
            } catch (Throwable $exception) {
                $chunkData = ['native_chunk_error' => $exception->getMessage()];
            }
        }
        $data['import'] = [
            'native_chunk_probe' => $chunkData,
            'path' => $path, 'absolute_path' => $absolute,
            'file_exists' => is_file($absolute),
            'file_size' => is_file($absolute) ? filesize($absolute) : null,
            'xpath' => $import->xpath, 'root_element' => $import->root_element,
            'imported' => (int) $import->imported,
            'created' => (int) $import->created,
            'updated' => (int) $import->updated,
            'skipped' => (int) $import->skipped,
            'count' => (int) $import->count,
            'status_flags' => [
                'triggered' => $import->triggered,
                'executing' => $import->executing,
                'processing' => $import->processing,
            ],
            'mapping' => array_intersect_key($import->options, array_flip([
                'wizard_type', 'custom_type', 'title', 'unique_key',
                'single_product_sku', 'single_product_regular_price',
                'single_product_type', 'update_all_data'
            ])),
        ];
    } else {
        $data['error'] = 'NATIVE_IMPORT_RECORD_NOT_FOUND';
    }
}
file_put_contents($out . '/failure-diagnostics.json', wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
