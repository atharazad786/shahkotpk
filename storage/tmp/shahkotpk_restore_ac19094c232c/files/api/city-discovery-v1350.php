<?php
declare(strict_types=1);
/** ShahkotPK v13.5.0 — public map-safe city discovery feed. */
$root=dirname(__DIR__);foreach([$root.'/config/config.php',$root.'/bootstrap.php',$root.'/app/bootstrap.php'] as $b){if(is_file($b)){require_once $b;break;}}
require_once $root.'/app/city_discovery_v1350.php';header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: public, max-age=120, stale-while-revalidate=300');
try{$q=sk1350cd_cut(trim((string)($_GET['q']??'')),100);$type=sk1350cd_cut(trim((string)($_GET['type']??'')),50);echo json_encode(sk1350cd_payload(['q'=>$q,'type'=>$type]),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}catch(Throwable $e){http_response_code(200);echo json_encode(['ok'=>false,'version'=>'13.5.0','categories'=>[],'items'=>[]]);}
