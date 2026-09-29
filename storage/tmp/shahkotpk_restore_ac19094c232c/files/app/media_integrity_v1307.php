<?php
declare(strict_types=1);
/** ShahkotPK v13.0.7.0 — on-demand media/file integrity audit + conservative path formatting repair. */
if (!function_exists('sk1307_fresh_audit')) {
function sk1307_db(): PDO { return function_exists('sk1306_db') ? sk1306_db() : (function_exists('sk1305_db') ? sk1305_db() : db()); }
function sk1307_tid(): int { return function_exists('sk1306_tid') ? sk1306_tid() : (function_exists('tenant_id') ? (int)tenant_id() : 0); }
function sk1307_table(string $table): bool {
    if (function_exists('sk1306_table')) return sk1306_table($table);
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $table)) return false;
    try { $q=sk1307_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1'); $q->execute([$table]); return (bool)$q->fetchColumn(); }
    catch (Throwable $e) { return false; }
}
function sk1307_cols(string $table): array {
    if (function_exists('sk1306_cols')) return sk1306_cols($table);
    $out=[]; try { $q=sk1307_db()->prepare('SELECT COLUMN_NAME,DATA_TYPE,IS_NULLABLE,COLUMN_DEFAULT,COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'); $q->execute([$table]); foreach($q->fetchAll()?:[] as $r)$out[(string)$r['COLUMN_NAME']]=$r; } catch(Throwable $e){} return $out;
}
function sk1307_ident(string $name): string { if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name))throw new InvalidArgumentException('Unsafe SQL identifier');return '`'.$name.'`'; }
function sk1307_first(array $cols,array $names): string { foreach($names as $n)if(isset($cols[$n]))return $n;return ''; }
function sk1307_root(): string { $r=realpath(dirname(__DIR__));return is_string($r)&&$r!==''?$r:dirname(__DIR__); }
function sk1307_host(): string { $h=strtolower(trim((string)($_SERVER['HTTP_HOST']??'')));return preg_replace('/:\d+$/','',$h)??$h; }
function sk1307_actor_id(): ?int { $u=function_exists('current_user')?(current_user()?:[]):[];return isset($u['id'])?(int)$u['id']:null; }
function sk1307_pk(string $table,array $cols): string {
    if(function_exists('sk1306_pk'))return sk1306_pk($table,$cols);
    foreach($cols as $name=>$meta)if((string)($meta['COLUMN_KEY']??'')==='PRI')return (string)$name;
    return isset($cols['id'])?'id':'';
}
function sk1307_scope(array $cols,array &$params,string $alias=''): string {
    $tid=sk1307_tid();if($tid>0&&isset($cols['tenant_id'])){$params[]=$tid;return ($alias!==''?$alias.'.':'').sk1307_ident('tenant_id').'=?';}return '1=1';
}
function sk1307_visibility(array $cols): array {
    if(function_exists('sk1306_visibility'))return sk1306_visibility($cols);
    $status=sk1307_first($cols,['status','publication_status','state']);
    if($status!=='')return ['sql'=>'LOWER(COALESCE('.sk1307_ident($status).",'')) IN ('active','approved','published','live','enabled','public')",'field'=>$status,'mode'=>'status'];
    foreach(['is_published','published','is_approved','approved','is_active','active','enabled','is_enabled','visible','is_visible'] as $f)if(isset($cols[$f]))return ['sql'=>'COALESCE('.sk1307_ident($f).',0)=1','field'=>$f,'mode'=>'boolean'];
    return ['sql'=>'1=1','field'=>'','mode'=>'all'];
}
function sk1307_modules(): array { return function_exists('sk1305_modules')?sk1305_modules():[]; }
function sk1307_contract(array $module): ?array {
    if(function_exists('sk1306_contract'))return sk1306_contract($module);
    $table='';foreach(($module['tables']??[]) as $t)if(sk1307_table((string)$t)){$table=(string)$t;break;}if($table==='')return null;
    $cols=sk1307_cols($table);$pk=sk1307_pk($table,$cols);if($pk==='')return null;
    return ['key'=>(string)($module['key']??$table),'label'=>(string)($module['label']??$table),'table'=>$table,'cols'=>$cols,'pk'=>$pk,'media'=>sk1307_first($cols,$module['media']??[]),'visibility'=>sk1307_visibility($cols)];
}
function sk1307_structured(string $value): bool {
    $t=ltrim($value);if($t===''||($t[0]!=='['&&$t[0]!=='{'))return false;
    json_decode($t,true);return json_last_error()===JSON_ERROR_NONE;
}
function sk1307_normalize_local(string $path): string {
    $x=trim($path);$x=str_replace('\\','/',$x);
    $lead=str_starts_with($x,'/');
    while(str_starts_with($x,'./'))$x=substr($x,2);
    $x=preg_replace('#/+#','/',$x)??$x;
    if($lead&&!str_starts_with($x,'/'))$x='/'.$x;
    return $x;
}
function sk1307_has_traversal(string $path): bool {
    $p=str_replace('\\','/',$path);foreach(explode('/',$p) as $seg)if($seg==='..')return true;return str_contains($p,"\0");
}
function sk1307_classify(string $raw): array {
    $v=trim($raw);$base=['kind'=>'empty','exists'=>false,'repairable'=>false,'normalized'=>$v,'public_path'=>'','note'=>''];
    if($v==='')return $base;
    if(sk1307_structured($v))return array_merge($base,['kind'=>'structured','note'=>'Structured/JSON media value is read-only in Step 7.']);
    if(str_starts_with(strtolower($v),'data:'))return array_merge($base,['kind'=>'embedded','note'=>'Embedded data URI; no filesystem check.']);
    if(str_starts_with(strtolower($v),'blob:'))return array_merge($base,['kind'=>'transient','note'=>'blob: URLs are browser-session values and should not be stored as durable public media.']);
    if(str_starts_with($v,'//'))return array_merge($base,['kind'=>'external','note'=>'Protocol-relative remote URL is not network-checked.']);
    if(preg_match('#^https?://#i',$v)){
        $u=@parse_url($v);$host=is_array($u)?strtolower((string)($u['host']??'')):'';$path=is_array($u)?(string)($u['path']??''):'';
        if($host!==''&&sk1307_host()!==''&&$host===sk1307_host()&&$path!==''){
            if(sk1307_has_traversal($path))return array_merge($base,['kind'=>'unsafe','note'=>'Same-site URL contains an unsafe traversal segment.']);
            $norm=sk1307_normalize_local($path);$candidate=sk1307_root().'/'.ltrim(rawurldecode($norm),'/');$exists=is_file($candidate);
            return array_merge($base,['kind'=>$exists?'same_host_existing':'same_host_missing','exists'=>$exists,'normalized'=>$v,'public_path'=>$norm,'note'=>$exists?'Same-host absolute URL resolves to a file.':'Same-host absolute URL does not resolve to a file.']);
        }
        return array_merge($base,['kind'=>'external','note'=>'Remote URL is intentionally not network-checked.']);
    }
    if(preg_match('#^[a-z][a-z0-9+.-]*:#i',$v))return array_merge($base,['kind'=>'unsupported','note'=>'Unsupported media URI scheme.']);
    $pathOnly=preg_split('/[?#]/',$v,2)[0]??$v;
    if(sk1307_has_traversal($pathOnly))return array_merge($base,['kind'=>'unsafe','note'=>'Local media path contains .. traversal or NUL.']);
    $norm=sk1307_normalize_local($pathOnly);
    $candidate=sk1307_root().'/'.ltrim(rawurldecode($norm),'/');$exists=is_file($candidate);
    $hasSuffix=(str_contains($v,'?')||str_contains($v,'#'));
    $repairable=$exists&&!$hasSuffix&&$norm!==$v;
    return array_merge($base,['kind'=>$exists?'local_existing':'local_missing','exists'=>$exists,'repairable'=>$repairable,'normalized'=>$norm,'public_path'=>$norm,'note'=>$exists?($repairable?'Existing local file; path formatting can be normalized safely.':'Existing local file.'):'Local file was not found.']);
}
function sk1307_empty_module(string $key,string $label,string $table,string $field): array { return ['key'=>$key,'label'=>$label,'table'=>$table,'field'=>$field,'total_nonempty'=>0,'sampled'=>0,'sample_limited'=>false,'local_existing'=>0,'local_missing'=>0,'same_host_existing'=>0,'same_host_missing'=>0,'external'=>0,'embedded'=>0,'structured'=>0,'transient'=>0,'unsafe'=>0,'unsupported'=>0,'repairable'=>0,'examples'=>[],'score'=>100]; }
function sk1307_scan_contract(array $c,int $limit=350): array {
    $field=(string)($c['media']??'');$out=sk1307_empty_module((string)$c['key'],(string)$c['label'],(string)$c['table'],$field);if($field===''){return $out;}
    $cols=$c['cols'];$params=[];$scope=sk1307_scope($cols,$params);$vis=(string)($c['visibility']['sql']??'1=1');$where='('.$scope.') AND ('.$vis.') AND TRIM(COALESCE('.sk1307_ident($field).",''))<>''";
    try{$q=sk1307_db()->prepare('SELECT COUNT(*) FROM '.sk1307_ident($c['table']).' WHERE '.$where);$q->execute($params);$out['total_nonempty']=(int)$q->fetchColumn();}catch(Throwable $e){$out['examples'][]=['record'=>'','value'=>'','kind'=>'error','note'=>'Count failed: '.$e->getMessage()];return $out;}
    $out['sample_limited']=$out['total_nonempty']>$limit;
    try{$sql='SELECT '.sk1307_ident($c['pk']).' AS rid,'.sk1307_ident($field).' AS media_value FROM '.sk1307_ident($c['table']).' WHERE '.$where.' ORDER BY '.sk1307_ident($c['pk']).' DESC LIMIT '.max(1,min(1000,$limit));$q=sk1307_db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll()?:[];}catch(Throwable $e){$rows=[];$out['examples'][]=['record'=>'','value'=>'','kind'=>'error','note'=>'Sample failed: '.$e->getMessage()];}
    foreach($rows as $row){$out['sampled']++;$cl=sk1307_classify((string)($row['media_value']??''));$k=$cl['kind'];if(isset($out[$k]))$out[$k]++;if(!empty($cl['repairable']))$out['repairable']++;if(in_array($k,['local_missing','same_host_missing','unsafe','transient','unsupported'],true)&&count($out['examples'])<18)$out['examples'][]=['record'=>(string)($row['rid']??''),'value'=>mb_substr((string)($row['media_value']??''),0,260),'kind'=>$k,'note'=>$cl['note']];}
    $penalty=$out['local_missing']*5+$out['same_host_missing']*5+$out['unsafe']*10+$out['transient']*6+$out['unsupported']*4;$out['score']=max(0,100-min(100,$penalty));return $out;
}
function sk1307_library_contract(): ?array {
    if(!sk1307_table('media_library'))return null;$cols=sk1307_cols('media_library');$pk=sk1307_pk('media_library',$cols);$field=sk1307_first($cols,['file_path','path','storage_path','url','file_url','source_url']);if($pk===''||$field==='')return null;
    return ['key'=>'__media_library__','label'=>'Media Library','table'=>'media_library','cols'=>$cols,'pk'=>$pk,'media'=>$field,'visibility'=>['sql'=>'1=1','field'=>'','mode'=>'all']];
}
function sk1307_library_duplicates(array $c): int {
    $params=[];$scope=sk1307_scope($c['cols'],$params);$f=sk1307_ident($c['media']);try{$sql='SELECT COUNT(*) FROM (SELECT '.$f.' FROM '.sk1307_ident($c['table']).' WHERE '.$scope.' AND TRIM(COALESCE('.$f.",''))<>'' GROUP BY ".$f.' HAVING COUNT(*)>1) d';$q=sk1307_db()->prepare($sql);$q->execute($params);return (int)$q->fetchColumn();}catch(Throwable $e){return 0;}
}
function sk1307_scan_all(): array {
    $mods=[];$tot=['modules'=>0,'sampled'=>0,'local_existing'=>0,'broken_local'=>0,'external_unverified'=>0,'unsafe'=>0,'transient'=>0,'repairable'=>0,'structured'=>0,'duplicate_media_groups'=>0];
    foreach(sk1307_modules() as $m){$c=sk1307_contract($m);if(!$c||($c['media']??'')==='')continue;$r=sk1307_scan_contract($c);$mods[]=$r;$tot['modules']++;$tot['sampled']+=$r['sampled'];$tot['local_existing']+=$r['local_existing']+$r['same_host_existing'];$tot['broken_local']+=$r['local_missing']+$r['same_host_missing'];$tot['external_unverified']+=$r['external'];$tot['unsafe']+=$r['unsafe']+$r['unsupported'];$tot['transient']+=$r['transient'];$tot['repairable']+=$r['repairable'];$tot['structured']+=$r['structured'];}
    $lib=null;$lc=sk1307_library_contract();if($lc){$lib=sk1307_scan_contract($lc);$lib['duplicate_groups']=sk1307_library_duplicates($lc);$tot['duplicate_media_groups']=$lib['duplicate_groups'];$tot['sampled']+=$lib['sampled'];$tot['local_existing']+=$lib['local_existing']+$lib['same_host_existing'];$tot['broken_local']+=$lib['local_missing']+$lib['same_host_missing'];$tot['external_unverified']+=$lib['external'];$tot['unsafe']+=$lib['unsafe']+$lib['unsupported'];$tot['transient']+=$lib['transient'];$tot['repairable']+=$lib['repairable'];$tot['structured']+=$lib['structured'];}
    $penalty=$tot['broken_local']*4+$tot['unsafe']*8+$tot['transient']*4+min(15,$tot['duplicate_media_groups']);$score=max(0,100-min(100,$penalty));
    return ['version'=>'13.0.7.0','generated_at'=>gmdate('c'),'score'=>$score,'totals'=>$tot,'modules'=>$mods,'media_library'=>$lib,'network_checks'=>false,'sample_limit_per_module'=>350];
}
function sk1307_save_run(array $scan): int {
    if(!sk1307_table('media_integrity_runs_v1307'))return 0;try{$q=sk1307_db()->prepare('INSERT INTO media_integrity_runs_v1307(tenant_id,actor_user_id,score,summary_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1307_tid(),sk1307_actor_id(),(int)($scan['score']??0),json_encode($scan,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);return (int)sk1307_db()->lastInsertId();}catch(Throwable $e){return 0;}
}
function sk1307_fresh_audit(): array {$x=sk1307_scan_all();$x['run_id']=sk1307_save_run($x);return $x;}
function sk1307_last_audit(): ?array {
    if(!sk1307_table('media_integrity_runs_v1307'))return null;try{$params=[];$where='1=1';if(sk1307_tid()>0){$where='tenant_id=?';$params[] = sk1307_tid();}$q=sk1307_db()->prepare('SELECT id,score,summary_json,created_at FROM media_integrity_runs_v1307 WHERE '.$where.' ORDER BY id DESC LIMIT 1');$q->execute($params);$r=$q->fetch();if(!$r)return null;$x=json_decode((string)$r['summary_json'],true);if(!is_array($x))return null;$x['run_id']=(int)$r['id'];$x['saved_at']=(string)$r['created_at'];return $x;}catch(Throwable $e){return null;}
}
function sk1307_batch(): string { try{return 'm7-'.bin2hex(random_bytes(10));}catch(Throwable $e){return 'm7-'.gmdate('YmdHis').'-'.mt_rand(1000,9999);} }
function sk1307_log(string $batch,array $c,string $rid,string $before,string $after): void {
    if(!sk1307_table('media_integrity_actions_v1307'))return;try{$q=sk1307_db()->prepare('INSERT INTO media_integrity_actions_v1307(batch_key,tenant_id,actor_user_id,module_key,table_name,record_id,field_name,before_value,after_value,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');$q->execute([$batch,sk1307_tid(),sk1307_actor_id(),(string)$c['key'],(string)$c['table'],$rid,(string)$c['media'],$before,$after]);}catch(Throwable $e){}
}
function sk1307_contract_by_key(string $key): ?array {
    if($key==='__media_library__')return sk1307_library_contract();foreach(sk1307_modules() as $m)if((string)($m['key']??'')===$key)return sk1307_contract($m);return null;
}
function sk1307_repair_path_formatting(string $key,int $max=200): array {
    $c=sk1307_contract_by_key($key);if(!$c||($c['media']??'')==='')throw new RuntimeException('Media contract not found for this module.');$max=max(1,min(200,$max));$params=[];$scope=sk1307_scope($c['cols'],$params);$vis=(string)($c['visibility']['sql']??'1=1');$where='('.$scope.') AND ('.$vis.') AND TRIM(COALESCE('.sk1307_ident($c['media']).",''))<>''";$sql='SELECT '.sk1307_ident($c['pk']).' AS rid,'.sk1307_ident($c['media']).' AS media_value FROM '.sk1307_ident($c['table']).' WHERE '.$where.' ORDER BY '.sk1307_ident($c['pk']).' DESC LIMIT '.($max*4);$q=sk1307_db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll()?:[];$pdo=sk1307_db();$batch=sk1307_batch();$changed=0;$errors=[];
    try{$pdo->beginTransaction();foreach($rows as $row){if($changed>=$max)break;$rid=(string)($row['rid']??'');$before=(string)($row['media_value']??'');$cl=sk1307_classify($before);if(empty($cl['repairable']))continue;$after=(string)$cl['normalized'];if($after===''||$after===$before)continue;$u=$pdo->prepare('UPDATE '.sk1307_ident($c['table']).' SET '.sk1307_ident($c['media']).'=? WHERE '.sk1307_ident($c['pk']).'=? AND '.sk1307_ident($c['media']).'=?');$u->execute([$after,$rid,$before]);if($u->rowCount()>0){$changed++;sk1307_log($batch,$c,$rid,$before,$after);}}$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$errors[]=$e->getMessage();}
    if($changed>0){try{if(function_exists('sk1306_refresh_runtime'))sk1306_refresh_runtime();}catch(Throwable $e){$errors[]='Runtime refresh: '.$e->getMessage();}}
    return ['batch'=>$batch,'changed'=>$changed,'errors'=>$errors,'max'=>$max];
}
}
