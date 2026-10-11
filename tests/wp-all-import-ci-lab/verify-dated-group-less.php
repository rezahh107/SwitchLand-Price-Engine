<?php
declare(strict_types=1);
// Diagnostic evidence completeness only; no business acceptance for group-less row.
$out=$argv[1]??'';
$get=static function($name)use($out):array {
  $path=$out.'/dated-group-less-'.$name.'.json';
  if(!is_file($path))throw new RuntimeException('DATED_GROUPLESS_MISSING_EVIDENCE:'.$name);
  return json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
};
$d=[];
foreach(['A','B'] as $v)$d[$v]=['bootstrap'=>$get($v.'-bootstrap'),'post'=>$get($v.'-post')];
// Validate the actual native WooCommerce readback, not only fixture roles/counts.
// All observations remain synthetic; row 757 of the private Bundle is unresolved.
$verify=static function(array $value):bool {
    $allIds=[];
    foreach(['A','B'] as $v) {
        $b=$value[$v]['bootstrap']??[];
        $p=$value[$v]['post']??[];
        $mapping=$b['mapping']??[];
        $parentSku='PARENT_LAB-ORPHAN-'.$v.'-G';
        if(($b['classification']??null)!==($v==='A'?'LAB_RECONSTRUCTED_REPORTED_OPTIONS':'LAB_OWNER_TARGET_CANDIDATE')
          ||($b['input_count']??null)!==3 ||($b['schema_columns']??null)!==95
          ||($b['group_less_variable_input_count']??null)!==1
          ||($b['native_readback']??false)!==true
          ||!is_int($b['saved_import_id']??null) ||$b['saved_import_id']<=0
          ||($b['group_less_row_business_classification']??null)!=='UNRESOLVED'
          ||($mapping['unique_key']??null)!=='{column1[1]}'
          ||($mapping['single_product_sku']??null)!=='{column2[1]}'
          ||($mapping['single_product_id_first_is_parent_id']??null)!=='{column8[1]}'
          ||($mapping['single_product_first_is_parent_id_parent_sku']??null)!=='{column28[1]}'
          ||($mapping['single_product_id_first_is_variation']??null)!=='{column2[1]}'
          ||($mapping['first_is_parent']??null)!==($v==='A'?'yes':'no')
          ||($mapping['is_multiple_product_type']??null)!==($v==='A'?'no':'yes')
          ||($mapping['matching_parent']??null)!=='first_is_parent_id'
          ||($p['classification']??null)!=='SYNTHETIC_NATIVE_GROUPLESS_DIAGNOSTIC_NOT_BUSINESS_ADMISSION'
          ||($p['variant']??null)!==$v
          ||($p['exact_owner_row_classification']??null)!=='NOT_PROVEN'
          ||($p['allowed_business_inference']??null)!=='NONE'
          ||($p['import_id']??null)!==$b['saved_import_id']
          ||($p['readback_count']??null)!==3
          ||!is_array($p['records']??null)
          ||!array_is_list($p['records'])
          ||count($p['records'])!==3) return false;
        $records=$p['records'];
        $anchor=$records[0];
        if(($anchor['synthetic_role']??null)!=='GROUPLESS_POSSIBLE_ANCHOR'
          ||!is_int($anchor['product_id']??null) ||$anchor['product_id']<=0
          ||($anchor['type']??null)!=='simple'
          ||($anchor['parent_id']??null)!==0
          ||!array_key_exists('parent_sku',$anchor) ||$anchor['parent_sku']!==null
          ||!array_key_exists('group_member_expected_parent_sku',$anchor)
          ||$anchor['group_member_expected_parent_sku']!==null
          ||!is_numeric($anchor['price']??null)
          ||(float)$anchor['price']!==0.0) return false;
        $ids=[$anchor['product_id']];
        $parentId=null;
        foreach([1=>200,2=>300] as $index=>$expectedPrice) {
            $child=$records[$index];
            if(($child['synthetic_role']??null)!=='GROUP_MEMBER'
              ||!is_int($child['product_id']??null) ||$child['product_id']<=0
              ||($child['type']??null)!=='variation'
              ||!is_int($child['parent_id']??null) ||$child['parent_id']<=0
              ||($child['parent_sku']??null)!==$parentSku
              ||($child['group_member_expected_parent_sku']??null)!==$parentSku
              ||!is_numeric($child['price']??null)
              ||(float)$child['price']!==(float)$expectedPrice) return false;
            if(in_array($child['product_id'],$ids,true))return false;
            $ids[]=$child['product_id'];
            if($parentId===null)$parentId=$child['parent_id'];
            elseif($parentId!==$child['parent_id'])return false;
        }
        // The group-less product and children cannot be their own parents.
        if(in_array($parentId,$ids,true))return false;
        foreach(array_merge($ids,[$parentId]) as $id) {
            if(isset($allIds[$id]))return false; // separate A/B native products
            $allIds[$id]=true;
        }
    }
    return true;
};
if(!$verify($d))throw new RuntimeException('DATED_GROUPLESS_NATIVE_DIAGNOSTIC_INVALID');
$negative=[
 'misclassified_anchor'=>static function(&$x){$x['B']['post']['exact_owner_row_classification']='VALID_PARENT';},
 'missing_anchor_input'=>static function(&$x){$x['A']['bootstrap']['group_less_variable_input_count']=0;},
 'missing_native_record'=>static function(&$x){array_pop($x['B']['post']['records']);},
 'missing_native_saved_import'=>static function(&$x){$x['A']['bootstrap']['native_readback']=false;},
 'invented_business_admission'=>static function(&$x){$x['B']['post']['allowed_business_inference']='VALID';},
 'fake_variant_role'=>static function(&$x){$x['A']['post']['records'][0]['synthetic_role']='GROUP_MEMBER';},
 'missing_native_anchor'=>static function(&$x){$x['A']['post']['records'][0]['product_id']=0;},
 'missing_native_child'=>static function(&$x){$x['B']['post']['records'][1]['product_id']=0;},
 'duplicate_product_identity'=>static function(&$x){$x['B']['post']['records'][2]['product_id']=$x['B']['post']['records'][1]['product_id'];},
 'invalid_product_id_type'=>static function(&$x){$x['A']['post']['records'][1]['product_id']='23';},
 'wrong_anchor_type'=>static function(&$x){$x['B']['post']['records'][0]['type']='variable';},
 'wrong_child_type'=>static function(&$x){$x['A']['post']['records'][1]['type']='simple';},
 'anchor_attached_to_parent'=>static function(&$x){$x['A']['post']['records'][0]['parent_id']=1;},
 'anchor_wrong_price'=>static function(&$x){$x['B']['post']['records'][0]['price']='200';},
 'child_wrong_price'=>static function(&$x){$x['A']['post']['records'][2]['price']='301';},
 'child_wrong_parent_sku'=>static function(&$x){$x['B']['post']['records'][1]['parent_sku']='PARENT_WRONG';},
 'child_expected_parent_sku_wrong'=>static function(&$x){$x['B']['post']['records'][1]['group_member_expected_parent_sku']='PARENT_WRONG';},
 'child_missing_parent_id'=>static function(&$x){$x['A']['post']['records'][2]['parent_id']=0;},
 'children_cross_parent'=>static function(&$x){$x['B']['post']['records'][2]['parent_id']++;},
 'cross_variant_identity_collision'=>static function(&$x){$x['B']['post']['records'][0]['product_id']=$x['A']['post']['records'][0]['product_id'];},
 'wrong_grouping_option'=>static function(&$x){$x['B']['bootstrap']['mapping']['single_product_id_first_is_parent_id']='{column3[1]}';},
];
foreach($negative as $name=>$mutate) {
    $copy=$d;
    $mutate($copy);
    if($copy===$d || $verify($copy))throw new RuntimeException('DATED_GROUPLESS_FALSE_PASS:'.$name);
}
$logs=[];
foreach(['A','B'] as $v) {
 $log=$out.'/dated-group-less-'.$v.'.log';
 $body=is_file($log)?file_get_contents($log):false;
 if(!is_string($body)||strpos($body,'Success: Import completed.')===false)
     throw new RuntimeException('DATED_GROUPLESS_NATIVE_RUN_NOT_PROVEN:'.$v);
 $logs[$v]=hash_file('sha256',$log);
}
$report=[
 'classification'=>'SYNTHETIC_NATIVE_GROUPLESS_DIAGNOSTIC',
 'observations'=>['A'=>$d['A']['post'],'B'=>$d['B']['post']],
 'negative_controls_rejected'=>array_keys($negative),
 'native_log_sha256'=>$logs,
 'owner_dated_row_business_classification'=>'UNRESOLVED_ORPHAN_VS_PARENT_ANCHOR_VS_EXCEPTION',
 'owner_dated_row_bytes_available'=>false,
 'policy_first_row_as_parent_allowed'=>false,
 'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN'
];
file_put_contents($out.'/dated-group-less-evidence.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "DATED_GROUPLESS_NATIVE_DIAGNOSTIC_AND_".count($negative)."_NEGATIVES_PASS_BUSINESS_NOT_PROVEN\n";
