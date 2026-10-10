<?php
declare(strict_types=1);
// Compare two truly executed native imports; A is RECONSTRUCTED reported options.
// The B safety contract fails closed; neither A outcome nor CLI success can fake it.
$dir=$argv[1]??'';
$get=static function($variant,$stage)use($dir):array {
    $path=$dir.'/dated-'.$variant.'-'.$stage.'.json';
    if(!is_file($path))throw new RuntimeException('DATED_AB_MISSING_EVIDENCE:'.$variant.'-'.$stage);
    return json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
};
$variants=[];
foreach(['A','B'] as $v){
    $variants[$v]=[
       'bootstrap'=>$get($v,'bootstrap'),
       'pre'=>$get($v,'pre'),
       'post'=>$get($v,'post')
    ];
}
$verify=static function(array $data):bool {
    foreach(['A','B'] as $v){
        $b=$data[$v]['bootstrap']??[];
        $pre=$data[$v]['pre']??[];
        $post=$data[$v]['post']??[];
        if(($b['native_save_readback']??false)!==true || ($b['actual_export_verified']??true)!==false
          ||($b['schema_headers']??null)!==95 ||($b['import_id']??0)<=0
          ||($pre['product_id']??0)<=0
          ||($pre['title']??null)!=='OWNER_INDEPENDENT_TITLE'
          ||($pre['custom_sentinel']??null)!=='OWNER_CUSTOM_KEEP'
          ||($pre['acf_like_sentinel']??null)!=='OWNER_ACF_LIKE_KEEP'
          ||($pre['next_source_readback']??false)!==true
          ||($post['classification']??null)!=='NATIVE_WOO_CRUD_OBSERVATION'
          ||($post['import_id']??0)!==$b['import_id']
          ||($post['product_id']??0)!==$pre['product_id']
          ||($post['type']??null)!=='simple'
          ||($post['native_counters']['updated']??0)<1
          ||($b['tested_mapping']['unique_key']??null)!=='{column1[1]}'
          ||($b['tested_mapping']['single_product_sku']??null)!=='{column2[1]}'
          ||($b['tested_mapping']['single_product_regular_price']??null)!=='{price[1]}'
          ||($b['tested_mapping']['single_product_sale_price']??null)!=='{column10[1]}'
          ||($b['tested_mapping']['single_product_visibility']??null)!=='{visibility[1]}')return false;
        if((string)$pre['regular_price']!=='12' && (string)$pre['regular_price']!=='12.00')return false;
        if(!is_numeric($post['regular_price']) || (float)$post['regular_price']!==0.0
          ||$post['sale_price']!=='')return false;
    }
    $a=$data['A']['bootstrap']['tested_mapping'];
    $b=$data['B']['bootstrap']['tested_mapping'];
    if($data['A']['bootstrap']['classification']!=='LAB_RECONSTRUCTED_FROM_OWNER_REPORTED_OPTIONS'
      ||$data['B']['bootstrap']['classification']!=='LAB_OWNER_APPROVED_TARGET_CANDIDATE'
      ||($a['is_update_sku']??null)!==1 ||($a['is_update_title']??null)!==1
      ||($a['is_update_custom_fields']??null)!==1 ||($a['is_update_acf']??null)!==1
      ||($a['is_product_visibility']??null)!=='xpath'
      ||array_key_exists('product_visibility_xpath',$a)
      ||($b['is_update_sku']??null)!==0 ||($b['is_update_title']??null)!==0
      ||($b['is_update_custom_fields']??null)!==0 ||($b['is_update_acf']??null)!==0
      ||($b['is_product_visibility']??null)!=='xpath'
      ||($b['product_visibility_xpath']??null)!=='{visibility[1]}')return false;
    $pre=$data['B']['pre'];$post=$data['B']['post'];
    if($post['sku']!==$pre['sku']
      ||$post['title']!=='OWNER_INDEPENDENT_TITLE'
      ||$post['custom_sentinel']!=='OWNER_CUSTOM_KEEP'
      ||$post['acf_like_sentinel']!=='OWNER_ACF_LIKE_KEEP'
      ||$post['catalog_visibility']!=='hidden')return false;
    return true;
};
if(!$verify($variants))throw new RuntimeException('DATED_AB_INDEPENDENT_NATIVE_OUTCOME_NOT_PROVEN');
$mutations=[
  'b_sku_overwritten'=>static function(&$s){$s['B']['post']['sku']='INCOMING_DIFFERENT_SKU';},
  'b_title_overwritten'=>static function(&$s){$s['B']['post']['title']='SOURCE_OVERRIDDEN_TITLE';},
  'b_visibility_wrong'=>static function(&$s){$s['B']['post']['catalog_visibility']='visible';},
  'b_unrelated_custom_field'=>static function(&$s){$s['B']['post']['custom_sentinel']='OVERRIDDEN';},
  'b_acf_like_meta'=>static function(&$s){$s['B']['post']['acf_like_sentinel']='OVERRIDDEN';},
  'b_product_id_changed'=>static function(&$s){$s['B']['post']['product_id']++;},
  'b_price_not_zero'=>static function(&$s){$s['B']['post']['regular_price']='12';},
  'b_sale_not_cleared'=>static function(&$s){$s['B']['post']['sale_price']='10';},
  'b_update_not_executed'=>static function(&$s){$s['B']['post']['native_counters']['updated']=0;},
  'a_reconstructed_mislabelled'=>static function(&$s){$s['A']['bootstrap']['actual_export_verified']=true;},
  'a_risky_flag_silenced'=>static function(&$s){$s['A']['bootstrap']['tested_mapping']['is_update_sku']=0;},
  'b_source_option_removed'=>static function(&$s){unset($s['B']['bootstrap']['tested_mapping']['product_visibility_xpath']);}
];
foreach($mutations as $name=>$mutation) {
    $changed=$variants;$mutation($changed);
    if($verify($changed))throw new RuntimeException('DATED_AB_NEGATIVE_FALSE_PASS:'.$name);
}
$logs=[];
foreach(['A','B'] as $v) {
    foreach(['initial','updated'] as $phase) {
        $file=$dir.'/dated-'.$v.'-'.$phase.'.log';
        $contents=is_file($file)?file_get_contents($file):false;
        if(!is_string($contents)||strpos($contents,'Success: Import completed.')===false){
            throw new RuntimeException('DATED_AB_NATIVE_CLI_LOG_MISSING:'.$v.'-'.$phase);
        }
        $logs[$v.'_'.$phase]=hash_file('sha256',$file);
    }
}
$summary=[
  'qualification'=>'OWNER_SUPPLIED_DATED_WPAI_BUNDLE_V1',
  'source_original_zip_access'=>'NOT_AVAILABLE',
  'a_config_classification'=>'RECONSTRUCTED_FROM_OWNER_REPORTED_OPTIONS_NOT_EXACT_EXPORT',
  'b_config_classification'=>'OWNER_APPROVED_LAB_CANDIDATE_NOT_PRODUCTION',
  'native_plugin'=>'WP_ALL_IMPORT_PRO_5_1_0_WOO_ADDON_4_0_6',
  'outcome'=>'PASS_FOR_MEASURED_BOUNDED_LAB_BEHAVIOR',
  'a_native_outcome'=>$variants['A']['post'],
  'b_native_outcome'=>$variants['B']['post'],
  'a_observed_risks'=>[
     'sku_changed'=>$variants['A']['post']['sku']!==$variants['A']['pre']['sku'],
     'title_changed'=>$variants['A']['post']['title']!==$variants['A']['pre']['title'],
     'custom_sentinel_changed'=>$variants['A']['post']['custom_sentinel']!==$variants['A']['pre']['custom_sentinel'],
     'acf_like_meta_changed'=>$variants['A']['post']['acf_like_sentinel']!==$variants['A']['pre']['acf_like_sentinel'],
     'visibility_not_hidden'=>$variants['A']['post']['catalog_visibility']!=='hidden',
  ],
  'zero_price_simple_b_verified'=>true,
  'zero_price_variation_business_display'=>'NOT_PROVEN',
  'acf_plugin_integrated'=>'NOT_PROVEN',
  'actual_export_config_identity'=>'NOT_PROVEN',
  'negative_controls_rejected'=>array_keys($mutations),
  'native_log_sha256'=>$logs,
  'EXACT_SWITCHLAND_CONSUMER'=>'NOT_PROVEN',
  'C1_PROVIDER_NATIVE_COMMA_CSV'=>'NOT_PROVEN',
  'C2_PROVIDER_NATIVE_XLSX'=>'NOT_PROVEN',
  'PRODUCTION_EQUIVALENCE'=>'NOT_PROVEN',
];
if(file_put_contents($dir.'/dated-ab-comparison.json',json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n")===false){
    throw new RuntimeException('DATED_AB_REPORT_WRITE_FAILED');
}
echo "DATED_AB_NATIVE_COMPARISON_AND_12_NEGATIVES_PASS\n";
