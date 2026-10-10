<?php
declare(strict_types=1);
// Independent WooCommerce variation admission; deliberate corruption must fail.
// Never turn a missing/wrong native variation into a passing CI status.
$out=$argv[1]??'';
$load=static function($name)use($out):array {
    $path=$out.'/'.$name.'.json';
    if(!is_file($path))throw new RuntimeException('TARGET_VARIATION_EVIDENCE_MISSING:'.$name);
    $value=json_decode((string)file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
    if(!is_array($value))throw new RuntimeException('TARGET_VARIATION_EVIDENCE_INVALID');
    return $value;
};
$b=$load('target-variation-bootstrap');
$first=$load('target-variation-first');
$second=$load('target-variation-second');
$valid=static function(array $b,array $a,array $z):bool {
    if(($b['classification']??null)!=='LAB_SYNTHETIC_VARIATION_CANDIDATE'
      ||($b['saved_readback']??false)!==true
      ||($b['import_id']??0)<=0
      ||($b['mapping']['unique_key']??null)!=='{column1[1]}'
      ||($b['mapping']['single_product_sku']??null)!=='{column2[1]}'
      ||($b['mapping']['is_multiple_product_type']??null)!=='yes'
      ||($b['mapping']['multiple_product_type']??null)!=='variable'
      ||($b['mapping']['variable_sku']??null)!=='{column2[1]}'
      ||($b['mapping']['first_is_parent']??null)!=='no'
      ||($b['mapping']['matching_parent']??null)!=='first_is_parent_id'
      ||($b['mapping']['single_product_id_first_is_parent_id']??null)!=='{column8[1]}'
      ||($b['mapping']['single_product_first_is_parent_id_parent_sku']??null)!=='{column28[1]}'
      ||($b['mapping']['is_update_sku']??null)!==0) return false;
    foreach([$a,$z] as $s) {
        if(($s['classification']??null)!=='AUTHENTIC_WOOCOMMERCE_READBACK_VARIATION_CANDIDATE'
           ||($s['import_id']??0)!==$b['import_id']
           ||($s['product_parent_count']??0)!==2
           ||($s['variation_count']??0)!==4
           ||count($s['records']??[])!==4
           ||count($s['group_parents']??[])!==2) return false;
        $ids=[];$pids=[];$groups=[];
        foreach($s['group_parents'] as $p) {
            if(($p['product_id']??0)<=0||($p['type']??null)!=='variable')return false;
            if(isset($pids[$p['product_id']]))return false;
            $pids[$p['product_id']]=$p['sku'];
        }
        foreach($s['records'] as $r) {
            if(($r['product_id']??0)<=0||($r['type']??null)!=='variation'
              ||($r['parent_id']??0)<=0||($r['parent_type']??null)!=='variable'
              ||($r['actual_parent_sku']??null)!==$r['expected_parent_sku']
              ||!isset($pids[$r['parent_id']])
              ||$pids[$r['parent_id']]!==$r['expected_parent_sku'])return false;
            if(isset($ids[$r['product_id']]))return false;
            $ids[$r['product_id']]=true;
            $g=$r['group_id'];
            if(isset($groups[$g])&&$groups[$g]!==$r['parent_id'])return false;
            $groups[$g]=$r['parent_id'];
            if($r['regular_price']==='' || $r['regular_price']===null || !is_numeric($r['regular_price'])) return false;
        }
        if(count($groups)!==2||count(array_unique(array_values($groups)))!==2)return false;
    }
    $index=static function($state):array {
        $a=[];foreach($state['records'] as $r)$a[$r['sku']]=$r;
        return $a;
    };
    $one=$index($a);$two=$index($z);
    foreach($one as $sku=>$r) {
        if(!isset($two[$sku])||$two[$sku]['product_id']!==$r['product_id']
          ||$two[$sku]['parent_id']!==$r['parent_id'])return false;
    }
    $zero=$two['LAB-VAR-A2']??null;
    if(!$zero||!is_numeric($zero['regular_price'])||(float)$zero['regular_price']!==0.0)return false;
    return true;
};
$tests=[
 'parent_sku_ignored'=>static function(&$b,&$a,&$z){$z['records'][0]['actual_parent_sku']='';},
 'distinct_groups_merge'=>static function(&$b,&$a,&$z){$z['records'][2]['parent_id']=$z['records'][0]['parent_id'];$z['records'][2]['actual_parent_sku']=$z['records'][0]['actual_parent_sku'];},
 'duplicate_parent'=>static function(&$b,&$a,&$z){$z['product_parent_count']=3;},
 'missing_variation'=>static function(&$b,&$a,&$z){$z['records'][3]['product_id']=0;},
 'wrong_parent'=>static function(&$b,&$a,&$z){$z['records'][1]['parent_id']=$z['records'][2]['parent_id'];},
 'missing_native_parent_mapping'=>static function(&$b,&$a,&$z){unset($b['mapping']['single_product_first_is_parent_id_parent_sku']);},
 'missing_native_product_type'=>static function(&$b,&$a,&$z){unset($b['mapping']['multiple_product_type']);},
 'zero_misread_as_blank'=>static function(&$b,&$a,&$z){$z['records'][1]['regular_price']='';},
 'repeat_creates_new_parent'=>static function(&$b,&$a,&$z){$z['group_parents'][0]['product_id']++;},
 'repeat_creates_new_child'=>static function(&$b,&$a,&$z){$z['records'][0]['product_id']++;},
];
if(!$valid($b,$first,$second))throw new RuntimeException('TARGET_VARIATION_AUTHENTIC_WOO_OUTCOME_NOT_PROVEN');
foreach($tests as $name=>$tamper){
    $bb=$b;$aa=$first;$zz=$second;$tamper($bb,$aa,$zz);
    if($valid($bb,$aa,$zz))throw new RuntimeException('TARGET_VARIATION_FALSE_PASS:'.$name);
}
$digests=[];
foreach(['target-native-variation-initial.log','target-native-variation-repeat.log'] as $name) {
    $log=$out.'/'.$name;
    if(!is_file($log)||strpos((string)file_get_contents($log),'Success: Import completed.')===false)
      throw new RuntimeException('TARGET_VARIATION_CLI_EVIDENCE_MISSING:'.$name);
    $digests[$name]=hash_file('sha256',$log);
}
$report=[
 'classification'=>'CANDIDATE_VARIATION_WPAI_5_1_0_WOO_ADDON_4_0_6',
 'status'=>'PASS',
 'saved_config'=>$b,
 'first_woocommerce_readback'=>$first,
 'repeated_woocommerce_readback'=>$second,
 'negative_controls_rejected'=>array_keys($tests),
 'native_log_sha256'=>$digests,
 'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN',
 'PRODUCTION_EQUIVALENCE'=>'NOT_PROVEN',
];
file_put_contents($out.'/target-variation-evidence.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n");
echo "TARGET_VARIATION_NATIVE_WOO_AND_NEGATIVE_CONTROLS_PASS\n";
