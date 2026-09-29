<?php
declare(strict_types=1);

function updater_root(): string {
    $root=realpath(__DIR__.'/..');
    if(!$root) throw new RuntimeException('Application root could not be resolved.');
    return $root;
}

function updater_log(string $message): void {
    try{
        $dir=updater_root().'/storage/logs';
        if(!is_dir($dir)) @mkdir($dir,0755,true);
        @file_put_contents($dir.'/updater.log','['.date('c').'] '.$message.PHP_EOL,FILE_APPEND|LOCK_EX);
    }catch(Throwable $e){}
}

function safe_update_path(string $p): bool {
    $p=str_replace('\\','/',$p);
    return $p!=='' && !str_starts_with($p,'/') && !str_contains($p,'../') && !preg_match('/^[A-Za-z]:\//',$p) && !str_contains($p,"\0");
}

function updater_setting_bool(string $key,bool $default): bool {
    try{return setting_bool($key,$default);}catch(Throwable $e){return $default;}
}
function updater_setting_int(string $key,int $default): int {
    try{return setting_int($key,$default);}catch(Throwable $e){return $default;}
}

function installed_app_version(): string {
    global $config;
    try{
        $saved=trim((string)setting('installed_app_version',''));
        if($saved!=='') return $saved;
    }catch(Throwable $e){}

    $version=(string)($config['version']??'1.0.0');
    try{
        $latest=db()->query("SELECT version FROM system_updates WHERE status='success' ORDER BY id DESC LIMIT 1")->fetchColumn();
        if($latest) $version=(string)$latest;
    }catch(Throwable $e){}
    return $version;
}

function updater_set_installed_version(string $version): void {
    try{save_setting('installed_app_version',$version);}catch(Throwable $e){updater_log('Could not persist installed version: '.$e->getMessage());}
}

function find_update_manifest(ZipArchive $z): array {
    $idx=$z->locateName('update.json',ZipArchive::FL_NOCASE);
    if($idx!==false) return [$idx,''];
    for($i=0;$i<$z->numFiles;$i++){
        $name=str_replace('\\','/',$z->getNameIndex($i));
        if(strtolower(basename($name))==='update.json') return [$i,substr($name,0,-strlen(basename($name)))];
    }
    throw new RuntimeException('update.json is missing. Upload an updater-compatible ZIP.');
}

