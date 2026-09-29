<?php
declare(strict_types=1);
/** ShahkotPK v13.0.10.0 — on-demand authentication/session/security integrity. */
if (!function_exists('sk1310_fresh_audit')) {
function sk1310_db(): PDO {
    if (function_exists('sk1309_db')) return sk1309_db();
    if (function_exists('sk1308_db')) return sk1308_db();
    if (function_exists('sk1307_db')) return sk1307_db();
    if (function_exists('sk1306_db')) return sk1306_db();
    if (function_exists('sk1305_db')) return sk1305_db();
    if (function_exists('sk1301_db')) return sk1301_db();
    return db();
}
function sk1310_tid(): int {
    if (function_exists('sk1309_tid')) return sk1309_tid();
    if (function_exists('sk1308_tid')) return sk1308_tid();
    return function_exists('tenant_id') ? (int)tenant_id() : 0;
}
function sk1310_actor(): array {
    try { $u=function_exists('current_user')?(current_user()?:[]):[]; return is_array($u)?$u:[]; } catch (Throwable $e) { return []; }
}
function sk1310_actor_id(): ?int { $u=sk1310_actor(); return isset($u['id'])?(int)$u['id']:null; }
function sk1310_table(string $table): bool {
    if (function_exists('sk1309_table')) return sk1309_table($table);
    try{$q=sk1310_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1310_https(): bool {
    $https=strtolower((string)($_SERVER['HTTPS']??''));
    if($https!==''&&$https!=='off'&&$https!=='0')return true;
    $xfp=strtolower(trim(explode(',',(string)($_SERVER['HTTP_X_FORWARDED_PROTO']??''))[0]??''));
    return $xfp==='https'||(int)($_SERVER['SERVER_PORT']??0)===443;
}
function sk1310_session_policy(): array {
    if(is_file(__DIR__.'/session_security_v13151.php')) require_once __DIR__.'/session_security_v13151.php';
    if(function_exists('sk13151_apply_session_security')) sk13151_apply_session_security(false);
    $active=session_status()===PHP_SESSION_ACTIVE;$https=sk1310_https();
    $guard=function_exists('sk13151_status')?sk13151_status():[];
    $same=trim((string)ini_get('session.cookie_samesite'));
    $effectiveSame=(string)($guard['effective_samesite']??$same);$sameNorm=strtolower($effectiveSame);
    $strict=(string)ini_get('session.use_strict_mode')==='1';
    $effectiveHttp=!empty($guard['effective_httponly'])||(string)ini_get('session.cookie_httponly')==='1';
    $effectiveSecure=!$https||!empty($guard['effective_secure'])||(string)ini_get('session.cookie_secure')==='1';
    $fixation=$strict||!empty($guard['fixation_mitigated']);
    $rows=[
        ['key'=>'session.use_strict_mode','value'=>(string)ini_get('session.use_strict_mode').($fixation&&!$strict?' (app rotation guard active)':''),'ok'=>$strict,'severity'=>'medium','recommend'=>'1 at server/PHP level; current authenticated session is rotated by v13.0.15.1'],
        ['key'=>'session.use_only_cookies','value'=>(string)ini_get('session.use_only_cookies'),'ok'=>(string)ini_get('session.use_only_cookies')==='1','severity'=>'high','recommend'=>'1'],
        ['key'=>'session.cookie_httponly','value'=>$effectiveHttp?'1 (effective)':'0','ok'=>$effectiveHttp,'severity'=>'high','recommend'=>'1'],
        ['key'=>'session.cookie_secure','value'=>$effectiveSecure?'1 (effective)':'0','ok'=>$effectiveSecure,'severity'=>'high','recommend'=>$https?'1 on HTTPS':'Enable when HTTPS is enforced'],
        ['key'=>'session.cookie_samesite','value'=>$effectiveSame!==''?$effectiveSame:'(empty)','ok'=>in_array($sameNorm,['lax','strict'],true),'severity'=>'medium','recommend'=>'Lax or Strict unless cross-site flow requires None'],
        ['key'=>'session.gc_maxlifetime','value'=>(string)ini_get('session.gc_maxlifetime'),'ok'=>(int)ini_get('session.gc_maxlifetime')<=86400,'severity'=>'low','recommend'=>'<= 86400 seconds unless business flow requires longer'],
    ];
    $sidLen=0;if($active){try{$sidLen=strlen((string)session_id());}catch(Throwable $e){$sidLen=0;}}
    return ['active'=>$active,'https'=>$https,'session_name'=>(string)session_name(),'session_id_length'=>$sidLen,'rows'=>$rows,'app_guard'=>$guard];
}
function sk1310_visible_headers(): array {
    $raw=[];try{$raw=headers_list();}catch(Throwable $e){}
    $map=[];foreach($raw as $h){$p=strpos($h,':');if($p===false)continue;$k=strtolower(trim(substr($h,0,$p)));$map[$k]=trim(substr($h,$p+1));}
    $want=[
      'x-content-type-options'=>'nosniff',
      'x-frame-options'=>'DENY or SAMEORIGIN',
      'referrer-policy'=>'a restrictive policy',
      'content-security-policy'=>'site-specific policy',
      'permissions-policy'=>'site-specific policy'
    ];
    $rows=[];foreach($want as $k=>$rec)$rows[]=['header'=>$k,'present'=>array_key_exists($k,$map),'value'=>$map[$k]??'','recommend'=>$rec];
    return $rows;
}
function sk1310_php_files(): array {
    $root=dirname(__DIR__);$dirs=[$root.'/admin',$root.'/auth'];$files=[];
    foreach($dirs as $dir){if(!is_dir($dir))continue;foreach(glob($dir.'/*.php')?:[] as $f){$files[$f]=true;if(count($files)>=180)break 2;}}
    foreach([$root.'/login.php',$root.'/logout.php',$root.'/forgot-password.php',$root.'/reset-password.php'] as $f)if(is_file($f)&&count($files)<180)$files[$f]=true;
    return array_keys($files);
}
function sk1310_static_scan(): array {
    $out=['files_checked'=>0,'files_skipped_large'=>0,'post_handlers'=>0,'csrf_gaps'=>[],'redirect_risks'=>[],'weak_hash_risks'=>[]];
    foreach(sk1310_php_files() as $file){
        $size=(int)@filesize($file);if($size>384000){$out['files_skipped_large']++;continue;}
        $txt=(string)@file_get_contents($file);if($txt==='')continue;$out['files_checked']++;
        $rel=ltrim(str_replace('\\','/',substr($file,strlen(dirname(__DIR__)))),'/');
        $hasPost=(bool)preg_match('/REQUEST_METHOD[^\n]{0,120}POST|method\s*=\s*["\']post["\']|method\s*:\s*["\']POST["\']/i',$txt);
        if($hasPost){$out['post_handlers']++;$hasVerify=(bool)preg_match('/\bcsrf_check\s*\(|\bverify_csrf\s*\(|\bcsrf_verify\s*\(|hash_equals\s*\([^;]{0,300}csrf/is',$txt);if(!$hasVerify&&count($out['csrf_gaps'])<60)$out['csrf_gaps'][]=['file'=>$rel,'message'=>'POST activity found but no obvious CSRF verification marker was detected.'];}
        if(count($out['redirect_risks'])<50){
            $risk=(bool)preg_match('/header\s*\([^;]{0,500}Location\s*:[^;]{0,500}\$_(?:GET|POST|REQUEST)/is',$txt)||(bool)preg_match('/\b(?:redirect|go|location_redirect)\s*\(\s*\$_(?:GET|POST|REQUEST)/i',$txt);
            if($risk)$out['redirect_risks'][]=['file'=>$rel,'message'=>'Possible user-controlled redirect target; verify allow-list/local-path validation.'];
        }
        if(count($out['weak_hash_risks'])<30){
            $weak=(bool)preg_match('/\b(?:md5|sha1)\s*\([^\)]{0,240}(?:password|passwd|pwd|pass)/is',$txt)||(bool)preg_match('/(?:password|passwd|pwd)\s*[^;]{0,160}\b(?:md5|sha1)\s*\(/is',$txt);
            if($weak)$out['weak_hash_risks'][]=['file'=>$rel,'message'=>'Possible MD5/SHA1 use near password handling; review manually.'];
        }
    }
    return $out;
}
function sk1310_auth_files(): array {
    $root=dirname(__DIR__);$candidates=['login.php','logout.php','forgot-password.php','reset-password.php','admin/login.php'];$out=[];foreach($candidates as $p)$out[]=['path'=>'/'.$p,'exists'=>is_file($root.'/'.$p)];return $out;
}
function sk1310_score(array $session,array $scan,array $headers): int {
    $score=100;
    foreach($session['rows'] as $x)if(!$x['ok'])$score-=($x['severity']==='high'?10:($x['severity']==='medium'?6:2));
    $score-=min(24,count($scan['csrf_gaps'])*4);$score-=min(18,count($scan['redirect_risks'])*6);$score-=min(20,count($scan['weak_hash_risks'])*10);
    foreach($headers as $h)if(!$h['present'])$score-=2;
    return max(0,min(100,$score));
}
function sk1310_findings(array $session,array $scan,array $headers): array {
    $f=[];foreach($session['rows'] as $x)if(!$x['ok'])$f[]=['severity'=>$x['severity'],'message'=>$x['key'].' is '.$x['value'].'; recommended: '.$x['recommend'].'.'];
    if($scan['csrf_gaps'])$f[]=['severity'=>'high','message'=>count($scan['csrf_gaps']).' admin/auth PHP file(s) have POST activity without an obvious static CSRF verification marker. Review false positives before changing code.'];
    if($scan['redirect_risks'])$f[]=['severity'=>'high','message'=>count($scan['redirect_risks']).' possible user-controlled redirect pattern(s) require manual allow-list/local-path review.'];
    if($scan['weak_hash_risks'])$f[]=['severity'=>'high','message'=>count($scan['weak_hash_risks']).' possible weak legacy password-hash pattern(s) require manual review.'];
    $missing=array_values(array_filter($headers,static fn($h)=>!$h['present']));if($missing)$f[]=['severity'=>'low','message'=>count($missing).' recommended security header(s) were not visible in PHP headers_list() at audit time. Web-server/CDN headers may not appear here, so verify externally before changing config.'];
    return $f;
}
function sk1310_save_run(array $r): int {
    if(!sk1310_table('security_integrity_runs_v1310'))return 0;
    try{$q=sk1310_db()->prepare('INSERT INTO security_integrity_runs_v1310(tenant_id,actor_user_id,score,high_count,medium_count,summary_json,created_at) VALUES(?,?,?,?,?,?,NOW())');$high=0;$med=0;foreach($r['findings'] as $x){if($x['severity']==='high')$high++;elseif($x['severity']==='medium')$med++;}$q->execute([sk1310_tid(),sk1310_actor_id(),(int)$r['score'],$high,$med,json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);return (int)sk1310_db()->lastInsertId();}catch(Throwable $e){return 0;}
}
function sk1310_fresh_audit(): array {
    $session=sk1310_session_policy();$headers=sk1310_visible_headers();$scan=sk1310_static_scan();$auth=sk1310_auth_files();$score=sk1310_score($session,$scan,$headers);$findings=sk1310_findings($session,$scan,$headers);
    $r=['version'=>'13.0.10.0','tenant_id'=>sk1310_tid(),'created_at'=>date('c'),'score'=>$score,'session'=>$session,'headers'=>$headers,'scan'=>$scan,'auth_files'=>$auth,'findings'=>$findings];$r['run_id']=sk1310_save_run($r);return $r;
}
function sk1310_last_audit(): ?array {
    if(!sk1310_table('security_integrity_runs_v1310'))return null;try{$q=sk1310_db()->prepare('SELECT summary_json FROM security_integrity_runs_v1310 WHERE tenant_id=? ORDER BY id DESC LIMIT 1');$q->execute([sk1310_tid()]);$j=$q->fetchColumn();if(!$j)return null;$r=json_decode((string)$j,true);return is_array($r)?$r:null;}catch(Throwable $e){return null;}
}
function sk1310_log_action(string $key,array $result): void {
    if(!sk1310_table('security_integrity_actions_v1310'))return;try{$q=sk1310_db()->prepare('INSERT INTO security_integrity_actions_v1310(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1310_tid(),sk1310_actor_id(),$key,json_encode($result,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);}catch(Throwable $e){}
}
function sk1310_safe_session_refresh(): array {
    $r=['rotated'=>false,'session_active'=>session_status()===PHP_SESSION_ACTIVE,'cookie_hardened'=>false,'errors'=>[]];
    if(!$r['session_active']){$r['errors'][]='No active PHP session is available to rotate.';sk1310_log_action('safe_session_refresh',$r);return $r;}
    try{
        if(is_file(__DIR__.'/session_security_v13151.php')) require_once __DIR__.'/session_security_v13151.php';
        if(function_exists('sk13151_apply_session_security')){$x=sk13151_apply_session_security(true);$r['rotated']=!empty($x['rotated']);$r['cookie_hardened']=!empty($x['cookie_reissued']);$r['errors']=array_merge($r['errors'],$x['errors']??[]);}
        else{$beforeLen=strlen((string)session_id());$ok=session_regenerate_id(true);$r['rotated']=(bool)$ok;$r['old_id_length']=$beforeLen;$r['new_id_length']=strlen((string)session_id());}
    }catch(Throwable $e){$r['errors'][]=$e->getMessage();}
    try{if(sk1310_table('settings')){$q=sk1310_db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('security_session_rotation_at',?),('session_security_hotfix_version','13.0.15.1') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$q->execute([date('Y-m-d H:i:s')]);}}catch(Throwable $e){}
    sk1310_log_action('safe_session_refresh',$r);return $r;
}
}
