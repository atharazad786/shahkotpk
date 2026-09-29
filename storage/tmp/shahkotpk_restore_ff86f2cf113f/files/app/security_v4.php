<?php
declare(strict_types=1);
require_once __DIR__.'/mailer.php';

function security_v4_enabled(): bool { return setting_bool('security_suite_enabled',true); }
function security_client_ip(): string {
    if(function_exists('activity_client_ip')) return activity_client_ip();
    $ip=(string)($_SERVER['REMOTE_ADDR']??'');
    if(strlen($ip)>64)$ip=substr($ip,0,64);
    return filter_var($ip,FILTER_VALIDATE_IP)?$ip:'';
}
function security_user_agent(): string { return substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,500); }
function security_role_requires_2fa(array $u): bool {
    if(!security_v4_enabled()||!setting_bool('security_2fa_enabled',false))return false;
    $roles=array_filter(array_map('trim',explode(',',strtolower((string)setting('security_2fa_roles','admin,editor,shopkeeper')))));
    return in_array(strtolower((string)($u['role']??'')),$roles,true);
}
function security_ip_blocked(?string $ip=null): bool {
    if(!security_v4_enabled())return false;$ip=$ip===null?security_client_ip():$ip;if($ip==='')return false;
    try{$q=db()->prepare("SELECT id FROM security_ip_blocks WHERE ip_address=? AND (expires_at IS NULL OR expires_at>NOW()) LIMIT 1");$q->execute([$ip]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function security_assert_login_allowed(string $identifier=''): void {
    if(!security_v4_enabled())return;$ip=security_client_ip();if(security_ip_blocked($ip))throw new RuntimeException('Login from this network is temporarily blocked.');
    $mins=max(1,min(120,setting_int('security_login_window_minutes',15)));$max=max(3,min(50,setting_int('security_login_max_failures',8)));
    try{$q=db()->prepare("SELECT COUNT(*) FROM security_login_events WHERE success=0 AND ip_address=? AND created_at>=DATE_SUB(NOW(),INTERVAL {$mins} MINUTE)");$q->execute([$ip]);if((int)$q->fetchColumn()>=$max)throw new RuntimeException('Too many failed login attempts. Please wait and try again.');}catch(RuntimeException $e){throw $e;}catch(Throwable $e){}
}
function security_record_login(bool $success,string $identifier='',?int $userId=null,string $source='web',string $reason=''): void {
    if(!security_v4_enabled())return;try{db()->prepare('INSERT INTO security_login_events(user_id,login_identifier,source,ip_address,user_agent,success,failure_reason) VALUES(?,?,?,?,?,?,?)')->execute([$userId,$identifier?:null,substr($source,0,40),security_client_ip(),security_user_agent(),$success?1:0,$reason?:null]);}catch(Throwable $e){}
    if(function_exists('activity_log_event')) activity_log_event($success?'login.success':'login.failed','security','user',$userId,$reason?['reason'=>$reason]:[],$userId,$source);
}
function security_issue_otp(array $u,string $purpose='login'): void {
    if(empty($u['id'])||empty($u['email'])||!filter_var($u['email'],FILTER_VALIDATE_EMAIL))throw new RuntimeException('Two-factor verification requires a valid email on this account.');
    if(!smtp_settings_ready())throw new RuntimeException('Two-factor authentication is enabled but SMTP email is not configured. Ask the administrator to configure SMTP.');
    $code=(string)random_int(100000,999999);$mins=max(3,min(30,setting_int('security_otp_minutes',10)));
    db()->prepare("UPDATE security_otp_codes SET consumed_at=NOW() WHERE user_id=? AND purpose=? AND consumed_at IS NULL")->execute([(int)$u['id'],$purpose]);
    db()->prepare("INSERT INTO security_otp_codes(user_id,purpose,code_hash,expires_at) VALUES(?,?,?,DATE_ADD(NOW(),INTERVAL {$mins} MINUTE))")->execute([(int)$u['id'],$purpose,password_hash($code,PASSWORD_DEFAULT)]);
    $site=e((string)setting('site_name','ShahkotPK'));$html='<h2>'.$site.' security code</h2><p>Your verification code is:</p><p style="font-size:30px;font-weight:800;letter-spacing:6px">'.e($code).'</p><p>This code expires in '.$mins.' minutes. If you did not request it, ignore this email.</p>';
    smtp_send_mail((string)$u['email'],'Your '.$site.' verification code',$html);
}
function security_verify_otp(int $userId,string $code,string $purpose='login'): bool {
    if(!preg_match('/^\\d{6}$/',$code))return false;try{$q=db()->prepare("SELECT * FROM security_otp_codes WHERE user_id=? AND purpose=? AND consumed_at IS NULL AND expires_at>NOW() ORDER BY id DESC LIMIT 1");$q->execute([$userId,$purpose]);$r=$q->fetch();if(!$r)return false;if((int)$r['attempts']>=6)return false;db()->prepare('UPDATE security_otp_codes SET attempts=attempts+1 WHERE id=?')->execute([$r['id']]);if(!password_verify($code,$r['code_hash']))return false;db()->prepare('UPDATE security_otp_codes SET consumed_at=NOW() WHERE id=?')->execute([$r['id']]);return true;}catch(Throwable $e){return false;}
}
function security_register_session(int $userId,string $device='Browser'): void {
    if(!setting_bool('security_session_tracking_enabled',true)||session_status()!==PHP_SESSION_ACTIVE)return;$sid=session_id();if($sid==='')return;$hash=hash('sha256',$sid);try{db()->prepare("INSERT INTO security_sessions(user_id,session_hash,device_name,ip_address,user_agent,last_seen_at) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE last_seen_at=NOW(),ip_address=VALUES(ip_address),user_agent=VALUES(user_agent)")->execute([$userId,$hash,substr($device,0,160),security_client_ip(),security_user_agent()]);}catch(Throwable $e){}
}
function security_finalize_login(array $u,string $redirect,string $source='web'): void {
    if(feature_enabled('session_regenerate_on_login',true))session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];unset($_SESSION['security_pending_2fa']);security_register_session((int)$u['id'],ucfirst($source));security_record_login(true,(string)($u['email']??$u['phone']??''),(int)$u['id'],$source);header('Location: '.$redirect);exit;
}
function security_login_success(array $u,string $redirect,string $source='web'): void {
    if(security_role_requires_2fa($u)){
        security_issue_otp($u,'login');$_SESSION['security_pending_2fa']=['user_id'=>(int)$u['id'],'redirect'=>$redirect,'source'=>$source,'created_at'=>time()];header('Location: /two-factor.php');exit;
    }
    security_finalize_login($u,$redirect,$source);
}
function security_enforce_current_session(): void {
    if(!security_v4_enabled()||!setting_bool('security_session_tracking_enabled',true)||empty($_SESSION['user_id'])||session_status()!==PHP_SESSION_ACTIVE)return;$hash=hash('sha256',session_id());$uid=(int)$_SESSION['user_id'];try{$q=db()->prepare('SELECT revoked_at FROM security_sessions WHERE session_hash=? AND user_id=? LIMIT 1');$q->execute([$hash,$uid]);$r=$q->fetch();if(!$r){security_register_session($uid);return;}if(!empty($r['revoked_at'])){unset($_SESSION['user_id']);session_regenerate_id(true);return;}db()->prepare('UPDATE security_sessions SET last_seen_at=NOW() WHERE session_hash=?')->execute([$hash]);}catch(Throwable $e){}
}
function security_apply_headers(): void {
    if(!security_v4_enabled()||!setting_bool('security_headers_enabled',true)||headers_sent())return;header('X-Content-Type-Options: nosniff');header('X-Frame-Options: SAMEORIGIN');header('Referrer-Policy: strict-origin-when-cross-origin');header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
    if(setting_bool('security_csp_report_only',true))header("Content-Security-Policy-Report-Only: default-src 'self' https: data: blob:; img-src 'self' https: data: blob:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https:; frame-src 'self' https:; connect-src 'self' https:; media-src 'self' https: blob:");
}
function security_health_snapshot_v4(): array {
    $dbStatus='ok';try{db()->query('SELECT 1')->fetchColumn();}catch(Throwable $e){$dbStatus='failed';}$free=@disk_free_space(__DIR__.'/../storage');$pending=0;$failed=0;try{$pending=(int)db()->query("SELECT COUNT(*) FROM job_queue WHERE status='pending'")->fetchColumn();$failed=(int)db()->query("SELECT COUNT(*) FROM job_queue WHERE status='failed'")->fetchColumn();}catch(Throwable $e){}$log=__DIR__.'/../storage/logs/runtime-errors.log';$kb=is_file($log)?(int)ceil(filesize($log)/1024):0;$status=($dbStatus==='ok'&&$failed<10)?'ok':'warning';$meta=['sapi'=>PHP_SAPI,'memory_limit'=>ini_get('memory_limit'),'upload_max_filesize'=>ini_get('upload_max_filesize'),'extensions'=>['pdo_mysql'=>extension_loaded('pdo_mysql'),'curl'=>extension_loaded('curl'),'openssl'=>extension_loaded('openssl'),'mbstring'=>extension_loaded('mbstring')]];try{db()->prepare('INSERT INTO security_health_snapshots(status,php_version,db_status,disk_free_mb,queue_pending,queue_failed,runtime_log_kb,meta_text) VALUES(?,?,?,?,?,?,?,?)')->execute([$status,PHP_VERSION,$dbStatus,$free===false?null:(int)floor($free/1048576),$pending,$failed,$kb,json_encode($meta)]);}catch(Throwable $e){}return ['status'=>$status,'db'=>$dbStatus,'disk_free_mb'=>$free===false?0:(int)floor($free/1048576),'queue_pending'=>$pending,'queue_failed'=>$failed,'runtime_log_kb'=>$kb,'php'=>PHP_VERSION];
}
function security_backup_due(): bool {
    if(!setting_bool('security_backup_scheduler_enabled',true))return false;$hour=max(0,min(23,setting_int('security_backup_hour',3)));if((int)date('G')!==$hour)return false;try{$last=db()->query("SELECT MAX(completed_at) FROM backup_records WHERE status='completed' AND backup_type='database'")->fetchColumn();return !$last||strtotime((string)$last)<strtotime('-20 hours');}catch(Throwable $e){return false;}
}
function security_prune_backups(): void {
    $days=max(3,min(365,setting_int('security_backup_retention_days',14)));try{$q=db()->query("SELECT id,file_name FROM backup_records WHERE status='completed' AND completed_at<DATE_SUB(NOW(),INTERVAL {$days} DAY)");foreach($q->fetchAll() as $r){$path=__DIR__.'/../storage/backups/'.basename((string)$r['file_name']);if(is_file($path))@unlink($path);db()->prepare('DELETE FROM backup_records WHERE id=?')->execute([$r['id']]);}}catch(Throwable $e){}
}
