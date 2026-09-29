<?php
declare(strict_types=1);

/** ShahkotPK v12.8.0 — safe backup/storage cleanup helpers. */
if (!function_exists('sk1280_root')) {
function sk1280_root(): string { return realpath(__DIR__.'/..') ?: dirname(__DIR__); }
function sk1280_table_exists(string $table): bool {
    try {$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1280_fmt(int|float $bytes): string {
    $n=max(0,(float)$bytes);$u=['B','KB','MB','GB','TB'];$i=0;while($n>=1024&&$i<count($u)-1){$n/=1024;$i++;}
    return number_format($n,$i?1:0).' '.$u[$i];
}
function sk1280_tenant_id(): int {
    try {if(function_exists('current_tenant_id'))return (int)current_tenant_id();} catch(Throwable $e){}
    return 0;
}
function sk1280_backup_helper(): void {
    $p=sk1280_root().'/app/backup_restore_v990.php';
    if(is_file($p))require_once $p;
}
function sk1280_backup_root(): string {
    sk1280_backup_helper();
    if(function_exists('sk990_backup_root')){try{return sk990_backup_root();}catch(Throwable $e){}}
    return sk1280_root().'/storage/backups/shahkotpk-v990';
}
function sk1280_is_inside(string $path,string $root): bool {
    $rp=realpath($path);$rr=realpath($root);if(!$rp||!$rr)return false;
    return $rp===$rr || str_starts_with($rp,$rr.DIRECTORY_SEPARATOR);
}
function sk1280_safe_roots(): array {
    $r=sk1280_root();
    return [
      'backups'=>sk1280_backup_root(),
      'cache'=>$r.'/storage/cache',
      'tmp'=>$r.'/storage/tmp',
      'update_tmp'=>$r.'/storage/update-temp',
      'updates'=>$r.'/storage/updates',
      'updater'=>$r.'/storage/updater',
      'update_archives'=>$r.'/storage/update_archives',
      'logs'=>$r.'/storage/logs'
    ];
}
function sk1280_file_size(string $path): int {return is_file($path)?(int)@filesize($path):0;}
function sk1280_dir_size(string $dir,int $capFiles=20000): int {
    if(!is_dir($dir))return 0;$sum=0;$n=0;
    try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if(++$n>$capFiles)break;if($f->isFile()&&!$f->isLink())$sum+=(int)$f->getSize();}}catch(Throwable $e){}
    return $sum;
}
function sk1280_snapshots(): array {
    if(!sk1280_table_exists('backup_snapshots_v990'))return [];
    try{return db()->query('SELECT * FROM backup_snapshots_v990 ORDER BY id DESC LIMIT 500')->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}
}
function sk1280_snapshot_refs(array $rows): array {
    $refs=[];$root=sk1280_root();
    foreach($rows as $r)foreach(['db_path','files_path'] as $c){$rel=trim((string)($r[$c]??''));if($rel==='')continue;$p=$root.'/'.ltrim(str_replace('\\','/',$rel),'/');$rp=realpath($p);if($rp)$refs[$rp]=true;}
    return $refs;
}
function sk1280_protected_snapshot_ids(array $rows,int $keepLatest=10): array {
    $protect=[];$types=[];$now=time();
    foreach($rows as $i=>$r){$id=(int)($r['id']??0);if(!$id)continue;$ts=strtotime((string)($r['created_at']??''))?:0;$type=(string)($r['snapshot_type']??'');
      if($i<$keepLatest || ($ts && $ts>$now-86400))$protect[$id]=true;
      if(!isset($types[$type]) && in_array($type,['manual','scheduled','pre-update','pre-db-restore','pre-file-restore','scheduled-manual'],true)){$types[$type]=$id;$protect[$id]=true;}
    }
    return $protect;
}
function sk1280_item(string $kind,string $label,string $detail,int $bytes,array $meta=[]): array {
    $seed=$kind.'|'.$label.'|'.json_encode($meta,JSON_UNESCAPED_SLASHES);return ['id'=>substr(hash('sha256',$seed),0,24),'kind'=>$kind,'label'=>$label,'detail'=>$detail,'bytes'=>$bytes,'meta'=>$meta];
}
function sk1280_scan(int $keepLatest=10,int $backupDays=21,int $updateDays=14,int $logDays=30): array {
    $now=time();$root=sk1280_root();$items=[];$protected=[];$rows=sk1280_snapshots();$refs=sk1280_snapshot_refs($rows);$protectIds=sk1280_protected_snapshot_ids($rows,$keepLatest);
    foreach($rows as $r){$id=(int)($r['id']??0);if(!$id)continue;$bytes=(int)($r['db_bytes']??0)+(int)($r['files_bytes']??0);$ts=strtotime((string)($r['created_at']??''))?:$now;$status=strtolower((string)($r['status']??''));
      if(isset($protectIds[$id])){$protected[]=['label'=>'Restore point #'.$id,'reason'=>'Latest/safety restore point','bytes'=>$bytes];continue;}
      $expired=$ts<$now-$backupDays*86400;$failed=in_array($status,['failed','partial','error'],true)&&$ts<$now-3*86400;
      if($expired||$failed){$items[]=sk1280_item('snapshot','Restore point #'.$id,(string)($r['snapshot_type']??'backup').' · '.(string)($r['created_at']??''),$bytes,['snapshot_id'=>$id]);}
    }
    $roots=sk1280_safe_roots();$backupRoot=$roots['backups'];
    if(is_dir($backupRoot)){
      try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backupRoot,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if(!$f->isFile()||$f->isLink())continue;$p=$f->getPathname();$rp=realpath($p);if(!$rp||isset($refs[$rp]))continue;$name=$f->getFilename();$age=$now-(int)$f->getMTime();
        // Only v9.9 snapshot-named orphan files are auto-clean candidates. Unknown backup formats remain protected.
        if($age>7*86400 && preg_match('/^snapshot-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.(?:sql(?:\.gz)?|files\.zip)$/i',$name)){$items[]=sk1280_item('orphan_backup','Orphan backup file',$name,(int)$f->getSize(),['path'=>$rp]);}
      }}catch(Throwable $e){}
      foreach(glob($backupRoot.'/_restore-*')?:[] as $p){if(is_dir($p)&&$now-(int)@filemtime($p)>86400)$items[]=sk1280_item('temp_dir','Old restore temporary folder',basename($p),sk1280_dir_size($p),['path'=>$p]);}
    }
    foreach(['cache','tmp','update_tmp'] as $key){$dir=$roots[$key]??'';if(!is_dir($dir))continue;$bytes=sk1280_dir_size($dir);if($bytes>0)$items[]=sk1280_item('cache_dir',ucwords(str_replace('_',' ',$key)).' files','Regenerable temporary/cache data',$bytes,['path'=>$dir,'key'=>$key]);}
    foreach(['updates','updater','update_archives'] as $key){$dir=$roots[$key]??'';if(!is_dir($dir))continue;$zips=[];foreach(glob($dir.'/*.zip')?:[] as $p){if(is_file($p))$zips[]=$p;}usort($zips,fn($a,$b)=>(int)@filemtime($b)<=>(int)@filemtime($a));foreach($zips as $i=>$p){$age=$now-(int)@filemtime($p);if($i<5||$age<$updateDays*86400){$protected[]=['label'=>basename($p),'reason'=>'Recent updater archive','bytes'=>sk1280_file_size($p)];continue;}$items[]=sk1280_item('updater_zip','Old updater ZIP',basename($p),sk1280_file_size($p),['path'=>$p]);}}
    $logDir=$roots['logs']??'';if(is_dir($logDir)){try{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($logDir,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if(!$f->isFile()||$f->isLink())continue;$name=$f->getFilename();if(!preg_match('/\.(?:log|txt)(?:\.\d+)?$/i',$name))continue;if($now-(int)$f->getMTime()<$logDays*86400)continue;$items[]=sk1280_item('old_log','Old log file',$name,(int)$f->getSize(),['path'=>$f->getPathname()]);}}catch(Throwable $e){}}
    $byKind=[];$reclaim=0;foreach($items as $i){$reclaim+=(int)$i['bytes'];$k=$i['kind'];if(!isset($byKind[$k]))$byKind[$k]=['count'=>0,'bytes'=>0];$byKind[$k]['count']++;$byKind[$k]['bytes']+=(int)$i['bytes'];}
    $backupBytes=is_dir($backupRoot)?sk1280_dir_size($backupRoot):0;
    return ['items'=>$items,'protected'=>$protected,'summary'=>$byKind,'reclaimable'=>$reclaim,'backup_bytes'=>$backupBytes,'snapshot_count'=>count($rows),'settings'=>['keep_latest'=>$keepLatest,'backup_days'=>$backupDays,'update_days'=>$updateDays,'log_days'=>$logDays]];
}
function sk1280_delete_tree_safe(string $dir): int {
    $roots=sk1280_safe_roots();$allowed=false;foreach(['cache','tmp','update_tmp','backups'] as $k){$r=$roots[$k]??'';if($r&&sk1280_is_inside($dir,$r)){$allowed=true;break;}}if(!$allowed||!is_dir($dir))return 0;$bytes=sk1280_dir_size($dir);
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){if($f->isLink()){@unlink($f->getPathname());continue;}if($f->isDir())@rmdir($f->getPathname());else @unlink($f->getPathname());}
    // Keep root directories for cache/tmp; remove only generated restore subdirs.
    if(str_contains(basename($dir),'_restore-'))@rmdir($dir);
    return $bytes;
}
function sk1280_delete_file_safe(string $path,string $kind): int {
    $rp=realpath($path);if(!$rp||!is_file($rp))return 0;$roots=sk1280_safe_roots();$allowed=false;
    $keys=$kind==='old_log'?['logs']:($kind==='updater_zip'?['updates','updater','update_archives']:['backups']);
    foreach($keys as $k){$r=$roots[$k]??'';if($r&&is_dir($r)&&sk1280_is_inside($rp,$r)){$allowed=true;break;}}
    if(!$allowed)return 0;$bytes=(int)@filesize($rp);return @unlink($rp)?$bytes:0;
}
function sk1280_execute(array $selected,?int $uid=null,string $mode='manual',array $scanSettings=[]): array {
    $scan=sk1280_scan((int)($scanSettings['keep_latest']??10),(int)($scanSettings['backup_days']??21),(int)($scanSettings['update_days']??14),(int)($scanSettings['log_days']??30));$map=[];foreach($scan['items'] as $item)$map[$item['id']]=$item;
    $results=[];$freed=0;$deleted=0;sk1280_backup_helper();
    foreach(array_unique(array_map('strval',$selected)) as $id){if(!isset($map[$id])){$results[]=['id'=>$id,'ok'=>false,'message'=>'Item is no longer eligible/protected.'];continue;}$item=$map[$id];$ok=false;$bytes=0;$msg='';try{
      if($item['kind']==='snapshot'){$sid=(int)($item['meta']['snapshot_id']??0);if($sid<1)throw new RuntimeException('Invalid restore point.');if(function_exists('sk990_delete_snapshot')){$before=(int)$item['bytes'];sk990_delete_snapshot($sid,$uid);$bytes=$before;$ok=true;}else{throw new RuntimeException('Backup helper unavailable; restore point was not deleted.');}}
      elseif($item['kind']==='cache_dir'){$bytes=sk1280_delete_tree_safe((string)$item['meta']['path']);$ok=true;}
      elseif($item['kind']==='temp_dir'){$bytes=sk1280_delete_tree_safe((string)$item['meta']['path']);$ok=true;}
      elseif(in_array($item['kind'],['orphan_backup','updater_zip','old_log'],true)){$bytes=sk1280_delete_file_safe((string)$item['meta']['path'],$item['kind']);$ok=$bytes>0;}
      else{$msg='Unsupported cleanup item.';}
    }catch(Throwable $e){$msg=$e->getMessage();}
      if($ok){$deleted++;$freed+=$bytes;$msg=$msg?:'Deleted';}$results[]=['id'=>$id,'kind'=>$item['kind'],'label'=>$item['label'],'ok'=>$ok,'bytes'=>$bytes,'message'=>$msg];
    }
    try{if(sk1280_table_exists('recovery_cleanup_runs_v1280')){db()->prepare('INSERT INTO recovery_cleanup_runs_v1280(tenant_id,user_id,mode,selected_count,deleted_count,bytes_freed,result_json,created_at) VALUES(?,?,?,?,?,?,?,NOW())')->execute([sk1280_tenant_id(),$uid,$mode,count($selected),$deleted,$freed,json_encode($results,JSON_UNESCAPED_SLASHES)]);}}catch(Throwable $e){}
    return ['selected'=>count($selected),'deleted'=>$deleted,'bytes_freed'=>$freed,'results'=>$results];
}
function sk1280_recent_runs(int $limit=10): array {if(!sk1280_table_exists('recovery_cleanup_runs_v1280'))return [];try{$q=db()->query('SELECT * FROM recovery_cleanup_runs_v1280 ORDER BY id DESC LIMIT '.max(1,min(50,$limit)));return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}}
}
