<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/enterprise_v900.php';
header('Content-Type: application/json; charset=utf-8');
function v900_api_out(array $data,int $code=200): never {http_response_code($code);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
$action=(string)($_GET['action']??$_POST['action']??'config');
$body=[];$raw=file_get_contents('php://input');if($raw){$j=json_decode($raw,true);if(is_array($j))$body=$j;}
$in=array_merge($_POST,$body);
try{
  if($action==='config'){
    $platform=in_array((string)($in['platform']??$_GET['platform']??''),['android','ios'],true)?(string)($in['platform']??$_GET['platform']):'android';
    $branding=v900_mobile_branding();$version=v900_mobile_version($platform);
    v900_api_out(['ok'=>true,'tenant_id'=>v900_tid(),'branding'=>$branding,'version'=>$version,'features'=>['push'=>true,'realtime'=>true,'offline_sync'=>true,'deep_links'=>true]]);
  }
  if($action==='login'){
    $login=trim((string)($in['login']??''));$password=(string)($in['password']??'');if(!v900_rate_limit('mobile_login:'.hash('sha256',strtolower($login)),10,600))v900_api_out(['ok'=>false,'error'=>'Too many login attempts. Try again later.'],429);if($login===''||$password==='')v900_api_out(['ok'=>false,'error'=>'Login and password are required.'],422);
    $u=v900_user_login($login,$password);if(!$u)v900_api_out(['ok'=>false,'error'=>'Invalid credentials or inactive account.'],401);
    if(isset($u['tenant_id']))$GLOBALS['V900_TENANT_OVERRIDE']=(int)$u['tenant_id'];$deviceId=v900_mobile_device_upsert($in,(int)$u['id']);$token=v900_mobile_issue_token((int)$u['id'],$deviceId,['mobile','sync','realtime']);v900_audit('mobile','login','Mobile API session created.','medium','user',(int)$u['id']);
    v900_api_out(['ok'=>true,'token'=>$token,'expires_in_days'=>(int)v900_setting('mobile_session_days',30),'user'=>['id'=>(int)$u['id'],'name'=>$u['name']??$u['full_name']??$u['username']??'User','role'=>$u['role']??'user'],'device_id'=>$deviceId]);
  }
  if($action==='device_register'){
    $s=v900_mobile_auth();$uid=$s?(int)$s['user_id']:null;$id=v900_mobile_device_upsert($in,$uid);v900_api_out(['ok'=>true,'device_id'=>$id]);
  }
  if($action==='version_check'){
    $platform=in_array((string)($in['platform']??''),['android','ios'],true)?(string)$in['platform']:'android';$current=(int)($in['build_number']??0);$v=v900_mobile_version($platform);$available=$v&&$current<(int)$v['build_number'];$required=$v&&($current<(int)$v['minimum_build']||($available&&in_array((string)$v['update_mode'],['required','blocked'],true)));v900_api_out(['ok'=>true,'version'=>$v,'update_available'=>$available,'update_required'=>$required]);
  }
  if($action==='sync'){
    $s=v900_mobile_auth();if(!$s)v900_api_out(['ok'=>false,'error'=>'Valid mobile bearer token required.'],401);$snapshot=v900_public_snapshot(max(10,min(100,(int)($in['limit']??60))));$after=max(0,(int)($in['event_cursor']??0));$events=v900_realtime_poll($after,'public',(int)$s['user_id'],100);v900_api_out(['ok'=>true,'snapshot'=>$snapshot,'events'=>$events,'next_event_cursor'=>$events?(int)end($events)['id']:$after]);
  }
  if($action==='events'){
    $s=v900_mobile_auth();if(!$s)v900_api_out(['ok'=>false,'error'=>'Valid mobile bearer token required.'],401);$after=max(0,(int)($in['after']??0));$channel=v900_clip((string)($in['channel']??'public'),120);$events=v900_realtime_poll($after,$channel,(int)$s['user_id'],100);v900_api_out(['ok'=>true,'events'=>$events,'next_cursor'=>$events?(int)end($events)['id']:$after]);
  }
  if($action==='deep_link'){
    $code=v900_clip((string)($in['code']??''),120);$d=$code!==''?v900_deep_link($code):null;if(!$d)v900_api_out(['ok'=>false,'error'=>'Deep link not found.'],404);v900_api_out(['ok'=>true,'deep_link'=>$d]);
  }
  if($action==='logout'){
    $s=v900_mobile_auth();if($s)db()->prepare('UPDATE mobile_sessions_v900 SET revoked_at=NOW() WHERE id=?')->execute([(int)$s['id']]);v900_api_out(['ok'=>true]);
  }
  v900_api_out(['ok'=>false,'error'=>'Unknown action.'],404);
}catch(Throwable $e){v900_api_out(['ok'=>false,'error'=>'Request could not be completed.'],500);}
