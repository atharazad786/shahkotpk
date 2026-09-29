<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/business_source_import_v1270.php';
require_once dirname(__DIR__).'/app/business_source_publish_v1277.php';
$s=bs1270_setting_row();if(empty($s['enabled'])||empty($s['scheduled_sync'])){echo "Business source scheduled sync disabled.\n";exit;}
$auto=bs1270_auto_process_one();echo 'Auto candidate: '.($auto['status']??'none').(!empty($auto['business_id'])?' business #'.$auto['business_id']:'')."\n";$rows=bs1270_linked_businesses(2);$ok=0;$bad=0;foreach($rows as $r){try{bs1277_sync_business((int)$r['business_id']);$ok++;echo 'Synced #'.(int)$r['business_id']."\n";}catch(Throwable $e){$bad++;echo 'Skipped #'.(int)$r['business_id'].' — '.$e->getMessage()."\n";}}
echo "Done. synced=$ok skipped=$bad\n";
