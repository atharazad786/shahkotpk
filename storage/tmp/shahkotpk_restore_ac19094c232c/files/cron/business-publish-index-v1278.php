<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/business_publish_index_v1278.php';
$r=bs1278_repair_batch(20,false);
echo json_encode($r,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
