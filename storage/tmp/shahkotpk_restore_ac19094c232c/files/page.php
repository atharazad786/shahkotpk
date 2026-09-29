<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/homepage.php';
require __DIR__.'/app/banners.php';
require __DIR__.'/app/ads_public.php';
require __DIR__.'/app/landing_theme_components.php';
require __DIR__.'/app/cms_public.php';

$page=null;
if(isset($_GET['cms_preview']) && current_user() && has_permission('homepage.manage',current_user()))$page=cms_page((int)$_GET['cms_preview']);
if(!$page){
    $slug=cms_clean_slug((string)($_GET['slug']??''),'');
    if($slug!=='')$page=cms_page_by_slug($slug,true);
}
if(!$page){http_response_code(404);echo 'Page not found.';exit;}

$ctx=cms_public_context($page);
$landingTheme=(string)($page['theme_slug']??'metro-portal');
if(isset($_GET['theme_preview']) && current_user() && has_permission('themes.manage',current_user())){
    $candidate=trim((string)$_GET['theme_preview']);
    if(isset(theme_catalog_index('landing')[$candidate]))$landingTheme=$candidate;
}
$page['theme_slug']=$landingTheme;
$themeCss=cms_theme_css_url($landingTheme);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?=e($page['seo_description']?:setting('seo_description','Discover Shahkot businesses, services and city information.'))?>">
<meta name="keywords" content="<?=e($page['seo_keywords']?:setting('seo_keywords','Shahkot, business directory'))?>">
<?php if(setting_bool('robots_index',true)&&($page['status']??'draft')==='published'):?><meta name="robots" content="index,follow"><?php else:?><meta name="robots" content="noindex,nofollow"><?php endif;?>
<title><?=e($page['seo_title']?:$page['title'])?></title>
<?php if(setting('favicon_url','')):?><link rel="icon" href="<?=e(setting('favicon_url',''))?>"><?php endif;?>
<link rel="stylesheet" href="/assets/themes/landing/base.css?v=240">
<link rel="stylesheet" href="/assets/cms-public-2.4.0.css?v=240">
<link rel="stylesheet" href="<?=e($themeCss)?>">
<?php if($landingTheme==='city-guide-pro'):?><link rel="stylesheet" href="/assets/city-landing-fix-3.0.1.css?v=301"><?php endif;?>
<?php if(!empty($page['custom_css'])):?><style><?=str_replace('</style','',$page['custom_css'])?></style><?php endif;?>
</head>
<body class="landing-theme landing-theme-<?=e($landingTheme)?> cms-public-page <?=($page['render_mode']??'theme')==='manual'?'cms-manual-mode':'cms-theme-mode'?>" data-landing-template="<?=e($landingTheme)?>" data-theme-animations="<?=theme_animation_enabled('landing')?'1':'0'?>">
<?php cms_render_page_body($page,$ctx); ?>

<?php if(feature_enabled('mobile_sticky_cta_enabled',true)):?>
<div class="mobile-sticky-cta">
<a class="sticky-search" href="/search.php">⌕ Search Shahkot</a>
<?php if(feature_enabled('business_registration_enabled',true)&&feature_enabled('shopkeeper_signup_enabled',true)):?><a class="sticky-business" href="/signup.php">＋ List Business</a><?php else:?><a class="sticky-business" href="/login.php">Login</a><?php endif;?>
</div>
<?php endif;?>

<script>
(function(){
 const times=document.querySelectorAll('.js-city-time');
 function updateTime(){const text=new Intl.DateTimeFormat('en-PK',{timeZone:'Asia/Karachi',weekday:'short',day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date())+' PKT';times.forEach(x=>x.textContent=text)}
 if(times.length){updateTime();setInterval(updateTime,1000)}
 const menu=document.querySelector('[data-lt-menu]'),nav=document.querySelector('[data-lt-nav]');
 if(menu&&nav)menu.addEventListener('click',()=>nav.classList.toggle('is-open'));
 const slider=document.querySelector('[data-lt-slider]');
 if(slider){
   const slides=[...slider.querySelectorAll('.lt-slide')];let index=0,timer=null;
   function show(n){if(!slides.length)return;slides[index].classList.remove('is-active');index=(n+slides.length)%slides.length;slides[index].classList.add('is-active')}
   slider.querySelector('[data-lt-next]')?.addEventListener('click',()=>show(index+1));
   slider.querySelector('[data-lt-prev]')?.addEventListener('click',()=>show(index-1));
   if(slides.length>1)timer=setInterval(()=>show(index+1),<?=max(2500,min(12000,setting_int('homepage_slider_interval_ms',5500)))?>);
 }
})();
</script>
<script src="/assets/city-guide-portal-2.9.0.js?v=290"></script>
</body>
</html>
