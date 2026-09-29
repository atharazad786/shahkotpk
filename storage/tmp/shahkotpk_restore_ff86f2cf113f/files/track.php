<?php
require __DIR__.'/app/bootstrap.php';
$event=preg_replace('/[^a-z0-9_-]/','',strtolower((string)($_REQUEST['event']??'')));if($event===''){http_response_code(400);exit;}$business=(int)($_REQUEST['business_id']??0)?:null;$entityType=preg_replace('/[^a-z0-9_-]/','',strtolower((string)($_REQUEST['entity_type']??'')));$entity=(int)($_REQUEST['entity_id']??0)?:null;growth_track($event,$entityType,$entity,$business);http_response_code(204);exit;
