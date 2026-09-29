<?php
declare(strict_types=1);
require_once __DIR__.'/city_discovery_v1350.php';
if(!function_exists('sk1350admin_allowed')){
function sk1350admin_db(): PDO{return db();}
function sk1350admin_h($v): string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function sk1350admin_allowed(): bool {try{$u=function_exists('current_user')?current_user():null;}catch(Throwable $e){$u=null;}if(!$u)return false;if(function_exists('sk1300_super_admin')&&sk1300_super_admin($u))return true;if(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($u))return true;if(function_exists('sk1300_can')&&sk1300_can('admin.city_discovery',$u))return true;return in_array(strtolower((string)($u['role']??'')),['admin','administrator','super_admin'],true);}
function sk1350admin_set(string $key,string $value): void {$allowed=['city_discovery_enabled','city_discovery_home_enabled','city_discovery_verified_first','city_discovery_item_limit','city_discovery_category_limit','city_discovery_title','city_discovery_subtitle'];if(!in_array($key,$allowed,true))throw new InvalidArgumentException('Unknown discovery setting.');$q=sk1350admin_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([$key,$value]);}
function sk1350admin_save(array $p): void {sk1350admin_set('city_discovery_enabled',!empty($p['city_discovery_enabled'])?'1':'0');sk1350admin_set('city_discovery_home_enabled',!empty($p['city_discovery_home_enabled'])?'1':'0');sk1350admin_set('city_discovery_verified_first',!empty($p['city_discovery_verified_first'])?'1':'0');sk1350admin_set('city_discovery_item_limit',(string)max(4,min(24,(int)($p['city_discovery_item_limit']??12))));sk1350admin_set('city_discovery_category_limit',(string)max(3,min(16,(int)($p['city_discovery_category_limit']??8))));sk1350admin_set('city_discovery_title',sk1350cd_cut(trim((string)($p['city_discovery_title']??'Explore Shahkot')),80));sk1350admin_set('city_discovery_subtitle',sk1350cd_cut(trim((string)($p['city_discovery_subtitle']??'')),180));}
function sk1350admin_snapshot(): array {return ['config'=>sk1350cd_config(),'payload'=>sk1350cd_payload()];}
}
