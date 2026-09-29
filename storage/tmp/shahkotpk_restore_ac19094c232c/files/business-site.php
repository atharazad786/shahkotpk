<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/app/business_microsite_v1130.php';
$mediaFile=__DIR__.'/app/business_media_v1279.php';if(is_file($mediaFile))require_once $mediaFile;
$commerceFile=__DIR__.'/app/commerce_v1230.php'; if(is_file($commerceFile)) require_once $commerceFile;
$sourceLifeFile=__DIR__.'/app/business_source_lifecycle_v1273.php';if(is_file($sourceLifeFile))require_once $sourceLifeFile;
$sourcePublishFile=__DIR__.'/app/business_source_publish_v1277.php';if(is_file($sourcePublishFile))require_once $sourcePublishFile;
$uf=__DIR__.'/app/unified_experience_v1100.php'; if(is_file($uf)) require_once $uf;

$id=(int)($_GET['id']??0); $slug=trim((string)($_GET['slug']??''));
$b=sk1130_business($id,$slug); $settings=sk1130_settings();
if(!$b || empty($settings['enabled'])){http_response_code(404);exit('Business website not available.');}
$bid=(int)$b['id']; $profile=sk1130_profile($bid); $pageMedia=function_exists('bm1279_media')?bm1279_media($bid):['logo_path'=>'','banner_json'=>[],'gallery_json'=>[],'template_key'=>'premium']; $builder=sk1170_builder($bid); if(empty($profile['enabled'])){http_response_code(404);exit('Business website not available.');}
$name=(string)sk1130_business_value($b,['name'],'Business'); $cat=sk1130_category($b); $copy=sk1130_copy($b,$profile); $theme=sk1130_theme($b,$profile); $palette=sk1130_palette($b,$theme);
$images=sk1130_images($b); foreach((array)($pageMedia['gallery_json']??[]) as $localMedia){$localMedia=trim((string)$localMedia);if($localMedia!==''&&!in_array($localMedia,$images,true))$images[]=$localMedia;} $googleLivePhotos=function_exists('bs1277_live_photo_gallery')?bs1277_live_photo_gallery($bid,4):[]; foreach($googleLivePhotos as $gp){$u=(string)($gp['url']??'');if($u!==''&&!in_array($u,$images,true))$images[]=$u;} $images=array_slice($images,0,12); $logo=trim((string)($pageMedia['logo_path']??''));if($logo==='')$logo=(string)sk1130_business_value($b,['logo_url','logo','image_url','image'],'');$pageTemplate=(string)($pageMedia['template_key']??'premium');if(!in_array($pageTemplate,['premium','cinematic','clean','storefront'],true))$pageTemplate='premium';
$services=sk1130_services($b); $products=!empty($settings['show_products'])?sk1130_products($bid,12):[]; $deals=!empty($settings['show_deals'])?sk1130_deals($bid,8):[]; $reviews=!empty($settings['show_reviews'])?sk1130_reviews($bid,12):[]; $rating=sk1130_rating($reviews);
$address=trim((string)sk1130_business_value($b,['address','area'],'')); $phone=trim((string)sk1130_business_value($b,['phone'],'')); $wa=trim((string)sk1130_business_value($b,['whatsapp','phone'],'')); $email=trim((string)sk1130_business_value($b,['email'],'')); $website=trim((string)sk1130_business_value($b,['website'],'')); $hours=trim((string)sk1130_business_value($b,['opening_hours','hours','working_hours','hours_text'],''));
$lat=(string)sk1130_business_value($b,['latitude','lat'],''); $lng=(string)sk1130_business_value($b,['longitude','lng'],'');
$verified=!empty($b['verified'])||!empty($b['is_verified'])||strtolower((string)($b['verification_status']??''))==='verified'; $featured=!empty($b['featured'])||!empty($b['is_featured']); $sourceTag=function_exists('bs1273_source_tag')?bs1273_source_tag($bid):['linked'=>false,'imported'=>false];
$siteId=function_exists('sk1100_site_identity')?sk1100_site_identity():['name'=>'ShahkotPK','logo'=>''];
$mapQuery=($lat!==''&&$lng!=='')?$lat.','.$lng:$address; $directions=(!empty($builder['show_directions'])&&$mapQuery!=='')?'https://www.google.com/maps/search/?api=1&query='.rawurlencode($mapQuery):''; $mapEmbed=$mapQuery!==''?'https://www.google.com/maps?q='.rawurlencode($mapQuery).'&z=16&output=embed':'';
$tel=preg_replace('/[^0-9+]/','',$phone); if(empty($builder['show_phone']))$tel=''; $wan=preg_replace('/[^0-9]/','',$wa); if(str_starts_with($wan,'0'))$wan='92'.substr($wan,1); $waUrl=(!empty($builder['show_whatsapp'])&&$wan!=='')?'https://wa.me/'.$wan.'?text='.rawurlencode('Hello, I found '.$name.' on ShahkotPK.') : '';
$bookUrl=!empty($builder['show_booking'])?('/assistant-v1040.php?q='.rawurlencode('Book or contact '.$name)):''; $shareUrl=(isset($_SERVER['HTTP_HOST'])?'https://'.$_SERVER['HTTP_HOST']:'').sk1130_url($b);
$desc=(string)($copy['meta_description']??''); $metaTitle=(string)($copy['meta_title']??($name.' — ShahkotPK')); $faq=(array)($copy['faq']??[]); $why=(array)($copy['why_choose_points']??[]);
function b1160_pickimg(array $r): string {return (string)sk1130_pick($r,['image_url','image','thumbnail_url','thumbnail','cover_url','featured_image','card_image'],'');}
function b1160_title(array $r): string {return (string)sk1130_pick($r,['title','name'],'Item');}
function b1160_money($v): string {if($v===''||$v===null)return '';return 'PKR '.number_format((float)$v,0);}
function b1160_cleanurl(string $u): string {return trim($u);}

