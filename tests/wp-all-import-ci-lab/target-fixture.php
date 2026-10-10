<?php
declare(strict_types=1);

// Deterministic synthetic 95-column fixture, intentionally NOT native WP All Import evidence.
// Run only as CI fixture assurance; current Master/Contract are read-only.
$repo = dirname(__DIR__, 2);
$contractFile = $repo . '/references/current_package/v3.14.2/extracted/PROJECT_SOURCES/SLPE_WP_ALL_IMPORT_CONTRACT_v1.2.0.json';
$contract = json_decode((string) file_get_contents($contractFile), true, 512, JSON_THROW_ON_ERROR);
$headers = $contract['schema_binding']['ordered_headers'] ?? [];
if (count($headers) !== 95 || count(array_unique($headers)) !== 95
    || ($contract['contract_version'] ?? '') !== '1.2.0'
    || ($contract['schema_binding']['header_count'] ?? 0) !== 95) {
    throw new RuntimeException('TARGET_FIXTURE_SCHEMA_DRIFT');
}
$required = ['Column1','Column2','Column8','Column27','Column28','Price','Column10','visibility','Column4'];
foreach ($required as $name) {
    if (!in_array($name, $headers, true)) throw new RuntimeException('TARGET_FIXTURE_MISSING_COLUMN:' . $name);
}
$make = static function (string $id, string $sku, string $group, string $price, string $visibility, string $description = '') use ($headers): array {
    $data = array_fill_keys($headers, '');
    $data['Column1'] = $id;
    $data['Column2'] = $sku;
    $data['Column8'] = $group;
    $data['Column27'] = $group === '' ? 'simple' : 'variable';
    $data['Column28'] = $group === '' ? '' : 'PARENT_' . $group;
    $data['Price'] = $price;
    $data['visibility'] = $visibility;
    $data['Column4'] = $description;
    return $data;
};
$rows = [
    $make('LAB-C1-SIMPLE', 'LAB-SKU-SIMPLE', '', '300', 'visible', "کلید روشنایی؛ «ساده»"),
    $make('LAB-C1-A1', 'LAB-SKU-A1', 'LAB-GROUP-A', '250', 'visible', "توضیح فارسی؛ خط اول\nخط دوم \"نقل قول\""),
    $make('LAB-C1-A2', 'LAB-SKU-A2', 'LAB-GROUP-A', '0', 'hidden'),
    $make('LAB-C1-B1', 'LAB-SKU-B1', 'LAB-GROUP-B', '290', 'visible'),
    $make('LAB-C1-B2', 'LAB-SKU-B2', 'LAB-GROUP-B', '330', 'visible'),
];
$validate = static function (array $items) use ($headers): void {
    $ids = $skus = $groups = $parents = [];
    foreach ($items as $row) {
        if (array_keys($row) !== $headers) throw new RuntimeException('TARGET_FIXTURE_95_COLUMN_ORDER_INVALID');
        $id = $row['Column1'];
        $sku = strtolower($row['Column2']);
        $group = $row['Column8'];
        $parent = $row['Column28'];
        if ($id === '' || isset($ids[$id])) throw new RuntimeException('TARGET_FIXTURE_COLUMN1_COLLISION');
        if ($sku === '' || isset($skus[$sku])) throw new RuntimeException('TARGET_FIXTURE_SKU_COLLISION');
        if ($row['Price'] === '' || !ctype_digit($row['Price'])) {
            throw new RuntimeException('TARGET_FIXTURE_BLANK_IS_NOT_ZERO');
        }
        if (($row['Price'] === '0' && $row['visibility'] !== 'hidden')
            || ($row['Price'] !== '0' && $row['visibility'] !== 'visible')) {
            throw new RuntimeException('TARGET_FIXTURE_PRICE_VISIBILITY_INCONSISTENT');
        }
        if ($group === '' && ($parent !== '' || $row['Column27'] !== 'simple')) {
            throw new RuntimeException('TARGET_FIXTURE_SIMPLE_HAS_PARENT');
        }
        if ($group !== '') {
            if ($row['Column27'] !== 'variable' || $parent !== 'PARENT_' . $group) {
                throw new RuntimeException('TARGET_FIXTURE_PARENT_SKU_INVALID');
            }
            if (isset($groups[$group]) && $groups[$group] !== $parent) {
                throw new RuntimeException('TARGET_FIXTURE_ONE_GROUP_MULTIPLE_PARENTS');
            }
            if (isset($parents[$parent]) && $parents[$parent] !== $group) {
                throw new RuntimeException('TARGET_FIXTURE_CROSS_GROUP_PARENT');
            }
            $groups[$group] = $parent;
            $parents[$parent] = $group;
        }
        $ids[$id] = $skus[$sku] = true;
    }
    if (count($groups) !== 2 || count($items) !== 5) throw new RuntimeException('TARGET_FIXTURE_GROUP_CENSUS_INVALID');
};
$validate($rows);
$out = getenv('LAB_OUT') ?: sys_get_temp_dir();
if (!is_dir($out)) throw new RuntimeException('TARGET_FIXTURE_OUTPUT_DIR_MISSING');
$path = $out . '/synthetic-95col.csv';
$handle = fopen($path, 'wb');
if ($handle === false) throw new RuntimeException('TARGET_FIXTURE_OPEN_FAILED');
fwrite($handle, "\xEF\xBB\xBF");
fputcsv($handle, $headers, ';', '"', '', "\n");
foreach ($rows as $row) fputcsv($handle, array_values($row), ';', '"', '', "\n");
fclose($handle);
$fp = fopen($path, 'rb');
if ($fp === false || fread($fp, 3) !== "\xEF\xBB\xBF") throw new RuntimeException('TARGET_FIXTURE_BOM_MISSING');
$gotHeaders = fgetcsv($fp, 0, ';', '"', '');
$read = [];
while (($values = fgetcsv($fp, 0, ';', '"', '')) !== false) {
    if (count($values) !== 95) throw new RuntimeException('TARGET_FIXTURE_CSV_ROW_WIDTH_DRIFT');
    $read[] = array_combine($gotHeaders, $values);
}
fclose($fp);
if ($gotHeaders !== $headers || $read !== $rows) throw new RuntimeException('TARGET_FIXTURE_ROUNDTRIP_FAILED');
// Negative controls deliberately corrupt independent material boundaries.
$rejects = [
    'blank_not_zero' => function () use ($rows, $validate): void {
        $bad=$rows; $bad[2]['Price']=''; $validate($bad);
    },
    'wrong_parent_sku' => function () use ($rows, $validate): void {
        $bad=$rows; $bad[2]['Column28']='PARENT_LAB-GROUP-B'; $validate($bad);
    },
    'duplicate_column1' => function () use ($rows, $validate): void {
        $bad=$rows; $bad[1]['Column1']=$bad[0]['Column1']; $validate($bad);
    },
    'duplicate_sku' => function () use ($rows, $validate): void {
        $bad=$rows; $bad[1]['Column2']=strtolower($bad[0]['Column2']); $validate($bad);
    },
    'reordered_columns' => function () use ($rows, $validate): void {
        $bad=$rows; $values=array_values($bad[0]); $keys=array_keys($bad[0]);
        [$keys[0],$keys[1]]=[$keys[1],$keys[0]];
        $bad[0]=array_combine($keys,$values); $validate($bad);
    }
];
foreach ($rejects as $name => $tamper) {
    try {$tamper();} catch (RuntimeException $e) {continue;}
    throw new RuntimeException('TARGET_FIXTURE_FALSE_PASS:' . $name);
}
$report = [
    'classification'=>'LAB_SYNTHETIC_INPUT_ONLY_NOT_NATIVE_CONSUMER_PROOF',
    'source_contract'=>'v1.2.0',
    'exact_ordered_headers'=>count($headers),
    'data_rows'=>count($rows),
    'fixture_sha256'=>hash_file('sha256',$path),
    'multiline_persian_roundtrip'=>true,
    'zero_distinct_from_blank'=>true,
    'negative_controls'=>array_keys($rejects),
    'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN',
    'C1_PROVIDER_NATIVE_COMMA_CSV'=>'NOT_PROVEN',
];
file_put_contents($out.'/synthetic-95col-report.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "WPAI_TARGET_95COL_SYNTHETIC_FIXTURE_PASS\n";
