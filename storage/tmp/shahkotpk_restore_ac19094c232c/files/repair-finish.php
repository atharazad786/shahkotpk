<?php
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$reset=false;
if(function_exists('opcache_reset')){
    try{$reset=(bool)opcache_reset();}catch(Throwable $e){$reset=false;}
}
clearstatcache(true);
$files=['app/store.php','app/accounting.php','app/cms.php','app/dashboard_widgets.php','app/runtime_guard.php'];
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ShahkotPK Runtime Repair</title><style>body{font-family:system-ui;background:#f4f8f6;color:#10241c;padding:30px}.card{max-width:850px;margin:auto;background:#fff;border:1px solid #dce8e1;border-radius:20px;padding:28px}code{background:#10241c;color:#dfffea;padding:3px 7px;border-radius:6px}.ok{color:#087b50;font-weight:700}li{margin:8px 0}a{display:inline-block;margin:8px 8px 0 0;padding:10px 15px;border-radius:10px;background:#0e6948;color:#fff;text-decoration:none}</style></head><body><div class="card"><h1>ShahkotPK Runtime Repair 3.4.3</h1><p class="ok">Repair files are installed.</p><p>PHP: <code><?=htmlspecialchars(PHP_VERSION)?></code> | SAPI: <code><?=htmlspecialchars(PHP_SAPI)?></code> | OPcache reset: <code><?=$reset?'yes':'not available / not required'?></code></p><ul><?php foreach($files as $f):?><li><?=htmlspecialchars($f)?> — <?=is_file(__DIR__.'/'.$f)?'<span class="ok">present</span>':'missing'?></li><?php endforeach;?></ul><a href="/">Open Homepage</a><a href="/boot-check.php">Boot Check</a><a href="/server-check.php">Server Check</a><p style="margin-top:22px">After the site is stable, delete <code>repair-finish.php</code>, <code>server-check.php</code> and <code>boot-check.php</code> from the public web root.</p></div></body></html>