// Build a genuine visual hero slider from the business gallery first, then product/deal media.
$slideImages=[]; $addSlideImg=function($u)use(&$slideImages){$u=trim((string)$u);if($u!==''&&!in_array($u,$slideImages,true))$slideImages[]=$u;};
foreach((array)($pageMedia['banner_json']??[]) as $banner)$addSlideImg($banner); foreach($images as $im)$addSlideImg($im); foreach($products as $p)$addSlideImg(b1160_pickimg($p)); foreach($deals as $d)$addSlideImg(b1160_pickimg($d));
if(!$slideImages && $logo)$addSlideImg($logo);
$slides=[];
$customHeroTitle=trim((string)($builder['hero_title']??''));$customHeroSubtitle=trim((string)($builder['hero_subtitle']??''));$customHeroLabel=trim((string)($builder['hero_cta_label']??''));$customHeroUrl=trim((string)($builder['hero_cta_url']??''));
$slideCopy=[
 ['eyebrow'=>$cat,'title'=>$customHeroTitle!==''?$customHeroTitle:(string)($copy['hero_headline']??$name),'text'=>$customHeroSubtitle!==''?$customHeroSubtitle:(string)($copy['hero_subtitle']??$desc),'cta'=>$customHeroLabel!==''?$customHeroLabel:'Explore business','href'=>$customHeroUrl!==''?$customHeroUrl:'#about'],
 ['eyebrow'=>'SERVICES','title'=>'Services built around your needs','text'=>!empty($services)?('Explore '.implode(', ',array_slice($services,0,4)).'.'):'Explore the services and information available from this business.','cta'=>'View services','href'=>'#services'],
 ['eyebrow'=>'DISCOVER','title'=>'See what this business offers','text'=>$products?'Browse featured products and local offers from this business.':($deals?'See current local offers and promotions.':'Browse photos, reviews and contact options.'),'cta'=>$products?'View products':($deals?'View offers':'View gallery'),'href'=>$products?'#products':($deals?'#offers':'#gallery')],
 ['eyebrow'=>'VISIT & CONNECT','title'=>'Everything you need in one place','text'=>$address!==''?('Find '.$name.' at '.$address.' and contact them directly.'):'Call, message, book or get directions directly from this page.','cta'=>'Contact now','href'=>'#contact'],
];
$slideCount=max(1,min(5,max(count($slideImages),3)));
for($i=0;$i<$slideCount;$i++){$c=$slideCopy[$i%count($slideCopy)];$slides[]=$c+['image'=>$slideImages[$i%max(1,count($slideImages))]??''];}
$social=[]; foreach(['facebook'=>'Facebook','instagram'=>'Instagram','youtube'=>'YouTube','tiktok'=>'TikTok','twitter'=>'X','x_url'=>'X'] as $k=>$label){$v=trim((string)sk1130_business_value($b,[$k,$k.'_url'],''));if($v!=='')$social[$label]=$v;}
$ld=['@context'=>'https://schema.org','@type'=>'LocalBusiness','name'=>$name,'url'=>$shareUrl];if($address)$ld['address']=$address;if($phone)$ld['telephone']=$phone;if($email)$ld['email']=$email;if(!empty($slideImages[0]))$ld['image']=$slideImages[0];if((int)$rating['count']>0)$ld['aggregateRating']=['@type'=>'AggregateRating','ratingValue'=>$rating['avg'],'reviewCount'=>$rating['count']];
sk1130_event($bid,'page_view',['theme'=>$theme,'design'=>'12.8.2-professional-map-contact']);
$cartCount=function_exists('sk1230_cart_count')?sk1230_cart_count():0;
?>
<!doctype html>
<html lang="en" data-biz-theme="<?=sk1130_h($theme)?>" data-site-template="<?=sk1130_h($pageTemplate)?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?=sk1130_h($metaTitle)?></title><meta name="description" content="<?=sk1130_h($desc)?>">
<meta property="og:title" content="<?=sk1130_h($name)?>"><meta property="og:description" content="<?=sk1130_h($desc)?>"><?php if(!empty($slideImages[0])):?><meta property="og:image" content="<?=sk1130_h($slideImages[0])?>"><?php endif;?>
<script type="application/ld+json"><?=json_encode($ld,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP)?></script>
<link rel="stylesheet" href="/assets/business-website-v1160.css?v=1282">
<style>.bw-google-photo-note{margin-top:14px;font-size:12px;opacity:.76}.bw-google-photo-note a{color:inherit;font-weight:800}.bw-source-note{display:inline-flex;margin-top:10px;padding:7px 10px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);font-size:11px;font-weight:800}.bw-info-card .bw-source-note{background:#eef6ff;border-color:#cfe2f8;color:#19588f}:root{--biz-accent:<?=sk1130_h($palette['a'])?>;--biz-accent2:<?=sk1130_h($palette['b'])?>}.bw-buy-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:9px}.bw-buy-row form{margin:0}.bw-buy-row button{border:0;border-radius:10px;padding:9px 12px;background:var(--biz-accent);color:#fff;font-weight:800;cursor:pointer}.bw-cart-link{display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-weight:800}</style>
<script>window.SKBiz1160={businessId:<?=$bid?>,businessName:<?=json_encode($name)?>,shareUrl:<?=json_encode($shareUrl)?>,animations:<?=!empty($settings['animations'])?'true':'false'?>,ai:<?=!empty($settings['show_ai_concierge'])?'true':'false'?>,autoplay:<?=!empty($builder['slider_autoplay'])?(int)$builder['slider_speed_ms']:0?>,sectionOrder:<?=json_encode($builder['section_order']??[])?>,hiddenSections:<?=json_encode($builder['hidden_sections']??[])?>};</script>
<script defer src="/assets/business-website-v1160.js?v=1282"></script>
</head>
<body class="bw1160 template-<?=sk1130_h($pageTemplate)?> <?=!empty($settings['animations'])?'motion-on':'motion-off'?>">
<a class="skip-link" href="#main">Skip to content</a>
<header class="bw-header" data-sticky-header>
  <div class="bw-container bw-header-inner">
    <a class="bw-brand" href="#top"><?php if($logo):?><img src="<?=sk1130_h($logo)?>" alt="<?=sk1130_h($name)?> logo"><?php endif;?><div><strong><?=sk1130_h($name)?></strong><span><?=sk1130_h($cat)?></span></div></a>
    <nav class="bw-nav" data-bw-nav aria-label="Business website navigation">
      <a href="#top">Home</a><a href="#about">About</a><?php if($services):?><a href="#services">Services</a><?php endif;?><?php if($products):?><a href="#products">Products</a><?php endif;?><?php if(count($images)>1):?><a href="#gallery">Gallery</a><?php endif;?><?php if($reviews):?><a href="#reviews">Reviews</a><?php endif;?><?php if($deals):?><a href="#offers">Offers</a><?php endif;?><?php if($mapEmbed):?><a href="#location">Location</a><?php endif;?><a href="#contact">Contact</a>
    </nav>
    <div class="bw-header-actions"><a class="bw-outline bw-cart-link" href="/cart-v1230.php">Cart <span>(<?=$cartCount?>)</span></a><?php if($tel):?><a class="bw-outline" data-biz-event="call" href="tel:<?=sk1130_h($tel)?>">Call</a><?php endif;?><?php if($waUrl):?><a class="bw-solid" data-biz-event="whatsapp" href="<?=sk1130_h($waUrl)?>" target="_blank" rel="noopener">WhatsApp</a><?php endif;?></div>
    <a class="bw-chat-link" href="/business-contact-v1180.php?business_id=<?=$bid?>">Chat / Quote</a>
    <button class="bw-menu" data-bw-menu type="button" aria-label="Open menu">☰</button>
  </div>
</header>
<main id="main">
<section class="bw-hero" id="top" aria-label="Featured business highlights">
  <div class="bw-slider" data-hero-slider>
    <div class="bw-slides">
      <?php foreach($slides as $i=>$s):?><article class="bw-slide <?=$i===0?'is-active':''?>" data-slide="<?=$i?>">
        <?php if($s['image']):?><img class="bw-slide-img" src="<?=sk1130_h($s['image'])?>" alt="<?=sk1130_h($name)?>" <?=$i===0?'fetchpriority="high"':'loading="lazy"'?>><?php endif;?><div class="bw-slide-shade"></div>
        <div class="bw-container bw-slide-content"><div class="bw-slide-copy"><?php if($logo):?><div class="bw-hero-logo"><img src="<?=sk1130_h($logo)?>" alt="<?=sk1130_h($name)?> logo"></div><?php endif;?>
          <div class="bw-badges"><span><?=sk1130_h($s['eyebrow'])?></span><?php if($verified):?><span>✓ Verified</span><?php endif;?><?php if($featured):?><span>★ Featured</span><?php endif;?><?php if(!empty($sourceTag['linked'])):?><span><?=!empty($sourceTag['imported'])?'↻ Google-imported listing':'↗ Google source-linked'?></span><?php endif;?></div>
          <h1><?=sk1130_h($s['title'])?></h1><p><?=sk1130_h($s['text'])?></p>
          <div class="bw-hero-actions"><a class="bw-hero-primary" href="<?=sk1130_h($s['href'])?>"><?=sk1130_h($s['cta'])?></a><?php if($waUrl):?><a href="<?=sk1130_h($waUrl)?>" target="_blank" rel="noopener">WhatsApp</a><?php endif;?><?php if($directions):?><a href="<?=sk1130_h($directions)?>" target="_blank" rel="noopener">Directions</a><?php endif;?></div>
        </div></div>
      </article><?php endforeach;?>
    </div>
    <?php if(count($slides)>1):?><button class="bw-slider-arrow prev" data-slider-prev aria-label="Previous slide">‹</button><button class="bw-slider-arrow next" data-slider-next aria-label="Next slide">›</button>
    <div class="bw-slider-dots" data-slider-dots><?php foreach($slides as $i=>$s):?><button class="<?=$i===0?'is-active':''?>" data-dot="<?=$i?>" aria-label="Go to slide <?=$i+1?>"></button><?php endforeach;?></div>
    <div class="bw-slider-progress"><i data-slider-progress></i></div><?php endif;?>
  </div>
</section>

<section class="bw-quick"><div class="bw-container bw-quick-grid">
  <?php if($phone):?><a href="tel:<?=sk1130_h($tel)?>" data-biz-event="call"><b>Call us</b><span><?=sk1130_h($phone)?></span></a><?php endif;?>
  <?php if($address):?><a href="<?=sk1130_h($directions?:'#contact')?>" target="<?=$directions?'_blank':'_self'?>"><b>Visit us</b><span><?=sk1130_h($address)?></span></a><?php endif;?>
  <?php if($hours):?><div><b>Opening hours</b><span><?=sk1130_h($hours)?></span></div><?php endif;?>
  <?php if((int)$rating['count']>0):?><a href="#reviews"><b>★ <?=number_format((float)$rating['avg'],1)?></b><span><?=(int)$rating['count']?> customer reviews</span></a><?php endif;?>
</div></section>

<section class="bw-section bw-about" id="about" data-builder-section="about"><div class="bw-container bw-two-col">
 <div class="bw-copy reveal"><span class="bw-eyebrow">ABOUT US</span><h2><?=sk1130_h((string)($copy['about_title']??('About '.$name)))?></h2><p><?=nl2br(sk1130_h((string)($copy['about_body']??$desc)))?></p><?php if($why):?><div class="bw-points"><?php foreach(array_slice($why,0,6) as $x):?><div><i>✓</i><span><?=sk1130_h((string)$x)?></span></div><?php endforeach;?></div><?php endif;?></div>
 <aside class="bw-info-card reveal"><?php if($logo):?><img src="<?=sk1130_h($logo)?>" alt="<?=sk1130_h($name)?>"><?php endif;?><h3><?=sk1130_h($name)?></h3><p><?=sk1130_h($cat)?></p><?php if($verified):?><span class="bw-verified">✓ ShahkotPK Verified</span><?php endif;?><?php if(!empty($sourceTag['linked'])):?><span class="bw-source-note"><?=!empty($sourceTag['imported'])?'↻ Imported via ShahkotPK Business Importer':'↗ Google source-linked listing'?></span><?php endif;?><div class="bw-info-links"><?php if($email):?><a href="mailto:<?=sk1130_h($email)?>"><?=sk1130_h($email)?></a><?php endif;?><?php if($website):?><a href="<?=sk1130_h($website)?>" target="_blank" rel="noopener">Official website ↗</a><?php endif;?></div></aside>
</div></section>

<?php if($services && !empty($settings['show_services'])):?><section class="bw-section bw-soft" id="services" data-builder-section="services"><div class="bw-container"><div class="bw-section-head"><div><span class="bw-eyebrow">WHAT WE DO</span><h2>Services</h2><p><?=sk1130_h((string)($copy['services_intro']??'Explore the services available from this business.'))?></p></div><div class="bw-carousel-nav"><button data-carousel-prev="services">‹</button><button data-carousel-next="services">›</button></div></div><div class="bw-carousel" data-carousel="services"><div class="bw-track"><?php foreach($services as $i=>$svc):?><article class="bw-service-card reveal"><span><?=str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?></span><h3><?=sk1130_h((string)$svc)?></h3><p>Contact <?=sk1130_h($name)?> for details and availability.</p><?php if($bookUrl):?><a href="<?=sk1130_h($bookUrl)?>">Enquire →</a><?php endif;?></article><?php endforeach;?></div></div></div></section><?php endif;?>

<?php if($products):?><section class="bw-section" id="products" data-builder-section="products"><div class="bw-container"><div class="bw-section-head"><div><span class="bw-eyebrow">SHOP LOCAL</span><h2>Featured products</h2><p>Explore products currently connected to this business.</p></div><div class="bw-carousel-nav"><button data-carousel-prev="products">‹</button><button data-carousel-next="products">›</button></div></div><div class="bw-carousel" data-carousel="products"><div class="bw-track"><?php foreach($products as $p):$pi=b1160_pickimg($p);?><article class="bw-product-card reveal"><?php if($pi):?><div class="bw-product-img"><img loading="lazy" decoding="async" src="<?=sk1130_h($pi)?>" alt="<?=sk1130_h(b1160_title($p))?>"></div><?php endif;?><div class="bw-product-body"><small><?=sk1130_h($cat)?></small><h3><?=sk1130_h(b1160_title($p))?></h3><?php $price=sk1130_pick($p,['sale_price','price','selling_price','unit_price'],'');if($price!==''):?><b><?=sk1130_h(b1160_money($price))?></b><?php endif;?><div class="bw-buy-row"><a data-biz-event="product" href="/product.php?id=<?=(int)($p['id']??0)?>">View product →</a><?php if(function_exists('sk1230_cart_add') && (float)$price>0):?><form method="post" action="/cart-v1230.php"><input type="hidden" name="_csrf" value="<?=sk1130_h(csrf_token())?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?=(int)($p['id']??0)?>"><input type="hidden" name="qty" value="1"><button type="submit">Add to Cart</button></form><?php endif;?></div></div></article><?php endforeach;?></div></div></div></section><?php endif;?>

<?php if(count($images)>1 && !empty($settings['show_gallery'])):?><section class="bw-section bw-dark" id="gallery" data-builder-section="gallery"><div class="bw-container"><div class="bw-section-head light"><div><span class="bw-eyebrow">GALLERY</span><h2>Take a closer look</h2><p>Photos from <?=sk1130_h($name)?>.</p></div><div class="bw-carousel-nav"><button data-carousel-prev="gallery">‹</button><button data-carousel-next="gallery">›</button></div></div><div class="bw-carousel" data-carousel="gallery"><div class="bw-track bw-gallery-track"><?php foreach($images as $im):?><button class="bw-gallery-card" data-gallery-src="<?=sk1130_h($im)?>"><img loading="lazy" decoding="async" src="<?=sk1130_h($im)?>" alt="<?=sk1130_h($name)?> gallery"></button><?php endforeach;?></div><?php if($googleLivePhotos):?><p class="bw-google-photo-note">Google-sourced business photos are loaded live from Google Maps and are not copied into ShahkotPK storage. <?php $gm=(string)($googleLivePhotos[0]['maps_url']??'');if($gm!==''):?><a href="<?=sk1130_h($gm)?>" target="_blank" rel="noopener">View source ↗</a><?php endif;?></p><?php endif;?></div></div></section><?php endif;?>

<?php if($deals):?><section class="bw-section" id="offers" data-builder-section="offers"><div class="bw-container"><div class="bw-section-head"><div><span class="bw-eyebrow">OFFERS</span><h2>Deals & promotions</h2></div></div><div class="bw-offers-grid"><?php foreach($deals as $d):?><article class="bw-offer reveal"><?php $di=b1160_pickimg($d);if($di):?><img loading="lazy" src="<?=sk1130_h($di)?>" alt="<?=sk1130_h(b1160_title($d))?>"><?php endif;?><div><span>LOCAL DEAL</span><h3><?=sk1130_h(b1160_title($d))?></h3><p><?=sk1130_h((string)sk1130_pick($d,['description','details','summary'],''))?></p><a data-biz-event="deal" href="/deals-v1050.php">View deal →</a></div></article><?php endforeach;?></div></div></section><?php endif;?>

<?php if($reviews):?><section class="bw-section bw-soft" id="reviews" data-builder-section="reviews"><div class="bw-container"><div class="bw-section-head"><div><span class="bw-eyebrow">CUSTOMER VOICES</span><h2>Reviews</h2><p><?php if((int)$rating['count']>0):?>Average <?=number_format((float)$rating['avg'],1)?> / 5 from <?=(int)$rating['count']?> listed reviews.<?php endif;?></p></div><div class="bw-carousel-nav"><button data-carousel-prev="reviews">‹</button><button data-carousel-next="reviews">›</button></div></div><div class="bw-carousel" data-carousel="reviews"><div class="bw-track"><?php foreach($reviews as $r):?><article class="bw-review-card reveal"><div class="bw-stars"><?php $rv=(int)round((float)sk1130_pick($r,['rating','stars','score'],5));echo str_repeat('★',max(1,min(5,$rv)));?></div><p>“<?=sk1130_h((string)sk1130_pick($r,['review_text','comment','body','text'],'Customer review'))?>”</p><b><?=sk1130_h((string)sk1130_pick($r,['title','customer_name','name'],'Customer'))?></b></article><?php endforeach;?></div></div></div></section><?php endif;?>

<?php if($faq):?><section class="bw-section" id="faq" data-builder-section="faq"><div class="bw-container bw-faq-wrap"><div><span class="bw-eyebrow">FAQ</span><h2>Questions & answers</h2><p>Answers based on the information currently listed for this business.</p></div><div class="bw-faq-list"><?php foreach(array_slice($faq,0,8) as $x):?><details><summary><?=sk1130_h((string)($x['q']??'Question'))?></summary><p><?=sk1130_h((string)($x['a']??''))?></p></details><?php endforeach;?></div></div></section><?php endif;?>

<?php if($mapEmbed):?>
<section class="bw-section bw-location-section" id="location" data-builder-section="location">
  <div class="bw-container">
    <div class="bw-section-head bw-location-head">
      <div><span class="bw-eyebrow">FIND US</span><h2>Visit <?=sk1130_h($name)?></h2><p><?=sk1130_h($address!==''?$address:'Open the map to view this business location.')?></p></div>
      <?php if($directions):?><a class="bw-map-direction" href="<?=sk1130_h($directions)?>" target="_blank" rel="noopener" data-biz-event="directions">Open directions ↗</a><?php endif;?>
    </div>
    <div class="bw-location-shell reveal">
      <div class="bw-map-frame"><iframe title="Map location of <?=sk1130_h($name)?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?=sk1130_h($mapEmbed)?>"></iframe></div>
      <aside class="bw-location-details">
        <?php if($logo):?><div class="bw-location-logo"><img src="<?=sk1130_h($logo)?>" alt="<?=sk1130_h($name)?> logo"></div><?php endif;?>
        <span class="bw-eyebrow">LOCATION DETAILS</span><h3><?=sk1130_h($name)?></h3>
        <?php if($address):?><div class="bw-location-row"><i>⌖</i><div><small>Address</small><b><?=sk1130_h($address)?></b></div></div><?php endif;?>
        <?php if($phone):?><div class="bw-location-row"><i>☎</i><div><small>Phone</small><a href="tel:<?=sk1130_h($tel)?>"><?=sk1130_h($phone)?></a></div></div><?php endif;?>
        <?php if($hours):?><div class="bw-location-row"><i>◷</i><div><small>Opening hours</small><b><?=sk1130_h($hours)?></b></div></div><?php endif;?>
        <?php if($directions):?><a class="bw-location-cta" href="<?=sk1130_h($directions)?>" target="_blank" rel="noopener">Get directions</a><?php endif;?>
      </aside>
    </div>
  </div>
</section>
<?php endif;?>

<section class="bw-contact" id="contact" data-builder-section="contact">
  <div class="bw-container">
    <div class="bw-contact-intro">
      <span class="bw-eyebrow">LET'S CONNECT</span>
      <h2>Visit or contact <?=sk1130_h($name)?></h2>
      <p>Call, WhatsApp, request a quote or send an enquiry directly to the business.</p>
    </div>
    <div class="bw-contact-layout">
      <div class="bw-contact-primary">
        <div class="bw-contact-buttons">
          <?php if($tel):?><a class="is-call" href="tel:<?=sk1130_h($tel)?>" data-biz-event="call"><span>☎</span><b>Call</b><small><?=sk1130_h($phone)?></small></a><?php endif;?>
          <?php if($waUrl):?><a class="is-whatsapp" href="<?=sk1130_h($waUrl)?>" target="_blank" rel="noopener" data-biz-event="whatsapp"><span>◉</span><b>WhatsApp</b><small>Message business</small></a><?php endif;?>
          <?php if($directions):?><a href="<?=sk1130_h($directions)?>" target="_blank" rel="noopener" data-biz-event="directions"><span>⌖</span><b>Directions</b><small>Open map route</small></a><?php endif;?>
          <?php if($bookUrl):?><a href="<?=sk1130_h($bookUrl)?>" data-biz-event="booking"><span>✓</span><b>Book / Enquire</b><small>Request assistance</small></a><?php endif;?>
          <a href="/business-contact-v1180.php?business_id=<?=$bid?>"><span>✦</span><b>Chat / Quote</b><small>Start a conversation</small></a>
        </div>
        <aside class="bw-contact-card">
          <div class="bw-contact-card-brand"><?php if($logo):?><img src="<?=sk1130_h($logo)?>" alt="<?=sk1130_h($name)?>"><?php endif;?><div><strong><?=sk1130_h($name)?></strong><span><?=sk1130_h($cat)?></span></div></div>
          <?php if($address):?><div><span>ADDRESS</span><b><?=sk1130_h($address)?></b></div><?php endif;?>
          <?php if($hours):?><div><span>HOURS</span><b><?=sk1130_h($hours)?></b></div><?php endif;?>
          <?php if($email):?><div><span>EMAIL</span><a href="mailto:<?=sk1130_h($email)?>"><?=sk1130_h($email)?></a></div><?php endif;?>
          <?php if($website):?><div><span>WEBSITE</span><a href="<?=sk1130_h($website)?>" target="_blank" rel="noopener">Visit official website ↗</a></div><?php endif;?>
        </aside>
      </div>
      <?php if(!empty($builder['lead_form_enabled'])):?><form class="bw-lead-form" action="/api/business-lead-v1170.php" method="post" data-business-lead>
        <input type="hidden" name="business_id" value="<?=$bid?>"><input type="text" name="company_website" value="" tabindex="-1" autocomplete="off" class="bw-honeypot" aria-hidden="true">
        <div class="bw-lead-heading"><span>ENQUIRY FORM</span><h3>Send an enquiry</h3><p>Tell <?=sk1130_h($name)?> what you need and they can respond through ShahkotPK.</p></div>
        <div class="bw-lead-grid"><label><span>Name *</span><input required maxlength="160" name="customer_name" placeholder="Your name"></label><label><span>Phone</span><input maxlength="60" name="phone" inputmode="tel" placeholder="03xx xxxxxxx"></label><label><span>Email</span><input maxlength="190" type="email" name="email" placeholder="you@example.com"></label><label><span>Subject</span><input maxlength="190" name="subject" placeholder="How can we help?"></label></div>
        <label class="bw-lead-message"><span>Message *</span><textarea required maxlength="2000" name="message" rows="5" placeholder="Write your question or requirement..."></textarea></label>
        <button type="submit"><span>Send enquiry</span><b>→</b></button><div class="bw-lead-status" aria-live="polite"></div>
      </form><?php endif;?>
    </div>
  </div>
</section>
</main>
<footer class="bw-footer"><div class="bw-container bw-footer-grid"><div class="bw-footer-brand"><?php if($logo):?><img src="<?=sk1130_h($logo)?>" alt="<?=sk1130_h($name)?>"><?php endif;?><h3><?=sk1130_h($name)?></h3><p><?=sk1130_h($cat)?> · ShahkotPK Business Directory</p></div><div><b>Explore</b><a href="#about">About</a><?php if($services):?><a href="#services">Services</a><?php endif;?><?php if($products):?><a href="#products">Products</a><?php endif;?><a href="#contact">Contact</a></div><div><b>ShahkotPK</b><a href="/businesses.php">Business Directory</a><a href="/">Home</a><a href="/business-contact-v1180.php?business_id=<?=$bid?>">Chat / Quote</a><button type="button" data-share>Share business</button></div><?php if($social):?><div><b>Social</b><?php foreach($social as $label=>$url):?><a href="<?=sk1130_h($url)?>" target="_blank" rel="noopener"><?=sk1130_h($label)?></a><?php endforeach;?></div><?php endif;?></div><div class="bw-container bw-footer-bottom"><span>Powered by ShahkotPK</span><span>Business information is provided from the ShahkotPK directory.</span></div></footer>

<?php if(!empty($settings['show_contact_bar'])):?><div class="bw-mobile-actions"><?php if($tel):?><a href="tel:<?=sk1130_h($tel)?>">Call</a><?php endif;?><?php if($waUrl):?><a href="<?=sk1130_h($waUrl)?>" target="_blank" rel="noopener">WhatsApp</a><?php endif;?><?php if($bookUrl):?><a href="<?=sk1130_h($bookUrl)?>">Book</a><?php endif;?></div><?php endif;?>
<div class="bw-lightbox" data-bw-lightbox hidden><button type="button" aria-label="Close">×</button><img alt="Gallery preview"></div>
<?php if(!empty($settings['show_ai_concierge'])):?><div class="bw-ai" data-biz-ai><button class="bw-ai-launch" type="button">✦ Ask <?=sk1130_h($name)?></button><section class="bw-ai-panel" hidden><header><div><strong>Business Assistant</strong><small>Answers from ShahkotPK business information</small></div><button type="button" data-biz-ai-close>×</button></header><div class="bw-ai-msgs" data-biz-ai-msgs><div class="bot">Ask about services, contact information, location or listed business details.</div></div><div class="bw-ai-status" data-biz-ai-status></div><form data-biz-ai-form><textarea rows="2" placeholder="Ask about this business…"></textarea><div><button type="button" data-biz-ai-voice>🎙</button><button type="button" data-biz-ai-speak>🔊</button><button type="submit">Send</button></div></form></section></div><?php endif;?>
</body></html>
