<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
foreach(['core_sync_v1301.php','access_control_v1300.php','platform_settings_root_v1314.php'] as $f){$p=__DIR__.'/../app/'.$f;if(is_file($p))require_once $p;}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
function sk1314ps_api_allowed(): bool {
    $me=function_exists('current_user')?current_user():null;if(!$me)return false;
    if(function_exists('sk1300_super_admin')&&sk1300_super_admin($me))return true;
    if(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($me))return true;
    if(function_exists('sk1300_can'))foreach(['admin.control_center','admin.settings','admin.map_control'] as $p)if(sk1300_can($p,$me))return true;
    if(function_exists('has_permission'))foreach(['settings.manage','admin.settings'] as $p)if(has_permission($p,$me))return true;
    $role=strtolower((string)($me['role']??''));return in_array($role,['admin','super_admin'],true);
}
try{
    if(!sk1314ps_api_allowed()){http_response_code(403);echo json_encode(['ok'=>false,'error'=>'Forbidden']);exit;}
    $method=strtoupper((string)($_SERVER['REQUEST_METHOD']??'GET'));
    if($method==='POST'){
        $raw=(string)file_get_contents('php://input');$data=json_decode($raw,true);if(!is_array($data))$data=$_POST;
        $headerToken=(string)($_SERVER['HTTP_X_CSRF_TOKEN']??$_SERVER['HTTP_X_CSRF']??'');
        if(!isset($_POST['_csrf']))$_POST['_csrf']=(string)($data['_csrf']??$headerToken);
        if(function_exists('csrf_check'))csrf_check();
        $module=(string)($data['module']??'');$values=$data['values']??[];if(!is_array($values))$values=[];
        $saved=sk1314ps_save($module,$values);
        echo json_encode(['ok'=>true,'module'=>$module,'values'=>$saved],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
    }
    $module=$_GET['module']??null;
    $payload=sk1314ps_module_payload($module===null||$module===''?null:(string)$module);
    echo json_encode(['ok'=>true,'version'=>'13.1.4']+$payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}catch(Throwable $e){http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
