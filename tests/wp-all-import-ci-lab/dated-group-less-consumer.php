<?php
// Lab-only classification probe for ONE group-less variable-like source row.
// Does not infer whether a real dated row is an explicit parent anchor.
$variant=getenv('LAB_VARIANT');
$stage=getenv('LAB_STAGE');
$out=getenv('LAB_OUT');
$repo=getenv('GITHUB_WORKSPACE');
if(!in_array($variant,['A','B'],true)||!in_array($stage,['bootstrap','readback'],true)||!$repo||!$out) {
  throw new RuntimeException('DATED_GROUPLESS_INVALID_ENV');
}
$dir=wp_upload_dir()['basedir'].'/wpallimport/files';
$prefix='LAB-ORPHAN-'.$variant;
$rows=[
 ['column1'=>$prefix.'-ANCHOR','column2'=>$prefix.'-ANCHOR-SKU','column8'=>'','column28'=>'','column3'=>'Synthetic anchor candidate','column27'=>'variable','price'=>'0','colour'=>''],
 ['column1'=>$prefix.'-CHILD1','column2'=>$prefix.'-CHILD1-SKU','column8'=>$prefix.'-G','column28'=>'PARENT_'.$prefix.'-G','column3'=>'Synthetic child','column27'=>'variable','price'=>'200','colour'=>'Blue'],
 ['column1'=>$prefix.'-CHILD2','column2'=>$prefix.'-CHILD2-SKU','column8'=>$prefix.'-G','column28'=>'PARENT_'.$prefix.'-G','column3'=>'Synthetic child','column27'=>'variable','price'=>'300','colour'=>'Red'],
];
$emit=static function($name,$data)use($out) {
  if(file_put_contents($out.'/'.$name.'.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n")===false)
     throw new RuntimeException('DATED_GROUPLESS_EVIDENCE_WRITE_FAILED');
};
$read=static function($id) {
  $r=new PMXI_Import_Record();$r->getById($id);
  if($r->isEmpty())throw new RuntimeException('DATED_GROUPLESS_IMPORT_NOT_FOUND');
  return $r;
};
$idFile=$out.'/dated-group-less-'.$variant.'-id.txt';
if($stage==='bootstrap') {
  if(!is_dir($dir)&&!wp_mkdir_p($dir))throw new RuntimeException('DATED_GROUPLESS_UPLOAD_FAILED');
  $contract=json_decode(file_get_contents($repo.'/references/current_package/v3.14.2/extracted/PROJECT_SOURCES/SLPE_WP_ALL_IMPORT_CONTRACT_v1.2.0.json'),true,512,JSON_THROW_ON_ERROR);
  $headers=$contract['schema_binding']['ordered_headers']??[];
  if(count($headers)!==95)throw new RuntimeException('DATED_GROUPLESS_SCHEMA_DRIFT');
  $xml=new SimpleXMLElement('<products/>');
  foreach($rows as $r) {
     $row=array_fill_keys($headers,'');
     foreach(['column1'=>'Column1','column2'=>'Column2','column8'=>'Column8','column28'=>'Column28','column27'=>'Column27','price'=>'Price'] as $tag=>$col)$row[$col]=$r[$tag];
     if(count($row)!==95||array_keys($row)!==$headers)throw new RuntimeException('DATED_GROUPLESS_95COL_FAILURE');
     $n=$xml->addChild('product');
     foreach($r as $key=>$value)$n->addChild($key,htmlspecialchars($value,ENT_XML1|ENT_QUOTES,'UTF-8'));
  }
  $file=$dir.'/dated-group-less-'.$variant.'.xml';
  if($xml->asXML($file)===false)throw new RuntimeException('DATED_GROUPLESS_SOURCE_FAILURE');
  $defaults=PMXI_Plugin::get_default_import_options();
  $addon=PMWI_Plugin::get_default_import_options();
  $defaults+=$addon;
  $common=[
      'wizard_type'=>'new','custom_type'=>'product','post_type'=>'product',
      'title'=>'{column3[1]}','unique_key'=>'{column1[1]}',
      'single_product_type'=>'variable','single_product_sku'=>'{column2[1]}',
      'single_product_regular_price'=>'{price[1]}',
      'grouping_indicator'=>'xpath','single_product_id_first_is_parent_id'=>'{column8[1]}',
      'single_product_first_is_parent_id_parent_sku'=>'{column28[1]}',
      'single_product_id_first_is_variation'=>'{column2[1]}',
      'attribute_name'=>['Colour'],'attribute_value'=>['{colour[1]}'],
      'in_variations'=>['1'],'is_taxonomy'=>['0'],'is_visible'=>['1'],
      'update_all_data'=>'no','is_using_new_product_import_options'=>1,
      'is_update_products'=>1,'is_update_price'=>1,'is_update_regular_price'=>1,
  ];
  $mode=$variant==='A'?[
      'is_multiple_product_type'=>'no','first_is_parent'=>'yes',
      'matching_parent'=>'first_is_parent_id','variable_sku'=>'',
      'is_update_sku'=>1,'is_update_title'=>1,
  ]:[
      'is_multiple_product_type'=>'yes','multiple_product_type'=>'variable',
      'first_is_parent'=>'no','matching_parent'=>'first_is_parent_id',
      'variable_sku'=>'{column2[1]}',
      'is_update_sku'=>0,'is_update_title'=>0,
  ];
  $options=array_replace($defaults,array_replace($common,$mode));
  $rel=wp_all_import_get_relative_path($file);
  $imp=new PMXI_Import_Record();
  $imp->set([
    'name'=>'LAB Group-less '.$variant,'friendly_name'=>'LAB Group-less '.$variant,
    'type'=>'upload','feed_type'=>'','path'=>$rel,'root_element'=>'product','xpath'=>'//product',
    'options'=>$options,'count'=>3,'parent_import_id'=>0,'queue_chunk_number'=>0,'triggered'=>0,
    'processing'=>0,'executing'=>0,'imported'=>0,'created'=>0,'updated'=>0,'skipped'=>0,
    'registered_on'=>date('Y-m-d H:i:s'),'last_activity'=>date('Y-m-d H:i:s')
  ])->save();
  $id=(int)$imp->id;if($id<=0)throw new RuntimeException('DATED_GROUPLESS_NATIVE_SAVE_FAILED');
  $hist=new PMXI_File_Record();$hist->set(['import_id'=>$id,'name'=>basename($file),'path'=>$rel,'registered_on'=>date('Y-m-d H:i:s')])->save();
  $saved=$read($id);
  foreach(array_replace($common,$mode) as $key=>$value) {
    if(($saved->options[$key]??null)!==$value)throw new RuntimeException('DATED_GROUPLESS_OPTION_READBACK_FAILED:'.$key);
  }
  file_put_contents($idFile,$id."\n");
  $emit('dated-group-less-'.$variant.'-bootstrap',[
    'classification'=>$variant==='A'?'LAB_RECONSTRUCTED_REPORTED_OPTIONS':'LAB_OWNER_TARGET_CANDIDATE',
    'input_count'=>3,'group_less_variable_input_count'=>1,'schema_columns'=>95,
    'saved_import_id'=>$id,'native_readback'=>true,'source_sha256'=>hash_file('sha256',$file),
    'actual_dated_bundle_row'=>'NOT_AVAILABLE','group_less_row_business_classification'=>'UNRESOLVED',
    'mapping'=>array_replace($common,$mode),
  ]);
  echo "DATED_GROUPLESS_NATIVE_SAVED_".$variant."\n";return;
}
$id=is_file($idFile)?(int)trim(file_get_contents($idFile)):0;
if($id<=0)throw new RuntimeException('DATED_GROUPLESS_ID_MISSING');
$import=$read($id);$result=[];
foreach($rows as $r){
  $pid=(int)wc_get_product_id_by_sku($r['column2']);
  $p=$pid>0?wc_get_product($pid):false;
  $parent=$p&&$p->get_parent_id()?wc_get_product($p->get_parent_id()):false;
  $result[]=[
   'synthetic_role'=>$r['column8']===''?'GROUPLESS_POSSIBLE_ANCHOR':'GROUP_MEMBER',
   'product_id'=>$pid,'type'=>$p?$p->get_type():null,
   'parent_id'=>$p?$p->get_parent_id():0,'parent_sku'=>$parent?$parent->get_sku():null,
   'group_member_expected_parent_sku'=>$r['column28']?:null,
   'price'=>$p?$p->get_regular_price():null,
  ];
}
$emit('dated-group-less-'.$variant.'-post',[
  'classification'=>'SYNTHETIC_NATIVE_GROUPLESS_DIAGNOSTIC_NOT_BUSINESS_ADMISSION',
  'variant'=>$variant,'import_id'=>$id,'readback_count'=>count($result),
  'records'=>$result,'import_counters'=>['created'=>(int)$import->created,'updated'=>(int)$import->updated,'skipped'=>(int)$import->skipped],
  'exact_owner_row_classification'=>'NOT_PROVEN',
  'allowed_business_inference'=>'NONE',
]);
echo "DATED_GROUPLESS_NATIVE_READBACK_".$variant." ".wp_json_encode($result,JSON_UNESCAPED_SLASHES)."\n";
