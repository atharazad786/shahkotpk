<?php
declare(strict_types=1);
/** ShahkotPK v13.9.0 — bounded public smart-search endpoint. */
$root=dirname(__DIR__);foreach([$root.'/config/config.php',$root.'/bootstrap.php',$root.'/app/bootstrap.php'] as $b){if(is_file($b)){require_once $b;break;}}
if(is_file($root.'/app/access_control_v1300.php'))require_once $root.'/app/access_control_v1300.php';
require_once $root.'/app/homepage_smart_v1390.php';
header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: public, max-age=30, stale-while-revalidate=60');
try{$q=(string)($_GET['q']??'');echo json_encode(sk1390_search($q),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}catch(Throwable $e){http_response_code(200);echo json_encode(['ok'=>false,'version'=>'13.9.0','items'=>[]]);}
