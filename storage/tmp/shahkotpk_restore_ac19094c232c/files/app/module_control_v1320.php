<?php
declare(strict_types=1);
/** ShahkotPK v13.2.0 — tenant-level public module visibility control. */
if (!function_exists('sk1320mc_snapshot')) {
function sk1320mc_db(): PDO { return db(); }
function sk1320mc_tid(): int {
    try { if (function_exists('tenant_id')) return max(0,(int)tenant_id()); if (function_exists('current_tenant')) { $t=current_tenant(); return max(0,(int)($t['id']??0)); } } catch (Throwable $e) {}
    return 0;
}
function sk1320mc_table(string $t): bool {
    static $c=[]; if (array_key_exists($t,$c)) return $c[$t];
    try { $q=sk1320mc_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1'); $q->execute([$t]); return $c[$t]=(bool)$q->fetchColumn(); } catch(Throwable $e){ return $c[$t]=false; }
}
function sk1320mc_cols(string $t): array {
    static $c=[]; if(isset($c[$t])) return $c[$t]; $o=[];
    try { $q=sk1320mc_db()->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'); $q->execute([$t]); foreach($q->fetchAll()?:[] as $r)$o[(string)$r['COLUMN_NAME']]=true; } catch(Throwable $e){}
    return $c[$t]=$o;
}
function sk1320mc_h($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function sk1320mc_allowed(): bool {
    try { $me=function_exists('current_user')?current_user():null; } catch(Throwable $e){$me=null;}
    if(!$me) return false;
    if(function_exists('sk1300_super_admin')&&sk1300_super_admin($me)) return true;
    if(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($me)) return true;
    if(function_exists('sk1300_can')&&sk1300_can('admin.module_control',$me)) return true;
    if(function_exists('has_permission')&&(has_permission('settings.manage',$me)||has_permission('admin.settings',$me))) return true;
    return in_array(strtolower((string)($me['role']??'')),['admin','super_admin','administrator'],true);
}
function sk1320mc_overrides(): array {
    $tid=sk1320mc_tid(); $out=[];
    if(!sk1320mc_table('access_tenant_feature_overrides_v1300')) return $out;
    try { $q=sk1320mc_db()->prepare('SELECT feature_key,enabled FROM access_tenant_feature_overrides_v1300 WHERE tenant_id=?');$q->execute([$tid]);foreach($q->fetchAll()?:[] as $r)$out[(string)$r['feature_key']]=(int)$r['enabled']; } catch(Throwable $e){}
    return $out;
}
function sk1320mc_features(): array {
    $rows=[];$tid=sk1320mc_tid();
    if(sk1320mc_table('access_features_v1300')){
        try{$q=sk1320mc_db()->query('SELECT feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source FROM access_features_v1300 WHERE active=1 AND admin_only=0 ORDER BY module_group,label LIMIT 600');$rows=$q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){}
    }
    if($tid>0&&sk1320mc_table('access_tenant_custom_features_v1301')){
        try{$q=sk1320mc_db()->prepare('SELECT feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,"tenant_custom" source FROM access_tenant_custom_features_v1301 WHERE tenant_id=? AND active=1 AND admin_only=0 ORDER BY module_group,label LIMIT 300');$q->execute([$tid]);$rows=array_merge($rows,$q->fetchAll(PDO::FETCH_ASSOC)?:[]);}catch(Throwable $e){}
    }
    $ov=sk1320mc_overrides();$ded=[];
    foreach($rows as $r){
        $key=trim((string)($r['feature_key']??''));if($key==='')continue;
        $r['feature_key']=$key;$r['label']=trim((string)($r['label']??$key))?:$key;$r['description']=trim((string)($r['description']??''));
        $r['module_group']=trim((string)($r['module_group']??''))?:'Public Platform';$r['route_pattern']=trim((string)($r['route_pattern']??''));
        $r['override']=array_key_exists($key,$ov)?(int)$ov[$key]:null;
        $r['tenant_enabled']=$r['override']===null?1:(int)$r['override'];
        $r['protected']=($r['route_pattern']==='/*');
        $ded[$key]=$r;
    }
    $rows=array_values($ded);usort($rows,static fn($a,$b)=>[$a['module_group'],$a['label']]<=>[$b['module_group'],$b['label']]);
    return $rows;
}
function sk1320mc_groups(array $features): array {
    $g=[];foreach($features as $f){$k=(string)$f['module_group'];if(!isset($g[$k]))$g[$k]=['name'=>$k,'features'=>[],'on'=>0,'off'=>0,'custom'=>0];$g[$k]['features'][]=$f;if(!empty($f['tenant_enabled']))$g[$k]['on']++;else$g[$k]['off']++;if($f['override']!==null)$g[$k]['custom']++;}
    ksort($g,SORT_NATURAL|SORT_FLAG_CASE);return array_values($g);
}
function sk1320mc_set_override(string $featureKey,?bool $enabled): void {
    $tid=sk1320mc_tid(); if($tid<1) throw new RuntimeException('Tenant context is unavailable.');
    if(!sk1320mc_table('access_tenant_feature_overrides_v1300')) throw new RuntimeException('Tenant feature override table is unavailable.');
    $valid=false;foreach(sk1320mc_features() as $f)if($f['feature_key']===$featureKey){$valid=true;if(!empty($f['protected']))throw new RuntimeException('This global wildcard feature is protected from this screen.');break;}
    if(!$valid) throw new InvalidArgumentException('Unknown or inactive public feature.');
    if($enabled===null){$q=sk1320mc_db()->prepare('DELETE FROM access_tenant_feature_overrides_v1300 WHERE tenant_id=? AND feature_key=?');$q->execute([$tid,$featureKey]);return;}
    $cols=sk1320mc_cols('access_tenant_feature_overrides_v1300');
    $fields=['tenant_id','feature_key','enabled'];$vals=[$tid,$featureKey,$enabled?1:0];
    if(isset($cols['updated_at'])){$fields[]='updated_at';$vals[]=date('Y-m-d H:i:s');}
    if(isset($cols['created_at'])){$fields[]='created_at';$vals[]=date('Y-m-d H:i:s');}
    $quoted=array_map(static fn($x)=>'`'.$x.'`',$fields);
    $updates=['`enabled`=VALUES(`enabled`)'];if(isset($cols['updated_at']))$updates[]='`updated_at`=VALUES(`updated_at`)';
    $sql='INSERT INTO access_tenant_feature_overrides_v1300('.implode(',',$quoted).') VALUES('.implode(',',array_fill(0,count($fields),'?')).') ON DUPLICATE KEY UPDATE '.implode(',',$updates);
    sk1320mc_db()->prepare($sql)->execute($vals);
}
function sk1320mc_apply_group(string $group,bool $enabled): int {
    $features=array_values(array_filter(sk1320mc_features(),static fn($f)=>(string)$f['module_group']===$group&&!$f['protected']));
    if(!$features)throw new InvalidArgumentException('No controllable public features are registered in this group.');if(count($features)>200)throw new RuntimeException('Group is too large for one safe action.');
    $pdo=sk1320mc_db();$pdo->beginTransaction();$n=0;try{foreach($features as $f){sk1320mc_set_override((string)$f['feature_key'],$enabled);$n++;}$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    return $n;
}
function sk1320mc_snapshot(): array {
    $f=sk1320mc_features();$on=0;$off=0;$custom=0;foreach($f as $r){if($r['tenant_enabled'])$on++;else$off++;if($r['override']!==null)$custom++;}
    return ['version'=>'13.2.0','tenant_id'=>sk1320mc_tid(),'features'=>$f,'groups'=>sk1320mc_groups($f),'summary'=>['features'=>count($f),'on'=>$on,'off'=>$off,'custom'=>$custom]];
}
}
