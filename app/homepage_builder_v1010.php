<?php
declare(strict_types=1);
/** ShahkotPK v10.1.0 Smart Homepage Builder + reversible activation. */

function shp1010_table_exists(string $table): bool {
    static $c=[]; if(isset($c[$table])) return $c[$table];
    if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$table)) return false;
    try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $c[$table]=(bool)$q->fetchColumn();}catch(Throwable $e){return $c[$table]=false;}
}
function shp1010_columns(string $table): array {
    static $c=[]; if(isset($c[$table])) return $c[$table]; if(!shp1010_table_exists($table)) return $c[$table]=[];
    try{$q=db()->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');$q->execute([$table]);return $c[$table]=array_map('strval',$q->fetchAll(PDO::FETCH_COLUMN));}catch(Throwable $e){return $c[$table]=[];}
}
function shp1010_tenant_id(): int {
    try{if(function_exists('tenant_id')) return max(0,(int)tenant_id()); if(function_exists('current_tenant')){$t=current_tenant();return (int)($t['id']??0);}}catch(Throwable $e){} return 0;
}
function shp1010_root(): string { return dirname(__DIR__); }
function shp1010_backup_dir(): string { return shp1010_root().'/storage/homepage_backups'; }
function shp1010_meta_file(): string { return shp1010_backup_dir().'/v1010-state.json'; }
function shp1010_defaults(): array {
    return [
      ['hero','Hero & Search','Discover Shahkot. Everything you need, one city.',1,10,'feature',1],
      ['quick_actions','Explore Services','Popular city services in one tap.',1,20,'grid',10],
      ['daily_utility','What do you need today?','Daily utilities, nearby help, price comparison and smart service requests.',1,22,'feature',6],
      ['local_assistant','Ask ShahkotPK','Tell us what you need. Search local options and book from one place.',1,24,'feature',6],
      ['deal_wallet','Deals & Rewards','Save local offers, claim coupons and earn loyalty points.',1,26,'slider',6],
      ['trending','Trending Now','What people are exploring across Shahkot this week.',1,25,'slider',8],
      ['businesses','Featured Businesses','Trusted local businesses and services.',1,30,'slider',8],
      ['near_you','Popular Near You','Useful local businesses in your city, with optional location-based discovery.',1,35,'slider',8],
      ['deals','Deals & Promotions','Latest promotions from Banner Manager.',1,40,'slider',6],
      ['marketplace','Marketplace','Fresh products from local sellers.',1,50,'grid',8],
      ['property','Property Showcase','Homes, apartments and property opportunities.',1,60,'grid',6],
      ['reviews','Community Reviews','Recent approved customer feedback and local recommendations.',1,65,'grid',6],
      ['health','Health & Doctors','Find healthcare services and doctors.',1,70,'slider',6],
      ['news','Latest News','What is happening around the city.',1,80,'grid',6],
      ['jobs_events','Jobs & Events','Opportunities and upcoming activities.',1,90,'grid',6],
      ['recently_viewed','Continue Exploring','Quickly return to things you viewed recently.',1,92,'slider',8],
      ['local_feed','What’s New in Shahkot','Fresh businesses, products, news and events in one local feed.',0,94,'grid',8],
      ['rewards','Rewards & Referrals','Check in, invite friends and build your ShahkotPK rewards balance.',1,96,'feature',1],
      ['seo_discovery','Explore Shahkot','Popular local discovery pages for visitors and search engines.',1,98,'feature',8],
    ];
}
function shp1010_ensure_tenant(int $tenantId): void {
    if(!shp1010_table_exists('homepage_settings_v1010')||!shp1010_table_exists('homepage_sections_v1010')) return;
    try{
      $q=db()->prepare('SELECT tenant_id FROM homepage_settings_v1010 WHERE tenant_id=? LIMIT 1');$q->execute([$tenantId]);
      if(!$q->fetchColumn()){
        $i=db()->prepare('INSERT INTO homepage_settings_v1010(tenant_id,theme_key,hero_heading,hero_subtitle,hero_cta_text,hero_cta_url,search_enabled,quick_actions_enabled,is_active,config_json) VALUES(?,?,?,?,?,?,?,?,?,?)');
        $i->execute([$tenantId,'premium-light','Discover Shahkot. Everything you need, one city.','Businesses, shopping, property, healthcare, jobs and local services — beautifully organized in one place.','Explore Shahkot','/businesses.php',1,1,0,'{}']);
      }
      $ins=db()->prepare('INSERT IGNORE INTO homepage_sections_v1010(tenant_id,section_key,title,subtitle,enabled,sort_order,display_mode,record_limit,featured_only,config_json) VALUES(?,?,?,?,?,?,?,?,0,?)');
      foreach(shp1010_defaults() as $d)$ins->execute([$tenantId,$d[0],$d[1],$d[2],$d[3],$d[4],$d[5],$d[6],'{}']);
    }catch(Throwable $e){}
}
function shp1010_settings(int $tenantId): array {
    shp1010_ensure_tenant($tenantId); $r=[];
    try{$q=db()->prepare('SELECT * FROM homepage_settings_v1010 WHERE tenant_id=? LIMIT 1');$q->execute([$tenantId]);$r=$q->fetch()?:[];}catch(Throwable $e){}
    if(!$r)$r=['tenant_id'=>$tenantId,'hero_heading'=>'Discover Shahkot. Everything you need, one city.','hero_subtitle'=>'Businesses, shopping, property, healthcare, jobs and local services — beautifully organized in one place.','hero_cta_text'=>'Explore Shahkot','hero_cta_url'=>'/businesses.php','hero_ad_id'=>0,'search_enabled'=>1,'quick_actions_enabled'=>1,'is_active'=>0,'theme_key'=>'premium-light'];
    return $r;
}
function shp1010_sections(int $tenantId): array {
    shp1010_ensure_tenant($tenantId);$rows=[];
    try{$q=db()->prepare('SELECT * FROM homepage_sections_v1010 WHERE tenant_id=? ORDER BY sort_order,id');$q->execute([$tenantId]);$rows=$q->fetchAll()?:[];}catch(Throwable $e){}
    if(!$rows){foreach(shp1010_defaults() as $i=>$d)$rows[]=['id'=>$i+1,'section_key'=>$d[0],'title'=>$d[1],'subtitle'=>$d[2],'enabled'=>$d[3],'sort_order'=>$d[4],'display_mode'=>$d[5],'record_limit'=>$d[6],'featured_only'=>0];}
    return $rows;
}
function shp1010_ads(int $tenantId): array {
    if(!shp1010_table_exists('advertisements')) return [];$cols=shp1010_columns('advertisements');$where=[];$p=[];
    if(in_array('tenant_id',$cols,true)&&$tenantId>0){$where[]='tenant_id=?';$p[]=$tenantId;}
    if(in_array('status',$cols,true))$where[]="status IN ('active','published','1')";
    try{$sql='SELECT * FROM advertisements'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY id DESC LIMIT 100';$q=db()->prepare($sql);$q->execute($p);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function shp1010_save_settings(int $tenantId,array $in): void {
    shp1010_ensure_tenant($tenantId);
    $heroAd=max(0,(int)($in['hero_ad_id']??0));$search=!empty($in['search_enabled'])?1:0;$quick=!empty($in['quick_actions_enabled'])?1:0;
    $q=db()->prepare('UPDATE homepage_settings_v1010 SET theme_key=?,hero_ad_id=?,hero_heading=?,hero_subtitle=?,hero_cta_text=?,hero_cta_url=?,search_enabled=?,quick_actions_enabled=?,updated_at=NOW() WHERE tenant_id=?');
    $q->execute([substr((string)($in['theme_key']??'premium-light'),0,64),$heroAd,mb_substr(trim((string)($in['hero_heading']??'')),0,220),mb_substr(trim((string)($in['hero_subtitle']??'')),0,500),mb_substr(trim((string)($in['hero_cta_text']??'')),0,80),mb_substr(trim((string)($in['hero_cta_url']??'/businesses.php')),0,500),$search,$quick,$tenantId]);
    if(!empty($in['section'])&&is_array($in['section'])){
      $u=db()->prepare('UPDATE homepage_sections_v1010 SET title=?,subtitle=?,enabled=?,sort_order=?,display_mode=?,record_limit=?,featured_only=?,updated_at=NOW() WHERE tenant_id=? AND section_key=?');
      foreach($in['section'] as $key=>$v){if(!is_array($v)||!preg_match('/^[a-z0-9_]{2,64}$/',(string)$key))continue;$mode=in_array(($v['display_mode']??'grid'),['grid','slider','feature'],true)?$v['display_mode']:'grid';$u->execute([mb_substr(trim((string)($v['title']??'')),0,190),mb_substr(trim((string)($v['subtitle']??'')),0,500),!empty($v['enabled'])?1:0,max(1,min(999,(int)($v['sort_order']??50))),$mode,max(1,min(24,(int)($v['record_limit']??6))),!empty($v['featured_only'])?1:0,$tenantId,(string)$key]);}
    }
}
function shp1010_status(): array {
    $index=shp1010_root().'/index.php';$active=false;$marker='SHAHKOTPK_HOMEPAGE_V1010_ACTIVE';
    if(is_file($index)){$s=@file_get_contents($index);$active=is_string($s)&&strpos($s,$marker)!==false;}
    $meta=[];if(is_file(shp1010_meta_file())){$x=json_decode((string)@file_get_contents(shp1010_meta_file()),true);if(is_array($x))$meta=$x;}
    return ['active'=>$active,'index'=>$index,'meta'=>$meta,'writable'=>is_file($index)?is_writable($index):is_writable(shp1010_root())];
}
function shp1010_activate(int $actorId=0): array {
    $root=shp1010_root();$index=$root.'/index.php';if(!is_file($index))return ['ok'=>false,'message'=>'Root index.php not found.'];if(!is_writable($index))return ['ok'=>false,'message'=>'Root index.php is not writable by PHP.'];
    $current=(string)@file_get_contents($index);if($current==='')return ['ok'=>false,'message'=>'Could not read current index.php.'];if(strpos($current,'SHAHKOTPK_HOMEPAGE_V1010_ACTIVE')!==false)return ['ok'=>true,'message'=>'Smart Homepage is already active.'];
    $dir=shp1010_backup_dir();if(!is_dir($dir)&&!@mkdir($dir,0775,true))return ['ok'=>false,'message'=>'Could not create homepage backup directory.'];
    $backup=$dir.'/index.php.pre-v1010.'.date('Ymd-His').'.bak';if(@file_put_contents($backup,$current,LOCK_EX)===false)return ['ok'=>false,'message'=>'Could not create previous homepage backup. No changes made.'];
    $wrapper="<?php\n/* SHAHKOTPK_HOMEPAGE_V1010_ACTIVE */\nrequire __DIR__.'/app/bootstrap.php';\nrequire_once __DIR__.'/app/homepage_front_v1010.php';\nshp1010_render_homepage();\n";
    $tmp=$index.'.v1010.tmp';if(@file_put_contents($tmp,$wrapper,LOCK_EX)===false){@unlink($tmp);return ['ok'=>false,'message'=>'Could not write homepage wrapper.'];}@chmod($tmp,fileperms($index)&0777);if(!@rename($tmp,$index)){@unlink($tmp);return ['ok'=>false,'message'=>'Could not activate new homepage. Previous homepage remains unchanged.'];}
    @file_put_contents(shp1010_meta_file(),json_encode(['backup'=>$backup,'activated_at'=>date('c'),'actor_id'=>$actorId],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);
    try{db()->prepare('UPDATE homepage_settings_v1010 SET is_active=1,activated_at=NOW() WHERE tenant_id=?')->execute([shp1010_tenant_id()]);}catch(Throwable $e){}
    if(function_exists('tenant_audit'))try{tenant_audit('homepage.v1010.activated','homepage',0,'Activated v10.1.0 Smart Homepage',['backup'=>basename($backup)]);}catch(Throwable $e){}
    return ['ok'=>true,'message'=>'Smart Homepage activated. Previous index.php was backed up first.','backup'=>$backup];
}
function shp1010_revert(int $actorId=0): array {
    $st=shp1010_status();if(!$st['active'])return ['ok'=>true,'message'=>'Smart Homepage is not active. Nothing to restore.'];$meta=$st['meta'];$backup=(string)($meta['backup']??'');
    $realDir=realpath(shp1010_backup_dir());$realBackup=$backup!==''?realpath($backup):false;if(!$realDir||!$realBackup||!str_starts_with($realBackup,$realDir.DIRECTORY_SEPARATOR)||!is_file($realBackup))return ['ok'=>false,'message'=>'Previous homepage backup could not be verified. Restore was stopped.'];
    $old=(string)@file_get_contents($realBackup);if($old==='')return ['ok'=>false,'message'=>'Previous homepage backup is unreadable.'];$index=shp1010_root().'/index.php';$current=(string)@file_get_contents($index);$safety=shp1010_backup_dir().'/index.php.v1010-before-revert.'.date('Ymd-His').'.bak';@file_put_contents($safety,$current,LOCK_EX);
    $tmp=$index.'.v1010.restore.tmp';if(@file_put_contents($tmp,$old,LOCK_EX)===false){@unlink($tmp);return ['ok'=>false,'message'=>'Could not stage previous homepage.'];}@chmod($tmp,fileperms($index)&0777);if(!@rename($tmp,$index)){@unlink($tmp);return ['ok'=>false,'message'=>'Could not restore previous homepage.'];}
    $meta['reverted_at']=date('c');$meta['reverted_by']=$actorId;$meta['v1010_safety_backup']=$safety;@file_put_contents(shp1010_meta_file(),json_encode($meta,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);
    try{db()->prepare('UPDATE homepage_settings_v1010 SET is_active=0 WHERE tenant_id=?')->execute([shp1010_tenant_id()]);}catch(Throwable $e){}
    if(function_exists('tenant_audit'))try{tenant_audit('homepage.v1010.reverted','homepage',0,'Restored previous homepage',['backup'=>basename($realBackup)]);}catch(Throwable $e){}
    return ['ok'=>true,'message'=>'Previous homepage restored successfully.','restored_from'=>$realBackup];
}
