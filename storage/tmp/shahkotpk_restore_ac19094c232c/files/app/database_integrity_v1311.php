<?php
declare(strict_types=1);
/** ShahkotPK v13.0.11.0 — on-demand database integrity and optimization metadata audit. */
if (!function_exists('sk1311_fresh_audit')) {
function sk1311_db(): PDO {
    if (function_exists('sk1310_db')) return sk1310_db();
    if (function_exists('sk1309_db')) return sk1309_db();
    if (function_exists('sk1308_db')) return sk1308_db();
    if (function_exists('sk1301_db')) return sk1301_db();
    return db();
}
function sk1311_tid(): int {
    if (function_exists('sk1310_tid')) return sk1310_tid();
    if (function_exists('sk1309_tid')) return sk1309_tid();
    return function_exists('tenant_id') ? (int)tenant_id() : 0;
}
function sk1311_actor(): array {
    try {$u=function_exists('current_user')?current_user():null;return is_array($u)?$u:[];}catch(Throwable $e){return [];}
}
function sk1311_actor_id(): ?int {
    $u=sk1311_actor();$id=(int)($u['id']??$u['user_id']??0);return $id>0?$id:null;
}
function sk1311_driver(): string {
    try{return strtolower((string)sk1311_db()->getAttribute(PDO::ATTR_DRIVER_NAME));}catch(Throwable $e){return '';}
}
function sk1311_table(string $name): bool {
    static $c=[];if(isset($c[$name]))return $c[$name];
    try{
        if(sk1311_driver()==='mysql'){$q=sk1311_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$name]);return $c[$name]=(bool)$q->fetchColumn();}
        $q=sk1311_db()->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=? LIMIT 1");$q->execute([$name]);return $c[$name]=(bool)$q->fetchColumn();
    }catch(Throwable $e){return $c[$name]=false;}
}
function sk1311_setting(string $key,string $fallback=''): string {
    if(!sk1311_table('settings'))return $fallback;
    try{$q=sk1311_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$fallback:(string)$v;}catch(Throwable $e){return $fallback;}
}
function sk1311_bytes(int $n): string {
    if($n<1024)return $n.' B';$u=['KB','MB','GB','TB'];$v=$n/1024;foreach($u as $unit){if($v<1024)return number_format($v,$v>=100?0:($v>=10?1:2)).' '.$unit;$v/=1024;}return number_format($v,2).' PB';
}
function sk1311_mysql_meta(): array {
    $r=['supported'=>false,'driver'=>sk1311_driver(),'database'=>'','server_version'=>'','charset'=>'','collation'=>'','tables'=>[],'largest'=>[],'missing_pk'=>[],'duplicate_indexes'=>[],'index_candidates'=>[],'fragmented'=>[],'engine_mismatch'=>[],'collation_mismatch'=>[],'migration'=>[],'query_counts'=>[]];
    if($r['driver']!=='mysql')return $r;
    $r['supported']=true;
    try{$r['database']=(string)sk1311_db()->query('SELECT DATABASE()')->fetchColumn();}catch(Throwable $e){}
    try{$r['server_version']=(string)sk1311_db()->getAttribute(PDO::ATTR_SERVER_VERSION);}catch(Throwable $e){}
    try{$x=sk1311_db()->query("SELECT @@character_set_database AS c, @@collation_database AS col")->fetch(PDO::FETCH_ASSOC);if($x){$r['charset']=(string)$x['c'];$r['collation']=(string)$x['col'];}}catch(Throwable $e){}

    $tables=[];
    try{
        $sql="SELECT TABLE_NAME,ENGINE,TABLE_COLLATION,COALESCE(TABLE_ROWS,0) TABLE_ROWS,COALESCE(DATA_LENGTH,0) DATA_LENGTH,COALESCE(INDEX_LENGTH,0) INDEX_LENGTH,COALESCE(DATA_FREE,0) DATA_FREE,COALESCE(AUTO_INCREMENT,0) AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY (COALESCE(DATA_LENGTH,0)+COALESCE(INDEX_LENGTH,0)) DESC LIMIT 500";
        $q=sk1311_db()->query($sql);while($x=$q->fetch(PDO::FETCH_ASSOC)){$x['TOTAL_BYTES']=(int)$x['DATA_LENGTH']+(int)$x['INDEX_LENGTH'];$tables[]=$x;}
    }catch(Throwable $e){}
    $r['tables']=$tables;$r['largest']=array_slice($tables,0,20);
    foreach($tables as $x){
        if(strtoupper((string)$x['ENGINE'])!=='INNODB')$r['engine_mismatch'][]=['table'=>$x['TABLE_NAME'],'engine'=>$x['ENGINE']?:'(none)'];
        if($r['collation']!==''&&(string)$x['TABLE_COLLATION']!==''&&strcasecmp((string)$x['TABLE_COLLATION'],$r['collation'])!==0)$r['collation_mismatch'][]=['table'=>$x['TABLE_NAME'],'collation'=>$x['TABLE_COLLATION']];
        $total=max(1,(int)$x['TOTAL_BYTES']);$free=(int)$x['DATA_FREE'];if($total>=10*1024*1024&&$free/$total>=0.25)$r['fragmented'][]=['table'=>$x['TABLE_NAME'],'total_bytes'=>$total,'free_bytes'=>$free,'ratio'=>round(($free/$total)*100,1)];
    }
    try{
        $sql="SELECT t.TABLE_NAME FROM information_schema.TABLES t LEFT JOIN information_schema.TABLE_CONSTRAINTS c ON c.TABLE_SCHEMA=t.TABLE_SCHEMA AND c.TABLE_NAME=t.TABLE_NAME AND c.CONSTRAINT_TYPE='PRIMARY KEY' WHERE t.TABLE_SCHEMA=DATABASE() AND t.TABLE_TYPE='BASE TABLE' AND c.TABLE_NAME IS NULL ORDER BY t.TABLE_NAME LIMIT 100";
        $r['missing_pk']=sk1311_db()->query($sql)->fetchAll(PDO::FETCH_COLUMN)?:[];
    }catch(Throwable $e){}

    $stats=[];$indexRows=0;
    try{
        $q=sk1311_db()->query("SELECT TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME,SUB_PART,INDEX_TYPE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX");
        while(($x=$q->fetch(PDO::FETCH_ASSOC))&&$indexRows<6000){$indexRows++;$t=(string)$x['TABLE_NAME'];$i=(string)$x['INDEX_NAME'];if(!isset($stats[$t][$i]))$stats[$t][$i]=['non_unique'=>(int)$x['NON_UNIQUE'],'columns'=>[],'type'=>(string)$x['INDEX_TYPE']];$stats[$t][$i]['columns'][]=(string)$x['COLUMN_NAME'].($x['SUB_PART']!==null?'('.(int)$x['SUB_PART'].')':'');}
    }catch(Throwable $e){}
    $r['query_counts']['statistics_rows']=$indexRows;
    foreach($stats as $table=>$idxs){$seen=[];foreach($idxs as $name=>$meta){if($name==='PRIMARY')continue;$sig=$meta['non_unique'].'|'.$meta['type'].'|'.implode(',',$meta['columns']);if(isset($seen[$sig])){$r['duplicate_indexes'][]=['table'=>$table,'indexes'=>[$seen[$sig],$name],'columns'=>implode(', ',$meta['columns'])];if(count($r['duplicate_indexes'])>=80)break 2;}$seen[$sig]=$name;}}

    $interesting=[];$colRows=0;
    try{
        $q=sk1311_db()->query("SELECT TABLE_NAME,COLUMN_NAME,IS_NULLABLE,COLUMN_KEY,DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND (COLUMN_NAME='tenant_id' OR COLUMN_NAME='created_at' OR COLUMN_NAME='slug' OR COLUMN_NAME='status' OR COLUMN_NAME='user_id' OR COLUMN_NAME='owner_id' OR COLUMN_NAME='category_id' OR COLUMN_NAME LIKE '%\\_id') ORDER BY TABLE_NAME,ORDINAL_POSITION");
        while(($x=$q->fetch(PDO::FETCH_ASSOC))&&$colRows<5000){$colRows++;$interesting[(string)$x['TABLE_NAME']][(string)$x['COLUMN_NAME']]=$x;}
    }catch(Throwable $e){}
    $r['query_counts']['interesting_column_rows']=$colRows;
    foreach($interesting as $table=>$cols){
        $idxs=$stats[$table]??[];$first=[];foreach($idxs as $name=>$m){if(!empty($m['columns'])){$c=preg_replace('/\(.*/','',(string)$m['columns'][0]);$first[strtolower($c)]=true;}}
        $cands=[];
        if(isset($cols['tenant_id'])&&!isset($first['tenant_id']))$cands[]='tenant_id';
        if(isset($cols['slug'])&&!isset($first['slug']))$cands[]='slug';
        foreach(['user_id','owner_id','category_id'] as $c)if(isset($cols[$c])&&!isset($first[$c]))$cands[]=$c;
        if($cands){$r['index_candidates'][]=['table'=>$table,'columns'=>$cands,'note'=>'Review query patterns and table size before adding indexes. No automatic ALTER is performed.'];if(count($r['index_candidates'])>=100)break;}
    }

    $migDir=dirname(__DIR__).'/database/migrations';$files=[];if(is_dir($migDir)){foreach(glob($migDir.'/*.sql')?:[] as $f)$files[]=basename($f);natsort($files);$files=array_values($files);if(count($files)>80)$files=array_slice($files,-80);}
    $r['migration']=['installed_version'=>sk1311_setting('installed_app_version',''),'current_file_present'=>in_array('13.0.11.0.sql',$files,true),'recent_files'=>$files];
    return $r;
}
function sk1311_findings(array $m): array {
    $f=[];
    if(!$m['supported'])return [['severity'=>'medium','message'=>'Database driver '.$m['driver'].' is not MySQL/MariaDB; Step 11 metadata audit is report-limited on this driver.']];
    if($m['missing_pk'])$f[]=['severity'=>'high','message'=>count($m['missing_pk']).' base table(s) have no primary key. Review before replication, large updates or ORM changes.'];
    if($m['duplicate_indexes'])$f[]=['severity'=>'medium','message'=>count($m['duplicate_indexes']).' duplicate index group(s) were detected. Removing duplicates can reduce write cost, but requires manual verification.'];
    if($m['engine_mismatch'])$f[]=['severity'=>'medium','message'=>count($m['engine_mismatch']).' table(s) are not InnoDB. Engine conversion is intentionally not automatic.'];
    if($m['collation_mismatch'])$f[]=['severity'=>'low','message'=>count($m['collation_mismatch']).' table(s) use a collation different from the database default. Mixed collations can affect joins/comparisons.'];
    if($m['fragmented'])$f[]=['severity'=>'low','message'=>count($m['fragmented']).' table(s) have high estimated DATA_FREE. Do not run OPTIMIZE TABLE during peak traffic without a maintenance plan.'];
    if($m['index_candidates'])$f[]=['severity'=>'low','message'=>count($m['index_candidates']).' possible index gap(s) were identified from common key columns. These are recommendations only, not proof of slow queries.'];
    $iv=(string)($m['migration']['installed_version']??'');if($iv!==''&&version_compare($iv,'13.0.11.0','<'))$f[]=['severity'=>'medium','message'=>'installed_app_version reports '.$iv.' while Step 11 expects 13.0.11.0 after migration.'];
    if(empty($m['migration']['current_file_present']))$f[]=['severity'=>'low','message'=>'13.0.11.0.sql is not visible in the runtime migration directory after install. This may be updater cleanup behavior; verify updater history if version drift appears.'];
    return $f;
}
function sk1311_score(array $m,array $f): int {
    $score=100;foreach($f as $x){$score-=($x['severity']==='high'?15:($x['severity']==='medium'?7:2));}return max(0,min(100,$score));
}
function sk1311_save_run(array $r): int {
    if(!sk1311_table('database_integrity_runs_v1311'))return 0;
    try{$high=0;$med=0;foreach($r['findings'] as $x){if($x['severity']==='high')$high++;elseif($x['severity']==='medium')$med++;}$q=sk1311_db()->prepare('INSERT INTO database_integrity_runs_v1311(tenant_id,actor_user_id,score,high_count,medium_count,summary_json,created_at) VALUES(?,?,?,?,?,?,NOW())');$q->execute([sk1311_tid(),sk1311_actor_id(),(int)$r['score'],$high,$med,json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);return (int)sk1311_db()->lastInsertId();}catch(Throwable $e){return 0;}
}
function sk1311_fresh_audit(): array {
    $meta=sk1311_mysql_meta();$find=sk1311_findings($meta);$r=['version'=>'13.0.11.0','tenant_id'=>sk1311_tid(),'created_at'=>date('c'),'score'=>sk1311_score($meta,$find),'meta'=>$meta,'findings'=>$find];$r['run_id']=sk1311_save_run($r);return $r;
}
function sk1311_last_audit(): ?array {
    if(!sk1311_table('database_integrity_runs_v1311'))return null;try{$q=sk1311_db()->prepare('SELECT summary_json FROM database_integrity_runs_v1311 WHERE tenant_id=? ORDER BY id DESC LIMIT 1');$q->execute([sk1311_tid()]);$j=$q->fetchColumn();if(!$j)return null;$r=json_decode((string)$j,true);return is_array($r)?$r:null;}catch(Throwable $e){return null;}
}
function sk1311_log_action(string $key,array $result): void {
    if(!sk1311_table('database_integrity_actions_v1311'))return;try{$q=sk1311_db()->prepare('INSERT INTO database_integrity_actions_v1311(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1311_tid(),sk1311_actor_id(),$key,json_encode($result,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);}catch(Throwable $e){}
}
function sk1311_index_exists(string $table,string $index): bool {
    if(sk1311_driver()!=='mysql')return false;try{$q=sk1311_db()->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1');$q->execute([$table,$index]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1311_safe_metadata_maintenance(): array {
    $r=['changed'=>[],'analyzed'=>[],'errors'=>[],'application_tables_altered'=>false];
    if(sk1311_driver()!=='mysql'){$r['errors'][]='Safe metadata maintenance currently requires MySQL/MariaDB.';sk1311_log_action('safe_metadata_maintenance',$r);return $r;}
    $owned=[
      ['database_integrity_runs_v1311','idx_di1311_tenant_created','tenant_id, created_at'],
      ['database_integrity_actions_v1311','idx_dia1311_tenant_created','tenant_id, created_at']
    ];
    foreach($owned as $x){[$table,$idx,$cols]=$x;if(!sk1311_table($table))continue;if(!sk1311_index_exists($table,$idx)){try{sk1311_db()->exec('ALTER TABLE `'.$table.'` ADD INDEX `'.$idx.'` ('.$cols.')');$r['changed'][]=$table.'.'.$idx;}catch(Throwable $e){$r['errors'][]=$table.': '.$e->getMessage();}}}
    foreach(['database_integrity_runs_v1311','database_integrity_actions_v1311'] as $table){if(!sk1311_table($table))continue;try{sk1311_db()->query('ANALYZE TABLE `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);$r['analyzed'][]=$table;}catch(Throwable $e){$r['errors'][]='ANALYZE '.$table.': '.$e->getMessage();}}
    if(sk1311_table('settings')){try{$q=sk1311_db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('database_integrity_last_safe_maintenance_at',?),('step11_database_integrity_version','13.0.11.0') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$q->execute([date('Y-m-d H:i:s')]);}catch(Throwable $e){$r['errors'][]='settings: '.$e->getMessage();}}
    sk1311_log_action('safe_metadata_maintenance',$r);return $r;
}
}
