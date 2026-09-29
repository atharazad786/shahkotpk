<?php
declare(strict_types=1);
$root=dirname(__DIR__);
require_once $root.'/config/config.php';
$helper=$root.'/app/business_publish_index_v1278.php';
if(is_file($helper))require_once $helper;
if(!function_exists('bs1281_recover_public_status')){fwrite(STDERR,"Visibility helper unavailable.\n");exit(1);}
$r=bs1281_recover_public_status(250);
echo json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
