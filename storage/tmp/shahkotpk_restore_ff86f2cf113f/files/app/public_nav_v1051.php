<?php
declare(strict_types=1);
require_once __DIR__.'/public_nav_v1100.php';
if(!function_exists('spn1051_h')){function spn1051_h($v):string{return spn1100_h($v);}function spn1051_tid():int{return spn1100_tid();}function spn1051_items(?int $tenantId=null):array{return spn1100_items($tenantId);}function spn1051_current(string $url):bool{return spn1100_current($url);}function spn1051_render(string $group='primary'):void{spn1100_render($group);}function spn1051_safe_url(string $url):bool{return spn1100_safe_url($url);}}
