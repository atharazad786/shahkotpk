<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/homepage.php';
require __DIR__.'/app/banners.php';
require __DIR__.'/app/ads_public.php';
require __DIR__.'/app/landing_theme_components.php';
require __DIR__.'/app/cms_public.php';

foreach(['city_portal_upgrade_homepage','news_upgrade_homepage','blog_upgrade_homepage','live_upgrade_homepage','store_upgrade_homepage'] as $upgrade){
    runtime_call($upgrade);
}

try{$page=cms_home_page();}catch(Throwable $e){runtime_log('Unable to load homepage CMS',$e);$page=null;}
if(!$page){
    $page=['id'=>0,'title'=>setting('site_name','ShahkotPK'),'status'=>'published','render_mode'=>'theme','theme_slug'=>active_landing_theme(),'layout_json'=>cms_encode_layout(homepage_sections()),'seo_title'=>setting('seo_title',''),'seo_description'=>setting('seo_description',''),'seo_keywords'=>setting('seo_keywords',''),'custom_css'=>''];
}
if(isset($_GET['cms_preview']) && current_user() && has_permission('homepage.manage',current_user())){
    $preview=cms_page((int)$_GET['cms_preview']);if($preview)$page=$preview;
}
try{$ctx=cms_public_context($page);}catch(Throwable $e){runtime_log('Homepage context failed',$e);$ctx=['sections'=>homepage_sections(),'categories'=>[],'featured'=>[],'slides'=>[],'landingStats'=>['Active Businesses'=>'0','Categories'=>'0','City Coverage'=>'0','Featured'=>'0']];}
$landingTheme=(string)($page['theme_slug']??'metro-portal');
if(isset($_GET['theme_preview']) && current_user() && has_permission('themes.manage',current_user())){$candidate=trim((string)$_GET['theme_preview']);if(isset(theme_catalog_index('landing')[$candidate]))$landingTheme=$candidate;}
$page['theme_slug']=$landingTheme;
$themeCss=cms_theme_css_url($landingTheme);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?=e($page['seo_description']?:setting('seo_description','Discover Shahkot businesses, services and city information.'))?>">
<meta name="keywords" content="<?=e($page['seo_keywords']?:setting('seo_keywords','Shahkot, business directory'))?>">
<?php if(setting_bool('robots_index',true)&&($page['status']??'draft')==='published'):?><meta name="robots" content="index,follow"><?php else:?><meta name="robots" content="noindex,nofollow"><?php endif;?>
<title><?=e($page['seo_title']?:$page['title'])?></title>
<?php if(setting('favicon_url','')):?><link rel="icon" href="<?=e(setting('favicon_url',''))?>"><?php endif;?>
<link rel="stylesheet" href="/assets/themes/landing/base.css?v=240"><link rel="stylesheet" href="/assets/cms-public-2.4.0.css?v=240"><link rel="stylesheet" href="<?=e($themeCss)?>" data-landing-theme-css="<?=e($landingTheme)?>">
<?php if($landingTheme==='city-guide-pro'):?><link rel="stylesheet" href="/assets/city-landing-fix-3.0.1.css?v=301"><?php endif;?>
<link rel="stylesheet" href="/assets/news-portal-3.2.0.css?v=320"><link rel="stylesheet" href="/assets/live-commerce-3.4.0.css?v=341">
<?php if(!empty($page['custom_css'])):?><style><?=str_replace('</style','',$page['custom_css'])?></style><?php endif;?>
</head>
<body class="landing-theme landing-theme-<?=e($landingTheme)?> cms-public-page <?=($page['render_mode']??'theme')==='manual'?'cms-manual-mode':'cms-theme-mode'?>" data-landing-template="<?=e($landingTheme)?>" data-theme-animations="<?=theme_animation_enabled('landing')?'1':'0'?>">
<?php
try{cms_render_page_body($page,$ctx);}catch(Throwable $e){
    runtime_log('Homepage renderer failed',$e);
    echo '<main style="font-family:system-ui;padding:48px 18px;max-width:1100px;margin:auto"><div style="padding:32px;border:1px solid #dbe5df;border-radius:24px;background:#fff"><b style="color:#176b46">SHAHKOTPK</b><h1 style="font-size:clamp(32px,6vw,64px);margin:12px 0">Your digital city portal is online.</h1><p style="font-size:18px;color:#52605a">A homepage module was temporarily isolated by the recovery guard. Businesses, news, city guide and account pages remain available.</p><p><a href="/businesses.php">Browse Businesses</a> · <a href="/city-guide.php">City Guide</a> · <a href="/news.php">News</a> · <a href="/shop.php">Shop</a></p></div></main>';
}
?>
<?php if(feature_enabled('mobile_sticky_cta_enabled',true)):?><div class="mobile-sticky-cta"><a class="sticky-search" href="/search.php">⌕ Search Shahkot</a><?php if(feature_enabled('business_registration_enabled',true)&&feature_enabled('shopkeeper_signup_enabled',true)):?><a class="sticky-business" href="/signup.php">＋ List Business</a><?php else:?><a class="sticky-business" href="/login.php">Login</a><?php endif;?></div><?php endif;?>
<script>(function(){const times=document.querySelectorAll('.js-city-time');function updateTime(){const text=new Intl.DateTimeFormat('en-PK',{timeZone:'Asia/Karachi',weekday:'short',day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date())+' PKT';times.forEach(x=>x.textContent=text)}if(times.length){updateTime();setInterval(updateTime,1000)}const menu=document.querySelector('[data-lt-menu]'),nav=document.querySelector('[data-lt-nav]');if(menu&&nav)menu.addEventListener('click',()=>nav.classList.toggle('is-open'));const slider=document.querySelector('[data-lt-slider]');if(slider){const slides=[...slider.querySelectorAll('.lt-slide')];let index=0;function show(n){if(!slides.length)return;slides[index].classList.remove('is-active');index=(n+slides.length)%slides.length;slides[index].classList.add('is-active')}slider.querySelector('[data-lt-next]')?.addEventListener('click',()=>show(index+1));slider.querySelector('[data-lt-prev]')?.addEventListener('click',()=>show(index-1));if(slides.length>1)setInterval(()=>show(index+1),<?=max(2500,min(12000,setting_int('homepage_slider_interval_ms',5500)))?>);}})();</script>
<script src="/assets/city-guide-portal-2.9.0.js?v=290"></script>
<?php try{if(function_exists('google_maps_ready')&&google_maps_ready()&&setting_bool('google_maps_home_enabled',true)&&function_exists('render_google_maps_assets'))render_google_maps_assets();}catch(Throwable $e){runtime_log('Google Maps assets failed',$e);}?>
<?php try{if(function_exists('engagement_render'))engagement_render();}catch(Throwable $e){runtime_log('Engagement footer failed',$e);}?>
</body></html>
