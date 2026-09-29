<?php
require __DIR__.'/app/bootstrap.php';
$id=(int)($_GET['id']??0);ad_sync_scheduled_statuses();
$q=db()->prepare("SELECT a.*,d.pricing_model,d.rate,d.impressions,d.clicks,d.impression_limit,d.click_limit,d.budget FROM advertisements a LEFT JOIN advertisement_details d ON d.advertisement_id=a.id WHERE a.id=? AND a.status='active' LIMIT 1");$q->execute([$id]);$ad=$q->fetch();
$url=$ad?(string)$ad['target_url']:'';
if($ad && $url && !ad_limit_reached($ad)){
    try{db()->prepare("UPDATE advertisement_details SET clicks=clicks+1 WHERE advertisement_id=?")->execute([$id]);ad_sync_scheduled_statuses();}catch(Throwable $e){}
    if(ad_valid_target_url($url)){header('Location: '.$url);exit;}
}
header('Location: /');exit;
