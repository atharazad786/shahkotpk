<?php
declare(strict_types=1);

/** ShahkotPK v11.1.1 — Google Maps auth guard + diagnostics helpers. */
if(!function_exists('sk1111_maps_browser_key')){
function sk1111_maps_setting_candidates(): array {
    return ['google_maps_browser_api_key','google_maps_api_key','maps_browser_api_key','google_map_api_key','map_api_key','maps_api_key','gmaps_api_key'];
}
function sk1111_maps_browser_key(): string {
    foreach(sk1111_maps_setting_candidates() as $key){
        try{$v=function_exists('setting')?(string)setting($key,''):'';}catch(Throwable $e){$v='';}
        if(trim($v)!=='') return trim($v);
    }
    return '';
}
function sk1111_maps_key_source(): string {
    foreach(sk1111_maps_setting_candidates() as $key){
        try{$v=function_exists('setting')?(string)setting($key,''):'';}catch(Throwable $e){$v='';}
        if(trim($v)!=='') return $key;
    }
    return '';
}
function sk1111_maps_mask(string $value): string {
    $n=strlen($value);if($n<9)return $value===''?'Not configured':str_repeat('•',$n);
    return substr($value,0,4).str_repeat('•',max(4,$n-8)).substr($value,-4);
}
function sk1111_maps_origin(): string {
    $https=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
    $scheme=$https?'https':'http';$host=(string)($_SERVER['HTTP_HOST']??'');
    return $host!==''?$scheme.'://'.$host:'';
}
function sk1111_maps_public_config(): array {
    $key=sk1111_maps_browser_key();
    return [
      'configured'=>$key!=='',
      'keySource'=>sk1111_maps_key_source(),
      'origin'=>sk1111_maps_origin(),
      'fallback'=>true,
      'version'=>'11.1.1'
    ];
}
}
