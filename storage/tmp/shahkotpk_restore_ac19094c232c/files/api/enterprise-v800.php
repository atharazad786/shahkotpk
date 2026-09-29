<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/enterprise_v800.php';
header('Content-Type: application/json; charset=utf-8');
function v800_api_fail(int $code,string $msg): never {http_response_code($code);echo json_encode(['ok'=>false,'error'=>$msg]);exit;}
$auth=(string)($_SERVER['HTTP_AUTHORIZATION']??'');if(!preg_match('/^Bearer\s+([^\.\s]+)\.([A-Fa-f0-9]+)$/',$auth,$m))v800_api_fail(401,'Bearer credential required.');$clientKey=$m[1];$secret=$m[2];
if(!v800_table('enterprise_api_clients_v800'))v800_api_fail(503,'Enterprise API schema unavailable.');$q=db()->prepare('SELECT * FROM enterprise_api_clients_v800 WHERE client_key=? AND enabled=1 LIMIT 1');$q->execute([$clientKey]);$client=$q->fetch();if(!$client||!hash_equals((string)$client['secret_hash'],hash('sha256',$secret)))v800_api_fail(401,'Invalid API credential.');
$tid=(int)$client['tenant_id'];$GLOBALS['V800_TENANT_OVERRIDE']=$tid;$scopes=v800_json($client['scopes_json']??'');$resource=(string)($_GET['resource']??'overview');
try{$rq=db()->prepare('SELECT COALESCE(SUM(metric_value),0) FROM enterprise_usage_v800 WHERE tenant_id=? AND usage_date=CURDATE() AND metric_key=\'api_requests\'');$rq->execute([$tid]);if((float)$rq->fetchColumn()>=(int)$client['rate_limit_per_hour']*24)v800_api_fail(429,'Daily safety limit reached.');}catch(Throwable $e){}
try{db()->prepare('INSERT INTO enterprise_usage_v800(tenant_id,usage_date,metric_key,metric_value,meta_json) VALUES(?,CURDATE(),\'api_requests\',1,NULL) ON DUPLICATE KEY UPDATE metric_value=metric_value+1')->execute([$tid]);db()->prepare('UPDATE enterprise_api_clients_v800 SET last_used_at=NOW() WHERE id=?')->execute([(int)$client['id']]);}catch(Throwable $e){}
if(!in_array('read',$scopes,true)&&!in_array('*',$scopes,true))v800_api_fail(403,'Read scope is not assigned.');
if($resource==='overview'){$data=v800_overview_stats();}
elseif($resource==='health'){$data=v800_health_rows();}
elseif($resource==='bi'){$data=v800_bi_overview();foreach($data as &$m)unset($m['series']);unset($m);}
else v800_api_fail(404,'Unknown resource.');
echo json_encode(['ok'=>true,'tenant_id'=>$tid,'resource'=>$resource,'data'=>$data],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
