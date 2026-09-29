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
<style>
/* Yelp Style Product Detail - Green Theme */
body.mp552-product-body { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; color: #333; }
.mp552-shell { max-width: 1200px; margin: 0 auto; padding: 20px; }
.mp552-breadcrumb { font-size: 14px; margin-bottom: 20px; color: #666; }
.mp552-breadcrumb a { color: #176b46; text-decoration: none; font-weight: 600; }
.mp552-breadcrumb span { margin: 0 8px; color: #ccc; }
.mp552-breadcrumb b { color: #333; }

/* Main Card */
.mp552-product-card { display: flex; gap: 40px; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #ebebeb; margin-bottom: 30px; }
@media (max-width: 900px) { .mp552-product-card { flex-direction: column; } }
.mp552-gallery { width: 400px; flex-shrink: 0; }
.mp552-main-image-wrap { background: #f5f6f5; border-radius: 8px; overflow: hidden; position: relative; height: 400px; display: flex; align-items: center; justify-content: center; }
.mp552-main-image-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
.mp552-discount-badge { position: absolute; top: 15px; left: 15px; background: #e00707; color: #fff; font-weight: bold; padding: 4px 10px; border-radius: 4px; font-size: 14px; }
.mp552-condition { position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.7); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
.mp552-thumbs { display: flex; gap: 10px; margin-top: 15px; }
.mp552-thumb { width: 70px; height: 70px; border: 2px solid transparent; border-radius: 4px; overflow: hidden; cursor: pointer; padding: 0; background: #f5f6f5; }
.mp552-thumb.active { border-color: #176b46; }
.mp552-thumb img { width: 100%; height: 100%; object-fit: cover; }
.mp552-share-row { display: flex; align-items: center; gap: 15px; margin-top: 20px; font-size: 14px; color: #666; }
.mp552-share-row button, .mp552-share-row a { background: none; border: none; color: #176b46; font-weight: bold; cursor: pointer; text-decoration: none; padding: 0; display: inline-flex; }

/* Product Info */
.mp552-product-info { flex: 1; }
.mp552-badges { display: flex; gap: 10px; margin-bottom: 15px; }
.mp552-badges span { background: #e9f5ee; color: #176b46; font-weight: bold; font-size: 12px; padding: 4px 8px; border-radius: 4px; }
.mp552-badges span.verified { background: #173228; color: #fff; }
.mp552-product-info h1 { font-size: 32px; font-weight: 800; color: #173228; margin: 0 0 15px 0; line-height: 1.2; }
.mp552-rating-line { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; font-size: 14px; color: #666; }
.mp552-rating-line a { display: flex; align-items: center; gap: 5px; color: #333; text-decoration: none; }
.mp552-rating-line .stars { color: #f59e0b; letter-spacing: 2px; }
.mp552-rating-line u { color: #176b46; text-decoration: none; font-weight: bold; }
.mp552-rating-line i { width: 4px; height: 4px; background: #ccc; border-radius: 50%; }

.mp552-price-box { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ebebeb; margin-bottom: 25px; }
.mp552-price-main { display: flex; align-items: baseline; gap: 10px; margin-bottom: 5px; }
.mp552-price-main strong { font-size: 32px; font-weight: 900; color: #176b46; }
.mp552-price-main del { color: #999; font-size: 18px; }
.mp552-price-main span { background: #e00707; color: #fff; font-size: 14px; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
.mp552-save { color: #176b46; font-weight: bold; font-size: 14px; }

.mp552-lead { font-size: 16px; color: #555; line-height: 1.6; margin-bottom: 25px; }
.mp552-service-list { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
.mp552-service-list div { display: flex; gap: 10px; }
.mp552-service-list i { font-style: normal; font-size: 20px; }
.mp552-service-list b { display: block; color: #333; font-size: 14px; margin-bottom: 2px; }
.mp552-service-list small { color: #666; font-size: 13px; }

/* Buy Area */
.mp552-buy { display: flex; flex-direction: column; gap: 20px; margin-bottom: 25px; border-top: 1px solid #ebebeb; padding-top: 25px; }
.mp552-qty-row { display: flex; align-items: center; gap: 15px; }
.mp552-qty-row > span { font-weight: bold; color: #333; }
.mp552-qty { display: flex; border: 1px solid #ccc; border-radius: 4px; overflow: hidden; width: 120px; }
.mp552-qty button { flex: 1; background: #f5f6f5; border: none; font-size: 18px; cursor: pointer; transition: background 0.2s; }
.mp552-qty button:hover { background: #e0e0e0; }
.mp552-qty input { width: 50px; text-align: center; border: none; border-left: 1px solid #ccc; border-right: 1px solid #ccc; font-weight: bold; outline: none; }
.mp552-buy-actions { display: flex; gap: 15px; }
.mp552-buy-actions button { flex: 1; padding: 15px; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer; transition: all 0.2s; border: none; }
.mp552-buy-actions .cart { background: #f5f6f5; color: #173228; border: 1px solid #ccc; }
.mp552-buy-actions .cart:hover { background: #e9e9e9; }
.mp552-buy-actions .buy { background: #176b46; color: #fff; }
.mp552-buy-actions .buy:hover { background: #125235; }

.mp552-secondary-actions { display: flex; gap: 15px; border-top: 1px solid #ebebeb; padding-top: 20px; }
.mp552-secondary-actions form, .mp552-secondary-actions button { background: none; border: none; color: #176b46; font-weight: bold; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 5px; text-decoration: none; padding: 0; }
.mp552-secondary-actions button:hover { text-decoration: underline; }

.mp552-affiliate { background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px solid #ebebeb; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
.mp552-affiliate div { display: flex; gap: 10px; align-items: center; }
.mp552-affiliate i { font-style: normal; font-size: 20px; }
.mp552-affiliate b { font-size: 14px; color: #333; }
.mp552-affiliate small { display: block; font-size: 12px; color: #666; }
.mp552-affiliate button { background: #fff; border: 1px solid #ccc; padding: 8px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; }

/* Seller Sidebar */
.mp552-seller-card { width: 300px; flex-shrink: 0; background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ebebeb; align-self: flex-start; }
.mp552-seller-head { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; }
.mp552-shop-logo { width: 60px; height: 60px; background: #e9f5ee; color: #176b46; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; border-radius: 8px; overflow: hidden; }
.mp552-shop-logo img { width: 100%; height: 100%; object-fit: cover; }
.mp552-seller-head small { color: #666; font-size: 12px; font-weight: bold; text-transform: uppercase; }
.mp552-seller-head h3 { font-size: 18px; font-weight: 800; color: #173228; margin: 2px 0; }
.mp552-seller-head h3 em { color: #176b46; font-style: normal; margin-left: 5px; }
.mp552-seller-head span { color: #666; font-size: 13px; }
.mp552-seller-stats { display: flex; justify-content: space-between; border-top: 1px solid #ebebeb; border-bottom: 1px solid #ebebeb; padding: 15px 0; margin-bottom: 20px; text-align: center; }
.mp552-seller-stats b { display: block; font-size: 18px; color: #173228; font-weight: 900; }
.mp552-seller-stats span { font-size: 12px; color: #666; }
.mp552-seller-buttons { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.mp552-seller-buttons a { padding: 12px; text-align: center; font-weight: bold; border-radius: 4px; text-decoration: none; transition: background 0.2s; font-size: 14px; }
.mp552-seller-buttons a:not(.ghost) { background: #176b46; color: #fff; }
.mp552-seller-buttons a:not(.ghost):hover { background: #125235; }
.mp552-seller-buttons a.ghost { background: #fff; color: #173228; border: 1px solid #ccc; }
.mp552-seller-buttons a.ghost:hover { background: #f5f6f5; }
.mp552-seller-card ul { list-style: none; padding: 0; margin: 0; }
.mp552-seller-card li { font-size: 13px; color: #555; margin-bottom: 8px; display: flex; align-items: flex-start; }

/* Panels below */
.mp552-layout { display: flex; gap: 40px; }
@media (max-width: 900px) { .mp552-layout { flex-direction: column; } }
.mp552-layout main { flex: 1; }
.mp552-side-info { width: 300px; flex-shrink: 0; }
.mp552-panel { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 30px; margin-bottom: 30px; }
.mp552-panel-title { margin-bottom: 25px; border-bottom: 1px solid #ebebeb; padding-bottom: 15px; }
.mp552-panel-title span { color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; display: block; margin-bottom: 5px; text-transform: uppercase; }
.mp552-panel-title h2 { font-size: 24px; font-weight: 800; color: #173228; margin: 0; }
.mp552-description { font-size: 15px; color: #444; line-height: 1.6; margin-bottom: 30px; }
.mp552-spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.mp552-spec-grid div { background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px solid #ebebeb; }
.mp552-spec-grid span { color: #666; font-size: 13px; display: block; margin-bottom: 5px; }
.mp552-spec-grid b { color: #173228; font-size: 15px; }

/* Reviews */
.mp552-review-summary { display: flex; gap: 30px; margin-bottom: 30px; align-items: center; background: #f9f9f9; padding: 20px; border-radius: 8px; }
.mp552-review-summary .score { text-align: center; }
.mp552-review-summary .score strong { font-size: 48px; font-weight: 900; color: #173228; line-height: 1; display: block; }
.mp552-review-summary .score .stars { color: #f59e0b; font-size: 20px; margin: 5px 0; letter-spacing: 2px; }
.mp552-review-summary .score span { color: #666; font-size: 13px; }
.mp552-review-summary .bars { flex: 1; }
.mp552-review-summary .bars div { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; font-size: 13px; color: #555; }
.mp552-review-summary .bars i { flex: 1; height: 8px; background: #ebebeb; border-radius: 4px; overflow: hidden; display: block; }
.mp552-review-summary .bars b { display: block; height: 100%; background: #f59e0b; }
.mp552-review-summary .bars em { font-style: normal; width: 20px; text-align: right; }

.mp552-review-form { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ebebeb; margin-bottom: 30px; }
.mp552-review-form > div { display: flex; gap: 20px; margin-bottom: 15px; }
.mp552-review-form label { display: block; flex: 1; font-weight: bold; font-size: 14px; color: #333; }
.mp552-review-form select, .mp552-review-form input, .mp552-review-form textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; margin-top: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box; }
.mp552-review-form button { background: #176b46; color: #fff; border: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s; font-size: 15px; }
.mp552-review-form button:hover { background: #125235; }

.mp552-reviews article { border-bottom: 1px solid #ebebeb; padding: 25px 0; display: flex; gap: 20px; }
.mp552-reviews article:first-child { padding-top: 0; }
.mp552-reviews article:last-child { border-bottom: none; padding-bottom: 0; }
.mp552-reviews .avatar { width: 40px; height: 40px; background: #e9f5ee; color: #176b46; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0; }
.mp552-reviews header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.mp552-reviews header b { font-size: 16px; color: #173228; }
.mp552-reviews header .stars { color: #f59e0b; }
.mp552-reviews header em { background: #f5f6f5; padding: 2px 6px; font-size: 11px; font-weight: bold; color: #666; font-style: normal; border-radius: 4px; border: 1px solid #ebebeb; }
.mp552-reviews h4 { font-size: 16px; font-weight: bold; color: #333; margin: 0 0 8px 0; }
.mp552-reviews p { font-size: 15px; color: #555; line-height: 1.5; margin: 0 0 10px 0; }
.mp552-reviews small { color: #999; font-size: 13px; }

/* Side info widgets */
.mp552-side-info section { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 25px; margin-bottom: 20px; }
.mp552-side-info h3 { font-size: 18px; font-weight: 800; color: #173228; margin: 0 0 15px 0; }
.mp552-side-info p { font-size: 14px; color: #555; margin-bottom: 15px; line-height: 1.5; }
.mp552-side-info div { display: flex; align-items: center; gap: 10px; font-size: 14px; color: #333; margin-bottom: 10px; }
.mp552-side-info a { display: inline-block; background: #f5f6f5; color: #173228; font-weight: bold; padding: 10px 15px; border-radius: 4px; text-decoration: none; border: 1px solid #ccc; font-size: 14px; margin-top: 10px; }

/* Related Products */
.mp552-related { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 30px; margin-top: 30px; }
.mp552-related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
.mp552-related-grid a { display: block; text-decoration: none; border: 1px solid #ebebeb; border-radius: 6px; overflow: hidden; transition: box-shadow 0.2s; }
.mp552-related-grid a:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.mp552-related-grid .pic { height: 140px; background-size: cover; background-position: center; background-color: #f5f6f5; display: flex; align-items: center; justify-content: center; }
.mp552-related-grid .copy { padding: 15px; }
.mp552-related-grid small { display: block; color: #666; font-size: 12px; margin-bottom: 5px; }
.mp552-related-grid h3 { font-size: 14px; font-weight: bold; color: #333; margin: 0 0 5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mp552-related-grid strong { display: block; color: #176b46; font-size: 16px; margin-bottom: 5px; }
.mp552-related-grid span { display: block; font-size: 12px; color: #999; }
</style>
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
