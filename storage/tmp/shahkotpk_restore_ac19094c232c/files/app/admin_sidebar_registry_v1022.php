<?php
declare(strict_types=1);
/**
 * ShahkotPK v10.2.2 — Navigation Registry Pro.
 *
 * Placement contract for future modules:
 * - omitted / "sidebar": automatically appears in the managed sidebar.
 * - "admin_tools": appears inside /admin/admin-tools.php, not as a separate sidebar row.
 * - "hidden": registered but not rendered until an administrator changes it.
 */
$sk1301Core=__DIR__.'/core_sync_v1301.php';
if(is_file($sk1301Core)) require_once $sk1301Core;
$sk1300Access=__DIR__.'/access_control_v1300.php';
if(is_file($sk1300Access)) require_once $sk1300Access;
if(function_exists('sk1300_guard_admin_request')) sk1300_guard_admin_request();
if(!function_exists('sk1022_nav_manifest_dir')){
function sk1022_nav_manifest_dir(): string { return __DIR__.'/sidebar-modules'; }
function sk1022_nav_key_ok(string $key): bool { return (bool)preg_match('/^[a-z0-9][a-z0-9_.-]{1,110}$/i',$key); }
function sk1022_nav_admin_url_ok(string $url): bool { return $url!=='' && str_starts_with($url,'/admin/') && !str_contains($url,"\n") && !str_contains($url,"\r"); }
function sk1022_nav_defaults(): array {
 return [
  ['module_key'=>'homepage_builder','label'=>'Homepage Builder','url'=>'/admin/homepage-builder.php','icon'=>'⌂','placement'=>'sidebar','category_key'=>'site_growth','category_label'=>'SITE & GROWTH','category_order'=>70,'sort_order'=>10,'enabled'=>1,'permission'=>'settings.manage'],
  ['module_key'=>'visitor_growth','label'=>'Visitor Growth','url'=>'/admin/growth-engagement.php','icon'=>'↗','placement'=>'sidebar','category_key'=>'site_growth','category_label'=>'SITE & GROWTH','category_order'=>70,'sort_order'=>20,'enabled'=>1,'permission'=>'settings.manage'],
  ['module_key'=>'admin_tools','label'=>'Admin Tools','url'=>'/admin/admin-tools.php','icon'=>'◆','placement'=>'sidebar','category_key'=>'admin','category_label'=>'ADMIN','category_order'=>90,'sort_order'=>10,'enabled'=>1,'permission'=>'settings.manage'],
 ];
}
function sk1022_nav_manifest_items(): array {
 $out=[];$dir=sk1022_nav_manifest_dir();
 if(is_dir($dir))foreach(glob($dir.'/*.json')?:[] as $file){
  try{$raw=@file_get_contents($file);$j=is_string($raw)?json_decode($raw,true):null;$rows=(is_array($j)&&isset($j['items'])&&is_array($j['items']))?$j['items']:(is_array($j)?[$j]:[]);
   foreach($rows as $r){if(!is_array($r))continue;$key=(string)($r['module_key']??'');$url=(string)($r['url']??'');if(!sk1022_nav_key_ok($key)||!sk1022_nav_admin_url_ok($url)||trim((string)($r['label']??''))==='')continue;$r['_source']='manifest';$out[$key]=$r;}
  }catch(Throwable $e){}
 }
 if(!$out)foreach(sk1022_nav_defaults() as $r){$r['_source']='default';$out[(string)$r['module_key']]=$r;}
 return array_values($out);
}
function sk1022_nav_table(string $name): bool { try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$name]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;} }
function sk1022_nav_permission_ok(string $permission=''): bool {
 if($permission==='')return true;try{$u=function_exists('current_user')?current_user():null;return $u && (!function_exists('has_permission')||has_permission($permission,$u)||has_permission('updates.manage',$u)||has_permission('tenants.manage',$u));}catch(Throwable $e){return false;}
}
function sk1022_nav_overrides(): array {
 if(!sk1022_nav_table('admin_navigation_overrides_v1022'))return [];$o=[];try{foreach(db()->query('SELECT * FROM admin_navigation_overrides_v1022')->fetchAll()?:[] as $r)$o[(string)$r['module_key']]=$r;}catch(Throwable $e){}return $o;
}
function sk1022_nav_groups(): array {
 $o=[];if(sk1022_nav_table('admin_navigation_groups_v1022'))try{foreach(db()->query('SELECT * FROM admin_navigation_groups_v1022')->fetchAll()?:[] as $r)$o[(string)$r['category_key']]=$r;}catch(Throwable $e){}return $o;
}
function sk1022_nav_custom_items(): array {
 if(!sk1022_nav_table('admin_navigation_custom_v1022'))return [];$rows=[];try{foreach(db()->query('SELECT * FROM admin_navigation_custom_v1022 ORDER BY sort_order,label')->fetchAll()?:[] as $r){$r['_source']='custom';$rows[]=$r;}}catch(Throwable $e){}return $rows;
}
function sk1022_nav_normalize(array $x): array {
 $x['module_key']=(string)($x['module_key']??'');$x['label']=trim((string)($x['label']??$x['module_key']));$x['url']=(string)($x['url']??'');if(function_exists('sk1301_canonical_url'))$x['url']=sk1301_canonical_url($x['url']);$x['icon']=trim((string)($x['icon']??'•'))?:'•';
 $x['placement']=strtolower((string)($x['placement']??'sidebar'));if(!in_array($x['placement'],['sidebar','admin_tools','hidden'],true))$x['placement']='sidebar';
 $x['category_key']=trim((string)($x['category_key']??'extensions'))?:'extensions';$x['category_label']=trim((string)($x['category_label']??'EXTENSIONS'))?:'EXTENSIONS';$x['category_order']=(int)($x['category_order']??80);
 $x['subgroup_key']=trim((string)($x['subgroup_key']??''));$x['subgroup_label']=trim((string)($x['subgroup_label']??''));$x['subgroup_order']=(int)($x['subgroup_order']??0);$x['parent_key']=trim((string)($x['parent_key']??''));$x['sort_order']=(int)($x['sort_order']??100);$x['enabled']=(int)($x['enabled']??1);$x['tool_group']=trim((string)($x['tool_group']??'Tools'))?:'Tools';$x['description']=trim((string)($x['description']??''));
 if(function_exists('sk1302_apply_navigation_contract'))$x=sk1302_apply_navigation_contract($x);
 return $x;
}
function sk1022_nav_all_items(bool $includeDisabled=true): array {
 $base=[];foreach(array_merge(sk1022_nav_manifest_items(),sk1022_nav_custom_items()) as $r){$r=sk1022_nav_normalize($r);if(sk1022_nav_key_ok($r['module_key'])&&sk1022_nav_admin_url_ok($r['url']))$base[$r['module_key']]=$r;}
 $ov=sk1022_nav_overrides();$groups=sk1022_nav_groups();$out=[];
 foreach($base as $key=>$x){$o=$ov[$key]??null;if($o){foreach(['enabled','placement','category_key','category_label','category_order','subgroup_key','subgroup_label','subgroup_order','parent_key','sort_order'] as $f)if(array_key_exists($f,$o)&&$o[$f]!==null&&$o[$f]!=='')$x[$f]=in_array($f,['enabled','category_order','subgroup_order','sort_order'],true)?(int)$o[$f]:(string)$o[$f];if(!empty($o['label_override']))$x['label']=(string)$o['label_override'];if(!empty($o['icon_override']))$x['icon']=(string)$o['icon_override'];}
  $x=sk1022_nav_normalize($x);$g=$groups[$x['category_key']]??null;if($g){if((int)($g['enabled']??1)===0)$x['_group_disabled']=1;if(!empty($g['category_label']))$x['category_label']=(string)$g['category_label'];if($g['sort_order']!==null)$x['category_order']=(int)$g['sort_order'];$x['_group_collapsible']=(int)($g['collapsible']??0);$x['_group_default_open']=(int)($g['default_open']??1);}else{$x['_group_collapsible']=0;$x['_group_default_open']=1;}
  if(!$includeDisabled&&(!$x['enabled']||!empty($x['_group_disabled'])))continue;if(function_exists('sk1300_admin_nav_allowed')){if(!sk1300_admin_nav_allowed($x))continue;}elseif(!sk1022_nav_permission_ok((string)($x['permission']??'')))continue;$out[]=$x;
 }
 usort($out,static fn($a,$b)=>[$a['category_order'],$a['category_label'],$a['subgroup_order'],$a['subgroup_label'],$a['sort_order'],$a['label']]<=>[$b['category_order'],$b['category_label'],$b['subgroup_order'],$b['subgroup_label'],$b['sort_order'],$b['label']]);return $out;
}
function sk1022_nav_native_rows(bool $onlyManaged=false): array {
 if(!sk1022_nav_table('admin_navigation_native_v1022'))return [];$where=$onlyManaged?' WHERE managed=1 AND enabled=1':'';try{return db()->query('SELECT * FROM admin_navigation_native_v1022'.$where.' ORDER BY category_order,subgroup_order,sort_order,detected_label')->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function sk1022_nav_native_rules(): array { return sk1022_nav_native_rows(false); }
function sk1022_nav_sidebar_items(bool $includeDisabled=false): array {
 $out=[];foreach(sk1022_nav_all_items($includeDisabled) as $x)if($x['placement']==='sidebar'&&($includeDisabled||$x['enabled']))$out[]=$x;
 foreach(sk1022_nav_native_rows(true) as $r){$x=sk1022_nav_normalize(['module_key'=>(string)$r['native_key'],'label'=>(string)($r['label_override']?:$r['detected_label']),'url'=>(string)$r['url'],'icon'=>(string)($r['icon_override']?:'•'),'placement'=>'sidebar','category_key'=>$r['category_key']?:'core_custom','category_label'=>$r['category_label']?:'CUSTOM MENU','category_order'=>$r['category_order']??75,'subgroup_key'=>$r['subgroup_key']??'','subgroup_label'=>$r['subgroup_label']??'','subgroup_order'=>$r['subgroup_order']??0,'parent_key'=>$r['parent_key']??'','sort_order'=>$r['sort_order']??100,'enabled'=>$r['enabled']??1,'_source'=>'native']);$g=sk1022_nav_groups()[$x['category_key']]??null;if($g){if((int)($g['enabled']??1)===0)continue;if(!empty($g['category_label']))$x['category_label']=(string)$g['category_label'];if($g['sort_order']!==null)$x['category_order']=(int)$g['sort_order'];$x['_group_collapsible']=(int)($g['collapsible']??0);$x['_group_default_open']=(int)($g['default_open']??1);}if(function_exists('sk1300_admin_nav_allowed')&&!sk1300_admin_nav_allowed($x))continue;$out[]=$x;}
 usort($out,static fn($a,$b)=>[$a['category_order'],$a['category_label'],$a['subgroup_order'],$a['subgroup_label'],$a['sort_order'],$a['label']]<=>[$b['category_order'],$b['category_label'],$b['subgroup_order'],$b['subgroup_label'],$b['sort_order'],$b['label']]);$ded=[];$seen=[];foreach($out as $x){$p=(string)(parse_url((string)$x['url'],PHP_URL_PATH)?:$x['url']);if(isset($seen[$p]))continue;$seen[$p]=1;$ded[]=$x;}return $ded;
}
function sk1022_nav_admin_tool_items(bool $includeDisabled=false): array { $o=[];foreach(sk1022_nav_all_items($includeDisabled) as $x)if($x['placement']==='admin_tools'&&($includeDisabled||$x['enabled']))$o[]=$x;usort($o,static fn($a,$b)=>[$a['tool_group'],$a['sort_order'],$a['label']]<=>[$b['tool_group'],$b['sort_order'],$b['label']]);return $o; }
function sk1022_nav_save_override(string $key,array $d,?int $uid=null): void {
 if(!sk1022_nav_key_ok($key)||!sk1022_nav_table('admin_navigation_overrides_v1022'))throw new RuntimeException('Navigation override table is unavailable.');$placement=(string)($d['placement']??'sidebar');if(!in_array($placement,['sidebar','admin_tools','hidden'],true))$placement='sidebar';
 $sql='INSERT INTO admin_navigation_overrides_v1022(module_key,enabled,placement,label_override,icon_override,category_key,category_label,category_order,subgroup_key,subgroup_label,subgroup_order,parent_key,sort_order,updated_by,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled),placement=VALUES(placement),label_override=VALUES(label_override),icon_override=VALUES(icon_override),category_key=VALUES(category_key),category_label=VALUES(category_label),category_order=VALUES(category_order),subgroup_key=VALUES(subgroup_key),subgroup_label=VALUES(subgroup_label),subgroup_order=VALUES(subgroup_order),parent_key=VALUES(parent_key),sort_order=VALUES(sort_order),updated_by=VALUES(updated_by),updated_at=NOW()';
 db()->prepare($sql)->execute([$key,!empty($d['enabled'])?1:0,$placement,trim((string)($d['label_override']??''))?:null,trim((string)($d['icon_override']??''))?:null,trim((string)($d['category_key']??''))?:null,trim((string)($d['category_label']??''))?:null,(int)($d['category_order']??80),trim((string)($d['subgroup_key']??''))?:null,trim((string)($d['subgroup_label']??''))?:null,(int)($d['subgroup_order']??0),trim((string)($d['parent_key']??''))?:null,(int)($d['sort_order']??100),$uid]);
}
function sk1022_nav_reset_override(string $key): void { if(sk1022_nav_table('admin_navigation_overrides_v1022'))db()->prepare('DELETE FROM admin_navigation_overrides_v1022 WHERE module_key=?')->execute([$key]); }
function sk1022_nav_save_group(array $d,?int $uid=null): void {
 $key=trim((string)($d['category_key']??''));if(!sk1022_nav_key_ok($key))throw new InvalidArgumentException('Invalid group key.');$sql='INSERT INTO admin_navigation_groups_v1022(category_key,category_label,sort_order,enabled,collapsible,default_open,updated_by,updated_at) VALUES(?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE category_label=VALUES(category_label),sort_order=VALUES(sort_order),enabled=VALUES(enabled),collapsible=VALUES(collapsible),default_open=VALUES(default_open),updated_by=VALUES(updated_by),updated_at=NOW()';db()->prepare($sql)->execute([$key,trim((string)($d['category_label']??''))?:strtoupper($key),(int)($d['sort_order']??80),!empty($d['enabled'])?1:0,!empty($d['collapsible'])?1:0,!empty($d['default_open'])?1:0,$uid]);
}
function sk1022_nav_save_native(array $d,?int $uid=null): void {
 $key=(string)($d['native_key']??'');if(!sk1022_nav_key_ok($key))throw new InvalidArgumentException('Invalid native item.');$sql='UPDATE admin_navigation_native_v1022 SET enabled=?,managed=?,label_override=?,icon_override=?,category_key=?,category_label=?,category_order=?,subgroup_key=?,subgroup_label=?,subgroup_order=?,parent_key=?,sort_order=?,updated_by=?,updated_at=NOW() WHERE native_key=?';db()->prepare($sql)->execute([!empty($d['enabled'])?1:0,!empty($d['managed'])?1:0,trim((string)($d['label_override']??''))?:null,trim((string)($d['icon_override']??''))?:null,trim((string)($d['category_key']??''))?:null,trim((string)($d['category_label']??''))?:null,(int)($d['category_order']??75),trim((string)($d['subgroup_key']??''))?:null,trim((string)($d['subgroup_label']??''))?:null,(int)($d['subgroup_order']??0),trim((string)($d['parent_key']??''))?:null,(int)($d['sort_order']??100),$uid,$key]);
}
function sk1022_nav_sync_native(array $rows,?int $uid=null): int {
 if(!sk1022_nav_table('admin_navigation_native_v1022'))return 0;$n=0;$sql='INSERT INTO admin_navigation_native_v1022(native_key,url,detected_label,enabled,managed,category_order,sort_order,last_seen_at,updated_by,updated_at) VALUES(?,?,?,1,0,75,100,NOW(),?,NOW()) ON DUPLICATE KEY UPDATE detected_label=VALUES(detected_label),last_seen_at=NOW()';$st=db()->prepare($sql);
 foreach($rows as $r){if(!is_array($r))continue;$url=(string)($r['url']??'');$label=trim((string)($r['label']??''));if(!sk1022_nav_admin_url_ok($url)||$label===''||strlen($url)>500)continue;$key='native_'.substr(sha1($url),0,24);$st->execute([$key,$url,mb_substr($label,0,160),$uid]);$n++;}return $n;
}
function sk1022_nav_create_custom(array $d,?int $uid=null): string {
 $label=trim((string)($d['label']??''));$url=trim((string)($d['url']??''));if($label===''||!sk1022_nav_admin_url_ok($url))throw new InvalidArgumentException('Custom link label and /admin/ URL are required.');$key='custom_'.substr(sha1($url.'|'.$label.'|'.microtime(true)),0,24);$placement=(string)($d['placement']??'sidebar');if(!in_array($placement,['sidebar','admin_tools','hidden'],true))$placement='sidebar';$sql='INSERT INTO admin_navigation_custom_v1022(module_key,label,url,icon,enabled,placement,category_key,category_label,category_order,subgroup_key,subgroup_label,subgroup_order,parent_key,sort_order,permission,description,created_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())';db()->prepare($sql)->execute([$key,$label,$url,trim((string)($d['icon']??'•'))?:'•',1,$placement,trim((string)($d['category_key']??'extensions'))?:'extensions',trim((string)($d['category_label']??'EXTENSIONS'))?:'EXTENSIONS',(int)($d['category_order']??80),trim((string)($d['subgroup_key']??''))?:null,trim((string)($d['subgroup_label']??''))?:null,(int)($d['subgroup_order']??0),trim((string)($d['parent_key']??''))?:null,(int)($d['sort_order']??100),'settings.manage',trim((string)($d['description']??''))?:null,$uid]);return $key;
}
function sk1022_nav_delete_custom(string $key): void { if(!str_starts_with($key,'custom_'))throw new InvalidArgumentException('Only custom links can be deleted.');db()->prepare('DELETE FROM admin_navigation_custom_v1022 WHERE module_key=?')->execute([$key]);if(sk1022_nav_table('admin_navigation_overrides_v1022'))db()->prepare('DELETE FROM admin_navigation_overrides_v1022 WHERE module_key=?')->execute([$key]); }
}
