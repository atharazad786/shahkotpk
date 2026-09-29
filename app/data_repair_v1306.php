<?php
declare(strict_types=1);
/** ShahkotPK v13.0.6.0 — controlled data consistency repair layer. */
if (!function_exists('sk1306_preview')) {
function sk1306_db(): PDO { return function_exists('sk1305_db') ? sk1305_db() : (function_exists('sk1301_db') ? sk1301_db() : db()); }
function sk1306_tid(): int { return function_exists('sk1305_tid') ? sk1305_tid() : (function_exists('sk1301_tid') ? sk1301_tid() : (function_exists('tenant_id') ? (int)tenant_id() : 0)); }
function sk1306_table(string $table): bool {
    if (function_exists('sk1305_table')) return sk1305_table($table);
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $table)) return false;
    try { $q=sk1306_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1'); $q->execute([$table]); return (bool)$q->fetchColumn(); }
    catch (Throwable $e) { return false; }
}
function sk1306_cols(string $table): array {
    if (function_exists('sk1305_cols')) return sk1305_cols($table);
    $out=[];
    try { $q=sk1306_db()->prepare('SELECT COLUMN_NAME,DATA_TYPE,IS_NULLABLE,COLUMN_DEFAULT,COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'); $q->execute([$table]); foreach($q->fetchAll()?:[] as $r)$out[(string)$r['COLUMN_NAME']]=$r; }
    catch(Throwable $e){}
    return $out;
}
function sk1306_ident(string $name): string { if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name)) throw new InvalidArgumentException('Unsafe SQL identifier'); return '`'.$name.'`'; }
function sk1306_first(array $cols,array $names): string { foreach($names as $n) if(isset($cols[$n])) return $n; return ''; }
function sk1306_existing_table(array $names): string { foreach($names as $t) if(sk1306_table($t)) return $t; return ''; }
function sk1306_modules(): array { return function_exists('sk1305_modules') ? sk1305_modules() : []; }
function sk1306_pk(string $table,array $cols): string {
    foreach($cols as $name=>$meta) if((string)($meta['COLUMN_KEY']??'')==='PRI') return (string)$name;
    try { $q=sk1306_db()->prepare("SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_NAME='PRIMARY' ORDER BY ORDINAL_POSITION LIMIT 1");$q->execute([$table]);$v=$q->fetchColumn();if(is_string($v)&&$v!=='')return $v; } catch(Throwable $e){}
    return isset($cols['id'])?'id':'';
}
function sk1306_scope(array $cols,array &$params,string $alias=''): string {
    $tid=sk1306_tid(); if($tid>0 && isset($cols['tenant_id'])){$params[]=$tid;return ($alias!==''?$alias.'.':'').sk1306_ident('tenant_id').'=?';} return '1=1';
}
function sk1306_visibility(array $cols): array {
    if(function_exists('sk1305_visibility')){$x=[];return sk1305_visibility($cols,$x);} // visibility has no bound params in v13.0.5
    $status=sk1306_first($cols,['status','publication_status','state']);
    if($status!=='')return ['sql'=>'LOWER(COALESCE('.sk1306_ident($status).",'')) IN ('active','approved','published','live','enabled','public')",'field'=>$status,'mode'=>'status'];
    foreach(['is_published','published','is_approved','approved','is_active','active','enabled','is_enabled','visible','is_visible'] as $f)if(isset($cols[$f]))return ['sql'=>'COALESCE('.sk1306_ident($f).',0)=1','field'=>$f,'mode'=>'boolean'];
    return ['sql'=>'1=1','field'=>'','mode'=>'all'];
}
function sk1306_contract(array $module): ?array {
    $table=sk1306_existing_table($module['tables']??[]); if($table==='')return null;
    $cols=sk1306_cols($table);$pk=sk1306_pk($table,$cols);if($pk==='')return null;
    $title=sk1306_first($cols,$module['title']??[]);$slug=sk1306_first($cols,$module['slug']??[]);$media=sk1306_first($cols,$module['media']??[]);
    $user=sk1306_first($cols,['user_id','owner_id','created_by','seller_id','vendor_id']);
    $cat=isset($cols['category_id'])?'category_id':'';$catTable=$cat!==''?sk1306_existing_table($module['category_tables']??[]):'';
    $vis=sk1306_visibility($cols);
    return ['key'=>(string)($module['key']??$table),'label'=>(string)($module['label']??$table),'table'=>$table,'cols'=>$cols,'pk'=>$pk,'title'=>$title,'slug'=>$slug,'media'=>$media,'user'=>$user,'category'=>$cat,'category_table'=>$catTable,'visibility'=>$vis];
}
function sk1306_slugify(string $text): string {
    $text=trim($text);if($text==='')return '';
    if(function_exists('iconv')){$x=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text);if(is_string($x)&&$x!=='')$text=$x;}
    $text=strtolower($text);$text=preg_replace('/[^a-z0-9]+/','-',$text)??'';$text=trim($text,'-');return substr($text,0,150);
}
function sk1306_preview_contract(array $c): array {
    $pdo=sk1306_db();$cols=$c['cols'];$params=[];$scope=sk1306_scope($cols,$params);$vis=$c['visibility']['sql'];$where='('.$scope.') AND ('.$vis.')';
    $out=['key'=>$c['key'],'label'=>$c['label'],'table'=>$c['table'],'duplicate_slug_groups'=>0,'duplicate_slug_rows'=>0,'missing_slug'=>0,'orphan_category'=>0,'repairable_orphan_category'=>0,'orphan_user'=>0,'missing_title'=>0,'missing_media'=>0,'safe_changes'=>0,'notes'=>[]];
    try{
        if($c['slug']!==''){$s=sk1306_ident($c['slug']);$sql='SELECT COUNT(*) groups_count,COALESCE(SUM(cnt),0) rows_count FROM (SELECT COUNT(*) cnt FROM '.sk1306_ident($c['table']).' WHERE '.$where.' AND TRIM(COALESCE('.$s.",''))<>'' GROUP BY ".$s.' HAVING COUNT(*)>1) d';$q=$pdo->prepare($sql);$q->execute($params);$r=$q->fetch()?:[];$out['duplicate_slug_groups']=(int)($r['groups_count']??0);$out['duplicate_slug_rows']=(int)($r['rows_count']??0);
            if($c['title']!==''){$t=sk1306_ident($c['title']);$q=$pdo->prepare('SELECT COUNT(*) FROM '.sk1306_ident($c['table']).' WHERE '.$where.' AND TRIM(COALESCE('.$s.",''))='' AND TRIM(COALESCE(".$t.",''))<>''");$q->execute($params);$out['missing_slug']=(int)$q->fetchColumn();}
        }
        if($c['title']!==''){$t=sk1306_ident($c['title']);$q=$pdo->prepare('SELECT COUNT(*) FROM '.sk1306_ident($c['table']).' WHERE '.$where.' AND TRIM(COALESCE('.$t.",''))=''");$q->execute($params);$out['missing_title']=(int)$q->fetchColumn();}
        if($c['media']!==''){$m=sk1306_ident($c['media']);$q=$pdo->prepare('SELECT COUNT(*) FROM '.sk1306_ident($c['table']).' WHERE '.$where.' AND TRIM(COALESCE('.$m.",''))=''");$q->execute($params);$out['missing_media']=(int)$q->fetchColumn();}
        if($c['user']!==''&&sk1306_table('users')){$u=sk1306_ident($c['user']);$a=[];$sc=sk1306_scope($cols,$a,'x');$sql='SELECT COUNT(*) FROM '.sk1306_ident($c['table']).' x LEFT JOIN `users` u ON u.id=x.'.$u.' WHERE ('.$sc.') AND ('.$vis.') AND x.'.$u.' IS NOT NULL AND x.'.$u.'<>0 AND u.id IS NULL';$q=$pdo->prepare($sql);$q->execute($a);$out['orphan_user']=(int)$q->fetchColumn();}
        if($c['category']!==''&&$c['category_table']!==''){$a=[];$sc=sk1306_scope($cols,$a,'x');$sql='SELECT COUNT(*) FROM '.sk1306_ident($c['table']).' x LEFT JOIN '.sk1306_ident($c['category_table']).' c ON c.id=x.`category_id` WHERE ('.$sc.') AND ('.$vis.') AND x.`category_id` IS NOT NULL AND x.`category_id`<>0 AND c.id IS NULL';$q=$pdo->prepare($sql);$q->execute($a);$out['orphan_category']=(int)$q->fetchColumn();$nullable=strtoupper((string)($cols['category_id']['IS_NULLABLE']??''))==='YES';if($nullable)$out['repairable_orphan_category']=$out['orphan_category'];else if($out['orphan_category']>0)$out['notes'][]='Orphan category rows are read-only because category_id is NOT NULL.';}
    }catch(Throwable $e){$out['notes'][]='Preview note: '.$e->getMessage();}
    $out['safe_changes']=max(0,$out['duplicate_slug_rows']-$out['duplicate_slug_groups'])+$out['missing_slug']+$out['repairable_orphan_category'];
    if($out['orphan_user']>0)$out['notes'][]='Missing owners/users are never automatically reassigned.';
    if($out['missing_title']>0)$out['notes'][]='Missing titles are never fabricated.';
    if($out['missing_media']>0)$out['notes'][]='Missing media is never replaced with fake content.';
    return $out;
}
function sk1306_preview(): array {
    $mods=[];$tot=['safe_changes'=>0,'duplicate_groups'=>0,'missing_slug'=>0,'repairable_orphan_category'=>0,'manual_only'=>0];
    foreach(sk1306_modules() as $m){$c=sk1306_contract($m);if(!$c)continue;$p=sk1306_preview_contract($c);$mods[]=$p;$tot['safe_changes']+=$p['safe_changes'];$tot['duplicate_groups']+=$p['duplicate_slug_groups'];$tot['missing_slug']+=$p['missing_slug'];$tot['repairable_orphan_category']+=$p['repairable_orphan_category'];$tot['manual_only']+=$p['orphan_user']+$p['missing_title']+$p['missing_media']+max(0,$p['orphan_category']-$p['repairable_orphan_category']);}
    return ['version'=>'13.0.6.0','modules'=>$mods,'totals'=>$tot];
}
function sk1306_actor_id(): ?int {$u=function_exists('current_user')?(current_user()?:[]):[];return isset($u['id'])?(int)$u['id']:null;}
function sk1306_log(string $batch,string $module,string $table,string $recordId,string $action,array $before,array $after): void {
    if(!sk1306_table('data_repair_actions_v1306'))return;
    try{$q=sk1306_db()->prepare('INSERT INTO data_repair_actions_v1306(batch_key,tenant_id,actor_user_id,module_key,table_name,record_id,action_key,before_json,after_json,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');$q->execute([$batch,sk1306_tid(),sk1306_actor_id(),$module,$table,$recordId,$action,json_encode($before,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),json_encode($after,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}
}
function sk1306_unique_slug(string $base,array &$used,string $id): string {
    $base=substr(trim($base,'-'),0,145);if($base==='')$base='item';$candidate=$base;
    if(!isset($used[strtolower($candidate)])){$used[strtolower($candidate)]=1;return $candidate;}
    $suffix='-'.$id;$candidate=substr($base,0,max(1,150-strlen($suffix))).$suffix;$n=2;
    while(isset($used[strtolower($candidate)])&&$n<1000){$suffix='-'.$id.'-'.$n;$candidate=substr($base,0,max(1,150-strlen($suffix))).$suffix;$n++;}
    $used[strtolower($candidate)]=1;return $candidate;
}
function sk1306_used_slugs(array $c): array {
    $used=[];if($c['slug']==='')return $used;$params=[];$scope=sk1306_scope($c['cols'],$params);try{$q=sk1306_db()->prepare('SELECT '.sk1306_ident($c['slug']).' s FROM '.sk1306_ident($c['table']).' WHERE '.$scope.' AND TRIM(COALESCE('.sk1306_ident($c['slug']).",''))<>''");$q->execute($params);foreach($q->fetchAll()?:[] as $r){$s=strtolower(trim((string)($r['s']??'')));if($s!=='')$used[$s]=1;}}catch(Throwable $e){}return $used;
}
function sk1306_update_row(array $c,string $field,$value,$id): bool {
    $params=[$value,$id];$where=sk1306_ident($c['pk']).'=?';$tid=sk1306_tid();if($tid>0&&isset($c['cols']['tenant_id'])){$where.=' AND `tenant_id`=?';$params[]=$tid;}
    $q=sk1306_db()->prepare('UPDATE '.sk1306_ident($c['table']).' SET '.sk1306_ident($field).'=? WHERE '.$where.' LIMIT 1');$q->execute($params);return $q->rowCount()>=0;
}
function sk1306_repair_module(string $moduleKey,array $options): array {
    $target=null;foreach(sk1306_modules() as $m)if((string)($m['key']??'')===$moduleKey){$target=$m;break;}if(!$target)throw new InvalidArgumentException('Unknown module.');$c=sk1306_contract($target);if(!$c)throw new RuntimeException('Module table is not available.');
    $result=['module'=>$moduleKey,'changed'=>0,'duplicate_slug'=>0,'missing_slug'=>0,'orphan_category'=>0,'errors'=>[],'batch'=>gmdate('YmdHis').'-'.bin2hex(random_bytes(3))];$pdo=sk1306_db();$limit=500;
    try{$pdo->beginTransaction();$used=sk1306_used_slugs($c);
        if(!empty($options['duplicate_slug'])&&$c['slug']!==''){
            $params=[];$scope=sk1306_scope($c['cols'],$params);$vis=$c['visibility']['sql'];$s=sk1306_ident($c['slug']);$pk=sk1306_ident($c['pk']);
            $sql='SELECT '.$pk.' rid,'.$s.' slug_value FROM '.sk1306_ident($c['table']).' WHERE ('.$scope.') AND ('.$vis.') AND TRIM(COALESCE('.$s.",''))<>'' ORDER BY ".$s.','.$pk.' LIMIT 10000';$q=$pdo->prepare($sql);$q->execute($params);$rows=$q->fetchAll()?:[];$seen=[];
            foreach($rows as $r){$old=trim((string)$r['slug_value']);$key=strtolower($old);if(!isset($seen[$key])){$seen[$key]=1;continue;}if($result['duplicate_slug']>=$limit)break;$id=(string)$r['rid'];$new=sk1306_unique_slug($old,$used,$id);if($new===$old)continue;sk1306_update_row($c,$c['slug'],$new,$r['rid']);sk1306_log($result['batch'],$moduleKey,$c['table'],$id,'duplicate_slug',['slug'=>$old],['slug'=>$new]);$result['duplicate_slug']++;$result['changed']++;}
        }
        if(!empty($options['missing_slug'])&&$c['slug']!==''&&$c['title']!==''){
            $params=[];$scope=sk1306_scope($c['cols'],$params);$vis=$c['visibility']['sql'];$s=sk1306_ident($c['slug']);$t=sk1306_ident($c['title']);$pk=sk1306_ident($c['pk']);$sql='SELECT '.$pk.' rid,'.$t.' title_value FROM '.sk1306_ident($c['table']).' WHERE ('.$scope.') AND ('.$vis.') AND TRIM(COALESCE('.$s.",''))='' AND TRIM(COALESCE(".$t.",''))<>'' ORDER BY ".$pk.' LIMIT '.$limit;$q=$pdo->prepare($sql);$q->execute($params);foreach($q->fetchAll()?:[] as $r){$id=(string)$r['rid'];$base=sk1306_slugify((string)$r['title_value']);if($base==='')continue;$new=sk1306_unique_slug($base,$used,$id);sk1306_update_row($c,$c['slug'],$new,$r['rid']);sk1306_log($result['batch'],$moduleKey,$c['table'],$id,'missing_slug',['slug'=>''],['slug'=>$new]);$result['missing_slug']++;$result['changed']++;}
        }
        if(!empty($options['orphan_category'])&&$c['category']!==''&&$c['category_table']!==''&&strtoupper((string)($c['cols']['category_id']['IS_NULLABLE']??''))==='YES'){
            $params=[];$scope=sk1306_scope($c['cols'],$params,'x');$vis=$c['visibility']['sql'];$pk=sk1306_ident($c['pk']);$sql='SELECT x.'.$pk.' rid,x.`category_id` old_category FROM '.sk1306_ident($c['table']).' x LEFT JOIN '.sk1306_ident($c['category_table']).' c ON c.id=x.`category_id` WHERE ('.$scope.') AND ('.$vis.') AND x.`category_id` IS NOT NULL AND x.`category_id`<>0 AND c.id IS NULL ORDER BY x.'.$pk.' LIMIT '.$limit;$q=$pdo->prepare($sql);$q->execute($params);foreach($q->fetchAll()?:[] as $r){$id=(string)$r['rid'];sk1306_update_row($c,'category_id',null,$r['rid']);sk1306_log($result['batch'],$moduleKey,$c['table'],$id,'orphan_category',['category_id'=>$r['old_category']],['category_id'=>null]);$result['orphan_category']++;$result['changed']++;}
        }
        $pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$result['errors'][]=$e->getMessage();}
    return $result;
}
function sk1306_refresh_runtime(): array {
    $r=['settings'=>0,'cache_rows'=>0,'errors'=>[]];$pdo=sk1306_db();$stamp=gmdate('Y-m-d H:i:s').' UTC';
    try{if(sk1306_table('settings')){$q=$pdo->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('public_data_cache_version','13.0.6.0'),('public_data_cache_bust',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$q->execute([$stamp]);$r['settings']=$q->rowCount();}}catch(Throwable $e){$r['errors'][]='Settings: '.$e->getMessage();}
    try{if(sk1306_table('ai_recommendation_cache')){$cols=sk1306_cols('ai_recommendation_cache');if(isset($cols['tenant_id'])&&sk1306_tid()>0){$q=$pdo->prepare('DELETE FROM `ai_recommendation_cache` WHERE `tenant_id`=?');$q->execute([sk1306_tid()]);$r['cache_rows']=$q->rowCount();}}}catch(Throwable $e){$r['errors'][]='Cache: '.$e->getMessage();}
    return $r;
}
}
