<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
$me=require_permission('settings.manage');

function smtp631_setting(array $keys, string $default=''): string {
    // ShahkotPK's canonical settings API is setting(), backed by
    // settings(setting_key, setting_value). Keep legacy fallbacks so the
    // diagnostic page can also survive older/custom installations.
    foreach($keys as $key){
        try{
            if(function_exists('setting')){
                $v=setting($key, null);
                if($v!==null && $v!=='') return (string)$v;
            }
        }catch(Throwable $e){}
        try{
            if(function_exists('get_setting')){
                $v=get_setting($key, null);
                if($v!==null && $v!=='') return (string)$v;
            }
        }catch(Throwable $e){}
        try{
            $q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');
            $q->execute([$key]);
            $v=$q->fetchColumn();
            if($v!==false && $v!==null && $v!=='') return (string)$v;
        }catch(Throwable $e){}
        try{
            $q=db()->prepare('SELECT `value` FROM settings WHERE `key`=? LIMIT 1');
            $q->execute([$key]);
            $v=$q->fetchColumn();
            if($v!==false && $v!==null && $v!=='') return (string)$v;
        }catch(Throwable $e){}
    }
    return $default;
}
function smtp631_valid_email(string $email): bool {return (bool)filter_var($email,FILTER_VALIDATE_EMAIL);}
function smtp631_read($fp): array {
    $lines=[];$code=0;
    while(!feof($fp)){
        $line=fgets($fp,2048);if($line===false)break;
        $line=rtrim($line,"\r\n");$lines[]=$line;
        if(preg_match('/^(\d{3})([ -])/', $line,$m)){
            $code=(int)$m[1];
            if($m[2]===' ')break;
        }
        if(count($lines)>100)break;
    }
    return [$code,$lines];
}
function smtp631_expect($fp,array $ok,string $label,array &$log): array {
    [$code,$lines]=smtp631_read($fp);
    foreach($lines as $line)$log[]=['server',$line];
    if(!in_array($code,$ok,true)) throw new RuntimeException($label.' failed. SMTP response '.$code.'.');
    return [$code,$lines];
}
function smtp631_cmd($fp,string $cmd,array $ok,string $label,array &$log,bool $secret=false): array {
    $log[]=['client',$secret?preg_replace('/\s+.*/',' ******',$cmd):$cmd];
    if(fwrite($fp,$cmd."\r\n")===false) throw new RuntimeException('Could not write to SMTP server.');
    return smtp631_expect($fp,$ok,$label,$log);
}
function smtp631_send(array $cfg,string $to): array {
    $log=[];$host=trim($cfg['host']);$port=(int)$cfg['port'];$enc=strtolower(trim($cfg['encryption']));
    if(!$host)throw new RuntimeException('SMTP host is not configured.');
    if($port<1||$port>65535)throw new RuntimeException('SMTP port is invalid.');
    if(!smtp631_valid_email($to))throw new RuntimeException('Enter a valid recipient email address.');
    $implicit=in_array($enc,['ssl','smtps','implicit_tls'],true)||$port===465;
    $starttls=in_array($enc,['tls','starttls'],true)||(!$implicit&&$port===587);
    $transport=($implicit?'ssl':'tcp').'://'.$host.':'.$port;
    $ctx=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false,'SNI_enabled'=>true,'peer_name'=>$host]]);
    $errno=0;$errstr='';$started=microtime(true);
    $fp=@stream_socket_client($transport,$errno,$errstr,12,STREAM_CLIENT_CONNECT,$ctx);
    if(!$fp)throw new RuntimeException('Connection failed: '.($errstr?:('error '.$errno)).'.');
    stream_set_timeout($fp,12);
    try{
        smtp631_expect($fp,[220],'Server greeting',$log);
        $ehloHost=preg_replace('/[^A-Za-z0-9.-]/','',($_SERVER['SERVER_NAME']??'shahkotpk.local'))?:'shahkotpk.local';
        [, $hello]=smtp631_cmd($fp,'EHLO '.$ehloHost,[250],'EHLO',$log);
        if($starttls){
            $caps=strtoupper(implode("\n",$hello));
            if(!str_contains($caps,'STARTTLS'))throw new RuntimeException('Server does not advertise STARTTLS on this port.');
            smtp631_cmd($fp,'STARTTLS',[220],'STARTTLS',$log);
            $crypto=@stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if($crypto!==true)throw new RuntimeException('TLS negotiation failed. Check encryption mode, certificate and SMTP hostname.');
            smtp631_cmd($fp,'EHLO '.$ehloHost,[250],'EHLO after TLS',$log);
        }
        if(trim($cfg['username'])!==''){
            smtp631_cmd($fp,'AUTH LOGIN',[334],'SMTP AUTH',$log);
            smtp631_cmd($fp,base64_encode($cfg['username']),[334],'SMTP username',$log,true);
            smtp631_cmd($fp,base64_encode($cfg['password']),[235],'SMTP password',$log,true);
        }
        $from=smtp631_valid_email($cfg['from_email'])?$cfg['from_email']:$cfg['username'];
        if(!smtp631_valid_email($from))throw new RuntimeException('SMTP From Email is not configured or invalid.');
        smtp631_cmd($fp,'MAIL FROM:<'.$from.'>',[250],'MAIL FROM',$log);
        smtp631_cmd($fp,'RCPT TO:<'.$to.'>',[250,251],'RCPT TO',$log);
        smtp631_cmd($fp,'DATA',[354],'DATA',$log);
        $subject='ShahkotPK SMTP Test — '.date('d M Y H:i:s');
        $name=trim($cfg['from_name'])?:'ShahkotPK';
        $safeName=str_replace(["\r","\n"],' ',$name);
        $boundary='=_ShahkotPK_'.bin2hex(random_bytes(8));
        $text="ShahkotPK SMTP test completed successfully.\r\n\r\nHost: {$host}:{$port}\r\nEncryption: ".($implicit?'SSL/TLS':($starttls?'STARTTLS':'None'))."\r\nTime: ".date('c')."\r\n";
        $html='<!doctype html><html><body style="font-family:Arial,sans-serif;background:#f4f7fb;padding:28px"><div style="max-width:620px;margin:auto;background:#fff;border-radius:18px;padding:30px;border:1px solid #e6eaf0"><h2 style="margin-top:0;color:#0f172a">SMTP test successful ✓</h2><p style="color:#475569">Your ShahkotPK server connected to the configured SMTP service, authenticated and accepted this test message.</p><table style="border-collapse:collapse;width:100%;color:#334155"><tr><td style="padding:8px 0"><b>Host</b></td><td>'.htmlspecialchars($host).':'.$port.'</td></tr><tr><td style="padding:8px 0"><b>Encryption</b></td><td>'.($implicit?'SSL/TLS':($starttls?'STARTTLS':'None')).'</td></tr><tr><td style="padding:8px 0"><b>Sent</b></td><td>'.htmlspecialchars(date('d M Y h:i A')).'</td></tr></table></div></body></html>';
        $headers=[
            'Date: '.date(DATE_RFC2822),
            'From: '.$safeName.' <'.$from.'>',
            'To: <'.$to.'>',
            'Subject: '.$subject,
            'Message-ID: <'.bin2hex(random_bytes(12)).'@'.$ehloHost.'>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="'.$boundary.'"'
        ];
        $body=implode("\r\n",$headers)."\r\n\r\n";
        $body.='--'.$boundary."\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$text."\r\n";
        $body.='--'.$boundary."\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$html."\r\n";
        $body.='--'.$boundary."--\r\n";
        $body=preg_replace('/(?m)^\./','..',$body).".\r\n";
        $log[]=['client','[message body omitted]'];
        if(fwrite($fp,$body)===false)throw new RuntimeException('Could not send message body.');
        smtp631_expect($fp,[250],'Message acceptance',$log);
        try{smtp631_cmd($fp,'QUIT',[221],'QUIT',$log);}catch(Throwable $e){}
        fclose($fp);
        return ['ok'=>true,'elapsed'=>round((microtime(true)-$started)*1000),'log'=>$log,'mode'=>$implicit?'SSL/TLS':($starttls?'STARTTLS':'Plain SMTP')];
    }catch(Throwable $e){
        if(is_resource($fp))fclose($fp);
        throw $e;
    }
}

