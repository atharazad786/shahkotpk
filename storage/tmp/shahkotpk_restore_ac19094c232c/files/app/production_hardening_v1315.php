<?php
declare(strict_types=1);
/** ShahkotPK v13.0.15.2 — Step 15 stable cleanup readiness. */
if (!function_exists('sk1315_fresh_audit')) {
function sk1315_root(): string { return dirname(__DIR__); }
function sk1315_db(): PDO {
    foreach (['sk1314_db','sk1313_db','sk1312_db','sk1311_db','sk1310_db','sk1309_db','sk1308_db','sk1307_db','sk1306_db','sk1305_db','sk1301_db'] as $fn) {
        if (function_exists($fn)) return $fn();
    }
    return db();
}
function sk1315_tid(): int {
    foreach (['sk1314_tid','sk1313_tid','sk1312_tid','sk1311_tid','sk1310_tid','sk1309_tid','sk1308_tid'] as $fn) {
        if (function_exists($fn)) return (int)$fn();
    }
    return function_exists('tenant_id') ? (int)tenant_id() : 0;
}
function sk1315_actor_id(): ?int {
    try {
        $u=function_exists('current_user')?(current_user()?:[]):[];
        return isset($u['id'])?(int)$u['id']:null;
    } catch (Throwable $e) { return null; }
}
function sk1315_table(string $t): bool {
    try {
        $q=sk1315_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$t]);
        return (bool)$q->fetchColumn();
    } catch (Throwable $e) { return false; }
}
function sk1315_setting(string $k,string $d=''): string {
    if(!sk1315_table('settings')) return $d;
    try {
        $q=sk1315_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');
        $q->execute([$k]);$v=$q->fetchColumn();
        return $v===false?$d:(string)$v;
    } catch(Throwable $e){return $d;}
}
function sk1315_set_settings(array $pairs): int {
    if(!sk1315_table('settings'))return 0;$n=0;
    foreach($pairs as $k=>$v){
        try{$q=sk1315_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([(string)$k,(string)$v]);$n+=$q->rowCount();}catch(Throwable $e){}
    }
    return $n;
}
function sk1315_bool_ini(string $key): ?bool {
    $v=ini_get($key); if($v===false)return null; $s=strtolower(trim((string)$v));
    return !in_array($s,['','0','off','false','no','none'],true);
}
function sk1315_ini_row(string $key,string $wanted,string $severity='medium'): array {
    $raw=ini_get($key); return ['key'=>$key,'actual'=>$raw===false?'unavailable':(string)$raw,'wanted'=>$wanted,'severity'=>$severity];
}
function sk1315_environment(): array {
    $https=(!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off')||strtolower(trim(explode(',',(string)($_SERVER['HTTP_X_FORWARDED_PROTO']??''))[0]??''))==='https';
    if(is_file(__DIR__.'/session_security_v13151.php')) require_once __DIR__.'/session_security_v13151.php';
    if(function_exists('sk13151_apply_session_security')) sk13151_apply_session_security(false);
    $guard=function_exists('sk13151_status')?sk13151_status():[];
    $rows=[];$findings=[];
    $display=sk1315_bool_ini('display_errors');$log=sk1315_bool_ini('log_errors');$expose=sk1315_bool_ini('expose_php');$strict=sk1315_bool_ini('session.use_strict_mode');
    $httpOnly=sk1315_bool_ini('session.cookie_httponly')===true||!empty($guard['effective_httponly']);
    $secure=!$https||sk1315_bool_ini('session.cookie_secure')===true||!empty($guard['effective_secure']);
    $sameSite=trim((string)ini_get('session.cookie_samesite'));$effectiveSame=(string)($guard['effective_samesite']??$sameSite);
    $checks=[
      ['name'=>'display_errors','ok'=>$display===false,'actual'=>ini_get('display_errors'),'wanted'=>'Off','severity'=>'high','msg'=>'PHP display_errors is enabled; production errors can leak paths or sensitive runtime details.'],
      ['name'=>'log_errors','ok'=>$log===true,'actual'=>ini_get('log_errors'),'wanted'=>'On','severity'=>'medium','msg'=>'PHP log_errors is not enabled; production faults may be harder to diagnose safely.'],
      ['name'=>'expose_php','ok'=>$expose===false,'actual'=>ini_get('expose_php'),'wanted'=>'Off','severity'=>'low','msg'=>'expose_php is enabled; disabling it reduces unnecessary server fingerprinting.'],
      ['name'=>'session.use_strict_mode','ok'=>$strict===true,'actual'=>(string)ini_get('session.use_strict_mode').(!empty($guard['fixation_mitigated'])&&$strict!==true?' (authenticated-session rotation guard active)':''),'wanted'=>'1 at server/PHP level','severity'=>'medium','msg'=>'Strict PHP session mode is not enabled at server level; v13.0.15.1 rotates authenticated sessions but server strict_mode is still recommended.'],
      ['name'=>'session.cookie_httponly','ok'=>$httpOnly,'actual'=>$httpOnly?'1 (effective)':'0','wanted'=>'1','severity'=>'high','msg'=>'Session cookies are not effectively marked HttpOnly.'],
      ['name'=>'session.cookie_samesite','ok'=>$effectiveSame!==''&&in_array(strtolower($effectiveSame),['lax','strict'],true),'actual'=>$effectiveSame?:'empty','wanted'=>'Lax/Strict','severity'=>'medium','msg'=>'Session SameSite is not effectively Lax/Strict.'],
    ];
    if($https)$checks[]=['name'=>'session.cookie_secure','ok'=>$secure,'actual'=>$secure?'1 (effective)':'0','wanted'=>'1 on HTTPS','severity'=>'high','msg'=>'HTTPS is active but session cookies are not effectively marked Secure.'];
    foreach($checks as $c){$rows[]=$c;if(!$c['ok'])$findings[]=['severity'=>$c['severity'],'message'=>$c['msg']];}
    $opcacheLoaded=function_exists('opcache_get_status');$opcacheEnabled=false;if($opcacheLoaded){try{$st=@opcache_get_status(false);$opcacheEnabled=is_array($st)&&!empty($st['opcache_enabled']);}catch(Throwable $e){}}
    if(!$opcacheEnabled)$findings[]=['severity'=>'low','message'=>'PHP OPcache does not appear enabled for this request. On production PHP-FPM, OPcache normally improves response time.'];
    return ['https'=>$https,'php_version'=>PHP_VERSION,'sapi'=>PHP_SAPI,'memory_limit'=>(string)ini_get('memory_limit'),'max_execution_time'=>(string)ini_get('max_execution_time'),'opcache_enabled'=>$opcacheEnabled,'session_guard'=>$guard,'checks'=>$rows,'findings'=>$findings];
}
function sk1315_sensitive_files(): array {
    $root=sk1315_root();$candidates=['config/config.php','config/shahkotpk-secrets.php','.env','.htaccess'];$rows=[];$findings=[];
    foreach($candidates as $rel){$f=$root.'/'.$rel;if(!is_file($f)){if(in_array($rel,['config/config.php'],true))$findings[]=['severity'=>'medium','message'=>'Expected configuration file '.$rel.' was not found.'];continue;}$perms=@fileperms($f);$mode=$perms===false?'unknown':substr(sprintf('%o',$perms),-4);$worldWritable=$perms!==false&&(($perms&0x0002)!==0);$rows[]=['path'=>$rel,'mode'=>$mode,'bytes'=>(int)@filesize($f),'world_writable'=>$worldWritable];if($worldWritable)$findings[]=['severity'=>'high','message'=>$rel.' is world-writable. Review hosting file permissions.'];}
    foreach(['storage','uploads'] as $dir){$p=$root.'/'.$dir;$rows[]=['path'=>$dir.'/','mode'=>is_dir($p)?substr(sprintf('%o',(int)@fileperms($p)),-4):'missing','bytes'=>0,'world_writable'=>false,'writable'=>is_dir($p)&&is_writable($p)];if(!is_dir($p)||!is_writable($p))$findings[]=['severity'=>'medium','message'=>$dir.'/ is missing or not writable; uploads/cache/runtime operations may fail.'];}
    return ['rows'=>$rows,'findings'=>$findings];
}
function sk1315_root_artifacts(): array {
    $root=sk1315_root();$findings=[];$rows=[];
    $dangerNames=['phpinfo.php','info.php','debug.php','test.php','server-info.php','db-dump.sql','database.sql','backup.sql'];
    foreach($dangerNames as $name){if(is_file($root.'/'.$name)){$rows[]=$name;$findings[]=['severity'=>'high','message'=>'Potential production debug/backup artifact is web-accessible at /'.$name.'.'];}}
    foreach(['.git','.svn'] as $d){if(is_dir($root.'/'.$d)){$rows[]=$d.'/';$findings[]=['severity'=>'medium','message'=>'Repository metadata directory '.$d.'/ exists under the application web root. Ensure the web server blocks access.'];}}
    $rootFiles=@scandir($root)?:[];$seen=0;foreach($rootFiles as $n){if($seen>=250)break;if($n==='.'||$n==='..')continue;$seen++;if(!is_file($root.'/'.$n))continue;if(preg_match('/\.(?:bak|old|orig|save|sql|tar|tgz|gz)$/i',$n)){if(!in_array($n,$rows,true))$rows[]=$n;$findings[]=['severity'=>'medium','message'=>'Backup/development artifact '.$n.' is present in the public application root.'];}}
    return ['rows'=>array_values(array_unique($rows)),'findings'=>$findings,'root_entries_checked'=>$seen];
}
function sk1315_upload_script_scan(int $limit=800): array {
    $root=sk1315_root();$dir=$root.'/uploads';$hits=[];$checked=0;$truncated=false;if(!is_dir($dir))return ['checked'=>0,'hits'=>[],'truncated'=>false];
    try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if($checked>=$limit){$truncated=true;break;}if(!$f->isFile())continue;$checked++;$ext=strtolower($f->getExtension());if(in_array($ext,['php','phtml','pht','phar','php3','php4','php5','php7','php8'],true)){if(count($hits)<50){$p=str_replace('\\','/',$f->getPathname());$hits[]=ltrim(str_replace(str_replace('\\','/',$root),'',$p),'/');}}}}catch(Throwable $e){}
    return ['checked'=>$checked,'hits'=>$hits,'truncated'=>$truncated];
}
function sk1315_manifest(): array {
    $file=sk1315_root().'/app/stable-release-manifest-v1315.json';if(!is_file($file))return ['exists'=>false,'entries'=>0,'checked'=>0,'missing'=>[],'mismatch'=>[],'invalid'=>true];
    $j=json_decode((string)@file_get_contents($file),true);if(!is_array($j)||!isset($j['files'])||!is_array($j['files']))return ['exists'=>true,'entries'=>0,'checked'=>0,'missing'=>[],'mismatch'=>[],'invalid'=>true];
    $missing=[];$mismatch=[];$checked=0;foreach(array_slice($j['files'],0,120) as $x){if(!is_array($x))continue;$rel=(string)($x['path']??'');$want=strtolower((string)($x['sha256']??''));if($rel===''||!preg_match('/^[a-f0-9]{64}$/',$want))continue;$checked++;$f=sk1315_root().'/'.$rel;if(!is_file($f)){if(count($missing)<80)$missing[]=$rel;continue;}$got=@hash_file('sha256',$f);if(!is_string($got)||!hash_equals($want,strtolower($got))){if(count($mismatch)<80)$mismatch[]=$rel;}}
    return ['exists'=>true,'entries'=>count($j['files']),'checked'=>$checked,'missing'=>$missing,'mismatch'=>$mismatch,'invalid'=>false,'version'=>(string)($j['version']??'')];
}
function sk1315_version_contract(): array {
    $expect=[
      'installed_app_version'=>'13.0.15.2','access_runtime_performance_hotfix'=>'13.0.5.1','step5_data_consistency_version'=>'13.0.5.0','step6_data_repair_version'=>'13.0.6.0','step7_media_integrity_version'=>'13.0.7.0','step8_runtime_integrity_version'=>'13.0.8.0','step9_permission_integrity_version'=>'13.0.9.0','step10_security_integrity_version'=>'13.0.10.0','step11_database_integrity_version'=>'13.0.11.0','step12_public_seo_routing_version'=>'13.0.12.0','step13_background_integrity_version'=>'13.0.13.0','step14_regression_integrity_version'=>'13.0.14.0','step15_production_hardening_version'=>'13.0.15.2','runtime_cache_contract_version'=>'13.0.15.2'
    ];$rows=[];foreach($expect as $k=>$v){$got=sk1315_setting($k,'');$rows[]=['key'=>$k,'expected'=>$v,'actual'=>$got,'ok'=>$got!==''&&version_compare($got,$v,'>=')];}return $rows;
}
function sk1315_control_history(): array {
    $tables=['runtime_integrity_runs_v1308'=>'Runtime','security_integrity_runs_v1310'=>'Security','database_integrity_runs_v1311'=>'Database','public_seo_routing_runs_v1312'=>'Public SEO','background_integrity_runs_v1313'=>'Background','regression_integrity_runs_v1314'=>'Regression'];$rows=[];$tid=sk1315_tid();
    foreach($tables as $t=>$label){if(!sk1315_table($t)){$rows[]=['label'=>$label,'table'=>$t,'exists'=>false,'created_at'=>'','score'=>null];continue;}try{$where='1=1';$p=[];if($tid>0){$where='tenant_id=?';$p[]=$tid;}$q=sk1315_db()->prepare('SELECT * FROM `'.$t.'` WHERE '.$where.' ORDER BY id DESC LIMIT 1');$q->execute($p);$r=$q->fetch(PDO::FETCH_ASSOC)?:[];$score=array_key_exists('score',$r)?(int)$r['score']:null;$rows[]=['label'=>$label,'table'=>$t,'exists'=>true,'created_at'=>(string)($r['created_at']??''),'score'=>$score];}catch(Throwable $e){$rows[]=['label'=>$label,'table'=>$t,'exists'=>true,'created_at'=>'','score'=>null];}}
    return $rows;
}
function sk1315_findings(array $env,array $fs,array $art,array $uploads,array $manifest,array $versions,array $history): array {
    $f=array_merge($env['findings'],$fs['findings'],$art['findings']);
    if($uploads['hits'])$f[]=['severity'=>'high','message'=>count($uploads['hits']).' executable PHP-like file(s) were found inside uploads/. Review and remove only after confirming they are not intentional.'];
    if(!$manifest['exists']||$manifest['invalid'])$f[]=['severity'=>'high','message'=>'Stable release SHA-256 manifest is missing or invalid.'];
    if($manifest['missing'])$f[]=['severity'=>'high','message'=>count($manifest['missing']).' stable package-owned file(s) are missing.'];
    if($manifest['mismatch'])$f[]=['severity'=>'high','message'=>count($manifest['mismatch']).' stable package-owned file(s) differ from the v13.0.15.2 baseline.'];
    $verBad=0;foreach($versions as $x)if(!$x['ok'])$verBad++;if($verBad)$f[]=['severity'=>'medium','message'=>$verBad.' stable version/cache contract setting(s) are missing or older than expected.'];
    $reg=null;foreach($history as $x)if($x['label']==='Regression')$reg=$x;if(!$reg||!$reg['exists']||$reg['created_at']==='')$f[]=['severity'=>'medium','message'=>'No saved Step-14 regression audit was found for the current tenant. Run Full Regression Integrity at least once after installation.'];elseif($reg['score']!==null&&$reg['score']<85)$f[]=['severity'=>'high','message'=>'Latest Step-14 regression score is below 85/100. Review regression findings before treating the site as production-ready.'];
    return $f;
}
function sk1315_scan(): array {
    $env=sk1315_environment();$fs=sk1315_sensitive_files();$art=sk1315_root_artifacts();$uploads=sk1315_upload_script_scan();$manifest=sk1315_manifest();$versions=sk1315_version_contract();$history=sk1315_control_history();$findings=sk1315_findings($env,$fs,$art,$uploads,$manifest,$versions,$history);$pen=0;$high=0;$medium=0;foreach($findings as $x){if($x['severity']==='high'){$pen+=18;$high++;}elseif($x['severity']==='medium'){$pen+=7;$medium++;}else $pen+=2;}$score=max(0,100-min(100,$pen));$ready=$high===0&&$score>=90;
    return ['version'=>'13.0.15.2','generated_at'=>gmdate('c'),'score'=>$score,'ready'=>$ready,'high_count'=>$high,'medium_count'=>$medium,'environment'=>$env,'filesystem'=>$fs,'root_artifacts'=>$art,'uploads'=>$uploads,'manifest'=>$manifest,'versions'=>$versions,'history'=>$history,'findings'=>$findings,'normal_request_scanner'=>false,'upload_scan_limit'=>800];
}
function sk1315_save_run(array $scan): int {
    if(!sk1315_table('production_hardening_runs_v1315'))return 0;
    try{$q=sk1315_db()->prepare('INSERT INTO production_hardening_runs_v1315(tenant_id,actor_user_id,score,ready_flag,high_count,medium_count,manifest_checked,manifest_mismatch,summary_json,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');$q->execute([sk1315_tid(),sk1315_actor_id(),(int)$scan['score'],$scan['ready']?1:0,(int)$scan['high_count'],(int)$scan['medium_count'],(int)$scan['manifest']['checked'],count($scan['manifest']['missing'])+count($scan['manifest']['mismatch']),json_encode($scan,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);return (int)sk1315_db()->lastInsertId();}catch(Throwable $e){return 0;}
}
function sk1315_fresh_audit(): array {$x=sk1315_scan();$x['run_id']=sk1315_save_run($x);return $x;}
function sk1315_last_audit(): ?array {
    if(!sk1315_table('production_hardening_runs_v1315'))return null;
    try{$p=[];$w='1=1';$tid=sk1315_tid();if($tid>0){$w='tenant_id=?';$p[]=$tid;}$q=sk1315_db()->prepare('SELECT id,summary_json,created_at FROM production_hardening_runs_v1315 WHERE '.$w.' ORDER BY id DESC LIMIT 1');$q->execute($p);$r=$q->fetch();if(!$r)return null;$x=json_decode((string)$r['summary_json'],true);if(!is_array($x))return null;$x['run_id']=(int)$r['id'];$x['saved_at']=(string)$r['created_at'];return $x;}catch(Throwable $e){return null;}
}
function sk1315_log_action(string $key,array $result): void {
    if(!sk1315_table('production_hardening_actions_v1315'))return;try{$q=sk1315_db()->prepare('INSERT INTO production_hardening_actions_v1315(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1315_tid(),sk1315_actor_id(),$key,json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}
}
function sk1315_prune(int $keep=100): int {
    if(!sk1315_table('production_hardening_runs_v1315'))return 0;$keep=max(20,min(500,$keep));$tid=sk1315_tid();try{$p=[];$w='1=1';if($tid>0){$w='tenant_id=?';$p[]=$tid;}$q=sk1315_db()->prepare('SELECT id FROM production_hardening_runs_v1315 WHERE '.$w.' ORDER BY id DESC LIMIT 1 OFFSET '.($keep-1));$q->execute($p);$cut=$q->fetchColumn();if($cut===false)return 0;$p2=[(int)$cut];$w2='id<?';if($tid>0){$w2.=' AND tenant_id=?';$p2[]=$tid;}$d=sk1315_db()->prepare('DELETE FROM production_hardening_runs_v1315 WHERE '.$w2);$d->execute($p2);return $d->rowCount();}catch(Throwable $e){return 0;}
}
function sk1315_safe_finalize(): array {
    $scan=sk1315_scan();$stamp=gmdate('Y-m-d H:i:s').' UTC';$status=$scan['ready']?'ready':'review';$r=['status'=>$status,'score'=>$scan['score'],'settings_rows'=>0,'audits_pruned'=>0,'stat_cache_cleared'=>false,'notes'=>[],'errors'=>[]];
    try{$r['settings_rows']=sk1315_set_settings(['installed_app_version'=>'13.0.15.2','step15_production_hardening_version'=>'13.0.15.2','runtime_cache_contract_version'=>'13.0.15.2','public_data_cache_version'=>'13.0.15.2','stable_release_channel'=>'stable','stable_release_baseline'=>'13.0.15.2','stable_release_readiness'=>$status,'stable_release_last_finalize'=>$stamp]);}catch(Throwable $e){$r['errors'][]='Settings: '.$e->getMessage();}
    try{$r['audits_pruned']=sk1315_prune(100);}catch(Throwable $e){$r['errors'][]='Audit history: '.$e->getMessage();}
    try{clearstatcache(true);$r['stat_cache_cleared']=true;}catch(Throwable $e){$r['errors'][]='Stat cache: '.$e->getMessage();}
    $r['notes'][]='Stable metadata is marked READY only when the current bounded audit has no high findings and scores at least 90/100.';
    $r['notes'][]='Finalize does not edit php.ini, .htaccess, passwords, roles, permissions, content, uploads, jobs, notifications or the native front-page menu.';
    sk1315_log_action('safe_production_finalize',$r);return $r;
}
}
