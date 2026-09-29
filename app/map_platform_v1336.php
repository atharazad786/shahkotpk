<?php
declare(strict_types=1);
/** ShahkotPK v13.3.6 — single-host no-key map platform with corrected Shahkot center. */
if(!function_exists('sk1336_map_public_config')){
function sk1336_map_db(): ?PDO { try{return function_exists('db')?db():null;}catch(Throwable $e){return null;} }
function sk1336_map_setting(string $key,string $default=''): string {
    static $cache=[]; if(array_key_exists($key,$cache)) return $cache[$key];
    $pdo=sk1336_map_db(); if(!$pdo) return $cache[$key]=$default;
    try{$q=$pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $cache[$key]=$v===false?$default:(string)$v;}catch(Throwable $e){return $cache[$key]=$default;}
}
function sk1336_map_bool(string $key,bool $default=true): bool { $v=strtolower(trim(sk1336_map_setting($key,$default?'1':'0')));return in_array($v,['1','true','yes','on','enabled'],true); }
function sk1336_map_num(string $key,float $default,float $min,float $max): float { $v=sk1336_map_setting($key,(string)$default);$n=is_numeric($v)?(float)$v:$default;return max($min,min($max,$n)); }
function sk1336_map_public_config(): array {
    $provider=strtolower(trim(sk1336_map_setting('map_provider',sk1336_map_setting('maps_provider','openfreemap'))));
    if(in_array($provider,['maplibre','open-free-map','open_free_map','osm-vector','auto'],true))$provider='openfreemap';
    if(!in_array($provider,['openfreemap','leaflet','google'],true))$provider='openfreemap';
    $style=strtolower(trim(sk1336_map_setting('map_style','standard')));
    if(!in_array($style,['light','dark','standard','bright','fiord','satellite'],true))$style='standard';
    $styles=['standard'=>'liberty','light'=>'positron','dark'=>'dark','bright'=>'bright','fiord'=>'fiord','satellite'=>'liberty'];
    return [
      'version'=>'13.3.6','provider'=>$provider,'noApiKey'=>in_array($provider,['openfreemap','leaflet'],true),
      'defaultLat'=>sk1336_map_num('map_default_latitude',31.5709,-90,90),'defaultLng'=>sk1336_map_num('map_default_longitude',73.48531,-180,180),
      'defaultZoom'=>(int)round(sk1336_map_num('map_default_zoom',14,1,20)),'style'=>$style,
      'openFreeMapStyle'=>'https://tiles.openfreemap.org/styles/'.$styles[$style],
      'maplibreJs'=>'https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js','maplibreCss'=>'https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css',
      'maplibreJsFallback'=>'https://cdn.jsdelivr.net/npm/maplibre-gl@5/dist/maplibre-gl.js','maplibreCssFallback'=>'https://cdn.jsdelivr.net/npm/maplibre-gl@5/dist/maplibre-gl.css',
      'markerClustering'=>sk1336_map_bool('map_marker_clustering',true),'currentLocation'=>sk1336_map_bool('map_current_location',true),
      'directions'=>sk1336_map_bool('map_directions',true),'searchRadiusKm'=>sk1336_map_num('map_search_radius_km',5,1,100),
      'attribution'=>'OpenFreeMap · OpenStreetMap','globalCompatibility'=>true,'replaceGoogle'=>in_array($provider,['openfreemap','leaflet'],true),
      'mapDataEndpoint'=>'/api/map-data-v1336.php','fullWidgetSizing'=>true,'legacyMarkerHarvest'=>true,'singleMapControlHost'=>true,
      'verifiedMapDataset'=>'13.3.6'
    ];
}
}
