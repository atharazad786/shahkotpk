<?php
declare(strict_types=1);
require_once __DIR__.'/app/bootstrap.php';
$id=(int)($_GET['id']??0);if($id<=0){http_response_code(404);exit('QR not found.');}
try{$q=db()->prepare("SELECT id,target_url,status FROM qr_assets WHERE id=? LIMIT 1");$q->execute([$id]);$r=$q->fetch();if(!$r||(int)$r['status']!==1){http_response_code(404);exit('QR not found.');}$target=trim((string)$r['target_url']);if($target===''||preg_match('/[\r\n]/',$target)){http_response_code(400);exit('Invalid QR target.');}if(!str_starts_with($target,'/')&&!preg_match('#^https?://#i',$target)){http_response_code(400);exit('Invalid QR target.');}db()->prepare("UPDATE qr_assets SET scan_count=scan_count+1 WHERE id=?")->execute([$id]);header('Location: '.$target,true,302);exit;}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('QR redirect failed',$e);http_response_code(500);exit('QR redirect is temporarily unavailable.');}
