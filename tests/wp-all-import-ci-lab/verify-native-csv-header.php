<?php
declare(strict_types=1);
// Independent verification from on-disk native saved-import and WooCommerce read-back evidence.
// A qualified mapping FAILURE is a legitimate diagnostic result, never relabeled a PASS.
$out = $argv[1] ?? '';
$load = static function (string $name) use ($out): array {
    $path = $out.'/'.$name.'.json';
    if (!is_file($path)) throw new RuntimeException('NATIVE_CSV_EVIDENCE_MISSING:'.$name);
    $value = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($value)) throw new RuntimeException('NATIVE_CSV_EVIDENCE_NOT_OBJECT:'.$name);
    return $value;
};
$manifest = $load('native-csv-fixtures');
$expected = $manifest['expected_rows'] ?? null;
if (!is_array($expected) || count($expected) !== 4
    || ($manifest['header_count'] ?? 0) !== 95
    || ($manifest['row_count'] ?? 0) !== 4
    || ($manifest['canonical_encoding'] ?? '') !== 'UTF-8-BOM'
    || ($manifest['delimiter'] ?? '') !== ';' || ($manifest['line_endings'] ?? '') !== 'LF') {
    throw new RuntimeException('NATIVE_CSV_FIXTURE_MANIFEST_INVALID');
}
$require = static function(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException('NATIVE_CSV_QUALIFICATION_REJECTED:'.$message);
};
$results = [];
$ids = [];
foreach (['persian','ascii'] as $variant) {
    $csv = $out.'/'.$manifest['variants'][$variant]['filename'];
    $require(is_file($csv) && hash_file('sha256',$csv) === $manifest['variants'][$variant]['sha256'],
        'wrong csv source/variant '.$variant);
    $bytes = (string)file_get_contents($csv);
    $require(str_starts_with($bytes, "\xEF\xBB\xBF") && !str_contains($bytes, "\r"),
        'noncanonical csv bytes '.$variant);
    $fp = fopen($csv, 'rb');
    fread($fp, 3);
    $headers = fgetcsv($fp, 0, ';', '"', '');
    $require(count($headers) === 95 && $headers[2] === ($variant==='persian' ? "Column\u{06F3}" : 'Column3')
        && $headers[2] === $manifest['variants'][$variant]['third_header'], 'wrong third header');
    $observed = [];
    while (($record = fgetcsv($fp, 0, ';', '"', '')) !== false) $observed[] = $record;
    fclose($fp);
    $require(count($observed) === 4, 'wrong source row count');
    foreach ($expected as $idx=>$row) {
        $require($observed[$idx][0] === $row[0] && $observed[$idx][1] === $row[1]
            && $observed[$idx][2] === $row[3] && $observed[$idx][7] === $row[2],
            'source row provenance/values changed '.$variant);
    }
}
$asciiRecords = (string) file_get_contents($out.'/native-csv-ascii.csv');
$persianRecords = (string) file_get_contents($out.'/native-csv-persian.csv');
$require(substr($asciiRecords, strpos($asciiRecords, "\n")+1) ===
    substr($persianRecords, strpos($persianRecords, "\n")+1), 'pair semantic data drift');
