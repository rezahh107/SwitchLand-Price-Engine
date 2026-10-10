<?php
// LAB-ONLY native variation candidate. Uses exact installed WP All Import model;
// does not synthesize WooCommerce parents or children outside the importer.
$repo=getenv('GITHUB_WORKSPACE');
$out=getenv('LAB_OUT');
$stage=getenv('LAB_STAGE');
if(!$repo||!$out||!in_array($stage,['bootstrap','first','second'],true)) {
    throw new RuntimeException('TARGET_VARIATION_INVALID_STAGE');
}
$dir=wp_upload_dir()['basedir'].'/wpallimport/files';
$file=$dir.'/target-variable-groups.xml';
$idFile=$out.'/target-variable-import-id.txt';
$emit=static function ($name,$value) use($out) {
    if(file_put_contents($out.'/'.$name.'.json',wp_json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n")===false)
        throw new RuntimeException('TARGET_VARIATION_EVIDENCE_WRITE_FAILED');
};
$rows=[
    ['column1'=>'LAB-VAR-UID-A1','column2'=>'LAB-VAR-A1','column8'=>'LAB-GROUP-A','column28'=>'PARENT_LAB-GROUP-A','column3'=>'ترموستات گروه الف','price'=>'200','colour'=>'آبی'],
    ['column1'=>'LAB-VAR-UID-A2','column2'=>'LAB-VAR-A2','column8'=>'LAB-GROUP-A','column28'=>'PARENT_LAB-GROUP-A','column3'=>'ترموستات گروه الف','price'=>'0','colour'=>'قرمز'],
    ['column1'=>'LAB-VAR-UID-B1','column2'=>'LAB-VAR-B1','column8'=>'LAB-GROUP-B','column28'=>'PARENT_LAB-GROUP-B','column3'=>'ترموستات گروه ب','price'=>'300','colour'=>'آبی'],
    ['column1'=>'LAB-VAR-UID-B2','column2'=>'LAB-VAR-B2','column8'=>'LAB-GROUP-B','column28'=>'PARENT_LAB-GROUP-B','column3'=>'ترموستات گروه ب','price'=>'400','colour'=>'قرمز'],
];
$readImport=static function($id) {
    $imp=new PMXI_Import_Record();$imp->getById($id);
    if($imp->isEmpty())throw new RuntimeException('TARGET_VARIATION_IMPORT_READBACK_MISSING');
    return $imp;
};
if($stage==='bootstrap') {
    if(!class_exists('PMWI_Plugin')||!class_exists('PMXI_File_Record')||!function_exists('wc_get_product')) {
        throw new RuntimeException('TARGET_VARIATION_NATIVE_PLUGIN_MISSING');
    }
    if(!is_dir($dir)&&!wp_mkdir_p($dir))throw new RuntimeException('TARGET_VARIATION_UPLOAD_DIR_FAILED');
    $xml=new SimpleXMLElement('<products/>');
    foreach($rows as $row) {
        $n=$xml->addChild('product');
        foreach($row as $key=>$val) $n->addChild($key,htmlspecialchars($val,ENT_XML1|ENT_COMPAT,'UTF-8'));
    }
    if($xml->asXML($file)===false)throw new RuntimeException('TARGET_VARIATION_SOURCE_WRITE_FAILED');
    $options=PMXI_Plugin::get_default_import_options();
    $defaults=PMWI_Plugin::get_default_import_options();
    $options+=$defaults;
    $mapping=[
      'wizard_type'=>'new','custom_type'=>'product','post_type'=>'product',
      'title'=>'{column3[1]}','unique_key'=>'{column1[1]}',
      'single_product_type'=>'variable',
      // Add-On default multiple_product_type=simple overrides an unmapped type.
      // Source-backed fixed product-type selector.
      'is_multiple_product_type'=>'yes',
      'multiple_product_type'=>'variable',
      'single_product_sku'=>'{column2[1]}',
      'single_product_regular_price'=>'{price[1]}',
      'single_product_sale_price'=>'',
      'first_is_parent'=>'no',
      'matching_parent'=>'first_is_parent_id',
      'grouping_indicator'=>'xpath',
      'single_product_id_first_is_parent_id'=>'{column8[1]}',
      'single_product_first_is_parent_id_parent_sku'=>'{column28[1]}',
      'single_product_id_first_is_variation'=>'{column2[1]}',
      'variable_sku'=>'{column2[1]}',
      'create_new_product_if_no_parent'=>0,
      'attribute_name'=>['رنگ'],
      'attribute_value'=>['{colour[1]}'],
      'in_variations'=>['1'],
      'is_taxonomy'=>['0'],
      'is_visible'=>['1'],
      'update_all_data'=>'no',
      'is_update_title'=>0,'is_update_custom_fields'=>0,'is_update_content'=>0,
      'is_update_categories'=>0,'is_update_images'=>0,
      'is_update_products'=>1,'is_update_sku'=>0,'is_update_price'=>1,
      'is_update_regular_price'=>1,'is_update_sale_price'=>1,
      'is_update_catalog_visibility'=>1,'is_using_new_product_import_options'=>1,
    ];
    foreach(['first_is_parent','matching_parent','grouping_indicator',
       'single_product_id_first_is_parent_id','single_product_first_is_parent_id_parent_sku',
       'single_product_id_first_is_variation','variable_sku'] as $key) {
        if(!array_key_exists($key,$defaults)) {
            throw new RuntimeException('TARGET_VARIATION_NATIVE_SOURCE_KEY_MISSING:'.$key);
        }
    }
    if(($defaults['multiple_product_type']??null)!=='simple'
       ||($defaults['is_multiple_product_type']??null)!=='yes') {
        throw new RuntimeException('TARGET_VARIATION_NATIVE_PRODUCT_TYPE_VERSION_DRIFT');
    }
    $options=array_replace($options,$mapping);
    $rel=wp_all_import_get_relative_path($file);
    $imp=new PMXI_Import_Record();
    $imp->set(['name'=>'LAB Source-Backed Target Variation Candidate','friendly_name'=>'LAB Source-Backed Target Variation Candidate',
      'type'=>'upload','feed_type'=>'','path'=>$rel,'root_element'=>'product','xpath'=>'//product',
      'options'=>$options,'count'=>4,'parent_import_id'=>0,'queue_chunk_number'=>0,
      'triggered'=>0,'processing'=>0,'executing'=>0,'imported'=>0,'created'=>0,'updated'=>0,
      'skipped'=>0,'registered_on'=>date('Y-m-d H:i:s'),'last_activity'=>date('Y-m-d H:i:s')])->save();
    $id=(int)$imp->id;
    if($id<=0)throw new RuntimeException('TARGET_VARIATION_IMPORT_SAVE_FAILED');
    $history=new PMXI_File_Record();
    $history->set(['import_id'=>$id,'name'=>basename($file),'path'=>$rel,'registered_on'=>date('Y-m-d H:i:s')])->save();
    $saved=$readImport($id);
    foreach($mapping as $key=>$value) {
        if(!array_key_exists($key,$saved->options)||$saved->options[$key]!==$value) {
            throw new RuntimeException('TARGET_VARIATION_NATIVE_SAVED_MAPPING_DRIFT:'.$key);
        }
    }
    file_put_contents($idFile,$id."\n");
    $emit('target-variation-bootstrap',[
      'classification'=>'LAB_SYNTHETIC_VARIATION_CANDIDATE',
      'import_id'=>$id,'saved_readback'=>true,'mapping'=>$mapping,
      'source_sha256'=>hash_file('sha256',$file),
      'effective_saved_options_sha256'=>hash('sha256',serialize($saved->options)),
      'addon_version'=>'4.0.6',
    ]);
    echo "TARGET_VARIATION_NATIVE_SAVED_ID=$id\n";
    return;
}
$id=is_file($idFile)?(int)trim(file_get_contents($idFile)):0;
if($id<=0)throw new RuntimeException('TARGET_VARIATION_ID_MISSING');
$import=$readImport($id);
$records=[];
foreach($rows as $row) {
    $sku=$row['column2'];
    $productId=(int)wc_get_product_id_by_sku($sku);
    $p=$productId>0?wc_get_product($productId):false;
    $parent=$p&&$p->get_parent_id()>0?wc_get_product($p->get_parent_id()):false;
    $records[]=[
      'sku'=>$sku,'uid'=>$row['column1'],
      'group_id'=>$row['column8'],
      'expected_parent_sku'=>$row['column28'],
      'product_id'=>$productId,
      'type'=>$p?$p->get_type():null,
      'parent_id'=>$p?$p->get_parent_id():null,
      'actual_parent_sku'=>$parent?$parent->get_sku():null,
      'parent_type'=>$parent?$parent->get_type():null,
      'regular_price'=>$p?$p->get_regular_price():null,
      'status'=>$p?$p->get_status():null,
    ];
}
$groupParents=[];
foreach(['PARENT_LAB-GROUP-A','PARENT_LAB-GROUP-B'] as $parentSku) {
    $id=(int)wc_get_product_id_by_sku($parentSku);
    $p=$id>0?wc_get_product($id):false;
    $groupParents[]=['sku'=>$parentSku,'product_id'=>$id,'type'=>$p?$p->get_type():null];
}
$allProducts=get_posts(['post_type'=>'product','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids']);
$allParents=array_values(array_filter($allProducts,static function($candidateId) {
    $candidate=wc_get_product((int)$candidateId);
    return $candidate && $candidate->is_type('variable');
}));
$variations=get_posts(['post_type'=>'product_variation','posts_per_page'=>-1,'fields'=>'ids','post_status'=>'any']);
$state=[
  'classification'=>'AUTHENTIC_WOOCOMMERCE_READBACK_VARIATION_CANDIDATE',
  'import_id'=>(int)$import->id,
  'run_phase'=>$stage,'records'=>$records,'group_parents'=>$groupParents,
  'product_parent_count'=>count($allParents),'variation_count'=>count($variations),
  'native_import_counters'=>['created'=>(int)$import->created,'updated'=>(int)$import->updated,'skipped'=>(int)$import->skipped],
];
$emit('target-variation-'.$stage,$state);
echo "TARGET_VARIATION_NATIVE_READBACK_".strtoupper($stage)." ",wp_json_encode([
    'records'=>$records,'group_parents'=>$groupParents,'variation_count'=>count($variations),
    'product_parent_count'=>count($allParents),
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),"\n";
