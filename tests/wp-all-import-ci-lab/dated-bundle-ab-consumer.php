<?php
// CI-only pairwise probe: reported dated options versus Owner target candidate.
// No dated Bundle bytes; A is RECONSTRUCTED, never verified as an exported config.
$repo = getenv('GITHUB_WORKSPACE');
$out = getenv('LAB_OUT');
$stage = getenv('LAB_STAGE');
$variant = getenv('LAB_VARIANT');
if (!$repo || !$out || !in_array($stage, ['bootstrap','pre','post'], true)
    || !in_array($variant, ['A','B'], true)) {
    throw new RuntimeException('DATED_AB_INVALID_ENVIRONMENT');
}
$prefix = "LAB-DATED-".$variant;
$folder = wp_upload_dir()['basedir'].'/wpallimport/files';
$sourceDir = $repo.'/references/current_package/v3.14.2/extracted/PROJECT_SOURCES/';
$schema = json_decode(file_get_contents($sourceDir.'SLPE_WP_ALL_IMPORT_CONTRACT_v1.2.0.json'),true,512,JSON_THROW_ON_ERROR);
$headers = $schema['schema_binding']['ordered_headers'] ?? [];
if (count($headers)!==95 || count(array_unique($headers))!==95) {
    throw new RuntimeException('DATED_AB_ACTIVE_SCHEMA_DRIFT');
}
$emit = static function($name,$content)use($out) {
    if(file_put_contents($out.'/'.$name.'.json',wp_json_encode($content,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n")===false) {
        throw new RuntimeException('DATED_AB_EVIDENCE_WRITE_ERROR');
    }
};
$source = static function($phase)use($folder,$headers,$prefix) {
    $row = array_fill_keys($headers,'');
    $row['Column1'] = $prefix.'-ROW-1';
    $row['Column2'] = $prefix.($phase==='initial' ? '-SKU' : '-SOURCE-CHANGED-SKU');
    $row['Column8'] = '';
    $row['Column27'] = 'simple';
    $row['Column28'] = '';
    $row['Price'] = $phase==='initial'?'12.00':'0';
    $row['Column10'] = $phase==='initial'?'10.00':'';
    $row['visibility'] = $phase==='initial'?'visible':'hidden';
    $row['Column4'] = "توضیح فارسی؛\nمتن آزمایشی";
    if(count($row)!==95 || array_keys($row)!==$headers) {
        throw new RuntimeException('DATED_AB_95_COLUMN_FIXTURE_INVALID');
    }
    $file=$folder.'/dated-'.$prefix.'-'.$phase.'.xml';
    // Source uses 95-column schema, projected to real XML bindings used by the native Lab.
    $xml = new SimpleXMLElement('<products/>');
    $node=$xml->addChild('product');
    $material=['column1'=>$row['Column1'],'column2'=>$row['Column2'],
        'column3'=>$phase==='initial'?'Synthetic Dated Product':'SOURCE_OVERRIDDEN_TITLE',
        'column8'=>$row['Column8'],'column27'=>$row['Column27'],'column28'=>$row['Column28'],
        'price'=>$row['Price'],'column10'=>$row['Column10'],'visibility'=>$row['visibility'],
        'custom_value'=>$phase==='initial'?'SOURCE_INITIAL':'SOURCE_MODIFIED',
        'acf_value'=>$phase==='initial'?'SOURCE_INITIAL':'SOURCE_MODIFIED'];
    foreach($material as $key=>$value) {
        $node->addChild($key,htmlspecialchars($value,ENT_XML1|ENT_QUOTES,'UTF-8'));
    }
    if($xml->asXML($file)===false)throw new RuntimeException('DATED_AB_SOURCE_WRITE_ERROR');
    return [$file,$row];
};
$readImport = static function($id) {
    $import=new PMXI_Import_Record();$import->getById($id);
    if($import->isEmpty())throw new RuntimeException('DATED_AB_SAVED_IMPORT_MISSING');
    return $import;
};
$idFile = $out.'/dated-'.$variant.'-id.txt';
$id = is_file($idFile)?(int)trim(file_get_contents($idFile)):0;
if($stage==='bootstrap') {
    if(!is_dir($folder) && !wp_mkdir_p($folder))throw new RuntimeException('DATED_AB_UPLOAD_DIR_MISSING');
    if(!class_exists('PMXI_Import_Record')||!class_exists('PMWI_Plugin')||!class_exists('PMXI_File_Record')) {
        throw new RuntimeException('DATED_AB_PINNED_PLUGINS_MISSING');
    }
    [$file,$row]=$source('initial');
    $defaults = PMXI_Plugin::get_default_import_options();
    $defaults += PMWI_Plugin::get_default_import_options();
    $common=[
      'wizard_type'=>'new','custom_type'=>'product','post_type'=>'product',
      'title'=>'{column3[1]}','unique_key'=>'{column1[1]}',
      'is_multiple_product_type'=>'yes','multiple_product_type'=>'simple',
      'single_product_type'=>'simple',
      'single_product_sku'=>'{column2[1]}',
      'single_product_regular_price'=>'{price[1]}',
      'single_product_sale_price'=>'{column10[1]}',
      'single_product_visibility'=>'{visibility[1]}',
      'update_all_data'=>'no',
      'is_update_products'=>1,
      'is_using_new_product_import_options'=>1,
      'is_update_price'=>1,'is_update_regular_price'=>1,'is_update_sale_price'=>1,
      'is_update_catalog_visibility'=>1,
      'is_update_categories'=>0,'is_update_images'=>0,'is_update_attributes'=>0,
      'is_update_content'=>0,'is_update_product_type'=>0
    ];
    $mode = $variant==='A' ? [
      // Explicitly Owner-REPORTED material options, NOT independent ZIP read-back.
      'is_product_visibility'=>'xpath',
      'is_multiple_product_type'=>'no',
      'is_update_sku'=>1,'is_update_title'=>1,
      'is_update_custom_fields'=>1,'is_update_acf'=>1,
      'acf_update_logic'=>'full_update',
      // Missing product_visibility_xpath intentionally left at native defaults.
    ] : [
      'is_product_visibility'=>'xpath',
      'product_visibility_xpath'=>'{visibility[1]}',
      'is_update_sku'=>0,'is_update_title'=>0,
      'is_update_custom_fields'=>0,'is_update_acf'=>0,
    ];
    $mapping = array_replace($common,$mode);
    $options = array_replace($defaults,$mapping);
    $rel = wp_all_import_get_relative_path($file);
    $import = new PMXI_Import_Record();
    $import->set([
      'name'=>'LAB Dated '.$variant,'friendly_name'=>'LAB Dated '.$variant,
      'type'=>'upload','feed_type'=>'','path'=>$rel,
      'root_element'=>'product','xpath'=>'//product','options'=>$options,
      'count'=>1,'parent_import_id'=>0,'queue_chunk_number'=>0,'triggered'=>0,
      'processing'=>0,'executing'=>0,'imported'=>0,'created'=>0,'updated'=>0,'skipped'=>0,
      'registered_on'=>date('Y-m-d H:i:s'),'last_activity'=>date('Y-m-d H:i:s'),
    ])->save();
    $id=(int)$import->id;
    if($id<=0)throw new RuntimeException('DATED_AB_SAVE_FAILED');
    $hist=new PMXI_File_Record();
    $hist->set(['import_id'=>$id,'name'=>basename($file),'path'=>$rel,'registered_on'=>date('Y-m-d H:i:s')])->save();
    $got=$readImport($id);
    foreach($mapping as $name=>$value) {
        if(!array_key_exists($name,$got->options)||$got->options[$name]!==$value) {
            throw new RuntimeException('DATED_AB_SAVED_OPTION_DRIFT:'.$name);
        }
    }
    if($variant==='A' && !empty($got->options['product_visibility_xpath'])) {
        throw new RuntimeException('DATED_A_UNINTENDED_TARGET_VISIBILITY_MAPPING');
    }
    file_put_contents($idFile,$id."\n");
    $emit('dated-'.$variant.'-bootstrap',[
       'classification'=>$variant==='A'?'LAB_RECONSTRUCTED_FROM_OWNER_REPORTED_OPTIONS':'LAB_OWNER_APPROVED_TARGET_CANDIDATE',
       'import_id'=>$id,'native_save_readback'=>true,
       'tested_mapping'=>$mapping,'schema_headers'=>count($headers),
       'schema_source_row_identity'=>'SYNTHETIC_95COL_PROJECTED_TO_XML',
       'source_sha256'=>hash_file('sha256',$file),
       'effective_options_sha256'=>hash('sha256',serialize($got->options)),
       'actual_export_verified'=>false,
       'production_equivalence'=>'NOT_PROVEN'
    ]);
    echo "DATED_AB_NATIVE_SAVED_".$variant."_ID=".$id."\n";return;
}
if($id<=0)throw new RuntimeException('DATED_AB_SAVED_ID_NOT_FOUND');
$import=$readImport($id);
$preFile=$out.'/dated-'.$variant.'-pre.json';
if($stage==='pre') {
    $productId=(int)wc_get_product_id_by_sku($prefix.'-SKU');
    if($productId<=0)throw new RuntimeException('DATED_AB_BASELINE_PRODUCT_NOT_CREATED');
    $p=wc_get_product($productId);
    if(!$p||!$p->is_type('simple'))throw new RuntimeException('DATED_AB_BASELINE_PRODUCT_TYPE_INCORRECT');
    update_post_meta($productId,'_dated_lab_custom_sentinel','OWNER_CUSTOM_KEEP');
    update_post_meta($productId,'_dated_lab_acf_like_sentinel','OWNER_ACF_LIKE_KEEP');
    $changed=wp_update_post(['ID'=>$productId,'post_title'=>'OWNER_INDEPENDENT_TITLE'],true);
    if(is_wp_error($changed)||(int)$changed!==$productId)throw new RuntimeException('DATED_AB_TITLE_SEED_FAILED');
    [$next,$row]=$source('updated');
    $relative=wp_all_import_get_relative_path($next);
    $import->set(['path'=>$relative,'queue_chunk_number'=>0,'processing'=>0])->update();
    $hist=new PMXI_File_Record();$hist->getBy(['import_id'=>$id],'id DESC');
    if($hist->isEmpty())throw new RuntimeException('DATED_AB_HISTORY_NOT_FOUND');
    $hist->set(['path'=>$relative])->update();
    if($readImport($id)->path!==$relative)throw new RuntimeException('DATED_AB_UPDATED_PATH_READBACK_FAILURE');
    $emit('dated-'.$variant.'-pre',[
       'product_id'=>$productId,'uid'=>$prefix.'-ROW-1',
       'sku'=>$p->get_sku(),'regular_price'=>$p->get_regular_price(),
       'sale_price'=>$p->get_sale_price(),
       'catalog_visibility'=>$p->get_catalog_visibility(),
       'title'=>get_the_title($productId),
       'custom_sentinel'=>get_post_meta($productId,'_dated_lab_custom_sentinel',true),
       'acf_like_sentinel'=>get_post_meta($productId,'_dated_lab_acf_like_sentinel',true),
       'next_source_sha256'=>hash_file('sha256',$next),
       'next_source_readback'=>true,
    ]);
    echo "DATED_AB_NATIVE_PRE_".$variant."\n";return;
}
if(!is_file($preFile))throw new RuntimeException('DATED_AB_PRE_STATE_MISSING');
$pre=json_decode(file_get_contents($preFile),true,512,JSON_THROW_ON_ERROR);
$productId=(int)$pre['product_id'];
$p=wc_get_product($productId);
if(!$p)throw new RuntimeException('DATED_AB_POST_PRODUCT_MISSING');
$got=$readImport($id);
$state=[
   'classification'=>'NATIVE_WOO_CRUD_OBSERVATION',
   'variant'=>$variant,'product_id'=>$productId,'import_id'=>$id,
   'sku'=>$p->get_sku(),'type'=>$p->get_type(),
   'title'=>$p->get_name(),
   'regular_price'=>$p->get_regular_price(),
   'sale_price'=>$p->get_sale_price(),
   'catalog_visibility'=>$p->get_catalog_visibility(),
   'custom_sentinel'=>get_post_meta($productId,'_dated_lab_custom_sentinel',true),
   'acf_like_sentinel'=>get_post_meta($productId,'_dated_lab_acf_like_sentinel',true),
   'native_counters'=>['created'=>(int)$got->created,'updated'=>(int)$got->updated,'skipped'=>(int)$got->skipped],
];
$emit('dated-'.$variant.'-post',$state);
echo "DATED_AB_NATIVE_POST_".$variant." ".wp_json_encode($state,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";
