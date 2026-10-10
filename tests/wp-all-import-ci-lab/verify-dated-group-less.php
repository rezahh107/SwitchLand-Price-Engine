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
$verify=static function($value):bool {
   foreach(['A','B'] as $v) {
      $b=$value[$v]['bootstrap']??[];$p=$value[$v]['post']??[];
      if(($b['input_count']??0)!==3||($b['schema_columns']??0)!==95
        ||($b['group_less_variable_input_count']??0)!==1
        ||($b['native_readback']??false)!==true
        ||($b['group_less_row_business_classification']??null)!=='UNRESOLVED'
        ||($p['classification']??null)!=='SYNTHETIC_NATIVE_GROUPLESS_DIAGNOSTIC_NOT_BUSINESS_ADMISSION'
        ||($p['exact_owner_row_classification']??null)!=='NOT_PROVEN'
        ||($p['allowed_business_inference']??null)!=='NONE'
        ||($p['import_id']??0)!==$b['saved_import_id']
        ||($p['readback_count']??0)!==3
        ||count($p['records']??[])!==3)return false;
      if(($p['records'][0]['synthetic_role']??null)!=='GROUPLESS_POSSIBLE_ANCHOR')return false;
      for($i=1;$i<3;$i++)
        if(($p['records'][$i]['synthetic_role']??null)!=='GROUP_MEMBER')return false;
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
];
foreach($negative as $name=>$mutate){$copy=$d;$mutate($copy);if($verify($copy))throw new RuntimeException('DATED_GROUPLESS_FALSE_PASS:'.$name);}
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
echo "DATED_GROUPLESS_NATIVE_DIAGNOSTIC_CAPTURED_BUSINESS_NOT_PROVEN\n";
