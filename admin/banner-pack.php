<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
require_once __DIR__.'/../app/banner_pack_v54.php';
$me=require_permission('homepage.manage');$flash='';$error='';
function v54_admin_url(string $value,string $fallback): string {$v=trim($value);if($v===''||!(str_starts_with($v,'/')||str_starts_with($v,'https://')))return $fallback;return substr($v,0,500);}
try{
 if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
  csrf_check();
  $position=(string)($_POST['position']??'prepend');if(!in_array($position,['prepend','append','replace'],true))$position='prepend';
  $interval=max(2500,min(12000,(int)($_POST['slider_interval']??5500)));
  save_settings([
   'v54_banner_pack_enabled'=>!empty($_POST['pack_enabled'])?'1':'0','v54_banner_visual_only'=>!empty($_POST['visual_only'])?'1':'0','v54_banner_pack_position'=>$position,
   'v54_banner_1_enabled'=>!empty($_POST['banner_1_enabled'])?'1':'0','v54_banner_2_enabled'=>!empty($_POST['banner_2_enabled'])?'1':'0','v54_banner_3_enabled'=>!empty($_POST['banner_3_enabled'])?'1':'0',
   'v54_banner_1_url'=>v54_admin_url((string)($_POST['banner_1_url']??''),'/search.php'),'v54_banner_2_url'=>v54_admin_url((string)($_POST['banner_2_url']??''),'/businesses.php'),'v54_banner_3_url'=>v54_admin_url((string)($_POST['banner_3_url']??''),'/city-discovery.php'),
   'homepage_slider_enabled'=>!empty($_POST['slider_enabled'])?'1':'0','homepage_slider_interval_ms'=>(string)$interval,
  ]);
  $flash='Landing slider banner settings saved.';
  if(function_exists('tenant_audit'))tenant_audit('banner_pack_v54.saved','setting',null,'v5.4 landing banner pack updated');
 }
}catch(Throwable $e){$error=$e->getMessage();}
$banners=[
 ['n'=>1,'name'=>'Discover Shahkot','image'=>'/assets/banners/v5.4/01-discover-shahkot.webp','url'=>(string)setting('v54_banner_1_url','/search.php')],
 ['n'=>2,'name'=>'Everything You Need in Shahkot','image'=>'/assets/banners/v5.4/02-everything-in-shahkot.webp','url'=>(string)setting('v54_banner_2_url','/businesses.php')],
 ['n'=>3,'name'=>'Explore · Connect · Experience','image'=>'/assets/banners/v5.4/03-explore-connect-shahkot.webp','url'=>(string)setting('v54_banner_3_url','/city-discovery.php')],
];
page_start('Landing Banner Pack',true);
?>
<style>
.v54b-hero{padding:24px;border-radius:22px;background:linear-gradient(135deg,#0b1730,#183a6f);color:#fff;margin-bottom:18px}.v54b-hero span{font-size:11px;font-weight:900;letter-spacing:.14em;color:#ff7880}.v54b-hero h2{font-size:30px;margin:8px 0}.v54b-hero p{max-width:820px;color:#cbd6e8;line-height:1.6}.v54b-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.v54b-card{background:#fff;border:1px solid #e1e8f1;border-radius:18px;overflow:hidden}.v54b-card img{display:block;width:100%;aspect-ratio:1916/821;object-fit:cover}.v54b-body{padding:14px}.v54b-body h3{margin:0 0 10px}.v54b-toggle{display:flex;gap:8px;align-items:center;font-weight:700;margin:8px 0}.v54b-settings{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.v54b-settings label{display:grid;gap:6px;font-weight:700}.v54b-settings small{font-weight:400;color:#6c788b}.v54b-alert{padding:12px 15px;border-radius:12px;margin-bottom:14px}.v54b-alert.ok{background:#ecfdf3;color:#17633d}.v54b-alert.err{background:#fff1f2;color:#9f253a}.v54b-save{margin-top:18px;display:flex;gap:10px;align-items:center}.v54b-save .btn{padding:12px 18px}@media(max-width:1050px){.v54b-grid,.v54b-settings{grid-template-columns:1fr}}
</style>
<div class="v54b-hero"><span>SHAHKOTPK v5.4.0</span><h2>Landing Slider Banner Pack</h2><p>Three built-in 1916×821 premium banners for the front-page slider. Enable or disable each banner, set click targets, choose whether the pack appears before/after existing Banner Manager slides, and control autoplay speed.</p></div>
<?php if($flash):?><div class="v54b-alert ok"><?=e($flash)?></div><?php endif;?><?php if($error):?><div class="v54b-alert err"><?=e($error)?></div><?php endif;?>
<form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<div class="card"><div class="v54b-settings">
<label><span>Banner Pack</span><span class="v54b-toggle"><input type="checkbox" name="pack_enabled" <?=setting_bool('v54_banner_pack_enabled',true)?'checked':''?>> Enabled on landing slider</span><small>Does not delete or overwrite your existing Banner Manager slides.</small></label>
<label>Pack position<select name="position"><option value="prepend" <?=setting('v54_banner_pack_position','prepend')==='prepend'?'selected':''?>>Before existing banners</option><option value="append" <?=setting('v54_banner_pack_position','prepend')==='append'?'selected':''?>>After existing banners</option><option value="replace" <?=setting('v54_banner_pack_position','prepend')==='replace'?'selected':''?>>Show only v5.4 banner pack</option></select><small>Recommended: Before existing banners.</small></label>
<label>Slider interval (milliseconds)<input type="number" min="2500" max="12000" step="100" name="slider_interval" value="<?=e((string)setting_int('homepage_slider_interval_ms',5500))?>"><span class="v54b-toggle"><input type="checkbox" name="slider_enabled" <?=setting_bool('homepage_slider_enabled',true)?'checked':''?>> Slider enabled</span></label>
</div><label class="v54b-toggle" style="margin-top:14px"><input type="checkbox" name="visual_only" <?=setting_bool('v54_banner_visual_only',true)?'checked':''?>> Use banners as full visual images without adding duplicate text overlay on top</label></div>
<div class="v54b-grid">
<?php foreach($banners as $b):?><article class="v54b-card"><img src="<?=e($b['image'])?>" alt="<?=e($b['name'])?>"><div class="v54b-body"><h3><?=e($b['name'])?></h3><label class="v54b-toggle"><input type="checkbox" name="banner_<?=e((string)$b['n'])?>_enabled" <?=setting_bool('v54_banner_'.$b['n'].'_enabled',true)?'checked':''?>> Include in slider</label><label>Click destination<input style="width:100%" name="banner_<?=e((string)$b['n'])?>_url" value="<?=e($b['url'])?>"></label></div></article><?php endforeach;?>
</div>
<div class="v54b-save"><button class="btn" type="submit">Save Banner Settings</button><a class="btn" style="background:#475569" target="_blank" href="/?theme_preview=<?=e((string)active_landing_theme())?>">Preview Homepage ↗</a></div>
</form>
<?php page_end(); ?>
