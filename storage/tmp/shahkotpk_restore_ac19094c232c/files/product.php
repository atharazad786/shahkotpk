<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';

$slug=(string)($_GET['slug']??$_GET['id']??'');
$p=mp550_product($slug);
if(!$p||$p['status']!=='published'){
    http_response_code(404);
    exit('Product not found.');
}
mp550_capture_referral((int)$p['id']);
$me=current_user();
$productId=(int)$p['id'];
$flash='';$error='';

// Product experience v5.5.2 actions. Tables are isolated and non-destructive.
try{
    if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
        csrf_check();
        $action=(string)($_POST['action']??'');
        if($action==='review'){
            if(!$me) throw new RuntimeException('Please sign in to review this product.');
            $rating=max(1,min(5,(int)($_POST['rating']??5)));
            $title=trim((string)($_POST['review_title']??''));
            $body=trim((string)($_POST['review_body']??''));
            if(mb_strlen($body)<5) throw new RuntimeException('Please write a little more about your experience.');
            $verified=0;
            try{
                $q=db()->prepare("SELECT 1 FROM marketplace_order_items_v550 oi JOIN marketplace_orders_v550 o ON o.id=oi.order_id WHERE oi.product_id=? AND o.buyer_user_id=? AND o.status IN ('delivered','completed') LIMIT 1");
                $q->execute([$productId,(int)$me['id']]);
                $verified=$q->fetchColumn()?1:0;
            }catch(Throwable $e){}
            db()->prepare("INSERT INTO marketplace_product_reviews_v552(product_id,user_id,rating,title,body,verified_purchase,status,created_at,updated_at) VALUES(?,?,?,?,?,?,'approved',NOW(),NOW()) ON DUPLICATE KEY UPDATE rating=VALUES(rating),title=VALUES(title),body=VALUES(body),verified_purchase=VALUES(verified_purchase),status='approved',updated_at=NOW()")
              ->execute([$productId,(int)$me['id'],$rating,mb_substr($title,0,180),mb_substr($body,0,4000),$verified]);
            $flash='Your review has been saved.';
        }elseif($action==='wishlist'){
            if(!$me) throw new RuntimeException('Please sign in to save products.');
            $q=db()->prepare('SELECT id FROM marketplace_wishlist_v552 WHERE product_id=? AND user_id=? LIMIT 1');
            $q->execute([$productId,(int)$me['id']]);
            $wid=(int)$q->fetchColumn();
            if($wid){db()->prepare('DELETE FROM marketplace_wishlist_v552 WHERE id=?')->execute([$wid]);$flash='Removed from wishlist.';}
            else{db()->prepare('INSERT INTO marketplace_wishlist_v552(product_id,user_id,created_at) VALUES(?,?,NOW())')->execute([$productId,(int)$me['id']]);$flash='Saved to wishlist.';}
        }
    }
}catch(Throwable $e){$error=$e->getMessage();}

$aff=$me?mp550_affiliate_profile((int)$me['id'],true):null;
$sharePath=$aff?('/product.php?slug='.urlencode((string)$p['slug']).'&ref='.urlencode((string)$aff['code'])):'';
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=(string)($_SERVER['HTTP_HOST']??'');
$canonical=$scheme.'://'.$host.'/product.php?slug='.urlencode((string)$p['slug']);
$shareUrl=$sharePath?$scheme.'://'.$host.$sharePath:$canonical;

$gallery=[];
if(!empty($p['image_url'])) $gallery[]=(string)$p['image_url'];
try{
    $g=json_decode((string)($p['gallery_json']??'[]'),true);
    if(is_array($g)) foreach($g as $img){if(is_string($img)&&trim($img)!=='')$gallery[]=trim($img);}
}catch(Throwable $e){}
$gallery=array_values(array_unique($gallery));

