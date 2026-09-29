<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_admin();
if(function_exists('require_permission')) require_permission('settings.manage');
function ri_h($v): string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function ri_slug(string $v): string {
    $v=strtolower(trim($v));
    $v=preg_replace('~\.php$~','',$v)??$v;
    $v=preg_replace('~(?:[-_]v?\d{3,})$~','',$v)??$v;
    $v=preg_replace('~[^a-z0-9]+~','-',$v)??$v;
    return trim($v,'-');
}
$root=dirname(__DIR__);
$routes=[];
foreach(glob($root.'/*.php')?:[] as $f){$routes[]='/'.basename($f);}
sort($routes);
$families=[];
foreach($routes as $r){$k=ri_slug(basename($r));$families[$k][]=$r;}
$versionFamilies=array_filter($families,fn($xs)=>count($xs)>1);
$nav=[];
foreach(glob($root.'/app/public-nav-modules/*.json')?:[] as $f){
    $j=json_decode((string)@file_get_contents($f),true); if(!is_array($j))continue;
    foreach((array)($j['items']??[]) as $i){if(!is_array($i))continue;$nav[]=['file'=>basename($f),'key'=>(string)($i['nav_key']??''),'label'=>(string)($i['label']??''),'url'=>(string)($i['url']??'')];}
}
$byLabel=[];$byUrl=[];
foreach($nav as $i){$lk=strtolower(trim($i['label']));if($lk!=='')$byLabel[$lk][]=$i;if($i['url']!=='')$byUrl[$i['url']][]=$i;}
$labelConflicts=[];foreach($byLabel as $k=>$xs){$urls=array_values(array_unique(array_column($xs,'url')));if(count($urls)>1)$labelConflicts[$k]=$xs;}
$urlDupes=[];foreach($byUrl as $u=>$xs){if(count($xs)>1)$urlDupes[$u]=$xs;}
page_start('Route Integrity Center',true);
?>
<style>
.ri{max-width:1240px;margin:0 auto;padding:18px}.ri-hero{padding:24px;border:1px solid #d9e3ef;border-radius:18px;background:#fff;display:flex;justify-content:space-between;gap:20px;align-items:center}.ri-hero h1{margin:4px 0 8px}.ri-ok{padding:12px 14px;border-radius:12px;background:#ecfdf5;color:#065f46;margin:18px 0}.ri-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:18px 0}.ri-grid>div,.ri-card{background:#fff;border:1px solid #d9e3ef;border-radius:16px;padding:18px}.ri-grid b{display:block;font-size:26px}.ri-card{margin:14px 0}.ri-card h2{margin-top:0}.ri-table{width:100%;border-collapse:collapse}.ri-table th,.ri-table td{text-align:left;padding:10px;border-bottom:1px solid #e8eef5;vertical-align:top}.ri-badge{display:inline-block;border-radius:999px;background:#eef5ff;padding:4px 8px;font-size:12px}.ri-code{font-family:ui-monospace,monospace;font-size:12px}@media(max-width:760px){.ri-grid{grid-template-columns:1fr}.ri-hero{display:block}.ri-table{display:block;overflow:auto}}
</style>
<div class="ri"><section class="ri-hero"><div><span>v12.4.2 · ROUTE QUALITY CONTROL</span><h1>Route Integrity Center</h1><p>Detect duplicate public-navigation labels, repeated URLs and clean/versioned route families before they become customer-facing duplicates.</p></div><a href="/city-guide.php" target="_blank">Open canonical City Guide ↗</a></section>
<div class="ri-ok"><b>Canonical rule active:</b> <span class="ri-code">/city-guide.php</span> is the main Shahkot City Guide. <span class="ri-code">/city-information.php</span> is now a 301 compatibility alias only.</div>
<section class="ri-grid"><div><span>Root PHP routes</span><b><?=count($routes)?></b></div><div><span>Potential version families</span><b><?=count($versionFamilies)?></b></div><div><span>Navigation label conflicts</span><b><?=count($labelConflicts)?></b></div></section>
<section class="ri-card"><h2>Potential route families</h2><p>These are review candidates only. The scanner does not auto-delete or redirect unknown routes.</p><?php if(!$versionFamilies):?><p>No duplicate-looking version families found.</p><?php else:?><table class="ri-table"><thead><tr><th>Family</th><th>Routes</th></tr></thead><tbody><?php foreach($versionFamilies as $k=>$xs):?><tr><td><span class="ri-badge"><?=ri_h($k)?></span></td><td class="ri-code"><?=ri_h(implode(' · ',$xs))?></td></tr><?php endforeach;?></tbody></table><?php endif;?></section>
<section class="ri-card"><h2>Same navigation label → different URLs</h2><?php if(!$labelConflicts):?><p>No label conflicts found in public-nav manifests.</p><?php else:?><table class="ri-table"><thead><tr><th>Label</th><th>URLs</th></tr></thead><tbody><?php foreach($labelConflicts as $k=>$xs):?><tr><td><?=ri_h($xs[0]['label'])?></td><td><?php foreach($xs as $x):?><div class="ri-code"><?=ri_h($x['url'])?> <small>(<?=ri_h($x['file'])?>)</small></div><?php endforeach;?></td></tr><?php endforeach;?></tbody></table><?php endif;?></section>
<section class="ri-card"><h2>Repeated exact navigation URLs</h2><?php if(!$urlDupes):?><p>No repeated exact URLs found.</p><?php else:?><table class="ri-table"><thead><tr><th>URL</th><th>Entries</th></tr></thead><tbody><?php foreach($urlDupes as $u=>$xs):?><tr><td class="ri-code"><?=ri_h($u)?></td><td><?=count($xs)?></td></tr><?php endforeach;?></tbody></table><?php endif;?></section>
</div><?php page_end(); ?>
