<?php
declare(strict_types=1);require __DIR__.'/app/bootstrap.php';if(!mp550_enabled()){http_response_code(404);exit('Marketplace unavailable.');}mp550_capture_referral();$filters=['q'=>trim((string)($_GET['q']??'')),'category'=>(string)($_GET['category']??''),'type'=>(string)($_GET['type']??''),'sort'=>(string)($_GET['sort']??''),'shop'=>(int)($_GET['shop']??0)];$products=mp550_products($filters,60);$cats=mp550_categories();$shops=mp550_shops([],8);$deals=array_filter(mp550_discount_rows(true),fn($d)=>(int)$d['automatic_apply']===1);$auctions=mp550_auctions(['status'=>'live'],4);mp550_public_header('Marketplace Pro');
?>
<style>
/* Yelp Style Marketplace - Green Theme */
body { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; }
.mp550-hero { background: #fff !important; padding: 50px 20px; border-bottom: 1px solid #ebebeb; color: #333; display: flex; justify-content: center; }
.mp550-hero > div { width: 100%; max-width: 1200px; }
.mp550-hero .eyebrow { display: none; }
.mp550-hero h1 { font-size: 42px; font-weight: 800; color: #333; margin: 0 0 15px 0; letter-spacing: -1px; }
.mp550-hero h1 em { font-style: normal; color: #176b46; }
.mp550-hero p { font-size: 18px; color: #666; margin: 0 0 30px 0; max-width: 800px; line-height: 1.5; }
.mp550-search { display: flex; gap: 10px; max-width: 800px; margin-bottom: 25px; }
.mp550-search input, .mp550-search select { padding: 14px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; background: #fff; flex: 1; outline: none; }
.mp550-search input:focus, .mp550-search select:focus { border-color: #176b46; }
.mp550-search button { background: #176b46; color: #fff; border: none; border-radius: 4px; padding: 0 30px; font-weight: bold; font-size: 16px; cursor: pointer; transition: background 0.2s; }
.mp550-search button:hover { background: #125235; }
.hero-links { display: flex; gap: 20px; flex-wrap: wrap; }
.hero-links a { color: #176b46; font-weight: bold; text-decoration: none; font-size: 15px; }
.hero-links a:hover { text-decoration: underline; }
.mp550-hero aside { display: none; } /* Simplify hero */

.mp550-strip { background: #fff; padding: 15px 20px; border-bottom: 1px solid #ebebeb; }
.mp550-strip div { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.mp550-strip span { font-weight: bold; color: #e00707; font-size: 14px; }
.mp550-strip b { font-size: 14px; color: #333; background: #f5f6f5; padding: 6px 12px; border-radius: 4px; }

.mp550-section { padding: 60px 20px; max-width: 1200px; margin: 0 auto; }
.section-head { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; }
.section-head > div > span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.section-head h2 { font-size: 28px; font-weight: 800; color: #176b46; margin: 0; }
.section-head form { display: flex; gap: 10px; }
.section-head select { padding: 10px 30px 10px 15px; border: 1px solid #ebebeb; border-radius: 4px; font-weight: bold; color: #333; background: #fff; cursor: pointer; }

.mp550-products { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
.mp550-product { background: #fff; border: 1px solid #ebebeb; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; transition: box-shadow 0.2s, border-color 0.2s; }
.mp550-product:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: #ccc; }
.mp550-product .photo { height: 220px; background-size: cover; background-position: center; background-color: #f5f6f5; position: relative; display: block; }
.mp550-product .photo span { position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
.mp550-product .photo em { position: absolute; top: 10px; right: 10px; background: #e00707; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; font-style: normal; }
.mp550-product .copy { padding: 20px; flex: 1; display: flex; flex-direction: column; }
.mp550-product .copy small { color: #666; font-size: 13px; margin-bottom: 8px; display: block; }
.mp550-product .copy h3 { font-size: 18px; font-weight: 800; color: #333; margin: 0 0 10px 0; line-height: 1.3; }
.mp550-product .copy a { text-decoration: none; }
.mp550-product .copy h3:hover { color: #176b46; text-decoration: underline; }
.mp550-product .price { font-size: 20px; color: #176b46; font-weight: 800; margin-bottom: 12px; }
.mp550-product .price del { color: #999; font-size: 14px; font-weight: normal; margin-left: 8px; }
.mp550-product p { font-size: 14px; color: #555; line-height: 1.5; margin-bottom: 20px; flex: 1; }
.product-actions { display: flex; gap: 10px; }
.product-actions form { flex: 1; margin: 0; }
.product-actions button { width: 100%; background: #176b46; color: #fff; border: none; padding: 12px 0; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s; font-size: 14px; }
.product-actions button:hover { background: #125235; }
.product-actions > a { display: flex; align-items: center; justify-content: center; background: #f5f6f5; color: #333; border: 1px solid #ebebeb; padding: 0 20px; font-weight: bold; border-radius: 4px; text-decoration: none; transition: background 0.2s; font-size: 14px; }
.product-actions > a:hover { background: #ebebeb; }

.mp550-shops { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.mp550-shops a { display: flex; align-items: center; background: #fff; border: 1px solid #ebebeb; border-radius: 6px; padding: 15px; text-decoration: none; transition: all 0.2s; }
.mp550-shops a:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: #ccc; }
.mp550-shops i { width: 50px; height: 50px; background: #e9f5ee; color: #176b46; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; font-style: normal; margin-right: 15px; flex-shrink: 0; }
.mp550-shops b { display: block; color: #333; font-size: 16px; margin-bottom: 4px; font-weight: bold; }
.mp550-shops span { display: block; color: #666; font-size: 13px; }
.mp550-shops strong { margin-left: auto; color: #ccc; font-weight: bold; }
.mp550-empty { text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #ebebeb; border-radius: 6px; }
.mp550-empty b { display: block; font-size: 20px; color: #333; margin-bottom: 10px; }
.mp550-empty span { color: #666; }
@media (max-width: 768px) { .mp550-search { flex-direction: column; } .section-head { flex-direction: column; align-items: flex-start; } }
</style>
<section class="mp550-hero"><div><span class="eyebrow">SHAHKOTPK MARKETPLACE PRO</span><h1>Shop smarter. <em>Sell locally.</em> Earn together.</h1><p>Products from multiple local sellers, real deals, seller-routed orders, affiliate rewards and live auctions in one modern marketplace.</p><form class="mp550-search"><input name="q" value="<?=e($filters['q'])?>" placeholder="Search products, brands, shops..."><select name="category"><option value="">All categories</option><?php foreach($cats as $c):?><option value="<?=e($c['slug'])?>" <?=$filters['category']===$c['slug']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select><button>Search</button></form><div class="hero-links"><a href="/auctions.php">⚡ Live Auctions</a><a href="/affiliate.php">↗ Share & Earn</a><a href="/seller-marketplace.php">▣ Seller Center</a></div></div><aside><div><b><?=count($products)?></b><span>Products found</span></div><div><b><?=count($shops)?></b><span>Featured shops</span></div><div><b><?=count($auctions)?></b><span>Live auctions</span></div></aside></section>
<?php if($deals):?><section class="mp550-strip"><div><span>🔥 HOT DEALS</span><?php foreach(array_slice($deals,0,4) as $d):?><b><?=e($d['name'])?> · <?=e($d['discount_type']==='percent'?number_format((float)$d['discount_value'],0).'% OFF':mp550_money((float)$d['discount_value']).' OFF')?></b><?php endforeach;?></div></section><?php endif;?>
<section class="mp550-section"><div class="section-head"><div><span>DISCOVER</span><h2>Browse marketplace</h2></div><form><input type="hidden" name="q" value="<?=e($filters['q'])?>"><select name="type" onchange="this.form.submit()"><option value="">New, used & digital</option><option value="new" <?=$filters['type']==='new'?'selected':''?>>New</option><option value="used" <?=$filters['type']==='used'?'selected':''?>>Used</option><option value="digital" <?=$filters['type']==='digital'?'selected':''?>>Digital</option></select><select name="sort" onchange="this.form.submit()"><option value="">Recommended</option><option value="price_asc" <?=$filters['sort']==='price_asc'?'selected':''?>>Price low to high</option><option value="price_desc" <?=$filters['sort']==='price_desc'?'selected':''?>>Price high to low</option></select></form></div><div class="mp550-products"><?php foreach($products as $p):$sale=(float)$p['sale_price'];$old=(float)($p['compare_at_price']?:$p['price']);?><article class="mp550-product"><a class="photo" href="/product.php?slug=<?=urlencode($p['slug'])?>"<?=!empty($p['image_url'])?' style="background-image:url(\''.e($p['image_url']).'\')"':''?>><span><?=e(strtoupper($p['product_type']))?></span><?php if(($p['discount_info']['amount']??0)>0):?><em>-<?=number_format((float)$p['discount_info']['percent'],0)?>%</em><?php endif;?></a><div class="copy"><small><?=e($p['category_name'])?> · <?=e($p['shop_name']?:$p['seller_name'])?></small><a href="/product.php?slug=<?=urlencode($p['slug'])?>"><h3><?=e($p['title'])?></h3></a><div class="price"><b><?=mp550_money($sale)?></b><?php if($old>$sale):?><del><?=mp550_money($old)?></del><?php endif;?></div><p><?=e(mb_substr((string)$p['short_description'],0,110))?></p><div class="product-actions"><form method="post" action="/cart.php"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?=(int)$p['id']?>"><button>Add to cart</button></form><a href="/product.php?slug=<?=urlencode($p['slug'])?>">View</a></div></div></article><?php endforeach;?><?php if(!$products):?><div class="mp550-empty"><b>No products found</b><span>Try another search or category.</span></div><?php endif;?></div></section>
<?php if($shops):?><section class="mp550-section"><div class="section-head"><div><span>LOCAL SELLERS</span><h2>Shop trusted storefronts</h2></div></div><div class="mp550-shops"><?php foreach($shops as $s):?><a href="/shop.php?shop=<?=(int)$s['id']?>"><i><?=e(strtoupper(mb_substr($s['name'],0,1)))?></i><div><b><?=e($s['name'])?> <?=$s['verified']?'✓':''?></b><span><?=e((string)$s['product_count'])?> products · <?=e($s['address']?:'Local seller')?></span></div><strong>→</strong></a><?php endforeach;?></div></section><?php endif;?>
<?php if($auctions):?><section class="mp550-section"><div class="section-head"><div><span>LIVE NOW</span><h2>Power auctions</h2></div><a href="/auctions.php">View all auctions →</a></div><div class="mp550-auction-grid"><?php foreach($auctions as $a):?><a href="/auction.php?id=<?=(int)$a['id']?>"><div class="photo"<?=!empty($a['image_url'])?' style="background-image:url(\''.e($a['image_url']).'\')"':''?>><span>LIVE</span></div><div><b><?=e($a['title'])?></b><strong><?=mp550_money((float)$a['current_price'])?></strong><small><?=e((string)$a['bidder_count'])?> bidders · ends <span data-countdown="<?=e($a['ends_at'])?>"></span></small></div></a><?php endforeach;?></div></section><?php endif;?>
<?php mp550_public_footer(); ?>
