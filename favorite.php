<?php
require __DIR__.'/app/bootstrap.php';
$u=require_login();

if(!in_array($u['role'],['customer','user'],true)){
    http_response_code(403);
    exit('Favorites are available for user/customer accounts.');
}

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    exit('Method not allowed.');
}

csrf_check();

$businessId=(int)($_POST['business_id']??0);
$return=(string)($_POST['return']??'/account.php');
if(!str_starts_with($return,'/') || str_starts_with($return,'//')) $return='/account.php';

$q=db()->prepare("SELECT id FROM businesses WHERE id=? AND status=1 LIMIT 1");
$q->execute([$businessId]);
if(!$q->fetch()){
    header('Location: '.$return);
    exit;
}

$q=db()->prepare("SELECT id FROM favorite_businesses WHERE user_id=? AND business_id=? LIMIT 1");
$q->execute([$u['id'],$businessId]);
$existing=$q->fetchColumn();

if($existing){
    $q=db()->prepare("DELETE FROM favorite_businesses WHERE user_id=? AND business_id=?");
    $q->execute([$u['id'],$businessId]);
}else{
    $q=db()->prepare("INSERT IGNORE INTO favorite_businesses(user_id,business_id) VALUES(?,?)");
    $q->execute([$u['id'],$businessId]);
}

header('Location: '.$return);
exit;