$cfg=[
 'enabled'=>smtp631_setting(['smtp_enabled','mail_enabled'],'0'),
 'host'=>smtp631_setting(['smtp_host','mail_host','mailer_host']),
 'port'=>smtp631_setting(['smtp_port','mail_port','mailer_port'],'587'),
 'username'=>smtp631_setting(['smtp_username','smtp_user','mail_username','mail_user']),
 'password'=>smtp631_setting(['smtp_password','smtp_pass','mail_password','mail_pass']),
 'encryption'=>smtp631_setting(['smtp_encryption','smtp_secure','mail_encryption'],'tls'),
 'from_email'=>smtp631_setting(['smtp_from_email','mail_from_address','mail_from_email','from_email']),
 'from_name'=>smtp631_setting(['smtp_from_name','mail_from_name','from_name'],'ShahkotPK'),
];
if(!$cfg['from_email'] && smtp631_valid_email($cfg['username']))$cfg['from_email']=$cfg['username'];
$result=null;$error='';$to='';
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    try{
        csrf_check();
        $to=trim((string)($_POST['to']??''));
        $override=!empty($_POST['use_override']);
        if($override){
            $cfg['host']=trim((string)($_POST['host']??$cfg['host']));
            $cfg['port']=(string)(int)($_POST['port']??$cfg['port']);
            $cfg['username']=trim((string)($_POST['username']??$cfg['username']));
            if(isset($_POST['password'])&&$_POST['password']!=='')$cfg['password']=(string)$_POST['password'];
            $cfg['encryption']=trim((string)($_POST['encryption']??$cfg['encryption']));
            $cfg['from_email']=trim((string)($_POST['from_email']??$cfg['from_email']));
            $cfg['from_name']=trim((string)($_POST['from_name']??$cfg['from_name']));
        }
        $result=smtp631_send($cfg,$to);
    }catch(Throwable $e){$error=$e->getMessage();}
}
$openssl=extension_loaded('openssl');$sockets=function_exists('stream_socket_client');
page_start('SMTP Test Center',true);
?>
<link rel="stylesheet" href="/assets/smtp-test-6.3.1.css?v=632">
<div class="smtp631-wrap">
  <section class="smtp631-hero">
    <div><span class="smtp631-kicker">MAIL DELIVERY DIAGNOSTICS</span><h2>SMTP Test Center</h2><p>Check the exact SMTP connection used by ShahkotPK without exposing the saved password.</p></div>
    <div class="smtp631-state <?=($openssl&&$sockets)?'good':'bad'?>"><b><?=($openssl&&$sockets)?'Runtime Ready':'Runtime Issue'?></b><small>OpenSSL <?= $openssl?'✓':'✕' ?> · Socket <?= $sockets?'✓':'✕' ?></small></div>
  </section>
  <?php if($result):?><div class="smtp631-alert ok"><b>Test email accepted by SMTP server ✓</b><span><?=e((string)$result['mode'])?> · <?=e((string)$result['elapsed'])?> ms</span></div><?php endif;?>
  <?php if($error):?><div class="smtp631-alert err"><b>SMTP test failed</b><span><?=e($error)?></span></div><?php endif;?>
  <div class="smtp631-grid">
    <section class="smtp631-card">
      <div class="smtp631-head"><div><h3>Configured SMTP</h3><p>Values are read from the same Platform Settings source used by ShahkotPK. Temporary override below is not saved.</p></div><a class="btn" href="/admin/settings.php">Open Settings</a></div>
      <div class="smtp631-config">
        <div><span>SMTP status</span><b><?=in_array(strtolower((string)$cfg['enabled']),['1','true','yes','on'],true)?'Enabled':'Disabled'?></b></div>
        <div><span>Host</span><b><?=e($cfg['host']?:'Not configured')?></b></div>
        <div><span>Port</span><b><?=e((string)$cfg['port'])?></b></div>
        <div><span>Encryption</span><b><?=e(strtoupper($cfg['encryption']?:'none'))?></b></div>
        <div><span>Username</span><b><?=e($cfg['username']?:'Not configured')?></b></div>
        <div><span>From email</span><b><?=e($cfg['from_email']?:'Not configured')?></b></div>
        <div><span>Password</span><b><?=$cfg['password']!==''?'••••••••':'Not configured'?></b></div>
      </div>
      <form method="post" class="smtp631-form">
        <?=csrf_field()?>
        <label>Send test email to<input type="email" name="to" required value="<?=e($to)?>" placeholder="you@example.com"></label>
        <label class="smtp631-check"><input type="checkbox" name="use_override" value="1" id="smtp631Override"> Use temporary connection values for this test</label>
        <div class="smtp631-override" id="smtp631OverrideFields">
          <label>SMTP Host<input name="host" value="<?=e($cfg['host'])?>" autocomplete="off"></label>
          <div class="smtp631-two"><label>Port<input type="number" min="1" max="65535" name="port" value="<?=e((string)$cfg['port'])?>"></label><label>Encryption<select name="encryption"><option value="tls" <?=in_array(strtolower($cfg['encryption']),['tls','starttls'],true)?'selected':''?>>STARTTLS</option><option value="ssl" <?=in_array(strtolower($cfg['encryption']),['ssl','smtps','implicit_tls'],true)?'selected':''?>>SSL/TLS</option><option value="none" <?=in_array(strtolower($cfg['encryption']),['','none','plain'],true)?'selected':''?>>None</option></select></label></div>
          <label>Username<input name="username" value="<?=e($cfg['username'])?>" autocomplete="username"></label>
          <label>Password<input type="password" name="password" value="" placeholder="Leave blank to use saved password" autocomplete="new-password"></label>
          <div class="smtp631-two"><label>From Email<input type="email" name="from_email" value="<?=e($cfg['from_email'])?>"></label><label>From Name<input name="from_name" value="<?=e($cfg['from_name'])?>"></label></div>
        </div>
        <button class="smtp631-send" type="submit">✉ Run SMTP Test</button>
      </form>
    </section>
    <aside class="smtp631-card smtp631-side">
      <h3>Connection checklist</h3>
      <div class="smtp631-checks"><div class="<?= $cfg['host']?'yes':'no' ?>">SMTP host <b><?= $cfg['host']?'Ready':'Missing' ?></b></div><div class="<?= ((int)$cfg['port']>0)?'yes':'no' ?>">SMTP port <b><?=e((string)$cfg['port'])?></b></div><div class="<?= $cfg['from_email']?'yes':'no' ?>">From address <b><?= $cfg['from_email']?'Ready':'Missing' ?></b></div><div class="<?= $openssl?'yes':'no' ?>">OpenSSL <b><?= $openssl?'Loaded':'Missing' ?></b></div><div class="<?= $sockets?'yes':'no' ?>">Socket client <b><?= $sockets?'Available':'Missing' ?></b></div></div>
      <div class="smtp631-tip"><b>Recommended</b><p>Port 587 → STARTTLS<br>Port 465 → SSL/TLS</p></div>
      <div class="smtp631-tip"><b>Security</b><p>Saved SMTP password is never printed into the page or diagnostic log.</p></div>
    </aside>
  </div>
  <?php if($result && !empty($result['log'])):?><details class="smtp631-log"><summary>Show SMTP conversation</summary><pre><?php foreach($result['log'] as $row){echo e(($row[0]==='client'?'C: ':'S: ').$row[1])."\n";}?></pre></details><?php endif;?>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){const c=document.getElementById('smtp631Override'),f=document.getElementById('smtp631OverrideFields');function s(){if(f)f.classList.toggle('show',!!(c&&c.checked));}if(c)c.addEventListener('change',s);s();});</script>
<?php page_end();
