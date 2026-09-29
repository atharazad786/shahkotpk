<?php
declare(strict_types=1);

/**
 * ShahkotPK v4.0.2 Activity Logs & IP Location History
 * Privacy boundary: this module performs coarse IP-based geolocation only.
 * It does not call the browser Geolocation API and does not silently collect GPS coordinates.
 */

function activity_tracking_enabled(): bool { return setting_bool('activity_tracking_enabled', true); }
function activity_request_logging_enabled(): bool { return activity_tracking_enabled() && setting_bool('activity_request_logging_enabled', true); }
function activity_location_enabled(): bool { return activity_tracking_enabled() && setting_bool('ip_location_tracking_enabled', true); }
function activity_store_raw_ip(): bool { return setting_bool('activity_store_raw_ip', true); }

function activity_client_ip(): string {
    $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    // Proxy headers are deliberately opt-in to prevent spoofed client IPs.
    if (setting_bool('activity_trust_cloudflare_ip', false)) {
        $cf = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
        if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) $ip = $cf;
    } elseif (setting_bool('activity_trust_forwarded_ip', false)) {
        $xff = trim((string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
        if ($xff !== '') {
            $candidate = trim(explode(',', $xff)[0] ?? '');
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_IP)) $ip = $candidate;
        }
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return '';
    return substr($ip, 0, 64);
}
function activity_ip_hash(string $ip): string { return $ip === '' ? '' : hash('sha256', $ip.'|'.(string)setting('activity_ip_hash_salt','shahkotpk-v402')); }
function activity_public_ip(string $ip): bool {
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) return false;
    return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}
