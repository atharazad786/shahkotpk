<?php
require __DIR__.'/app/bootstrap.php';if(!feature_enabled('directory_enabled',true)){http_response_code(403);exit('Business directory is currently disabled.');}
$stats=city_business_stats();$featured=city_business_list(['featured'=>1],6);$newBusinesses=city_business_list(['sort'=>'new'],12);$categories=city_portal_category_counts(12);$cities=city_portal_cities();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Businesses — <?=e(setting('site_name','ShahkotPK'))?></title><meta name="description" content="Browse local shops, services and verified businesses across <?=e(setting('default_city','Shahkot'))?>."><link rel="stylesheet" href="/assets/themes/landing/base.css?v=240"><link rel="stylesheet" href="/assets/city-verticals-3.0.1.css?v=301">
<style>
/* Yelp Style Business Directory - Green Theme */
body.cityv-page { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; }
.cityv-header { background: #fff !important; border-bottom: 1px solid #ebebeb; padding: 15px 0; }
.cityv-brand i { background: #176b46; border-radius: 4px; }
.cityv-nav a { color: #666; font-weight: 600; }
.cityv-nav a:hover, .cityv-nav a.active { color: #176b46; border-bottom: 3px solid #176b46; padding-bottom: 5px; }

.cityv-hero { background: #fff !important; color: #333; border-bottom: 1px solid #ebebeb; padding: 50px 0; }
.cityv-hero h1 { color: #333; font-size: 42px; font-weight: 800; letter-spacing: -1px; margin-bottom: 15px; }
.cityv-hero p { color: #666; font-size: 18px; line-height: 1.6; }
.cityv-kicker { display: none; } /* Hide kicker for cleaner look */
.cityv-hero-actions a.primary { background: #176b46; border-radius: 4px; padding: 14px 24px; font-weight: bold; }
.cityv-hero-actions a:not(.primary) { color: #176b46; font-weight: bold; }
.cityv-pills span { background: #f5f6f5; color: #333; border: 1px solid #ebebeb; border-radius: 4px; padding: 6px 12px; }
.cityv-pills span em { background: #176b46; color: #fff; border-radius: 4px; }

/* Dashboard Card (Right panel in hero) */
.cityv-dashboard-card { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); color: #333; padding: 25px; }
.cityv-dashboard-label { color: #176b46; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 20px; display: block; border-bottom: 1px solid #ebebeb; padding-bottom: 10px; }
.cityv-stat-grid div { background: transparent; border: none; padding: 10px 0; border-bottom: 1px solid #f5f6f5; border-radius: 0; }
.cityv-stat-grid strong { color: #333; font-size: 24px; }
.cityv-stat-grid span { color: #666; }
.cityv-city-list { border-top: 1px solid #ebebeb; padding-top: 15px; margin-top: 15px; }
.cityv-city-list span { color: #176b46; font-weight: 600; }
.cityv-city-list b { color: #888; }

/* Section Headings */
.cityv-section { padding: 60px 0; }
.cityv-title-row h2 { color: #176b46; font-size: 28px; font-weight: 800; margin: 0; }
.cityv-title-row span { color: #666; font-weight: 700; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 5px; display: block; }

/* Yelp Style Business Cards */
.cityv-grid { grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 30px; }
.cityv-listing-card { border: 1px solid #ebebeb; border-radius: 6px; box-shadow: none; transition: box-shadow 0.2s, border-color 0.2s; background: #fff; display: flex; flex-direction: column; overflow: hidden; }
.cityv-listing-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: none; border-color: #ccc; }
.cityv-card-media { height: 210px; border-radius: 0; position: relative; }
.cityv-card-media span { background: rgba(0,0,0,0.7); border-radius: 4px; padding: 5px 10px; font-weight: bold; }
.cityv-card-media em { background: #176b46; border-radius: 4px; padding: 5px 10px; font-style: normal; font-weight: bold; }
.cityv-card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
.cityv-card-body h3 { font-size: 22px; font-weight: 800; color: #173228; margin-bottom: 8px; line-height: 1.2; }
.cityv-card-body h3:hover { text-decoration: underline; color: #176b46; }
.cityv-card-meta { margin-bottom: 12px; border-bottom: 1px solid #f5f6f5; padding-bottom: 12px; }
.cityv-card-meta b { color: #176b46; font-size: 13px; background: #e9f5ee; padding: 3px 8px; border-radius: 4px; }
.cityv-card-meta small { color: #666; font-size: 14px; display: flex; align-items: center; }
.cityv-card-meta small::before { content: "📍"; margin-right: 4px; }
.cityv-card-body p { font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 20px; flex: 1; }
.cityv-card-body > a { display: block; padding: 12px; background: #f5f6f5; color: #173228; font-weight: 700; border-radius: 6px; text-decoration: none; text-align: center; transition: background 0.2s; border: 1px solid #ebebeb; }
.cityv-card-body > a:hover { background: #176b46; color: #fff; border-color: #176b46; }

/* Filter / Category Links */
.cityv-link-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 15px; }
.cityv-link-grid a { border: 1px solid #ebebeb; border-radius: 6px; padding: 15px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: all 0.2s; align-items: center; }
.cityv-link-grid a:hover { background: #f9f9f9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: #ccc; }
.cityv-link-grid i { background: #e9f5ee; color: #176b46; border-radius: 6px; width: 45px; height: 45px; font-size: 20px; }
.cityv-link-grid b { color: #173228; font-size: 16px; font-weight: 700; }
.cityv-link-grid small { color: #888; }
.cityv-link-grid em { display: none; } /* Hide arrow for cleaner look */
</style>
</head><body class="cityv-page cityv-businesses"><header class="cityv-header"><div class="cityv-shell cityv-header-inner"><a class="cityv-brand" href="/"><i>S</i><span><b><?=e(setting('site_name','ShahkotPK'))?></b><small>Local Business Directory</small></span></a><button class="cityv-menu" type="button" data-cityv-menu>☰</button><nav class="cityv-nav" data-cityv-nav><a href="/city-guide.php">City Guide</a><a href="/deals.php">Deals</a><a href="/events.php">Events</a><a href="/news.php">News</a><a href="/blog.php">Blog</a><a href="/jobs.php">Jobs</a><a href="/property.php">Property</a><a class="active" href="/businesses.php">Businesses</a></nav><div class="cityv-head-actions"><a href="/search.php">Advanced Search</a><a class="cityv-head-primary" href="/signup.php">List Business</a></div></div></header><section class="cityv-hero"><div class="cityv-shell cityv-hero-grid"><article><h1>Find shops, services and trusted local businesses</h1><p>Search the ShahkotPK directory, explore featured profiles and connect directly using call, WhatsApp and business details.</p><div class="cityv-hero-actions"><a class="primary" href="/search.php">Search Directory</a><a href="/signup.php">List Your Business</a></div><div class="cityv-pills"><?php foreach(array_slice($categories,0,7) as $c):?><span><?=e($c['name'])?> <em><?=e($c['business_count'])?></em></span><?php endforeach;?></div></article><aside class="cityv-dashboard-card"><span class="cityv-dashboard-label">LIVE BUSINESS DIRECTORY</span><div class="cityv-stat-grid"><div><strong><?=e((string)$stats['total'])?></strong><span>Total Businesses</span></div><div><strong><?=e((string)$stats['verified_count'])?></strong><span>Verified</span></div><div><strong><?=e((string)$stats['featured_count'])?></strong><span>Featured</span></div><div><strong><?=e((string)$stats['city_count'])?></strong><span>Cities</span></div></div><div class="cityv-city-list"><?php foreach(array_slice($cities,0,5) as $c):?><div><span><?=e($c['name'])?></span><b>Live directory</b></div><?php endforeach;?></div></aside></div></section><section class="cityv-section"><div class="cityv-shell"><div class="cityv-title-row"><div><span>FEATURED BUSINESSES</span><h2>Premium local showcases</h2><p>Highlighted businesses with enhanced visibility on ShahkotPK.</p></div><a href="/pricing.php" style="color: #176b46; font-weight: bold;">Get Featured →</a></div><div class="cityv-grid"><?php foreach($featured as $b):?><article class="cityv-listing-card"><div class="cityv-card-media"<?php if($b['image']):?> style="background-image:url('<?=e($b['image'])?>')"<?php endif;?>><span><?=e($b['category_name'])?></span><?php if($b['verification_status']==='verified'):?><em>Verified</em><?php endif;?></div><div class="cityv-card-body"><div class="cityv-card-meta"><small><?=e($b['city_name'])?></small><b><?=e($b['open_status']['label']??'Business')?></b></div><h3><?=e($b['name'])?></h3><p><?=e($b['tagline']?:mb_strimwidth((string)$b['description'],0,120,'…'))?></p><a href="/business.php?slug=<?=urlencode((string)$b['slug'])?>">View Business</a></div></article><?php endforeach;?></div><?php if(!$featured):?><div class="cityv-empty"><div>▦</div><h3>Featured businesses are coming soon</h3><p>Explore all active businesses or register your own business for a ShahkotPK listing.</p><div><a class="primary" href="/search.php">Browse Businesses</a><a href="/signup.php">List Business</a></div></div><?php endif;?></div></section><section class="cityv-section cityv-muted" style="background:#fff;"><div class="cityv-shell"><div class="cityv-title-row"><div><span>NEWLY ADDED</span><h2>Fresh local business listings</h2></div><a href="/search.php" style="color: #176b46; font-weight: bold;">Full Directory →</a></div><div class="cityv-grid"><?php foreach($newBusinesses as $b):?><article class="cityv-listing-card"><div class="cityv-card-media"<?php if($b['image']):?> style="background-image:url('<?=e($b['image'])?>')"<?php endif;?>><span><?=e($b['category_name'])?></span></div><div class="cityv-card-body"><div class="cityv-card-meta"><small><?=e($b['city_name'])?></small><b><?=e($b['open_status']['label']??'Listing')?></b></div><h3><?=e($b['name'])?></h3><p><?=e($b['tagline']?:mb_strimwidth((string)$b['description'],0,115,'…'))?></p><a href="/business.php?slug=<?=urlencode((string)$b['slug'])?>">Explore Profile</a></div></article><?php endforeach;?></div></div></section><section class="cityv-section"><div class="cityv-shell"><div class="cityv-title-row"><div><span>POPULAR CATEGORIES</span><h2>Browse by category</h2></div></div><div class="cityv-link-grid"><?php foreach($categories as $c):?><a href="/search.php?category=<?=urlencode((string)$c['slug'])?>"><i><?=e(mb_strtoupper(mb_substr($c['name'],0,1)))?></i><span><b><?=e($c['name'])?></b><small><?=e($c['business_count'])?> local businesses</small></span><em>→</em></a><?php endforeach;?></div></div></section><footer class="cityv-footer"><div class="cityv-shell"><div><b><?=e(setting('site_name','ShahkotPK'))?></b><span>Your local business and city guide.</span></div><nav><a href="/">Home</a><a href="/city-guide.php">City Guide</a><a href="/pricing.php">Advertising & Plans</a></nav><small>© <?=date('Y')?> <?=e(setting('site_name','ShahkotPK'))?></small></div></footer><script>(function(){const b=document.querySelector('[data-cityv-menu]'),n=document.querySelector('[data-cityv-nav]');b?.addEventListener('click',()=>n?.classList.toggle('open'));})();</script><?php if(google_maps_ready()&&setting_bool('google_maps_businesses_enabled',true))render_google_maps_assets();?>
</body></html>