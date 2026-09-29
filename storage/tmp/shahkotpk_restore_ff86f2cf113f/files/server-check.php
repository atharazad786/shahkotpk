<?php
// Standalone: does NOT load ShahkotPK bootstrap.
@ini_set('display_errors', '0');
header('Content-Type: text/html; charset=UTF-8');
function esc342($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function yes342($v){return $v ? '<b style="color:#137548">PASS</b>' : '<b style="color:#b42318">FAIL</b>';}
function row342($name,$value,$ok){echo '<tr><td>'.esc342($name).'</td><td>'.esc342($value).'</td><td>'.yes342($ok).'</td></tr>';}
$root=__DIR__;
$php=PHP_VERSION;
$checks=array();
$checks[]=array('PHP version',$php,version_compare($php,'8.1.0','>='));
$checks[]=array('PHP SAPI',PHP_SAPI,true);
$checks[]=array('PDO',extension_loaded('pdo')?'loaded':'missing',extension_loaded('pdo'));
$checks[]=array('PDO MySQL',extension_loaded('pdo_mysql')?'loaded':'missing',extension_loaded('pdo_mysql'));
$checks[]=array('JSON',extension_loaded('json')?'loaded':'missing',extension_loaded('json'));
$checks[]=array('OpenSSL',extension_loaded('openssl')?'loaded':'missing',extension_loaded('openssl'));
$checks[]=array('mbstring',extension_loaded('mbstring')?'loaded':'missing',true);
$checks[]=array('cURL',extension_loaded('curl')?'loaded':'missing',true);
$checks[]=array('ZIP',class_exists('ZipArchive')?'loaded':'missing',true);
$checks[]=array('config/config.php',is_readable($root.'/config/config.php')?'readable':'missing/unreadable',is_readable($root.'/config/config.php'));
$checks[]=array('app/bootstrap.php',is_readable($root.'/app/bootstrap.php')?'readable':'missing/unreadable',is_readable($root.'/app/bootstrap.php'));
$checks[]=array('database/migrations',is_dir($root.'/database/migrations')?'present':'missing',is_dir($root.'/database/migrations'));
$checks[]=array('storage writable',is_dir($root.'/storage')&&is_writable($root.'/storage')?'writable':'not writable',is_dir($root.'/storage')&&is_writable($root.'/storage'));
$checks[]=array('storage/logs writable',(is_dir($root.'/storage/logs')&&is_writable($root.'/storage/logs'))?'writable':'not writable',is_dir($root.'/storage/logs')&&is_writable($root.'/storage/logs'));
$dbStatus='not tested';$dbOk=false;
if(is_readable($root.'/config/config.php') && extension_loaded('pdo_mysql')){
  try{
    $cfg=require $root.'/config/config.php';
    if(!is_array($cfg)||empty($cfg['db'])) throw new Exception('Config db section missing');
    $c=$cfg['db'];
    $dsn='mysql:host='.(isset($c['host'])?$c['host']:'localhost').';port='.(isset($c['port'])?$c['port']:'3306').';dbname='.(isset($c['name'])?$c['name']:'').';charset=utf8mb4';
    $pdo=new PDO($dsn,isset($c['user'])?$c['user']:'',isset($c['pass'])?$c['pass']:'',array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_TIMEOUT=>5));
    $pdo->query('SELECT 1');$dbStatus='connected';$dbOk=true;
  }catch(Throwable $e){$dbStatus='FAILED: '.$e->getMessage();}
  catch(Exception $e){$dbStatus='FAILED: '.$e->getMessage();}
}
$logs=array('storage/logs/bootstrap-fatal.log','storage/logs/runtime-errors.log','storage/logs/migrations.log');
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ShahkotPK Server Check</title><style>body{font-family:Arial;background:#f5f7f6;color:#14251c;margin:0}.w{max-width:1050px;margin:35px auto;padding:20px}.card{background:#fff;border:1px solid #dce5df;border-radius:18px;padding:24px;margin-bottom:18px}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:11px;border-bottom:1px solid #edf1ee;vertical-align:top}pre{white-space:pre-wrap;word-break:break-word;background:#111b16;color:#e8fff3;padding:16px;border-radius:12px;max-height:340px;overflow:auto}.btn{display:inline-block;background:#176b46;color:#fff;padding:11px 15px;border-radius:10px;text-decoration:none;font-weight:700;margin-right:8px}</style></head><body><div class="w"><div class="card"><h1>ShahkotPK Standalone Server Check</h1><p>This page does not load the ShahkotPK application. Passwords and database credentials are not displayed.</p><a class="btn" href="/boot-check.php">Run App Boot Check</a><a class="btn" href="/">Open Home</a></div><div class="card"><h2>Environment</h2><table><tr><th>Check</th><th>Value</th><th>Status</th></tr><?php foreach($checks as $c)row342($c[0],$c[1],$c[2]); row342('Database connection',$dbStatus,$dbOk);?></table></div><?php foreach($logs as $rel): $file=$root.'/'.$rel; ?><div class="card"><h2><?=esc342($rel)?></h2><?php if(is_readable($file)): $lines=@file($file,FILE_IGNORE_NEW_LINES); $lines=is_array($lines)?array_slice($lines,-40):array(); ?><pre><?=esc342(implode("\n",$lines))?></pre><?php else:?><p>No readable log yet.</p><?php endif;?></div><?php endforeach;?></div></body></html>
