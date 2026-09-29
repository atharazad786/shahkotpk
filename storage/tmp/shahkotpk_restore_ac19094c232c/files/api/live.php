<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
function live545_json(array $data,int $code=200): never {http_response_code($code);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
$action=(string)($_GET['action']??$_POST['action']??'');$id=(int)($_GET['broadcast_id']??$_POST['broadcast_id']??0);if($id<1)live545_json(['ok'=>false,'message'=>'Invalid broadcast.'],422);$b=live_v545_find($id);if(!$b||!live_v545_is_public($b))live545_json(['ok'=>false,'message'=>'Broadcast unavailable.'],404);
try{
 if($action==='heartbeat'){$seconds=(int)($_POST['seconds']??10);$r=!empty($b['analytics_enabled'])&&setting_bool('live_analytics_enabled',true)?live_v545_heartbeat($id,$seconds):['online'=>live_v545_refresh_peak($id)];live545_json(['ok'=>true]+$r);}
 if($action==='chat_list'){$rows=live_v545_chat_messages($id,80,false);live545_json(['ok'=>true,'messages'=>$rows,'online'=>live_v545_refresh_peak($id)]);}
 if($action==='chat_send'){csrf_check();$r=live_v545_send_chat($id,(string)($_POST['name']??''),(string)($_POST['message']??''));live545_json($r,$r['ok']?200:422);}
 live545_json(['ok'=>false,'message'=>'Unknown action.'],404);
}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Live API failure',$e);live545_json(['ok'=>false,'message'=>'Live service is temporarily unavailable.'],500);}
