<?php
declare(strict_types=1);

/** ShahkotPK v9.8.0 — Server & System Control Center helpers. */

function sk980_root(): string {
    return realpath(__DIR__.'/..') ?: dirname(__DIR__);
}
function sk980_ident(string $name): string {
    if(!preg_match('/^[A-Za-z0-9_]{1,64}$/',$name)) throw new InvalidArgumentException('Invalid identifier.');
    return '`'.$name.'`';
}
function sk980_table_exists(string $table): bool {
    try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk980_columns(string $table): array {
    if(!sk980_table_exists($table)) return [];
    try{$q=db()->prepare('SELECT COLUMN_NAME,DATA_TYPE,COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');$q->execute([$table]);$o=[];foreach($q->fetchAll(PDO::FETCH_ASSOC)?:[] as $r)$o[(string)$r['COLUMN_NAME']]=$r;return $o;}catch(Throwable $e){return [];}
}
function sk980_fmt_bytes(int|float|null $bytes): string {
    $n=(float)($bytes??0);$u=['B','KB','MB','GB','TB'];$i=0;while($n>=1024&&$i<count($u)-1){$n/=1024;$i++;}return number_format($n,$i?2:0).' '.$u[$i];
}
function sk980_ini_bytes(string $v): int {
    $v=trim($v);if($v==='')return 0;$last=strtolower(substr($v,-1));$n=(float)$v;return (int)match($last){'g'=>$n*1073741824,'m'=>$n*1048576,'k'=>$n*1024,default=>$n};
}
function sk980_setting(string $key,string $default=''): string {
    try{
        if(function_exists('setting')) return (string)setting($key,$default);
        if(sk980_table_exists('settings')){$q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}
    }catch(Throwable $e){}
    return $default;
}
function sk980_save_setting(string $key,string $value): void {
    if(function_exists('save_setting')){save_setting($key,$value);return;}
    if(!sk980_table_exists('settings')) throw new RuntimeException('Settings table is unavailable.');
    $q=db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([$key,$value]);
}
function sk980_audit(string $event,string $summary,array $meta=[],?int $uid=null,string $severity='info'): void {
    try{
        if(sk980_table_exists('system_control_events_v980')){
            $tid=null;try{if(function_exists('tenant_id')){$x=(int)tenant_id();$tid=$x>0?$x:null;}}catch(Throwable $e){}
            $q=db()->prepare('INSERT INTO system_control_events_v980(tenant_id,actor_user_id,event_key,severity,summary,meta_json,created_at) VALUES(?,?,?,?,?,?,NOW())');
            $q->execute([$tid,$uid,$event,$severity,mb_substr($summary,0,250),json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
        }
        if(function_exists('tenant_audit')) tenant_audit('system.'.$event,'system_control',0,$summary,$meta);
    }catch(Throwable $e){}
}
function sk980_proc_mem(): array {
    $out=['total'=>0,'available'=>0,'used'=>0,'percent'=>null];$file='/proc/meminfo';if(!is_readable($file))return $out;
    $data=@file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)?:[];$m=[];foreach($data as $line){if(preg_match('/^([A-Za-z_()]+):\s+(\d+)\s+kB$/',$line,$x))$m[$x[1]]=(int)$x[2]*1024;}
    $out['total']=$m['MemTotal']??0;$out['available']=$m['MemAvailable']??($m['MemFree']??0);$out['used']=max(0,$out['total']-$out['available']);$out['percent']=$out['total']>0?round($out['used']*100/$out['total'],1):null;return $out;
}
function sk980_db_summary(): array {
    try{$q=db()->query("SELECT COUNT(*) tables,COALESCE(SUM(DATA_LENGTH+INDEX_LENGTH),0) bytes,COALESCE(SUM(TABLE_ROWS),0) rows FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()");$r=$q->fetch(PDO::FETCH_ASSOC)?:[];return ['connected'=>true,'name'=>(string)(db()->query('SELECT DATABASE()')->fetchColumn()?:''),'version'=>(string)(db()->query('SELECT VERSION()')->fetchColumn()?:''),'tables'=>(int)($r['tables']??0),'bytes'=>(int)($r['bytes']??0),'rows'=>(int)($r['rows']??0)];}catch(Throwable $e){return ['connected'=>false,'error'=>$e->getMessage(),'name'=>'','version'=>'','tables'=>0,'bytes'=>0,'rows'=>0];}
}
function sk980_server_health(): array {
    $root=sk980_root();$diskTotal=@disk_total_space($root);$diskFree=@disk_free_space($root);$diskUsed=($diskTotal!==false&&$diskFree!==false)?$diskTotal-$diskFree:0;$load=function_exists('sys_getloadavg')?(@sys_getloadavg()?:[]):[];$mem=sk980_proc_mem();$db=sk980_db_summary();
    return [
      'php'=>PHP_VERSION,'sapi'=>PHP_SAPI,'os'=>PHP_OS_FAMILY,'server'=>(string)($_SERVER['SERVER_SOFTWARE']??'Unknown'),
      'memory_limit'=>(string)ini_get('memory_limit'),'memory_limit_bytes'=>sk980_ini_bytes((string)ini_get('memory_limit')),'memory_peak'=>memory_get_peak_usage(true),'max_execution_time'=>(int)ini_get('max_execution_time'),
      'upload_max_filesize'=>(string)ini_get('upload_max_filesize'),'post_max_size'=>(string)ini_get('post_max_size'),'load'=>$load,'ram'=>$mem,
      'disk_total'=>$diskTotal===false?0:(int)$diskTotal,'disk_free'=>$diskFree===false?0:(int)$diskFree,'disk_used'=>(int)$diskUsed,'disk_percent'=>($diskTotal&&$diskTotal>0)?round($diskUsed*100/$diskTotal,1):null,
      'db'=>$db,'opcache'=>function_exists('opcache_get_status')?(bool)@opcache_get_status(false):false,'time'=>date('c')
    ];
}
function sk980_safe_dirs(): array {
    $root=sk980_root();$map=[
      'app_cache'=>$root.'/storage/cache','framework_cache'=>$root.'/storage/framework/cache','view_cache'=>$root.'/storage/framework/views','storage_views'=>$root.'/storage/views','legacy_cache'=>$root.'/cache','tmp_cache'=>$root.'/tmp/cache'
    ];$out=[];foreach($map as $k=>$p){if(is_dir($p)){$r=realpath($p);if($r&&str_starts_with($r,$root.DIRECTORY_SEPARATOR))$out[$k]=$r;}}return $out;
}
function sk980_dir_size(string $dir,int $maxFiles=100000): array {
    $bytes=0;$files=0;if(!is_dir($dir))return ['bytes'=>0,'files'=>0,'truncated'=>false];
    try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if($f->isFile()){$bytes+=$f->getSize();$files++;if($files>=$maxFiles)return ['bytes'=>$bytes,'files'=>$files,'truncated'=>true];}}}catch(Throwable $e){}
    return ['bytes'=>$bytes,'files'=>$files,'truncated'=>false];
}
function sk980_cache_stats(): array {$o=[];foreach(sk980_safe_dirs() as $k=>$p)$o[$k]=['path'=>$p]+sk980_dir_size($p);return $o;}
function sk980_clear_cache(string $key): array {
    $dirs=sk980_safe_dirs();if(!isset($dirs[$key]))throw new RuntimeException('Unknown cache location.');$dir=$dirs[$key];$keep=['.htaccess','index.php','.gitkeep'];$files=0;$bytes=0;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $f){if(in_array($f->getFilename(),$keep,true))continue;try{if($f->isFile()||$f->isLink()){$bytes+=$f->isFile()?$f->getSize():0;if(@unlink($f->getPathname()))$files++;}elseif($f->isDir())@rmdir($f->getPathname());}catch(Throwable $e){}}
    return ['key'=>$key,'path'=>$dir,'files'=>$files,'bytes'=>$bytes];
}
function sk980_clear_all_cache(): array {$o=['items'=>[],'files'=>0,'bytes'=>0,'opcache'=>null];foreach(array_keys(sk980_safe_dirs()) as $k){$r=sk980_clear_cache($k);$o['items'][]=$r;$o['files']+=$r['files'];$o['bytes']+=$r['bytes'];}if(function_exists('opcache_reset')){try{$o['opcache']=(bool)opcache_reset();}catch(Throwable $e){$o['opcache']=false;}}return $o;}
function sk980_log_files(): array {
    $root=sk980_root();$c=[];$dirs=[$root.'/storage/logs',$root.'/logs',$root.'/storage'];$direct=[$root.'/error_log',$root.'/php_errorlog',$root.'/storage/error.log'];
    foreach($direct as $f)if(is_file($f))$c[]=$f;
    foreach($dirs as $d){if(!is_dir($d))continue;try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if(!$f->isFile())continue;$n=strtolower($f->getFilename());if(str_ends_with($n,'.log')||str_contains($n,'error_log'))$c[]=$f->getPathname();if(count($c)>=80)break 2;}}catch(Throwable $e){}}
    $out=[];foreach(array_values(array_unique($c)) as $p){$r=realpath($p);if(!$r||!str_starts_with($r,$root.DIRECTORY_SEPARATOR))continue;$out[sha1($r)]=['id'=>sha1($r),'path'=>$r,'relative'=>ltrim(str_replace($root,'',$r),DIRECTORY_SEPARATOR),'size'=>(int)@filesize($r),'modified'=>(int)@filemtime($r)];}
    uasort($out,fn($a,$b)=>($b['modified']<=>$a['modified']));return $out;
}
function sk980_log_by_id(string $id): array {$all=sk980_log_files();if(!isset($all[$id]))throw new RuntimeException('Log file not found or not allowed.');return $all[$id];}
function sk980_tail_file(string $path,int $maxBytes=262144): string {
    $size=(int)@filesize($path);$read=min(max(0,$size),$maxBytes);$fh=@fopen($path,'rb');if(!$fh)return '';if($size>$read)@fseek($fh,-$read,SEEK_END);$data=(string)@stream_get_contents($fh);@fclose($fh);return $data;
}
function sk980_clear_log(string $id): array {$l=sk980_log_by_id($id);$before=(int)@filesize($l['path']);$ok=@file_put_contents($l['path'],'',LOCK_EX)!==false;return ['file'=>$l['relative'],'bytes_cleared'=>$before,'success'=>$ok];}
function sk980_queue_tables(): array {return ['ops_job_queue_v800','mobile_push_queue_v900','automation_runs_v900','automation_content_jobs_v900'];}
function sk980_queue_summary(): array {
    $out=[];foreach(sk980_queue_tables() as $table){if(!sk980_table_exists($table))continue;$cols=sk980_columns($table);$status=isset($cols['status'])?'status':null;$row=['table'=>$table,'total'=>(int)db()->query('SELECT COUNT(*) FROM '.sk980_ident($table))->fetchColumn(),'statuses'=>[],'recent'=>[],'retryable'=>false];
      if($status){try{$q=db()->query('SELECT '.sk980_ident($status).' s,COUNT(*) c FROM '.sk980_ident($table).' GROUP BY '.sk980_ident($status).' ORDER BY c DESC');foreach($q->fetchAll(PDO::FETCH_ASSOC)?:[] as $r)$row['statuses'][(string)($r['s']??'NULL')]=(int)$r['c'];}catch(Throwable $e){}}
      $select=[];foreach(['id','job_key','event_key','title','status','attempts','available_at','created_at','updated_at','last_error','error_message'] as $c)if(isset($cols[$c]))$select[]=$c;
      if($select){$order=isset($cols['id'])?' ORDER BY id DESC':'';try{$q=db()->query('SELECT `'.implode('`,`',$select).'` FROM '.sk980_ident($table).$order.' LIMIT 15');$row['recent']=$q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){}}
      $row['retryable']=isset($cols['id'],$cols['status']);$out[]=$row;
    }return $out;
}
function sk980_retry_queue(string $table): array {
    if(!in_array($table,sk980_queue_tables(),true)||!sk980_table_exists($table))throw new RuntimeException('Queue table is not supported.');$cols=sk980_columns($table);if(!isset($cols['status']))throw new RuntimeException('Queue status column is unavailable.');
    $sets=['`status`=?'];$params=['pending'];if(isset($cols['attempts']))$sets[]='`attempts`=0';if(isset($cols['available_at']))$sets[]='`available_at`=NOW()';$where="LOWER(CAST(`status` AS CHAR)) IN ('failed','error','cancelled')";$sql='UPDATE '.sk980_ident($table).' SET '.implode(',',$sets).' WHERE '.$where.' LIMIT 500';$q=db()->prepare($sql);$q->execute($params);return ['table'=>$table,'retried'=>$q->rowCount()];
}
function sk980_cron_summary(): array {
    foreach(['ops_cron_jobs_v800','cron_jobs','scheduled_jobs'] as $table){if(!sk980_table_exists($table))continue;$cols=sk980_columns($table);$select=[];foreach(['id','job_key','name','command','schedule','cron_expression','status','last_run_at','next_run_at','last_status','last_error','updated_at'] as $c)if(isset($cols[$c]))$select[]=$c;$rows=[];if($select){$order=isset($cols['id'])?' ORDER BY id DESC':'';try{$rows=db()->query('SELECT `'.implode('`,`',$select).'` FROM '.sk980_ident($table).$order.' LIMIT 50')->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){}}return ['table'=>$table,'count'=>(int)db()->query('SELECT COUNT(*) FROM '.sk980_ident($table))->fetchColumn(),'rows'=>$rows];}return ['table'=>null,'count'=>0,'rows'=>[]];
}
function sk980_diagnostics(): array {
    $root=sk980_root();$tests=[];$add=function(string $name,bool $ok,string $detail)use(&$tests){$tests[]=['name'=>$name,'ok'=>$ok,'detail'=>$detail];};
    try{db()->query('SELECT 1')->fetchColumn();$add('Database connection',true,'PDO database query successful.');}catch(Throwable $e){$add('Database connection',false,$e->getMessage());}
    foreach(['pdo_mysql','mbstring','openssl','curl','fileinfo','json'] as $ext)$add('PHP extension: '.$ext,extension_loaded($ext),extension_loaded($ext)?'Loaded':'Missing');
    foreach(['gd','imagick','zip','intl','opcache'] as $ext)$add('Optional extension: '.$ext,extension_loaded($ext),extension_loaded($ext)?'Loaded':'Not loaded');
    foreach([$root.'/storage',$root.'/uploads'] as $p)$add('Writable: '.basename($p),is_dir($p)&&is_writable($p),is_dir($p)?(is_writable($p)?'Writable':'Not writable'):'Directory missing');
    $add('HTTPS request',(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||((string)($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https'),'Current admin request '.((!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'uses HTTPS':'may not be direct HTTPS'));
    $add('Storage Control Center',is_file($root.'/admin/storage-manager.php'),is_file($root.'/admin/storage-manager.php')?'Installed':'Not detected');
    $add('Database Center',is_file($root.'/admin/database-manager.php'),is_file($root.'/admin/database-manager.php')?'Installed':'Not detected');
    $add('Maintenance hook',sk980_maintenance_hook_installed(),sk980_maintenance_hook_installed()?'Installed in app/bootstrap.php':'Not installed (optional)');
    return $tests;
}
function sk980_bootstrap_path(): string {return sk980_root().'/app/bootstrap.php';}
function sk980_maintenance_hook_installed(): bool {$p=sk980_bootstrap_path();return is_file($p)&&str_contains((string)@file_get_contents($p),'SHAHKOTPK_V980_MAINTENANCE_HOOK');}
function sk980_install_maintenance_hook(): array {
    $p=sk980_bootstrap_path();if(!is_file($p)||!is_writable($p))throw new RuntimeException('app/bootstrap.php is missing or not writable.');$src=(string)file_get_contents($p);if(str_contains($src,'SHAHKOTPK_V980_MAINTENANCE_HOOK'))return ['installed'=>true,'already'=>true,'backup'=>null];
    $backup=$p.'.pre-v980.bak';if(!is_file($backup)&&!@copy($p,$backup))throw new RuntimeException('Could not create bootstrap backup.');
    $trim=rtrim($src);$hook="\n/* SHAHKOTPK_V980_MAINTENANCE_HOOK */\nrequire_once __DIR__.'/maintenance_guard_v980.php';\nif(function_exists('sk980_maintenance_guard')) sk980_maintenance_guard();\n";
    if(str_ends_with($trim,'?>')){$trim=substr($trim,0,-2);$new=$trim.$hook."?>\n";}else{$new=$trim.$hook;}
    if(@file_put_contents($p,$new,LOCK_EX)===false)throw new RuntimeException('Could not update app/bootstrap.php.');return ['installed'=>true,'already'=>false,'backup'=>$backup];
}
function sk980_uninstall_maintenance_hook(): array {
    $p=sk980_bootstrap_path();$backup=$p.'.pre-v980.bak';if(is_file($backup)){if(!@copy($backup,$p))throw new RuntimeException('Could not restore bootstrap backup.');return ['restored'=>true,'backup'=>$backup];}
    return ['restored'=>false,'message'=>'No v9.8.0 bootstrap backup was found.'];
}
function sk980_events(int $limit=30): array {if(!sk980_table_exists('system_control_events_v980'))return [];try{$q=db()->query('SELECT * FROM system_control_events_v980 ORDER BY id DESC LIMIT '.max(1,min(100,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
?>