<?php
require __DIR__.'/app/bootstrap.php';
if(!feature_enabled('directory_enabled',true) || !feature_enabled('search_enabled',true)){http_response_code(403);exit('Public search is currently disabled.');}
require __DIR__.'/app/homepage.php';
require __DIR__.'/app/ads_public.php';

$q=trim($_GET['q']??'');
$category=trim($_GET['category']??'');
$city=trim($_GET['city']??'');
$params=[];
$where=["b.status=1","b.verification_status<>'suspended'"];
if($q!==''){
    $where[]="(b.name LIKE ? OR b.description LIKE ? OR b.address LIKE ? OR cat.name LIKE ?)";
    $like='%'.$q.'%';$params=array_merge($params,[$like,$like,$like,$like]);
}
if($category!==''){
    $where[]="cat.slug=?";
    $params[]=$category;
}
if($city!==''){
    $where[]="c.slug=?";
    $params[]=$city;
}
$limit=max(10,min(500,setting_int('search_results_limit',100)));
$sql="SELECT b.*,c.name city_name,cat.name category_name FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id WHERE ".implode(' AND ',$where)." ORDER BY b.is_featured DESC,b.id DESC LIMIT ".$limit;
$stmt=db()->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Search - <?=e(setting('site_name','ShahkotPK'))?></title><link rel="stylesheet" href="/assets/shahkotpk-1.5.0.css?v=150">
<style>
/* Yelp Style Search Page - Green Theme */
body { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; }
.city-header { background: #fff !important; border-bottom: 1px solid #ebebeb; padding: 15px 0; }
.brand-mark { background: #176b46 !important; border-radius: 4px; }
.brand strong { color: #333; }
.btn-green { background: #176b46 !important; border-radius: 4px; border: none; font-weight: bold; }

.page-hero { background: #fff !important; color: #333; border-bottom: 1px solid #ebebeb; padding: 40px 0; }
.page-hero h2 { color: #333; font-size: 32px; font-weight: 800; letter-spacing: -1px; margin-bottom: 10px; }
.page-hero p { color: #666; font-size: 16px; }
.section-kicker { color: #176b46; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }

.search-panel { background: #fff; padding: 20px; border-radius: 8px; display: flex; gap: 10px; margin-top: -30px; position: relative; z-index: 10; box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important; border: 1px solid #ebebeb !important; }
.search-panel input { border: 1px solid #ddd; border-radius: 4px; padding: 12px 15px; font-size: 16px; flex: 1; }
.search-panel input:focus { border-color: #176b46; outline: none; box-shadow: 0 0 0 3px rgba(23, 107, 70, 0.1); }
.search-panel button { background: #176b46; color: #fff; border: none; border-radius: 4px; padding: 12px 25px; font-weight: bold; font-size: 16px; cursor: pointer; transition: background 0.2s; }
.search-panel button:hover { background: #125437; }

.business-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
.business-card { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; }
.business-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.business-cover { height: 180px; background-size: cover; background-position: center; background-color: #f0f0f0; }
.business-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
.business-body h3 { font-size: 20px; font-weight: 700; color: #333; margin: 0 0 10px 0; }
.business-meta { font-size: 14px; color: #666; margin-bottom: 10px; font-weight: 500; }
.business-body p { color: #666; font-size: 14px; line-height: 1.5; margin: 0 0 15px 0; flex: 1; }
.business-body .verified { background: rgba(23, 107, 70, 0.1); color: #176b46; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; display: inline-block; align-self: flex-start; }
.business-body a { color: #176b46; font-weight: 600; text-decoration: none; border-top: 1px solid #ebebeb; padding-top: 15px; display: block; text-align: center; }
.business-body a:hover { color: #125437; background: #fafafa; margin: 0 -20px -20px -20px; padding: 15px 20px; border-radius: 0 0 8px 8px; }

.empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 8px; border: 1px solid #ebebeb; color: #666; font-size: 18px; }
</style>
</head><body>
<div class="city-header"><div class="city-shell city-header-inner"><a class="brand" href="/"><span class="brand-mark">S</span><span><strong><?=e(setting('site_name','ShahkotPK'))?></strong><small>City Directory</small></span></a><a class="btn btn-green" href="/signup.php">Register Business</a></div></div>
<section class="page-hero"><div class="city-shell"><div class="section-head"><div><div class="section-kicker">Directory Search</div><h2><?=e($q?:($category?:'All Businesses'))?></h2><p><?=count($rows)?> result(s) found for your search.</p></div></div></div></section>
<section class="section" style="padding-top:0px"><div class="city-shell">
<?php if(feature_enabled('advertisements_enabled',true)&&($searchAd=active_ad_for_placement('search_sponsor'))):?><div class="lt-sponsored" style="margin-top:20px;background:#fff;border-radius:8px;border:1px solid #ebebeb;"><div class="lt-sponsored-inner"><div class="lt-sponsored-image"<?php if($searchAd['image_url']):?> style="background-image:url('<?=e($searchAd['image_url'])?>')"<?php endif;?>></div><div><span style="color:#176b46">SPONSORED</span><h3 style="color:#333"><?=e($searchAd['title'])?></h3><p style="color:#666"><?=e($searchAd['business_name']?:'Local promotion')?></p></div><a href="/ad-click.php?id=<?=e($searchAd['id'])?>" style="background:#176b46;color:#fff;border:none">View Offer</a></div></div><?php endif;?>
<form class="search-panel" method="get"><input name="q" value="<?=e($q)?>" placeholder="Search Shahkot businesses (e.g. Pizza, Plumber)"><input name="city" value="<?=e($city)?>" placeholder="City slug (optional)"><button>Search</button></form>
<div class="business-grid"><?php foreach($rows as $b):?><article class="business-card"><div class="business-cover" <?=$b['image']?'style="background-image:url(\''.e($b['image']).'\')"':''?>></div><div class="business-body"><span class="verified"><?=e($b['verification_status']==='verified'?'✓ Verified':'Listing')?></span><h3><?=e($b['name'])?></h3><div class="business-meta"><?=e($b['category_name'])?> · <?=e($b['city_name'])?></div><p><?=e(mb_strimwidth((string)$b['description'],0,120,'…'))?></p><a href="/business.php?slug=<?=urlencode($b['slug'])?>">View Profile &rarr;</a></div></article><?php endforeach;?></div>
<?php if(!$rows):?><div class="empty-state">No businesses found. Try another search.</div><?php endif;?>
</div></section></body></html>
