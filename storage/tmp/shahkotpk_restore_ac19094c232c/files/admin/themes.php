<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
require_once __DIR__.'/../app/landing_reference_themes_v51.php';
$me=require_permission('themes.manage');
$flash='';$error='';

try{
    if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
        csrf_check();
        $action=(string)($_POST['action']??'');
        if($action==='landing_theme'){
            $slug=trim((string)($_POST['theme_slug']??''));
            shahkot_apply_landing_theme($slug);
            $flash='Landing theme activated. Homepage CMS and Theme Studio are now synchronized.';
        } elseif($action==='landing_animation'){
            save_setting('landing_theme_animations',isset($_POST['enabled'])?'1':'0');
            $flash='Landing animation preference saved.';
        } elseif($action==='landing_design_settings'){
            $slug=trim((string)($_POST['theme_slug']??''));
            shahkot_theme52_save_settings($slug,$_POST);
            $flash='Theme design controls saved for '.($landingName=($slug!==''?(shahkot_reference_landing_catalog()[$slug]['name']??$slug):'theme')).'.';
        } elseif($action==='admin_theme'){
            $slug=trim((string)($_POST['theme_slug']??''));
            $catalog=function_exists('theme_catalog_index')?(array)theme_catalog_index('admin'):[];
            if(!isset($catalog[$slug]))throw new InvalidArgumentException('Unknown admin theme.');
            save_setting('admin_theme_slug',$slug);
            $flash='Admin theme activated.';
        } elseif($action==='admin_animation'){
            save_setting('admin_theme_animations',isset($_POST['enabled'])?'1':'0');
            $flash='Admin animation preference saved.';
        } elseif($action==='dashboard_theme'){
            $mode=trim((string)($_POST['dashboard_theme']??'neon-command'));
            if(!in_array($mode,['neon-command','system'],true))throw new InvalidArgumentException('Unknown dashboard theme.');
            save_setting('admin_dashboard_theme_v544',$mode);
            $flash='Dashboard theme activated.';
        }
    }
}catch(Throwable $e){$error=$e->getMessage();}

$activeLanding=shahkot_home_theme_slug();
$landingThemes=shahkot_visible_landing_catalog($activeLanding);
$activeAdmin=function_exists('active_admin_theme')?(string)active_admin_theme():(string)setting('admin_theme_slug','aurora-command');
$adminThemes=[];
try{$adminThemes=function_exists('theme_catalog_index')?(array)theme_catalog_index('admin'):[];}catch(Throwable $e){$adminThemes=[];}

