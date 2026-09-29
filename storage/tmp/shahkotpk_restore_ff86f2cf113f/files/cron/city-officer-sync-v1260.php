<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/city_portal_v1251.php';
require_once dirname(__DIR__).'/app/city_sync_v1260.php';
$s=sk1260_settings();
if(empty($s['scheduled_sync_enabled'])){fwrite(STDOUT,"City official-source scheduled sync is disabled.\n");exit(0);}
$r=sk1260_sync_all('cron',0);
fwrite(STDOUT,json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
