<?php
declare(strict_types=1);
// Independent measured WooCommerce read-back verifier for target simple-product LAB.
// A failed or missing stage is never a PASS; deliberate tampering must be rejected.
$out=$argv[1]??'';
$load=static function($path):array {
    if(!is_file($path))throw new RuntimeException('TARGET_VERIFIER_REQUIRED_EVIDENCE_MISSING:'.basename($path));
    $r=json_decode((string)file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
    if(!is_array($r))throw new RuntimeException('TARGET_VERIFIER_INVALID_EVIDENCE');
    return $r;
};
$bootstrap=$load($out.'/target-simple-bootstrap.json');
$pre=$load($out.'/target-simple-pre.json');
$mid=$load($out.'/target-simple-mid.json');
$post=$load($out.'/target-simple-post.json');
$updated=$load($out.'/target-source-updated.json');
$zero=$load($out.'/target-source-zero.json');
$expected=[
    'pre'=>['regular_price'=>'12.00','sale_price'=>'10.00','catalog_visibility'=>'visible'],
    'mid'=>['regular_price'=>'19.50','sale_price'=>'17.00','catalog_visibility'=>'visible'],
    'post'=>['regular_price'=>'0.00','sale_price'=>'','catalog_visibility'=>'hidden'],
];
$verify=static function(array $b,array $states,array $sources)use($expected):bool {
    if(($b['classification']??null)!=='LAB_SYNTHETIC_CONFIGURATION'
        ||($b['readback']??false)!==true||($b['import_id']??0)<=0
        ||($b['mapping']['unique_key']??null)!=='{column1[1]}'
        ||($b['mapping']['single_product_sku']??null)!=='{column2[1]}'
        ||($b['mapping']['single_product_regular_price']??null)!=='{price[1]}'
        ||($b['mapping']['single_product_sale_price']??null)!=='{column10[1]}'
        ||($b['mapping']['single_product_visibility']??null)!=='{visibility[1]}'
        ||($b['mapping']['is_product_visibility']??null)!=='xpath'
        ||($b['mapping']['product_visibility_xpath']??null)!=='{visibility[1]}'
        ||($b['mapping']['is_update_sku']??null)!==0
        ||($b['mapping']['is_update_catalog_visibility']??null)!==1
        ||($b['mapping']['is_update_regular_price']??null)!==1
        ||($b['mapping']['is_update_sale_price']??null)!==1) return false;
    $id=$states['pre']['product_id']??0;
    foreach($expected as $name=>$wanted) {
        $s=$states[$name]??[];
        if($id<=0||($s['product_id']??0)!==$id
            ||($s['import_id']??0)!==$b['import_id']
            ||($s['sku']??'')!=='LAB-TARGET-SIMPLE'
            ||($s['title']??'')!=='KEEP_OWNER_TITLE'
            ||($s['type']??'')!=='simple'
            ||($s['sentinel']??'')!=='DO_NOT_CHANGE'
            ||($s['source']??'')!=='WooCommerce CRUD read-back') return false;
        foreach($wanted as $key=>$value)if(($s[$key]??null)!==$value)return false;
    }
    foreach(['mid','post'] as $name) {
        if((int)($states[$name]['import_counters']['updated']??0)<1)return false;
    }
    foreach(['updated','zero'] as $name) {
        if(($sources[$name]['source_state']??null)!==$name
          ||($sources[$name]['import_id']??null)!==$b['import_id']
          ||($sources[$name]['path_readback']??false)!==true
          ||!preg_match('/^[a-f0-9]{64}$/',(string)($sources[$name]['source_sha256']??'')))return false;
    }
    return true;
};
$states=['pre'=>$pre,'mid'=>$mid,'post'=>$post];
$sources=['updated'=>$updated,'zero'=>$zero];
if(!$verify($bootstrap,$states,$sources))throw new RuntimeException('TARGET_SIMPLE_NATIVE_OUTCOME_FAILED');
$mutations=[
    'parent_identity_replacement' => static function(&$b,&$s,&$src){$s['post']['product_id']++;},
    'protected_sku_changed' => static function(&$b,&$s,&$src){$s['post']['sku']='LAB-TARGET-SIMPLE-SOURCE-CHANGED';},
    'protected_title_changed' => static function(&$b,&$s,&$src){$s['mid']['title']='OVERRIDDEN_BY_IMPORT';},
    'unrelated_sentinel_changed' => static function(&$b,&$s,&$src){$s['mid']['sentinel']='CORRUPTED';},
    'blank_masquerades_as_zero' => static function(&$b,&$s,&$src){$s['post']['regular_price']='';},
    'catalog_visibility_not_updated' => static function(&$b,&$s,&$src){$s['post']['catalog_visibility']='visible';},
    'sale_price_not_updated' => static function(&$b,&$s,&$src){$s['mid']['sale_price']='10.00';},
    'saved_field_missing' => static function(&$b,&$s,&$src){unset($b['mapping']['single_product_sale_price']);},
    'missing_visibility_mode' => static function(&$b,&$s,&$src){unset($b['mapping']['is_product_visibility']);},
    'wrong_visibility_xpath' => static function(&$b,&$s,&$src){$b['mapping']['product_visibility_xpath']='{column8[1]}';},
    'native_source_readback_missing' => static function(&$b,&$s,&$src){$src['zero']['path_readback']=false;},
    'native_update_not_executed' => static function(&$b,&$s,&$src){$s['mid']['import_counters']['updated']=0;},
];
foreach($mutations as $name=>$mutate) {
    $b=$bootstrap;$s=$states;$src=$sources;$mutate($b,$s,$src);
    if($verify($b,$s,$src))throw new RuntimeException('TARGET_SIMPLE_VERIFIER_FALSE_PASS:'.$name);
}
$logPaths=['target-native-initial.log','target-native-updated.log','target-native-zero.log'];
$logDigests=[];
foreach($logPaths as $log) {
    $p=$out.'/'.$log;
    if(!is_file($p)||strpos((string)file_get_contents($p),'Success: Import completed.')===false) {
        throw new RuntimeException('TARGET_SIMPLE_NATIVE_CLI_NOT_PROVEN:'.$log);
    }
    $logDigests[$log]=hash_file('sha256',$p);
}
$report=[
    'classification'=>'CANDIDATE_RUNTIME_QUALIFICATION_WPAI_5_1_0_WOO_ADDON_4_0_6',
    'scenario'=>'EXISTING_SIMPLE_PRODUCT_COLUMN_MAPPING_PRICE_SALE_VISIBILITY_AND_PROTECTED_SKU',
    'status'=>'PASS',
    'actual_woocommerce_readbacks'=>$states,
    'native_saved_import'=>$bootstrap,
    'native_source_readbacks'=>$sources,
    'native_cli_logs_sha256'=>$logDigests,
    'negative_controls_rejected'=>array_keys($mutations),
    'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN',
    'PRODUCTION_EQUIVALENCE'=>'NOT_PROVEN',
    'C1_PROVIDER_NATIVE_COMMA_CSV'=>'NOT_PROVEN',
    'C2_PROVIDER_NATIVE_XLSX'=>'NOT_PROVEN',
];
file_put_contents($out.'/target-simple-evidence.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n");
echo "WPAI_TARGET_SIMPLE_NATIVE_READBACK_AND_NEGATIVE_CONTROLS_PASS\n";