function ensure_recovery_tables(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS system_backups (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      backup_key CHAR(32) NOT NULL UNIQUE,
      file_name VARCHAR(255) NOT NULL,
      backup_path VARCHAR(700) NOT NULL,
      source_version VARCHAR(50) NOT NULL,
      backup_type VARCHAR(40) NOT NULL DEFAULT 'manual_system',
      includes_database TINYINT(1) NOT NULL DEFAULT 1,
      includes_uploads TINYINT(1) NOT NULL DEFAULT 0,
      includes_config TINYINT(1) NOT NULL DEFAULT 1,
      file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
      checksum_sha256 CHAR(64) NULL,
      status VARCHAR(30) NOT NULL DEFAULT 'ready',
      notes TEXT NULL,
      created_by BIGINT UNSIGNED NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      restored_at DATETIME NULL,
      INDEX idx_system_backups_created (created_at),
      INDEX idx_system_backups_type (backup_type),
      INDEX idx_system_backups_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function updater_backup_dir(): string {
    $dir=updater_root().'/storage/backups';
    if(!is_dir($dir) && !mkdir($dir,0750,true)) throw new RuntimeException('Unable to create backup directory.');
    $guard=$dir.'/.htaccess';
    if(!is_file($guard)) @file_put_contents($guard,"Options -Indexes\n<FilesMatch \\\"\\.(zip|sql|json|log)$\\\">\nRequire all denied\n</FilesMatch>\n",LOCK_EX);
    return $dir;
}

function updater_temp_dir(string $prefix='recovery'): string {
    $base=updater_root().'/storage/tmp';
    if(!is_dir($base) && !mkdir($base,0750,true)) $base=sys_get_temp_dir();
    $dir=$base.'/shahkotpk_'.$prefix.'_'.bin2hex(random_bytes(6));
    if(!mkdir($dir,0700,true)) throw new RuntimeException('Unable to create temporary directory.');
    return $dir;
}

function updater_remove_tree(string $dir): void {
    if(!is_dir($dir)) return;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $f){$path=$f->getPathname();if($f->isDir())@rmdir($path);else @unlink($path);}
    @rmdir($dir);
}

function updater_sql_ident(string $name): string {return '`'.str_replace('`','``',$name).'`';}

function create_database_dump(string $target): array {
    $pdo=db();
    $fp=fopen($target,'wb');
    if(!$fp) throw new RuntimeException('Unable to create database backup file.');
    $tables=[];$rowsTotal=0;
    try{
        fwrite($fp,"-- ShahkotPK database backup\n-- Created: ".date('c')."\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        $q=$pdo->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'");
        while($r=$q->fetch(PDO::FETCH_NUM)) if(!empty($r[0])) $tables[]=(string)$r[0];

        foreach($tables as $table){
            $ident=updater_sql_ident($table);
            $create=$pdo->query("SHOW CREATE TABLE {$ident}")->fetch(PDO::FETCH_NUM);
            if(!$create || empty($create[1])) continue;
            fwrite($fp,"\n-- Table: {$table}\nDROP TABLE IF EXISTS {$ident};\n".$create[1].";\n");

            $columns=[];
            foreach($pdo->query("SHOW COLUMNS FROM {$ident}")->fetchAll(PDO::FETCH_ASSOC) as $col) $columns[]=(string)$col['Field'];
            if(!$columns) continue;
            $columnSql=implode(',',array_map('updater_sql_ident',$columns));

            $data=$pdo->query("SELECT * FROM {$ident}");
            $batch=[];
            while($row=$data->fetch(PDO::FETCH_ASSOC)){
                $vals=[];
                foreach($columns as $c){
                    $v=$row[$c]??null;
                    $vals[]=$v===null?'NULL':$pdo->quote((string)$v);
                }
                $batch[]='('.implode(',',$vals).')';$rowsTotal++;
                if(count($batch)>=100){
                    fwrite($fp,"INSERT INTO {$ident} ({$columnSql}) VALUES\n".implode(",\n",$batch).";\n");$batch=[];
                }
            }
            if($batch) fwrite($fp,"INSERT INTO {$ident} ({$columnSql}) VALUES\n".implode(",\n",$batch).";\n");
        }
        fwrite($fp,"\nSET FOREIGN_KEY_CHECKS=1;\n");
    }finally{fclose($fp);}
    return ['tables'=>count($tables),'rows'=>$rowsTotal,'bytes'=>is_file($target)?filesize($target):0];
}

function restore_database_dump(string $sqlFile): void {
    if(!is_file($sqlFile)) throw new RuntimeException('Database backup is missing from restore point.');
    $root=updater_root();
    require_once $root.'/app/migrations.php';
    $sql=file_get_contents($sqlFile);
    if($sql===false) throw new RuntimeException('Unable to read database backup.');
    $pdo=db();
    try{
        foreach(split_sql_statements($sql) as $stmt){
            $stmt=trim($stmt);if($stmt==='')continue;$pdo->exec($stmt);
        }
    }catch(Throwable $e){
        try{$pdo->exec('SET FOREIGN_KEY_CHECKS=1');}catch(Throwable $ignored){}
        throw new RuntimeException('Database restore failed: '.$e->getMessage(),0,$e);
    }
}

function updater_should_exclude_from_backup(string $rel,bool $includeUploads,bool $includeConfig): bool {
    $rel=str_replace('\\','/',$rel);
    if(str_starts_with($rel,'storage/backups/')) return true;
    if(str_starts_with($rel,'storage/updates/')) return true;
    if(str_starts_with($rel,'storage/tmp/')) return true;
    if(str_starts_with($rel,'storage/logs/')) return true;
    if(str_starts_with($rel,'storage/')) return true;
    if(!$includeUploads && str_starts_with($rel,'uploads/')) return true;
    if(!$includeConfig && $rel==='config/config.php') return true;
    return false;
}

function register_backup_record(array $meta,string $path,string $typeOverride=''): array {
    ensure_recovery_tables();
    $key=preg_replace('/[^a-f0-9]/','',strtolower((string)($meta['backup_key']??'')));
    if(strlen($key)!==32) $key=bin2hex(random_bytes(16));
    $type=$typeOverride!==''?$typeOverride:(string)($meta['backup_type']??'uploaded');
    $version=(string)($meta['source_version']??'unknown');
    $checksum=is_file($path)?hash_file('sha256',$path):null;
    $size=is_file($path)?(int)filesize($path):0;
    $name=basename($path);
    $includesDb=!empty($meta['includes_database'])?1:0;
    $includesUploads=!empty($meta['includes_uploads'])?1:0;
    $includesConfig=array_key_exists('includes_config',$meta)?(!empty($meta['includes_config'])?1:0):1;
    $notes=(string)($meta['notes']??'');
    $user=current_user();$uid=(int)($user['id']??0);

    $q=db()->prepare("SELECT * FROM system_backups WHERE backup_path=? OR backup_key=? LIMIT 1");$q->execute([$path,$key]);$existing=$q->fetch();
    if($existing) return $existing;
    db()->prepare("INSERT INTO system_backups(backup_key,file_name,backup_path,source_version,backup_type,includes_database,includes_uploads,includes_config,file_size,checksum_sha256,status,notes,created_by) VALUES(?,?,?,?,?,?,?,?,?,?, 'ready',?,?)")
      ->execute([$key,$name,$path,$version,$type,$includesDb,$includesUploads,$includesConfig,$size,$checksum,$notes,$uid?:null]);
    return backup_record_by_key($key)?:[];
}

function create_system_backup(string $type='manual_system',string $notes='',bool $includeUploads=false,bool $includeDatabase=true,bool $includeConfig=true): array {
    if(!class_exists('ZipArchive')) throw new RuntimeException('PHP ZIP extension is required for backups.');
    @set_time_limit(0);
    ensure_recovery_tables();
    $root=updater_root();$dir=updater_backup_dir();$version=installed_app_version();
    $key=bin2hex(random_bytes(16));
    $safeVersion=preg_replace('/[^A-Za-z0-9._-]/','_',$version);
    $fileName='ShahkotPK_'.date('Ymd_His').'_'.$type.'_v'.$safeVersion.'_'.substr($key,0,8).'.zip';
    $path=$dir.'/'.$fileName;
    $tmp=updater_temp_dir('backup');$dbInfo=['tables'=>0,'rows'=>0,'bytes'=>0];
    $zip=new ZipArchive();$applicationFiles=[];$allFiles=[];

    try{
        if($zip->open($path,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new RuntimeException('Could not create backup ZIP.');
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
        foreach($it as $f){
            if(!$f->isFile() || $f->isLink()) continue;
            $rel=str_replace('\\','/',substr($f->getPathname(),strlen($root)+1));
            if(updater_should_exclude_from_backup($rel,$includeUploads,$includeConfig)) continue;
            if(!$zip->addFile($f->getPathname(),'files/'.$rel)) throw new RuntimeException('Failed to add '.$rel.' to backup.');
            $allFiles[]=$rel;
            if(!str_starts_with($rel,'uploads/') && $rel!=='config/config.php') $applicationFiles[]=$rel;
        }

        if($includeDatabase){
            $dbFile=$tmp.'/database.sql';$dbInfo=create_database_dump($dbFile);
            if(!$zip->addFile($dbFile,'database/database.sql')) throw new RuntimeException('Failed to add database backup.');
        }

        $manifest=[
            'format'=>'ShahkotPK-System-Backup','format_version'=>1,'app'=>'ShahkotPK','backup_key'=>$key,
            'created_at'=>date('c'),'source_version'=>$version,'backup_type'=>$type,
            'includes_database'=>$includeDatabase,'includes_uploads'=>$includeUploads,'includes_config'=>$includeConfig,
            'application_files'=>$applicationFiles,'file_count'=>count($allFiles),'database'=>$dbInfo,'notes'=>$notes
        ];
        $zip->addFromString('backup.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $zip->close();

        $record=register_backup_record($manifest,$path,$type);
        updater_log("Created {$type} backup {$fileName} for v{$version}");
        prune_automatic_backups();
        return $record;
    }catch(Throwable $e){
        try{$zip->close();}catch(Throwable $ignored){}
        if(is_file($path)) @unlink($path);
        throw $e;
    }finally{updater_remove_tree($tmp);}
}

function inspect_backup_zip(string $path): array {
    if(!class_exists('ZipArchive')) throw new RuntimeException('PHP ZIP extension is required.');
    if(!is_file($path)) throw new RuntimeException('Backup file does not exist.');
    $z=new ZipArchive();if($z->open($path)!==true) throw new RuntimeException('Invalid backup ZIP.');
    try{
        $idx=$z->locateName('backup.json',ZipArchive::FL_NOCASE);
        if($idx===false) throw new RuntimeException('backup.json is missing. This is not a ShahkotPK full backup package.');
        $meta=json_decode((string)$z->getFromIndex($idx),true);
        if(!is_array($meta)||($meta['format']??'')!=='ShahkotPK-System-Backup'||($meta['app']??'')!=='ShahkotPK') throw new RuntimeException('Invalid ShahkotPK backup manifest.');
        for($i=0;$i<$z->numFiles;$i++){
            $name=str_replace('\\','/',$z->getNameIndex($i));
            if($name===''||str_ends_with($name,'/')) continue;
            if(!safe_update_path($name)) throw new RuntimeException('Unsafe path in backup ZIP: '.$name);
            if($name!=='backup.json' && !str_starts_with($name,'files/') && $name!=='database/database.sql') throw new RuntimeException('Unsupported item in backup ZIP: '.$name);
        }
        return $meta;
    }finally{$z->close();}
}

function backup_record_by_key(string $key): ?array {
    ensure_recovery_tables();
    $q=db()->prepare("SELECT * FROM system_backups WHERE backup_key=? LIMIT 1");$q->execute([$key]);return $q->fetch()?:null;
}
function backup_record_by_path(string $path): ?array {
    ensure_recovery_tables();$q=db()->prepare("SELECT * FROM system_backups WHERE backup_path=? LIMIT 1");$q->execute([$path]);return $q->fetch()?:null;
}
function system_backups(int $limit=100): array {
    ensure_recovery_tables();$limit=max(1,min(500,$limit));return db()->query("SELECT * FROM system_backups ORDER BY id DESC LIMIT {$limit}")->fetchAll();
}

function safe_registered_backup_path(array $record): string {
    $dir=realpath(updater_backup_dir());$path=realpath((string)$record['backup_path']);
    if(!$dir||!$path||!str_starts_with($path,$dir.DIRECTORY_SEPARATOR)) throw new RuntimeException('Backup path is outside the protected backup directory.');
    return $path;
}

function updater_managed_roots(): array {return ['admin','app','assets','database','themes'];}

function clean_managed_application_files(array $expected): void {
    $root=updater_root();$set=array_fill_keys(array_map(fn($p)=>str_replace('\\','/',$p),$expected),true);
    foreach(updater_managed_roots() as $managed){
        $dir=$root.'/'.$managed;if(!is_dir($dir))continue;
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $f){
            $rel=str_replace('\\','/',substr($f->getPathname(),strlen($root)+1));
            if($f->isFile() && !$f->isLink() && empty($set[$rel])) @unlink($f->getPathname());
            elseif($f->isDir()) @rmdir($f->getPathname());
        }
    }
}

function extract_backup_files(string $path,string $tmp,array $meta,bool $restoreUploads,bool $restoreConfig): void {
    $z=new ZipArchive();if($z->open($path)!==true) throw new RuntimeException('Unable to open restore ZIP.');
    try{
        for($i=0;$i<$z->numFiles;$i++){
            $name=str_replace('\\','/',$z->getNameIndex($i));
            if(!str_starts_with($name,'files/')||str_ends_with($name,'/'))continue;
            $rel=substr($name,6);if(!safe_update_path($rel))throw new RuntimeException('Unsafe restore path: '.$rel);
            if(!$restoreUploads && str_starts_with($rel,'uploads/'))continue;
            if(!$restoreConfig && $rel==='config/config.php')continue;
            if(str_starts_with($rel,'storage/'))continue;
            $dest=$tmp.'/files/'.$rel;if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);
            $in=$z->getStream($name);if(!$in)throw new RuntimeException('Could not read '.$rel.' from backup.');$out=fopen($dest,'wb');stream_copy_to_stream($in,$out);fclose($out);fclose($in);
        }
        if(!empty($meta['includes_database'])){
            $idx=$z->locateName('database/database.sql');
            if($idx!==false){$in=$z->getStream('database/database.sql');$out=fopen($tmp.'/database.sql','wb');stream_copy_to_stream($in,$out);fclose($out);fclose($in);}
        }
    }finally{$z->close();}
}

function copy_restore_files(string $tmp): void {
    $root=updater_root();$source=$tmp.'/files';if(!is_dir($source))return;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
    foreach($it as $f){
        $rel=str_replace('\\','/',substr($f->getPathname(),strlen($source)+1));$dest=$root.'/'.$rel;
        if($f->isDir()){if(!is_dir($dest)&&!mkdir($dest,0755,true))throw new RuntimeException('Could not create '.$rel);}
        elseif($f->isFile()){if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);if(!copy($f->getPathname(),$dest))throw new RuntimeException('Could not restore '.$rel);}
    }
}

function perform_system_restore(string $path,array $meta,array $options=[]): array {
    @set_time_limit(0);$tmp=updater_temp_dir('restore');
    $restoreDb=!empty($options['restore_database'])&&!empty($meta['includes_database']);
    $restoreUploads=!empty($options['restore_uploads'])&&!empty($meta['includes_uploads']);
    $restoreConfig=!empty($options['restore_config'])&&!empty($meta['includes_config']);
    try{
        extract_backup_files($path,$tmp,$meta,$restoreUploads,$restoreConfig);
        if($restoreDb){if(!is_file($tmp.'/database.sql'))throw new RuntimeException('Database SQL is missing from backup.');restore_database_dump($tmp.'/database.sql');}
        $expected=is_array($meta['application_files']??null)?$meta['application_files']:[];
        if($expected)clean_managed_application_files($expected);
        copy_restore_files($tmp);
        return ['database'=>$restoreDb,'uploads'=>$restoreUploads,'config'=>$restoreConfig];
    }finally{updater_remove_tree($tmp);}
}

function restore_system_backup(array $record,array $options=[]): array {
    $path=safe_registered_backup_path($record);$meta=inspect_backup_zip($path);
    if(!empty($record['checksum_sha256'])){
        $actual=hash_file('sha256',$path);if(!hash_equals((string)$record['checksum_sha256'],$actual))throw new RuntimeException('Backup checksum verification failed.');
    }
    $makeSafety=!array_key_exists('create_safety_backup',$options)||!empty($options['create_safety_backup']);
    $safety=null;
    if($makeSafety){
        $safety=create_system_backup('pre_restore','Emergency safety backup before restoring '.$record['file_name'],!empty($options['restore_uploads']),true,true);
    }
    try{
        $result=perform_system_restore($path,$meta,$options);
        ensure_recovery_tables();
        if($safety && !backup_record_by_path((string)$safety['backup_path'])){
            try{$sm=inspect_backup_zip((string)$safety['backup_path']);register_backup_record($sm,(string)$safety['backup_path'],'pre_restore');}catch(Throwable $e){}
        }
        if(!backup_record_by_path($path)) register_backup_record($meta,$path,(string)($record['backup_type']??'uploaded'));
        $version=(string)($meta['source_version']??$record['source_version']??'unknown');updater_set_installed_version($version);
        try{db()->prepare("INSERT INTO system_updates(version,file_name,status,backup_path,notes) VALUES(?,?,?,?,?)")->execute([$version,'restore:'.basename($path),'success',$safety['backup_path']??null,'System restored from backup '.$record['file_name']]);}catch(Throwable $e){}
        try{db()->prepare("UPDATE system_backups SET status='restored',restored_at=NOW() WHERE backup_path=?")->execute([$path]);}catch(Throwable $e){}
        updater_log('Restored system from '.$record['file_name'].' to v'.$version);
        return ['version'=>$version,'safety_backup'=>$safety,'restored'=>$result];
    }catch(Throwable $e){
        updater_log('Restore failed for '.$record['file_name'].': '.$e->getMessage());
        if($safety && !empty($options['auto_recover_on_failure'])){
            try{$sm=inspect_backup_zip((string)$safety['backup_path']);perform_system_restore((string)$safety['backup_path'],$sm,['restore_database'=>true,'restore_uploads'=>!empty($options['restore_uploads']),'restore_config'=>false]);updater_log('Emergency recovery after failed restore succeeded.');}catch(Throwable $rollbackError){updater_log('Emergency recovery failed: '.$rollbackError->getMessage());}
        }
        throw $e;
    }
}

function infer_legacy_backup_version(string $path,string $targetVersion=''): string {
    $name=basename($path);
    if(preg_match('/_v([0-9][A-Za-z0-9._-]*)\.zip$/',$name,$m)) return rtrim($m[1],'._-');
    return $targetVersion!==''?$targetVersion:'unknown';
}

function register_legacy_update_backups(): void {
    ensure_recovery_tables();
    try{$rows=db()->query("SELECT * FROM system_updates WHERE status='success' AND backup_path IS NOT NULL AND backup_path<>'' ORDER BY id DESC")->fetchAll();}catch(Throwable $e){return;}
    foreach($rows as $row){
        $path=(string)$row['backup_path'];if(!is_file($path)||backup_record_by_path($path))continue;
        try{
            $meta=['backup_key'=>bin2hex(random_bytes(16)),'source_version'=>infer_legacy_backup_version($path,(string)$row['version']),'backup_type'=>'legacy','includes_database'=>false,'includes_uploads'=>false,'includes_config'=>false,'notes'=>'Legacy pre-update application backup. Database and uploads were not included by the older updater engine.'];
            register_backup_record($meta,$path,'legacy');
        }catch(Throwable $e){updater_log('Legacy backup registration warning: '.$e->getMessage());}
    }
}

function restore_legacy_application_backup(array $record): array {
    $path=safe_registered_backup_path($record);$z=new ZipArchive();if($z->open($path)!==true)throw new RuntimeException('Legacy backup ZIP is invalid.');
    $tmp=updater_temp_dir('legacy');$safety=create_system_backup('pre_restore','Safety backup before legacy application rollback',false,true,true);
    try{
        for($i=0;$i<$z->numFiles;$i++){
            $name=str_replace('\\','/',$z->getNameIndex($i));if($name===''||str_ends_with($name,'/'))continue;
            if(!safe_update_path($name))throw new RuntimeException('Unsafe path in legacy backup: '.$name);
            if(str_starts_with($name,'storage/')||str_starts_with($name,'uploads/')||$name==='config/config.php'||$name==='install.php')continue;
            $dest=$tmp.'/'.$name;if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);$in=$z->getStream($z->getNameIndex($i));$out=fopen($dest,'wb');stream_copy_to_stream($in,$out);fclose($out);fclose($in);
        }$z->close();
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);$root=updater_root();
        foreach($it as $f){$rel=str_replace('\\','/',substr($f->getPathname(),strlen($tmp)+1));$dest=$root.'/'.$rel;if($f->isDir()){if(!is_dir($dest))mkdir($dest,0755,true);}elseif(!copy($f->getPathname(),$dest))throw new RuntimeException('Could not restore '.$rel);}
        $version=(string)$record['source_version'];updater_set_installed_version($version);
        try{db()->prepare("INSERT INTO system_updates(version,file_name,status,backup_path,notes) VALUES(?,?,?,?,?)")->execute([$version,'legacy-restore:'.basename($path),'success',$safety['backup_path']??null,'Legacy application files restored. Database/uploads preserved.']);}catch(Throwable $e){}
        db()->prepare("UPDATE system_backups SET status='restored',restored_at=NOW() WHERE id=?")->execute([$record['id']]);
        return ['version'=>$version,'safety_backup'=>$safety,'legacy'=>true];
    }finally{try{$z->close();}catch(Throwable $e){}updater_remove_tree($tmp);}
}

