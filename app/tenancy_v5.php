<?php
declare(strict_types=1);

function tenant_v5_table_exists(string $table): bool {
    static $cache=[];
    if(isset($cache[$table])) return $cache[$table];
    try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();}catch(Throwable $e){return $cache[$table]=false;}
}
function tenant_normalize_host(string $host): string {
    $host=strtolower(trim($host));
    if(str_contains($host,':'))$host=explode(':',$host,2)[0];
    $host=rtrim($host,'.');
    return $host;
}
function tenant_request_host(): string {return tenant_normalize_host((string)($_SERVER['HTTP_HOST']??$_SERVER['SERVER_NAME']??''));}
function tenant_row(int $id): ?array {if($id<1||!tenant_v5_table_exists('tenants'))return null;try{$q=db()->prepare('SELECT t.*,c.name city_name,c.slug city_slug FROM tenants t JOIN cities c ON c.id=t.city_id WHERE t.id=? LIMIT 1');$q->execute([$id]);return $q->fetch()?:null;}catch(Throwable $e){return null;}}
function tenant_master(): ?array {if(!tenant_v5_table_exists('tenants'))return null;try{$r=db()->query("SELECT t.*,c.name city_name,c.slug city_slug FROM tenants t JOIN cities c ON c.id=t.city_id WHERE t.is_master=1 AND t.status='active' ORDER BY t.id LIMIT 1")->fetch();if($r)return $r;$r=db()->query("SELECT t.*,c.name city_name,c.slug city_slug FROM tenants t JOIN cities c ON c.id=t.city_id WHERE t.status='active' ORDER BY t.id LIMIT 1")->fetch();return $r?:null;}catch(Throwable $e){return null;}}
function tenant_by_host(string $host): ?array {
    $host=tenant_normalize_host($host);if($host===''||!tenant_v5_table_exists('tenants'))return null;
    $candidates=[$host];if(str_starts_with($host,'www.'))$candidates[]=substr($host,4);else $candidates[]='www.'.$host;
    try{$ph=implode(',',array_fill(0,count($candidates),'?'));$q=db()->prepare("SELECT t.*,c.name city_name,c.slug city_slug FROM tenant_domains d JOIN tenants t ON t.id=d.tenant_id JOIN cities c ON c.id=t.city_id WHERE d.domain IN ($ph) AND d.status='verified' AND t.status='active' ORDER BY d.is_primary DESC,d.id LIMIT 1");$q->execute($candidates);$r=$q->fetch();if($r)return $r;
        $q=db()->prepare("SELECT t.*,c.name city_name,c.slug city_slug FROM tenants t JOIN cities c ON c.id=t.city_id WHERE LOWER(t.primary_domain) IN ($ph) AND t.status='active' ORDER BY t.is_master DESC,t.id LIMIT 1");$q->execute($candidates);return $q->fetch()?:null;
    }catch(Throwable $e){return null;}
}
function current_tenant(bool $refresh=false): ?array {
    static $cache=null;if($refresh)$cache=null;if($cache!==null)return $cache?:null;
    if(!setting_bool('multi_tenant_enabled',true)||!tenant_v5_table_exists('tenants')){ $cache=false; return null; }
    $sessionId=(int)($_SESSION['tenant_context_id']??0);if($sessionId>0){$r=tenant_row($sessionId);if($r&&$r['status']==='active')return $cache=$r;}
    $r=tenant_by_host(tenant_request_host());if($r)return $cache=$r;
    if((string)setting('tenant_unknown_host_mode','master')==='master')$tm=tenant_master();return $cache=is_array($tm)?$tm:null;
    $cache=false; return null;
}
function tenant_id(): int {$t=current_tenant();return (int)($t['id']??0);}
function tenant_city_id(): int {$t=current_tenant();return (int)($t['city_id']??0);}
function tenant_is_master(): bool {$t=current_tenant();return !empty($t['is_master']);}
function tenant_brand(string $key,$default='') {
    $t=current_tenant();if(!$t)return $default;
    $map=['site_name'=>'site_name','tagline'=>'tagline','logo_url'=>'logo_url','favicon_url'=>'favicon_url','primary_color'=>'primary_color','secondary_color'=>'secondary_color','accent_color'=>'accent_color','timezone'=>'timezone','currency'=>'currency','locale'=>'locale','support_email'=>'support_email','support_phone'=>'support_phone','city_name'=>'city_name','city_slug'=>'city_slug'];
    $col=$map[$key]??null;if($col&&isset($t[$col])&&trim((string)$t[$col])!=='')return $t[$col];
    $settings=json_decode((string)($t['settings_json']??''),true);return is_array($settings)&&array_key_exists($key,$settings)?$settings[$key]:$default;
}
function tenant_base_url(?array $tenant=null): string {$tenant=$tenant?:current_tenant();if(!$tenant)return '/';$d=trim((string)($tenant['primary_domain']??''));if($d===''){$d=tenant_request_host();}return $d!==''?'https://'.tenant_normalize_host($d):'/';}
function tenant_feature_key_for_setting(string $key): ?string {
    $k=strtolower($key);
    if(str_contains($k,'news'))return 'news';if(str_contains($k,'blog'))return 'blog';if(str_contains($k,'live'))return 'live';
    if(str_contains($k,'shop')||str_contains($k,'store')||str_contains($k,'marketplace'))return 'marketplace';
    if(str_contains($k,'property'))return 'property';if(str_contains($k,'job'))return 'jobs';if(str_contains($k,'classified'))return 'classifieds';
    if(str_contains($k,'advert')||str_contains($k,'ads'))return 'ads';if(str_contains($k,'map'))return 'maps';if(str_contains($k,'service')||str_contains($k,'restaurant')||str_contains($k,'emergency'))return 'services';
    if(str_starts_with($k,'ai_'))return 'ai';if(str_contains($k,'mobile_api')||str_contains($k,'mobile_app'))return 'mobile_api';if(str_contains($k,'seller_pos')||str_starts_with($k,'pos_'))return 'seller_pos';
    if(str_contains($k,'directory')||str_contains($k,'business'))return 'directory';if(str_contains($k,'city_')||str_contains($k,'homepage_guide')||str_contains($k,'deal')||str_contains($k,'event'))return 'city_content';
    return null;
}
function tenant_feature_enabled(string $settingKey,bool $globalEnabled=true): bool {
    if(!$globalEnabled)return false;$tid=tenant_id();if($tid<1||!tenant_v5_table_exists('tenant_features'))return $globalEnabled;$feature=tenant_feature_key_for_setting($settingKey);if($feature===null)return $globalEnabled;
    static $cache=[];$ck=$tid.':'.$feature;if(isset($cache[$ck]))return $cache[$ck];try{$q=db()->prepare('SELECT enabled FROM tenant_features WHERE tenant_id=? AND feature_key=? LIMIT 1');$q->execute([$tid,$feature]);$v=$q->fetchColumn();return $cache[$ck]=$v===false?$globalEnabled:(bool)$v;}catch(Throwable $e){return $globalEnabled;}
}
function tenant_query_city_id(): int {
    $city=tenant_city_id();if($city<1)return 0;
    $isAdmin=str_contains((string)($_SERVER['PHP_SELF']??''),'/admin/');
    if($isAdmin&&function_exists('is_super_admin')&&is_super_admin())return 0;
    return $city;
}
function tenant_apply_city_filter(array &$where,array &$params,string $column='city_id',bool $allowNull=false): void {$city=tenant_query_city_id();if($city<1)return;$where[]=$allowNull?"({$column}=? OR {$column} IS NULL)":"{$column}=?";$params[]=$city;}
function tenant_member_record(int $userId,?int $tenantId=null): ?array {
    if($userId<1||!tenant_v5_table_exists('tenant_members'))return null;$tenantId=$tenantId?:tenant_id();if($tenantId<1)return null;
    try{$q=db()->prepare('SELECT tm.*,t.is_master,t.city_id,t.site_name FROM tenant_members tm JOIN tenants t ON t.id=tm.tenant_id WHERE tm.tenant_id=? AND tm.user_id=? AND tm.status=1 AND t.status=\'active\' LIMIT 1');$q->execute([$tenantId,$userId]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function tenant_user_has_membership(int $userId): bool {if($userId<1||!tenant_v5_table_exists('tenant_members'))return false;try{$q=db()->prepare('SELECT 1 FROM tenant_members tm JOIN tenants t ON t.id=tm.tenant_id WHERE tm.user_id=? AND tm.status=1 AND t.status=\'active\' LIMIT 1');$q->execute([$userId]);return (bool)$q->fetchColumn();}catch(Throwable $e){return false;}}
function tenant_safe_permissions(): array {
    if(!function_exists('permission_registry'))return [];$deny=['updates.manage','security.manage','backups.manage','roles.manage','users.assign_admin','users.assign_staff_role','settings.manage'];return array_values(array_diff(array_keys(permission_registry()),$deny));
}
function tenant_role_default_permissions(string $role): array {
    $all=tenant_safe_permissions();$map=[
      'owner'=>$all,
      'admin'=>$all,
      'manager'=>['dashboard.view','businesses.manage','categories.manage','city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage','news.manage','blog.dashboard','blog.posts.create','blog.posts.edit_all','blog.posts.publish','live.manage','engagement.manage','shop.manage','shop.orders.manage','shop.auctions.manage','reviews.manage','verification.manage','bookings.manage','leads.manage','analytics.manage','classifieds.manage','restaurants.manage','services.manage','ads.manage','reports.manage','mobile_api.manage','pos.manage','tenants.manage'],
      'editor'=>['dashboard.view','city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage','news.manage','blog.dashboard','blog.posts.create','blog.posts.edit_all','blog.posts.publish','blog.categories.manage','blog.tags.manage','blog.comments.manage','homepage.manage','banners.manage','ticker.manage'],
      'finance'=>['dashboard.view','subscriptions.manage','ads.manage','payments.manage','accounts.manage','payouts.manage','commerce_invoices.manage','reports.manage','renewals.manage','commissions.manage'],
      'support'=>['dashboard.view','support.manage','disputes.manage','inbox.manage','leads.manage','bookings.manage','reviews.manage'],
    ];return array_values(array_intersect($all,$map[$role]??[]));
}
function tenant_member_permissions_for_user(int $userId): ?array {
    $m=tenant_member_record($userId);if(!$m)return null;$allowed=tenant_safe_permissions();$custom=json_decode((string)($m['permissions_json']??''),true);if(is_array($custom)&&$custom){return array_values(array_unique(array_intersect($allowed,$custom)));}return tenant_role_default_permissions((string)$m['member_role']);
}
function tenant_restrict_super_admin(int $userId): bool {return tenant_member_record($userId)!==null&&!tenant_is_master();}
function tenant_boot_user_context(): void {
    if(empty($_SESSION['user_id'])||!tenant_v5_table_exists('tenant_members'))return;$uid=(int)$_SESSION['user_id'];$current=tenant_member_record($uid);if($current)return;
    try{$q=db()->prepare("SELECT tm.tenant_id FROM tenant_members tm JOIN tenants t ON t.id=tm.tenant_id WHERE tm.user_id=? AND tm.status=1 AND t.status='active' ORDER BY t.is_master DESC,tm.id LIMIT 2");$q->execute([$uid]);$ids=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));if(count($ids)===1){$_SESSION['tenant_context_id']=$ids[0];current_tenant(true);}}catch(Throwable $e){}
}
function tenant_switch_context(int $tenantId,int $userId): bool {
    $t=tenant_row($tenantId);if(!$t||$t['status']!=='active')return false;
    $globalAdmin=false;try{$q=db()->prepare("SELECT u.role,up.custom_role_id FROM users u LEFT JOIN user_profiles up ON up.user_id=u.id WHERE u.id=? LIMIT 1");$q->execute([$userId]);$u=$q->fetch();$globalAdmin=$u&&$u['role']==='admin'&&empty($u['custom_role_id'])&&!tenant_user_has_membership($userId);}catch(Throwable $e){}
    if(!$globalAdmin&&!tenant_member_record($userId,$tenantId))return false;$_SESSION['tenant_context_id']=$tenantId;current_tenant(true);return true;
}
function tenant_enforce_admin_request_scope(): void {
    $path=(string)($_SERVER['PHP_SELF']??'');if(!str_contains($path,'/admin/')||empty($_SESSION['user_id']))return;$uid=(int)$_SESSION['user_id'];$m=tenant_member_record($uid);if(!$m)return;$city=(int)$m['city_id'];if($city<1)return;
    if(isset($_GET['city_id']))$_GET['city_id']=$city;if(isset($_POST['city_id'])||($_SERVER['REQUEST_METHOD']??'')==='POST')$_POST['city_id']=$city;
}
function tenant_audit(string $action,string $entityType='',?int $entityId=null,string $description='',array $meta=[]): void {
    if(!tenant_v5_table_exists('tenant_audit_logs')||tenant_id()<1)return;try{$u=current_user();$ip=function_exists('security_client_ip')?security_client_ip():(string)($_SERVER['REMOTE_ADDR']??'');db()->prepare('INSERT INTO tenant_audit_logs(tenant_id,user_id,action_key,entity_type,entity_id,description,ip_address,meta_json) VALUES(?,?,?,?,?,?,?,?)')->execute([tenant_id(),$u?(int)$u['id']:null,substr($action,0,120),$entityType?:null,$entityId,$description?:null,substr($ip,0,64)?:null,$meta?json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null]);}catch(Throwable $e){}
}
function tenant_stats(int $tenantId): array {
    $t=tenant_row($tenantId);if(!$t)return [];$city=(int)$t['city_id'];$out=['businesses'=>0,'news'=>0,'content'=>0,'products'=>0,'members'=>0,'domains'=>0];
    foreach(['businesses'=>['businesses','city_id'],'news'=>['news_posts','city_id'],'content'=>['city_portal_items','city_id'],'products'=>['store_products','city_id']] as $k=>$v){try{$q=db()->prepare("SELECT COUNT(*) FROM {$v[0]} WHERE {$v[1]}=?");$q->execute([$city]);$out[$k]=(int)$q->fetchColumn();}catch(Throwable $e){}}
    try{$q=db()->prepare('SELECT COUNT(*) FROM tenant_members WHERE tenant_id=? AND status=1');$q->execute([$tenantId]);$out['members']=(int)$q->fetchColumn();$q=db()->prepare("SELECT COUNT(*) FROM tenant_domains WHERE tenant_id=? AND status='verified'");$q->execute([$tenantId]);$out['domains']=(int)$q->fetchColumn();}catch(Throwable $e){}
    return $out;
}