$ratingAvg=0.0;$ratingCount=0;$ratingBreakdown=[1=>0,2=>0,3=>0,4=>0,5=>0];$reviews=[];$sold=0;$isWish=false;
try{
    $q=db()->prepare("SELECT COALESCE(AVG(rating),0) avg_rating,COUNT(*) total FROM marketplace_product_reviews_v552 WHERE product_id=? AND status='approved'");$q->execute([$productId]);$r=$q->fetch()?:[];$ratingAvg=(float)($r['avg_rating']??0);$ratingCount=(int)($r['total']??0);
    $q=db()->prepare("SELECT rating,COUNT(*) c FROM marketplace_product_reviews_v552 WHERE product_id=? AND status='approved' GROUP BY rating");$q->execute([$productId]);foreach($q->fetchAll()?:[] as $rb){$ratingBreakdown[(int)$rb['rating']]=(int)$rb['c'];}
    $q=db()->prepare("SELECT r.*,u.name user_name FROM marketplace_product_reviews_v552 r JOIN users u ON u.id=r.user_id WHERE r.product_id=? AND r.status='approved' ORDER BY r.id DESC LIMIT 12");$q->execute([$productId]);$reviews=$q->fetchAll()?:[];
    $q=db()->prepare("SELECT COALESCE(SUM(oi.quantity),0) FROM marketplace_order_items_v550 oi JOIN marketplace_orders_v550 o ON o.id=oi.order_id WHERE oi.product_id=? AND o.status IN ('delivered','completed')");$q->execute([$productId]);$sold=(int)$q->fetchColumn();
    if($me){$q=db()->prepare('SELECT 1 FROM marketplace_wishlist_v552 WHERE product_id=? AND user_id=? LIMIT 1');$q->execute([$productId,(int)$me['id']]);$isWish=(bool)$q->fetchColumn();}
}catch(Throwable $e){}

$sellerProducts=0;$sellerOrders=0;$sellerRating=0.0;$sellerRatingCount=0;
try{
    if(!empty($p['shop_id'])){
        $q=db()->prepare("SELECT COUNT(*) FROM marketplace_product_meta_v550 m JOIN store_products sp ON sp.id=m.product_id WHERE m.shop_id=? AND sp.status='published'");$q->execute([(int)$p['shop_id']]);$sellerProducts=(int)$q->fetchColumn();
        $q=db()->prepare("SELECT COUNT(*) FROM marketplace_orders_v550 WHERE shop_id=? AND status IN ('delivered','completed')");$q->execute([(int)$p['shop_id']]);$sellerOrders=(int)$q->fetchColumn();
        $q=db()->prepare("SELECT COALESCE(AVG(r.rating),0),COUNT(*) FROM marketplace_product_reviews_v552 r JOIN marketplace_product_meta_v550 m ON m.product_id=r.product_id WHERE m.shop_id=? AND r.status='approved'");$q->execute([(int)$p['shop_id']]);$sr=$q->fetch(PDO::FETCH_NUM);if($sr){$sellerRating=(float)$sr[0];$sellerRatingCount=(int)$sr[1];}
    }
}catch(Throwable $e){}

$related=[];
try{
    $related=mp550_products(['category'=>(string)$p['category_slug']],9);
    $related=array_values(array_filter($related,fn($x)=>(int)$x['id']!==$productId));
    $related=array_slice($related,0,6);
}catch(Throwable $e){}

$sale=(float)$p['sale_price'];
$old=(float)($p['compare_at_price']?:$p['price']);
$save=max(0,$old-$sale);
$discountPct=$old>0&&$save>0?(int)round(($save/$old)*100):0;
$stock=(int)$p['stock'];
$minQty=max(1,(int)($p['min_order_qty']??1));
$maxQty=(int)($p['max_order_qty']??0);$qtyMax=max($minQty,min($stock,$maxQty>0?$maxQty:$stock));
$shopName=(string)($p['shop_name']?:$p['seller_name']);
$cityName=(string)($p['city_name']?:setting('default_city','Shahkot'));
$initial=mb_strtoupper(mb_substr($shopName,0,1));