function import_backup_upload(array $file): array {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)throw new RuntimeException('Choose a ShahkotPK backup ZIP.');
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK)throw new RuntimeException('Backup upload failed.');
    if(strtolower(pathinfo((string)($file['name']??''),PATHINFO_EXTENSION))!=='zip')throw new RuntimeException('Only ZIP backups are accepted.');
    $max=1024*1024*1024;if(($file['size']??0)>$max)throw new RuntimeException('Backup ZIP exceeds the 1 GB web-upload safety limit. Upload it to storage/backups using cPanel File Manager if it is larger.');
    $path=updater_backup_dir().'/uploaded_'.date('Ymd_His').'_'.bin2hex(random_bytes(5)).'.zip';
    if(!move_uploaded_file($file['tmp_name'],$path))throw new RuntimeException('Could not store uploaded backup.');
    try{$meta=inspect_backup_zip($path);$meta['notes']='Manually uploaded backup: '.basename((string)$file['name']);return register_backup_record($meta,$path,'uploaded');}
    catch(Throwable $e){@unlink($path);throw $e;}
}

function delete_system_backup(array $record): void {
    $path=safe_registered_backup_path($record);if(is_file($path)&&!@unlink($path))throw new RuntimeException('Backup file could not be deleted.');
    db()->prepare("DELETE FROM system_backups WHERE id=?")->execute([$record['id']]);
}

