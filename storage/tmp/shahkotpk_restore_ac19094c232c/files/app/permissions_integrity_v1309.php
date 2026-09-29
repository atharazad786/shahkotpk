<?php
declare(strict_types=1);
/** ShahkotPK v13.0.9.0 — on-demand permissions, roles and admin-access integrity. */
if (!function_exists('sk1309_fresh_audit')) {
function sk1309_db(): PDO {
    if (function_exists('sk1308_db')) return sk1308_db();
    if (function_exists('sk1307_db')) return sk1307_db();
    if (function_exists('sk1306_db')) return sk1306_db();
    if (function_exists('sk1305_db')) return sk1305_db();
    if (function_exists('sk1301_db')) return sk1301_db();
    return db();
}
function sk1309_tid(): int {
    if (function_exists('sk1308_tid')) return sk1308_tid();
    if (function_exists('sk1307_tid')) return sk1307_tid();
    if (function_exists('sk1306_tid')) return sk1306_tid();
    if (function_exists('sk1305_tid')) return sk1305_tid();
    if (function_exists('sk1301_tid')) return sk1301_tid();
    return function_exists('tenant_id') ? (int)tenant_id() : 0;
}
function sk1309_actor(): array {
    try { $u=function_exists('current_user')?(current_user()?:[]):[]; return is_array($u)?$u:[]; } catch (Throwable $e) { return []; }
}
function sk1309_actor_id(): ?int { $u=sk1309_actor(); return isset($u['id'])?(int)$u['id']:null; }
function sk1309_table(string $table): bool {
    if (function_exists('sk1308_table')) return sk1308_table($table);
    if (function_exists('sk1307_table')) return sk1307_table($table);
    if (function_exists('sk1306_table')) return sk1306_table($table);
    try{$q=sk1309_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1309_cols(string $table): array {
    if (function_exists('sk1308_cols')) return sk1308_cols($table);
    if (function_exists('sk1307_cols')) return sk1307_cols($table);
    if (function_exists('sk1306_cols')) return sk1306_cols($table);
    $out=[];try{$q=sk1309_db()->prepare('SELECT COLUMN_NAME,DATA_TYPE,IS_NULLABLE,COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$table]);foreach($q->fetchAll()?:[] as $r)$out[(string)$r['COLUMN_NAME']]=$r;}catch(Throwable $e){}return $out;
}
function sk1309_ident(string $name): string { if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name))throw new InvalidArgumentException('Unsafe SQL identifier');return '`'.$name.'`'; }
function sk1309_scalar(string $sql,array $params=[],$default=0) { try{$q=sk1309_db()->prepare($sql);$q->execute($params);$v=$q->fetchColumn();return $v===false?$default:$v;}catch(Throwable $e){return $default;} }
function sk1309_required_tables(): array {
    return ['access_features_v1300','access_roles_v1300','access_role_features_v1300','access_user_overrides_v1300','tenant_members','users'];
}
function sk1309_table_status(): array { $out=[];foreach(sk1309_required_tables() as $t)$out[]=['table'=>$t,'exists'=>sk1309_table($t)];return $out; }
function sk1309_feature_stats(int $tid): array {
    $o=['core_active'=>0,'admin_active'=>0,'custom_active'=>0,'duplicate_routes'=>[]];
    if(sk1309_table('access_features_v1300')){
        $o['core_active']=(int)sk1309_scalar('SELECT COUNT(*) FROM access_features_v1300 WHERE active=1',[],0);
        $o['admin_active']=(int)sk1309_scalar('SELECT COUNT(*) FROM access_features_v1300 WHERE active=1 AND admin_only=1',[],0);
        try{$q=sk1309_db()->query('SELECT admin_only,route_pattern,COUNT(*) c FROM access_features_v1300 WHERE active=1 AND route_pattern IS NOT NULL AND route_pattern<>"" GROUP BY admin_only,route_pattern HAVING COUNT(*)>1 ORDER BY c DESC LIMIT 40');$o['duplicate_routes']=$q?$q->fetchAll():[];}catch(Throwable $e){}
    }
    if($tid>0&&sk1309_table('access_tenant_custom_features_v1301'))$o['custom_active']=(int)sk1309_scalar('SELECT COUNT(*) FROM access_tenant_custom_features_v1301 WHERE tenant_id=? AND active=1',[$tid],0);
    return $o;
}
function sk1309_roles(int $tid): array {
    $out=[];if($tid<1||!sk1309_table('access_roles_v1300'))return $out;
    try{$q=sk1309_db()->prepare('SELECT role_key,name,is_system,active FROM access_roles_v1300 WHERE tenant_id=? ORDER BY active DESC,is_system DESC,role_key LIMIT 100');$q->execute([$tid]);$out=$q->fetchAll()?:[];}catch(Throwable $e){}
    foreach($out as &$r){$rk=(string)($r['role_key']??'');$r['mapped']=(int)sk1309_scalar('SELECT COUNT(*) FROM access_role_features_v1300 WHERE tenant_id=? AND role_key=?',[$tid,$rk],0);$r['enabled']=(int)sk1309_scalar('SELECT COUNT(*) FROM access_role_features_v1300 WHERE tenant_id=? AND role_key=? AND enabled=1',[$tid,$rk],0);}unset($r);
    return $out;
}
function sk1309_feature_keys(int $tid): array {
    static $cache=[];if(isset($cache[$tid]))return $cache[$tid];$set=[];
    if(sk1309_table('access_features_v1300'))try{$q=sk1309_db()->query('SELECT feature_key FROM access_features_v1300');foreach($q?($q->fetchAll()?:[]):[] as $r)$set[(string)$r['feature_key']]=true;}catch(Throwable $e){}
    if($tid>0&&sk1309_table('access_tenant_custom_features_v1301'))try{$q=sk1309_db()->prepare('SELECT feature_key FROM access_tenant_custom_features_v1301 WHERE tenant_id=?');$q->execute([$tid]);foreach($q->fetchAll()?:[] as $r)$set[(string)$r['feature_key']]=true;}catch(Throwable $e){}
    return $cache[$tid]=$set;
}
function sk1309_role_keys(int $tid): array {
    static $cache=[];if(isset($cache[$tid]))return $cache[$tid];$set=[];
    if($tid>0&&sk1309_table('access_roles_v1300'))try{$q=sk1309_db()->prepare('SELECT role_key FROM access_roles_v1300 WHERE tenant_id=?');$q->execute([$tid]);foreach($q->fetchAll()?:[] as $r)$set[(string)$r['role_key']]=true;}catch(Throwable $e){}
    return $cache[$tid]=$set;
}
function sk1309_feature_exists(int $tid,string $key): bool { $set=sk1309_feature_keys($tid);return $key!==''&&!empty($set[$key]); }
function sk1309_orphan_role_features(int $tid): array {
    $out=[];if($tid<1||!sk1309_table('access_role_features_v1300'))return $out;$roles=sk1309_role_keys($tid);$features=sk1309_feature_keys($tid);
    try{$q=sk1309_db()->prepare('SELECT role_key,feature_key,enabled FROM access_role_features_v1300 WHERE tenant_id=? ORDER BY role_key,feature_key LIMIT 700');$q->execute([$tid]);foreach($q->fetchAll()?:[] as $r){$role=(string)$r['role_key'];$fk=(string)$r['feature_key'];$roleOk=!empty($roles[$role]);$featureOk=!empty($features[$fk]);if(!$roleOk||!$featureOk){$out[]=['role_key'=>$role,'feature_key'=>$fk,'enabled'=>(int)$r['enabled'],'missing_role'=>!$roleOk,'missing_feature'=>!$featureOk];if(count($out)>=80)break;}}}catch(Throwable $e){}
    return $out;
}
function sk1309_orphan_overrides(int $tid): array {
    $out=[];if($tid<1||!sk1309_table('access_user_overrides_v1300'))return $out;$rows=[];$features=sk1309_feature_keys($tid);
    try{$q=sk1309_db()->prepare('SELECT user_id,feature_key,effect FROM access_user_overrides_v1300 WHERE tenant_id=? ORDER BY user_id DESC LIMIT 500');$q->execute([$tid]);$rows=$q->fetchAll()?:[];}catch(Throwable $e){return $out;}
    $userSet=[];if(sk1309_table('users')&&$rows){$ids=array_values(array_unique(array_filter(array_map(static fn($r)=>(int)($r['user_id']??0),$rows))));if($ids){$ph=implode(',',array_fill(0,count($ids),'?'));try{$q=sk1309_db()->prepare('SELECT id FROM users WHERE id IN ('.$ph.')');$q->execute($ids);foreach($q->fetchAll()?:[] as $u)$userSet[(int)$u['id']]=true;}catch(Throwable $e){}}}
    foreach($rows as $r){$uid=(int)$r['user_id'];$fk=(string)$r['feature_key'];$userOk=!sk1309_table('users')||!empty($userSet[$uid]);$featureOk=!empty($features[$fk]);$effect=strtolower((string)$r['effect']);$effectOk=in_array($effect,['allow','deny'],true);if(!$userOk||!$featureOk||!$effectOk){$out[]=['user_id'=>$uid,'feature_key'=>$fk,'effect'=>$effect,'missing_user'=>!$userOk,'missing_feature'=>!$featureOk,'invalid_effect'=>!$effectOk];if(count($out)>=80)break;}}
    return $out;
}
function sk1309_member_orphans(int $tid): array {
    $out=[];if($tid<1||!sk1309_table('tenant_members')||!sk1309_table('users'))return $out;$c=sk1309_cols('tenant_members');if(!isset($c['user_id'])||!isset($c['tenant_id']))return $out;
    try{$q=sk1309_db()->prepare('SELECT tm.id,tm.user_id FROM tenant_members tm LEFT JOIN users u ON u.id=tm.user_id WHERE tm.tenant_id=? AND u.id IS NULL LIMIT 80');$q->execute([$tid]);$out=$q->fetchAll()?:[];}catch(Throwable $e){}
    return $out;
}
function sk1309_default_gaps(int $tid): array {
    $out=[];if($tid<1||!function_exists('sk1300_role_templates')||!function_exists('sk1300_role_default_features')||!sk1309_table('access_roles_v1300')||!sk1309_table('access_role_features_v1300'))return $out;
    foreach(sk1300_role_templates() as $rk=>$meta){$roleExists=(int)sk1309_scalar('SELECT COUNT(*) FROM access_roles_v1300 WHERE tenant_id=? AND role_key=? AND active=1',[$tid,$rk],0)>0;if(!$roleExists){$out[]=['role_key'=>$rk,'feature_key'=>'','type'=>'missing_role','label'=>(string)($meta[0]??$rk)];continue;}foreach(sk1300_role_default_features((string)$rk) as $fk){if(!sk1309_feature_exists($tid,(string)$fk))continue;$exists=(int)sk1309_scalar('SELECT COUNT(*) FROM access_role_features_v1300 WHERE tenant_id=? AND role_key=? AND feature_key=?',[$tid,$rk,$fk],0)>0;if(!$exists)$out[]=['role_key'=>$rk,'feature_key'=>(string)$fk,'type'=>'missing_default_mapping','label'=>(string)($meta[0]??$rk)];if(count($out)>=120)break 2;}}
    return $out;
}
function sk1309_sidebar_coverage(int $tid): array {
    $root=__DIR__.'/sidebar-modules';$rows=[];$seen=[];if(!is_dir($root))return ['checked'=>0,'missing_files'=>[],'unmapped'=>[]];
    foreach(glob($root.'/*.json')?:[] as $file){$j=json_decode((string)@file_get_contents($file),true);$items=(is_array($j)&&isset($j['items'])&&is_array($j['items']))?$j['items']:(is_array($j)?[$j]:[]);foreach($items as $it){if(!is_array($it)||empty($it['enabled']))continue;$url=trim((string)($it['url']??''));if($url===''||!str_starts_with($url,'/admin/'))continue;if(isset($seen[$url]))continue;$seen[$url]=1;$rows[]=['url'=>$url,'label'=>(string)($it['label']??$url)];if(count($rows)>=180)break 2;}}
    $missing=[];$unmapped=[];$appRoot=dirname(__DIR__);
    foreach($rows as $r){$path=parse_url($r['url'],PHP_URL_PATH)?:$r['url'];$rel=ltrim($path,'/');$physical=is_file($appRoot.'/'.$rel);if(!$physical)$missing[]=$r;$mapped=false;try{if(function_exists('sk1300_path_feature'))$mapped=(bool)sk1300_path_feature($path,true);else if(sk1309_table('access_features_v1300'))$mapped=(int)sk1309_scalar('SELECT COUNT(*) FROM access_features_v1300 WHERE active=1 AND admin_only=1 AND route_pattern IN (?,?)',[$path,$path.'*'],0)>0;}catch(Throwable $e){}if(!$mapped)$unmapped[]=$r;}
    return ['checked'=>count($rows),'missing_files'=>array_slice($missing,0,60),'unmapped'=>array_slice($unmapped,0,60)];
}
function sk1309_current_actor_state(int $tid): array {
    $u=sk1309_actor();$r=['id'=>(int)($u['id']??0),'raw_role'=>'','audience'=>'','staff_role'=>'','super_admin'=>false,'tenant_admin'=>false];
    try{if(function_exists('sk1300_role_raw'))$r['raw_role']=sk1300_role_raw($u);if(function_exists('sk1300_audience'))$r['audience']=sk1300_audience($u,$tid);if(function_exists('sk1300_staff_role'))$r['staff_role']=sk1300_staff_role($u,$tid);if(function_exists('sk1300_super_admin'))$r['super_admin']=sk1300_super_admin($u);if(function_exists('sk1300_tenant_admin'))$r['tenant_admin']=sk1300_tenant_admin($u,$tid);}catch(Throwable $e){}
    return $r;
}
function sk1309_findings(array $tables,array $features,array $roles,array $orphans,array $overrides,array $members,array $gaps,array $sidebar,array $actor): array {
    $f=[];foreach($tables as $t)if(!$t['exists'])$f[]=['severity'=>'high','key'=>'missing_table','message'=>'Required access table '.$t['table'].' is missing.'];
    if(!$actor['super_admin']&&$actor['audience']!=='staff')$f[]=['severity'=>'high','key'=>'actor_access','message'=>'Current admin request is not classified as staff/super-admin by the access layer.'];
    if(count($orphans)>0)$f[]=['severity'=>'medium','key'=>'orphan_role_features','message'=>count($orphans).' sampled role-feature mapping(s) reference a missing role or feature.'];
    if(count($overrides)>0)$f[]=['severity'=>'medium','key'=>'orphan_overrides','message'=>count($overrides).' sampled user override(s) have a missing user/feature or invalid effect.'];
    if(count($members)>0)$f[]=['severity'=>'medium','key'=>'orphan_members','message'=>count($members).' tenant-member record(s) reference a missing user.'];
    if(count($gaps)>0)$f[]=['severity'=>'medium','key'=>'default_gaps','message'=>count($gaps).' system role/default permission mapping(s) are missing.'];
    if(count($features['duplicate_routes'])>0)$f[]=['severity'=>'low','key'=>'duplicate_routes','message'=>count($features['duplicate_routes']).' duplicate active route-pattern group(s) were found for review.'];
    if(count($sidebar['missing_files'])>0)$f[]=['severity'=>'medium','key'=>'sidebar_missing','message'=>count($sidebar['missing_files']).' sidebar route(s) point to a missing physical admin PHP file.'];
    if(count($sidebar['unmapped'])>0)$f[]=['severity'=>'medium','key'=>'sidebar_unmapped','message'=>count($sidebar['unmapped']).' sidebar route(s) have no active admin permission feature mapping.'];
    if(count($roles)===0)$f[]=['severity'=>'high','key'=>'no_roles','message'=>'No active access roles were found for the current tenant.'];
    return $f;
}
function sk1309_score(array $findings): int { $s=100;foreach($findings as $f)$s-=($f['severity']==='high'?20:($f['severity']==='medium'?8:2));return max(0,min(100,$s)); }
function sk1309_save_run(array $r): int { if(!sk1309_table('permission_integrity_runs_v1309'))return 0;try{$high=0;$medium=0;foreach($r['findings'] as $f){if($f['severity']==='high')$high++;elseif($f['severity']==='medium')$medium++;}$q=sk1309_db()->prepare('INSERT INTO permission_integrity_runs_v1309(tenant_id,actor_user_id,score,high_count,medium_count,summary_json,created_at) VALUES(?,?,?,?,?,?,NOW())');$q->execute([sk1309_tid(),sk1309_actor_id(),$r['score'],$high,$medium,json_encode($r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);return (int)sk1309_db()->lastInsertId();}catch(Throwable $e){return 0;} }
function sk1309_log_action(string $key,array $result): void { if(!sk1309_table('permission_integrity_actions_v1309'))return;try{$q=sk1309_db()->prepare('INSERT INTO permission_integrity_actions_v1309(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1309_tid(),sk1309_actor_id(),$key,json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){} }
function sk1309_fresh_audit(): array {
    $tid=sk1309_tid();$tables=sk1309_table_status();$features=sk1309_feature_stats($tid);$roles=sk1309_roles($tid);$orphans=sk1309_orphan_role_features($tid);$overrides=sk1309_orphan_overrides($tid);$members=sk1309_member_orphans($tid);$gaps=sk1309_default_gaps($tid);$sidebar=sk1309_sidebar_coverage($tid);$actor=sk1309_current_actor_state($tid);$findings=sk1309_findings($tables,$features,$roles,$orphans,$overrides,$members,$gaps,$sidebar,$actor);$r=['tenant_id'=>$tid,'score'=>sk1309_score($findings),'tables'=>$tables,'features'=>$features,'roles'=>$roles,'orphan_role_features'=>$orphans,'orphan_overrides'=>$overrides,'orphan_members'=>$members,'default_gaps'=>$gaps,'sidebar'=>$sidebar,'actor'=>$actor,'findings'=>$findings,'generated_at'=>gmdate('c')];$r['run_id']=sk1309_save_run($r);return $r;
}
function sk1309_last_audit(): ?array { if(!sk1309_table('permission_integrity_runs_v1309'))return null;try{$q=sk1309_db()->prepare('SELECT summary_json,id FROM permission_integrity_runs_v1309 WHERE tenant_id=? ORDER BY id DESC LIMIT 1');$q->execute([sk1309_tid()]);$row=$q->fetch();if(!$row)return null;$r=json_decode((string)$row['summary_json'],true);if(!is_array($r))return null;$r['run_id']=(int)$row['id'];return $r;}catch(Throwable $e){return null;} }
function sk1309_safe_repair(): array {
    $tid=sk1309_tid();$r=['roles_inserted'=>0,'mappings_inserted'=>0,'notes'=>[],'errors'=>[]];if($tid<1){$r['errors'][]='No active tenant id is available.';return $r;}if(!function_exists('sk1300_role_templates')||!function_exists('sk1300_role_default_features')){$r['errors'][]='Core access-control role templates are unavailable.';return $r;}if(!sk1309_table('access_roles_v1300')||!sk1309_table('access_role_features_v1300')){$r['errors'][]='Required role tables are missing.';return $r;}
    $pdo=sk1309_db();try{$pdo->beginTransaction();foreach(sk1300_role_templates() as $rk=>$meta){$exists=(int)sk1309_scalar('SELECT COUNT(*) FROM access_roles_v1300 WHERE tenant_id=? AND role_key=?',[$tid,$rk],0)>0;if(!$exists){$q=$pdo->prepare('INSERT INTO access_roles_v1300(tenant_id,role_key,name,description,is_system,active,sort_order,created_at,updated_at) VALUES(?,?,?,?,1,1,100,NOW(),NOW())');$q->execute([$tid,$rk,(string)($meta[0]??$rk),(string)($meta[1]??'System role')]);$r['roles_inserted']++;}foreach(sk1300_role_default_features((string)$rk) as $fk){if(!sk1309_feature_exists($tid,(string)$fk))continue;$q=$pdo->prepare('SELECT enabled FROM access_role_features_v1300 WHERE tenant_id=? AND role_key=? AND feature_key=? LIMIT 1');$q->execute([$tid,$rk,$fk]);$current=$q->fetchColumn();if($current!==false)continue;$ins=$pdo->prepare('INSERT INTO access_role_features_v1300(tenant_id,role_key,feature_key,enabled,updated_at) VALUES(?,?,?,1,NOW())');$ins->execute([$tid,$rk,$fk]);$r['mappings_inserted']++;}}$pdo->commit();$r['notes'][]='Existing role-feature enabled/disabled values and user allow/deny overrides were not changed.';$r['notes'][]='Orphan records were reported only; none were deleted automatically.';}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$r['errors'][]=$e->getMessage();}
    sk1309_log_action('safe_permission_repair',$r);return $r;
}
}