foreach (['persian','ascii'] as $variant) foreach (['ascii','persian','missing'] as $expression) foreach (['simple','variation'] as $mode) {
    if ($expression === 'missing' && $mode === 'variation') continue;
    $key = $variant.'-'.$expression.'-'.$mode;
    $bootstrap = $load('native-csv-'.$key.'-bootstrap');
    $first = $load('native-csv-'.$key.'-first');
    $second = $load('native-csv-'.$key.'-second');
    $status = $load('native-csv-'.$key.'-status');
    $clear = $load('native-csv-'.$key.'-clear');
    $mapping = $bootstrap['mapping'] ?? [];
    $expr = $expression==='ascii' ? '{column3[1]}' : ($expression==='persian' ? "{column\u{06F3}[1]}" : '{nonexistent_sentinel_field[1]}');
    $id = $bootstrap['import_id'] ?? 0;
    $require(($bootstrap['classification']??null)==='PINNED_LAB_NATIVE_CSV_SAVED_IMPORT'
        && ($bootstrap['case']??null)===$key
        && ($bootstrap['variant']??null)===$variant
        && ($bootstrap['expression_kind']??null)===$expression
        && ($bootstrap['mode']??null)===$mode
        && ($bootstrap['saved_readback']??false)===true
        && is_int($id) && $id>0 && !isset($ids[$id]),
        'native saved configuration readback missing '.$key);
    $ids[$id]=true;
    $require(($bootstrap['csv_sha256']??null)===$manifest['variants'][$variant]['sha256']
        && ($bootstrap['csv_fixture_header']??null)===$manifest['variants'][$variant]['third_header']
        && ($bootstrap['native_preparation']??null)==='PMXI_CsvParser (WP All Import plugin itself)'
        && ($bootstrap['native_parser_root']??null)==='data'
        && count($bootstrap['native_parser_values']??[])===4
        && count($bootstrap['native_parser_fields']??[])===95,
        'native parser origin/width missing '.$key);
    $nativeFields = $bootstrap['native_parser_fields'];
    $parserTag = $variant === 'persian' ? 'column' : 'column3';
    $require(in_array($parserTag, $nativeFields, true)
        && !in_array($variant==='persian' ? 'column3' : 'column', $nativeFields, true)
        && !in_array("column\u{06F3}", $nativeFields, true),
        'unexpected native parser field token shape '.$key);
    foreach ($bootstrap['native_parser_values'] as $i=>$record) {
        $require(($record['uid']??null)===$expected[$i][0]
            && ($record[$variant==='persian' ? 'stripped_token' : 'ascii_token']??null)===$expected[$i][3]
            && ($record[$variant==='persian' ? 'ascii_token' : 'stripped_token']??null)===null,
            'native parser lost identity or altered third-field token '.$key);
    }
    $require(($mapping['unique_key']??null)==='{column1[1]}'
        && ($mapping['single_product_sku']??null)==='{column2[1]}'
        && ($mapping['is_update_sku']??null)===0
        && ($bootstrap['selected_expression']??null)===$expr
        && ($mapping['title']??null)===($mode==='simple' ? $expr : '{column1[1]}'),
        'native mapping drift '.$key);
    if ($mode === 'variation') {
        $require(($mapping['single_product_id_first_is_variation']??null)===$expr
            && ($mapping['single_product_id_first_is_parent_id']??null)==='{column8[1]}'
            && ($mapping['single_product_first_is_parent_id_parent_sku']??null)==='{column28[1]}'
            && ($mapping['first_is_parent']??null)==='no'
            && ($mapping['variable_sku']??null)==='{column2[1]}',
            'variation identity mapping drift '.$key);
    }
    foreach ([$first,$second] as $state) {
        $require(($state['classification']??null)==='AUTHENTIC_WOOCOMMERCE_NATIVE_CSV_READBACK'
            && ($state['case']??null)===$key && ($state['import_id']??null)===$id
            && ($state['native_saved_path']??null)===$bootstrap['saved_import_path']
            && ($state['effective_saved_options_sha256']??null)===$bootstrap['effective_saved_options_sha256']
            && count($state['woocommerce']['records']??[])===4
            && count($state['woocommerce']['parents']??[])===2,
            'native Woo or persistence evidence incomplete '.$key);
        foreach ($state['woocommerce']['records'] as $i=>$p) {
            $require(($p['uid']??null)===$expected[$i][0]
                && ($p['sku']??null)===$expected[$i][1]
                && ($p['source_column3']??null)===$expected[$i][3]
                && ($p['source_group']??null)===$expected[$i][2],
                'Woo source identity fabricated/reordered '.$key);
        }
    }
    $require(($first['phase']??null)==='first' && ($second['phase']??null)==='second'
        && ($clear['cleared']??false)===true
        && ($clear['classification']??null)==='LAB_SYNTHETIC_CLEANUP',
        'native phases/cleanup absent '.$key);
    foreach (array_merge($clear['state']['records']??[], $clear['state']['parents']??[]) as $p) {
        $require(($p['product_id']??-1)===0,'cleanup incompletely observed '.$key);
    }
    foreach (['first','second'] as $phase) {
        $logName = 'native-csv-'.$key.'-'.$phase.'.log';
        $log = $out.'/'.$logName;
        $require(is_file($log) && filesize($log)>0, 'native CLI execution log absent '.$key);
        $statusCode = $status['cli_exit_codes'][$phase]??null;
        $require(is_int($statusCode) && $statusCode>=0, 'native CLI status missing '.$key);
    }
    $goodSimple = static function(array $state) use ($expected): bool {
        foreach ($state['woocommerce']['records'] as $i=>$p) {
            if (($p['product_id']??0)<=0 || ($p['type']??'')!=='simple'
                || ($p['title']??null)!==$expected[$i][3] || ($p['parent_id']??-1)!==null) return false;
        }
        return true;
    };
    $goodVariation = static function(array $state) use ($expected): bool {
        if (($state['woocommerce']['owned_variation_count']??-1)!==4) return false;
        $parents=[];
        foreach ($state['woocommerce']['parents'] as $p) {
            if (($p['type']??null)!=='variable'||($p['product_id']??0)<=0) return false;
            $parents[$p['sku']]=$p['product_id'];
        }
        if (count($parents)!==2 || $parents['PARENT_LAB-C3-G-A']===$parents['PARENT_LAB-C3-G-B']) return false;
        $ids=[];
        foreach ($state['woocommerce']['records'] as $i=>$p) {
            $parentSku='PARENT_'.$expected[$i][2];
            if (($p['product_id']??0)<=0 || ($p['type']??'')!=='variation'
                || ($p['parent_id']??0)!==$parents[$parentSku]
                || ($p['actual_parent_sku']??null)!==$parentSku
                || ($p['parent_type']??null)!=='variable'
                || !is_numeric($p['regular_price']??null)
                || (float)$p['regular_price']!==(float)$expected[$i][4]
                || isset($ids[$p['product_id']])) return false;
            $ids[$p['product_id']]=true;
        }
        return true;
    };
    $successful = ($status['cli_exit_codes']['first']===0 && $status['cli_exit_codes']['second']===0);
    $good = $successful && ($mode==='simple'
        ? $goodSimple($first['woocommerce']) && $goodSimple($second['woocommerce'])
        : $goodVariation($first['woocommerce']) && $goodVariation($second['woocommerce']));
    if ($good) {
        foreach ($first['woocommerce']['records'] as $i=>$p) {
            if ($p['product_id']!==$second['woocommerce']['records'][$i]['product_id']
                || $p['parent_id']!==$second['woocommerce']['records'][$i]['parent_id']) $good=false;
        }
        foreach ($first['woocommerce']['parents'] as $i=>$p) {
            if ($p['product_id']!==$second['woocommerce']['parents'][$i]['product_id']) $good=false;
        }
    }
    $parserResolves = in_array($expression === 'ascii' ? 'column3'
        : ($expression === 'persian' ? "column\u{06F3}" : 'nonexistent_sentinel_field'),
        $nativeFields, true);
    $results[$key] = ['mapping_and_identity_ok'=>$good,
        'expression_field_exists_in_native_parser'=>$parserResolves,
        'csv_variant'=>$variant,'expression'=>$expr,'mode'=>$mode,
        'first_cli_exit'=>$status['cli_exit_codes']['first'],
        'second_cli_exit'=>$status['cli_exit_codes']['second'],
        'first_woocommerce'=>$first['woocommerce'],'second_woocommerce'=>$second['woocommerce'],
        'native_parser_field_names'=>$bootstrap['native_parser_fields'],
        'native_parser_selected_field_values'=>$bootstrap['native_parser_values'],
        'native_options_sha256'=>$bootstrap['effective_saved_options_sha256'],
        'native_log_sha256'=>[
            'first'=>hash_file('sha256',$out.'/native-csv-'.$key.'-first.log'),
            'second'=>hash_file('sha256',$out.'/native-csv-'.$key.'-second.log')
        ]];
}
// Missing and spelling-mismatch controls must FAIL CLOSED: no blank-title product.
// A successful CLI exit or skipped row is not considered a resolved value.
foreach (['persian-missing-simple','ascii-missing-simple',
          'persian-ascii-simple','persian-persian-simple','ascii-persian-simple'] as $caseKey) {
    $case=$results[$caseKey];
    $require(($case['expression_field_exists_in_native_parser']??true)===false
        && ($case['mapping_and_identity_ok']??true)===false,
        'missing native expression incorrectly resolved '.$caseKey);
    foreach (['first_woocommerce','second_woocommerce'] as $stateKey) {
        foreach ($case[$stateKey]['records']??[] as $record) {
            $require(($record['product_id']??-1)===0 && ($record['title']??false)===null,
                'missing field silently created blank/wrong product '.$caseKey);
        }
    }
    $caseFirst=$load('native-csv-'.$caseKey.'-first');
    $require(($caseFirst['counters']['skipped']??-1)===4
        && ($caseFirst['counters']['created']??-1)===0,
        'missing field did not skip all four native source rows '.$caseKey);
}
$require($results['ascii-ascii-simple']['expression_field_exists_in_native_parser']===true
    && $results['ascii-ascii-variation']['expression_field_exists_in_native_parser']===true,
    'ASCII/ASCII parser lookup positive control absent');
