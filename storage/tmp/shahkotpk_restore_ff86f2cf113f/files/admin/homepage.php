<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
require_once __DIR__.'/../app/landing_reference_themes_v51.php';
require_once __DIR__.'/../app/homepage_builder_v53.php';
$me=require_permission('homepage.manage');
$catalog=shahkot_reference_landing_catalog();
$theme=(string)($_REQUEST['theme']??shahkot_home_theme_slug());
if(!isset($catalog[$theme]))$theme=array_key_first($catalog)?:'city-listing-motion';
$scope=hb53_scope();$flash='';$error='';

function hb53_admin_parse_layout(string $raw): array {
    $d=json_decode($raw,true);if(!is_array($d))throw new InvalidArgumentException('Builder layout payload is invalid.');
    $out=[];$order=10;foreach($d as $s){if(!is_array($s))continue;$out[]=hb53_normalize_section($s,$order);$order+=10;}if(!$out)throw new InvalidArgumentException('Homepage must contain at least one section.');return $out;
}

try{
    if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
        csrf_check();$action=(string)($_POST['action']??'save_draft');$userId=(int)($me['id']??0);$inherit=!$scope['is_global']&&!empty($_POST['inherit_global']);
        if(in_array($action,['save_draft','publish'],true)){
            $layout=hb53_admin_parse_layout((string)($_POST['layout_json']??''));
            $settings=['device_preview'=>(string)($_POST['device_preview']??'desktop'),'updated_from'=>'homepage_builder_2'];
            if($action==='publish'){
                hb53_publish($theme,$scope['scope_key'],$scope['tenant_id'],$inherit,$layout,$settings,$userId);
                $flash=$inherit?'Tenant set to inherit the global published theme layout.':'Homepage layout published successfully.';
            }else{
                hb53_upsert_profile($theme,$scope['scope_key'],$scope['tenant_id'],$inherit,$layout,$settings,$userId);
                $flash='Draft saved.';
            }
        }elseif($action==='revert'){
            $p=hb53_profile($theme,$scope['scope_key']);
            if(!$p||empty($p['published_layout_json']))throw new RuntimeException('No published local layout is available to revert to.');
            $layout=hb53_decode_layout((string)$p['published_layout_json']);
            hb53_upsert_profile($theme,$scope['scope_key'],$scope['tenant_id'],!empty($p['inherit_global']),$layout,json_decode((string)($p['published_settings_json']??'[]'),true)?:[],$userId);
            $flash='Draft reverted to the last published layout.';
        }elseif($action==='reset_recommended'){
            $layout=hb53_recommended_layout($theme);
            hb53_upsert_profile($theme,$scope['scope_key'],$scope['tenant_id'],false,$layout,['reset'=>'recommended'],$userId);
            $flash='Recommended '.$catalog[$theme]['name'].' layout loaded into Draft. Publish when ready.';
        }elseif($action==='reset_profile'){
            if($scope['is_global']){
                $layout=hb53_recommended_layout($theme);hb53_publish($theme,'global',null,false,$layout,['reset'=>'recommended'],$userId);$flash='Global theme profile reset to the recommended layout.';
            }else{
                $p=hb53_profile($theme,$scope['scope_key']);if($p)db()->prepare('DELETE FROM homepage_builder_profiles WHERE id=?')->execute([(int)$p['id']]);$flash='Tenant customization removed. This tenant now inherits the global layout.';
            }
        }elseif($action==='reset_all'){
            if($scope['is_global']){
                foreach(array_keys($catalog) as $slug){$layout=hb53_recommended_layout($slug);hb53_publish($slug,'global',null,false,$layout,['reset'=>'recommended'],$userId);} $flash='All three global theme layouts reset to recommended defaults.';
            }else{
                db()->prepare('DELETE FROM homepage_builder_profiles WHERE scope_key=?')->execute([$scope['scope_key']]);$flash='All tenant-specific homepage layouts removed. Global layouts are inherited again.';
            }
        }
        if(function_exists('tenant_audit'))tenant_audit('homepage_builder.action','homepage_builder_profile',null,'Homepage Builder action',['action'=>$action,'theme'=>$theme,'scope'=>$scope['scope_key']]);
        if(!empty($_POST['ajax'])){header('Content-Type: application/json');echo json_encode(['ok'=>true,'message'=>$flash,'preview'=>'/?theme_preview='.rawurlencode($theme).'&hb53_preview=1&t='.time()]);exit;}
    }
}catch(Throwable $e){$error=$e->getMessage();if(!empty($_POST['ajax'])){header('Content-Type: application/json');http_response_code(422);echo json_encode(['ok'=>false,'message'=>$error]);exit;}}

