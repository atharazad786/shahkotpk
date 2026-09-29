<?php
declare(strict_types=1);
/** ShahkotPK v13.0.15.1 — dynamic sitemap endpoint (host-safe, no DB scan). */
header('Content-Type: application/xml; charset=UTF-8');
$https=(!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off')||strtolower(trim(explode(',',(string)($_SERVER['HTTP_X_FORWARDED_PROTO']??''))[0]??''))==='https';
$host=trim((string)($_SERVER['HTTP_HOST']??''));
if(!preg_match('/^[A-Za-z0-9.-]+(?::[0-9]{1,5})?$/',$host))$host='localhost';
$base=($https?'https':'http').'://'.$host;
$routes=['/','/businesses.php','/marketplace.php','/events.php','/jobs.php','/property.php','/news.php','/services.php','/doctors.php','/blood-donors.php','/contact.php','/about.php'];
function sx($s){return htmlspecialchars((string)$s,ENT_XML1|ENT_QUOTES,'UTF-8');}
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
foreach($routes as $r){$file=$r==='/'?'index.php':ltrim($r,'/');if(!is_file(__DIR__.'/'.$file))continue;echo '  <url><loc>'.sx($base.$r).'</loc></url>'."\n";}
echo '</urlset>'."\n";
