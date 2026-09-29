<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/access_control_v1300.php';
header('Content-Type: application/json; charset=utf-8');
try{$u=current_user();$tid=sk1300_tid();$aud=sk1300_audience($u,$tid);$pkg=in_array($aud,['customer','shopkeeper'],true)?sk1300_active_package($tid,sk1300_uid($u),$aud):[];$role=$aud==='staff'?sk1300_staff_role($u,$tid):'';$allowed=[];foreach(array_merge(sk1300_features(false),sk1300_features(true)) as $f)if(sk1300_can((string)$f['feature_key'],$u,$tid))$allowed[]=(string)$f['feature_key'];echo json_encode(['ok'=>true,'tenant_id'=>$tid,'audience'=>$aud,'package'=>$pkg?['id'=>(int)$pkg['id'],'code'=>$pkg['package_code'],'name'=>$pkg['name'],'limits'=>json_decode((string)($pkg['limits_json']??'{}'),true)]:null,'staff_role'=>$role?:null,'features'=>$allowed],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}catch(Throwable $e){http_response_code(500);echo json_encode(['ok'=>false,'error'=>'Access entitlement lookup failed.']);}
