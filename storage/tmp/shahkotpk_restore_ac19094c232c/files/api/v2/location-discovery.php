<?php
declare(strict_types=1);
require __DIR__.'/../../app/bootstrap.php';
require_once __DIR__.'/../../app/location_discovery_v54.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

try{
    if(!v54_location_enabled()){
        http_response_code(404);echo json_encode(['ok'=>false,'error'=>'Location discovery is disabled.']);exit;
    }
    $center=function_exists('google_maps_default_center')?google_maps_default_center():['lat'=>31.5709,'lng'=>73.4853];
    $lat=isset($_GET['lat'])?(float)$_GET['lat']:(float)$center['lat'];
    $lng=isset($_GET['lng'])?(float)$_GET['lng']:(float)$center['lng'];
    if($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180)throw new InvalidArgumentException('Invalid map coordinates.');
    $radius=isset($_GET['radius'])?(float)$_GET['radius']:v54_location_default_radius();
    $radius=max(.25,min(v54_location_max_radius(),$radius));
    $query=trim((string)($_GET['q']??''));
    $typesRaw=trim((string)($_GET['types']??($_GET['type']??'')));
    $types=$typesRaw!==''?array_filter(array_map('trim',explode(',',$typesRaw))):[];
    $types=v54_location_types($types);
    $rows=v54_location_results($lat,$lng,$radius,$types,$query);
    v54_location_log($lat,$lng,$radius,$types,$query,count($rows));
    echo json_encode(['ok'=>true,'center'=>['lat'=>$lat,'lng'=>$lng],'radius_km'=>$radius,'types'=>$types,'count'=>count($rows),'results'=>$rows],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){
    http_response_code(422);echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}
