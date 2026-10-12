<?php
declare(strict_types=1);

// Synthetic only: exact active 95-header schema, two byte-comparable native CSV inputs.
$root = dirname(__DIR__, 2);
$contract = json_decode((string) file_get_contents($root . '/references/current_package/v3.14.2/extracted/PROJECT_SOURCES/SLPE_WP_ALL_IMPORT_CONTRACT_v1.2.0.json'), true, 512, JSON_THROW_ON_ERROR);
$headers = $contract['schema_binding']['ordered_headers'] ?? [];
if (($contract['contract_version'] ?? '') !== '1.2.0' || count($headers) !== 95
    || count(array_unique($headers)) !== 95 || ($headers[2] ?? null) !== "Column\u{06F3}"
    || in_array('Column3', $headers, true)) {
    throw new RuntimeException('NATIVE_CSV_ACTIVE_95_COLUMN_SCHEMA_DRIFT');
}
$spec = [
    ['LAB-C3-UID-01','LAB-C3-SKU-01','LAB-C3-G-A','MODEL-C3-IDENTITY-01','111','آبی'],
    ['LAB-C3-UID-02','LAB-C3-SKU-02','LAB-C3-G-A','MODEL-C3-IDENTITY-02','0','قرمز'],
    ['LAB-C3-UID-03','LAB-C3-SKU-03','LAB-C3-G-B','MODEL-C3-IDENTITY-03','333','آبی'],
    ['LAB-C3-UID-04','LAB-C3-SKU-04','LAB-C3-G-B','MODEL-C3-IDENTITY-04','444','قرمز'],
];
$rows = [];
foreach ($spec as [$uid,$sku,$group,$identity,$price,$colour]) {
    $row = array_fill_keys($headers, '');
    $row['Column1'] = $uid;
    $row['Column2'] = $sku;
    $row["Column\u{06F3}"] = $identity;
    $row['Column8'] = $group;
    $row['Column27'] = 'variable';
    $row['Column28'] = 'PARENT_' . $group;
    $row['Price'] = $price;
    $row['Column10'] = '';
    $row['visibility'] = $price === '0' ? 'hidden' : 'visible';
    $row['Column4'] = "آزمون CSV؛\nخط دوم «اقتباس»";
    $row['Column13'] = 'رنگ';
    $row['Column14'] = $colour;
    if (array_keys($row) !== $headers) throw new RuntimeException('NATIVE_CSV_ROW_ORDER_DRIFT');
    $rows[] = $row;
}
$out = getenv('LAB_OUT') ?: sys_get_temp_dir();
if (!is_dir($out)) throw new RuntimeException('NATIVE_CSV_MISSING_OUTPUT_DIR');
$manifest = ['classification'=>'SYNTHETIC_NATIVE_CSV_PAIR','source_contract'=>'v1.2.0',
    'canonical_encoding'=>'UTF-8-BOM','delimiter'=>';','line_endings'=>'LF',
    'header_count'=>95,'row_count'=>4,'expected_rows'=>$spec,'variants'=>[]];
foreach (['persian','ascii'] as $variant) {
    $columnNames = $headers;
    if ($variant === 'ascii') $columnNames[2] = 'Column3';
    $file = $out . '/native-csv-' . $variant . '.csv';
    $stream = fopen($file, 'wb');
    if ($stream === false) throw new RuntimeException('NATIVE_CSV_CANNOT_OPEN');
    if (fwrite($stream, "\xEF\xBB\xBF") !== 3) throw new RuntimeException('NATIVE_CSV_BOM_WRITE_FAILED');
    fputcsv($stream, $columnNames, ';', '"', '', "\n");
    foreach ($rows as $row) fputcsv($stream, array_values($row), ';', '"', '', "\n");
    fclose($stream);
    $bytes = (string) file_get_contents($file);
    if (!str_starts_with($bytes, "\xEF\xBB\xBF") || str_contains($bytes, "\r") || !str_ends_with($bytes, "\n")) {
        throw new RuntimeException('NATIVE_CSV_SERIALIZATION_DRIFT');
    }
    $stream = fopen($file, 'rb');
    fread($stream, 3);
    $observedHeaders = fgetcsv($stream, 0, ';', '"', '');
    $parsed = [];
    while (($values = fgetcsv($stream, 0, ';', '"', '')) !== false) {
        if (count($values) !== 95) throw new RuntimeException('NATIVE_CSV_INVALID_ROW_WIDTH');
        $parsed[] = $values;
    }
    fclose($stream);
    if ($observedHeaders !== $columnNames || $parsed !== array_map('array_values', $rows)) {
        throw new RuntimeException('NATIVE_CSV_ROUNDTRIP_DRIFT');
    }
    $manifest['variants'][$variant] = ['filename'=>basename($file),'sha256'=>hash_file('sha256',$file),
        'third_header'=>$columnNames[2], 'third_header_utf8_hex'=>bin2hex($columnNames[2])];
}
$a = file($out.'/native-csv-persian.csv');
$b = file($out.'/native-csv-ascii.csv');
if (count($a) !== count($b) || array_slice($a, 1) !== array_slice($b, 1)) {
    throw new RuntimeException('NATIVE_CSV_PAIR_DATA_NOT_IDENTICAL');
}
file_put_contents($out.'/native-csv-fixtures.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n");
echo "NATIVE_CSV_95_COLUMN_PAIR_VERIFIED\n";
