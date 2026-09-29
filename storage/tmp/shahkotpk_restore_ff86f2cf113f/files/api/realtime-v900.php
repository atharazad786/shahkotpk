<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/enterprise_v900.php';
$s=v900_mobile_auth();if(!$s){http_response_code(401);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>'Valid mobile session required.']);exit;}
if(!v900_rate_limit('realtime:'.(int)$s['id'],240,60)){http_response_code(429);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>'Realtime rate limit exceeded.']);exit;}
$after=max(0,(int)($_GET['after']??0));$channel=v900_clip((string)($_GET['channel']??'public'),120);$mode=(string)($_GET['mode']??'poll');
if($mode==='sse'||str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT']??'')),'text/event-stream')){
 header('Content-Type: text/event-stream; charset=utf-8');header('Cache-Control: no-cache, no-store');header('X-Accel-Buffering: no');$deadline=microtime(true)+25;$cursor=$after;do{$events=v900_realtime_poll($cursor,$channel,(int)$s['user_id'],100);foreach($events as $ev){$cursor=(int)$ev['id'];echo 'id: '.$cursor."\n";echo 'event: '.preg_replace('/[^A-Za-z0-9_.-]/','_',((string)$ev['event_key']))."\n";echo 'data: '.json_encode(['id'=>$cursor,'channel'=>$ev['channel_key'],'event'=>$ev['event_key'],'payload'=>$ev['payload'],'created_at'=>$ev['created_at']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n\n";}if(function_exists('ob_flush'))@ob_flush();flush();if(connection_aborted())break;if(!$events)usleep(500000);}while(microtime(true)<$deadline);echo "event: heartbeat\ndata: {\"cursor\":{$cursor}}\n\n";exit;
}
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');$wait=max(0,min(20,(int)($_GET['wait']??12)));$deadline=microtime(true)+$wait;$events=[];do{$events=v900_realtime_poll($after,$channel,(int)$s['user_id'],100);if($events||$wait===0)break;usleep(500000);}while(microtime(true)<$deadline);$next=$events?(int)end($events)['id']:$after;echo json_encode(['ok'=>true,'events'=>$events,'next_cursor'=>$next,'server_time'=>date('c')],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
