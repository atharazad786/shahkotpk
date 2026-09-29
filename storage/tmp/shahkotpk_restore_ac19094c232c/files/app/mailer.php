<?php
declare(strict_types=1);

function smtp_read($fp,array $expected): string {
    $response='';
    while(($line=fgets($fp,515))!==false){
        $response.=$line;
        if(strlen($line)>=4 && $line[3]===' ') break;
    }

    $code=(int)substr($response,0,3);
    if(!in_array($code,$expected,true)){
        throw new RuntimeException('SMTP server error '.$code.': '.trim($response));
    }
    return $response;
}

function smtp_command($fp,string $command,array $expected): string {
    if(fwrite($fp,$command."\r\n")===false) throw new RuntimeException('Unable to write to SMTP connection.');
    return smtp_read($fp,$expected);
}

function smtp_settings_ready(): bool {
    return feature_enabled('smtp_enabled',false)
        && trim((string)setting('smtp_host',''))!==''
        && setting_int('smtp_port',587)>0
        && filter_var((string)setting('smtp_from_email',setting('support_email','')),FILTER_VALIDATE_EMAIL);
}

function smtp_send_mail(string $to,string $subject,string $html,string $text=''): void {
    if(!filter_var($to,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Invalid recipient email address.');
    if(!smtp_settings_ready()) throw new RuntimeException('SMTP is not configured or enabled in Admin Settings.');

    $host=trim((string)setting('smtp_host',''));
    $port=setting_int('smtp_port',587);
    $security=strtolower(trim((string)setting('smtp_encryption','tls')));
    $username=(string)setting('smtp_username','');
    $password=(string)setting('smtp_password','');
    $fromEmail=(string)setting('smtp_from_email',setting('support_email',''));
    $fromName=trim((string)setting('smtp_from_name',setting('site_name','ShahkotPK')));
    $timeout=max(5,min(60,setting_int('smtp_timeout',15)));

    $transport=$security==='ssl'?'ssl://':'tcp://';
    $errno=0;$errstr='';
    $fp=@stream_socket_client($transport.$host.':'.$port,$errno,$errstr,$timeout,STREAM_CLIENT_CONNECT);

    if(!$fp) throw new RuntimeException('SMTP connection failed: '.$errstr.' ('.$errno.')');
    stream_set_timeout($fp,$timeout);

    try{
        smtp_read($fp,[220]);

        $ehlo=$_SERVER['SERVER_NAME']??'localhost';
        smtp_command($fp,'EHLO '.$ehlo,[250]);

        if($security==='tls'){
            smtp_command($fp,'STARTTLS',[220]);
            if(!stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)){
                throw new RuntimeException('Unable to enable SMTP TLS encryption.');
            }
            smtp_command($fp,'EHLO '.$ehlo,[250]);
        }

        if($username!==''){
            smtp_command($fp,'AUTH LOGIN',[334]);
            smtp_command($fp,base64_encode($username),[334]);
            smtp_command($fp,base64_encode($password),[235]);
        }

        smtp_command($fp,'MAIL FROM:<'.$fromEmail.'>',[250]);
        smtp_command($fp,'RCPT TO:<'.$to.'>',[250,251]);
        smtp_command($fp,'DATA',[354]);

        $safeFromName=str_replace(["\r","\n"],'',$fromName);
        $safeSubject=str_replace(["\r","\n"],'',$subject);
        $messageId='<'.bin2hex(random_bytes(10)).'@'.($ehlo?:'localhost').'>';

        $headers=[
            'Date: '.date(DATE_RFC2822),
            'From: '.$safeFromName.' <'.$fromEmail.'>',
            'To: <'.$to.'>',
            'Subject: '.$safeSubject,
            'Message-ID: '.$messageId,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $body=implode("\r\n",$headers)."\r\n\r\n".str_replace(["\r\n","\r"],"\n",$html);
        $body=str_replace("\n","\r\n",$body);
        $body=preg_replace('/^\./m','..',$body);

        if(fwrite($fp,$body."\r\n.\r\n")===false) throw new RuntimeException('Unable to send SMTP message data.');
        smtp_read($fp,[250]);
        smtp_command($fp,'QUIT',[221]);

    }finally{
        if(is_resource($fp)) fclose($fp);
    }
}
