<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
if(is_file(__DIR__.'/../app/core_sync_v1301.php'))require_once __DIR__.'/../app/core_sync_v1301.php';
if(is_file(__DIR__.'/../app/access_control_v1300.php'))require_once __DIR__.'/../app/access_control_v1300.php';
require_once __DIR__.'/../app/admin_control_center_v1310.php';
$me=current_user();$ok=false;if($me){if(function_exists('sk1300_super_admin')&&sk1300_super_admin($me))$ok=true;elseif(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($me))$ok=true;elseif(function_exists('sk1300_can')&&sk1300_can('admin.control_center',$me))$ok=true;elseif(function_exists('has_permission')&&has_permission('settings.manage',$me))$ok=true;}if(!$ok){http_response_code(403);exit('Forbidden');}
function sk1310h($v): string {return function_exists('sk1301_h')?sk1301_h((string)$v):htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
$r=sk1310_snapshot();$s=$r['summary'];
page_start('Unified Admin Control Center',true);
?>
<style>
.cc1310{max-width:1540px;margin:0 auto;padding:22px}.cc1310 *{box-sizing:border-box}.cc1310 .hero{padding:26px;border-radius:24px;background:linear-gradient(135deg,var(--sk-brand-primary,#0f2a4a),#275b98);color:#fff;display:flex;justify-content:space-between;gap:20px;align-items:flex-start}.cc1310 .hero h1{margin:.28em 0}.cc1310 .hero .ver{font-weight:800;font-size:12px;letter-spacing:.04em}.cc1310 .hero .badge{padding:9px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-weight:800}.cc1310 .grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px;margin:18px 0}.cc1310 .card,.cc1310 .panel{background:#fff;border:1px solid #dce5ef;border-radius:18px;padding:18px}.cc1310 .card b{display:block;font-size:25px;margin-bottom:3px}.cc1310 .panel{margin:16px 0}.cc1310 .health{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}.cc1310 .health a{display:block;text-decoration:none;color:inherit;border:1px solid #e1e8f0;border-radius:16px;padding:15px;min-width:0}.cc1310 .health strong{display:block;font-size:25px}.cc1310 .health small{color:#667085}.cc1310 .good{color:#067647}.cc1310 .warn{color:#b54708}.cc1310 .bad{color:#b42318}.cc1310 .muted{color:#667085}.cc1310 .toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:12px 0}.cc1310 input[type=search]{min-width:280px;max-width:520px;width:100%;padding:11px 13px;border:1px solid #cfd8e3;border-radius:12px;font-size:15px}.cc1310 .scroll{overflow:auto}.cc1310 table{width:100%;border-collapse:collapse}.cc1310 th,.cc1310 td{padding:10px;border-bottom:1px solid #e6edf5;text-align:left;vertical-align:middle}.cc1310 th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#667085}.cc1310 .pill{display:inline-block;padding:4px 8px;border-radius:999px;background:#eef2f6;font-size:12px;font-weight:800}.cc1310 .launch{display:inline-flex;padding:7px 10px;border-radius:10px;text-decoration:none;background:#1769e0;color:#fff;font-weight:800}.cc1310 tr.is-hidden{display:none}.cc1310 .contract{background:#f8fafc}.cc1310 code{font-size:12px}@media(max-width:1200px){.cc1310 .grid{grid-template-columns:repeat(3,minmax(0,1fr))}.cc1310 .health{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:760px){.cc1310{padding:12px}.cc1310 .hero{display:block;padding:19px}.cc1310 .grid{grid-template-columns:repeat(2,minmax(0,1fr))}.cc1310 .health{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.cc1310 .grid,.cc1310 .health{grid-template-columns:1fr}.cc1310 input[type=search]{min-width:0}}
</style>
<div class="cc1310">
<section class="hero"><div><div class="ver">SHAHKOTPK v13.1.0 · FEATURE RELEASE</div><h1>Unified Admin Control Center</h1><p>One lightweight place for saved integrity health, release state, module discovery and direct admin launches.</p></div><div class="badge">READ-ONLY SNAPSHOT</div></section>
<div class="grid">
 <div class="card"><b><?=sk1310h($r['installed'])?></b><span>Installed version</span></div>
 <div class="card"><b><?=sk1310h($r['stable_baseline']?:'—')?></b><span>Stable baseline</span></div>
 <div class="card"><b class="good"><?=sk1310h((string)$s['good'])?></b><span>Healthy audits</span></div>
 <div class="card"><b class="<?=$s['attention']?'bad':'good'?>"><?=sk1310h((string)$s['attention'])?></b><span>Needs attention</span></div>
 <div class="card"><b><?=sk1310h((string)$s['modules'])?></b><span>Admin modules</span></div>
 <div class="card"><b class="<?=$s['missing_routes']?'bad':'good'?>"><?=sk1310h((string)$s['missing_routes'])?></b><span>Missing module files</span></div>
</div>
<section class="panel"><h2>Integrity health</h2><p class="muted">These cards read the latest saved audit only. Opening this dashboard does not run any scanner or benchmark.</p><div class="health">
<?php foreach($r['centers'] as $c):$cl=$c['state']==='good'?'good':($c['state']==='attention'?'bad':($c['state']==='review'?'warn':''));?>
<a href="<?=sk1310h($c['url'])?>"><span><?=sk1310h($c['label'])?></span><strong class="<?=$cl?>"><?=$c['score']===null?'—':sk1310h((string)$c['score']).'/100'?></strong><small><?=$c['created_at']?sk1310h($c['created_at']):'Not run yet'?></small></a>
<?php endforeach;?>
</div></section>
<section class="panel"><h2>Admin module directory</h2><div class="toolbar"><input id="cc1310Search" type="search" placeholder="Search module, category or route…" aria-label="Search admin modules"><span class="pill"><?=sk1310h((string)$s['mapped'])?> permission-mapped</span><span class="pill"><?=sk1310h((string)$s['blocked'])?> unavailable to this account</span></div><div class="scroll"><table id="cc1310Modules"><thead><tr><th>Module</th><th>Category</th><th>Route</th><th>File</th><th>Permission</th><th></th></tr></thead><tbody>
<?php foreach($r['modules'] as $m):?><tr data-search="<?=sk1310h(strtolower($m['label'].' '.$m['category'].' '.$m['url'].' '.$m['feature']))?>"><td><strong><?=sk1310h($m['icon'].' '.$m['label'])?></strong></td><td><?=sk1310h($m['category'])?></td><td><code><?=sk1310h($m['url'])?></code></td><td class="<?=$m['physical']?'good':'bad'?>"><?=$m['physical']?'FOUND':'MISSING'?></td><td><?php if($m['feature']!==''):?><code><?=sk1310h($m['feature'])?></code> · <span class="<?=$m['allowed']?'good':'bad'?>"><?=$m['allowed']?'ALLOWED':'BLOCKED'?></span><?php else:?><span class="warn">No mapping</span><?php endif;?></td><td><?php if($m['physical']&&$m['allowed']):?><a class="launch" href="<?=sk1310h($m['url'])?>">Open</a><?php else:?><span class="muted">—</span><?php endif;?></td></tr><?php endforeach;?>
</tbody></table></div></section>
<section class="panel contract"><h2>Release contract</h2><table><tbody><tr><th>Stable readiness</th><td><?=sk1310h($r['stable_readiness']?:'—')?></td></tr><tr><th>Runtime contract</th><td><?=sk1310h($r['runtime_contract']?:'—')?></td></tr><tr><th>Public cache version</th><td><?=sk1310h($r['public_cache']?:'—')?></td></tr></tbody></table><p class="muted">The Control Center itself is read-only. It does not edit content, permissions, cache rows, users, menus or hosting configuration. Actions remain inside their owning admin modules.</p></section>
</div>
<script>
(()=>{const input=document.getElementById('cc1310Search'),rows=[...document.querySelectorAll('#cc1310Modules tbody tr')];if(!input)return;input.addEventListener('input',()=>{const q=input.value.trim().toLowerCase();rows.forEach(r=>r.classList.toggle('is-hidden',q!==''&&!String(r.dataset.search||'').includes(q)));});})();
</script>
