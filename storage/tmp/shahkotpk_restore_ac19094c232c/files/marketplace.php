<?php
declare(strict_types=1);
/**
 * ShahkotPK v13.0.5.1 — canonical Marketplace compatibility route.
 * Restores the registered /marketplace.php endpoint without changing the native menu.
 */
$root=__DIR__;
$targets=[];
$versioned=glob($root.'/marketplace-v*.php')?:[];
usort($versioned,static fn($a,$b)=>strnatcasecmp(basename($b),basename($a)));
foreach($versioned as $f)if(is_file($f)&&basename($f)!=='marketplace.php')$targets[]='/'.basename($f);
foreach(['products.php','shop.php','catalog.php','businesses.php'] as $name)if(is_file($root.'/'.$name))$targets[]='/'.$name;
$target=$targets[0]??'/';
if($target==='/businesses.php'){
    $qs=(string)($_SERVER['QUERY_STRING']??'');
    $target.='?'.($qs!==''?$qs.'&':'').'view=marketplace';
}else{
    $qs=(string)($_SERVER['QUERY_STRING']??'');
    if($qs!=='')$target.=(str_contains($target,'?')?'&':'?').$qs;
}
header('Cache-Control: no-store, max-age=0');
header('Location: '.$target,true,302);
exit;
