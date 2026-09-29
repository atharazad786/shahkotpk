<?php
declare(strict_types=1);

/** Optional public maintenance guard installed by v9.8.0 System Control Center. */
function sk980_maintenance_read(string $key,string $default=''): string {
    try{
        if(function_exists('setting')) return (string)setting($key,$default);
        if(function_exists('db')){$q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}
    }catch(Throwable $e){}
    return $default;
}
function sk980_maintenance_guard(): void {
    if(sk980_maintenance_read('system_maintenance_enabled_v980','0')!=='1')return;
    $uri=(string)($_SERVER['REQUEST_URI']??'/');$path=(string)(parse_url($uri,PHP_URL_PATH)?:'/');
    foreach(['/admin/','/assets/','/uploads/','/storage/'] as $prefix)if(str_starts_with($path,$prefix))return;
    if(in_array($path,['/admin','/login.php','/admin/login.php','/maintenance.php'],true))return;
    $until=trim(sk980_maintenance_read('system_maintenance_until_v980',''));
    if($until!=='' && strtotime($until)!==false && strtotime($until)<time())return;
    $message=trim(sk980_maintenance_read('system_maintenance_message_v980','Scheduled maintenance is in progress. Please try again shortly.'));
    if($message==='')$message='Scheduled maintenance is in progress. Please try again shortly.';
    http_response_code(503);header('Retry-After: 600');header('Cache-Control: no-store, no-cache, must-revalidate');header('Content-Type: text/html; charset=utf-8');
    $safe=htmlspecialchars($message,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Maintenance · ShahkotPK</title><style>body{margin:0;background:#071426;color:#e8f1ff;font:16px/1.6 system-ui,-apple-system,Segoe UI,sans-serif;min-height:100vh;display:grid;place-items:center;padding:20px;box-sizing:border-box}.c{max-width:640px;background:#0d2039;border:1px solid #254366;border-radius:24px;padding:34px;box-shadow:0 24px 80px #0006}.b{display:inline-block;background:#163e6d;border-radius:999px;padding:7px 11px;font-size:12px;font-weight:800;color:#9dcbff}h1{font-size:32px;line-height:1.15;margin:15px 0 10px}p{color:#c7d8ee}.dot{width:10px;height:10px;background:#34d399;border-radius:50%;display:inline-block;margin-right:8px;box-shadow:0 0 18px #34d399}</style></head><body><main class="c"><span class="b"><i class="dot"></i>SHAHKOTPK MAINTENANCE</span><h1>We will be back shortly.</h1><p>'.$safe.'</p><p style="font-size:13px;color:#87a3c3">Administrators can continue to use the protected admin panel.</p></main></body></html>';exit;
}
?>