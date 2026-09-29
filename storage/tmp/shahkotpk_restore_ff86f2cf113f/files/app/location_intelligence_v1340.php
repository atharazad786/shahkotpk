<?php
declare(strict_types=1);
/** ShahkotPK v13.4.0 — admin Location Intelligence helpers. */
require_once __DIR__.'/map_platform_v1340.php';require_once __DIR__.'/map_data_v1340.php';
if(!function_exists('sk1340li_snapshot')){
function sk1340li_db(): PDO{return db();}
function sk1340li_h($v): string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function sk1340li_allowed(): bool {try{$u=function_exists('current_user')?current_user():null;}catch(Throwable $e){$u=null;}if(!$u)return false;if(function_exists('sk1300_super_admin')&&sk1300_super_admin($u))return true;if(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($u))return true;if(function_exists('sk1300_can')&&sk1300_can('admin.location_intelligence',$u))return true;return in_array(strtolower((string)($u['role']??'')),['admin','administrator','super_admin'],true);}
function sk1340li_set(string $key,string $value): void {$allowed=['map_location_discovery','map_marker_clustering','map_search_radius_km','map_max_search_radius_km','map_nearby_list_limit','map_discovery_max_results'];if(!in_array($key,$allowed,true))throw new InvalidArgumentException('Unknown location setting.');$q=sk1340li_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([$key,$value]);}
function sk1340li_save(array $p): void {$bool=static fn($k)=>!empty($p[$k])?'1':'0';sk1340li_set('map_location_discovery',$bool('map_location_discovery'));sk1340li_set('map_marker_clustering',$bool('map_marker_clustering'));$radius=max(1,min(100,(float)($p['map_search_radius_km']??5)));$max=max($radius,min(250,(float)($p['map_max_search_radius_km']??25)));$list=max(5,min(50,(int)($p['map_nearby_list_limit']??20)));$results=max(25,min(500,(int)($p['map_discovery_max_results']??300)));sk1340li_set('map_search_radius_km',(string)$radius);sk1340li_set('map_max_search_radius_km',(string)$max);sk1340li_set('map_nearby_list_limit',(string)$list);sk1340li_set('map_discovery_max_results',(string)$results);}
function sk1340li_snapshot(): array {$cfg=sk1340_map_public_config();$d=sk1340_location_data(['limit'=>$cfg['maxDiscoveryResults']??300]);$types=[];foreach((array)($d['records']??[]) as $r){$t=(string)($r['type']??'place');$types[$t]=($types[$t]??0)+1;}ksort($types);return ['version'=>'13.4.0','config'=>$cfg,'data'=>$d,'types'=>$types];}
}