mp550_public_header((string)$p['title']);
?>
<link rel="stylesheet" href="/assets/marketplace-product-5.5.2.css?v=552">
<script>document.body.classList.add('mp552-product-body');</script>
<div class="mp552-shell">
<?php if($flash):?><div class="mp552-alert ok"><?=e($flash)?></div><?php endif;?>
<?php if($error):?><div class="mp552-alert err"><?=e($error)?></div><?php endif;?>
<nav class="mp552-breadcrumb" aria-label="Breadcrumb"><a href="/shop.php">Marketplace</a><span>›</span><a href="/shop.php?category=<?=urlencode((string)$p['category_slug'])?>"><?=e((string)$p['category_name'])?></a><span>›</span><b><?=e(mb_substr((string)$p['title'],0,65))?></b></nav>

<section class="mp552-product-card">
  <div class="mp552-gallery">
    <div class="mp552-main-image-wrap">
      <?php if($discountPct>0):?><span class="mp552-discount-badge">-<?=$discountPct?>%</span><?php endif;?>
      <span class="mp552-condition"><?=e(strtoupper((string)$p['product_type']))?></span>
      <?php if($gallery):?><img id="mp552-main-image" src="<?=e($gallery[0])?>" alt="<?=e((string)$p['title'])?>" loading="eager"><?php else:?><div class="mp552-no-image">🛍️<span>Product image</span></div><?php endif;?>
    </div>
    <?php if(count($gallery)>1):?><div class="mp552-thumbs" aria-label="Product images"><?php foreach($gallery as $i=>$img):?><button type="button" class="mp552-thumb <?=$i===0?'active':''?>" data-image="<?=e($img)?>"><img src="<?=e($img)?>" alt="Product image <?=($i+1)?>" loading="lazy"></button><?php endforeach;?></div><?php endif;?>
    <div class="mp552-share-row"><span>Share:</span><button type="button" data-copy="<?=e($canonical)?>">🔗 Copy link</button><a href="https://wa.me/?text=<?=urlencode((string)$p['title'].' '.$canonical)?>" target="_blank" rel="noopener">WhatsApp</a></div>
  </div>

  <div class="mp552-product-info">
    <div class="mp552-badges"><span class="verified">ShahkotPK Marketplace</span><?php if(!empty($p['shop_verified'])):?><span>✓ Verified seller</span><?php endif;?><?php if(!empty($p['featured'])):?><span>★ Featured</span><?php endif;?></div>
    <h1><?=e((string)$p['title'])?></h1>
    <div class="mp552-rating-line">
      <a href="#reviews"><strong><?=number_format($ratingAvg,1)?></strong><span class="stars" aria-label="<?=number_format($ratingAvg,1)?> out of 5"><?=str_repeat('★',(int)round($ratingAvg)).str_repeat('☆',5-(int)round($ratingAvg))?></span><u><?=$ratingCount?> ratings</u></a>
      <i></i><span><?=$sold?> sold</span><i></i><span>SKU: <?=e((string)($p['sku']?:'—'))?></span>
    </div>
    <div class="mp552-price-box">
      <div class="mp552-price-main"><strong><?=mp550_money($sale)?></strong><?php if($old>$sale):?><del><?=mp550_money($old)?></del><span>-<?=$discountPct?>%</span><?php endif;?></div>
      <?php if($save>0):?><div class="mp552-save">You save <b><?=mp550_money($save)?></b><?php if(!empty($p['discount_info']['name'])):?> with <?=e((string)$p['discount_info']['name'])?><?php endif;?></div><?php endif;?>
    </div>
    <?php if(trim((string)$p['short_description'])!==''):?><p class="mp552-lead"><?=e((string)$p['short_description'])?></p><?php endif;?>

    <div class="mp552-service-list">
      <div><i>🚚</i><span><b>Delivery</b><small><?=((float)$p['shipping_charge']>0)?mp550_money((float)$p['shipping_charge']).' shipping':'Free shipping'?> · availability confirmed at checkout</small></span></div>
      <div><i>↩</i><span><b>Returns</b><small><?=(int)$p['return_days']?> day return policy<?=(int)$p['return_days']===0?' / seller policy applies':''?></small></span></div>
      <div><i>🛡</i><span><b>Warranty</b><small><?=e((string)($p['warranty_text']?:'Seller support'))?></small></span></div>
      <div><i>📍</i><span><b>Ships from</b><small><?=e($cityName)?> · <?=e($shopName)?></small></span></div>
    </div>

    <form class="mp552-buy" method="post" action="/cart.php">
      <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?=$productId?>">
      <div class="mp552-qty-row"><span>Quantity</span><div class="mp552-qty"><button type="button" data-qty-minus>−</button><input id="mp552-qty-input" type="number" name="qty" min="<?=$minQty?>" max="<?=$qtyMax?>" value="<?=$minQty?>"><button type="button" data-qty-plus>+</button></div><small><?=$stock>0?number_format($stock).' items available':'Out of stock'?></small></div>
      <div class="mp552-buy-actions"><button class="cart" <?=$stock<1?'disabled':''?>>🛒 Add to Cart</button><button class="buy" name="buy_now" value="1" <?=$stock<1?'disabled':''?>>Buy Now</button></div>
    </form>

    <div class="mp552-secondary-actions">
      <form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="wishlist"><button>♡ <?=$isWish?'Saved':'Add to wishlist'?></button></form>
      <button type="button" data-copy="<?=e($shareUrl)?>">↗ <?=$aff?'Share & earn':'Share product'?></button>
    </div>

    <?php if($aff):?><div class="mp552-affiliate"><div><i>💸</i><span><b>Share & Earn</b><small>Use your affiliate link and earn on eligible completed orders.</small></span></div><button type="button" data-copy="<?=e($shareUrl)?>">Copy earning link</button></div><?php endif;?>
  </div>

  <aside class="mp552-seller-card">
    <div class="mp552-seller-head"><div class="mp552-shop-logo"><?php if(!empty($p['shop_logo'])):?><img src="<?=e((string)$p['shop_logo'])?>" alt="<?=e($shopName)?>"><?php else:?><?=e($initial)?><?php endif;?></div><div><small>Sold by</small><h3><?=e($shopName)?> <?php if(!empty($p['shop_verified'])):?><em>✓</em><?php endif;?></h3><span><?=e($cityName)?></span></div></div>
    <div class="mp552-seller-stats"><div><b><?=number_format($sellerRating,1)?></b><span>Seller rating</span></div><div><b><?=number_format($sellerProducts)?></b><span>Products</span></div><div><b><?=number_format($sellerOrders)?></b><span>Completed</span></div></div>
    <div class="mp552-seller-buttons"><a href="/shop.php?shop=<?=(int)($p['shop_id']??0)?>">Visit Store</a><a class="ghost" href="/shop.php?q=<?=urlencode($shopName)?>">View Products</a></div>
    <ul><li>✓ Seller-routed order management</li><li>✓ ShahkotPK marketplace checkout</li><li>✓ Order status tracking</li></ul>
  </aside>