page_start('Theme Studio',true);
?>
<link rel="stylesheet" href="/assets/theme-studio-5.2.0.css?v=520">
<div class="theme51-wrap">
    <section class="theme51-hero">
        <div><span>ADVANCED LANDING THEME STUDIO · v5.2</span><h2>Theme Studio</h2><p>Three production landing themes now include per-theme visual controls while the protected legacy active theme remains untouched. Section visibility/order continues to use Homepage Builder.</p></div>
        <div class="theme51-rule"><b>Safe cleanup</b><small>Active legacy theme kept</small><small>3 new landing themes added</small><small>No third-party template code/assets</small><small>Per-theme design controls</small></div>
    </section>

    <?php if($flash):?><div class="success"><?=e($flash)?></div><?php endif;?>
    <?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

    <section class="theme51-card">
        <div class="theme51-title"><div><span>PUBLIC WEBSITE</span><h3>Landing Page Themes</h3><p>Preview first, then activate. Existing businesses, homepage modules, tenant branding, news, shop, property and CMS content are reused automatically.</p></div><form method="post" class="theme51-toggle"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="landing_animation"><label><input type="checkbox" name="enabled" <?=setting_bool('landing_theme_animations',true)?'checked':''?>> Motion effects</label><button>Save</button></form></div>
        <div class="theme51-grid">
        <?php foreach($landingThemes as $slug=>$theme):$isActive=$slug===$activeLanding;$isLegacy=!empty($theme['legacy_active']);?>
            <article class="theme51-theme <?=$isActive?'is-active':''?> <?=$isLegacy?'is-legacy':''?>" style="--accent:<?=e((string)($theme['accent']??'#0f766e'))?>;--dark:<?=e((string)($theme['dark']??'#0f172a'))?>">
                <div class="theme51-preview theme51-preview-<?=e($slug)?>"><div class="fake-nav"><i></i><span></span><span></span><span></span></div><div class="fake-hero"><small></small><b></b><em></em><div></div></div><div class="fake-cards"><i></i><i></i><i></i><i></i></div></div>
                <div class="theme51-body"><div class="theme51-badges"><?=$isActive?'<span class="active-badge">ACTIVE</span>':''?><?=$isLegacy?'<span>PROTECTED LEGACY</span>':'<span>PRO v5.2</span>'?></div><h4><?=e((string)($theme['name']??$slug))?></h4><p><?=e((string)($theme['description']??''))?></p><small><?=e((string)($theme['reference']??''))?></small></div>
                <div class="theme51-actions"><a href="/?theme_preview=<?=rawurlencode($slug)?>" target="_blank">Preview ↗</a><?php if(!$isActive):?><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="landing_theme"><input type="hidden" name="theme_slug" value="<?=e($slug)?>"><button>Activate Theme</button></form><?php else:?><button class="current" disabled>Currently Active</button><?php endif;?></div>
                <?php if(shahkot_is_reference_landing_theme($slug)):$d52=shahkot_theme52_settings($slug);?>
                <details class="theme52-controls"><summary>Customize this theme</summary>
                  <form method="post" class="theme52-form"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="landing_design_settings"><input type="hidden" name="theme_slug" value="<?=e($slug)?>">
                    <label>Accent color<input type="color" name="accent" value="<?=e((string)$d52['accent'])?>"></label>
                    <label>Card radius <b><?=e((string)$d52['card_radius'])?>px</b><input type="range" min="0" max="36" name="card_radius" value="<?=e((string)$d52['card_radius'])?>"></label>
                    <label>Content width<select name="content_width"><?php foreach([1120,1200,1280,1360,1480] as $w):?><option value="<?=$w?>" <?=((int)$d52['content_width']===$w?'selected':'')?>><?=$w?> px</option><?php endforeach;?></select></label>
                    <label>Hero overlay<select name="hero_overlay"><?php foreach([40,50,60,70,80] as $o):?><option value="<?=$o?>" <?=((int)$d52['hero_overlay']===$o?'selected':'')?>><?=$o?>%</option><?php endforeach;?></select></label>
                    <label>Header<select name="header_style"><option value="solid" <?=$d52['header_style']==='solid'?'selected':''?>>Solid</option><option value="floating" <?=$d52['header_style']==='floating'?'selected':''?>>Floating</option></select></label>
                    <label>Search style<select name="search_style"><option value="boxed" <?=$d52['search_style']==='boxed'?'selected':''?>>Boxed</option><option value="pill" <?=$d52['search_style']==='pill'?'selected':''?>>Pill</option><option value="glass" <?=$d52['search_style']==='glass'?'selected':''?>>Glass</option></select></label>
                    <label>Card shadow<select name="card_shadow"><option value="soft" <?=$d52['card_shadow']==='soft'?'selected':''?>>Soft</option><option value="medium" <?=$d52['card_shadow']==='medium'?'selected':''?>>Medium</option><option value="strong" <?=$d52['card_shadow']==='strong'?'selected':''?>>Strong</option></select></label>
                    <label class="theme52-wide">Hero image URL <input type="text" name="hero_media" value="<?=e((string)$d52['hero_media'])?>" placeholder="/uploads/hero.jpg or https://..."></label>
                    <div class="theme52-wide theme52-help">Section visibility and order: <a href="/admin/homepage.php">Homepage Builder</a>. These controls only change the selected theme's visual treatment.</div>
                    <button class="theme52-save">Save Design Controls</button>
                  </form>
                </details><?php endif;?>
            </article>
        <?php endforeach;?>
        </div>
        <div class="theme51-note"><b>Legacy cleanup:</b> old inactive landing themes are no longer offered in Theme Studio. The theme that was active when this update was installed remains protected until you intentionally activate one of the new themes.</div>
    </section>

    <section class="theme51-card" id="dashboard-theme">
        <div class="theme51-title"><div><span>DASHBOARD EXPERIENCE · v5.4.4</span><h3>Dashboard Theme</h3><p>Choose the new functional dashboard presentation. Neon Command is the recommended high-density dark dashboard shown in the v5.4.4 preview.</p></div></div>
        <style>.v544-theme-picker{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.v544-theme-option{border:1px solid #dce5ef;border-radius:16px;padding:14px;background:#fff}.v544-theme-option.is-active{border-color:#7658ff;box-shadow:0 0 0 3px rgba(118,88,255,.09)}.v544-theme-preview{height:155px;border-radius:12px;overflow:hidden;position:relative;margin-bottom:12px;border:1px solid rgba(15,23,42,.12)}.v544-theme-preview.neon{background:#07111f url('/assets/previews/admin-dashboard-neon-5.4.4.webp') center/cover no-repeat}.v544-theme-preview.neon:before,.v544-theme-preview.neon:after{display:none}.v544-theme-preview.system{background:linear-gradient(135deg,#f5f8fc,#e8eef7)}.v544-theme-preview:before{content:"";position:absolute;left:0;top:0;bottom:0;width:23%;background:#081326;border-right:1px solid rgba(255,255,255,.08)}.v544-theme-preview.system:before{background:#fff;border-color:#dfe6ef}.v544-theme-preview:after{content:"";position:absolute;left:27%;right:5%;top:14%;height:23%;border-radius:8px;background:repeating-linear-gradient(90deg,rgba(125,87,255,.16) 0 15%,transparent 15% 17%),linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.02));box-shadow:0 48px 0 rgba(255,255,255,.035),0 96px 0 rgba(255,255,255,.025)}.v544-theme-preview.system:after{background:repeating-linear-gradient(90deg,#fff 0 15%,transparent 15% 17%);box-shadow:0 48px 0 #fff,0 96px 0 #fff}.v544-theme-option h4{margin:0 0 5px}.v544-theme-option p{margin:0 0 12px;color:#64748b;font-size:13px}.v544-theme-option form{display:flex;justify-content:space-between;align-items:center}.v544-theme-option button{border:0;border-radius:9px;background:#6d4bf5;color:#fff;padding:8px 12px;font-weight:800;cursor:pointer}.v544-theme-option button[disabled]{background:#d7deea;color:#718096;cursor:default}@media(max-width:760px){.v544-theme-picker{grid-template-columns:1fr}}</style>
        <?php $dashTheme=(string)setting('admin_dashboard_theme_v544','neon-command');?>
        <div class="v544-theme-picker">
          <?php foreach(['neon-command'=>['Neon Command','Exact v5.4.4 dark dashboard: live KPIs, map, listings, orders, charts and quick actions.'],'system'=>['System Palette','Same functional v5.4.4 dashboard layout using a lighter system-aligned palette.']] as $mode=>$d):$active=$dashTheme===$mode;?>
          <article class="v544-theme-option <?=$active?'is-active':''?>"><div class="v544-theme-preview <?=e($mode==='neon-command'?'neon':'system')?>"></div><h4><?=e($d[0])?></h4><p><?=e($d[1])?></p><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="dashboard_theme"><input type="hidden" name="dashboard_theme" value="<?=e($mode)?>"><span><?=$active?'<b>ACTIVE</b>':'Ready to use'?></span><button <?=$active?'disabled':''?>><?=$active?'Active':'Use Theme'?></button></form></article>
          <?php endforeach;?>
        </div>
    </section>

    <?php if($adminThemes):?><section class="theme51-card">
        <div class="theme51-title"><div><span>ADMIN PANEL</span><h3>Admin Themes</h3><p>Admin-panel themes are not part of the landing-theme cleanup and remain available.</p></div><form method="post" class="theme51-toggle"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="admin_animation"><label><input type="checkbox" name="enabled" <?=setting_bool('admin_theme_animations',true)?'checked':''?>> Admin motion</label><button>Save</button></form></div>
        <div class="theme51-admin-grid"><?php foreach($adminThemes as $slug=>$theme):?><form method="post" class="theme51-admin-item <?=$slug===$activeAdmin?'is-active':''?>"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="admin_theme"><input type="hidden" name="theme_slug" value="<?=e($slug)?>"><div><i></i><span><b><?=e((string)($theme['name']??ucwords(str_replace('-',' ',$slug))))?></b><small><?=e($slug===$activeAdmin?'Active admin theme':'Admin theme')?></small></span></div><button <?=$slug===$activeAdmin?'disabled':''?>><?=$slug===$activeAdmin?'Active':'Use'?></button></form><?php endforeach;?></div>
    </section><?php endif;?>
</div>
<?php require __DIR__.'/../app/end.php'; ?>
