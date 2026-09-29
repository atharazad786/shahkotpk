<?php
declare(strict_types=1);
/** ShahkotPK v13.3.3 — no-key Map Platform settings service (backward-compatible function names). */
if (!function_exists('sk1311map_payload')) {
function sk1311map_db(): PDO { return function_exists('db') ? db() : throw new RuntimeException('Database unavailable'); }
function sk1311map_table(string $table): bool {
    static $cache=[];
    if (array_key_exists($table,$cache)) return $cache[$table];
    try {$q=sk1311map_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();} catch(Throwable $e){return $cache[$table]=false;}
}
function sk1311map_get_raw(string $key): ?string {
    if (!sk1311map_table('settings')) return null;
    try {$q=sk1311map_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?null:(string)$v;} catch(Throwable $e){return null;}
}
function sk1311map_get(array $keys,string $default=''): string {
    foreach($keys as $k){$v=sk1311map_get_raw((string)$k);if($v!==null&&trim($v)!=='')return $v;}
    return $default;
}
function sk1311map_bool(array $keys,bool $default): bool {
    $v=strtolower(trim(sk1311map_get($keys,$default?'1':'0')));
    return in_array($v,['1','true','yes','on','enabled'],true);
}
function sk1311map_num(array $keys,float $default,float $min,float $max): float {
    $v=sk1311map_get($keys,(string)$default);$n=is_numeric($v)?(float)$v:$default;return max($min,min($max,$n));
}
function sk1311map_payload(): array {
    $provider=strtolower(sk1311map_get(['map_provider','maps_provider','map_platform_provider'],'openfreemap'));
    if(in_array($provider,['maplibre','open-free-map','open_free_map','osm-vector','auto'],true))$provider='openfreemap';
    if(!in_array($provider,['openfreemap','leaflet','google'],true))$provider='openfreemap';
    $style=strtolower(sk1311map_get(['map_style','maps_style','map_theme'],'light'));
    if(!in_array($style,['light','dark','standard','bright','fiord','satellite'],true))$style='standard';
    return [
      'version'=>'13.3.3',
      'provider'=>$provider,
      'latitude'=>sk1311map_num(['map_default_latitude','default_map_latitude','map_latitude','map_default_lat'],31.7397,-90,90),
      'longitude'=>sk1311map_num(['map_default_longitude','default_map_longitude','map_longitude','map_default_lng'],73.8643,-180,180),
      'zoom'=>(int)round(sk1311map_num(['map_default_zoom','default_map_zoom','map_zoom'],13,1,20)),
      'style'=>$style,
      'marker_clustering'=>sk1311map_bool(['map_marker_clustering','maps_marker_clustering','map_clustering'],true),
      'heatmap'=>sk1311map_bool(['map_heatmap','maps_heatmap'],true),
      'category_icons'=>sk1311map_bool(['map_category_icons','maps_category_icons'],true),
      'current_location'=>sk1311map_bool(['map_current_location','maps_current_location'],true),
      'directions'=>sk1311map_bool(['map_directions','maps_directions'],true),
      'search_radius_km'=>sk1311map_num(['map_search_radius_km','map_default_radius_km','maps_search_radius_km'],5,1,100),
      'max_search_radius_km'=>sk1311map_num(['map_max_search_radius_km','maps_max_search_radius_km'],25,1,250),
      'public_map_url'=>is_file(dirname(__DIR__).'/city-map.php')?'/city-map.php':(is_file(dirname(__DIR__).'/map.php')?'/map.php':''),
      'api_key_required'=>($provider==='google'),
      'no_key_provider'=>in_array($provider,['openfreemap','leaflet'],true),
      'status'=>'ready'
    ];
}
function sk1311map_set(string $key,string $value): void {
    if(!sk1311map_table('settings')) throw new RuntimeException('Settings table is unavailable.');
    $q=sk1311map_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    $q->execute([$key,$value]);
}
function sk1311map_save(array $in): array {
    $provider=strtolower(trim((string)($in['provider']??'openfreemap')));if(in_array($provider,['maplibre','open-free-map','open_free_map','osm-vector','auto'],true))$provider='openfreemap';if(!in_array($provider,['openfreemap','leaflet','google'],true))throw new InvalidArgumentException('Invalid map provider.');
    $style=strtolower(trim((string)($in['style']??'standard')));if(!in_array($style,['light','dark','standard','bright','fiord','satellite'],true))throw new InvalidArgumentException('Invalid map style.');
    $lat=filter_var($in['latitude']??null,FILTER_VALIDATE_FLOAT);$lng=filter_var($in['longitude']??null,FILTER_VALIDATE_FLOAT);$zoom=filter_var($in['zoom']??null,FILTER_VALIDATE_INT);
    $radius=filter_var($in['search_radius_km']??null,FILTER_VALIDATE_FLOAT);$maxRadius=filter_var($in['max_search_radius_km']??null,FILTER_VALIDATE_FLOAT);
    if($lat===false||$lat<-90||$lat>90)throw new InvalidArgumentException('Latitude must be between -90 and 90.');
    if($lng===false||$lng<-180||$lng>180)throw new InvalidArgumentException('Longitude must be between -180 and 180.');
    if($zoom===false||$zoom<1||$zoom>20)throw new InvalidArgumentException('Zoom must be between 1 and 20.');
    if($radius===false||$radius<1||$radius>100)throw new InvalidArgumentException('Default radius must be between 1 and 100 KM.');
    if($maxRadius===false||$maxRadius<1||$maxRadius>250||$maxRadius<$radius)throw new InvalidArgumentException('Maximum radius must be between the default radius and 250 KM.');
    $bool=static fn($k)=>!empty($in[$k])?'1':'0';
    $pairs=[
      'map_provider'=>$provider,'map_default_latitude'=>(string)$lat,'map_default_longitude'=>(string)$lng,'map_default_zoom'=>(string)$zoom,'map_style'=>$style,
      'map_marker_clustering'=>$bool('marker_clustering'),'map_heatmap'=>$bool('heatmap'),'map_category_icons'=>$bool('category_icons'),'map_current_location'=>$bool('current_location'),'map_directions'=>$bool('directions'),
      'map_search_radius_km'=>(string)$radius,'map_max_search_radius_km'=>(string)$maxRadius,'map_platform_settings_version'=>'13.3.3','map_no_key_mode'=>in_array($provider,['openfreemap','leaflet'],true)?'1':'0','map_tile_provider'=>$provider==='openfreemap'?'openfreemap':($provider==='leaflet'?'openstreetmap':$provider),'public_data_cache_bust'=>date('Y-m-d H:i:s')
    ];
    // Small compatibility alias set for older map modules.
    $pairs['default_map_latitude']=$pairs['map_default_latitude'];$pairs['default_map_longitude']=$pairs['map_default_longitude'];$pairs['default_map_zoom']=$pairs['map_default_zoom'];
    $pairs['maps_provider']=$pairs['map_provider'];$pairs['maps_marker_clustering']=$pairs['map_marker_clustering'];$pairs['maps_heatmap']=$pairs['map_heatmap'];
    $pdo=sk1311map_db();$pdo->beginTransaction();try{foreach($pairs as $k=>$v)sk1311map_set($k,$v);$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    return sk1311map_payload();
}
}
