<?php
require __DIR__.'/app/bootstrap.php';header('Content-Type: application/manifest+json; charset=utf-8');
$name=(string)tenant_brand('site_name',setting('site_name','ShahkotPK'));$short=mb_substr($name,0,24);$tag=(string)tenant_brand('tagline','Complete local city platform');$theme=(string)tenant_brand('primary_color','#0f766e');
$icons=[['src'=>'/assets/pwa-icon-192.png','sizes'=>'192x192','type'=>'image/png','purpose'=>'any maskable'],['src'=>'/assets/pwa-icon-512.png','sizes'=>'512x512','type'=>'image/png','purpose'=>'any maskable']];
echo json_encode(['name'=>$name.' — City Platform','short_name'=>$short,'description'=>$tag,'start_url'=>'/?source=pwa','scope'=>'/','display'=>'standalone','background_color'=>'#f3f7f7','theme_color'=>$theme,'icons'=>$icons],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
