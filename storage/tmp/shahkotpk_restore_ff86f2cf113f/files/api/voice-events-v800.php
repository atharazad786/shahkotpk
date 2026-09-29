<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/enterprise_v800.php';
header('Content-Type: application/json; charset=utf-8');
try{
    if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){http_response_code(405);throw new RuntimeException('POST required.');}
    $integrationId=(int)($_GET['integration_id']??$_POST['integration_id']??0);if($integrationId<1)throw new RuntimeException('integration_id is required.');
    if(!v800_table('voice_integrations_v800'))throw new RuntimeException('Voice integration schema is unavailable.');
    $q=db()->prepare('SELECT * FROM voice_integrations_v800 WHERE id=? AND enabled=1 LIMIT 1');$q->execute([$integrationId]);$integration=$q->fetch();if(!$integration){http_response_code(404);throw new RuntimeException('Voice integration not found.');}
    $token=(string)($_SERVER['HTTP_X_SHAHKOT_VOICE_TOKEN']??'');if($token===''||empty($integration['webhook_token_hash'])||!hash_equals((string)$integration['webhook_token_hash'],hash('sha256',$token))){http_response_code(401);v800_security_event('voice.webhook.denied','warning','Invalid voice webhook token.',['integration_id'=>$integrationId],null,(int)$integration['tenant_id']);throw new RuntimeException('Unauthorized webhook.');}
    $raw=file_get_contents('php://input')?:'';$data=json_decode($raw,true);if(!is_array($data))$data=$_POST;if(!is_array($data))$data=[];
    $tenantId=(int)$integration['tenant_id'];$GLOBALS['V800_TENANT_OVERRIDE']=$tenantId;$limit=max(10,min(10000,(int)(v800_setting('security_webhook_rate_limit','300'))));
    try{$rq=db()->prepare('SELECT COUNT(*) FROM voice_call_events_v800 WHERE tenant_id=? AND integration_id=? AND created_at>=DATE_SUB(NOW(),INTERVAL 1 HOUR)');$rq->execute([$tenantId,$integrationId]);if((int)$rq->fetchColumn()>$limit){http_response_code(429);throw new RuntimeException('Webhook rate limit exceeded.');}}catch(RuntimeException $e){throw $e;}catch(Throwable $e){}
    $id=v800_voice_event($data,$tenantId,$integrationId);
    $transcript=trim((string)($data['transcript']??$data['transcript_text']??''));if($id&&$transcript!==''&&!empty($integration['recording_enabled'])){db()->prepare('UPDATE voice_call_events_v800 SET transcript_text=?,updated_at=NOW() WHERE id=? AND tenant_id=?')->execute([mb_substr($transcript,0,100000),$id,$tenantId]);}
    $status=strtolower((string)($data['status']??''));if($id&&in_array($status,['ended','completed'],true)&&$transcript!=='')v800_queue_add('voice_ai_summary',['call_event_id'=>$id],80,$tenantId,4,'voice');
    echo json_encode(['ok'=>true,'event_id'=>$id]);
}catch(Throwable $e){if(http_response_code()<400)http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}
