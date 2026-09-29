<?php
declare(strict_types=1);

/** ShahkotPK v5.4.0 Advanced Map & Location Discovery. */
function v54_location_enabled(): bool { return setting_bool('location_discovery_enabled', true); }
function v54_lower(string $v): string { return function_exists('mb_strtolower')?mb_strtolower($v):strtolower($v); }
function v54_substr(string $v,int $start,int $len): string { return function_exists('mb_substr')?mb_substr($v,$start,$len):substr($v,$start,$len); }
function v54_location_default_radius(): float { return max(0.5, min(100.0, (float)setting('location_default_radius_km', '5'))); }
function v54_location_max_radius(): float { return max(v54_location_default_radius(), min(250.0, (float)setting('location_max_radius_km', '50'))); }
function v54_location_limit(): int { return max(5, min(100, setting_int('location_results_limit', 30))); }

function v54_location_table_exists(string $table): bool {
    static $cache=[];
    if (array_key_exists($table,$cache)) return $cache[$table];
    try {
        $q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$table]);
        return $cache[$table]=(bool)$q->fetchColumn();
    } catch (Throwable $e) { return $cache[$table]=false; }
}

function v54_haversine_km(float $lat1,float $lng1,float $lat2,float $lng2): float {
    $earth=6371.0088;
    $dLat=deg2rad($lat2-$lat1);$dLng=deg2rad($lng2-$lng1);
    $a=sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLng/2)**2;
    return $earth*2*atan2(sqrt($a),sqrt(max(0.0,1-$a)));
}

function v54_location_types(?array $types=null): array {
    $allowed=function_exists('google_maps_allowed_types')?google_maps_allowed_types():['business','guide','deal','event','job','property'];
    if ($types===null || !$types) return $allowed;
    $types=array_values(array_unique(array_map(static fn($v)=>preg_replace('/[^a-z_]/','',strtolower((string)$v)), $types)));
    return array_values(array_intersect($allowed,$types));
}

function v54_location_results(float $lat,float $lng,float $radiusKm,array $types=[],string $query=''): array {
    if (!v54_location_enabled() || !function_exists('google_maps_markers')) return [];
    $radiusKm=max(0.25,min(v54_location_max_radius(),$radiusKm));
    $types=v54_location_types($types);
    $query=v54_lower(trim(v54_substr($query,0,80)));
    $markers=google_maps_markers($types,true);
    $rows=[];
    foreach($markers as $m){
        $mlat=(float)($m['lat']??0);$mlng=(float)($m['lng']??0);
        if(!$mlat&&!$mlng)continue;
        if($query!==''){
            $hay=v54_lower(implode(' ',[(string)($m['title']??''),(string)($m['subtitle']??''),(string)($m['address']??''),(string)($m['city']??'')]));
            if(!str_contains($hay,$query))continue;
        }
        $distance=v54_haversine_km($lat,$lng,$mlat,$mlng);
        if($distance>$radiusKm)continue;
        $m['distance_km']=round($distance,2);
        $rows[]=$m;
    }
    usort($rows,static function($a,$b){
        $d=((float)$a['distance_km'])<=>((float)$b['distance_km']);
        if($d!==0)return $d;
        return ((int)!empty($b['featured']))<=>((int)!empty($a['featured']));
    });
    return array_slice($rows,0,v54_location_limit());
}

function v54_location_log(float $lat,float $lng,float $radius,array $types,string $query,int $count): void {
    if(!setting_bool('location_analytics_enabled',true)||!v54_location_table_exists('location_search_events'))return;
    try{
        $tenant=function_exists('current_tenant')?current_tenant():null;$tid=(int)($tenant['id']??0);
        // Privacy-preserving analytics: do not persist a user id and round coordinates to ~1 km cells.
        $q=db()->prepare('INSERT INTO location_search_events(tenant_id,user_id,query_text,latitude,longitude,radius_km,types_csv,result_count,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())');
        $q->execute([$tid?:null,null,v54_substr($query,0,80),round($lat,2),round($lng,2),$radius,implode(',',$types),$count]);
    }catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Location analytics insert failed',$e);}
}

function v54_location_config(): array {
    $center=function_exists('google_maps_default_center')?google_maps_default_center():['lat'=>31.5709,'lng'=>73.4853,'zoom'=>14];
    return [
        'enabled'=>v54_location_enabled(),
        'api'=>'/api/v2/location-discovery.php',
        'defaultLat'=>(float)$center['lat'],'defaultLng'=>(float)$center['lng'],'defaultZoom'=>(int)$center['zoom'],
        'defaultRadius'=>v54_location_default_radius(),'maxRadius'=>v54_location_max_radius(),'limit'=>v54_location_limit(),
        'liveResults'=>setting_bool('location_live_results',true),'autoNearby'=>setting_bool('location_auto_nearby',false),
        'showDistance'=>setting_bool('location_show_distance',true),'directions'=>setting_bool('location_directions_enabled',true),
        'fitMarkers'=>setting_bool('google_maps_fit_markers',true),'mapType'=>(string)setting('google_maps_map_type','roadmap'),
        'mapReady'=>function_exists('google_maps_ready')&&google_maps_ready(),
        'catalog'=>function_exists('google_maps_type_catalog')?google_maps_type_catalog():[],
    ];
}

function v54_location_public_module(string $title='Discover What Is Near You',string $subtitle='Use live location, radius search and smart filters to find businesses, deals, events, jobs and property around you.'): void {
    if(!v54_location_enabled())return;
    $cfg=v54_location_config();$types=v54_location_types();$catalog=$cfg['catalog'];
    echo '<section class="v54-location-section lt-section"><div class="lt-shell"><div class="v54-location-head"><div><span>ADVANCED LOCATION DISCOVERY · v5.4</span><h2>'.e($title).'</h2><p>'.e($subtitle).'</p></div><a href="/city-discovery.php">Open Full Discovery →</a></div>';
    echo '<div class="v54-location-shell" data-v54-location data-v54-full="0"><div class="v54-location-tools"><div class="v54-location-search"><span>⌕</span><input type="search" data-v54-query placeholder="Search nearby businesses, deals, jobs, property..."><button type="button" data-v54-search>Search</button></div><button type="button" class="v54-near" data-v54-near>◎ Near Me</button><label class="v54-radius">Radius <input type="range" min="1" max="'.e((string)(int)$cfg['maxRadius']).'" step="1" value="'.e((string)(int)$cfg['defaultRadius']).'" data-v54-radius><b data-v54-radius-label>'.e((string)(int)$cfg['defaultRadius']).' km</b></label></div>';
    echo '<div class="v54-type-filters"><button type="button" class="active" data-v54-type="">All</button>';
    foreach($types as $t){$m=$catalog[$t]??['label'=>ucfirst($t),'short'=>strtoupper(substr($t,0,1))];echo '<button type="button" data-v54-type="'.e($t).'"><i>'.e((string)($m['short']??'•')).'</i>'.e((string)($m['label']??ucfirst($t))).'</button>';}
    echo '</div><div class="v54-location-main"><div class="v54-map-wrap"><div class="v54-map" data-v54-map></div><div class="v54-map-note" data-v54-map-note>'.($cfg['mapReady']?'Loading map…':'Map API key is not configured. Nearby list search still works.').'</div></div><aside class="v54-results"><div class="v54-results-head"><div><b>Nearby results</b><span data-v54-status>Ready to discover around Shahkot.</span></div><button type="button" data-v54-saved>♡ Saved</button></div><div data-v54-results class="v54-results-list"><div class="v54-empty">Choose <b>Near Me</b> or search the default city area.</div></div></aside></div></div></div></section>';
}
