<?php
declare(strict_types=1);

if (!function_exists('pr634_log')) {
function pr634_log(string $message, ?Throwable $e=null): void {
    try {
        if (function_exists('runtime_log')) { runtime_log($message, $e); return; }
    } catch (Throwable $ignore) {}
    error_log('[ShahkotPK Password Recovery] '.$message.($e?' :: '.$e->getMessage():''));
}
}

function pr634_setting(array $keys, string $default=''): string {
    foreach ($keys as $key) {
        try {
            if (function_exists('setting')) {
                $v=setting($key, null);
                if ($v!==null && $v!=='') return (string)$v;
            }
        } catch (Throwable $e) {}
        try {
            $q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');
            $q->execute([$key]);
            $v=$q->fetchColumn();
            if ($v!==false && $v!==null && $v!=='') return (string)$v;
        } catch (Throwable $e) {}
    }
    return $default;
}

function pr634_base_url(): string {
    $configured=rtrim(pr634_setting(['site_url','app_url','base_url','website_url']),'/');
    if ($configured && preg_match('~^https?://~i',$configured)) return $configured;
    $https=(!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS'])!=='off') || ((string)($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https');
    $host=preg_replace('/[^A-Za-z0-9.\-:\[\]]/','',(string)($_SERVER['HTTP_HOST']??'shahkotpk.com')) ?: 'shahkotpk.com';
    return ($https?'https':'http').'://'.$host;
}

function pr634_client_hash(): string {
    $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
    $ua=(string)($_SERVER['HTTP_USER_AGENT']??'');
    return hash('sha256',$ip.'|'.$ua.'|'.pr634_setting(['app_key','security_key','site_name'],'ShahkotPK'));
}

function pr634_users_columns(): array {
    static $cols=null;
    if (is_array($cols)) return $cols;
    $cols=[];
    try {
        foreach (db()->query('SHOW COLUMNS FROM users')->fetchAll() ?: [] as $r) {
            $name=(string)($r['Field']??'');
            if ($name!=='') $cols[$name]=$r;
        }
    } catch (Throwable $e) { pr634_log('Unable to inspect users table',$e); }
    return $cols;
}

function pr634_password_column(): ?string {
    $cols=pr634_users_columns();
    foreach (['password_hash','password','passwd','user_password','pass_hash'] as $c) if(isset($cols[$c])) return $c;
    return null;
}

function pr634_find_user_by_email(string $email): ?array {
    $email=mb_strtolower(trim($email));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) return null;
    try {
        $q=db()->prepare('SELECT id,name,email,status FROM users WHERE LOWER(email)=? LIMIT 1');
        $q->execute([$email]);
        $u=$q->fetch();
        return $u?:null;
    } catch (Throwable $e) { pr634_log('User lookup failed',$e); return null; }
}

function pr634_rate_allowed(string $email): bool {
    try {
        $q=db()->prepare("SELECT COUNT(*) FROM password_reset_tokens_v634 WHERE requested_at>=DATE_SUB(NOW(),INTERVAL 30 MINUTE) AND (email_hash=? OR requester_hash=?)");
        $q->execute([hash('sha256',mb_strtolower(trim($email))),pr634_client_hash()]);
        return (int)$q->fetchColumn() < 5;
    } catch (Throwable $e) { return true; }
}

function pr634_create_token(array $user, bool $adminContext=false): string {
    $plain=bin2hex(random_bytes(32));
    $hash=hash('sha256',$plain);
    $email=(string)$user['email'];
    $uid=(int)$user['id'];
    $expires=(new DateTimeImmutable('+60 minutes'))->format('Y-m-d H:i:s');
    try {
        db()->prepare('UPDATE password_reset_tokens_v634 SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')->execute([$uid]);
        $q=db()->prepare('INSERT INTO password_reset_tokens_v634(user_id,email_hash,token_hash,requester_hash,admin_context,expires_at,requested_at) VALUES(?,?,?,?,?,?,NOW())');
        $q->execute([$uid,hash('sha256',mb_strtolower(trim($email))),$hash,pr634_client_hash(),$adminContext?1:0,$expires]);
    } catch (Throwable $e) { pr634_log('Could not create reset token',$e); throw new RuntimeException('Password recovery is temporarily unavailable. Please try again shortly.'); }
    return $plain;
}

