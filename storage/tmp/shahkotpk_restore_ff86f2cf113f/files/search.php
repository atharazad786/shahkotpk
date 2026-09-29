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
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Search - <?=e(setting('site_name','ShahkotPK'))?></title><link rel="stylesheet" href="/assets/shahkotpk-1.5.0.css?v=150"></head><body>
<div class="city-header"><div class="city-shell city-header-inner"><a class="brand" href="/"><span class="brand-mark">S</span><span><strong><?=e(setting('site_name','ShahkotPK'))?></strong><small>City Directory</small></span></a><a class="btn btn-green" href="/signup.php">Register Business</a></div></div>
<section class="page-hero"><div class="city-shell"><div class="section-head"><div><div class="section-kicker">Directory Search</div><h2><?=e($q?:($category?:'All Businesses'))?></h2><p><?=count($rows)?> result(s) found for your search.</p></div></div></div></section>
<section class="section" style="padding-top:20px"><div class="city-shell">
<?php if(feature_enabled('advertisements_enabled',true)&&($searchAd=active_ad_for_placement('search_sponsor'))):?><div class="lt-sponsored"><div class="lt-sponsored-inner"><div class="lt-sponsored-image"<?php if($searchAd['image_url']):?> style="background-image:url('<?=e($searchAd['image_url'])?>')"<?php endif;?>></div><div><span>SPONSORED</span><h3><?=e($searchAd['title'])?></h3><p><?=e($searchAd['business_name']?:'Local promotion')?></p></div><a href="/ad-click.php?id=<?=e($searchAd['id'])?>">View Offer</a></div></div><?php endif;?>
<form class="search-panel" style="box-shadow:none;border:1px solid #e4e9e5;margin-bottom:28px" method="get"><input name="q" value="<?=e($q)?>" placeholder="Search Shahkot businesses"><input name="city" value="<?=e($city)?>" placeholder="City slug (optional)"><button>Search</button></form>
<div class="business-grid"><?php foreach($rows as $b):?><article class="business-card"><div class="business-cover" <?=$b['image']?'style="background-image:url(\''.e($b['image']).'\')"':''?>></div><div class="business-body"><span class="verified"><?=e($b['verification_status']==='verified'?'✓ Verified':'Listing')?></span><h3><?=e($b['name'])?></h3><div class="business-meta"><?=e($b['category_name'])?> · <?=e($b['city_name'])?></div><p><?=e(mb_strimwidth((string)$b['description'],0,120,'…'))?></p><a href="/business.php?slug=<?=urlencode($b['slug'])?>">View Profile →</a></div></article><?php endforeach;?></div>
<?php if(!$rows):?><div class="empty-state">No businesses found. Try another search.</div><?php endif;?>
</div></section></body></html>
