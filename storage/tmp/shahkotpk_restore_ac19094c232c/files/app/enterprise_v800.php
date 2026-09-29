<?php
declare(strict_types=1);

if (is_file(__DIR__.'/ai_orchestrator_v710.php')) require_once __DIR__.'/ai_orchestrator_v710.php';

function v800_tid(): int { if(isset($GLOBALS['V800_TENANT_OVERRIDE'])) return (int)$GLOBALS['V800_TENANT_OVERRIDE']; return function_exists('tenant_id') ? (int)tenant_id() : 0; }
function v800_user(): ?array { return function_exists('current_user') ? current_user() : null; }
function v800_table(string $table): bool {
    static $cache=[];
    if(isset($cache[$table])) return $cache[$table];
    if(!preg_match('/^[A-Za-z0-9_]+$/',$table)) return false;
    try{$q=db()->prepare('SHOW TABLES LIKE ?');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();}
    catch(Throwable $e){return $cache[$table]=false;}
}
function v800_cols(string $table): array {
    static $cache=[]; if(isset($cache[$table])) return $cache[$table];
    if(!v800_table($table)) return $cache[$table]=[];
    try{$q=db()->query('SHOW COLUMNS FROM `'.str_replace('`','',$table).'`');$o=[];foreach($q->fetchAll()?:[] as $r)$o[(string)$r['Field']]=true;return $cache[$table]=$o;}catch(Throwable $e){return $cache[$table]=[];}
}
function v800_json($v,array $default=[]): array { if(is_array($v))return $v;$j=json_decode((string)$v,true);return is_array($j)?$j:$default; }
function v800_clip(string $s,int $max=500): string { $s=trim($s);return mb_strlen($s)>$max?mb_substr($s,0,$max).'…':$s; }
function v800_settings(bool $refresh=false): array {
    static $cache=null;if($refresh)$cache=null;if($cache!==null)return $cache;
    if(!v800_table('enterprise_tenant_settings_v800'))return $cache=[];
    try{$q=db()->prepare('SELECT settings_json FROM enterprise_tenant_settings_v800 WHERE tenant_id=? LIMIT 1');$q->execute([v800_tid()]);return $cache=v800_json($q->fetchColumn()?:'');}catch(Throwable $e){return $cache=[];}
}
function v800_setting(string $key,$default=null){$s=v800_settings();return array_key_exists($key,$s)?$s[$key]:(function_exists('setting')?setting($key,$default):$default);}
function v800_save_settings(array $patch,?int $by=null): void {
    $s=v800_settings();foreach($patch as $k=>$v)$s[(string)$k]=$v;
    db()->prepare('INSERT INTO enterprise_tenant_settings_v800(tenant_id,settings_json,updated_by,updated_at) VALUES(?,?,?,NOW()) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json),updated_by=VALUES(updated_by),updated_at=NOW()')->execute([v800_tid(),json_encode($s,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$by]);v800_settings(true);
}
function v800_encrypt(string $plain): string { if($plain==='')return '';if(function_exists('ai710_encrypt'))return ai710_encrypt($plain);throw new RuntimeException('AI secure vault is unavailable. Install/repair v7.1.0 first.'); }
function v800_decrypt(?string $cipher): string { if(!$cipher)return '';return function_exists('ai710_decrypt')?ai710_decrypt($cipher):''; }
function v800_hint(string $secret): string { if(function_exists('ai710_secret_hint'))return ai710_secret_hint($secret);$n=strlen($secret);return $n<8?'••••':substr($secret,0,3).'…'.substr($secret,-4); }

function v800_voice_catalog(): array {
    return [
        'grandstream_ucm'=>['Grandstream UCM / SIP PBX','SIP/CTI integration for UCM63xx and compatible PBX.'],
        'asterisk_ami'=>['Asterisk AMI','Asterisk Manager Interface event/control connector.'],
        'asterisk_ari'=>['Asterisk ARI','Asterisk REST Interface / media application connector.'],
        'twilio'=>['Twilio Voice','Cloud telephony voice/webhook connector.'],
        'sip_generic'=>['Generic SIP / PBX','Generic SIP PBX with webhook/media bridge.'],
        'webhook'=>['Webhook / Media Bridge','External call/media service posting normalized events.']
    ];
}
function v800_voice_integrations(bool $enabledOnly=false): array {
    if(!v800_table('voice_integrations_v800'))return [];
    try{$sql='SELECT * FROM voice_integrations_v800 WHERE tenant_id=?'.($enabledOnly?' AND enabled=1':'').' ORDER BY enabled DESC,id DESC';$q=db()->prepare($sql);$q->execute([v800_tid()]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function v800_voice_integration(int $id): ?array {foreach(v800_voice_integrations(false) as $r)if((int)$r['id']===$id)return $r;return null;}
function v800_voice_webhook_token(int $id): string {
    $p=v800_voice_integration($id);if(!$p)throw new RuntimeException('Voice integration not found.');
    $token=bin2hex(random_bytes(24));$hash=hash('sha256',$token);$hint=substr($token,0,5).'…'.substr($token,-5);
    db()->prepare('UPDATE voice_integrations_v800 SET webhook_token_hash=?,webhook_token_hint=?,updated_at=NOW() WHERE id=? AND tenant_id=?')->execute([$hash,$hint,$id,v800_tid()]);return $token;
}
function v800_voice_event(array $data,int $tenantId,?int $integrationId=null): int {
    if(!v800_table('voice_call_events_v800'))return 0;
    $external=v800_clip((string)($data['call_id']??$data['external_call_id']??''),190);
    $dir=in_array((string)($data['direction']??''),['inbound','outbound'],true)?(string)$data['direction']:'inbound';
    $status=v800_clip((string)($data['status']??'received'),40);
    $from=v800_clip((string)($data['from']??$data['from_number']??''),100);$to=v800_clip((string)($data['to']??$data['to_number']??''),100);
    $ext=v800_clip((string)($data['extension']??$data['extension_no']??''),40);$queue=v800_clip((string)($data['queue']??$data['queue_code']??''),60);
    $duration=max(0,(int)($data['duration_seconds']??0));$recording=v800_clip((string)($data['recording_url']??''),500);
    $meta=$data;unset($meta['secret'],$meta['token'],$meta['password'],$meta['api_key']);
    if($external!==''){
        $q=db()->prepare('SELECT id FROM voice_call_events_v800 WHERE tenant_id=? AND integration_id <=> ? AND external_call_id=? ORDER BY id DESC LIMIT 1');$q->execute([$tenantId,$integrationId,$external]);$id=(int)($q->fetchColumn()?:0);
        if($id){db()->prepare('UPDATE voice_call_events_v800 SET direction=?,from_number=?,to_number=?,extension_no=?,queue_code=?,status=?,duration_seconds=?,recording_url=?,meta_json=?,updated_at=NOW(),started_at=COALESCE(started_at,NOW()),answered_at=IF(? IN (\'answered\',\'connected\'),COALESCE(answered_at,NOW()),answered_at),ended_at=IF(? IN (\'ended\',\'completed\',\'failed\',\'missed\'),COALESCE(ended_at,NOW()),ended_at) WHERE id=?')->execute([$dir,$from?:null,$to?:null,$ext?:null,$queue?:null,$status,$duration,$recording?:null,json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$status,$status,$id]);return $id;}
    }
    db()->prepare('INSERT INTO voice_call_events_v800(tenant_id,integration_id,external_call_id,direction,from_number,to_number,extension_no,queue_code,status,started_at,duration_seconds,recording_url,meta_json,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,NOW(),?,?,?,NOW(),NOW())')->execute([$tenantId,$integrationId,$external?:null,$dir,$from?:null,$to?:null,$ext?:null,$queue?:null,$status,$duration,$recording?:null,json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);return (int)db()->lastInsertId();
}

function v800_queue_add(string $type,array $payload=[],int $priority=100,?int $tenantId=null,int $maxAttempts=5,string $queue='default'): int {
    if(!v800_table('ops_job_queue_v800'))return 0;$tid=$tenantId??v800_tid();
    db()->prepare('INSERT INTO ops_job_queue_v800(tenant_id,queue_name,job_type,payload_json,priority,status,attempts,max_attempts,available_at,created_at,updated_at) VALUES(?,?,?,?,?,\'pending\',0,?,NOW(),NOW(),NOW())')->execute([$tid,$queue,$type,json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$priority,max(1,min(20,$maxAttempts))]);return (int)db()->lastInsertId();
}
function v800_queue_stats(): array {
    $out=['pending'=>0,'running'=>0,'failed'=>0,'done_24h'=>0];if(!v800_table('ops_job_queue_v800'))return $out;
    try{$q=db()->prepare("SELECT status,COUNT(*) n FROM ops_job_queue_v800 WHERE tenant_id=? AND (created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) OR status IN ('pending','running','failed')) GROUP BY status");$q->execute([v800_tid()]);foreach($q->fetchAll()?:[] as $r){$s=(string)$r['status'];if(isset($out[$s]))$out[$s]=(int)$r['n'];} $q=db()->prepare("SELECT COUNT(*) FROM ops_job_queue_v800 WHERE tenant_id=? AND status='done' AND finished_at>=DATE_SUB(NOW(),INTERVAL 1 DAY)");$q->execute([v800_tid()]);$out['done_24h']=(int)$q->fetchColumn();}catch(Throwable $e){}return $out;
}
function v800_security_event(string $key,string $severity,string $message,array $meta=[],?int $userId=null,?int $tenantId=null): void {
    if(!v800_table('ops_security_events_v800'))return;$tid=$tenantId??v800_tid();$ip=(string)($_SERVER['REMOTE_ADDR']??'');$ua=(string)($_SERVER['HTTP_USER_AGENT']??'');
    try{db()->prepare('INSERT INTO ops_security_events_v800(tenant_id,user_id,event_key,severity,actor_label,ip_hash,user_agent_hash,message,meta_json,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())')->execute([$tid,$userId,$key,$severity,$userId?'user#'.$userId:null,$ip!==''?hash('sha256',$tid.'|'.$ip):null,$ua!==''?hash('sha256',$ua):null,v800_clip($message,1000),json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}
}

function v800_upsert_health(string $key,string $label,string $status,string $message,int $latency=0,array $meta=[]): void {
    if(!v800_table('ops_health_checks_v800'))return;
    db()->prepare('INSERT INTO ops_health_checks_v800(tenant_id,check_key,label,status,latency_ms,message,meta_json,checked_at) VALUES(?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE label=VALUES(label),status=VALUES(status),latency_ms=VALUES(latency_ms),message=VALUES(message),meta_json=VALUES(meta_json),checked_at=NOW()')->execute([v800_tid(),$key,$label,$status,$latency,v800_clip($message,1000),json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
}
function v800_run_health_checks(): array {
    $checks=[];
    $start=microtime(true);try{db()->query('SELECT 1')->fetchColumn();$checks['database']=['Database','ok','Database connection is healthy.',(int)round((microtime(true)-$start)*1000)];}catch(Throwable $e){$checks['database']=['Database','critical','Database query failed: '.$e->getMessage(),0];}
    $storage=dirname(__DIR__).'/storage';$ok=is_dir($storage)&&is_writable($storage);$checks['storage']=['Storage',$ok?'ok':'critical',$ok?'storage/ is writable.':'storage/ is not writable.',0];
    $checks['curl']=['PHP cURL',function_exists('curl_init')?'ok':'warning',function_exists('curl_init')?'cURL available.':'cURL missing; external APIs may fail.',0];
    $checks['openssl']=['OpenSSL',function_exists('openssl_encrypt')?'ok':'critical',function_exists('openssl_encrypt')?'OpenSSL available.':'OpenSSL missing; secret vault cannot operate.',0];
    $smtpHost=function_exists('setting')?trim((string)setting('smtp_host','')):'';$checks['smtp_config']=['SMTP',$smtpHost!==''?'ok':'warning',$smtpHost!==''?'SMTP host configured.':'SMTP host not configured.',0];
    $mapKey=function_exists('setting')?trim((string)setting('google_maps_api_key','')):'';$checks['maps_config']=['Google Maps',$mapKey!==''?'ok':'warning',$mapKey!==''?'Google Maps API key configured.':'Google Maps API key not configured.',0];
    $q=v800_queue_stats();$checks['queue']=['Background Queue',$q['failed']>0?'warning':'ok',$q['pending'].' pending · '.$q['failed'].' failed.',0];
    foreach($checks as $k=>$r){try{v800_upsert_health($k,$r[0],$r[1],$r[2],$r[3]);}catch(Throwable $e){}}
    return $checks;
}
function v800_health_rows(): array {if(!v800_table('ops_health_checks_v800'))return [];try{$q=db()->prepare('SELECT * FROM ops_health_checks_v800 WHERE tenant_id=? ORDER BY FIELD(status,\'critical\',\'warning\',\'unknown\',\'ok\'),label');$q->execute([v800_tid()]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];} }

function v800_metric_series(string $metric,int $days=30): array {
    $days=max(7,min(365,$days));$tid=v800_tid();$series=[];
    $specs=[
      'marketplace_revenue'=>['marketplace_orders_v550','placed_at','grand_total',"status NOT IN ('cancelled')"],
      'marketplace_orders'=>['marketplace_orders_v550','placed_at','COUNT(*)',"status NOT IN ('cancelled')"],
      'hotel_revenue'=>['hotel_bookings_v700','created_at','total_amount',"status NOT IN ('cancelled')"],
      'tourism_revenue'=>['tourism_package_bookings_v700','created_at','total_amount',"status NOT IN ('cancelled')"],
      'pharmacy_revenue'=>['health_pharmacy_orders_v590','created_at','total_amount',"status NOT IN ('cancelled','rejected')"],
      'complaints'=>['municipal_requests_v630','created_at','COUNT(*)','1=1'],
      'ai_messages'=>['ai_messages_v710','created_at','COUNT(*)','1=1'],
      'voice_calls'=>['voice_call_events_v800','created_at','COUNT(*)','1=1']
    ];
    if(!isset($specs[$metric]))return [];$s=$specs[$metric];if(!v800_table($s[0]))return [];$cols=v800_cols($s[0]);$tenantCol=isset($cols['tenant_id']);$valueExpr=$s[2]==='COUNT(*)'?'COUNT(*)':'COALESCE(SUM(`'.$s[2].'`),0)';
    try{$sql='SELECT DATE(`'.$s[1].'`) d,'.$valueExpr.' v FROM `'.$s[0].'` WHERE `'.$s[1].'`>=DATE_SUB(CURDATE(),INTERVAL '.$days.' DAY)'.($tenantCol?' AND COALESCE(tenant_id,0)=?':'').' AND '.$s[3].' GROUP BY DATE(`'.$s[1].'`) ORDER BY d';$q=db()->prepare($sql);$q->execute($tenantCol?[$tid]:[]);$map=[];foreach($q->fetchAll()?:[] as $r)$map[(string)$r['d']]=(float)$r['v'];for($i=$days-1;$i>=0;$i--){$d=date('Y-m-d',strtotime('-'.$i.' days'));$series[]=['d'=>$d,'v'=>(float)($map[$d]??0)];}}catch(Throwable $e){}
    return $series;
}
function v800_linear_forecast(array $series,int $horizon=30): array {
    $vals=array_values(array_map(fn($r)=>(float)($r['v']??0),$series));$n=count($vals);if($n<2)return ['current'=>$vals[$n-1]??0,'forecast'=>$vals[$n-1]??0,'slope'=>0,'confidence'=>'low'];$sx=$sy=$sxy=$sx2=0.0;foreach($vals as $i=>$v){$sx+=$i;$sy+=$v;$sxy+=$i*$v;$sx2+=$i*$i;}$den=$n*$sx2-$sx*$sx;$slope=abs($den)<1e-9?0:(($n*$sxy-$sx*$sy)/$den);$intercept=($sy-$slope*$sx)/$n;$forecast=max(0,$intercept+$slope*($n-1+$horizon));$mean=$sy/$n;$variance=0;foreach($vals as $v)$variance+=($v-$mean)**2;$cv=$mean!=0?sqrt($variance/$n)/abs($mean):1;$confidence=$n>=28&&$cv<1?'medium':'indicative';return ['current'=>$vals[$n-1]??0,'forecast'=>$forecast,'slope'=>$slope,'confidence'=>$confidence];
}
function v800_save_forecast(string $metric,int $horizon=30,?int $by=null): array {
    $series=v800_metric_series($metric,30);$f=v800_linear_forecast($series,$horizon);if(v800_table('bi_forecast_snapshots_v800'))db()->prepare('INSERT INTO bi_forecast_snapshots_v800(tenant_id,metric_key,horizon_days,method,current_value,forecast_value,confidence_label,series_json,generated_by,generated_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())')->execute([v800_tid(),$metric,$horizon,'linear_trend',$f['current'],$f['forecast'],$f['confidence'],json_encode($series,JSON_UNESCAPED_SLASHES),$by]);return $f+['series'=>$series];
}
function v800_bi_overview(): array {
    $metrics=['marketplace_revenue','marketplace_orders','hotel_revenue','tourism_revenue','pharmacy_revenue','complaints','ai_messages','voice_calls'];$out=[];foreach($metrics as $m){$s=v800_metric_series($m,30);$sum=0;foreach($s as $x)$sum+=(float)$x['v'];$out[$m]=['sum'=>$sum,'series'=>$s,'forecast'=>v800_linear_forecast($s,30)];}return $out;
}

function v800_enterprise_profile(): array {
    if(!v800_table('enterprise_profiles_v800'))return [];
    try{$q=db()->prepare('SELECT * FROM enterprise_profiles_v800 WHERE tenant_id=? LIMIT 1');$q->execute([v800_tid()]);return $q->fetch()?:[];}catch(Throwable $e){return [];}
}
function v800_domain_valid(string $host): bool { $host=strtolower(trim($host));if(str_starts_with($host,'http://')||str_starts_with($host,'https://')){$p=parse_url($host);$host=(string)($p['host']??'');}return $host!==''&&strlen($host)<=253&&(bool)preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',$host); }
function v800_clean_host(string $host): string { $host=trim(strtolower($host));if(str_contains($host,'://')){$p=parse_url($host);$host=(string)($p['host']??'');}return rtrim($host,'.'); }
function v800_enterprise_domains(): array {if(!v800_table('enterprise_domains_v800'))return [];try{$q=db()->prepare('SELECT * FROM enterprise_domains_v800 WHERE tenant_id=? ORDER BY is_primary DESC,id');$q->execute([v800_tid()]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];} }

function v800_overview_stats(): array {
    $tid=v800_tid();$out=['voice_integrations'=>0,'calls_30d'=>0,'queue_pending'=>0,'health_issues'=>0,'open_incidents'=>0,'domains'=>0,'security_24h'=>0,'ai_actions_pending'=>0];
    $queries=[
      'voice_integrations'=>['voice_integrations_v800','SELECT COUNT(*) FROM voice_integrations_v800 WHERE tenant_id=? AND enabled=1'],
      'calls_30d'=>['voice_call_events_v800','SELECT COUNT(*) FROM voice_call_events_v800 WHERE tenant_id=? AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)'],
      'queue_pending'=>['ops_job_queue_v800',"SELECT COUNT(*) FROM ops_job_queue_v800 WHERE tenant_id=? AND status='pending'"],
      'health_issues'=>['ops_health_checks_v800',"SELECT COUNT(*) FROM ops_health_checks_v800 WHERE tenant_id=? AND status IN ('warning','critical')"],
      'open_incidents'=>['ops_incidents_v800',"SELECT COUNT(*) FROM ops_incidents_v800 WHERE tenant_id=? AND status NOT IN ('resolved','closed')"],
      'domains'=>['enterprise_domains_v800','SELECT COUNT(*) FROM enterprise_domains_v800 WHERE tenant_id=?'],
      'security_24h'=>['ops_security_events_v800',"SELECT COUNT(*) FROM ops_security_events_v800 WHERE tenant_id=? AND severity IN ('warning','high','critical') AND created_at>=DATE_SUB(NOW(),INTERVAL 1 DAY)"],
      'ai_actions_pending'=>['ai_action_queue_v710',"SELECT COUNT(*) FROM ai_action_queue_v710 WHERE tenant_id=? AND status='pending'"]
    ];
    foreach($queries as $k=>$q){if(!v800_table($q[0]))continue;try{$s=db()->prepare($q[1]);$s->execute([$tid]);$out[$k]=(int)$s->fetchColumn();}catch(Throwable $e){}}
    return $out;
}

function v800_process_job(array $job): string {
    $type=(string)$job['job_type'];$payload=v800_json($job['payload_json']??'');
    if($type==='health_sweep'){v800_run_health_checks();return 'Health checks completed.';}
    if($type==='bi_snapshot'){foreach(['marketplace_revenue','marketplace_orders','hotel_revenue','tourism_revenue','pharmacy_revenue','complaints','ai_messages','voice_calls'] as $m)v800_save_forecast($m,30,null);return 'BI forecast snapshots generated.';}
    if($type==='queue_cleanup'){db()->prepare("DELETE FROM ops_job_queue_v800 WHERE tenant_id=? AND status='done' AND finished_at<DATE_SUB(NOW(),INTERVAL 30 DAY)")->execute([(int)$job['tenant_id']]);return 'Old completed queue jobs cleaned.';}
    if($type==='voice_ai_summary'){
        $id=(int)($payload['call_event_id']??0);if($id&&v800_table('voice_call_events_v800')){db()->prepare("UPDATE voice_call_events_v800 SET ai_summary=COALESCE(ai_summary,'Queued for AI summarization; configure a media/transcript bridge for automatic summaries.'),updated_at=NOW() WHERE id=? AND tenant_id=?")->execute([$id,(int)$job['tenant_id']]);}return 'Voice AI summary job processed.';
    }
    if($type==='scheduled_report'){
        $rid=(int)($payload['report_id']??0);if(!$rid||!v800_table('bi_report_schedules_v800'))throw new RuntimeException('Scheduled report not found.');
        $q=db()->prepare('SELECT * FROM bi_report_schedules_v800 WHERE id=? AND tenant_id=? LIMIT 1');$q->execute([$rid,(int)$job['tenant_id']]);$r=$q->fetch();if(!$r)throw new RuntimeException('Scheduled report is unavailable.');
        $series=v800_metric_series((string)$r['report_key'],30);$f=v800_linear_forecast($series,30);$sum=0;foreach($series as $x)$sum+=(float)$x['v'];$title=(string)$r['name'];$text=$title."\n30-day total: ".number_format($sum,2)."\n30-day trend forecast: ".number_format((float)$f['forecast'],2)."\nConfidence: ".$f['confidence']."\nGenerated: ".date('c');$html='<h2>'.htmlspecialchars($title,ENT_QUOTES,'UTF-8').'</h2><p><b>30-day total:</b> '.number_format($sum,2).'</p><p><b>30-day trend forecast:</b> '.number_format((float)$f['forecast'],2).' ('.htmlspecialchars((string)$f['confidence'],ENT_QUOTES,'UTF-8').')</p><p>Generated by ShahkotPK Enterprise BI v8.</p>';
        $recipients=array_values(array_filter(array_map('trim',preg_split('/[,;\s]+/',(string)$r['recipient_emails'])?:[]),fn($x)=>filter_var($x,FILTER_VALIDATE_EMAIL)));
        if(!$recipients)throw new RuntimeException('Scheduled report has no valid recipient emails.');$mailer=__DIR__.'/mailer.php';if(is_file($mailer))require_once $mailer;if(!function_exists('smtp_send_mail'))throw new RuntimeException('SMTP mailer is unavailable.');foreach($recipients as $to)smtp_send_mail($to,$title,$html,$text);db()->prepare("UPDATE bi_report_schedules_v800 SET last_run_at=NOW(),last_status='sent',updated_at=NOW() WHERE id=?")->execute([$rid]);return 'Scheduled BI report sent to '.count($recipients).' recipient(s).';
    }
    return 'No-op handler completed for '.$type.'.';
}
function v800_schedule_due_jobs(): void {
    if(v800_table('ops_cron_jobs_v800')){try{$rows=db()->query("SELECT * FROM ops_cron_jobs_v800 WHERE enabled=1 AND (next_run_at IS NULL OR next_run_at<=NOW()) ORDER BY id LIMIT 50")->fetchAll()?:[];foreach($rows as $r){v800_queue_add((string)$r['handler_key'],v800_json($r['payload_json']??''),50,(int)$r['tenant_id'],4,'system');$key=(string)$r['job_key'];$next='DATE_ADD(NOW(),INTERVAL 10 MINUTE)';if($key==='queue-cleanup')$next='DATE_ADD(NOW(),INTERVAL 1 HOUR)';elseif($key==='bi-daily-snapshot')$next='DATE_ADD(NOW(),INTERVAL 1 DAY)';db()->exec("UPDATE ops_cron_jobs_v800 SET last_run_at=NOW(),last_status='queued',next_run_at=".$next.",updated_at=NOW() WHERE id=".(int)$r['id']);}}catch(Throwable $e){}}
    if(v800_table('bi_report_schedules_v800')){try{$rows=db()->query("SELECT * FROM bi_report_schedules_v800 WHERE enabled=1 AND (next_run_at IS NULL OR next_run_at<=NOW()) ORDER BY id LIMIT 50")->fetchAll()?:[];foreach($rows as $r){v800_queue_add('scheduled_report',['report_id'=>(int)$r['id']],70,(int)$r['tenant_id'],5,'reports');$freq=(string)$r['frequency'];$interval=$freq==='daily'?'1 DAY':($freq==='monthly'?'1 MONTH':'1 WEEK');db()->exec("UPDATE bi_report_schedules_v800 SET next_run_at=DATE_ADD(NOW(),INTERVAL {$interval}),last_status='queued',updated_at=NOW() WHERE id=".(int)$r['id']);}}catch(Throwable $e){}}
}
function v800_worker_run(int $limit=10): array {
    if(!v800_table('ops_job_queue_v800'))return ['processed'=>0,'failed'=>0];v800_schedule_due_jobs();$limit=max(1,min(100,$limit));$processed=$failed=0;
    for($i=0;$i<$limit;$i++){
        $job=null;try{db()->beginTransaction();$q=db()->query("SELECT * FROM ops_job_queue_v800 WHERE status='pending' AND available_at<=NOW() ORDER BY priority ASC,id ASC LIMIT 1 FOR UPDATE");$job=$q->fetch()?:null;if(!$job){db()->commit();break;}db()->prepare("UPDATE ops_job_queue_v800 SET status='running',reserved_at=NOW(),attempts=attempts+1,updated_at=NOW() WHERE id=?")->execute([(int)$job['id']]);db()->commit();}catch(Throwable $e){if(db()->inTransaction())db()->rollBack();break;}
        try{$hadOverride=array_key_exists('V800_TENANT_OVERRIDE',$GLOBALS);$oldOverride=$GLOBALS['V800_TENANT_OVERRIDE']??null;$GLOBALS['V800_TENANT_OVERRIDE']=(int)$job['tenant_id'];try{$msg=v800_process_job($job);}finally{if($hadOverride)$GLOBALS['V800_TENANT_OVERRIDE']=$oldOverride;else unset($GLOBALS['V800_TENANT_OVERRIDE']);}db()->prepare("UPDATE ops_job_queue_v800 SET status='done',finished_at=NOW(),last_error=NULL,updated_at=NOW() WHERE id=?")->execute([(int)$job['id']]);$processed++;}
        catch(Throwable $e){$attempt=(int)$job['attempts']+1;$max=(int)$job['max_attempts'];$status=$attempt>=$max?'failed':'pending';$delay=min(3600,30*(2**min(6,$attempt)));db()->prepare("UPDATE ops_job_queue_v800 SET status=?,last_error=?,available_at=DATE_ADD(NOW(),INTERVAL ? SECOND),updated_at=NOW() WHERE id=?")->execute([$status,v800_clip($e->getMessage(),1000),$delay,(int)$job['id']]);$failed++;}
    }
    return ['processed'=>$processed,'failed'=>$failed];
}
