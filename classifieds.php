<?php
declare(strict_types=1);require __DIR__.'/app/bootstrap.php';if(is_file(__DIR__.'/app/access_control_v1300.php'))require_once __DIR__.'/app/access_control_v1300.php';require_once __DIR__.'/app/classifieds_v1370.php';if(!sk1370_bool('classifieds_enabled',true)){http_response_code(503);exit('Classifieds are temporarily unavailable.');}sk1370_maybe_expire();$categories=sk1370_categories();$f=['q'=>trim((string)($_GET['q']??'')),'category_id'=>(int)($_GET['category']??0),'condition'=>(string)($_GET['condition']??''),'area'=>trim((string)($_GET['area']??'')),'price_min'=>(string)($_GET['min']??''),'price_max'=>(string)($_GET['max']??''),'sort'=>(string)($_GET['sort']??''),'field_filters'=>[]];foreach($_GET as $k=>$v)if(preg_match('~^cf_(\d+)$~',(string)$k,$m)&&trim((string)$v)!=='')$f['field_filters'][(int)$m[1]]=trim((string)$v);$filterFields=$f['category_id']?array_values(array_filter(sk1370_fields($f['category_id']),fn($x)=>(int)$x['filterable']===1)):[];$res=sk1370_query_ads($f,(int)($_GET['page']??1),(int)sk1370_setting('classifieds_per_page','24'));sk1370_shell('Local Classifieds','marketplace');?><style>
/* Yelp Style Classifieds Directory - Green Theme */
body.sps1370 { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; color: #333; }
.sk1370-header { background: #fff !important; border-bottom: 1px solid #ebebeb; }
.sk1370-header .brand { color: #173228 !important; }
.sk1370-header nav a { color: #666; font-weight: bold; }
.sk1370-header nav a:hover { color: #176b46; }

.market-hero { background: #fff; padding: 60px 20px; border-bottom: 1px solid #ebebeb; display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; margin-bottom: 40px; border-radius: 0 0 8px 8px; }
@media (max-width: 900px) { .market-hero { flex-direction: column; } }
.market-hero > div { flex: 1; }
.market-hero .eyebrow { display: block; color: #176b46; font-weight: bold; font-size: 13px; letter-spacing: 1px; margin-bottom: 15px; text-transform: uppercase; }
.market-hero h1 { font-size: 42px; font-weight: 900; color: #173228; margin: 0 0 15px 0; line-height: 1.1; letter-spacing: -1px; }
.market-hero p { font-size: 18px; color: #555; line-height: 1.6; margin-bottom: 30px; }
.market-hero form { display: flex; flex-wrap: wrap; gap: 15px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ebebeb; align-items: center; }
.market-hero input { flex: 1; min-width: 200px; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
.market-hero button { background: #176b46; color: #fff; border: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 15px; }
.market-hero button:hover { background: #125235; }

.market-hero aside { width: 320px; background: #e9f5ee; padding: 30px; border-radius: 8px; text-align: center; display: flex; flex-direction: column; justify-content: center; }
.market-hero aside b { font-size: 20px; color: #173228; margin-bottom: 10px; display: block; font-weight: 800; }
.market-hero aside p { font-size: 15px; color: #176b46; line-height: 1.5; margin-bottom: 20px; }
.market-hero aside a { background: #173228; color: #fff; font-weight: bold; padding: 12px; border-radius: 4px; text-decoration: none; }

.cat-strip { background: #fff; padding: 50px 0; border-top: 1px solid #ebebeb; border-bottom: 1px solid #ebebeb; margin-bottom: 40px; }
.section-title { max-width: 1200px; margin: 0 auto; margin-bottom: 30px; padding: 0 20px; }
.section-title span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.section-title h2 { font-size: 28px; font-weight: 800; color: #173228; margin: 0; }
.cat-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; padding: 0 20px; }
.cat-grid a { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 20px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; text-align: center; display: flex; flex-direction: column; align-items: center; }
.cat-grid a:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.cat-grid span { font-size: 32px; margin-bottom: 15px; background: #e9f5ee; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: #176b46; }
.cat-grid b { color: #173228; font-size: 18px; font-weight: 800; margin-bottom: 8px; }
.cat-grid small { color: #666; font-size: 13px; line-height: 1.4; }

.market-layout { max-width: 1200px; margin: 0 auto 60px auto; display: flex; gap: 40px; padding: 0 20px; }
@media (max-width: 900px) { .market-layout { flex-direction: column; } }
.filter-panel { width: 300px; flex-shrink: 0; background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 25px; align-self: flex-start; }
.filter-panel h3 { font-size: 18px; font-weight: 800; color: #173228; margin: 0 0 20px 0; border-bottom: 1px solid #ebebeb; padding-bottom: 10px; }
.filter-panel label { display: block; font-size: 14px; font-weight: bold; color: #333; margin-bottom: 15px; }
.filter-panel select, .filter-panel input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; font-family: inherit; font-size: 14px; box-sizing: border-box; }
.filter-panel .two { display: flex; gap: 10px; }
.filter-panel button { background: #176b46; color: #fff; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 14px; width: 100%; margin-top: 10px; }
.filter-panel a.clear { display: block; text-align: center; color: #666; text-decoration: none; font-size: 14px; margin-top: 15px; }
.filter-panel a.clear:hover { color: #e00707; }

.results { flex: 1; }
.results header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #ebebeb; }
.results header span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.results header h2 { font-size: 28px; font-weight: 800; color: #173228; margin: 0; }
.results header form { display: flex; align-items: center; gap: 10px; }
.results header select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; }
.results header button.save-search { background: #f5f6f5; color: #173228; border: 1px solid #ccc; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; }

.ad-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
.sk1370-ad-card { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; text-decoration: none; }
.sk1370-ad-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.sk1370-ad-card .media { height: 180px; background-color: #f5f6f5; background-size: cover; background-position: center; position: relative; }
.sk1370-ad-card .media span { position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: #fff; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px; }
.sk1370-ad-card .info { padding: 15px; flex: 1; display: flex; flex-direction: column; }
.sk1370-ad-card .info h3 { font-size: 16px; font-weight: bold; color: #333; margin: 0 0 8px 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.sk1370-ad-card .info strong { color: #176b46; font-size: 18px; margin-bottom: 8px; display: block; font-weight: 900; }
.sk1370-ad-card .info p { font-size: 13px; color: #666; margin: 0 0 12px 0; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.sk1370-ad-card .info footer { display: flex; justify-content: space-between; font-size: 12px; color: #999; border-top: 1px solid #f5f6f5; padding-top: 10px; }
</style><section class="market-hero"><div><span class="eyebrow">LOCAL CLASSIFIEDS · SHAHKOT</span><h1>Buy, sell and discover local opportunities.</h1><p>Find nearby items, property, vehicles, electronics, jobs and services from the Shahkot community.</p><form class="hero-search"><input name="q" value="<?=sk1370_h($f['q'])?>" placeholder="What are you looking for?"><input name="area" value="<?=sk1370_h($f['area'])?>" placeholder="Area in Shahkot"><button>Search</button></form></div><aside><b>Sell locally</b><p>Create a detailed ad with photos, price, location and direct in-app inquiries.</p><a href="/post-ad.php">Post an Ad</a></aside></section>
<section class="cat-strip"><div class="section-title"><div><span>EXPLORE</span><h2>Browse Categories</h2></div></div><div class="cat-grid"><?php foreach($categories as $c)if(!(int)($c['parent_id']??0)):?><a href="?category=<?=$c['id']?>"><span><?=sk1370_h($c['icon']?:'▣')?></span><b><?=sk1370_h($c['name'])?></b><small><?=sk1370_h($c['description'])?></small></a><?php endif;?></div></section>
<section class="market-layout"><aside class="filter-panel"><form><input type="hidden" name="q" value="<?=sk1370_h($f['q'])?>"><h3>Filters</h3><label>Category<select name="category"><option value="0">All Categories</option><?php foreach($categories as $c):?><option value="<?=$c['id']?>" <?=$f['category_id']==$c['id']?'selected':''?>><?=sk1370_h(($c['parent_id']?'— ':'').$c['name'])?></option><?php endforeach;?></select></label><label>Condition<select name="condition"><option value="">Any condition</option><?php foreach(['new'=>'New','like_new'=>'Like new','used'=>'Used','refurbished'=>'Refurbished','not_applicable'=>'Not applicable'] as $k=>$v):?><option value="<?=$k?>" <?=$f['condition']===$k?'selected':''?>><?=$v?></option><?php endforeach;?></select></label><label>Area<input name="area" value="<?=sk1370_h($f['area'])?>" placeholder="e.g. Circular Road"></label><div class="two"><label>Min price<input type="number" min="0" name="min" value="<?=sk1370_h($f['price_min'])?>"></label><label>Max price<input type="number" min="0" name="max" value="<?=sk1370_h($f['price_max'])?>"></label></div><?php foreach($filterFields as $ff):$cur=$f['field_filters'][(int)$ff['id']]??'';?><label><?=sk1370_h($ff['label'])?><?php if($ff['field_type']==='select'):$opts=json_decode((string)$ff['options_json'],true)?:[];?><select name="cf_<?=$ff['id']?>"><option value="">Any</option><?php foreach($opts as $o):?><option value="<?=sk1370_h($o)?>" <?=$cur===(string)$o?'selected':''?>><?=sk1370_h($o)?></option><?php endforeach;?></select><?php else:?><input name="cf_<?=$ff['id']?>" value="<?=sk1370_h($cur)?>"><?php endif;?></label><?php endforeach;?><button>Apply Filters</button><a class="clear" href="/classifieds.php">Clear all</a></form></aside><div class="results"><header><div><span>SHAHKOT MARKETPLACE</span><h2><?=number_format($res['total'])?> ads found</h2></div><form><input type="hidden" name="q" value="<?=sk1370_h($f['q'])?>"><input type="hidden" name="category" value="<?=$f['category_id']?>"><select name="sort" onchange="this.form.submit()"><option value="">Newest first</option><option value="price_asc" <?=$f['sort']==='price_asc'?'selected':''?>>Price: low to high</option><option value="price_desc" <?=$f['sort']==='price_desc'?'selected':''?>>Price: high to low</option><option value="oldest" <?=$f['sort']==='oldest'?'selected':''?>>Oldest first</option></select></form><?php if(sk1370_uid()&&($_SERVER['QUERY_STRING']??'')!==''):?><button type="button" class="save-search" data-save-search="<?=sk1370_h($_SERVER['QUERY_STRING'])?>">Save Search</button><?php endif;?></header><div class="ad-grid"><?php if(!$res['rows']):?><div class="empty"><b>No ads match these filters.</b><p>Try another category, price range or location.</p></div><?php else:foreach($res['rows'] as $a)sk1370_ad_card($a);endif;?></div><?php if($res['pages']>1):?><nav class="pager"><?php for($i=max(1,$res['page']-2);$i<=min($res['pages'],$res['page']+2);$i++):$qs=$_GET;$qs['page']=$i;?><a class="<?=$i===$res['page']?'on':''?>" href="?<?=sk1370_h(http_build_query($qs))?>"><?=$i?></a><?php endfor;?></nav><?php endif;?></div></section><?php sk1370_end();