$local=hb53_profile($theme,$scope['scope_key']);
$global=hb53_profile($theme,'global');
if($local&&!empty($local['draft_layout_json']))$layout=hb53_decode_layout((string)$local['draft_layout_json']);
elseif(!$scope['is_global']&&$global&&!empty($global['published_layout_json']))$layout=hb53_decode_layout((string)$global['published_layout_json']);
elseif($global&&!empty($global['draft_layout_json']))$layout=hb53_decode_layout((string)$global['draft_layout_json']);
else $layout=hb53_recommended_layout($theme);
$inherit=!$scope['is_global']&&($local? !empty($local['inherit_global']):true);
$registry=hb53_section_registry();
page_start('Homepage Builder 2.0',true);
?>
<link rel="stylesheet" href="/assets/homepage-builder-5.3.0-admin.css?v=530">
<div class="hb53-admin" data-theme="<?=e($theme)?>">
  <section class="hb53-builder-hero">
    <div><span>HOMEPAGE BUILDER 2.0 · v5.3.0</span><h2>Build every theme independently.</h2><p>Drag sections, change content and layout, preview on desktop/tablet/mobile, save drafts and publish without replacing your live content.</p></div>
    <div class="hb53-scope-card"><small>Current scope</small><b><?=e($scope['label'])?></b><span><?=e($catalog[$theme]['name']??$theme)?></span></div>
  </section>

  <?php if($flash):?><div class="hb53-alert success"><?=e($flash)?></div><?php endif;?>
  <?php if($error):?><div class="hb53-alert error"><?=e($error)?></div><?php endif;?>

  <section class="hb53-toolbar-card">
    <div class="hb53-theme-tabs">
      <?php foreach($catalog as $slug=>$def):?><a class="<?=$slug===$theme?'active':''?>" href="?theme=<?=urlencode($slug)?>"><i></i><span><b><?=e($def['name'])?></b><small><?=e($def['reference'])?></small></span></a><?php endforeach;?>
    </div>
    <?php if(!$scope['is_global']):?><label class="hb53-inherit"><input id="inheritGlobal" type="checkbox" <?=$inherit?'checked':''?>> <span><b>Inherit Global Layout</b><small>When enabled, this city/franchise follows the master published layout. You can still prepare a tenant draft before switching to Custom.</small></span></label><?php endif;?>
  </section>

  <div class="hb53-workspace">
    <section class="hb53-editor-pane">
      <div class="hb53-editor-head"><div><span>PAGE STRUCTURE</span><h3>Drag & configure sections</h3></div><button type="button" class="hb53-secondary" id="addSectionToggle">＋ Add Section</button></div>
      <div id="addSectionPanel" class="hb53-add-panel" hidden>
        <?php foreach($registry as $type=>$def):?><button type="button" data-add-type="<?=e($type)?>" data-label="<?=e($def['label'])?>" data-icon="<?=e($def['icon'])?>"><?=e($def['icon'])?> <?=e($def['label'])?></button><?php endforeach;?>
      </div>
      <div id="hb53Sections" class="hb53-sections">
        <?php foreach($layout as $i=>$s):$b=$s['_builder']??hb53_default_builder_config((string)$s['type']);$def=$registry[$s['type']]??['label'=>ucwords(str_replace('_',' ',$s['type'])),'icon'=>'◫','data'=>false];?>
        <article class="hb53-section-card" draggable="true" data-type="<?=e($s['type'])?>" data-id="<?=e($s['id'])?>">
          <header><button type="button" class="hb53-drag" title="Drag to reorder">⋮⋮</button><span class="hb53-section-icon"><?=e($def['icon'])?></span><div><b><?=e($def['label'])?></b><small><?=e($s['type'])?></small></div><label class="hb53-switch"><input class="f-enabled" type="checkbox" <?=!empty($s['enabled'])?'checked':''?>><span></span></label><button type="button" class="hb53-expand">⌄</button><button type="button" class="hb53-remove" title="Remove section">×</button></header>
          <div class="hb53-section-body">
            <div class="hb53-form-grid">
              <label>Section title<input class="f-title" value="<?=e($s['title'])?>"></label>
              <label>Subtitle<input class="f-subtitle" value="<?=e($s['subtitle'])?>"></label>
              <label class="wide">Description<textarea class="f-content" rows="2"><?=e($s['content'])?></textarea></label>
              <label>Layout<select class="f-layout"><option value="default" <?=$b['layout_mode']==='default'?'selected':''?>>Default</option><option value="grid" <?=$b['layout_mode']==='grid'?'selected':''?>>Grid</option><option value="slider" <?=$b['layout_mode']==='slider'?'selected':''?>>Slider</option><option value="list" <?=$b['layout_mode']==='list'?'selected':''?>>List</option></select></label>
              <label>Animation<select class="f-animation"><option value="none" <?=$b['animation']==='none'?'selected':''?>>None</option><option value="fade-up" <?=$b['animation']==='fade-up'?'selected':''?>>Fade Up</option><option value="fade-in" <?=$b['animation']==='fade-in'?'selected':''?>>Fade In</option><option value="zoom-in" <?=$b['animation']==='zoom-in'?'selected':''?>>Zoom In</option><option value="slide-left" <?=$b['animation']==='slide-left'?'selected':''?>>Slide Left</option><option value="slide-right" <?=$b['animation']==='slide-right'?'selected':''?>>Slide Right</option></select></label>
              <label>Spacing<select class="f-spacing"><option value="compact" <?=$b['spacing']==='compact'?'selected':''?>>Compact</option><option value="normal" <?=$b['spacing']==='normal'?'selected':''?>>Normal</option><option value="relaxed" <?=$b['spacing']==='relaxed'?'selected':''?>>Relaxed</option></select></label>
              <label>Background<input class="f-background" value="<?=e($b['background'])?>" placeholder="transparent or #ffffff"></label>
              <label>Desktop columns<input class="f-desktop" type="number" min="1" max="6" value="<?=e($b['desktop_cols'])?>"></label>
              <label>Tablet columns<input class="f-tablet" type="number" min="1" max="4" value="<?=e($b['tablet_cols'])?>"></label>
              <label>Mobile columns<input class="f-mobile" type="number" min="1" max="2" value="<?=e($b['mobile_cols'])?>"></label>
              <?php if(!empty($def['data'])):?><label>Data source<select class="f-source"><option value="latest" <?=$b['data_source']==='latest'?'selected':''?>>Latest</option><option value="featured" <?=$b['data_source']==='featured'?'selected':''?>>Featured</option><option value="popular" <?=$b['data_source']==='popular'?'selected':''?>>Popular</option><option value="random" <?=$b['data_source']==='random'?'selected':''?>>Random</option><option value="manual" <?=$b['data_source']==='manual'?'selected':''?>>Manual IDs</option><option value="category" <?=$b['data_source']==='category'?'selected':''?>>Category filter</option><option value="city" <?=$b['data_source']==='city'?'selected':''?>>City filter</option></select></label>
              <label>Cards / items<input class="f-count" type="number" min="1" max="24" value="<?=e($b['card_count'])?>"></label>
              <label class="wide">Filter value / Manual IDs<input class="f-filter" value="<?=e($b['data_source']==='manual'?$b['manual_ids']:$b['filter_value'])?>" placeholder="e.g. 12,18,24 or category/city"></label><?php else:?><input class="f-source" type="hidden" value="latest"><input class="f-count" type="hidden" value="6"><input class="f-filter" type="hidden" value=""><?php endif;?>
              <?php if($s['type']==='custom_html'):?><label class="wide">Custom HTML / formatted text<textarea class="f-html" rows="6"><?=e($s['custom_html']??'')?></textarea><small>Safe formatting tags are allowed; scripts and event handlers are removed.</small></label><?php else:?><input class="f-html" type="hidden" value="<?=e($s['custom_html']??'')?>"><?php endif;?>
              <label class="wide">Items <small>(one per line: Title|Link|Text|Image)</small><textarea class="f-items" rows="3"><?=e($s['items'])?></textarea></label>
            </div>
          </div>
        </article>
        <?php endforeach;?>
      </div>
    </section>

    <aside class="hb53-preview-pane">
      <div class="hb53-preview-head"><div><span>LIVE DRAFT PREVIEW</span><b id="previewStatus">Ready</b></div><div class="hb53-device-buttons"><button type="button" data-device="desktop" class="active">▱</button><button type="button" data-device="tablet">▯</button><button type="button" data-device="mobile">▯</button></div></div>
      <div id="previewFrameWrap" class="hb53-frame-wrap desktop"><iframe id="hb53Preview" title="Homepage preview" src="/?theme_preview=<?=urlencode($theme)?>&hb53_preview=1"></iframe></div>
    </aside>
  </div>

  <section class="hb53-publishbar">
    <div><b>Draft / Publish workflow</b><span>Draft changes are private. Publish only when the preview is ready.</span></div>
    <div class="hb53-actions">
      <button type="button" class="hb53-secondary" data-action="reset_recommended">↺ Recommended Layout</button>
      <button type="button" class="hb53-secondary" data-action="revert">↶ Revert Draft</button>
      <button type="button" class="hb53-secondary danger" data-action="reset_profile">Reset Theme</button>
      <button type="button" class="hb53-secondary danger" data-action="reset_all">Reset All Themes</button>
      <button type="button" class="hb53-primary ghost" data-action="save_draft">Save Draft</button>
      <button type="button" class="hb53-primary" data-action="publish">Publish Homepage</button>
    </div>
  </section>
</div>
<form id="hb53ActionForm" method="post" hidden><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="theme" value="<?=e($theme)?>"><input type="hidden" name="action"><input type="hidden" name="layout_json"><input type="hidden" name="inherit_global"><input type="hidden" name="device_preview" value="desktop"></form>
<script>window.HB53_REGISTRY=<?=json_encode($registry,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;window.HB53_THEME=<?=json_encode($theme)?>;</script>
<script src="/assets/homepage-builder-5.3.0-admin.js?v=530"></script>
</div></main></body></html>