function activity_user_agent(): string { return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,500); }
function activity_device_info(?string $ua=null): array {
    $ua = $ua ?? activity_user_agent();
    $l = strtolower($ua);
    $device = preg_match('/ipad|tablet|kindle|silk/i',$ua) ? 'Tablet' : (preg_match('/mobile|android|iphone|ipod/i',$ua) ? 'Mobile' : 'Desktop');
    if (strpos($l,'windows')!==false) $os='Windows'; elseif (strpos($l,'iphone')!==false||strpos($l,'ipad')!==false) $os='iOS'; elseif (strpos($l,'android')!==false) $os='Android'; elseif (strpos($l,'mac os')!==false||strpos($l,'macintosh')!==false) $os='macOS'; elseif (strpos($l,'linux')!==false) $os='Linux'; else $os='Other';
    if (strpos($l,'edg/')!==false) $browser='Edge'; elseif (strpos($l,'opr/')!==false||strpos($l,'opera')!==false) $browser='Opera'; elseif (strpos($l,'chrome/')!==false) $browser='Chrome'; elseif (strpos($l,'firefox/')!==false) $browser='Firefox'; elseif (strpos($l,'safari/')!==false) $browser='Safari'; else $browser='Other';
    return ['device_type'=>$device,'os'=>$os,'browser'=>$browser];
}
function activity_location_empty(string $provider='none'): array {
    return ['provider'=>$provider,'country_code'=>'','country'=>'','region'=>'','city'=>'','timezone'=>'','isp'=>'','asn'=>''];
}
function activity_geolocate_ip(string $ip,bool $force=false): array {
    if (!activity_location_enabled() || !activity_public_ip($ip)) return activity_location_empty('local/private');
    $hash = activity_ip_hash($ip);
    try {
        if (!$force) {
            $q=db()->prepare('SELECT * FROM ip_geolocation_cache WHERE ip_hash=? AND expires_at>NOW() LIMIT 1');$q->execute([$hash]);$r=$q->fetch();
            if($r) return ['provider'=>(string)$r['provider'],'country_code'=>(string)($r['country_code']??''),'country'=>(string)($r['country']??''),'region'=>(string)($r['region_name']??''),'city'=>(string)($r['city']??''),'timezone'=>(string)($r['timezone_name']??''),'isp'=>(string)($r['isp']??''),'asn'=>(string)($r['asn']??'')];
        }
    } catch(Throwable $e) {}
    $provider = strtolower(trim((string)setting('ip_location_provider','ipwhois')));
    if ($provider === 'disabled' || !function_exists('curl_init')) return activity_location_empty($provider ?: 'disabled');
    $loc=activity_location_empty($provider);
    if ($provider === 'ipwhois') {
        $url='https://ipwho.is/'.rawurlencode($ip).'?fields=success,country,country_code,region,city,timezone,connection';
        try {
            $ch=curl_init($url);
            curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>3,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['Accept: application/json','User-Agent: ShahkotPK/4.0.2']]);
            $body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
            if($body!==false && $code>=200 && $code<300){$d=json_decode((string)$body,true);if(is_array($d)&&!empty($d['success'])){$tz=$d['timezone']??[];$cn=$d['connection']??[];$loc=['provider'=>'ipwhois','country_code'=>substr((string)($d['country_code']??''),0,8),'country'=>substr((string)($d['country']??''),0,120),'region'=>substr((string)($d['region']??''),0,160),'city'=>substr((string)($d['city']??''),0,160),'timezone'=>substr((string)(is_array($tz)?($tz['id']??''):$tz),0,120),'isp'=>substr((string)(is_array($cn)?($cn['isp']??''):$cn),0,190),'asn'=>substr((string)(is_array($cn)?($cn['asn']??''):'') ,0,80)];}}
        } catch(Throwable $e) { runtime_log('IP location lookup failed',$e); }
    }
    try {
        $days=max(1,min(90,setting_int('ip_location_cache_days',30)));
        db()->prepare("INSERT INTO ip_geolocation_cache(ip_hash,ip_address,provider,country_code,country,region_name,city,timezone_name,isp,asn,looked_up_at,expires_at) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),DATE_ADD(NOW(),INTERVAL {$days} DAY)) ON DUPLICATE KEY UPDATE ip_address=VALUES(ip_address),provider=VALUES(provider),country_code=VALUES(country_code),country=VALUES(country),region_name=VALUES(region_name),city=VALUES(city),timezone_name=VALUES(timezone_name),isp=VALUES(isp),asn=VALUES(asn),looked_up_at=NOW(),expires_at=VALUES(expires_at)")->execute([$hash,activity_store_raw_ip()?$ip:null,$loc['provider'],$loc['country_code']?:null,$loc['country']?:null,$loc['region']?:null,$loc['city']?:null,$loc['timezone']?:null,$loc['isp']?:null,$loc['asn']?:null]);
    } catch(Throwable $e) {}
    return $loc;
}
function activity_module_from_route(string $route): string {
    $route=strtolower($route);
    $map=['/admin/'=>'admin','/shop'=>'commerce','/seller'=>'seller','/news'=>'news','/blog'=>'blog','/live'=>'live','/property'=>'property','/city-guide'=>'city-guide','/services'=>'services','/food'=>'food','/api/'=>'api','/login'=>'auth','/signup'=>'auth','/account'=>'account'];
    foreach($map as $needle=>$module) if(strpos($route,$needle)!==false)return $module; return 'website';
}
function activity_log_event(string $event,string $category='activity',string $entityType='',?int $entityId=null,array $meta=[],?int $userId=null,?string $source=null): void {
    if(!activity_tracking_enabled())return;
    $u=$userId; if($u===null){try{$cu=current_user();$u=$cu?(int)$cu['id']:null;}catch(Throwable $e){$u=null;}}
    // Guest browsing is intentionally not recorded by default; login/security events may still be logged.
    if($u===null && !in_array($category,['security','auth'],true) && !setting_bool('activity_guest_logging_enabled',false)) return;
    $ip=activity_client_ip();$hash=activity_ip_hash($ip);$loc=activity_geolocate_ip($ip);$dev=activity_device_info();$route=substr((string)($_SERVER['REQUEST_URI']??$_SERVER['PHP_SELF']??''),0,500);$method=substr((string)($_SERVER['REQUEST_METHOD']??'CLI'),0,12);$status=http_response_code();if($status<100)$status=200;
    $metaText='';try{$metaText=json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?:'';}catch(Throwable $e){}
    try{db()->prepare('INSERT INTO activity_logs(user_id,event_name,event_category,source,module,route,method,status_code,entity_type,entity_id,ip_address,ip_hash,country_code,country,region_name,city,timezone_name,isp,asn,device_type,browser_name,os_name,user_agent,referrer,duration_ms,meta_text) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$u,substr($event,0,100),substr($category,0,60),substr((string)($source??'web'),0,40),activity_module_from_route($route),$route,$method,$status,$entityType?:null,$entityId,activity_store_raw_ip()?$ip:null,$hash?:null,$loc['country_code']?:null,$loc['country']?:null,$loc['region']?:null,$loc['city']?:null,$loc['timezone']?:null,$loc['isp']?:null,$loc['asn']?:null,$dev['device_type'],$dev['browser'],$dev['os'],activity_user_agent(),substr((string)($_SERVER['HTTP_REFERER']??''),0,500),null,$metaText?:null]);}catch(Throwable $e){runtime_log('Activity event log failed',$e);}
    if($u!==null) activity_record_location_snapshot($u,$ip,$loc,$source??'web',$dev);
}
function activity_record_location_snapshot(int $userId,string $ip,array $loc,string $source='web',?array $dev=null): void {
    if($userId<=0||$ip==='')return;$dev=$dev??activity_device_info();$hash=activity_ip_hash($ip);$fingerprint=hash('sha256',$userId.'|'.$hash.'|'.strtolower((string)($loc['city']??'')).'|'.strtolower((string)($loc['country_code']??'')));
    try{db()->prepare('INSERT INTO user_location_history(user_id,location_fingerprint,ip_hash,ip_address,country_code,country,region_name,city,timezone_name,isp,asn,device_type,browser_name,os_name,source,first_seen_at,last_seen_at,visit_count) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),1) ON DUPLICATE KEY UPDATE ip_address=VALUES(ip_address),country_code=VALUES(country_code),country=VALUES(country),region_name=VALUES(region_name),city=VALUES(city),timezone_name=VALUES(timezone_name),isp=VALUES(isp),asn=VALUES(asn),device_type=VALUES(device_type),browser_name=VALUES(browser_name),os_name=VALUES(os_name),source=VALUES(source),last_seen_at=NOW(),visit_count=visit_count+1')->execute([$userId,$fingerprint,$hash,activity_store_raw_ip()?$ip:null,$loc['country_code']?:null,$loc['country']?:null,$loc['region']?:null,$loc['city']?:null,$loc['timezone']?:null,$loc['isp']?:null,$loc['asn']?:null,$dev['device_type'],$dev['browser'],$dev['os'],substr($source,0,40)]);}catch(Throwable $e){runtime_log('Location history snapshot failed',$e);}
}
function activity_register_request_logger(): void {
    if(!activity_request_logging_enabled() || PHP_SAPI==='cli')return;
    $u=null;try{$u=current_user();}catch(Throwable $e){}
    if(!$u && !setting_bool('activity_guest_logging_enabled',false))return;
    $start=microtime(true);$uid=$u?(int)$u['id']:null;$route=(string)($_SERVER['REQUEST_URI']??'');$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
    register_shutdown_function(function()use($start,$uid,$route,$method){
        try{
            $duration=(int)round((microtime(true)-$start)*1000);$status=http_response_code();if($status<100)$status=200;
            // GET request noise is throttled per user/session/route; POST/PUT/DELETE are always retained.
            $write=true;if(strtoupper($method)==='GET' && $uid){$key='activity_last_'.sha1($uid.'|'.$route);$last=(int)($_SESSION[$key]??0);$seconds=max(15,min(600,setting_int('activity_get_throttle_seconds',60)));if($last && time()-$last<$seconds)$write=false;else $_SESSION[$key]=time();}
            if(!$write)return;$ip=activity_client_ip();$loc=activity_geolocate_ip($ip);$dev=activity_device_info();$hash=activity_ip_hash($ip);$action='page.view';if(strtoupper($method)!=='GET')$action='request.'.strtolower($method);$postAction=isset($_POST['action'])?preg_replace('/[^a-z0-9_.-]/i','',substr((string)$_POST['action'],0,80)):'';if($postAction!=='')$action=$postAction;
            db()->prepare('INSERT INTO activity_logs(user_id,event_name,event_category,source,module,route,method,status_code,ip_address,ip_hash,country_code,country,region_name,city,timezone_name,isp,asn,device_type,browser_name,os_name,user_agent,referrer,duration_ms) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$uid,$action,'request','web',activity_module_from_route($route),substr($route,0,500),substr($method,0,12),$status,activity_store_raw_ip()?$ip:null,$hash?:null,$loc['country_code']?:null,$loc['country']?:null,$loc['region']?:null,$loc['city']?:null,$loc['timezone']?:null,$loc['isp']?:null,$loc['asn']?:null,$dev['device_type'],$dev['browser'],$dev['os'],activity_user_agent(),substr((string)($_SERVER['HTTP_REFERER']??''),0,500),$duration]);
            if($uid)activity_record_location_snapshot($uid,$ip,$loc,'request',$dev);
        }catch(Throwable $e){runtime_log('Request activity logger failed',$e);}
    });
}
function activity_cleanup(): array {
    $logDays=max(7,min(730,setting_int('activity_log_retention_days',180)));$locDays=max(7,min(730,setting_int('ip_location_retention_days',180)));$cacheDays=max(1,min(90,setting_int('ip_location_cache_days',30)));$a=$l=$c=0;
    try{$a=(int)db()->exec("DELETE FROM activity_logs WHERE created_at<DATE_SUB(NOW(),INTERVAL {$logDays} DAY)");}catch(Throwable $e){}
    try{$l=(int)db()->exec("DELETE FROM user_location_history WHERE last_seen_at<DATE_SUB(NOW(),INTERVAL {$locDays} DAY)");}catch(Throwable $e){}
    try{$c=(int)db()->exec("DELETE FROM ip_geolocation_cache WHERE expires_at<DATE_SUB(NOW(),INTERVAL {$cacheDays} DAY)");}catch(Throwable $e){}
    return ['activity'=>$a,'locations'=>$l,'cache'=>$c];
}
