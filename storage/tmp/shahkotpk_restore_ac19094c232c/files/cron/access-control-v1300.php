<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/access_control_v1300.php';
$n=sk1300_sync_features();$r=sk1300_repair_legacy_access(false);echo 'Feature sync checks: '.$n.PHP_EOL.'Shopkeepers normalized: '.(int)$r['shopkeepers'].PHP_EOL.'Customers normalized: '.(int)$r['customers'].PHP_EOL;
