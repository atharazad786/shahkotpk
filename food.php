<?php
require __DIR__.'/app/bootstrap.php';require __DIR__.'/app/growth_public.php';if(!growth_enabled('restaurant_menu_enabled',true)){http_response_code(404);exit('Restaurant portal disabled.');}$restaurants=growth_restaurants(36);growth_public_head('Food & Restaurant Menus','Browse local restaurants and menu information.','food');
?>
<style>
/* Yelp Style Food Directory - Green Theme */
body.growth-public { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; color: #333; }
.growth-public-header { background: #fff !important; border-bottom: 1px solid #ebebeb; padding: 15px 0; }
.growth-brand { color: #173228 !important; }
.growth-brand small { color: #666; }
.growth-public-nav a { color: #666; font-weight: bold; }
.growth-public-nav a:hover, .growth-public-nav a.active { color: #176b46; }

/* Hero */
.growth-hero { background: #fff; padding: 50px 0; border-bottom: 1px solid #ebebeb; }
.growth-hero-grid { display: flex; gap: 40px; align-items: center; justify-content: space-between; }
@media (max-width: 900px) { .growth-hero-grid { flex-direction: column; text-align: center; } }
.growth-hero span { display: block; color: #176b46; font-weight: bold; font-size: 13px; letter-spacing: 1px; margin-bottom: 15px; text-transform: uppercase; }
.growth-hero h1 { font-size: 42px; font-weight: 900; color: #173228; margin: 0 0 15px 0; line-height: 1.1; letter-spacing: -1px; }
.growth-hero p { font-size: 18px; color: #555; line-height: 1.6; max-width: 600px; }
.growth-hero-panel { background: #e9f5ee; padding: 30px; border-radius: 8px; text-align: center; min-width: 200px; }
.growth-hero-panel b { display: block; font-size: 48px; font-weight: 900; color: #176b46; line-height: 1; margin-bottom: 10px; }
.growth-hero-panel small { color: #173228; font-weight: bold; font-size: 14px; }

/* Section & Cards */
.growth-section { padding: 60px 0; }
.growth-section-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
.growth-section-head span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.growth-section-head h2 { font-size: 28px; font-weight: 800; color: #173228; margin: 0; }
.growth-btn { background: #176b46; color: #fff; border: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
.growth-btn.ghost { background: #fff; color: #173228; border: 1px solid #ccc; }
.growth-btn.ghost:hover { background: #f5f6f5; }

.growth-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
.growth-card { background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #ebebeb; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; }
.growth-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.growth-card-image { height: 200px; background-size: cover; background-position: center; background-color: #e9f5ee; }
.growth-card-body { padding: 25px; flex: 1; display: flex; flex-direction: column; }
.growth-card-body > span { display: inline-block; background: #e9f5ee; color: #176b46; font-weight: bold; font-size: 12px; padding: 4px 8px; border-radius: 4px; margin-bottom: 12px; }
.growth-card-body h3 { font-size: 20px; font-weight: 800; color: #173228; margin: 0 0 8px 0; }
.growth-card-body p { font-size: 14px; color: #666; line-height: 1.5; margin-bottom: 15px; }
.growth-card-body p b { color: #333; }
.growth-card-body a { margin-top: auto; display: block; font-weight: bold; color: #176b46; text-decoration: none; padding: 10px; background: #f5f6f5; text-align: center; border-radius: 4px; transition: background 0.2s; }
.growth-card-body a:hover { background: #e9f5ee; }
</style>
<section class="growth-hero"><div class="lt-shell growth-hero-grid"><div><span>FOOD & RESTAURANTS</span><h1>Discover local food with real menus and prices.</h1><p>Browse restaurants, ratings and available menu items, then contact the business directly.</p></div><aside class="growth-hero-panel"><b><?=e(count($restaurants))?></b><small>Restaurants / menu-enabled businesses</small></aside></div></section>
<section class="growth-section"><div class="lt-shell"><div class="growth-section-head"><div><span>EAT LOCAL</span><h2>Restaurants & Food Businesses</h2></div><a class="growth-btn ghost" href="/search.php?q=restaurant">Search Directory</a></div><div class="growth-card-grid"><?php foreach($restaurants as $b):$menu=growth_restaurant_menu((int)$b['id']);?><article class="growth-card"><div class="growth-card-image" <?=$b['image']?'style="background-image:url(\''.e($b['image']).'\')"':''?>></div><div class="growth-card-body"><span>★ <?=e(number_format((float)$b['rating'],1))?> · <?=e($b['review_count'])?> reviews</span><h3><?=e($b['name'])?></h3><p><?=e($b['category_name'])?> · <?=e($b['city_name'])?></p><?php if($menu):?><p><b><?=e(count($menu))?> menu items available</b> · from Rs <?=e(number_format(min(array_map(fn($m)=>(float)$m['price'],$menu))))?></p><?php endif;?><a href="/business.php?slug=<?=urlencode($b['slug'])?>">View Menu & Business →</a></div></article><?php endforeach;?></div><?php if(!$restaurants):?><div class="growth-empty">Restaurant menus will appear as local businesses publish them.</div><?php endif;?></div></section>
<?php growth_public_footer(); ?>
