<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/business_source_import_v1270.php';
require_once __DIR__.'/../app/business_source_publish_v1277.php';
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=300');
try{
    $bid=max(0,(int)($_GET['business_id']??0));$idx=max(0,min(5,(int)($_GET['index']??0)));
    if($bid<1)throw new RuntimeException('Invalid business.');
    $b=bs1277_business($bid);if(!$b)throw new RuntimeException('Business not found.');
    $st=strtolower(trim((string)($b['status']??'active')));if(in_array($st,['deleted','rejected','disabled','inactive'],true))throw new RuntimeException('Business is not public.');
    $link=bs1277_link_for_business($bid);if(!$link)throw new RuntimeException('No source link.');
    $g=bs1270_google_detail((string)$link['provider_place_id']);$geo=bs1270_shahkot_check($g);if(empty($geo['ok']))throw new RuntimeException('Source outside Shahkot.');
    $photo=(array)($g['photos'][$idx]??[]);$name=trim((string)($photo['name']??''));if($name==='')throw new RuntimeException('Photo unavailable.');
    $key=bs1270_google_key();if($key==='')throw new RuntimeException('Google server key unavailable.');
    $url='https://places.googleapis.com/v1/'.ltrim($name,'/').'/media?maxWidthPx=1400&maxHeightPx=1000&key='.rawurlencode($key);
    $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_ENCODING=>'']);
    $body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$type=(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE);$err=curl_error($ch);curl_close($ch);
    if(!is_string($body)||$err!==''||$code<200||$code>=300||!str_starts_with(strtolower($type),'image/'))throw new RuntimeException('Photo unavailable.');
    header('Content-Type: '.$type);echo $body;
}catch(Throwable $e){http_response_code(404);header('Content-Type: image/svg+xml; charset=utf-8');echo '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#eef4f8"/><stop offset="1" stop-color="#dfe8ef"/></linearGradient></defs><rect width="100%" height="100%" fill="url(#g)"/><text x="50%" y="48%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="42" fill="#5d6b78">ShahkotPK Business</text><text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="24" fill="#7e8b96">Live photo unavailable</text></svg>';}
