<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/business_source_import_v1270.php';
require_once dirname(__DIR__).'/app/business_source_lifecycle_v1273.php';
require_once dirname(__DIR__).'/app/shopkeeper_auth_v1283.php';
$r=bs1283_repair_batch(20);echo json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
