<?php
declare(strict_types=1);
/** ShahkotPK v13.0.5.1 — Step 4 performance hotfix + route-only integrity audit. */
if (!function_exists('sk1304_linkage_scan')) {
function sk1304_root(): string { return dirname(__DIR__); }
function sk1304_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function sk1304_path(string $url): string {
    $p=(string)(parse_url($url,PHP_URL_PATH)?:$url);
    return $p===''?'/':('/'.ltrim($p,'/'));
}
function sk1304_internal(string $url): bool {
    $u=trim($url); if($u==='')return false;
    if(str_starts_with($u,'#')||str_starts_with(strtolower($u),'javascript:'))return false;
    $parts=parse_url($u); return !isset($parts['scheme']) && !str_starts_with($u,'//');
}
function sk1304_route_status(string $url, string $area='public'): array {
    $path=sk1304_path($url); $root=sk1304_root();
    if(!sk1304_internal($url))return ['status'=>'external','path'=>$path,'file'=>'','exists'=>true,'message'=>'External/non-file route'];
    if($path==='/'||$path==='/index.php'){$file=$root.'/index.php';$ok=is_file($file);return ['status'=>$ok?'ok':'missing','path'=>$path,'file'=>$file,'exists'=>$ok,'message'=>$ok?'Physical entry exists':'index.php is missing'];}
    if(!str_ends_with(strtolower($path),'.php'))return ['status'=>'virtual','path'=>$path,'file'=>'','exists'=>true,'message'=>'Rewrite/virtual route'];
    $file=$root.$path; $exists=is_file($file);
    return ['status'=>$exists?'ok':'missing','path'=>$path,'file'=>$file,'exists'=>$exists,'message'=>$exists?'Physical PHP route exists':'Registered PHP route has no matching file'];
}
function sk1304_public_items(): array {
    $rows=[];
    try{
        if(is_file(__DIR__.'/public_nav_v1100.php'))require_once __DIR__.'/public_nav_v1100.php';
        if(function_exists('spn1100_items'))$rows=spn1100_items();
    }catch(Throwable $e){}
    if(!$rows && function_exists('sk1301_manifest_rows'))foreach(sk1301_manifest_rows(__DIR__.'/public-nav-modules') as $r)if(!isset($r['_error']))$rows[]=$r;
    return is_array($rows)?$rows:[];
}
function sk1304_admin_items(): array {
    $rows=[];
    try{
        if(is_file(__DIR__.'/admin_sidebar_registry_v1022.php'))require_once __DIR__.'/admin_sidebar_registry_v1022.php';
        if(function_exists('sk1022_nav_all_items'))$rows=sk1022_nav_all_items(true);
    }catch(Throwable $e){}
    if(!$rows && function_exists('sk1301_manifest_rows'))foreach(sk1301_manifest_rows(__DIR__.'/sidebar-modules') as $r)if(!isset($r['_error']))$rows[]=$r;
    return is_array($rows)?$rows:[];
}
function sk1304_scan_rows(array $rows,string $area): array {
    $out=[];$seen=[];$dup=0;$missing=0;$ok=0;$virtual=0;$external=0;
    foreach($rows as $r){if(!is_array($r))continue;$url=trim((string)($r['url']??''));if($url==='')continue;
        if(function_exists('sk1301_canonical_url'))$url=sk1301_canonical_url($url);
        $key=(string)($r['nav_key']??$r['module_key']??'');$label=trim((string)($r['label']??$r['label_override']??$key?:$url));$path=sk1304_path($url);
        if(isset($seen[$path]))$dup++;else $seen[$path]=true;
        $st=sk1304_route_status($url,$area);if($st['status']==='missing')$missing++;elseif($st['status']==='ok')$ok++;elseif($st['status']==='virtual')$virtual++;else $external++;
        $out[]=['area'=>$area,'key'=>$key,'label'=>$label,'url'=>$url,'path'=>$path,'status'=>$st['status'],'message'=>$st['message']];
    }
    usort($out,static fn($a,$b)=>[$a['status']==='missing'?0:1,$a['label'],$a['path']]<=>[$b['status']==='missing'?0:1,$b['label'],$b['path']]);
    return ['rows'=>$out,'total'=>count($out),'ok'=>$ok,'missing'=>$missing,'duplicates'=>$dup,'virtual'=>$virtual,'external'=>$external];
}
function sk1304_shared_state(): array {
    $identity=function_exists('sk1301_identity')?sk1301_identity():['name'=>'ShahkotPK','logo'=>''];
    $theme=function_exists('sk1301_theme')?sk1301_theme():[];
    return ['identity'=>$identity,'theme'=>$theme,'logo_ok'=>!empty($identity['logo']),'name_ok'=>trim((string)($identity['name']??''))!==''];
}
function sk1304_cache_file(): string {
    $tid=0;try{if(function_exists('tenant_id'))$tid=(int)tenant_id();}catch(Throwable $e){}
    return sk1304_root().'/storage/cache/module-linkage-v13041-'.$tid.'.json';
}
function sk1304_linkage_scan(bool $force=false): array {
    if(is_file(__DIR__.'/core_sync_v1301.php'))require_once __DIR__.'/core_sync_v1301.php';
    $cache=sk1304_cache_file();
    if(!$force && is_file($cache) && (time()-(int)@filemtime($cache))<120){$j=json_decode((string)@file_get_contents($cache),true);if(is_array($j)&&($j['version']??'')==='13.0.5.1')return $j;}
    $pub=sk1304_scan_rows(sk1304_public_items(),'public');$adm=sk1304_scan_rows(sk1304_admin_items(),'admin');$shared=sk1304_shared_state();
    $issues=[];
    if($pub['missing'])$issues[]=['severity'=>'high','type'=>'public.routes','message'=>$pub['missing'].' registered public PHP route(s) point to missing files.'];
    if($adm['missing'])$issues[]=['severity'=>'high','type'=>'admin.routes','message'=>$adm['missing'].' registered admin PHP route(s) point to missing files.'];
    if($pub['duplicates'])$issues[]=['severity'=>'medium','type'=>'public.duplicates','message'=>$pub['duplicates'].' duplicate public route registration(s) detected.'];
    if($adm['duplicates'])$issues[]=['severity'=>'medium','type'=>'admin.duplicates','message'=>$adm['duplicates'].' duplicate admin route registration(s) detected.'];
    if(!$shared['name_ok'])$issues[]=['severity'=>'medium','type'=>'identity.name','message'=>'Shared site name is empty.'];
    if(!$shared['logo_ok'])$issues[]=['severity'=>'low','type'=>'identity.logo','message'=>'No shared logo resolved from current settings.'];
    $score=max(0,100-($pub['missing']+$adm['missing'])*12-($pub['duplicates']+$adm['duplicates'])*4-(!$shared['name_ok']?7:0)-(!$shared['logo_ok']?2:0));
    $result=['version'=>'13.0.5.1','generated_at'=>date('c'),'score'=>$score,'public'=>$pub,'admin'=>$adm,'shared'=>$shared,'issues'=>$issues];
    $dir=dirname($cache);if(is_dir($dir)&&is_writable($dir))@file_put_contents($cache,json_encode($result,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),LOCK_EX);
    return $result;
}
function sk1304_safe_repair(): array {
    // v13.0.5.1: route-only. Do NOT sync features, packages or legacy users here.
    $result=['routes'=>0,'access'=>0,'features'=>0,'errors'=>[]];
    try{
        if(is_file(__DIR__.'/core_sync_v1301.php'))require_once __DIR__.'/core_sync_v1301.php';
        if(!function_exists('sk1301_db')||!function_exists('sk1301_table')||!function_exists('sk1301_cols'))return $result;
        $map=function_exists('sk1301_canonical_map')?sk1301_canonical_map():[];
        foreach([['public_navigation_rules_v1100','url'],['public_navigation_v1051','url'],['public_navigation_items_v1051','url']] as [$t,$c])if(sk1301_table($t)&&isset(sk1301_cols($t)[$c]))foreach($map as $old=>$new)if(!str_starts_with($old,'/admin/')){$q=sk1301_db()->prepare('UPDATE `'.$t.'` SET `'.$c.'`=? WHERE `'.$c.'`=?');$q->execute([$new,$old]);$result['routes']+=$q->rowCount();}
        foreach([['admin_navigation_custom_v1022','url'],['admin_navigation_native_v1022','url']] as [$t,$c])if(sk1301_table($t)&&isset(sk1301_cols($t)[$c]))foreach($map as $old=>$new)if(str_starts_with($old,'/admin/')){$q=sk1301_db()->prepare('UPDATE `'.$t.'` SET `'.$c.'`=? WHERE `'.$c.'`=?');$q->execute([$new,$old]);$result['routes']+=$q->rowCount();}
    }catch(Throwable $e){$result['errors'][]=$e->getMessage();}
    $cache=sk1304_cache_file();if(is_file($cache))@unlink($cache);
    return $result;
}
}
