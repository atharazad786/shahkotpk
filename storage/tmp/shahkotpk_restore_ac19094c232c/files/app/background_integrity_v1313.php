<?php
declare(strict_types=1);
/** ShahkotPK v13.0.13.0 — on-demand notifications / cron / background-job integrity. */
if (!function_exists('sk1313_fresh_audit')) {
function sk1313_db(): PDO {
    global $pdo,$db;
    if(isset($pdo) && $pdo instanceof PDO) return $pdo;
    if(isset($db) && $db instanceof PDO) return $db;
    if(function_exists('db')) { $x=db(); if($x instanceof PDO) return $x; }
    throw new RuntimeException('Database connection unavailable');
}
function sk1313_tid(): int {
    if(function_exists('current_tenant_id')) { try{return (int)current_tenant_id();}catch(Throwable $e){} }
    if(function_exists('tenant_id')) { try{return (int)tenant_id();}catch(Throwable $e){} }
    return isset($_SESSION['tenant_id'])?(int)$_SESSION['tenant_id']:0;
}
function sk1313_actor_id(): ?int {
    if(function_exists('current_user')) { try{$u=current_user();if(is_array($u)&&isset($u['id']))return (int)$u['id'];if(is_object($u)&&isset($u->id))return (int)$u->id;}catch(Throwable $e){} }
    return isset($_SESSION['user_id'])?(int)$_SESSION['user_id']:null;
}
function sk1313_ident(string $name): string { if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name))throw new InvalidArgumentException('Unsafe SQL identifier');return '`'.$name.'`'; }
function sk1313_table(string $table): bool {
    if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$table))return false;
    try{$q=sk1313_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1313_cols(string $table): array {
    $out=[];if(!sk1313_table($table))return $out;
    try{$q=sk1313_db()->prepare('SELECT COLUMN_NAME,DATA_TYPE,IS_NULLABLE,COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');$q->execute([$table]);foreach($q->fetchAll()?:[] as $r)$out[(string)$r['COLUMN_NAME']]=$r;}catch(Throwable $e){}
    return $out;
}
function sk1313_first(array $cols,array $names): string { foreach($names as $n)if(isset($cols[$n]))return $n;return ''; }
function sk1313_setting(string $key,string $default=''): string {
    if(!sk1313_table('settings'))return $default;
    try{$q=sk1313_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}catch(Throwable $e){return $default;}
}
function sk1313_set_settings(array $pairs): int {
    if(!sk1313_table('settings')||!$pairs)return 0;$n=0;
    try{$q=sk1313_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');foreach($pairs as $k=>$v){$q->execute([(string)$k,(string)$v]);$n+=$q->rowCount();}}catch(Throwable $e){}
    return $n;
}
function sk1313_estimated_rows(string $table): int {
    try{$q=sk1313_db()->prepare('SELECT COALESCE(TABLE_ROWS,0) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (int)$q->fetchColumn();}catch(Throwable $e){return 0;}
}
function sk1313_candidate_tables(): array {
    $exact=['notifications','notification_queue','notification_jobs','notification_rules','outbound_campaigns','outbound_campaign_recipients','message_queue','email_queue','sms_queue','whatsapp_queue','push_queue','background_jobs','jobs_queue','scheduled_jobs','cron_jobs','cron_locks','queue_jobs','failed_jobs','ai_automation_runs','subscription_renewal_events','bulk_import_jobs','regression_test_runs','unified_inbox_messages','mobile_push_devices_v2'];
    $names=[];try{$q=sk1313_db()->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME LIMIT 600');foreach($q->fetchAll()?:[] as $r){$t=(string)$r['TABLE_NAME'];$l=strtolower($t);$hit=in_array($l,$exact,true)||(bool)preg_match('/(?:notification|queue|cron|schedul|background|campaign|automation|dispatch|outbox|delivery|worker|_jobs?$|jobs_)/i',$l);if($hit && !preg_match('/^background_integrity_/i',$l))$names[$t]=true;}}catch(Throwable $e){}
    $out=array_keys($names);sort($out,SORT_NATURAL|SORT_FLAG_CASE);return array_slice($out,0,36);
}
function sk1313_status_kind(string $status): string {
    $s=strtolower(trim($status));
    if($s==='')return 'unknown';
    if(in_array($s,['running','processing','in_progress','working','locked','sending','executing'],true))return 'running';
    if(in_array($s,['pending','queued','waiting','scheduled','ready','new','open'],true))return 'pending';
    if(in_array($s,['failed','error','dead','dead_letter','cancelled','canceled','rejected'],true))return 'failed';
    if(in_array($s,['retry','retrying','deferred','backoff'],true))return 'retry';
    if(in_array($s,['done','completed','complete','sent','delivered','success','succeeded','processed','closed'],true))return 'done';
    return 'other';
}
function sk1313_ts($v): ?int {
    if($v===null||$v==='')return null;if(is_int($v)||is_float($v)){ $n=(int)$v; return $n>1000000000?$n:null; }
    $s=trim((string)$v);if($s===''||$s==='0000-00-00 00:00:00')return null;$t=strtotime($s);return $t===false?null:$t;
}
function sk1313_sample_table(string $table): array {
    $cols=sk1313_cols($table);$out=['table'=>$table,'estimated_rows'=>sk1313_estimated_rows($table),'sampled'=>0,'status_col'=>'','time_col'=>'','schedule_col'=>'','attempt_col'=>'','key_col'=>'','running'=>0,'pending'=>0,'failed'=>0,'retry'=>0,'done'=>0,'other'=>0,'stale_running'=>0,'overdue_pending'=>0,'hot_retries'=>0,'duplicate_active_keys'=>0,'error'=>''];
    if(!$cols)return $out;
    $status=sk1313_first($cols,['status','state','job_status','delivery_status','send_status','run_status']);
    $time=sk1313_first($cols,['updated_at','locked_at','started_at','processing_at','last_attempt_at','created_at']);
    $sched=sk1313_first($cols,['scheduled_at','run_at','available_at','next_run_at','next_attempt_at','retry_at','send_at']);
    $attempt=sk1313_first($cols,['attempts','retry_count','retries','tries','attempt_count']);
    $key=sk1313_first($cols,['dedupe_key','unique_key','job_key','task_key','queue_key']);
    $pk='';foreach($cols as $n=>$m)if((string)($m['COLUMN_KEY']??'')==='PRI'){$pk=(string)$n;break;}if($pk==='')$pk=isset($cols['id'])?'id':'';
    $select=[];foreach(array_unique(array_filter([$pk,$status,$time,$sched,$attempt,$key,'tenant_id'])) as $c)if(isset($cols[$c]))$select[]=sk1313_ident($c);
    if(!$select)return $out;
    $params=[];$where='1=1';$tid=sk1313_tid();if($tid>0&&isset($cols['tenant_id'])){$where='`tenant_id`=?';$params[]=$tid;}
    $order=$pk!==''?sk1313_ident($pk).' DESC':($time!==''?sk1313_ident($time).' DESC':'');
    $sql='SELECT '.implode(',',$select).' FROM '.sk1313_ident($table).' WHERE '.$where.($order!==''?' ORDER BY '.$order:'').' LIMIT 250';
    try{$q=sk1313_db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll()?:[];$out['sampled']=count($rows);$out['status_col']=$status;$out['time_col']=$time;$out['schedule_col']=$sched;$out['attempt_col']=$attempt;$out['key_col']=$key;$activeKeys=[];$now=time();
        foreach($rows as $r){$kind=$status!==''?sk1313_status_kind((string)($r[$status]??'')):'other';if(isset($out[$kind]))$out[$kind]++;else$out['other']++;
            $activity=$time!==''?sk1313_ts($r[$time]??null):null;if($kind==='running'&&$activity!==null&&$activity<($now-1800))$out['stale_running']++;
            $due=$sched!==''?sk1313_ts($r[$sched]??null):null;if(($kind==='pending'||$kind==='retry')&&$due!==null&&$due<($now-900))$out['overdue_pending']++;
            if($attempt!==''&&(int)($r[$attempt]??0)>=5)$out['hot_retries']++;
            if($key!==''&&($kind==='running'||$kind==='pending'||$kind==='retry')){$kv=trim((string)($r[$key]??''));if($kv!==''){$lk=strtolower($kv);$activeKeys[$lk]=($activeKeys[$lk]??0)+1;}}
        }
        foreach($activeKeys as $n)if($n>1)$out['duplicate_active_keys']+=($n-1);
    }catch(Throwable $e){$out['error']=$e->getMessage();}
    return $out;
}
function sk1313_worker_files(): array {
    $root=dirname(__DIR__);$candidates=[];
    foreach(glob($root.'/*.php')?:[] as $f){if(preg_match('/(?:cron|worker|queue|schedule|dispatch|notify|notification|campaign|automation|renewal|job)/i',basename($f)))$candidates[$f]=true;}
    foreach(['cron','jobs','workers','bin','app/cron','app/jobs','app/workers'] as $rel){$base=$root.'/'.$rel;if(!is_dir($base))continue;try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS));foreach($it as $fi){if(!$fi->isFile()||strtolower($fi->getExtension())!=='php')continue;$candidates[$fi->getPathname()]=true;if(count($candidates)>=180)break 2;}}catch(Throwable $e){}}
    $out=[];foreach(array_keys($candidates) as $f){if(count($out)>=100)break;$rel=str_replace('\\','/',substr($f,strlen($root)+1));$size=(int)@filesize($f);$src='';if($size>0&&$size<=524288&&is_readable($f))$src=(string)@file_get_contents($f);$low=strtolower($src);$auth=(bool)preg_match('/hash_equals|cron[_-]?token|authorization|http_x_|secret|signed|signature|php_sapi|\bcli\b/i',$src);$lock=(bool)preg_match('/flock|GET_LOCK|RELEASE_LOCK|mutex|semaphore|lock[_a-z]*\s*\(/i',$src);$retry=(bool)preg_match('/retry|backoff|attempts?|max[_-]?tries/i',$src);$webExposed=(strpos($rel,'/')===false);$out[]=['file'=>$rel,'bytes'=>$size,'web_exposed'=>$webExposed,'auth_or_cli_hint'=>$auth,'lock_hint'=>$lock,'retry_hint'=>$retry,'readable'=>$src!==''];}
    usort($out,static fn($a,$b)=>strcmp($a['file'],$b['file']));return $out;
}
function sk1313_heartbeat_settings(): array {
    if(!sk1313_table('settings'))return [];$cols=sk1313_cols('settings');if(!isset($cols['setting_key'])||!isset($cols['setting_value']))return [];$out=[];
    try{$q=sk1313_db()->query("SELECT setting_key,setting_value FROM settings WHERE (setting_key LIKE '%cron%' OR setting_key LIKE '%scheduler%' OR setting_key LIKE '%worker%' OR setting_key LIKE '%background%' OR setting_key LIKE '%queue%') ORDER BY setting_key LIMIT 100");foreach($q->fetchAll()?:[] as $r){$k=(string)$r['setting_key'];if(preg_match('/secret|token|password|api[_-]?key|private/i',$k))continue;if(!preg_match('/last|heartbeat|seen|run|updated|refresh/i',$k))continue;$t=sk1313_ts($r['setting_value']??null);$out[]=['key'=>$k,'timestamp'=>$t?gmdate('Y-m-d H:i:s',$t).' UTC':'','age_minutes'=>$t?max(0,(int)floor((time()-$t)/60)):null,'present'=>trim((string)($r['setting_value']??''))!==''];if(count($out)>=40)break;}}catch(Throwable $e){}
    return $out;
}
function sk1313_findings(array $tables,array $files,array $heartbeats): array {
    $f=[];$stale=0;$overdue=0;$hot=0;$dupes=0;$failed=0;$sampled=0;$errors=0;
    foreach($tables as $t){$stale+=(int)$t['stale_running'];$overdue+=(int)$t['overdue_pending'];$hot+=(int)$t['hot_retries'];$dupes+=(int)$t['duplicate_active_keys'];$failed+=(int)$t['failed'];$sampled+=(int)$t['sampled'];if($t['error']!=='')$errors++;}
    if($errors)$f[]=['severity'=>'medium','message'=>$errors.' background/notification table sample(s) could not be inspected cleanly.'];
    if($stale)$f[]=['severity'=>'high','message'=>$stale.' sampled running/processing row(s) appear older than 30 minutes. Review for stuck workers before retrying anything.'];
    if($overdue>20)$f[]=['severity'=>'high','message'=>$overdue.' sampled pending/retry row(s) are more than 15 minutes past their scheduled time.'];elseif($overdue)$f[]=['severity'=>'medium','message'=>$overdue.' sampled pending/retry row(s) are more than 15 minutes past their scheduled time.'];
    if($hot)$f[]=['severity'=>'medium','message'=>$hot.' sampled row(s) report 5 or more attempts/retries. Review provider failures and backoff policy.'];
    if($dupes)$f[]=['severity'=>'medium','message'=>$dupes.' duplicate active dedupe/job key occurrence(s) were found inside bounded samples.'];
    if($sampled>=20 && $failed>0 && ($failed/$sampled)>=0.35)$f[]=['severity'=>'medium','message'=>'Failed/error states represent '.round(($failed/$sampled)*100).'% of sampled background rows.'];
    $exposed=0;foreach($files as $x)if($x['web_exposed']&&!$x['auth_or_cli_hint'])$exposed++;if($exposed)$f[]=['severity'=>'medium','message'=>$exposed.' root-level cron/worker candidate file(s) lack an obvious CLI/auth marker in bounded source inspection. Server-level protection may still exist; review manually.'];
    $activeTables=0;foreach($tables as $t)if((int)$t['estimated_rows']>0)$activeTables++;if($activeTables>0&&!$heartbeats)$f[]=['severity'=>'low','message'=>'Background-related tables contain estimated rows, but no non-secret cron/worker heartbeat setting was discovered. This is advisory only.'];
    $iv=sk1313_setting('installed_app_version','');if($iv!==''&&version_compare($iv,'13.0.13.0','<'))$f[]=['severity'=>'medium','message'=>'installed_app_version reports '.$iv.' while Step 13 expects 13.0.13.0 after migration.'];
    return $f;
}
function sk1313_scan(): array {
    $tables=[];foreach(sk1313_candidate_tables() as $t)$tables[]=sk1313_sample_table($t);$files=sk1313_worker_files();$heartbeats=sk1313_heartbeat_settings();$findings=sk1313_findings($tables,$files,$heartbeats);$penalty=0;foreach($findings as $x)$penalty+=($x['severity']==='high'?20:($x['severity']==='medium'?8:2));$score=max(0,100-min(100,$penalty));
    return ['version'=>'13.0.13.0','generated_at'=>gmdate('c'),'score'=>$score,'tables'=>$tables,'worker_files'=>$files,'heartbeats'=>$heartbeats,'findings'=>$findings,'normal_request_scanner'=>false,'sample_limit_per_table'=>250];
}
function sk1313_save_run(array $scan): int {
    if(!sk1313_table('background_integrity_runs_v1313'))return 0;try{$h=0;$m=0;foreach($scan['findings'] as $x){if($x['severity']==='high')$h++;elseif($x['severity']==='medium')$m++;}$q=sk1313_db()->prepare('INSERT INTO background_integrity_runs_v1313(tenant_id,actor_user_id,score,high_count,medium_count,tables_scanned,files_scanned,summary_json,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())');$q->execute([sk1313_tid(),sk1313_actor_id(),(int)$scan['score'],$h,$m,count($scan['tables']),count($scan['worker_files']),json_encode($scan,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);return (int)sk1313_db()->lastInsertId();}catch(Throwable $e){return 0;}
}
function sk1313_fresh_audit(): array {$x=sk1313_scan();$x['run_id']=sk1313_save_run($x);return $x;}
function sk1313_last_audit(): ?array {
    if(!sk1313_table('background_integrity_runs_v1313'))return null;try{$params=[];$where='1=1';$tid=sk1313_tid();if($tid>0){$where='tenant_id=?';$params[]=$tid;}$q=sk1313_db()->prepare('SELECT id,summary_json,created_at FROM background_integrity_runs_v1313 WHERE '.$where.' ORDER BY id DESC LIMIT 1');$q->execute($params);$r=$q->fetch();if(!$r)return null;$x=json_decode((string)$r['summary_json'],true);if(!is_array($x))return null;$x['run_id']=(int)$r['id'];$x['saved_at']=(string)$r['created_at'];return $x;}catch(Throwable $e){return null;}
}
function sk1313_log_action(string $key,array $result): void {
    if(!sk1313_table('background_integrity_actions_v1313'))return;try{$q=sk1313_db()->prepare('INSERT INTO background_integrity_actions_v1313(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1313_tid(),sk1313_actor_id(),$key,json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}
}
function sk1313_prune_own_history(int $keep=100): int {
    if(!sk1313_table('background_integrity_runs_v1313'))return 0;$keep=max(20,min(500,$keep));$tid=sk1313_tid();try{$params=[];$where='1=1';if($tid>0){$where='tenant_id=?';$params[]=$tid;}$q=sk1313_db()->prepare('SELECT id FROM background_integrity_runs_v1313 WHERE '.$where.' ORDER BY id DESC LIMIT 1 OFFSET '.($keep-1));$q->execute($params);$cut=$q->fetchColumn();if($cut===false)return 0;$params2=[(int)$cut];$where2='id<?';if($tid>0){$where2.=' AND tenant_id=?';$params2[]=$tid;}$d=sk1313_db()->prepare('DELETE FROM background_integrity_runs_v1313 WHERE '.$where2);$d->execute($params2);return $d->rowCount();}catch(Throwable $e){return 0;}
}
function sk1313_safe_maintenance(): array {
    $r=['settings_rows'=>0,'old_audits_pruned'=>0,'stat_cache_cleared'=>false,'notes'=>[],'errors'=>[]];$stamp=gmdate('Y-m-d H:i:s').' UTC';
    try{$r['settings_rows']=sk1313_set_settings(['step13_background_integrity_version'=>'13.0.13.0','background_integrity_last_maintenance'=>$stamp,'background_integrity_cache_bust'=>$stamp]);}catch(Throwable $e){$r['errors'][]='Settings: '.$e->getMessage();}
    try{$r['old_audits_pruned']=sk1313_prune_own_history(100);}catch(Throwable $e){$r['errors'][]='Audit history: '.$e->getMessage();}
    try{clearstatcache(true);$r['stat_cache_cleared']=true;}catch(Throwable $e){$r['errors'][]='PHP stat cache: '.$e->getMessage();}
    $r['notes'][]='No notification, queue, job, campaign, automation or cron row was modified, retried, cancelled or executed.';
    $r['notes'][]='Native front-page menu, public content, users, uploads and provider credentials were not touched.';sk1313_log_action('safe_background_maintenance',$r);return $r;
}
}
