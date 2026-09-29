<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/business_geo_map_bridge_v1284.php';
header('Content-Type: application/json; charset=utf-8');
try{$items=bs1284_map_feed((int)($_GET['limit']??250));echo json_encode(['ok'=>true,'count'=>count($items),'items'=>$items],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}catch(Throwable $e){http_response_code(500);echo json_encode(['ok'=>false,'count'=>0,'items'=>[],'message'=>'Map feed unavailable.']);}
