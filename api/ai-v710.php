<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/smart_city_v630.php';
require_once __DIR__.'/../app/ai_orchestrator_v710.php';
header('Content-Type: application/json; charset=utf-8');
try{
 if(!ai710_enabled())throw new RuntimeException('AI Command Center is disabled for this tenant.');
 $a=(string)($_POST['action']??$_GET['action']??'chat');
 if($a==='chat'){
  if(($_SERVER['REQUEST_METHOD']??'')!=='POST')throw new RuntimeException('POST required.');csrf_check();if(!ai710_bool('ai710_public_assistant_enabled',true)&&!current_user())throw new RuntimeException('Public AI assistant is disabled.');$msg=trim((string)($_POST['message']??''));$agent=(int)($_POST['agent_id']??0)?:null;$r=ai710_chat($msg,current_user(),$agent);echo json_encode(['ok'=>true]+$r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
 }
 if($a==='search'){$q=trim((string)($_GET['q']??''));$rows=function_exists('sc630_super_search')?sc630_super_search($q,30):[];echo json_encode(['ok'=>true,'results'=>$rows],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
 if($a==='queue_action'){
  if(($_SERVER['REQUEST_METHOD']??'')!=='POST')throw new RuntimeException('POST required.');csrf_check();$u=require_staff();if(!has_permission('ai.actions.execute',$u)&&!has_permission('ai.manage',$u))throw new RuntimeException('AI action permission is required.');$key=(string)($_POST['action_key']??'');$input=json_decode((string)($_POST['input_json']??'{}'),true);if(!is_array($input))throw new RuntimeException('Action input must be valid JSON.');$r=ai710_queue_action($key,$input,$u,(int)($_POST['agent_id']??0)?:null,(string)($_POST['conversation_key']??''));echo json_encode(['ok'=>true]+$r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
 }
 if($a==='tool'){
  if(($_SERVER['REQUEST_METHOD']??'')!=='POST')throw new RuntimeException('POST required.');csrf_check();$u=require_staff();if(!has_permission('ai.actions.execute',$u)&&!has_permission('ai.manage',$u))throw new RuntimeException('AI action permission is required.');$key=(string)($_POST['tool']??'');$input=json_decode((string)($_POST['input_json']??'{}'),true);if(!is_array($input))$input=[];$r=ai710_queue_action($key,$input,$u);echo json_encode(['ok'=>true]+$r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
 }
 throw new RuntimeException('Unknown AI API action.');
}catch(Throwable $e){http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
