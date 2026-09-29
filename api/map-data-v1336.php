<?php
declare(strict_types=1);
/** ShahkotPK v13.3.6 public map-safe coordinate endpoint. */
$root=dirname(__DIR__);foreach([$root.'/config/config.php',$root.'/bootstrap.php',$root.'/app/bootstrap.php'] as $b){if(is_file($b)){require_once $b;break;}}
if(is_file($root.'/app/access_control_v1300.php'))require_once $root.'/app/access_control_v1300.php';require_once $root.'/app/map_data_v1336.php';
header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: public, max-age=120, stale-while-revalidate=300');
try{$data=sk1336_map_data();http_response_code(200);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}catch(Throwable $e){http_response_code(200);echo json_encode(['ok'=>false,'version'=>'13.3.6','records'=>[],'counts'=>[]]);}