// Controls must prove that the lab can resolve ordinary ASCII against ASCII.
// If they cannot, the target case cannot be interpreted, regardless of importer exit codes.
$require($results['ascii-ascii-simple']['mapping_and_identity_ok']
    && $results['ascii-ascii-variation']['mapping_and_identity_ok'],
    'positive ASCII/ASCII control did not establish authentic import baseline');
$targetSimple = $results['persian-ascii-simple']['mapping_and_identity_ok'];
$targetVariation = $results['persian-ascii-variation']['mapping_and_identity_ok'];
$targetVariationExpressionResolves = $results['persian-ascii-variation']['expression_field_exists_in_native_parser'];
$classification = $targetSimple && $targetVariation && $targetVariationExpressionResolves
    ? 'PINNED_LAB_HARMLESS_NORMALIZATION_OBSERVED'
    : (!$targetSimple ? 'PINNED_LAB_MATERIAL_COLUMN3_MAPPING_DEFECT_OBSERVED'
                      : 'PINNED_LAB_VARIATION_IDENTITY_OR_GROUPING_DEFECT_OBSERVED');
$report = [
    'schema_version'=>'native-column3-csv-parser-qualification-v1',
    'status'=>'EXECUTED_WITH_INDEPENDENT_POSITIVE_CONTROL',
    'classification'=>$classification,
    'target_ascii_expression_against_persian_header'=>[
        'simple_expression_resolves_and_creates_correct_titles'=>$targetSimple,
        'variation_expression_resolves'=>$targetVariationExpressionResolves,
        'variation_grouping_and_sku_based_product_creation_observed'=>$targetVariation
    ],
    'unresolved_field_is_not_silent_success'=>true,
    'persian_digit_explicit_expression'=>[
        'persian_header'=>$results['persian-persian-simple']['mapping_and_identity_ok'],
        'ascii_header'=>$results['ascii-persian-simple']['mapping_and_identity_ok']
    ],
    'cases'=>$results,
    'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN',
    'C1_PROVIDER_NATIVE_COMMA_CSV'=>'NOT_PROVEN',
    'C2_PROVIDER_NATIVE_XLSX'=>'NOT_PROVEN',
    'OWNER_SERIALIZATION_DECISION_READY'=>'NO',
    'SUCCESSOR_IMPLEMENTATION'=>'DO_NOT_START_YET',
    'PRODUCTION_EQUIVALENCE'=>'NOT_PROVEN',
];
$targetFile=$out.'/native-csv-qualification.json';
file_put_contents($targetFile, json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
// Falsify the core predicates, not a self-reported PASS boolean.
$mutations=[
 'wrong_source'=>static function(&$d){$d['persian-ascii-simple']['csv_variant']='ascii';},
 'blank_expected_title'=>static function(&$d){$d['ascii-ascii-simple']['first_woocommerce']['records'][0]['title']='';},
 'collapsed_identity'=>static function(&$d){$d['ascii-ascii-variation']['first_woocommerce']['records'][1]['product_id']=$d['ascii-ascii-variation']['first_woocommerce']['records'][0]['product_id'];},
 'cross_group_parent'=>static function(&$d){$d['ascii-ascii-variation']['first_woocommerce']['records'][2]['parent_id']=$d['ascii-ascii-variation']['first_woocommerce']['records'][0]['parent_id'];},
 'missing_readback'=>static function(&$d){unset($d['ascii-ascii-variation']['second_woocommerce']);}
];
// Independent adversarial result-level check, including missing readback and identity.
$validControl=static function(array $r)use($expected):bool{
    foreach(['ascii-ascii-simple','ascii-ascii-variation'] as $key){
        $c=$r[$key]??null;
        if(!$c||!isset($c['first_woocommerce'],$c['second_woocommerce'])
          ||($c['csv_variant']??null)!=='ascii')return false;
        foreach(['first_woocommerce','second_woocommerce'] as $phase){
            $w=$c[$phase];
            if(count($w['records']??[])!==4)return false;
            $ids=[];$groups=[];
            foreach($w['records'] as $i=>$p){
                if(($p['product_id']??0)<=0||isset($ids[$p['product_id']]))return false;
                $ids[$p['product_id']]=true;
                if($key==='ascii-ascii-simple'&&($p['title']??null)!==$expected[$i][3])return false;
                if($key==='ascii-ascii-variation'){
                    $g=$expected[$i][2];
                    if(($p['actual_parent_sku']??null)!=='PARENT_'.$g||($p['parent_id']??0)<=0)return false;
                    if(isset($groups[$g])&&$groups[$g]!==$p['parent_id'])return false;
                    $groups[$g]=$p['parent_id'];
                }
            }
            if($key==='ascii-ascii-variation'&&count(array_unique($groups))!==2)return false;
        }
    }
    if(($r['persian-ascii-simple']['csv_variant']??null)!=='persian')return false;
    return true;
};
$require($validControl($results), 'base falsification checks invalid');
foreach($mutations as $name=>$change) {
    $modified=$results;
    $change($modified);
    $require(!$validControl($modified), 'falsification mutation passed: '.$name);
}
echo "NATIVE_CSV_QUALIFICATION_VERIFIED ".$classification."\n";
