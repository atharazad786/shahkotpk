<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/business_geo_map_bridge_v1284.php';
$r=bs1284_sync_linked_batch(20);echo json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
