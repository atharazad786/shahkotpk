<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/homepage.php';
require __DIR__.'/app/banners.php';
require __DIR__.'/app/ads_public.php';
require __DIR__.'/app/landing_theme_components.php';
require __DIR__.'/app/landing_reference_themes_v51.php';
require __DIR__.'/app/dummy_data_v52.php';
require __DIR__.'/app/homepage_builder_v53.php';
require __DIR__.'/app/banner_pack_v54.php';
require __DIR__.'/app/location_discovery_v54.php';
require __DIR__.'/app/cms_public.php';

foreach(['city_portal_upgrade_homepage','news_upgrade_homepage','blog_upgrade_homepage','live_upgrade_homepage','store_upgrade_homepage','growth_upgrade_homepage'] as $upgrade){
    runtime_call($upgrade);
}
runtime_call('dummy_v52_maybe_autoload');
runtime_call('hb53_maybe_seed_profiles');

try{$page=cms_home_page();}catch(Throwable $e){runtime_log('Unable to load homepage CMS',$e);$page=null;}
if(!$page){
    $page=['id'=>0,'title'=>setting('site_name','ShahkotPK'),'status'=>'published','render_mode'=>'theme','theme_slug'=>active_landing_theme(),'layout_json'=>cms_encode_layout(homepage_sections()),'seo_title'=>setting('seo_title',''),'seo_description'=>setting('seo_description',''),'seo_keywords'=>setting('seo_keywords',''),'custom_css'=>''];
}
if(isset($_GET['cms_preview']) && current_user() && has_permission('homepage.manage',current_user())){
    $preview=cms_page((int)$_GET['cms_preview']);if($preview)$page=$preview;
}
$landingTheme=(string)($page['theme_slug']??'metro-portal');
if(isset($_GET['theme_preview']) && current_user() && (has_permission('themes.manage',current_user())||has_permission('homepage.manage',current_user()))){
    $candidate=trim((string)$_GET['theme_preview']);
    $previewCatalog=shahkot_visible_landing_catalog($landingTheme);
    if(isset($previewCatalog[$candidate]))$landingTheme=$candidate;
}
$hb53DraftPreview=isset($_GET['hb53_preview']) && current_user() && has_permission('homepage.manage',current_user());
if(function_exists('hb53_apply_runtime_page')&&shahkot_is_reference_landing_theme($landingTheme))$page=hb53_apply_runtime_page($page,$landingTheme,$hb53DraftPreview);
$page['theme_slug']=$landingTheme;
try{$ctx=cms_public_context($page);if(function_exists('hb53_transform_context')&&shahkot_is_reference_landing_theme($landingTheme))$ctx=hb53_transform_context($ctx,$page);}catch(Throwable $e){runtime_log('Homepage context failed',$e);$ctx=['sections'=>homepage_sections(),'categories'=>[],'featured'=>[],'slides'=>[],'landingStats'=>['Active Businesses'=>'0','Categories'=>'0','City Coverage'=>'0','Featured'=>'0']];}
if(function_exists('v54_merge_slider_banners'))$ctx['slides']=v54_merge_slider_banners((array)($ctx['slides']??[]));
$themeCss=shahkot_reference_theme_css_url($landingTheme)??cms_theme_css_url($landingTheme);
$theme52=shahkot_is_reference_landing_theme($landingTheme)?shahkot_theme52_settings($landingTheme):[];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?=e($page['seo_description']?:setting('seo_description','Discover Shahkot businesses, services and city information.'))?>">
<meta name="keywords" content="<?=e($page['seo_keywords']?:setting('seo_keywords','Shahkot, business directory'))?>">
<?php if(setting_bool('robots_index',true)&&($page['status']??'draft')==='published'):?><meta name="robots" content="index,follow"><?php else:?><meta name="robots" content="noindex,nofollow"><?php endif;?>
<title><?=e($page['seo_title']?:$page['title'])?></title>
<?php if(setting('favicon_url','')):?><link rel="icon" href="<?=e(setting('favicon_url',''))?>"><?php endif;?>
<link rel="stylesheet" href="/assets/themes/landing/base.css?v=240"><link rel="stylesheet" href="/assets/cms-public-2.4.0.css?v=240">
<?php if(shahkot_is_reference_landing_theme($landingTheme)):?><link rel="stylesheet" href="/assets/reference-theme-structure-5.1.1.css?v=511"><?php endif;?>
<?php if($landingTheme==='city-guide-pro'):?><link rel="stylesheet" href="/assets/city-landing-fix-3.0.1.css?v=301"><?php endif;?>
<link rel="stylesheet" href="<?=e($themeCss)?>" data-landing-theme-css="<?=e($landingTheme)?>">
<link rel="stylesheet" href="/assets/news-portal-3.2.0.css?v=320"><link rel="stylesheet" href="/assets/live-commerce-3.4.0.css?v=343"><link rel="stylesheet" href="/assets/growth-suite-3.5.0.css?v=350"><link rel="stylesheet" href="/assets/location-discovery-5.4.0.css?v=540">
<?php if(shahkot_is_reference_landing_theme($landingTheme)):?><link rel="stylesheet" href="/assets/landing-professional-5.2.0.css?v=520"><link rel="stylesheet" href="/assets/homepage-builder-5.3.0-public.css?v=530"><style><?=shahkot_theme52_inline_css($landingTheme)?><?=function_exists('hb53_public_css')?hb53_public_css($page):''?></style><?php endif;?>
<?php if(!empty($page['custom_css'])):?><style><?=str_replace('</style','',$page['custom_css'])?></style><?php endif;?><link rel="stylesheet" href="/assets/public-header-4.3.1.css?v=431"><link rel="stylesheet" href="/assets/front-v582/front-premium-5.8.2.css?v=582"><link rel="stylesheet" href="/assets/front-v582/front-stability-5.8.3.css?v=583">
<?php if(function_exists('growth_enabled')&&growth_enabled('pwa_enabled',true)):?><link rel="manifest" href="/tenant-manifest.php"><meta name="theme-color" content="<?=e((string)(function_exists('tenant_brand')?tenant_brand('primary_color','#0f766e'):'#0f766e'))?>"><?php endif;?>
</head>
<body class="landing-theme landing-theme-<?=e($landingTheme)?> cms-public-page <?=($page['render_mode']??'theme')==='manual'?'cms-manual-mode':'cms-theme-mode'?>" data-landing-template="<?=e($landingTheme)?>" data-theme-animations="<?=theme_animation_enabled('landing')?'1':'0'?>" <?=shahkot_is_reference_landing_theme($landingTheme)?'data-sk52-header="'.e((string)($theme52['header_style']??'solid')).'" data-sk52-search="'.e((string)($theme52['search_style']??'boxed')).'"':''?>>
<?php
try{if(($page['render_mode']??'theme')==='theme'&&shahkot_is_reference_landing_theme($landingTheme)&&function_exists('hb53_render_page_body'))hb53_render_page_body($page,$ctx,$landingTheme);else cms_render_page_body($page,$ctx);}catch(Throwable $e){
    runtime_log('Homepage renderer failed',$e);
    echo '<main style="font-family:system-ui;padding:48px 18px;max-width:1100px;margin:auto"><div style="padding:32px;border:1px solid #dbe5df;border-radius:24px;background:#fff"><b style="color:#176b46">SHAHKOTPK</b><h1 style="font-size:clamp(32px,6vw,64px);margin:12px 0">Your digital city portal is online.</h1><p style="font-size:18px;color:#52605a">A homepage module was temporarily isolated by the recovery guard. Businesses, news, city guide and account pages remain available.</p><p><a href="/businesses.php">Browse Businesses</a> · <a href="/city-guide.php">City Guide</a> · <a href="/news.php">News</a> · <a href="/shop.php">Shop</a></p></div></main>';
}
?>
<?php
// v5.4.5 reliability fallback: if the published Homepage Builder layout does not
// contain a Live Broadcast block, render the live widget once below the page body.
try{
    $layoutRaw=(string)($page['layout_json']??'');
    $hasLiveSection=str_contains($layoutRaw,'"type":"live_widget"')||str_contains($layoutRaw,'"type": "live_widget"');
    // v5.8.3: Homepage Builder now owns the fallback and places it before the footer.
    // Keep this legacy path only for renderers that do not use the Builder flow guard.
    if(empty($GLOBALS['hb53_v583_flow_guard'])&&!$hasLiveSection&&function_exists('live_render_widget')&&setting_bool('live_portal_enabled',true)&&setting_bool('live_home_enabled',true))live_render_widget([]);
}catch(Throwable $e){runtime_log('v5.4.5 homepage live fallback failed',$e);}
?>
<?php if(feature_enabled('mobile_sticky_cta_enabled',true)):?><div class="mobile-sticky-cta"><a class="sticky-search" href="/search.php">⌕ Search Shahkot</a><?php if(feature_enabled('business_registration_enabled',true)&&feature_enabled('shopkeeper_signup_enabled',true)):?><a class="sticky-business" href="/signup.php">＋ List Business</a><?php else:?><a class="sticky-business" href="/login.php">Login</a><?php endif;?></div><?php endif;?>
<script>(function(){const times=document.querySelectorAll('.js-city-time');function updateTime(){const text=new Intl.DateTimeFormat('en-PK',{timeZone:'Asia/Karachi',weekday:'short',day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date())+' PKT';times.forEach(x=>x.textContent=text)}if(times.length){updateTime();setInterval(updateTime,1000)}const slider=document.querySelector('[data-lt-slider]');if(slider){const slides=[...slider.querySelectorAll('.lt-slide')];let index=0;function show(n){if(!slides.length)return;slides[index].classList.remove('is-active');index=(n+slides.length)%slides.length;slides[index].classList.add('is-active')}slider.querySelector('[data-lt-next]')?.addEventListener('click',()=>show(index+1));slider.querySelector('[data-lt-prev]')?.addEventListener('click',()=>show(index-1));if(slides.length>1)setInterval(()=>show(index+1),<?=max(2500,min(12000,setting_int('homepage_slider_interval_ms',5500)))?>);}})();</script>
<script src="/assets/city-guide-portal-2.9.0.js?v=290"></script>
<?php try{if(function_exists('google_maps_ready')&&google_maps_ready()&&setting_bool('google_maps_home_enabled',true)&&function_exists('render_google_maps_assets'))render_google_maps_assets();}catch(Throwable $e){runtime_log('Google Maps assets failed',$e);}?>
<?php if(function_exists('v54_location_enabled')&&v54_location_enabled()):?><script>window.ShahkotV54Location=<?=json_encode(v54_location_config(),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?>;</script><script src="/assets/location-discovery-5.4.0.js?v=540"></script><?php endif;?>
<?php try{if(function_exists('engagement_render'))engagement_render();}catch(Throwable $e){runtime_log('Engagement footer failed',$e);}?>
<?php if(function_exists('growth_enabled')&&growth_enabled('pwa_enabled',true)):?><script>if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/service-worker.js').catch(()=>{}));}</script><?php endif;?>
<?php if(shahkot_is_reference_landing_theme($landingTheme)):?><script src="/assets/landing-reference-themes-5.2.0.js?v=520"></script><script src="/assets/homepage-builder-5.3.0-public.js?v=530"></script><?php endif;?>
<script src="/assets/public-header-4.3.1.js?v=431"></script><script src="/assets/front-v582/front-premium-5.8.2.js?v=582"></script><script src="/assets/front-v582/front-stability-5.8.3.js?v=583"></script>
</body></html>