</section>

<div class="mp552-layout">
  <main>
    <section class="mp552-panel" id="details"><div class="mp552-panel-title"><span>PRODUCT INFORMATION</span><h2>Product details</h2></div><div class="mp552-description"><?=nl2br(e((string)$p['description']))?></div><div class="mp552-spec-grid"><div><span>Brand</span><b><?=e((string)($p['brand']?:'Not specified'))?></b></div><div><span>SKU</span><b><?=e((string)($p['sku']?:'—'))?></b></div><div><span>Category</span><b><?=e((string)$p['category_name'])?></b></div><div><span>Condition</span><b><?=e(ucfirst((string)$p['product_type']))?></b></div><div><span>Minimum order</span><b><?=$minQty?></b></div><div><span>Return window</span><b><?=(int)$p['return_days']?> days</b></div></div></section>

    <section class="mp552-panel" id="reviews"><div class="mp552-panel-title"><span>CUSTOMER FEEDBACK</span><h2>Ratings & Reviews</h2></div><div class="mp552-review-summary"><div class="score"><strong><?=number_format($ratingAvg,1)?></strong><div class="stars"><?=str_repeat('★',(int)round($ratingAvg)).str_repeat('☆',5-(int)round($ratingAvg))?></div><span>Based on <?=$ratingCount?> review<?=$ratingCount===1?'':'s'?></span></div><div class="bars"><?php for($s=5;$s>=1;$s--):$pct=$ratingCount?round(($ratingBreakdown[$s]/$ratingCount)*100):0;?><div><span><?=$s?> ★</span><i><b style="width:<?=$pct?>%"></b></i><em><?=$ratingBreakdown[$s]?></em></div><?php endfor;?></div></div>
      <?php if($me):?><form class="mp552-review-form" method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="review"><div><label>Your rating<select name="rating"><option value="5">5 - Excellent</option><option value="4">4 - Very good</option><option value="3">3 - Good</option><option value="2">2 - Fair</option><option value="1">1 - Poor</option></select></label><label>Review title<input name="review_title" maxlength="180" placeholder="Summarize your experience"></label></div><label>Review<textarea name="review_body" maxlength="4000" required placeholder="What did you like or dislike about this product?"></textarea></label><button>Submit Review</button></form><?php else:?><div class="mp552-login-note"><a href="/login.php">Sign in</a> to write a product review.</div><?php endif;?>
      <div class="mp552-reviews"><?php foreach($reviews as $rv):?><article><div class="avatar"><?=e(mb_strtoupper(mb_substr((string)$rv['user_name'],0,1)))?></div><div><header><b><?=e((string)$rv['user_name'])?></b><span class="stars"><?=str_repeat('★',(int)$rv['rating']).str_repeat('☆',5-(int)$rv['rating'])?></span><?php if(!empty($rv['verified_purchase'])):?><em>Verified Purchase</em><?php endif;?></header><?php if(!empty($rv['title'])):?><h4><?=e((string)$rv['title'])?></h4><?php endif;?><p><?=nl2br(e((string)$rv['body']))?></p><small><?=e(date('d M Y',strtotime((string)$rv['created_at'])))?></small></div></article><?php endforeach;?><?php if(!$reviews):?><div class="mp552-empty-review">No reviews yet. Be the first to review this product.</div><?php endif;?></div>
    </section>
  </main>
  <aside class="mp552-side-info"><section><h3>Buyer Protection</h3><p>Orders, seller routing, discounts and status tracking stay inside ShahkotPK Marketplace.</p><div><span>🔒</span><b>Controlled checkout</b></div><div><span>📦</span><b>Track your order</b></div><div><span>🏪</span><b>Seller accountability</b></div></section><section><h3>Need help?</h3><p>Review the seller information and product details before placing an order.</p><a href="/my-orders.php">My Orders →</a></section></aside>
