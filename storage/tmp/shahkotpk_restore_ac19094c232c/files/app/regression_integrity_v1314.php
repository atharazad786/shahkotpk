<?php
declare(strict_types=1);
/** ShahkotPK v13.0.14.0 — Step 14 consolidated regression integrity. */
if (!function_exists('sk1314_fresh_audit')) {
function sk1314_root(): string { return dirname(__DIR__); }
function sk1314_db(): PDO {
    foreach (['sk1313_db','sk1312_db','sk1311_db','sk1310_db','sk1309_db','sk1308_db','sk1307_db','sk1306_db','sk1305_db','sk1301_db'] as $fn) if(function_exists($fn)) return $fn();
    return db();
}
function sk1314_tid(): int {
    foreach (['sk1313_tid','sk1312_tid','sk1311_tid','sk1310_tid','sk1309_tid','sk1308_tid'] as $fn) if(function_exists($fn)) return (int)$fn();
    return function_exists('tenant_id')?(int)tenant_id():0;
}
function sk1314_actor_id(): ?int { try{$u=function_exists('current_user')?(current_user()?:[]):[];return isset($u['id'])?(int)$u['id']:null;}catch(Throwable $e){return null;} }
function sk1314_table(string $t): bool { try{$q=sk1314_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$t]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;} }
function sk1314_setting(string $k,string $d=''): string { if(!sk1314_table('settings'))return $d;try{$q=sk1314_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$k]);$v=$q->fetchColumn();return $v===false?$d:(string)$v;}catch(Throwable $e){return $d;} }
function sk1314_set_settings(array $pairs): int { if(!sk1314_table('settings'))return 0;$n=0;foreach($pairs as $k=>$v){try{$q=sk1314_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([(string)$k,(string)$v]);$n+=$q->rowCount();}catch(Throwable $e){}}return $n; }
function sk1314_expected_files(): array {
    return [
      ['step'=>'3','path'=>'app/public_runtime_v1101.php','critical'=>1],['step'=>'3','path'=>'app/landing_sync_v1303.php','critical'=>1],['step'=>'3','path'=>'app/admin_sidebar_native_modules_v1303.php','critical'=>1],['step'=>'3','path'=>'assets/public-header-sync-v1302.js','critical'=>1],['step'=>'3','path'=>'assets/landing-section-sync-v1303.js','critical'=>0],['step'=>'3','path'=>'admin/system-integrity.php','critical'=>0],
      ['step'=>'4','path'=>'app/linkage_integrity_v1304.php','critical'=>1],['step'=>'4','path'=>'admin/module-linkage.php','critical'=>1],['step'=>'4','path'=>'marketplace.php','critical'=>1],['step'=>'4','path'=>'app/access_control_v1300.php','critical'=>1],
      ['step'=>'5','path'=>'app/data_consistency_v1305.php','critical'=>1],['step'=>'5','path'=>'admin/data-consistency.php','critical'=>1],
      ['step'=>'6','path'=>'app/data_repair_v1306.php','critical'=>1],['step'=>'6','path'=>'admin/data-repair.php','critical'=>1],
      ['step'=>'7','path'=>'app/media_integrity_v1307.php','critical'=>1],['step'=>'7','path'=>'admin/media-integrity.php','critical'=>1],
      ['step'=>'8','path'=>'app/runtime_integrity_v1308.php','critical'=>1],['step'=>'8','path'=>'admin/runtime-integrity.php','critical'=>1],
      ['step'=>'9','path'=>'app/permissions_integrity_v1309.php','critical'=>1],['step'=>'9','path'=>'admin/permissions-integrity.php','critical'=>1],
      ['step'=>'10','path'=>'app/security_integrity_v1310.php','critical'=>1],['step'=>'10','path'=>'admin/security-integrity.php','critical'=>1],
      ['step'=>'11','path'=>'app/database_integrity_v1311.php','critical'=>1],['step'=>'11','path'=>'admin/database-integrity.php','critical'=>1],
      ['step'=>'12','path'=>'app/public_seo_routing_v1312.php','critical'=>1],['step'=>'12','path'=>'admin/public-seo-routing.php','critical'=>1],
      ['step'=>'13','path'=>'app/background_integrity_v1313.php','critical'=>1],['step'=>'13','path'=>'admin/background-integrity.php','critical'=>1]
    ];
}
function sk1314_file_matrix(): array {
    $root=sk1314_root();$rows=[];$missingCritical=0;$missingOptional=0;
    foreach(sk1314_expected_files() as $x){$f=$root.'/'.$x['path'];$ok=is_file($f);if(!$ok){if($x['critical'])$missingCritical++;else$missingOptional++;}$rows[]=$x+['exists'=>$ok,'bytes'=>$ok?(int)@filesize($f):0];}
    return ['rows'=>$rows,'missing_critical'=>$missingCritical,'missing_optional'=>$missingOptional];
}
function sk1314_source_checks(): array {
    $root=sk1314_root();$checks=[];
    $defs=[
      ['name'=>'Native front menu ownership','file'=>'assets/public-header-sync-v1302.js','must'=>['sk13034-native-menu','querySelectorAll(\'#sk1302-public-nav'], 'must_not'=>['document.createElement(\'nav\')','classList.add(\'sk13032-managed-header\')']],
      ['name'=>'Step-3 asset de-duplication','file'=>'app/public_runtime_v1101.php','must'=>['sk1101_strip_step3_assets','v=13034','sk1101_is_home_request'], 'must_not'=>[]],
      ['name'=>'Access performance hotfix','file'=>'app/access_control_v1300.php','must'=>['legacy repair is maintenance-only','static $cache=[]','has never been initialized'], 'must_not'=>['sk1300_repair_legacy_access(false);']],
      ['name'=>'Route-only Module Linkage repair','file'=>'app/linkage_integrity_v1304.php','must'=>['route-only','module-linkage-v13041','120'], 'must_not'=>['sk1300_repair_legacy_access']],
      ['name'=>'Marketplace canonical compatibility route','file'=>'marketplace.php','must'=>['canonical Marketplace compatibility route','Location:'], 'must_not'=>[]],
    ];
    foreach($defs as $d){$f=$root.'/'.$d['file'];$txt=is_file($f)?(string)@file_get_contents($f):'';$ok=$txt!=='';$notes=[];foreach($d['must'] as $m)if(strpos($txt,$m)===false){$ok=false;$notes[]='missing marker: '.$m;}foreach($d['must_not'] as $m)if(strpos($txt,$m)!==false){$ok=false;$notes[]='unexpected legacy marker: '.$m;}$checks[]=['name'=>$d['name'],'file'=>$d['file'],'ok'=>$ok,'notes'=>$notes];}
    return $checks;
}
function sk1314_manifest_scan(): array {
    $root=sk1314_root();$dir=$root.'/app/sidebar-modules';$files=is_dir($dir)?(glob($dir.'/*.json')?:[]):[];sort($files);$files=array_slice($files,0,250);$rows=[];$badJson=[];$missingRoutes=[];$seen=[];$dupes=[];$items=0;
    foreach($files as $f){$raw=(string)@file_get_contents($f);$j=json_decode($raw,true);$rel='app/sidebar-modules/'.basename($f);if(!is_array($j)){if(count($badJson)<50)$badJson[]=$rel;continue;}$list=$j['items']??[];if(!is_array($list))continue;foreach($list as $x){if(!is_array($x))continue;$items++;$url=trim((string)($x['url']??''));$label=trim((string)($x['label']??$x['module_key']??$url));if($url==='')continue;$path=(string)(parse_url($url,PHP_URL_PATH)?:$url);if(isset($seen[$path])){$dupes[]=['path'=>$path,'first'=>$seen[$path],'second'=>$label];}else$seen[$path]=$label;$exists=true;if(str_starts_with($path,'/admin/')&&str_ends_with(strtolower($path),'.php'))$exists=is_file($root.$path);if(!$exists&&count($missingRoutes)<100)$missingRoutes[]=['label'=>$label,'path'=>$path,'manifest'=>$rel];$rows[]=['label'=>$label,'path'=>$path,'manifest'=>$rel,'exists'=>$exists];}}
    return ['files_scanned'=>count($files),'items'=>$items,'bad_json'=>$badJson,'missing_routes'=>$missingRoutes,'duplicates'=>$dupes,'rows'=>$rows];
}
function sk1314_version_checks(): array {
    $expect=[
      'installed_app_version'=>'13.0.14.0','access_runtime_performance_hotfix'=>'13.0.5.1','step5_data_consistency_version'=>'13.0.5.0','step6_data_repair_version'=>'13.0.6.0','step7_media_integrity_version'=>'13.0.7.0','step8_runtime_integrity_version'=>'13.0.8.0','step9_permission_integrity_version'=>'13.0.9.0','step10_security_integrity_version'=>'13.0.10.0','step11_database_integrity_version'=>'13.0.11.0','step12_public_seo_routing_version'=>'13.0.12.0','step13_background_integrity_version'=>'13.0.13.0','step14_regression_integrity_version'=>'13.0.14.0'
    ];$rows=[];foreach($expect as $k=>$v){$got=sk1314_setting($k,'');$ok=$got!==''&&version_compare($got,$v,'>=');$rows[]=['key'=>$k,'expected'=>$v,'actual'=>$got,'ok'=>$ok];}return $rows;
}
function sk1314_control_tables(): array {
    $tables=['data_consistency_runs_v1305','data_repair_actions_v1306','media_integrity_runs_v1307','runtime_integrity_runs_v1308','permission_integrity_runs_v1309','security_integrity_runs_v1310','database_integrity_runs_v1311','public_seo_routing_runs_v1312','background_integrity_runs_v1313'];$rows=[];foreach($tables as $t)$rows[]=['table'=>$t,'exists'=>sk1314_table($t)];return $rows;
}
function sk1314_findings(array $files,array $source,array $man,array $versions,array $tables): array {
    $f=[];if($files['missing_critical'])$f[]=['severity'=>'high','message'=>$files['missing_critical'].' critical Step 3–13 control/runtime file(s) are missing. Step 14 package carries known-good copies for the consolidated baseline.'];if($files['missing_optional'])$f[]=['severity'=>'low','message'=>$files['missing_optional'].' optional Step-3 support file(s) are missing.'];
    foreach($source as $x)if(!$x['ok'])$f[]=['severity'=>'high','message'=>$x['name'].' failed source-marker regression check in '.$x['file'].'.'];
    if($man['bad_json'])$f[]=['severity'=>'medium','message'=>count($man['bad_json']).' sidebar manifest JSON file(s) could not be parsed.'];if($man['missing_routes'])$f[]=['severity'=>'high','message'=>count($man['missing_routes']).' sidebar-admin PHP route(s) point to missing physical files.'];if($man['duplicates'])$f[]=['severity'=>'medium','message'=>count($man['duplicates']).' duplicate sidebar route registration(s) were detected.'];
    $verBad=0;foreach($versions as $x)if(!$x['ok'])$verBad++;if($verBad)$f[]=['severity'=>'medium','message'=>$verBad.' version/cache contract setting(s) are missing or older than the consolidated baseline.'];
    $tblBad=0;foreach($tables as $x)if(!$x['exists'])$tblBad++;if($tblBad)$f[]=['severity'=>'medium','message'=>$tblBad.' prior integrity audit table(s) were not found. This can mean a previous migration did not run or the related audit has never been installed cleanly.'];
    return $f;
}
function sk1314_scan(): array {
    $files=sk1314_file_matrix();$source=sk1314_source_checks();$man=sk1314_manifest_scan();$versions=sk1314_version_checks();$tables=sk1314_control_tables();$findings=sk1314_findings($files,$source,$man,$versions,$tables);$pen=0;foreach($findings as $x)$pen+=($x['severity']==='high'?18:($x['severity']==='medium'?7:2));$score=max(0,100-min(100,$pen));
    return ['version'=>'13.0.14.0','generated_at'=>gmdate('c'),'score'=>$score,'files'=>$files,'source_checks'=>$source,'manifests'=>$man,'versions'=>$versions,'control_tables'=>$tables,'findings'=>$findings,'normal_request_scanner'=>false,'manifest_file_limit'=>250];
}
function sk1314_save_run(array $scan): int { if(!sk1314_table('regression_integrity_runs_v1314'))return 0;try{$h=0;$m=0;foreach($scan['findings'] as $x){if($x['severity']==='high')$h++;elseif($x['severity']==='medium')$m++;}$q=sk1314_db()->prepare('INSERT INTO regression_integrity_runs_v1314(tenant_id,actor_user_id,score,high_count,medium_count,files_checked,manifests_checked,summary_json,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())');$q->execute([sk1314_tid(),sk1314_actor_id(),(int)$scan['score'],$h,$m,count($scan['files']['rows']),(int)$scan['manifests']['files_scanned'],json_encode($scan,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);return (int)sk1314_db()->lastInsertId();}catch(Throwable $e){return 0;} }
function sk1314_fresh_audit(): array {$x=sk1314_scan();$x['run_id']=sk1314_save_run($x);return $x;}
function sk1314_last_audit(): ?array { if(!sk1314_table('regression_integrity_runs_v1314'))return null;try{$params=[];$where='1=1';$tid=sk1314_tid();if($tid>0){$where='tenant_id=?';$params[]=$tid;}$q=sk1314_db()->prepare('SELECT id,summary_json,created_at FROM regression_integrity_runs_v1314 WHERE '.$where.' ORDER BY id DESC LIMIT 1');$q->execute($params);$r=$q->fetch();if(!$r)return null;$x=json_decode((string)$r['summary_json'],true);if(!is_array($x))return null;$x['run_id']=(int)$r['id'];$x['saved_at']=(string)$r['created_at'];return $x;}catch(Throwable $e){return null;} }
function sk1314_log_action(string $key,array $result): void { if(!sk1314_table('regression_integrity_actions_v1314'))return;try{$q=sk1314_db()->prepare('INSERT INTO regression_integrity_actions_v1314(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1314_tid(),sk1314_actor_id(),$key,json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){} }
function sk1314_prune(int $keep=100): int { if(!sk1314_table('regression_integrity_runs_v1314'))return 0;$keep=max(20,min(500,$keep));$tid=sk1314_tid();try{$params=[];$where='1=1';if($tid>0){$where='tenant_id=?';$params[]=$tid;}$q=sk1314_db()->prepare('SELECT id FROM regression_integrity_runs_v1314 WHERE '.$where.' ORDER BY id DESC LIMIT 1 OFFSET '.($keep-1));$q->execute($params);$cut=$q->fetchColumn();if($cut===false)return 0;$p=[(int)$cut];$w='id<?';if($tid>0){$w.=' AND tenant_id=?';$p[]=$tid;}$d=sk1314_db()->prepare('DELETE FROM regression_integrity_runs_v1314 WHERE '.$w);$d->execute($p);return $d->rowCount();}catch(Throwable $e){return 0;} }
function sk1314_safe_cleanup(): array {
    $r=['settings_rows'=>0,'cache_files_removed'=>0,'audits_pruned'=>0,'stat_cache_cleared'=>false,'notes'=>[],'errors'=>[]];$stamp=gmdate('Y-m-d H:i:s').' UTC';
    try{$r['settings_rows']=sk1314_set_settings(['step14_regression_integrity_version'=>'13.0.14.0','step14_regression_last_cleanup'=>$stamp]);}catch(Throwable $e){$r['errors'][]='Settings: '.$e->getMessage();}
    try{$cache=sk1314_root().'/storage/cache';if(is_dir($cache)){foreach(glob($cache.'/module-linkage-v1304*.json')?:[] as $f)if(is_file($f)&&@unlink($f))$r['cache_files_removed']++;foreach(glob($cache.'/module-linkage-v13041-*.json')?:[] as $f)if(is_file($f)&&@unlink($f))$r['cache_files_removed']++;}}catch(Throwable $e){$r['errors'][]='Cache: '.$e->getMessage();}
    try{$r['audits_pruned']=sk1314_prune(100);}catch(Throwable $e){$r['errors'][]='Audit history: '.$e->getMessage();}
    try{clearstatcache(true);$r['stat_cache_cleared']=true;}catch(Throwable $e){$r['errors'][]='Stat cache: '.$e->getMessage();}
    $r['notes'][]='Known-good Step 3 native-menu/runtime and Step 4 performance/Marketplace files are already carried by this updater package.';
    $r['notes'][]='No user, listing, business, media, notification, role, permission or public-navigation record is deleted or rewritten by Safe Cleanup.';
    sk1314_log_action('safe_regression_cleanup',$r);return $r;
}
}