function pr634_get_token(string $plain): ?array {
    if (!preg_match('/^[a-f0-9]{64}$/i',$plain)) return null;
    try {
        $q=db()->prepare('SELECT r.*,u.name,u.email,u.status FROM password_reset_tokens_v634 r JOIN users u ON u.id=r.user_id WHERE r.token_hash=? AND r.used_at IS NULL AND r.expires_at>NOW() ORDER BY r.id DESC LIMIT 1');
        $q->execute([hash('sha256',$plain)]);
        $row=$q->fetch();
        return $row?:null;
    } catch (Throwable $e) { pr634_log('Reset token lookup failed',$e); return null; }
}

function pr634_mark_used(int $id, int $uid): void {
    db()->prepare('UPDATE password_reset_tokens_v634 SET used_at=NOW() WHERE id=?')->execute([$id]);
    db()->prepare('UPDATE password_reset_tokens_v634 SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')->execute([$uid]);
}

function pr634_update_password(int $userId, string $password): void {
    $col=pr634_password_column();
    if (!$col) throw new RuntimeException('The users table has no supported password column. Please contact the administrator.');
    $hash=password_hash($password,PASSWORD_DEFAULT);
    if (!$hash) throw new RuntimeException('Password hashing failed.');
    $sql='UPDATE users SET `'.str_replace('`','',$col).'`=? WHERE id=?';
    db()->prepare($sql)->execute([$hash,$userId]);
    // Best-effort invalidation of remember/session style columns when they exist.
    $cols=pr634_users_columns();
    foreach (['remember_token','reset_token','password_reset_token'] as $c) {
        if(isset($cols[$c])) { try{db()->prepare('UPDATE users SET `'.$c.'`=NULL WHERE id=?')->execute([$userId]);}catch(Throwable $e){} }
    }
}

function pr634_smtp_read($fp): array {
    $lines=[];$code=0;
    while(!feof($fp)){
        $line=fgets($fp,2048); if($line===false) break;
        $line=rtrim($line,"\r\n"); $lines[]=$line;
        if(preg_match('/^(\d{3})([ -])/',$line,$m)){ $code=(int)$m[1]; if($m[2]===' ') break; }
        if(count($lines)>100) break;
    }
    return [$code,$lines];
}
function pr634_smtp_expect($fp,array $ok,string $label): void {
    [$code]=pr634_smtp_read($fp);
    if(!in_array($code,$ok,true)) throw new RuntimeException($label.' failed (SMTP '.$code.').');
}
function pr634_smtp_cmd($fp,string $cmd,array $ok,string $label): void {
    if(fwrite($fp,$cmd."\r\n")===false) throw new RuntimeException('SMTP write failed.');
    pr634_smtp_expect($fp,$ok,$label);
}
function pr634_smtp_send(string $to,string $subject,string $html,string $text=''): void {
    $host=trim(pr634_setting(['smtp_host','mail_host']));
    $port=(int)pr634_setting(['smtp_port','mail_port'],'587');
    $user=trim(pr634_setting(['smtp_username','smtp_user','mail_username']));
    $pass=pr634_setting(['smtp_password','smtp_pass','mail_password']);
    $enc=strtolower(trim(pr634_setting(['smtp_encryption','smtp_secure','mail_encryption'],'tls')));
    $from=trim(pr634_setting(['smtp_from_email','mail_from_address','mail_from_email']));
    $fromName=trim(pr634_setting(['smtp_from_name','mail_from_name'],'ShahkotPK')) ?: 'ShahkotPK';
    if(!$from && filter_var($user,FILTER_VALIDATE_EMAIL)) $from=$user;
    if(!$host || !$port || !filter_var($from,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('SMTP is not fully configured.');
    $implicit=in_array($enc,['ssl','smtps','implicit_tls'],true)||$port===465;
    $starttls=in_array($enc,['tls','starttls'],true)||(!$implicit&&$port===587);
    $ctx=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false,'SNI_enabled'=>true,'peer_name'=>$host]]);
    $errno=0;$err='';$fp=@stream_socket_client(($implicit?'ssl':'tcp').'://'.$host.':'.$port,$errno,$err,12,STREAM_CLIENT_CONNECT,$ctx);
    if(!$fp) throw new RuntimeException('SMTP connection failed.');
    stream_set_timeout($fp,12);
    try {
        pr634_smtp_expect($fp,[220],'Greeting');
        $ehlo=preg_replace('/[^A-Za-z0-9.-]/','',(string)($_SERVER['SERVER_NAME']??'shahkotpk.com')) ?: 'shahkotpk.com';
        pr634_smtp_cmd($fp,'EHLO '.$ehlo,[250],'EHLO');
        if($starttls){
            pr634_smtp_cmd($fp,'STARTTLS',[220],'STARTTLS');
            if(@stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)!==true) throw new RuntimeException('SMTP TLS negotiation failed.');
            pr634_smtp_cmd($fp,'EHLO '.$ehlo,[250],'EHLO after TLS');
        }
        if($user!==''){
            pr634_smtp_cmd($fp,'AUTH LOGIN',[334],'AUTH');
            pr634_smtp_cmd($fp,base64_encode($user),[334],'Username');
            pr634_smtp_cmd($fp,base64_encode($pass),[235],'Password');
        }
        pr634_smtp_cmd($fp,'MAIL FROM:<'.$from.'>',[250],'MAIL FROM');
        pr634_smtp_cmd($fp,'RCPT TO:<'.$to.'>',[250,251],'RCPT TO');
        pr634_smtp_cmd($fp,'DATA',[354],'DATA');
        $boundary='=_ShahkotPK_'.bin2hex(random_bytes(8));
        $safeName=str_replace(["\r","\n"],' ',$fromName);
        $safeSubject=str_replace(["\r","\n"],' ',$subject);
        if($text==='') $text=trim(strip_tags(preg_replace('/<br\s*\/?>/i',"\n",$html)));
        $headers=['Date: '.date(DATE_RFC2822),'From: '.$safeName.' <'.$from.'>','To: <'.$to.'>','Subject: '.$safeSubject,'MIME-Version: 1.0','Content-Type: multipart/alternative; boundary="'.$boundary.'"'];
        $body=implode("\r\n",$headers)."\r\n\r\n";
        $body.='--'.$boundary."\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$text."\r\n";
        $body.='--'.$boundary."\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$html."\r\n";
        $body.='--'.$boundary."--\r\n";
        $body=preg_replace('/(?m)^\./','..',$body).".\r\n";
        if(fwrite($fp,$body)===false) throw new RuntimeException('SMTP message write failed.');
        pr634_smtp_expect($fp,[250],'Message acceptance');
        try{pr634_smtp_cmd($fp,'QUIT',[221],'QUIT');}catch(Throwable $e){}
        fclose($fp);
    } catch(Throwable $e){ if(is_resource($fp)) fclose($fp); throw $e; }
}