</div>

<?php if($related):?><section class="mp552-related"><div class="mp552-panel-title"><span>YOU MAY ALSO LIKE</span><h2>Similar products</h2></div><div class="mp552-related-grid"><?php foreach($related as $rp):$rs=(float)$rp['sale_price'];$ro=(float)($rp['compare_at_price']?:$rp['price']);?><a href="/product.php?slug=<?=urlencode((string)$rp['slug'])?>"><div class="pic"><?php if(!empty($rp['image_url'])):?><img src="<?=e((string)$rp['image_url'])?>" alt="<?=e((string)$rp['title'])?>" loading="lazy"><?php else:?><span>🛍️</span><?php endif;?></div><div class="copy"><small><?=e((string)$rp['category_name'])?></small><h3><?=e((string)$rp['title'])?></h3><strong><?=mp550_money($rs)?></strong><?php if($ro>$rs):?><del><?=mp550_money($ro)?></del><?php endif;?><span><?=e((string)($rp['shop_name']?:$rp['seller_name']))?></span></div></a><?php endforeach;?></div></section><?php endif;?>

<div class="mp552-mobile-buy"><div><small><?=e((string)$p['title'])?></small><b><?=mp550_money($sale)?></b></div><form method="post" action="/cart.php"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?=$productId?>"><input type="hidden" name="qty" value="<?=$minQty?>"><button <?=$stock<1?'disabled':''?>>Add to Cart</button><button name="buy_now" value="1" <?=$stock<1?'disabled':''?>>Buy Now</button></form></div>
</div>
<script src="/assets/marketplace-product-5.5.2.js?v=552"></script>
<?php mp550_public_footer(); ?>
