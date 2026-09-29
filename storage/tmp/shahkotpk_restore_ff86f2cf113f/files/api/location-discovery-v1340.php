<?php
declare(strict_types=1);
/** ShahkotPK v13.4.0 public map-safe discovery endpoint. */
$root=dirname(__DIR__);foreach([$root.'/config/config.php',$root.'/bootstrap.php',$root.'/app/bootstrap.php'] as $b){if(is_file($b)){require_once $b;break;}}
if(is_file($root.'/app/access_control_v1300.php'))require_once $root.'/app/access_control_v1300.php';require_once $root.'/app/map_data_v1340.php';
header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: public, max-age=90, stale-while-revalidate=240');
try{$f=['q'=>(string)($_GET['q']??''),'type'=>(string)($_GET['type']??''),'lat'=>$_GET['lat']??null,'lng'=>$_GET['lng']??null,'radius_km'=>$_GET['radius_km']??null,'limit'=>$_GET['limit']??300];echo json_encode(sk1340_location_data($f),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}catch(Throwable $e){http_response_code(200);echo json_encode(['ok'=>false,'version'=>'13.4.0','records'=>[],'counts'=>[]]);}
