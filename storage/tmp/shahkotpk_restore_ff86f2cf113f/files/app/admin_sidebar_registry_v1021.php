<?php
declare(strict_types=1);
/** ShahkotPK v10.2.1 — central admin sidebar registry.
 * Future modules auto-appear by dropping a JSON manifest into app/sidebar-modules/.
 */
if(!function_exists('sk1021_sidebar_defaults')){
function sk1021_sidebar_defaults(): array {
    return [
      ['module_key'=>'homepage_builder','label'=>'Homepage Builder','url'=>'/admin/homepage-builder.php','icon'=>'⌂','category_key'=>'site_growth','category_label'=>'SITE & GROWTH','category_order'=>70,'sort_order'=>10,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'visitor_growth','label'=>'Visitor Growth','url'=>'/admin/growth-engagement.php','icon'=>'↗','category_key'=>'site_growth','category_label'=>'SITE & GROWTH','category_order'=>70,'sort_order'=>20,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'admin_tools','label'=>'Admin Tools','url'=>'/admin/admin-tools.php','icon'=>'◆','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>10,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'security_performance','label'=>'Security & Performance','url'=>'/admin/security-performance.php','icon'=>'◈','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>20,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'system_control','label'=>'Server & System Control','url'=>'/admin/system-control.php','icon'=>'▣','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>30,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'backup_restore','label'=>'Backup, Restore & Rollback','url'=>'/admin/backup-restore.php','icon'=>'↺','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>40,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'database_manager','label'=>'Database & Maintenance','url'=>'/admin/database-manager.php','icon'=>'▦','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>50,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'storage_manager','label'=>'Storage Control Center','url'=>'/admin/storage-manager.php','icon'=>'☁','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>60,'enabled'=>1,'permission'=>'settings.manage'],
      ['module_key'=>'sidebar_manager','label'=>'Sidebar Manager','url'=>'/admin/sidebar-manager.php','icon'=>'☷','category_key'=>'admin_tools','category_label'=>'ADMIN TOOLS','category_order'=>90,'sort_order'=>70,'enabled'=>1,'permission'=>'settings.manage'],
    ];
}}
function sk1021_sidebar_manifest_dir(): string { return __DIR__.'/sidebar-modules'; }
function sk1021_sidebar_valid_item(array $x): bool {
    return !empty($x['module_key']) && preg_match('/^[a-z0-9][a-z0-9_.-]{1,80}$/i',(string)$x['module_key']) &&
           !empty($x['label']) && !empty($x['url']) && str_starts_with((string)$x['url'],'/admin/');
}
function sk1021_sidebar_manifest_items(): array {
    $items=[];$dir=sk1021_sidebar_manifest_dir();
    if(is_dir($dir)){
      foreach(glob($dir.'/*.json')?:[] as $file){
        try{$raw=@file_get_contents($file);$j=is_string($raw)?json_decode($raw,true):null;
          if(isset($j['items'])&&is_array($j['items']))$rows=$j['items'];elseif(is_array($j))$rows=[$j];else$rows=[];
          foreach($rows as $row)if(is_array($row)&&sk1021_sidebar_valid_item($row))$items[(string)$row['module_key']]=$row;
        }catch(Throwable $e){}
      }
    }
    if(!$items)foreach(sk1021_sidebar_defaults() as $row)$items[(string)$row['module_key']]=$row;
    return array_values($items);
}
function sk1021_sidebar_table_exists(): bool {
    try{$q=db()->query("SHOW TABLES LIKE 'admin_sidebar_overrides_v1021'");return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}
}
function sk1021_sidebar_overrides(): array {
    if(!sk1021_sidebar_table_exists())return [];$out=[];
    try{foreach(db()->query('SELECT * FROM admin_sidebar_overrides_v1021')->fetchAll()?:[] as $r)$out[(string)$r['module_key']]=$r;}catch(Throwable $e){}
    return $out;
}
function sk1021_sidebar_permission_ok(string $permission=''): bool {
    if($permission==='')return true;
    try{$u=function_exists('current_user')?current_user():null;return $u && (!function_exists('has_permission') || has_permission($permission,$u) || has_permission('updates.manage',$u) || has_permission('tenants.manage',$u));}catch(Throwable $e){return false;}
}
function sk1021_sidebar_items(bool $includeDisabled=false): array {
    $items=sk1021_sidebar_manifest_items();$ov=sk1021_sidebar_overrides();$out=[];
    foreach($items as $row){
      $key=(string)$row['module_key'];$x=$row;$o=$ov[$key]??null;
      $x['label']=(string)($x['label']??$key);$x['url']=(string)($x['url']??'');$x['icon']=(string)($x['icon']??'•');
      $x['category_key']=(string)($x['category_key']??'extensions');$x['category_label']=(string)($x['category_label']??'EXTENSIONS');
      $x['category_order']=(int)($x['category_order']??80);$x['sort_order']=(int)($x['sort_order']??100);$x['enabled']=(int)($x['enabled']??1);
      if($o){
        if($o['enabled']!==null)$x['enabled']=(int)$o['enabled'];
        if($o['sort_order']!==null)$x['sort_order']=(int)$o['sort_order'];
        if(!empty($o['category_key']))$x['category_key']=(string)$o['category_key'];
        if(!empty($o['category_label']))$x['category_label']=(string)$o['category_label'];
        if($o['category_order']!==null)$x['category_order']=(int)$o['category_order'];
      }
      if(!$includeDisabled && !$x['enabled'])continue;
      if(!sk1021_sidebar_permission_ok((string)($x['permission']??'')))continue;
      $out[]=$x;
    }
    usort($out,static fn($a,$b)=>[$a['category_order'],$a['category_label'],$a['sort_order'],$a['label']] <=> [$b['category_order'],$b['category_label'],$b['sort_order'],$b['label']]);
    return $out;
}
function sk1021_sidebar_save_override(string $key,?int $enabled,?int $sortOrder,?string $categoryKey,?string $categoryLabel,?int $categoryOrder,?int $userId=null): void {
    if(!preg_match('/^[a-z0-9][a-z0-9_.-]{1,80}$/i',$key))throw new InvalidArgumentException('Invalid module key.');
    if(!sk1021_sidebar_table_exists())throw new RuntimeException('Sidebar override table is not installed.');
    $sql='INSERT INTO admin_sidebar_overrides_v1021(module_key,enabled,sort_order,category_key,category_label,category_order,updated_by,updated_at) VALUES(?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled),sort_order=VALUES(sort_order),category_key=VALUES(category_key),category_label=VALUES(category_label),category_order=VALUES(category_order),updated_by=VALUES(updated_by),updated_at=NOW()';
    db()->prepare($sql)->execute([$key,$enabled,$sortOrder,$categoryKey,$categoryLabel,$categoryOrder,$userId]);
}
function sk1021_sidebar_reset_override(string $key): void { if(sk1021_sidebar_table_exists())db()->prepare('DELETE FROM admin_sidebar_overrides_v1021 WHERE module_key=?')->execute([$key]); }