function prune_automatic_backups(): void {
    ensure_recovery_tables();$keep=max(3,min(100,updater_setting_int('updater_backup_retention',15)));
    $types="'pre_update','pre_restore'";
    $rows=db()->query("SELECT * FROM system_backups WHERE backup_type IN ({$types}) ORDER BY id DESC")->fetchAll();
    foreach(array_slice($rows,$keep) as $r){
        try{$path=safe_registered_backup_path($r);if(is_file($path))@unlink($path);db()->prepare("DELETE FROM system_backups WHERE id=?")->execute([$r['id']]);}catch(Throwable $e){}
    }
}

function install_update(string $zip): array {
    if(!class_exists('ZipArchive')) throw new RuntimeException('PHP ZIP extension is required.');
    @set_time_limit(0);ensure_recovery_tables();register_legacy_update_backups();
    $current=installed_app_version();$root=updater_root();$z=new ZipArchive();
    if($z->open($zip)!==true) throw new RuntimeException('Invalid ZIP file.');
    [$manifestIndex,$wrapper]=find_update_manifest($z);
    $m=json_decode((string)$z->getFromIndex($manifestIndex),true);
    if(!is_array($m)||empty($m['version'])){$z->close();throw new RuntimeException('Invalid update manifest.');}
    if(version_compare((string)$m['version'],$current,'<=')){$z->close();throw new RuntimeException("Update version {$m['version']} must be higher than installed version {$current}.");}
    if(!empty($m['min_version'])&&version_compare($current,(string)$m['min_version'],'<')){$z->close();throw new RuntimeException("This update requires ShahkotPK {$m['min_version']} or newer. Current version: {$current}.");}

    $tmp=updater_temp_dir('update');$backup=null;$installedFiles=false;
    try{
        for($i=0;$i<$z->numFiles;$i++){
            $original=str_replace('\\','/',$z->getNameIndex($i));if($i===$manifestIndex)continue;$name=$original;
            if($wrapper!==''){if(!str_starts_with($name,$wrapper))continue;$name=substr($name,strlen($wrapper));}
            if($name===''||$name==='update.json')continue;if(!safe_update_path($name))throw new RuntimeException('Unsafe path in update ZIP: '.$name);
            if($name==='config/config.php'||str_starts_with($name,'storage/')||$name==='install.php')continue;
            $dest=$tmp.'/'.$name;if(str_ends_with($name,'/')){if(!is_dir($dest))mkdir($dest,0755,true);continue;}
            if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);$stream=$z->getStream($original);if(!$stream)throw new RuntimeException('Unable to read '.$name.' from update ZIP.');$fp=fopen($dest,'wb');stream_copy_to_stream($stream,$fp);fclose($fp);fclose($stream);
        }$z->close();

        if(updater_setting_bool('updater_auto_backup',true)){
            $backup=create_system_backup('pre_update','Automatic restore point before updating from v'.$current.' to v'.$m['version'],false,updater_setting_bool('updater_backup_database',true),true);
        }

        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
        foreach($it as $f){$rel=str_replace('\\','/',substr($f->getPathname(),strlen($tmp)+1));$dest=$root.'/'.$rel;if($f->isLink())continue;if($f->isDir()){if(!is_dir($dest))mkdir($dest,0755,true);}else{if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);if(!copy($f->getPathname(),$dest))throw new RuntimeException('Failed to install '.$rel);}}
        $installedFiles=true;

        require_once $root.'/app/migrations.php';$migrations=run_packaged_migrations(true);
        updater_set_installed_version((string)$m['version']);
        $notes=(string)($m['notes']??'Update installed successfully.');if($migrations)$notes.=' SQL migrations: '.implode(', ',$migrations);
        db()->prepare("INSERT INTO system_updates(version,file_name,status,backup_path,notes) VALUES(?,?,?,?,?)")->execute([$m['version'],basename($zip),'success',$backup['backup_path']??null,$notes]);
        updater_log('Update '.$m['version'].' installed from '.basename($zip));
        return ['version'=>$m['version'],'migrations'=>$migrations,'backup'=>$backup];
    }catch(Throwable $e){
        $rollbackMsg='';
        if($installedFiles && $backup && updater_setting_bool('updater_auto_rollback',true)){
            try{$meta=inspect_backup_zip((string)$backup['backup_path']);perform_system_restore((string)$backup['backup_path'],$meta,['restore_database'=>!empty($backup['includes_database']),'restore_uploads'=>false,'restore_config'=>false]);updater_set_installed_version($current);$rollbackMsg=' Automatic rollback succeeded.';}
            catch(Throwable $rollbackError){$rollbackMsg=' Automatic rollback failed: '.$rollbackError->getMessage();updater_log($rollbackMsg);}
        }
        try{db()->prepare("INSERT INTO system_updates(version,file_name,status,backup_path,notes) VALUES(?,?,?,?,?)")->execute([$m['version']??'unknown',basename($zip),'failed',$backup['backup_path']??null,$e->getMessage().$rollbackMsg]);}catch(Throwable $ignored){}
        throw new RuntimeException($e->getMessage().$rollbackMsg,0,$e);
    }finally{try{$z->close();}catch(Throwable $e){}updater_remove_tree($tmp);}
}
