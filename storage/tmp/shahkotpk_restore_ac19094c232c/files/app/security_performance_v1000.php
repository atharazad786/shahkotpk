<?php
declare(strict_types=1);

/** ShahkotPK v10.0.0 — Security & Performance Suite helpers. */

function sk1000_root(): string { return realpath(__DIR__.'/..') ?: dirname(__DIR__); }
function sk1000_table_exists(string $table): bool {
    static $cache=[];
    if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$table)) return false;
    if(array_key_exists($table,$cache)) return $cache[$table];
    try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();}catch(Throwable $e){return $cache[$table]=false;}
}
function sk1000_setting(string $key,string $default=''): string {
    try{if(function_exists('setting'))return (string)setting($key,$default);if(sk1000_table_exists('settings')){$q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}}catch(Throwable $e){}
    return $default;
}
function sk1000_save_setting(string $key,string $value): void {
    if(function_exists('save_setting')){save_setting($key,$value);return;}
    if(!sk1000_table_exists('settings'))throw new RuntimeException('Settings table is unavailable.');
    $q=db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([$key,$value]);
}
function sk1000_uid(): ?int {try{if(function_exists('current_user')){$u=current_user();$id=(int)($u['id']??0);return $id>0?$id:null;}}catch(Throwable $e){}return null;}
function sk1000_tid(): ?int {try{if(function_exists('tenant_id')){$id=(int)tenant_id();return $id>0?$id:null;}}catch(Throwable $e){}return null;}
function sk1000_path(): string {$u=(string)($_SERVER['REQUEST_URI']??'/');$p=(string)(parse_url($u,PHP_URL_PATH)?:'/');return mb_substr($p,0,240);}
function sk1000_method(): string {return mb_substr(strtoupper((string)($_SERVER['REQUEST_METHOD']??'GET')),0,10);}
function sk1000_client_ip(): string {
    $ip=(string)($_SERVER['REMOTE_ADDR']??'');
    // Do not trust forwarded headers by default; the web server/proxy should set REMOTE_ADDR correctly.
    return filter_var($ip,FILTER_VALIDATE_IP)?$ip:'';
}
function sk1000_hash_ip(?string $ip=null): string {$ip=$ip??sk1000_client_ip();return hash('sha256','sk1000|'.sk1000_root().'|'.$ip);}
function sk1000_ip_hint(string $ip): string {
    if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$p=explode('.',$ip);return '***.***.***.'.end($p);}
    if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6))return 'IPv6 …'.substr(str_replace(':','',$ip),-4);
    return 'unknown';
}
function sk1000_event(string $event,string $summary,string $severity='info',array $meta=[],?int $uid=null): void {
    if(!sk1000_table_exists('security_events_v1000'))return;
    try{$q=db()->prepare('INSERT INTO security_events_v1000(tenant_id,user_id,ip_hash,event_key,severity,route,method,status_code,summary,meta_json,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,NOW())');$q->execute([sk1000_tid(),$uid??sk1000_uid(),sk1000_hash_ip(),mb_substr($event,0,120),mb_substr($severity,0,20),sk1000_path(),sk1000_method(),http_response_code()?:200,mb_substr($summary,0,255),json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}
}
function sk1000_bool_setting(string $key,bool $default=false): bool {return sk1000_setting($key,$default?'1':'0')==='1';}
function sk1000_int_setting(string $key,int $default,int $min=0,int $max=1000000): int {return max($min,min($max,(int)sk1000_setting($key,(string)$default)));}

function sk1000_session_touch(): void {
    if(!str_starts_with(sk1000_path(),'/admin/')||!sk1000_table_exists('admin_sessions_v1000'))return;
    if(session_status()!==PHP_SESSION_ACTIVE)@session_start();$sid=(string)session_id();$uid=sk1000_uid();if($sid===''||!$uid)return;
    $hash=hash('sha256',$sid);$ua=hash('sha256',(string)($_SERVER['HTTP_USER_AGENT']??''));$ip=sk1000_hash_ip();
    try{
      $q=db()->prepare('SELECT revoked FROM admin_sessions_v1000 WHERE session_hash=? LIMIT 1');$q->execute([$hash]);$rev=(int)($q->fetchColumn()?:0);
      if($rev===1){sk1000_event('session.blocked','A revoked admin session attempted access.','warning',['session'=>substr($hash,0,12)],$uid);$_SESSION=[];if(ini_get('session.use_cookies')){$p=session_get_cookie_params();@setcookie(session_name(),'',time()-42000,$p['path']??'/',$p['domain']??'',(bool)($p['secure']??false),(bool)($p['httponly']??true));}@session_destroy();http_response_code(403);exit('This admin session has been revoked. Sign in again.');}
      $q=db()->prepare('INSERT INTO admin_sessions_v1000(session_hash,user_id,tenant_id,ip_hash,ip_hint,user_agent_hash,last_route,last_seen_at,created_at,revoked) VALUES(?,?,?,?,?,?,?,NOW(),NOW(),0) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),tenant_id=VALUES(tenant_id),ip_hash=VALUES(ip_hash),ip_hint=VALUES(ip_hint),user_agent_hash=VALUES(user_agent_hash),last_route=VALUES(last_route),last_seen_at=NOW()');
      $q->execute([$hash,$uid,sk1000_tid(),$ip,sk1000_ip_hint(sk1000_client_ip()),$ua,sk1000_path()]);
    }catch(Throwable $e){}
}
function sk1000_check_block(): void {
    if(!sk1000_table_exists('security_ip_blocks_v1000'))return;$h=sk1000_hash_ip();
    try{$q=db()->prepare("SELECT id,reason FROM security_ip_blocks_v1000 WHERE ip_hash=? AND active=1 AND (expires_at IS NULL OR expires_at>NOW()) ORDER BY id DESC LIMIT 1");$q->execute([$h]);$r=$q->fetch(PDO::FETCH_ASSOC);if($r){sk1000_event('access.blocked','A blocked network identity attempted access.','warning',['block_id'=>(int)$r['id']]);http_response_code(403);header('Retry-After: 3600');exit('Access temporarily blocked.');}}catch(Throwable $e){}
}
function sk1000_rate_bucket(): array {
    $path=sk1000_path();$kind=str_starts_with($path,'/admin/')?'admin':(str_starts_with($path,'/api/')?'api':'public');
    if($kind==='public'&&!sk1000_bool_setting('security_public_rate_monitor_v1000',false))return ['count'=>0,'limit'=>0,'kind'=>$kind];
    if(!sk1000_table_exists('security_rate_buckets_v1000'))return ['count'=>0,'limit'=>0,'kind'=>$kind];
    $limit=$kind==='admin'?sk1000_int_setting('security_admin_rpm_v1000',180,30,1200):($kind==='api'?sk1000_int_setting('security_api_rpm_v1000',300,30,3000):sk1000_int_setting('security_public_rpm_v1000',900,60,10000));
    $minute=date('Y-m-d H:i:00');$bucket=hash('sha256',$kind.'|'.sk1000_hash_ip().'|'.$minute);
    try{$q=db()->prepare('INSERT INTO security_rate_buckets_v1000(bucket_key,ip_hash,bucket_type,window_start,request_count,last_route,updated_at) VALUES(?,?,?,?,1,?,NOW()) ON DUPLICATE KEY UPDATE request_count=request_count+1,last_route=VALUES(last_route),updated_at=NOW()');$q->execute([$bucket,sk1000_hash_ip(),$kind,$minute,$path]);$q=db()->prepare('SELECT request_count FROM security_rate_buckets_v1000 WHERE bucket_key=?');$q->execute([$bucket]);$count=(int)$q->fetchColumn();if($count===$limit+1||($count>$limit&&$count%25===0))sk1000_event('rate.limit','Request rate exceeded configured threshold.','warning',['kind'=>$kind,'count'=>$count,'limit'=>$limit]);if($count>$limit&&sk1000_bool_setting('security_rate_enforce_v1000',false)){http_response_code(429);header('Retry-After: 60');exit('Too many requests. Please retry shortly.');}return ['count'=>$count,'limit'=>$limit,'kind'=>$kind];}catch(Throwable $e){return ['count'=>0,'limit'=>$limit,'kind'=>$kind];}
}
function sk1000_suspicious_route(): void {
    $p=strtolower(sk1000_path());$patterns=['/.env','/wp-admin','/wp-login','/phpmyadmin','/.git/','/vendor/phpunit','/xmlrpc.php'];foreach($patterns as $x)if(str_contains($p,$x)){sk1000_event('route.probe','Common exploit/probe path requested.','warning',['pattern'=>$x]);break;}
}
function sk1000_request_boot(): void {
    static $done=false;if($done||PHP_SAPI==='cli')return;$done=true;
    $GLOBALS['sk1000_request_start']=(float)($_SERVER['REQUEST_TIME_FLOAT']??microtime(true));
    try{sk1000_check_block();sk1000_suspicious_route();sk1000_rate_bucket();sk1000_session_touch();}catch(Throwable $e){}
    register_shutdown_function('sk1000_request_shutdown');
}
function sk1000_request_shutdown(): void {
    try{
      $start=(float)($GLOBALS['sk1000_request_start']??($_SERVER['REQUEST_TIME_FLOAT']??microtime(true)));$ms=max(0,(microtime(true)-$start)*1000);$status=http_response_code()?:200;$path=sk1000_path();$slow=sk1000_int_setting('performance_slow_ms_v1000',1500,250,30000);$isAdmin=str_starts_with($path,'/admin/');$sample=max(0,min(20,sk1000_int_setting('performance_public_sample_percent_v1000',2,0,20)));$keep=$isAdmin||$ms>=$slow||($sample>0&&mt_rand(1,100)<=$sample);
      if($keep&&sk1000_table_exists('performance_requests_v1000')){$q=db()->prepare('INSERT INTO performance_requests_v1000(tenant_id,user_id,route,method,status_code,duration_ms,memory_peak_bytes,created_at) VALUES(?,?,?,?,?,?,?,NOW())');$q->execute([sk1000_tid(),sk1000_uid(),$path,sk1000_method(),$status,round($ms,2),memory_get_peak_usage(true)]);}
      if($status>=500)sk1000_event('response.5xx','Server error response recorded.','error',['duration_ms'=>round($ms,2),'status'=>$status]);elseif(in_array($status,[401,403,429],true))sk1000_event('response.security','Security-related HTTP response recorded.','warning',['duration_ms'=>round($ms,2),'status'=>$status]);
    }catch(Throwable $e){}
}

function sk1000_hook_installed(): bool {$p=sk1000_root().'/app/bootstrap.php';return is_file($p)&&str_contains((string)@file_get_contents($p),'SHAHKOTPK_V1000_SECURITY_PERFORMANCE_HOOK_START');}
function sk1000_install_hook(): array {
    $p=sk1000_root().'/app/bootstrap.php';if(!is_file($p)||!is_writable($p))throw new RuntimeException('app/bootstrap.php is missing or not writable.');$src=(string)file_get_contents($p);if(sk1000_hook_installed())return ['installed'=>true,'already'=>true];$backup=$p.'.pre-v1000.bak';if(!is_file($backup)&&!@copy($p,$backup))throw new RuntimeException('Could not create bootstrap backup.');
    $hook="\n/* SHAHKOTPK_V1000_SECURITY_PERFORMANCE_HOOK_START */\nrequire_once __DIR__.'/security_performance_v1000.php';\nif(function_exists('sk1000_request_boot')) sk1000_request_boot();\n/* SHAHKOTPK_V1000_SECURITY_PERFORMANCE_HOOK_END */\n";$trim=rtrim($src);if(str_ends_with($trim,'?>')){$trim=substr($trim,0,-2);$new=$trim.$hook."?>\n";}else{$new=$trim.$hook;}if(@file_put_contents($p,$new,LOCK_EX)===false)throw new RuntimeException('Could not update app/bootstrap.php.');return ['installed'=>true,'backup'=>$backup];
}
function sk1000_uninstall_hook(): array {
    $p=sk1000_root().'/app/bootstrap.php';if(!is_file($p)||!is_writable($p))throw new RuntimeException('app/bootstrap.php is missing or not writable.');$src=(string)file_get_contents($p);$rx='/\\n?\\/\\* SHAHKOTPK_V1000_SECURITY_PERFORMANCE_HOOK_START \\*\\/.*?\\/\\* SHAHKOTPK_V1000_SECURITY_PERFORMANCE_HOOK_END \\*\\/\\n?/s';$new=preg_replace($rx,"\n",$src,-1,$count);if($new===null)throw new RuntimeException('Could not parse bootstrap hook.');if($count<1)return ['removed'=>false,'message'=>'Hook was not installed.'];if(@file_put_contents($p,$new,LOCK_EX)===false)throw new RuntimeException('Could not remove bootstrap hook.');return ['removed'=>true,'backup'=>$p.'.pre-v1000.bak'];
}
function sk1000_https(): bool {return (!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO']??''))==='https';}
function sk1000_disk(): array {$root=sk1000_root();$t=@disk_total_space($root);$f=@disk_free_space($root);$u=($t!==false&&$f!==false)?$t-$f:0;return ['total'=>$t===false?0:(int)$t,'free'=>$f===false?0:(int)$f,'used'=>(int)$u,'percent'=>$t?round($u*100/$t,1):null];}
function sk1000_fmt_bytes(int|float|null $n): string {$n=(float)($n??0);$u=['B','KB','MB','GB','TB'];$i=0;while($n>=1024&&$i<count($u)-1){$n/=1024;$i++;}return number_format($n,$i?2:0).' '.$u[$i];}
function sk1000_overview(): array {
    $o=['events24'=>0,'critical24'=>0,'rate24'=>0,'sessions'=>0,'blocks'=>0,'requests24'=>0,'avg_ms'=>0.0,'max_ms'=>0.0,'slow24'=>0,'errors24'=>0];
    try{if(sk1000_table_exists('security_events_v1000')){$r=db()->query("SELECT COUNT(*) total,SUM(severity IN ('error','critical')) critical,SUM(event_key='rate.limit') rate_events FROM security_events_v1000 WHERE created_at>=NOW()-INTERVAL 24 HOUR")->fetch(PDO::FETCH_ASSOC)?:[];$o['events24']=(int)($r['total']??0);$o['critical24']=(int)($r['critical']??0);$o['rate24']=(int)($r['rate_events']??0);}if(sk1000_table_exists('admin_sessions_v1000'))$o['sessions']=(int)(db()->query("SELECT COUNT(*) FROM admin_sessions_v1000 WHERE revoked=0 AND last_seen_at>=NOW()-INTERVAL 60 MINUTE")->fetchColumn()?:0);if(sk1000_table_exists('security_ip_blocks_v1000'))$o['blocks']=(int)(db()->query("SELECT COUNT(*) FROM security_ip_blocks_v1000 WHERE active=1 AND (expires_at IS NULL OR expires_at>NOW())")->fetchColumn()?:0);if(sk1000_table_exists('performance_requests_v1000')){$slow=sk1000_int_setting('performance_slow_ms_v1000',1500,250,30000);$q=db()->prepare('SELECT COUNT(*) total,COALESCE(AVG(duration_ms),0) avg_ms,COALESCE(MAX(duration_ms),0) max_ms,SUM(duration_ms>=?) slow,SUM(status_code>=500) errors FROM performance_requests_v1000 WHERE created_at>=NOW()-INTERVAL 24 HOUR');$q->execute([$slow]);$r=$q->fetch(PDO::FETCH_ASSOC)?:[];$o['requests24']=(int)($r['total']??0);$o['avg_ms']=(float)($r['avg_ms']??0);$o['max_ms']=(float)($r['max_ms']??0);$o['slow24']=(int)($r['slow']??0);$o['errors24']=(int)($r['errors']??0);}}catch(Throwable $e){}
    return $o;
}
function sk1000_security_events(int $limit=80): array {if(!sk1000_table_exists('security_events_v1000'))return [];try{$q=db()->query('SELECT * FROM security_events_v1000 ORDER BY id DESC LIMIT '.max(1,min(250,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
function sk1000_sessions(int $limit=100): array {if(!sk1000_table_exists('admin_sessions_v1000'))return [];try{$q=db()->query('SELECT s.*,u.name user_name,u.email user_email FROM admin_sessions_v1000 s LEFT JOIN users u ON u.id=s.user_id ORDER BY s.last_seen_at DESC LIMIT '.max(1,min(250,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){try{$q=db()->query('SELECT * FROM admin_sessions_v1000 ORDER BY last_seen_at DESC LIMIT '.max(1,min(250,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $x){return [];}}}
function sk1000_revoke_session(int $id,int $actor): array {if($id<1)throw new InvalidArgumentException('Invalid session.');$q=db()->prepare('UPDATE admin_sessions_v1000 SET revoked=1,revoked_at=NOW(),revoked_by=? WHERE id=?');$q->execute([$actor,$id]);sk1000_event('session.revoked','Admin session revoked from Security Center.','warning',['session_id'=>$id],$actor);return ['id'=>$id,'revoked'=>$q->rowCount()>0];}
function sk1000_blocks(): array {if(!sk1000_table_exists('security_ip_blocks_v1000'))return [];try{return db()->query('SELECT * FROM security_ip_blocks_v1000 ORDER BY active DESC,id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
function sk1000_add_block(string $ip,string $reason,int $minutes,int $actor): array {if(!filter_var($ip,FILTER_VALIDATE_IP))throw new InvalidArgumentException('Enter a valid IPv4 or IPv6 address.');$minutes=max(5,min(525600,$minutes));$q=db()->prepare('INSERT INTO security_ip_blocks_v1000(ip_hash,ip_hint,reason,active,expires_at,created_by,created_at) VALUES(?,?,?,1,DATE_ADD(NOW(),INTERVAL ? MINUTE),?,NOW())');$q->execute([sk1000_hash_ip($ip),sk1000_ip_hint($ip),mb_substr(trim($reason)?:'Manual security block',0,255),$minutes,$actor]);$id=(int)db()->lastInsertId();sk1000_event('block.created','Network identity block created.','warning',['block_id'=>$id,'duration_minutes'=>$minutes],$actor);return ['id'=>$id,'ip_hint'=>sk1000_ip_hint($ip),'minutes'=>$minutes];}
function sk1000_disable_block(int $id,int $actor): array {$q=db()->prepare('UPDATE security_ip_blocks_v1000 SET active=0,disabled_at=NOW(),disabled_by=? WHERE id=?');$q->execute([$actor,$id]);sk1000_event('block.disabled','Network identity block disabled.','info',['block_id'=>$id],$actor);return ['id'=>$id,'disabled'=>$q->rowCount()>0];}
function sk1000_top_routes(int $limit=20): array {if(!sk1000_table_exists('performance_requests_v1000'))return [];try{$q=db()->query('SELECT route,COUNT(*) hits,ROUND(AVG(duration_ms),1) avg_ms,ROUND(MAX(duration_ms),1) max_ms,SUM(status_code>=500) errors FROM performance_requests_v1000 WHERE created_at>=NOW()-INTERVAL 24 HOUR GROUP BY route ORDER BY avg_ms DESC LIMIT '.max(1,min(50,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
function sk1000_slow_queries(int $limit=20): array {
    try{$sql="SELECT LEFT(DIGEST_TEXT,500) digest_text,COUNT_STAR count_star,ROUND(AVG_TIMER_WAIT/1000000000,2) avg_ms,ROUND(MAX_TIMER_WAIT/1000000000,2) max_ms,ROUND(SUM_TIMER_WAIT/1000000000,2) total_ms FROM performance_schema.events_statements_summary_by_digest WHERE SCHEMA_NAME=DATABASE() AND DIGEST_TEXT IS NOT NULL ORDER BY AVG_TIMER_WAIT DESC LIMIT ".max(1,min(50,$limit));$q=db()->query($sql);return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}
}
function sk1000_running_queries(int $limit=20): array {try{$q=db()->query("SELECT ID,USER,HOST,DB,COMMAND,TIME,STATE,LEFT(INFO,500) INFO FROM information_schema.PROCESSLIST WHERE DB=DATABASE() AND COMMAND<>'Sleep' AND ID<>CONNECTION_ID() ORDER BY TIME DESC LIMIT ".max(1,min(50,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
function sk1000_alert_upsert(string $key,string $severity,string $title,string $message,array $meta=[]): void {if(!sk1000_table_exists('performance_alerts_v1000'))return;try{$q=db()->prepare("INSERT INTO performance_alerts_v1000(alert_key,severity,title,message,state,meta_json,first_seen_at,last_seen_at) VALUES(?,?,?,?,'open',?,NOW(),NOW()) ON DUPLICATE KEY UPDATE severity=VALUES(severity),title=VALUES(title),message=VALUES(message),state='open',meta_json=VALUES(meta_json),last_seen_at=NOW(),resolved_at=NULL,resolved_by=NULL");$q->execute([$key,$severity,mb_substr($title,0,160),mb_substr($message,0,500),json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}
}
function sk1000_generate_alerts(): array {$made=[];$disk=sk1000_disk();$diskLimit=sk1000_int_setting('performance_disk_alert_percent_v1000',85,60,99);if($disk['percent']!==null&&$disk['percent']>=$diskLimit){sk1000_alert_upsert('disk.high',$disk['percent']>=95?'critical':'warning','Hosting disk usage is high','Disk usage is '.$disk['percent'].'%, above the configured '.$diskLimit.'% threshold.',$disk);$made[]='disk.high';}$o=sk1000_overview();$errLimit=sk1000_int_setting('performance_5xx_alert_count_v1000',5,1,500);if($o['errors24']>=$errLimit){sk1000_alert_upsert('http.5xx','warning','Server errors detected',$o['errors24'].' sampled HTTP 5xx responses were recorded in the last 24 hours.',['count'=>$o['errors24']]);$made[]='http.5xx';}$long=sk1000_running_queries(10);foreach($long as $r)if((int)($r['TIME']??0)>=10){sk1000_alert_upsert('db.long_query','warning','Long-running database query detected','At least one current database query has been running for 10 seconds or longer.',['seconds'=>(int)$r['TIME']]);$made[]='db.long_query';break;}return $made;}
function sk1000_alerts(int $limit=80): array {if(!sk1000_table_exists('performance_alerts_v1000'))return [];try{$q=db()->query('SELECT * FROM performance_alerts_v1000 ORDER BY (state=\'open\') DESC,last_seen_at DESC LIMIT '.max(1,min(200,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
function sk1000_resolve_alert(int $id,int $actor): array {$q=db()->prepare("UPDATE performance_alerts_v1000 SET state='resolved',resolved_at=NOW(),resolved_by=? WHERE id=?");$q->execute([$actor,$id]);return ['id'=>$id,'resolved'=>$q->rowCount()>0];}
function sk1000_cleanup(int $actor=0): array {$days=sk1000_int_setting('performance_retention_days_v1000',14,1,90);$o=[];foreach([['performance_requests_v1000',$days],['security_events_v1000',max(30,$days*2)],['security_rate_buckets_v1000',2],['admin_sessions_v1000',60]] as [$t,$d]){if(!sk1000_table_exists($t))continue;try{$col=$t==='security_rate_buckets_v1000'?'updated_at':($t==='admin_sessions_v1000'?'last_seen_at':'created_at');$q=db()->prepare("DELETE FROM `$t` WHERE `$col`<DATE_SUB(NOW(),INTERVAL ? DAY)");$q->execute([$d]);$o[$t]=$q->rowCount();}catch(Throwable $e){$o[$t]='error';}}sk1000_event('telemetry.cleanup','Security/performance retention cleanup completed.','info',$o,$actor?:null);return $o;}
function sk1000_security_score(): array {$score=100;$notes=[];if(!sk1000_https()){$score-=15;$notes[]='Current admin request is not detected as HTTPS.';}if(!sk1000_hook_installed()){$score-=12;$notes[]='Global protection/telemetry hook is not enabled.';}if(!sk1000_bool_setting('security_rate_enforce_v1000',false)){$score-=5;$notes[]='Rate limits are monitor-only.';}$a=sk1000_alerts(100);$open=array_filter($a,fn($x)=>($x['state']??'')==='open');foreach($open as $x){$score-=($x['severity']??'')==='critical'?12:4;}return ['score'=>max(0,$score),'notes'=>$notes,'open_alerts'=>count($open)];}
?>