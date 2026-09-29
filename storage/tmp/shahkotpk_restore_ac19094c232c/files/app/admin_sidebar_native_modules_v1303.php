<?php
declare(strict_types=1);
/** v13.0.3.4: collect only valid physical manifest modules; native sidebar remains authoritative. */
if(!function_exists('sk1303_native_module_items')){
function sk1303_native_module_items(): array {
    $out=[];$dir=__DIR__.'/sidebar-modules';if(!is_dir($dir))return $out;
    foreach(glob($dir.'/*.json')?:[] as $file){$raw=@file_get_contents($file);$j=is_string($raw)?json_decode($raw,true):null;$rows=(is_array($j)&&isset($j['items'])&&is_array($j['items']))?$j['items']:(is_array($j)?[$j]:[]);
        foreach($rows as $r){if(!is_array($r))continue;$url=trim((string)($r['url']??''));$label=trim((string)($r['label']??''));$placement=strtolower((string)($r['placement']??'sidebar'));if($url===''||$label===''||!str_starts_with($url,'/admin/')||$placement!=='sidebar'||empty($r['enabled'])&&array_key_exists('enabled',$r))continue;
            if(function_exists('sk1301_canonical_url'))$url=sk1301_canonical_url($url);$r['url']=$url;
            if(function_exists('sk1300_admin_nav_allowed')&&!sk1300_admin_nav_allowed($r))continue;
            $path=(string)(parse_url($url,PHP_URL_PATH)?:$url);if(str_ends_with(strtolower($path),'.php')){$target=dirname(__DIR__).$path;if(!is_file($target))continue;}$out[$path]=['label'=>$label,'url'=>$url,'icon'=>(string)($r['icon']??'•'),'category_key'=>(string)($r['category_key']??'extensions'),'category_label'=>(string)($r['category_label']??'EXTENSIONS'),'category_order'=>(int)($r['category_order']??80),'sort_order'=>(int)($r['sort_order']??100)];
        }
    }
    uasort($out,static fn($a,$b)=>[$a['category_order'],$a['category_label'],$a['sort_order'],$a['label']]<=>[$b['category_order'],$b['category_label'],$b['sort_order'],$b['label']]);return array_values($out);
}
}
