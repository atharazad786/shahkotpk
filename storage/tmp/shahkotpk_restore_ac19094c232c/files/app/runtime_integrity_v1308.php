<?php
declare(strict_types=1);
/** ShahkotPK v13.0.8.0 — on-demand runtime/cache/session performance integrity. */
if (!function_exists('sk1308_fresh_audit')) {
function sk1308_db(): PDO {
    if (function_exists('sk1307_db')) return sk1307_db();
    if (function_exists('sk1306_db')) return sk1306_db();
    if (function_exists('sk1305_db')) return sk1305_db();
    if (function_exists('sk1301_db')) return sk1301_db();
    return db();
}
function sk1308_tid(): int {
    if (function_exists('sk1307_tid')) return sk1307_tid();
    if (function_exists('sk1306_tid')) return sk1306_tid();
    if (function_exists('sk1305_tid')) return sk1305_tid();
    if (function_exists('sk1301_tid')) return sk1301_tid();
    return function_exists('tenant_id') ? (int)tenant_id() : 0;
}
function sk1308_actor_id(): ?int {
    $u=function_exists('current_user')?(current_user()?:[]):[];
    return isset($u['id'])?(int)$u['id']:null;
}
function sk1308_ident(string $name): string {
    if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name))throw new InvalidArgumentException('Unsafe SQL identifier');
    return '`'.$name.'`';
}
function sk1308_table(string $table): bool {
    if(function_exists('sk1307_table'))return sk1307_table($table);
    if(function_exists('sk1306_table'))return sk1306_table($table);
    try{$q=sk1308_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1308_cols(string $table): array {
    if(function_exists('sk1307_cols'))return sk1307_cols($table);
    if(function_exists('sk1306_cols'))return sk1306_cols($table);
    $out=[];try{$q=sk1308_db()->prepare('SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$table]);foreach($q->fetchAll()?:[] as $r)$out[(string)$r['COLUMN_NAME']]=$r;}catch(Throwable $e){}return $out;
}
function sk1308_setting(string $key,string $default=''): string {
    if(!sk1308_table('settings'))return $default;
    try{$q=sk1308_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}catch(Throwable $e){return $default;}
}
function sk1308_set_settings(array $pairs): int {
    if(!sk1308_table('settings')||!$pairs)return 0;$n=0;
    try{$q=sk1308_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');foreach($pairs as $k=>$v){$q->execute([(string)$k,(string)$v]);$n+=$q->rowCount();}}catch(Throwable $e){}
    return $n;
}
function sk1308_db_latency(int $samples=5): array {
    $samples=max(1,min(8,$samples));$times=[];$err='';
    try{$pdo=sk1308_db();for($i=0;$i<$samples;$i++){$t=microtime(true);$q=$pdo->query('SELECT 1');if($q)$q->fetchColumn();$times[]=round((microtime(true)-$t)*1000,3);}}
    catch(Throwable $e){$err=$e->getMessage();}
    if(!$times)return ['samples'=>0,'avg_ms'=>null,'max_ms'=>null,'min_ms'=>null,'values'=>[],'error'=>$err];
    return ['samples'=>count($times),'avg_ms'=>round(array_sum($times)/count($times),3),'max_ms'=>max($times),'min_ms'=>min($times),'values'=>$times,'error'=>$err];
}
function sk1308_session_metrics(): array {
    $data=(isset($_SESSION)&&is_array($_SESSION))?$_SESSION:[];$bytes=0;$error='';
    try{$s=@serialize($data);$bytes=is_string($s)?strlen($s):0;}catch(Throwable $e){$error=$e->getMessage();}
    $keys=array_keys($data);$largest=[];
    foreach($data as $k=>$v){$b=0;try{$s=@serialize($v);$b=is_string($s)?strlen($s):0;}catch(Throwable $e){}$largest[]=['key'=>(string)$k,'bytes'=>$b];}
    usort($largest,static fn($a,$b)=>$b['bytes']<=>$a['bytes']);$largest=array_slice($largest,0,6);
    return ['bytes'=>$bytes,'keys'=>count($keys),'handler'=>(string)ini_get('session.save_handler'),'largest'=>$largest,'error'=>$error];
}
function sk1308_php_runtime(): array {
    $opEnabled=(bool)filter_var((string)ini_get('opcache.enable'),FILTER_VALIDATE_BOOLEAN);$opCli=(bool)filter_var((string)ini_get('opcache.enable_cli'),FILTER_VALIDATE_BOOLEAN);$opStatus=null;
    if(function_exists('opcache_get_status')){try{$st=@opcache_get_status(false);if(is_array($st))$opStatus=['enabled'=>(bool)($st['opcache_enabled']??false),'cache_full'=>(bool)($st['cache_full']??false),'memory_used'=>(int)($st['memory_usage']['used_memory']??0),'memory_free'=>(int)($st['memory_usage']['free_memory']??0),'hit_rate'=>(float)($st['opcache_statistics']['opcache_hit_rate']??0)];}catch(Throwable $e){}}
    return ['php_version'=>PHP_VERSION,'sapi'=>PHP_SAPI,'memory_limit'=>(string)ini_get('memory_limit'),'max_execution_time'=>(string)ini_get('max_execution_time'),'realpath_cache_size'=>(string)ini_get('realpath_cache_size'),'realpath_cache_ttl'=>(string)ini_get('realpath_cache_ttl'),'opcache_ini_enabled'=>$opEnabled,'opcache_cli_enabled'=>$opCli,'opcache_status'=>$opStatus];
}
function sk1308_table_estimate(string $table): ?int {
    if(!sk1308_table($table))return null;
    try{$q=sk1308_db()->prepare('SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);$v=$q->fetchColumn();return $v===false?null:(int)$v;}catch(Throwable $e){return null;}
}
function sk1308_cache_estimates(): array {
    $tables=['ai_recommendation_cache','ai_recommendation_cache_v2','notification_cache','search_cache','runtime_cache','route_cache'];$out=[];
    foreach($tables as $t){$x=sk1308_table_estimate($t);if($x!==null)$out[$t]=$x;}
    return $out;
}
function sk1308_version_contract(): array {
    $installed=sk1308_setting('installed_app_version','');
    $globalWant=$installed!==''?$installed:'13.0.8.0';
    $rules=[
      ['key'=>'installed_app_version','expected'=>$globalWant,'mode'=>'exact'],
      ['key'=>'step8_runtime_integrity_version','expected'=>'13.0.8.0','mode'=>'min'],
      ['key'=>'public_data_cache_version','expected'=>'13.0.8.0','mode'=>'min'],
      ['key'=>'runtime_cache_contract_version','expected'=>$globalWant,'mode'=>'min'],
      ['key'=>'access_runtime_performance_hotfix','expected'=>'13.0.5.1','mode'=>'min'],
    ];$rows=[];$bad=0;
    foreach($rules as $r){$got=sk1308_setting($r['key'],'');$want=$r['expected'];$ok=$got!==''&&($r['mode']==='exact'?$got===$want:version_compare($got,$want,'>='));$rows[]=['key'=>$r['key'],'expected'=>$want,'actual'=>$got,'ok'=>$ok];if(!$ok)$bad++;}
    return ['rows'=>$rows,'mismatches'=>$bad];
}
function sk1308_runtime_files(): array {
    $root=dirname(__DIR__);$checks=[
        ['file'=>'app/access_control_v1300.php','needles'=>['access_runtime_performance_hotfix','sk1300_runtime']],
        ['file'=>'app/public_runtime_v1101.php','needles'=>['public_runtime']],
        ['file'=>'app/landing_sync_v1303.php','needles'=>['landing']],
    ];$out=[];
    foreach($checks as $c){$path=$root.'/'.$c['file'];$exists=is_file($path);$size=$exists?(int)filesize($path):0;$hits=[];if($exists&&$size>0&&$size<1024*1024){$txt=@file_get_contents($path);if(is_string($txt))foreach($c['needles'] as $n)$hits[$n]=substr_count(strtolower($txt),strtolower($n));}$out[]=['file'=>$c['file'],'exists'=>$exists,'bytes'=>$size,'markers'=>$hits];}
    return $out;
}
function sk1308_findings(array $db,array $session,array $php,array $versions,array $caches): array {
    $f=[];
    $avg=$db['avg_ms'];if($avg===null)$f[]=['severity'=>'high','key'=>'db_unavailable','message'=>'Database latency benchmark failed.'];elseif($avg>75)$f[]=['severity'=>'high','key'=>'db_latency','message'=>'Average DB round-trip is '.number_format((float)$avg,1).' ms.'];elseif($avg>20)$f[]=['severity'=>'medium','key'=>'db_latency','message'=>'Average DB round-trip is '.number_format((float)$avg,1).' ms; investigate hosting/database latency if this persists.'];
    if((int)$session['bytes']>262144)$f[]=['severity'=>'high','key'=>'session_size','message'=>'Current admin session payload is larger than 256 KB.'];elseif((int)$session['bytes']>65536)$f[]=['severity'=>'medium','key'=>'session_size','message'=>'Current admin session payload is larger than 64 KB.'];
    if((int)$versions['mismatches']>0)$f[]=['severity'=>'medium','key'=>'cache_version','message'=>$versions['mismatches'].' runtime/cache version contract value(s) are stale or missing.'];
    $op=$php['opcache_status'];if($op===null&&!$php['opcache_ini_enabled'])$f[]=['severity'=>'low','key'=>'opcache','message'=>'PHP OPcache is not reported as enabled for this request.'];elseif(is_array($op)&&!empty($op['cache_full']))$f[]=['severity'=>'medium','key'=>'opcache_full','message'=>'PHP OPcache reports cache_full=true.'];
    foreach($caches as $t=>$rows)if($rows>50000)$f[]=['severity'=>'low','key'=>'cache_size','message'=>$t.' has an estimated '.number_format($rows).' rows; review retention/expiry policy.'];
    return $f;
}
function sk1308_scan(): array {
    $db=sk1308_db_latency(5);$session=sk1308_session_metrics();$php=sk1308_php_runtime();$versions=sk1308_version_contract();$caches=sk1308_cache_estimates();$files=sk1308_runtime_files();$findings=sk1308_findings($db,$session,$php,$versions,$caches);
    $penalty=0;foreach($findings as $x){$penalty+=($x['severity']==='high'?22:($x['severity']==='medium'?10:3));}$score=max(0,100-min(100,$penalty));
    return ['version'=>'13.0.8.0','generated_at'=>gmdate('c'),'score'=>$score,'db'=>$db,'session'=>$session,'php'=>$php,'versions'=>$versions,'cache_estimates'=>$caches,'runtime_files'=>$files,'findings'=>$findings,'normal_request_profiler'=>false];
}
function sk1308_save_run(array $scan): int {
    if(!sk1308_table('runtime_integrity_runs_v1308'))return 0;
    try{$q=sk1308_db()->prepare('INSERT INTO runtime_integrity_runs_v1308(tenant_id,actor_user_id,score,db_latency_ms,session_bytes,summary_json,created_at) VALUES(?,?,?,?,?,?,NOW())');$q->execute([sk1308_tid(),sk1308_actor_id(),(int)$scan['score'],$scan['db']['avg_ms'],(int)$scan['session']['bytes'],json_encode($scan,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);return (int)sk1308_db()->lastInsertId();}catch(Throwable $e){return 0;}
}
function sk1308_fresh_audit(): array {$x=sk1308_scan();$x['run_id']=sk1308_save_run($x);return $x;}
function sk1308_last_audit(): ?array {
    if(!sk1308_table('runtime_integrity_runs_v1308'))return null;
    try{$params=[];$where='1=1';if(sk1308_tid()>0){$where='tenant_id=?';$params[]=sk1308_tid();}$q=sk1308_db()->prepare('SELECT id,summary_json,created_at FROM runtime_integrity_runs_v1308 WHERE '.$where.' ORDER BY id DESC LIMIT 1');$q->execute($params);$r=$q->fetch();if(!$r)return null;$x=json_decode((string)$r['summary_json'],true);if(!is_array($x))return null;$x['run_id']=(int)$r['id'];$x['saved_at']=(string)$r['created_at'];return $x;}catch(Throwable $e){return null;}
}
function sk1308_log_action(string $key,array $result): void {
    if(!sk1308_table('runtime_integrity_actions_v1308'))return;
    try{$q=sk1308_db()->prepare('INSERT INTO runtime_integrity_actions_v1308(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1308_tid(),sk1308_actor_id(),$key,json_encode($result,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);}catch(Throwable $e){}
}
function sk1308_safe_refresh(): array {
    $r=['settings_rows'=>0,'recommendation_cache_rows'=>0,'stat_cache_cleared'=>false,'notes'=>[],'errors'=>[]];$stamp=gmdate('Y-m-d H:i:s').' UTC';
    try{$installed=sk1308_setting('installed_app_version','13.0.15.2');$r['settings_rows']=sk1308_set_settings(['public_data_cache_version'=>$installed,'runtime_cache_contract_version'=>$installed,'runtime_cache_bust'=>$stamp,'access_runtime_performance_hotfix'=>'13.0.5.1']);}catch(Throwable $e){$r['errors'][]='Settings: '.$e->getMessage();}
    try{if(sk1308_table('ai_recommendation_cache')){$c=sk1308_cols('ai_recommendation_cache');$tid=sk1308_tid();if(isset($c['tenant_id'])&&$tid>0){$q=sk1308_db()->prepare('DELETE FROM `ai_recommendation_cache` WHERE `tenant_id`=?');$q->execute([$tid]);$r['recommendation_cache_rows']=$q->rowCount();}else{$r['notes'][]='AI recommendation cache was not cleared because a safe tenant scope was not available.';}}}catch(Throwable $e){$r['errors'][]='Recommendation cache: '.$e->getMessage();}
    try{clearstatcache(true);$r['stat_cache_cleared']=true;}catch(Throwable $e){$r['errors'][]='PHP stat cache: '.$e->getMessage();}
    $r['notes'][]='Session authentication data, OPcache, content rows, menus, uploads and user records were not modified.';sk1308_log_action('safe_runtime_refresh',$r);return $r;
}
}
