<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
if(is_file(__DIR__.'/../app/core_sync_v1301.php'))require_once __DIR__.'/../app/core_sync_v1301.php';
if(is_file(__DIR__.'/../app/access_control_v1300.php'))require_once __DIR__.'/../app/access_control_v1300.php';
require_once __DIR__.'/../app/map_platform_settings_v1311.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store, max-age=0');
try{
  $me=function_exists('current_user')?current_user():null;$ok=false;
  if($me){if(function_exists('sk1300_super_admin')&&sk1300_super_admin($me))$ok=true;elseif(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($me))$ok=true;elseif(function_exists('sk1300_can')&&sk1300_can('admin.map_control',$me))$ok=true;elseif(function_exists('has_permission')&&has_permission('settings.manage',$me))$ok=true;}
  if(!$ok){http_response_code(403);echo json_encode(['ok'=>false,'error'=>'Forbidden']);exit;}
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    $raw=file_get_contents('php://input');$data=json_decode((string)$raw,true);if(!is_array($data))$data=$_POST;
    $headerToken=(string)($_SERVER['HTTP_X_CSRF_TOKEN']??$_SERVER['HTTP_X_CSRF']??'');
    if(!isset($_POST['_csrf']))$_POST['_csrf']=(string)($data['_csrf']??$headerToken);
    if(function_exists('csrf_check'))csrf_check();
    $payload=sk1311map_save($data);echo json_encode(['ok'=>true,'settings'=>$payload],JSON_UNESCAPED_SLASHES);exit;
  }
  echo json_encode(['ok'=>true,'settings'=>sk1311map_payload()],JSON_UNESCAPED_SLASHES);
}catch(Throwable $e){http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}