function pr634_send_reset(array $user,string $plain,bool $adminContext=false): void {
    $base=pr634_base_url();
    $url=$base.'/reset-password.php?token='.rawurlencode($plain).($adminContext?'&area=admin':'');
    $site=pr634_setting(['site_name','app_name'],'ShahkotPK');
    $name=trim((string)($user['name']??'')) ?: 'there';
    $subject=$site.' password reset';
    $html='<!doctype html><html><body style="margin:0;background:#f3f6fb;font-family:Arial,sans-serif;color:#0f172a"><div style="max-width:620px;margin:32px auto;background:#fff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden"><div style="padding:26px 30px;background:linear-gradient(135deg,#081c33,#0e7490);color:#fff"><div style="font-size:13px;letter-spacing:.12em;font-weight:700">'.htmlspecialchars($site).'</div><h2 style="margin:8px 0 0">Reset your password</h2></div><div style="padding:30px"><p>Hello '.htmlspecialchars($name).',</p><p>We received a request to reset the password for your account. This link is valid for <b>60 minutes</b> and can be used once.</p><p style="margin:28px 0"><a href="'.htmlspecialchars($url).'" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:13px 20px;border-radius:12px;font-weight:700">Reset Password</a></p><p style="font-size:13px;color:#64748b">If you did not request this, you can ignore this email. Your password will stay unchanged.</p></div></div></body></html>';
    $text="Hello {$name},\n\nReset your {$site} password using this link (valid 60 minutes):\n{$url}\n\nIf you did not request this, ignore this email.";
    pr634_smtp_send((string)$user['email'],$subject,$html,$text);
}

function pr634_request_reset(string $email,bool $adminContext=false): void {
    $email=trim($email);
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return;
    if(!pr634_rate_allowed($email)) return;
    $u=pr634_find_user_by_email($email);
    if(!$u) return; // Intentionally do not reveal account existence.
    try {
        $plain=pr634_create_token($u,$adminContext);
        pr634_send_reset($u,$plain,$adminContext);
    } catch(Throwable $e) {
        pr634_log('Password reset email could not be sent',$e);
        // Intentionally keep the public response generic and non-fatal.
    }
}


