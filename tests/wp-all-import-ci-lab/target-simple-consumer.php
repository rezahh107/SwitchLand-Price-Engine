<?php
// Isolated native LAB candidate: Column1/Column2/Price/Column10/visibility.
// This is NOT a fresh target-export read-back, not Production and not C1/C2 proof.
$repo = getenv('GITHUB_WORKSPACE');
$out = getenv('LAB_OUT');
$stage = getenv('LAB_STAGE');
if (!$repo || !$out || !in_array($stage, ['bootstrap','pre','mid','post'], true)) {
    throw new RuntimeException('TARGET_NATIVE_INVALID_STAGE');
}
$folder = wp_upload_dir()['basedir'] . '/wpallimport/files';
$originalSku = 'LAB-TARGET-SIMPLE';
$uid = 'LAB-TARGET-COLUMN1';
$sentinel = '_wpai_target_unrelated_sentinel';
$emit = static function ($name,$data) use($out) {
    if(file_put_contents($out.'/'.$name.'.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n")===false)
      throw new RuntimeException('TARGET_NATIVE_EVIDENCE_WRITE_FAILED');
};
$input = [
    'initial'=>['price'=>'12.00','sale'=>'10.00','visibility'=>'visible','sku'=>$originalSku],
    'updated'=>['price'=>'19.50','sale'=>'17.00','visibility'=>'visible','sku'=>$originalSku.'-SOURCE-CHANGED'],
    'zero'=>['price'=>'0','sale'=>'','visibility'=>'hidden','sku'=>$originalSku.'-SOURCE-CHANGED'],
];
$sourceFile = static function ($state) use($folder,$uid,$input) {
    $entry=$input[$state];
    $file=$folder.'/target-simple-'.$state.'.xml';
    $xml=new SimpleXMLElement('<products/>');
    $node=$xml->addChild('product');
    foreach ([
        'column1'=>$uid,'column2'=>$entry['sku'],'column3'=>'ترموستات آزمایشگاهی',
        'column27'=>'simple','price'=>$entry['price'],'column10'=>$entry['sale'],
        'visibility'=>$entry['visibility'],
    ] as $key=>$value) {
        $node->addChild($key,htmlspecialchars($value,ENT_XML1|ENT_COMPAT,'UTF-8'));
    }
    if($xml->asXML($file)===false)throw new RuntimeException('TARGET_NATIVE_XML_WRITE_FAILED');
    return $file;
};
$savedIdFile=$out.'/target-simple-import-id.txt';
$readImport = static function ($id) {
    $r=new PMXI_Import_Record();$r->getById($id);
    if($r->isEmpty())throw new RuntimeException('TARGET_NATIVE_SAVED_IMPORT_MISSING');
    return $r;
};
$repoint = static function ($id,$state) use($sourceFile,$emit,$readImport) {
    $file=$sourceFile($state);
    $next=wp_all_import_get_relative_path($file);
    $imp=$readImport($id);
    $imp->set(['path'=>$next,'queue_chunk_number'=>0,'processing'=>0])->update();
    $history=new PMXI_File_Record();
    $history->getBy(['import_id'=>$id],'id DESC');
    if($history->isEmpty())throw new RuntimeException('TARGET_NATIVE_FILE_HISTORY_MISSING');
    $history->set(['path'=>$next])->update();
    $got=$readImport($id);
    if($got->path!==$next)throw new RuntimeException('TARGET_NATIVE_SOURCE_SWITCH_READBACK_FAILED');
    $emit('target-source-'.$state,['import_id'=>$id,'path_readback'=>true,'source_sha256'=>hash_file('sha256',$file),
      'source_state'=>$state]);
};
if($stage==='bootstrap') {
    if(!class_exists('PMXI_Import_Record')||!class_exists('PMWI_Plugin')||!function_exists('wc_get_product'))
       throw new RuntimeException('TARGET_NATIVE_PLUGIN_CLASSES_NOT_AVAILABLE');
    if(!is_dir($folder) && !wp_mkdir_p($folder))throw new RuntimeException('TARGET_NATIVE_UPLOAD_NOT_AVAILABLE');
    $file=$sourceFile('initial');
    $options=PMXI_Plugin::get_default_import_options();
    $options+=PMWI_Plugin::get_default_import_options();
    $mapping=[
        'wizard_type'=>'new','custom_type'=>'product','post_type'=>'product',
        'title'=>'{column3[1]}',
        'unique_key'=>'{column1[1]}',
        'single_product_type'=>'simple',
        'single_product_sku'=>'{column2[1]}',
        'single_product_regular_price'=>'{price[1]}',
        'single_product_sale_price'=>'{column10[1]}',
        'single_product_visibility'=>'{visibility[1]}',
        // Installed Add-On 4.0.6 default is_product_visibility=visible;
        // product_visibility_xpath exists in installed source; both are needed.
        // Qualify the candidate's XPath-mode binding with real Woo read-back.
        'is_product_visibility'=>'xpath',
        'product_visibility_xpath'=>'{visibility[1]}',
        'update_all_data'=>'no',
        'is_update_title'=>0,'is_update_content'=>0,'is_update_categories'=>0,
        'is_update_images'=>0,'is_update_custom_fields'=>0,'is_update_attributes'=>0,
        'is_update_products'=>1,'is_update_price'=>1,
        'is_update_regular_price'=>1,'is_update_sale_price'=>1,
        'is_update_catalog_visibility'=>1,'is_update_sku'=>0,
        'is_update_product_type'=>0,
        'is_using_new_product_import_options'=>1,
    ];
    $options=array_replace($options,$mapping);
    $rel=wp_all_import_get_relative_path($file);
    $imp=new PMXI_Import_Record();
    $imp->set(['name'=>'LAB Target Simple Candidate','friendly_name'=>'LAB Target Simple Candidate',
        'type'=>'upload','feed_type'=>'','path'=>$rel,'root_element'=>'product','xpath'=>'//product',
        'options'=>$options,'count'=>1,'parent_import_id'=>0,'queue_chunk_number'=>0,
        'triggered'=>0,'processing'=>0,'executing'=>0,'imported'=>0,'created'=>0,
        'updated'=>0,'skipped'=>0,'registered_on'=>date('Y-m-d H:i:s'),
        'last_activity'=>date('Y-m-d H:i:s')])->save();
    $id=(int)$imp->id;
    if($id<=0)throw new RuntimeException('TARGET_NATIVE_IMPORT_SAVE_FAILED');
    $history=new PMXI_File_Record();
    $history->set(['import_id'=>$id,'name'=>basename($file),'path'=>$rel,
        'registered_on'=>date('Y-m-d H:i:s')])->save();
    $got=$readImport($id);
    $nativeDefaults=PMWI_Plugin::get_default_import_options();
    if(($nativeDefaults['is_product_visibility'] ?? null) !== 'visible') {
        throw new RuntimeException('TARGET_NATIVE_VISIBILITY_MODE_VERSION_DRIFT');
    }
    foreach($mapping as $key=>$val) {
        if(!array_key_exists($key,$got->options)||$got->options[$key]!==$val) {
            throw new RuntimeException('TARGET_NATIVE_OPTION_READBACK_MISMATCH:'.$key);
        }
    }
    $addon=WP_PLUGIN_DIR.'/wpai-woocommerce-add-on/src/XmlImportWooCommerceService.php';
    $src=is_file($addon)?file_get_contents($addon):false;
    foreach(['is_update_regular_price','is_update_sale_price','is_update_catalog_visibility','is_update_sku'] as $key) {
        if(!is_string($src)||strpos($src,$key)===false)
            throw new RuntimeException('TARGET_NATIVE_ADDON_SOURCE_GATE_MISSING:'.$key);
    }
    file_put_contents($savedIdFile,$id."\n");
    $emit('target-simple-bootstrap',[
        'classification'=>'LAB_SYNTHETIC_CONFIGURATION',
        'import_id'=>$id,'readback'=>true,'mapping'=>$mapping,
        'native_option_sha256'=>hash('sha256',serialize($got->options)),
        'addon_source_sha256'=>hash_file('sha256',$addon),
    ]);
    echo "WPAI_TARGET_SIMPLE_NATIVE_CONFIG_SAVED_ID=$id\n";return;
}
$id=is_file($savedIdFile)?(int)trim(file_get_contents($savedIdFile)):0;
if($id<=0)throw new RuntimeException('TARGET_NATIVE_IMPORT_ID_MISSING');
$imp=$readImport($id);
$productId=(int)wc_get_product_id_by_sku($originalSku);
$p=$productId>0?wc_get_product($productId):false;
if(!$p||!$p->is_type('simple'))throw new RuntimeException('TARGET_NATIVE_SIMPLE_NOT_FOUND_OR_SKU_MUTATED');
$money=static fn($n) => $n===''?'':(is_numeric($n)?number_format((float)$n,2,'.',''):'NONNUMERIC');
$state=[
    'product_id'=>$productId,'import_id'=>$id,'sku'=>$p->get_sku(),'type'=>$p->get_type(),
    'regular_price'=>$money($p->get_regular_price()),
    'sale_price'=>$money($p->get_sale_price()),
    'catalog_visibility'=>$p->get_catalog_visibility(),
    'sentinel'=>get_post_meta($productId,$sentinel,true),
    'source'=>'WooCommerce CRUD read-back',
    'import_counters'=>['created'=>(int)$imp->created,'updated'=>(int)$imp->updated,'skipped'=>(int)$imp->skipped],
];
if($stage==='pre') {
    update_post_meta($productId,$sentinel,'DO_NOT_CHANGE');
    $state['sentinel']=get_post_meta($productId,$sentinel,true);
    $emit('target-simple-pre',$state);
    $repoint($id,'updated');
    echo "WPAI_TARGET_SIMPLE_PRE_READBACK\n";return;
}
$before=json_decode((string)file_get_contents($out.'/target-simple-pre.json'),true,512,JSON_THROW_ON_ERROR);
if($state['product_id']!==$before['product_id']||$state['sku']!==$originalSku||$state['sentinel']!=='DO_NOT_CHANGE')
    throw new RuntimeException('TARGET_NATIVE_PROTECTED_IDENTITY_OR_META_CHANGED');
if($stage==='mid') {
    $emit('target-simple-mid',$state);
    $repoint($id,'zero');
    echo "WPAI_TARGET_SIMPLE_MID_READBACK\n";return;
}
$emit('target-simple-post',$state);
echo "WPAI_TARGET_SIMPLE_FINAL_READBACK\n";
