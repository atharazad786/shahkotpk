<?php
declare(strict_types=1);

/** ShahkotPK v9.6.0 — Database & Maintenance Center helpers. */

function sk960_ident(string $name): string {
    if(!preg_match('/^[A-Za-z0-9_]{1,64}$/',$name)) throw new InvalidArgumentException('Invalid database identifier.');
    return '`'.$name.'`';
}
function sk960_is_super_admin(array $me): bool {
    if(function_exists('is_super_admin')) return (bool)is_super_admin();
    return in_array(strtolower((string)($me['role']??'')),['super_admin','superadmin','admin'],true);
}
function sk960_db_name(): string {
    return (string)(db()->query('SELECT DATABASE()')->fetchColumn() ?: '');
}
function sk960_tables(): array {
    $sql="SELECT TABLE_NAME,ENGINE,TABLE_ROWS,DATA_LENGTH,INDEX_LENGTH,DATA_FREE,TABLE_COLLATION,CREATE_TIME,UPDATE_TIME
          FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME";
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function sk960_table_columns(string $table): array {
    $q=db()->prepare("SELECT COLUMN_NAME,COLUMN_TYPE,DATA_TYPE,IS_NULLABLE,COLUMN_DEFAULT,COLUMN_KEY,EXTRA,COLLATION_NAME
                      FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION");
    $q->execute([$table]); return $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function sk960_primary_key(string $table): ?string {
    foreach(sk960_table_columns($table) as $c) if((string)$c['COLUMN_KEY']==='PRI') return (string)$c['COLUMN_NAME'];
    return null;
}
function sk960_table_exists(string $table): bool {
    $q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
    $q->execute([$table]); return (bool)$q->fetchColumn();
}
function sk960_sensitive_column(string $name): bool {
    return (bool)preg_match('/password|passwd|secret|token|api[_-]?key|private|credential|session|cookie|salt|hash/i',$name);
}
function sk960_fmt_bytes(int|float|null $bytes): string {
    $n=(float)($bytes??0); $u=['B','KB','MB','GB','TB'];$i=0;
    while($n>=1024&&$i<count($u)-1){$n/=1024;$i++;}
    return number_format($n,$i?2:0).' '.$u[$i];
}
function sk960_db_summary(): array {
    $tables=sk960_tables();$data=0;$idx=0;$free=0;$rows=0;
    foreach($tables as $t){$data+=(int)$t['DATA_LENGTH'];$idx+=(int)$t['INDEX_LENGTH'];$free+=(int)$t['DATA_FREE'];$rows+=(int)$t['TABLE_ROWS'];}
    return ['database'=>sk960_db_name(),'version'=>(string)db()->query('SELECT VERSION()')->fetchColumn(),'tables'=>count($tables),'rows'=>$rows,'data'=>$data,'indexes'=>$idx,'free'=>$free,'total'=>$data+$idx];
}
function sk960_cache_dirs(): array {
    $root=realpath(__DIR__.'/..') ?: dirname(__DIR__);$candidates=[
        $root.'/storage/cache',$root.'/storage/framework/cache',$root.'/storage/framework/views',$root.'/storage/views',$root.'/cache',$root.'/tmp/cache'
    ];
    $out=[]; foreach($candidates as $p){ if(is_dir($p)){ $r=realpath($p); if($r && str_starts_with($r,$root.DIRECTORY_SEPARATOR)) $out[]=$r; } }
    return array_values(array_unique($out));
}
function sk960_dir_size(string $dir): int {
    $size=0;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));
    foreach($it as $f) if($f->isFile()) $size+=$f->getSize(); return $size;
}
function sk960_clear_dir(string $dir): array {
    $deleted=0;$bytes=0;$keep=['.htaccess','index.php','.gitkeep'];
    if(!is_dir($dir)) return ['files'=>0,'bytes'=>0];
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $f){$name=$f->getFilename(); if(in_array($name,$keep,true))continue;
        try{ if($f->isFile()||$f->isLink()){ $bytes+=$f->getSize(); if(@unlink($f->getPathname()))$deleted++; } elseif($f->isDir()) @rmdir($f->getPathname()); }catch(Throwable $e){}
    }
    return ['files'=>$deleted,'bytes'=>$bytes];
}
function sk960_clear_caches(): array {
    $result=['dirs'=>[],'files'=>0,'bytes'=>0,'opcache'=>null];
    foreach(sk960_cache_dirs() as $dir){$r=sk960_clear_dir($dir);$result['dirs'][]=['path'=>$dir]+$r;$result['files']+=$r['files'];$result['bytes']+=$r['bytes'];}
    if(function_exists('opcache_reset')){try{$result['opcache']=(bool)opcache_reset();}catch(Throwable $e){$result['opcache']=false;}}
    return $result;
}
function sk960_audit(string $action,array $meta=[],?int $uid=null): void {
    try{
        if(sk960_table_exists('admin_db_audit_v960')){
            $q=db()->prepare('INSERT INTO admin_db_audit_v960(user_id,action_key,metadata_json,created_at) VALUES(?,?,?,NOW())');
            $q->execute([$uid,$action,json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
        }
        if(function_exists('tenant_audit')) tenant_audit('database.'.$action,'database',0,'Database Maintenance Center action',$meta);
    }catch(Throwable $e){}
}
function sk960_run_table_command(string $command,array $tables): array {
    $allowed=['CHECK','ANALYZE','OPTIMIZE']; $command=strtoupper($command); if(!in_array($command,$allowed,true))throw new InvalidArgumentException('Unsupported maintenance command.');
    $out=[]; foreach($tables as $table){ if(!sk960_table_exists($table))continue; try{$q=db()->query($command.' TABLE '.sk960_ident($table));$out[$table]=$q->fetchAll(PDO::FETCH_ASSOC);}catch(Throwable $e){$out[$table]=[['error'=>$e->getMessage()]];} }
    return $out;
}
function sk960_browse(string $table,string $search='',int $page=1,int $limit=50): array {
    if(!sk960_table_exists($table)) throw new RuntimeException('Table not found.');
    $limit=max(10,min(100,$limit));$page=max(1,$page);$offset=($page-1)*$limit;$cols=sk960_table_columns($table);$params=[];$where='';
    if($search!==''){
        $parts=[];foreach($cols as $c){$dt=strtolower((string)$c['DATA_TYPE']); if(in_array($dt,['char','varchar','text','tinytext','mediumtext','longtext'],true)){$parts[]='CAST('.sk960_ident((string)$c['COLUMN_NAME']).' AS CHAR) LIKE ?';$params[]='%'.$search.'%';}}
        if($parts)$where=' WHERE '.implode(' OR ',$parts);
    }
    $count=db()->prepare('SELECT COUNT(*) FROM '.sk960_ident($table).$where);$count->execute($params);$total=(int)$count->fetchColumn();
    $sql='SELECT * FROM '.sk960_ident($table).$where.' LIMIT '.$limit.' OFFSET '.$offset;$q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll(PDO::FETCH_ASSOC)?:[];
    return ['columns'=>$cols,'rows'=>$rows,'total'=>$total,'page'=>$page,'limit'=>$limit,'pages'=>max(1,(int)ceil($total/$limit)),'pk'=>sk960_primary_key($table)];
}
function sk960_export_database(): never {
    if(function_exists('set_time_limit')) @set_time_limit(0);
    $dbName=sk960_db_name();$file='shahkotpk-db-backup-'.date('Ymd-His').'.sql';
    header('Content-Type: application/sql; charset=utf-8');header('Content-Disposition: attachment; filename="'.$file.'"');header('X-Content-Type-Options: nosniff');
    echo "-- ShahkotPK database backup\n-- Database: ".$dbName."\n-- Generated: ".date('c')."\nSET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";
    foreach(sk960_tables() as $t){$table=(string)$t['TABLE_NAME'];$qid=sk960_ident($table);$create=db()->query('SHOW CREATE TABLE '.$qid)->fetch(PDO::FETCH_NUM); if(!$create)continue;
        echo "DROP TABLE IF EXISTS $qid;\n".$create[1].";\n\n";
        $q=db()->query('SELECT * FROM '.$qid);$cols=null;$batch=[];
        while($row=$q->fetch(PDO::FETCH_ASSOC)){
            if($cols===null)$cols=array_keys($row);$vals=[];foreach($row as $v){ if($v===null)$vals[]='NULL'; else $vals[]=db()->quote((string)$v); }
            $batch[]='('.implode(',',$vals).')';
            if(count($batch)>=100){echo 'INSERT INTO '.$qid.' (`'.implode('`,`',$cols).'`) VALUES '.implode(',',$batch).";\n";$batch=[];}
        }
        if($batch&&$cols)echo 'INSERT INTO '.$qid.' (`'.implode('`,`',$cols).'`) VALUES '.implode(',',$batch).";\n";
        echo "\n";
        if(function_exists('flush')) @flush();
    }
    echo "SET FOREIGN_KEY_CHECKS=1;\n";exit;
}
function sk960_readonly_sql(string $sql): array {
    $sql=trim($sql);if($sql==='')return ['columns'=>[],'rows'=>[]];
    if(!preg_match('/^(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\b/i',$sql)) throw new RuntimeException('SQL Console is read-only. Use SELECT, SHOW, DESCRIBE or EXPLAIN.');
    if(preg_match('/\b(INTO\s+OUTFILE|INTO\s+DUMPFILE|LOAD_FILE|SLEEP\s*\(|BENCHMARK\s*\()/i',$sql)) throw new RuntimeException('This read-only console blocks file/timing functions.');
    $q=db()->query($sql);$rows=[];$limit=200;while($limit-->0&&($r=$q->fetch(PDO::FETCH_ASSOC)))$rows[]=$r;
    return ['rows'=>$rows,'columns'=>$rows?array_keys($rows[0]):[]];
}
