<?php
declare(strict_types=1);

/** ShahkotPK v9.9.0 — Backup, Restore & Rollback helpers. */

function sk990_root(): string { return realpath(__DIR__.'/..') ?: dirname(__DIR__); }
function sk990_backup_root(): string {
    $root=sk990_root();$p=$root.'/storage/backups/shahkotpk-v990';
    if(!is_dir($p) && !@mkdir($p,0750,true) && !is_dir($p)) throw new RuntimeException('Backup directory could not be created.');
    $r=realpath($p);if(!$r||!str_starts_with($r,$root.DIRECTORY_SEPARATOR))throw new RuntimeException('Invalid backup directory.');return $r;
}
function sk990_table_exists(string $t): bool { try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$t]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;} }
function sk990_setting(string $k,string $d=''): string { try{if(function_exists('setting'))return (string)setting($k,$d);if(sk990_table_exists('settings')){$q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$k]);$v=$q->fetchColumn();return $v===false?$d:(string)$v;}}catch(Throwable $e){}return $d; }
function sk990_save_setting(string $k,string $v): void { if(function_exists('save_setting')){save_setting($k,$v);return;}db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$k,$v]); }
function sk990_fmt_bytes(int|float $n): string {$n=max(0,(float)$n);$u=['B','KB','MB','GB','TB'];$i=0;while($n>=1024&&$i<count($u)-1){$n/=1024;$i++;}return number_format($n,$i?2:0).' '.$u[$i];}
function sk990_uid($me=null): ?int { if(is_array($me)&&!empty($me['id']))return (int)$me['id'];try{if(function_exists('current_user')){$u=current_user();if($u&&!empty($u['id']))return (int)$u['id'];}}catch(Throwable $e){}return null; }
function sk990_audit(string $event,string $summary,array $meta=[],?int $uid=null,string $severity='info'): void {
    try{if(function_exists('sk980_audit')){sk980_audit('backup.'.$event,$summary,$meta,$uid,$severity);return;}if(function_exists('tenant_audit'))tenant_audit('backup.'.$event,'backup_restore',0,$summary,$meta);}catch(Throwable $e){}
}
function sk990_snapshot_row(int $id): ?array {if($id<1||!sk990_table_exists('backup_snapshots_v990'))return null;$q=db()->prepare('SELECT * FROM backup_snapshots_v990 WHERE id=? LIMIT 1');$q->execute([$id]);$r=$q->fetch(PDO::FETCH_ASSOC);return $r?:null;}
function sk990_snapshots(int $limit=100): array {if(!sk990_table_exists('backup_snapshots_v990'))return [];try{$q=db()->query('SELECT * FROM backup_snapshots_v990 ORDER BY id DESC LIMIT '.max(1,min(250,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
function sk990_record(array $r): int {
    if(!sk990_table_exists('backup_snapshots_v990'))throw new RuntimeException('Run the v9.9.0 database migration first.');
    $q=db()->prepare('INSERT INTO backup_snapshots_v990(snapshot_key,snapshot_type,status,app_version,db_path,files_path,db_checksum,files_checksum,db_bytes,files_bytes,remote_profile_id,remote_status,remote_meta_json,meta_json,created_by,created_at,completed_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute([$r['snapshot_key'],$r['snapshot_type']??'manual',$r['status']??'completed',$r['app_version']??sk990_setting('installed_app_version','unknown'),$r['db_path']??null,$r['files_path']??null,$r['db_checksum']??null,$r['files_checksum']??null,$r['db_bytes']??0,$r['files_bytes']??0,$r['remote_profile_id']??null,$r['remote_status']??null,isset($r['remote_meta'])?json_encode($r['remote_meta'],JSON_UNESCAPED_SLASHES):null,isset($r['meta'])?json_encode($r['meta'],JSON_UNESCAPED_SLASHES):null,$r['created_by']??null,date('Y-m-d H:i:s'),$r['completed_at']??date('Y-m-d H:i:s')]);return (int)db()->lastInsertId();
}
function sk990_rel(string $path): string {$root=sk990_root();$r=realpath($path)?:$path;return ltrim(str_replace('\\','/',substr($r,strlen($root))),'/');}
function sk990_abs_backup_path(?string $rel): ?string {if(!$rel)return null;$root=sk990_root();$p=$root.'/'.ltrim(str_replace('\\','/',$rel),'/');$rr=realpath($p);$br=realpath(sk990_backup_root());if(!$rr||!$br||!str_starts_with($rr,$br.DIRECTORY_SEPARATOR))return null;return $rr;}
function sk990_write($h,string $s): void {if(is_resource($h)){if(@gzwrite($h,$s)===false)throw new RuntimeException('Could not write compressed backup.');}else{if(@fwrite($h,$s)===false)throw new RuntimeException('Could not write backup.');}}
function sk990_quote_value($v,string $type=''): string {
    if($v===null)return 'NULL';$type=strtolower($type);
    if(in_array($type,['blob','tinyblob','mediumblob','longblob','binary','varbinary','bit'],true))return '0x'.bin2hex((string)$v);
    return db()->quote((string)$v);
}
function sk990_database_backup(string $key): array {
    @set_time_limit(0);$dir=sk990_backup_root();$gzip=function_exists('gzopen');$file=$dir.'/'.$key.'.sql'.($gzip?'.gz':'');$h=$gzip?@gzopen($file,'wb6'):@fopen($file,'wb');if(!$h)throw new RuntimeException('Could not create database backup file.');
    $tables=[];$written=0;
    try{
      sk990_write($h,"-- ShahkotPK v9.9.0 database backup\n-- Created: ".date(DATE_ATOM)."\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");
      $q=db()->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME<>'backup_snapshots_v990' ORDER BY TABLE_NAME");
      $tables=$q->fetchAll(PDO::FETCH_COLUMN)?:[];
      foreach($tables as $table){
        if(!preg_match('/^[A-Za-z0-9_]+$/',(string)$table))continue;
        $qt='`'.str_replace('`','``',(string)$table).'`';
        $cr=db()->query('SHOW CREATE TABLE '.$qt)->fetch(PDO::FETCH_NUM);$create=(string)($cr[1]??'');if($create==='')continue;
        sk990_write($h,"\n-- TABLE ".$table."\nDROP TABLE IF EXISTS ".$qt.";\n".$create.";\n");
        $types=[];$cq=db()->prepare('SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');$cq->execute([$table]);foreach($cq->fetchAll(PDO::FETCH_ASSOC)?:[] as $c)$types[(string)$c['COLUMN_NAME']]=(string)$c['DATA_TYPE'];
        $rows=db()->query('SELECT * FROM '.$qt,PDO::FETCH_ASSOC);$batch=[];$cols=null;
        while($row=$rows->fetch(PDO::FETCH_ASSOC)){
          if($cols===null)$cols=array_keys($row);$vals=[];foreach($row as $c=>$v)$vals[]=sk990_quote_value($v,$types[$c]??'');$batch[]='('.implode(',',$vals).')';
          if(count($batch)>=100){sk990_write($h,'INSERT INTO '.$qt.' (`'.implode('`,`',$cols).'`) VALUES '.implode(',',$batch).";\n");$written+=count($batch);$batch=[];}
        }
        if($batch&&$cols){sk990_write($h,'INSERT INTO '.$qt.' (`'.implode('`,`',$cols).'`) VALUES '.implode(',',$batch).";\n");$written+=count($batch);}
      }
      sk990_write($h,"\nSET FOREIGN_KEY_CHECKS=1;\n");
    }finally{if(is_resource($h)){if($gzip)@gzclose($h);else @fclose($h);}}
    clearstatcache(true,$file);return ['path'=>$file,'relative'=>sk990_rel($file),'bytes'=>(int)@filesize($file),'checksum'=>(string)@hash_file('sha256',$file),'tables'=>count($tables),'rows'=>$written];
}
function sk990_default_code_paths(): array {return ['app','admin','assets','database','cron','config'];}
function sk990_file_backup(string $key,bool $includeUploads=false,bool $includeRootPhp=true): array {
    @set_time_limit(0);if(!class_exists('ZipArchive'))throw new RuntimeException('PHP ZipArchive extension is required for file backups.');$root=sk990_root();$file=sk990_backup_root().'/'.$key.'.files.zip';$zip=new ZipArchive();if($zip->open($file,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true)throw new RuntimeException('Could not create file backup ZIP.');
    $paths=sk990_default_code_paths();if($includeUploads)$paths[]='uploads';$count=0;$bytes=0;$backupRoot=realpath(sk990_backup_root());
    $addFile=function(string $abs,string $rel)use($zip,&$count,&$bytes,$backupRoot){$rr=realpath($abs);if(!$rr||!is_file($rr)||is_link($rr))return;if($backupRoot&&str_starts_with($rr,$backupRoot.DIRECTORY_SEPARATOR))return;$zip->addFile($rr,str_replace('\\','/',$rel));$count++;$bytes+=(int)@filesize($rr);};
    foreach($paths as $rel){$abs=$root.'/'.$rel;if(!is_dir($abs))continue;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($abs,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if(!$f->isFile()||$f->isLink())continue;$rr=$f->getPathname();if($backupRoot&&str_starts_with((realpath($rr)?:$rr),$backupRoot.DIRECTORY_SEPARATOR))continue;$r=ltrim(str_replace('\\','/',substr($rr,strlen($root))),'/');if(preg_match('#/(cache|logs?)/#i','/'.$r.'/')&&str_starts_with($r,'storage/'))continue;$addFile($rr,$r);}}
    if($includeRootPhp){foreach(glob($root.'/*.php')?:[] as $p)$addFile($p,basename($p));foreach(['.htaccess','composer.json','composer.lock'] as $n)if(is_file($root.'/'.$n))$addFile($root.'/'.$n,$n);}
    $manifest=['created_at'=>date(DATE_ATOM),'app_version'=>sk990_setting('installed_app_version','unknown'),'include_uploads'=>$includeUploads,'source_files'=>$count,'source_bytes'=>$bytes];$zip->addFromString('_shahkotpk_backup_manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));$zip->close();clearstatcache(true,$file);
    return ['path'=>$file,'relative'=>sk990_rel($file),'bytes'=>(int)@filesize($file),'source_bytes'=>$bytes,'checksum'=>(string)@hash_file('sha256',$file),'files'=>$count];
}
function sk990_create_snapshot(string $type='manual',bool $dbBackup=true,bool $fileBackup=true,bool $uploads=false,?int $uid=null,bool $remote=true): array {
    @set_time_limit(0);$key='snapshot-'.date('Ymd-His').'-'.substr(bin2hex(random_bytes(4)),0,8);$dbInfo=null;$filesInfo=null;$errors=[];
    try{if($dbBackup)$dbInfo=sk990_database_backup($key);}catch(Throwable $e){$errors[]='Database: '.$e->getMessage();}
    try{if($fileBackup)$filesInfo=sk990_file_backup($key,$uploads,true);}catch(Throwable $e){$errors[]='Files: '.$e->getMessage();}
    if(!$dbInfo&&!$filesInfo)throw new RuntimeException('Backup failed: '.implode(' | ',$errors));
    $row=['snapshot_key'=>$key,'snapshot_type'=>$type,'status'=>$errors?'partial':'completed','db_path'=>$dbInfo['relative']??null,'files_path'=>$filesInfo['relative']??null,'db_checksum'=>$dbInfo['checksum']??null,'files_checksum'=>$filesInfo['checksum']??null,'db_bytes'=>$dbInfo['bytes']??0,'files_bytes'=>$filesInfo['bytes']??0,'created_by'=>$uid,'meta'=>['errors'=>$errors,'db'=>$dbInfo,'files'=>$filesInfo,'uploads'=>$uploads]];$id=sk990_record($row);$remoteInfo=null;
    if($remote){try{$remoteInfo=sk990_send_snapshot_remote($id,$uid);if($remoteInfo){} }catch(Throwable $e){$errors[]='Remote: '.$e->getMessage();}}
    sk990_audit('created','Backup snapshot created',['snapshot_id'=>$id,'key'=>$key,'type'=>$type,'errors'=>$errors],$uid,$errors?'warning':'info');return ['id'=>$id,'key'=>$key,'db'=>$dbInfo,'files'=>$filesInfo,'remote'=>$remoteInfo,'errors'=>$errors];
}
function sk990_verify_snapshot(int $id): array {$r=sk990_snapshot_row($id);if(!$r)throw new RuntimeException('Snapshot not found.');$out=['id'=>$id,'ok'=>true,'items'=>[]];foreach([['db_path','db_checksum'],['files_path','files_checksum']] as [$pcol,$ccol]){if(empty($r[$pcol]))continue;$p=sk990_abs_backup_path((string)$r[$pcol]);$exists=$p&&is_file($p);$actual=$exists?(string)hash_file('sha256',$p):'';$ok=$exists&&hash_equals((string)$r[$ccol],$actual);$out['items'][]=['file'=>$r[$pcol],'exists'=>$exists,'checksum_ok'=>$ok,'size'=>$exists?(int)filesize($p):0];if(!$ok)$out['ok']=false;}return $out;}
function sk990_sql_statements(string $file): Generator {
    $gz=str_ends_with(strtolower($file),'.gz');$h=$gz?@gzopen($file,'rb'):@fopen($file,'rb');if(!$h)throw new RuntimeException('Backup file cannot be opened.');$buffer='';$quote=null;$escape=false;
    try{
      while($gz?!gzeof($h):!feof($h)){
        $line=$gz?gzgets($h):fgets($h);if($line===false)break;
        if($quote===null&&trim($buffer)===''&&str_starts_with(ltrim($line),'--'))continue;
        $len=strlen($line);
        for($i=0;$i<$len;$i++){
          $ch=$line[$i];$buffer.=$ch;
          if($quote!==null){
            if($escape){$escape=false;continue;}
            if($ch==='\\'&&$quote!=="`"){$escape=true;continue;}
            if($ch===$quote){if($i+1<$len&&$line[$i+1]===$quote){$buffer.=$line[++$i];continue;}$quote=null;}
            continue;
          }
          if($ch==="'"||$ch==='"'||$ch==='`'){$quote=$ch;continue;}
          if($ch===';'){$stmt=trim($buffer);$buffer='';if($stmt!==''&&$stmt!==';')yield $stmt;}
        }
      }
      if(trim($buffer)!=='')yield trim($buffer);
    }finally{if($gz)@gzclose($h);else @fclose($h);}
}
function sk990_restore_database(int $id,?int $uid=null): array {
    @set_time_limit(0);$r=sk990_snapshot_row($id);if(!$r||empty($r['db_path']))throw new RuntimeException('Database backup is unavailable.');$v=sk990_verify_snapshot($id);$dbok=false;foreach($v['items'] as $it)if($it['file']===$r['db_path'])$dbok=(bool)$it['checksum_ok'];if(!$dbok)throw new RuntimeException('Database backup checksum verification failed.');
    $safety=sk990_create_snapshot('pre-db-restore',true,false,false,$uid,false);$p=sk990_abs_backup_path((string)$r['db_path']);if(!$p)throw new RuntimeException('Database backup file is outside the protected backup directory.');$n=0;db()->exec('SET FOREIGN_KEY_CHECKS=0');try{foreach(sk990_sql_statements($p) as $sql){db()->exec($sql);$n++;}}finally{try{db()->exec('SET FOREIGN_KEY_CHECKS=1');}catch(Throwable $e){}}
    sk990_audit('db_restored','Database restored from snapshot',['snapshot_id'=>$id,'statements'=>$n,'safety_snapshot'=>$safety['id']??null],$uid,'warning');return ['snapshot_id'=>$id,'statements'=>$n,'safety_snapshot'=>$safety['id']??null];
}
function sk990_zip_safe_entries(ZipArchive $zip): array {$out=[];for($i=0;$i<$zip->numFiles;$i++){$name=(string)$zip->getNameIndex($i);$norm=str_replace('\\','/',$name);if($norm===''||str_starts_with($norm,'/')||str_contains($norm,'../')||preg_match('#^[A-Za-z]:/#',$norm))throw new RuntimeException('Unsafe path detected in file backup.');if($norm==='_shahkotpk_backup_manifest.json')continue;$out[]=$norm;}return $out;}
function sk990_restore_files(int $id,?int $uid=null): array {
    @set_time_limit(0);if(!class_exists('ZipArchive'))throw new RuntimeException('PHP ZipArchive extension is required.');$r=sk990_snapshot_row($id);if(!$r||empty($r['files_path']))throw new RuntimeException('File backup is unavailable.');$v=sk990_verify_snapshot($id);$ok=false;foreach($v['items'] as $it)if($it['file']===$r['files_path'])$ok=(bool)$it['checksum_ok'];if(!$ok)throw new RuntimeException('File backup checksum verification failed.');
    $safety=sk990_create_snapshot('pre-file-restore',false,true,false,$uid,false);$p=sk990_abs_backup_path((string)$r['files_path']);$zip=new ZipArchive();if($zip->open($p)!==true)throw new RuntimeException('Could not open file backup.');$entries=sk990_zip_safe_entries($zip);$root=sk990_root();$tmp=sk990_backup_root().'/_restore-'.bin2hex(random_bytes(4));@mkdir($tmp,0750,true);if(!$zip->extractTo($tmp)){$zip->close();throw new RuntimeException('Could not extract restore archive.');}$zip->close();$copied=0;
    try{foreach($entries as $rel){$src=$tmp.'/'.$rel;if(!is_file($src))continue;$dst=$root.'/'.$rel;$parent=dirname($dst);if(!is_dir($parent)&&!@mkdir($parent,0755,true)&&!is_dir($parent))throw new RuntimeException('Cannot create restore directory: '.$rel);if(!@copy($src,$dst))throw new RuntimeException('Could not restore file: '.$rel);$copied++;}}finally{sk990_delete_tree($tmp);}
    if(function_exists('opcache_reset'))@opcache_reset();sk990_audit('files_restored','Project files restored from snapshot',['snapshot_id'=>$id,'files'=>$copied,'safety_snapshot'=>$safety['id']??null],$uid,'warning');return ['snapshot_id'=>$id,'files'=>$copied,'safety_snapshot'=>$safety['id']??null];
}
function sk990_delete_tree(string $dir): void {if(!is_dir($dir))return;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){if($f->isDir()&&!$f->isLink())@rmdir($f->getPathname());else @unlink($f->getPathname());}@rmdir($dir);}
function sk990_delete_snapshot(int $id,?int $uid=null): array {$r=sk990_snapshot_row($id);if(!$r)throw new RuntimeException('Snapshot not found.');$deleted=[];foreach(['db_path','files_path'] as $c){$p=sk990_abs_backup_path((string)($r[$c]??''));if($p&&is_file($p)&&@unlink($p))$deleted[]=$r[$c];}db()->prepare('DELETE FROM backup_snapshots_v990 WHERE id=?')->execute([$id]);sk990_audit('deleted','Backup snapshot deleted',['snapshot_id'=>$id,'files'=>$deleted],$uid,'warning');return ['id'=>$id,'deleted'=>$deleted];}
function sk990_storage_helper(): bool {$p=sk990_root().'/app/storage_manager_v970.php';if(!is_file($p))return false;require_once $p;return function_exists('sk970_upload')&&function_exists('sk970_routes')&&function_exists('sk970_profile');}
function sk990_send_snapshot_remote(int $id,?int $uid=null): ?array {
    if(!sk990_storage_helper())return null;$routes=sk970_routes();$route=$routes['backups']??null;if(!$route||empty($route['enabled'])||empty($route['profile_id']))return null;$profile=sk970_profile((int)$route['profile_id'],true);if(!$profile||empty($profile['enabled']))return null;$r=sk990_snapshot_row($id);if(!$r)return null;$prefix=(string)($route['path_prefix']??'backups');$uploaded=[];
    foreach(['db_path','files_path'] as $c){if(empty($r[$c]))continue;$p=sk990_abs_backup_path((string)$r[$c]);if(!$p)continue;$res=sk970_upload($profile,'backups',basename($p),$p,$prefix);$uploaded[]=['file'=>basename($p),'key'=>$res['key']??null,'public_url'=>$res['public_url']??null];}
    db()->prepare('UPDATE backup_snapshots_v990 SET remote_profile_id=?,remote_status=?,remote_meta_json=? WHERE id=?')->execute([(int)$profile['id'],'uploaded',json_encode($uploaded,JSON_UNESCAPED_SLASHES),$id]);sk990_audit('remote_uploaded','Backup copied to configured Storage Center profile',['snapshot_id'=>$id,'profile_id'=>$profile['id'],'items'=>$uploaded],$uid);return ['profile'=>$profile['name']??('Profile #'.$profile['id']),'items'=>$uploaded];
}
function sk990_cleanup_retention(?int $uid=null): array {$days=max(1,(int)sk990_setting('backup_retention_days_v990','30'));$keep=max(1,(int)sk990_setting('backup_retention_count_v990','10'));$rows=sk990_snapshots(250);$cut=time()-$days*86400;$deleted=0;$kept=0;foreach($rows as $i=>$r){$ts=strtotime((string)$r['created_at'])?:time();$protected=in_array((string)$r['snapshot_type'],['pre-db-restore','pre-file-restore'],true)&&$ts>time()-86400;if($protected){$kept++;continue;}if($i<$keep||$ts>=$cut){$kept++;continue;}try{sk990_delete_snapshot((int)$r['id'],$uid);$deleted++;}catch(Throwable $e){}}return ['deleted'=>$deleted,'kept'=>$kept,'days'=>$days,'count'=>$keep];}
function sk990_schedule_settings(): array {return ['enabled'=>sk990_setting('backup_schedule_enabled_v990','0')==='1','frequency'=>sk990_setting('backup_schedule_frequency_v990','daily'),'include_files'=>sk990_setting('backup_schedule_files_v990','1')==='1','include_uploads'=>sk990_setting('backup_schedule_uploads_v990','0')==='1','remote'=>sk990_setting('backup_schedule_remote_v990','1')==='1','last_run'=>sk990_setting('backup_schedule_last_run_v990','')];}
function sk990_schedule_due(): bool {$s=sk990_schedule_settings();if(!$s['enabled'])return false;$last=$s['last_run']?strtotime($s['last_run']):0;$gap=$s['frequency']==='weekly'?7*86400:86400;return !$last||time()-$last>=$gap;}
function sk990_run_scheduled(): array {$s=sk990_schedule_settings();if(!$s['enabled'])return ['skipped'=>true,'reason'=>'disabled'];if(!sk990_schedule_due())return ['skipped'=>true,'reason'=>'not_due'];$r=sk990_create_snapshot('scheduled',true,$s['include_files'],$s['include_uploads'],null,$s['remote']);sk990_save_setting('backup_schedule_last_run_v990',date('Y-m-d H:i:s'));$r['retention']=sk990_cleanup_retention(null);return $r;}
function sk990_hook_path(): string {return sk990_root().'/app/bootstrap.php';}
function sk990_update_hook_installed(): bool {$p=sk990_hook_path();return is_file($p)&&str_contains((string)@file_get_contents($p),'SHAHKOTPK_V990_PRE_UPDATE_SNAPSHOT_HOOK');}
function sk990_install_update_hook(): array {$p=sk990_hook_path();if(!is_file($p)||!is_writable($p))throw new RuntimeException('app/bootstrap.php is missing or not writable.');$src=(string)file_get_contents($p);if(str_contains($src,'SHAHKOTPK_V990_PRE_UPDATE_SNAPSHOT_HOOK'))return ['installed'=>true,'already'=>true];$backup=$p.'.pre-v990.bak';if(!is_file($backup)&&!@copy($p,$backup))throw new RuntimeException('Could not create bootstrap backup.');$hook="\n/* SHAHKOTPK_V990_PRE_UPDATE_SNAPSHOT_HOOK */\nrequire_once __DIR__.'/update_snapshot_guard_v990.php';\nif(function_exists('sk990_pre_update_guard')) sk990_pre_update_guard();\n";$trim=rtrim($src);$new=str_ends_with($trim,'?>')?substr($trim,0,-2).$hook."?>\n":$trim.$hook;if(@file_put_contents($p,$new,LOCK_EX)===false)throw new RuntimeException('Could not install update snapshot hook.');return ['installed'=>true,'already'=>false,'backup'=>$backup];}
function sk990_uninstall_update_hook(): array {$p=sk990_hook_path();$backup=$p.'.pre-v990.bak';if(is_file($backup)){if(!@copy($backup,$p))throw new RuntimeException('Could not restore bootstrap backup.');return ['restored'=>true];}return ['restored'=>false,'message'=>'No v9.9.0 bootstrap backup found.'];}
?>