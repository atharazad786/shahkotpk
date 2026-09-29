<?php
declare(strict_types=1);

function lt_section(array $sections,string $type): ?array {
    foreach($sections as $s)if(!empty($s['enabled'])&&($s['type']??'')===$type)return $s;
    return null;
}
function lt_items(?array $s): array {return $s?section_items((string)($s['items']??'')):[];}
function lt_heading_html(array $s): string {return '<div class="lt-heading"><span>'.e($s['subtitle']).'</span><h2>'.e($s['title']).'</h2><p>'.e($s['content']).'</p></div>';}

function lt_header(array $sections,string $variant='default'): void {
    // Global header is rendered via app/views/header.php — skip native landing header to prevent duplicates.
    return;
}
function lt_topbar(array $sections): void {
    $s=lt_section($sections,'topbar');if(!$s)return;
    echo '<div class="lt-topbar cgp-utility"><div class="lt-shell"><div><span>⌖ '.e(setting('default_city','Shahkot')).'</span><span class="js-city-time">Pakistan Standard Time</span><span>☁ '.e(setting('city_weather_text','Weather updates available from Admin')).'</span><a href="/city-guide.php">'.e(setting('city_emergency_text','Emergency: Rescue 1122')).'</a></div><div>';
    foreach(city_portal_cities() as $c)echo '<a href="/search.php?city='.urlencode($c['slug']).'">'.e($c['name']).'</a>';
    echo '</div></div></div>';render_ticker('public');
}
function lt_slider(array $slides,string $variant='default'): void {
    if(!feature_enabled('homepage_slider_enabled',true)||!$slides)return;
    echo '<style>
    .yelp-slider-wrapper { position: relative; margin: 0; width: 100%; overflow: hidden; height: 500px; }
    .lt-slide.yelp-slide { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transform: scale(1.05); transition: all 0.8s cubic-bezier(0.25, 0.8, 0.25, 1); }
    .lt-slide.yelp-slide.is-active { opacity: 1; transform: scale(1); }
    .yelp-slide-overlay { position: absolute; inset: 0; background: linear-gradient(to right, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 100%); display: flex; align-items: center; }
    .yelp-slide-content { padding: 0 5%; max-width: 800px; color: #fff; }
    .yelp-slide-sub { font-size: 14px; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; color: #4ade80; margin-bottom: 12px; display: inline-block; background: rgba(74, 222, 128, 0.2); padding: 4px 12px; border-radius: 50px; }
    .yelp-slide-title { font-size: clamp(32px, 4.5vw, 60px); font-weight: 950; margin-bottom: 20px; line-height: 1.1; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
    .yelp-slide-text { font-size: 18px; color: #e2e8f0; margin-bottom: 35px; font-weight: 400; line-height: 1.6; max-width: 600px; }
    .yelp-slide-btn { display: inline-block; padding: 15px 35px; background: #fff; color: #0f172a; border-radius: 12px; font-weight: 900; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; font-size: 16px; }
    .yelp-slide-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255,255,255,0.25); background: #f8fafc; }
    .yelp-slider-nav { position: absolute; bottom: 30px; right: 5%; display: flex; gap: 12px; z-index: 10; }
    .yelp-slider-nav button { width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); color: #fff; font-size: 24px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
    .yelp-slider-nav button:hover { background: #fff; color: #000; }
    @media (max-width: 768px) { .yelp-slider-wrapper { height: 450px; } .yelp-slide-content { text-align: center; margin: 0 auto; padding: 0 20px; } .yelp-slide-overlay { background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 100%); align-items: flex-end; padding-bottom: 80px; } .yelp-slider-nav { bottom: 20px; right: 50%; transform: translateX(50%); } }
    </style>';
    
    echo '<section class="yelp-slider-wrapper" data-lt-slider>';
    foreach($slides as $i=>$sl) {
        echo '<article class="lt-slide yelp-slide '.($i===0?'is-active':'').'" '.($sl['image']?'style="background-image:url(\''.e($sl['image']).'\')"':'').'>';
        echo '<div class="yelp-slide-overlay"><div class="yelp-slide-content">';
        echo '<span class="yelp-slide-sub">'.e($sl['subtitle']).'</span>';
        echo '<h2 class="yelp-slide-title">'.e($sl['title']).'</h2>';
        echo '<p class="yelp-slide-text">'.e($sl['text']).'</p>';
        echo '<a class="yelp-slide-btn" href="'.e(safe_link($sl['button_url'])).'">'.e($sl['button_text']).'</a>';
        echo '</div></div></article>';
    }
    if(count($slides)>1) {
        echo '<div class="yelp-slider-nav"><button data-lt-prev>❮</button><button data-lt-next>❯</button></div>';
    }
    echo '</section>';
}
function lt_hero(array $sections,array $stats,string $variant='default'): void {
    $s=lt_section($sections,'hero');if(!$s||!feature_enabled('homepage_search_enabled',true))return;$items=lt_items($s);
    echo '<style>
    .yelp-hero-premium { padding: 90px 0 70px; background: linear-gradient(135deg, #f8fcf9, #e6f2eb); text-align: center; border-bottom: 1px solid #d9e8df; }
    .yelp-hero-inner { max-width: 950px; margin: 0 auto; padding: 0 20px; }
    .yelp-hero-premium h1 { font-size: clamp(36px, 5vw, 64px); font-weight: 950; color: #173228; margin-bottom: 15px; letter-spacing: -1.5px; line-height: 1.1; }
    .yelp-hero-premium p { font-size: 18px; color: #6c7e74; margin-bottom: 40px; font-weight: 500; }
    
    .yelp-search-wrapper { background: #fff; border: 1px solid #dfe8e1; border-radius: 12px; padding: 8px; box-shadow: 0 20px 40px rgba(23, 107, 70, 0.08); display: flex; align-items: stretch; margin-bottom: 40px; transition: box-shadow 0.3s ease; }
    .yelp-search-wrapper:hover { box-shadow: 0 25px 50px rgba(23, 107, 70, 0.12); }
    
    .yelp-search-col { flex: 1; display: flex; align-items: center; padding: 5px 20px; position: relative; }
    .yelp-search-col:first-child::after { content: ""; position: absolute; right: 0; top: 15%; height: 70%; width: 1px; background: #dfe8e1; }
    
    .yelp-search-icon { font-size: 20px; color: #176b46; margin-right: 12px; font-weight: 900; }
    .yelp-search-col input, .yelp-search-col select { border: none; outline: none; width: 100%; font-size: 16px; color: #173228; background: transparent; font-weight: 600; }
    .yelp-search-col input::placeholder { color: #a3b3a9; font-weight: 500; }
    
    .yelp-search-btn-premium { background: #176b46; color: #fff; border: none; padding: 0 45px; border-radius: 8px; font-size: 18px; font-weight: 800; cursor: pointer; transition: background 0.2s, transform 0.1s; display: flex; align-items: center; justify-content: center; }
    .yelp-search-btn-premium:hover { background: #125537; transform: translateY(-1px); }
    
    .yelp-cats-premium { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; }
    .yelp-cat-item { display: flex; align-items: center; gap: 8px; background: #fff; color: #173228; text-decoration: none; font-size: 14px; font-weight: 700; padding: 10px 20px; border: 1px solid #dfe8e1; border-radius: 100px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); transition: all 0.2s ease; }
    .yelp-cat-item:hover { background: #176b46; color: #fff; border-color: #176b46; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(23,107,70,0.2); }
    .yelp-cat-item i { font-style: normal; font-size: 18px; opacity: 0.9; }
    
    @media (max-width: 768px) {
        .yelp-search-wrapper { flex-direction: column; background: transparent; box-shadow: none; border: none; padding: 0; gap: 10px; }
        .yelp-search-col { background: #fff; border: 1px solid #dfe8e1; border-radius: 12px; padding: 15px 20px; }
        .yelp-search-col:first-child::after { display: none; }
        .yelp-search-btn-premium { padding: 18px; border-radius: 12px; }
        .yelp-cats-premium { gap: 10px; }
        .yelp-cat-item { padding: 8px 16px; font-size: 13px; }
    }
    </style>';
    
    echo '<section class="yelp-hero-premium"><div class="lt-shell yelp-hero-inner">';
    echo '<h1>Find everything in '.e(setting('default_city','Shahkot')).'</h1>';
    echo '<p>Discover local businesses, restaurants, doctors, deals, and much more.</p>';
    
    if(feature_enabled('search_enabled',true)){
        echo '<form class="yelp-search-wrapper" action="/search.php">';
        
        echo '<div class="yelp-search-col">';
        echo '<span class="yelp-search-icon">🔍</span>';
        echo '<input type="text" name="q" placeholder="plumbers, delivery, takeout...">';
        echo '</div>';
        
        echo '<div class="yelp-search-col">';
        echo '<span class="yelp-search-icon">📍</span>';
        echo '<select name="category"><option value="">All Categories</option>';
        foreach(city_portal_category_counts(15) as $c) echo '<option value="'.e($c['slug']).'">'.e($c['name']).'</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<button class="yelp-search-btn-premium" type="submit">Search</button>';
        echo '</form>';
    }
    
    echo '<div class="yelp-cats-premium">';
    echo '<a href="/doctor-online.php" class="yelp-cat-item"><i>🩺</i> Doctors</a>';
    echo '<a href="/shop.php" class="yelp-cat-item"><i>🛍️</i> Marketplace</a>';
    echo '<a href="/deals.php" class="yelp-cat-item"><i>🏷️</i> Deals & Offers</a>';
    echo '<a href="/jobs.php" class="yelp-cat-item"><i>💼</i> Local Jobs</a>';
    echo '<a href="/property.php" class="yelp-cat-item"><i>🏡</i> Real Estate</a>';
    echo '<a href="/businesses.php" class="yelp-cat-item"><i>🏢</i> Directory</a>';
    echo '</div>';
    
    echo '</div></section>';
}
function lt_info(array $sections,string $variant='default'): void {$s=lt_section($sections,'info_cards');if(!$s||!feature_enabled('homepage_city_info_enabled',true))return;echo '<section class="lt-info lt-info-'.$variant.'"><div class="lt-shell lt-info-grid">';foreach(lt_items($s) as $it)echo '<article><b>'.e($it['title']).'</b><span class="'.(stripos($it['title'],'time')!==false?'js-city-time':'').'">'.e($it['text']).'</span></article>';echo '</div></section>';}
function lt_services(array $sections,string $variant='default'): void {$s=lt_section($sections,'quick_services');if(!$s)return;echo '<section class="lt-section lt-services lt-services-'.$variant.'"><div class="lt-shell">'.lt_heading_html($s).'<div class="lt-service-grid">';foreach(lt_items($s) as $it)echo '<a href="'.e(safe_link($it['link'])).'"><i>'.e(mb_strtoupper(mb_substr($it['title'],0,1))).'</i><b>'.e($it['title']).'</b><span>'.e($it['text']).'</span></a>';echo '</div></div></section>';}
function lt_directory(array $sections,array $categories,string $variant='default'): void {$s=lt_section($sections,'directory');if(!$s||!feature_enabled('directory_enabled',true))return;echo '<section class="lt-section lt-directory lt-directory-'.$variant.'"><div class="lt-shell">'.lt_heading_html($s).'<div class="lt-category-grid">';foreach($categories as $c)echo '<a href="/search.php?category='.urlencode($c['slug']).'"><i>'.e(mb_strtoupper(mb_substr($c['name'],0,1))).'</i><b>'.e($c['name']).'</b><span>Explore listings →</span></a>';echo '</div></div></section>';}
function lt_featured(array $sections,array $featured,string $variant='default'): void {
    $s=lt_section($sections,'featured');if(!$s||!feature_enabled('homepage_featured_enabled',true))return;
    echo '<style>
    .yelp-featured-section { padding: 80px 0; background: #fff; }
    .yelp-section-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
    .yelp-section-head h2 { font-size: 32px; font-weight: 800; color: #173228; margin: 0; letter-spacing: -0.5px; }
    .yelp-section-head p { font-size: 16px; color: #6c7e74; margin: 8px 0 0 0; }
    .yelp-business-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px; }
    .yelp-b-card { background: #fff; border: 1px solid #dfe8e1; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease; text-decoration: none; color: inherit; }
    .yelp-b-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(23,107,70,0.12); border-color: transparent; }
    .yelp-b-cover { height: 210px; background-size: cover; background-position: center; background-color: #f0f4f2; position: relative; }
    .yelp-b-badges { position: absolute; top: 12px; left: 12px; display: flex; gap: 6px; }
    .yelp-b-badge { background: #fff; color: #173228; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .yelp-b-badge.verified { background: #176b46; color: #fff; display: flex; align-items: center; gap: 4px; }
    .yelp-b-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
    .yelp-b-title { font-size: 20px; font-weight: 800; color: #173228; margin: 0 0 6px 0; }
    .yelp-b-meta { font-size: 13px; color: #6c7e74; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .yelp-b-meta span { display: inline-flex; align-items: center; }
    .yelp-b-cat { color: #176b46; font-weight: 700; background: #e9f5ee; padding: 3px 8px; border-radius: 4px; }
    .yelp-b-status { font-size: 13px; font-weight: 700; display: inline-block; margin-top: auto; }
    .yelp-b-status.open { color: #176b46; }
    .yelp-b-status.closed { color: #e00707; }
    .yelp-b-actions { display: flex; gap: 8px; margin-top: 15px; border-top: 1px solid #f0f4f2; padding-top: 15px; }
    .yelp-b-btn { flex: 1; text-align: center; background: #f8fafc; color: #173228; font-size: 13px; font-weight: 700; padding: 10px 0; border-radius: 8px; text-decoration: none; transition: background 0.2s; border: 1px solid #dfe8e1; }
    .yelp-b-btn:hover { background: #e9f5ee; color: #176b46; border-color: #c3d9cc; }
    @media(max-width: 768px){ .yelp-section-head { flex-direction: column; align-items: flex-start; } .yelp-business-grid { grid-template-columns: 1fr; } }
    </style>';
    
    echo '<section class="yelp-featured-section"><div class="lt-shell">';
    echo '<div class="yelp-section-head"><div><h2>'.e($s['title']).'</h2><p>'.e($s['content']).'</p></div></div>';
    
    echo '<div class="yelp-business-grid">';
    if(!$featured)echo '<div class="lt-empty">Featured businesses will appear here.</div>';
    
    foreach($featured as $b){
        $hours=business_open_status((int)$b['id']);
        $wa=preg_replace('/\D/','',(string)$b['whatsapp']);
        
        echo '<a href="/business.php?slug='.urlencode($b['slug']).'" class="yelp-b-card">';
        echo '<div class="yelp-b-cover" '.($b['image']?'style="background-image:url(\''.e($b['image']).'\')"':'').'>';
        echo '<div class="yelp-b-badges">';
        echo '<span class="yelp-b-badge">FEATURED</span>';
        if($b['verification_status']==='verified') echo '<span class="yelp-b-badge verified">✓ VERIFIED</span>';
        echo '</div></div>';
        
        echo '<div class="yelp-b-body">';
        echo '<h3 class="yelp-b-title">'.e($b['name']).'</h3>';
        echo '<div class="yelp-b-meta"><span class="yelp-b-cat">'.e($b['category_name']).'</span> • <span>📍 '.e($b['city_name']).'</span></div>';
        echo '<div class="yelp-b-meta"><span>'.e($b['address']).'</span></div>';
        
        echo '<span class="yelp-b-status '.($hours['known']?($hours['open']?'open':'closed'):'unknown').'">'.($hours['known']?($hours['open']?'Open Now':'Closed'):'').'</span>';
        
        echo '<div class="yelp-b-actions">';
        if($b['phone']) echo '<object><a href="tel:'.e($b['phone']).'" class="yelp-b-btn">📞 Call</a></object>';
        if($wa) echo '<object><a target="_blank" href="https://wa.me/'.e($wa).'" class="yelp-b-btn">💬 WhatsApp</a></object>';
        echo '</div>';
        
        echo '</div></a>';
    }
    echo '</div></div></section>';
}
function lt_cta(array $sections,string $variant='default'): void {$s=lt_section($sections,'cta');if(!$s)return;echo '<section class="lt-section lt-cta lt-cta-'.$variant.'"><div class="lt-shell"><div class="lt-cta-inner"><div>'.lt_heading_html($s).'</div><div>';foreach(lt_items($s) as $it)echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).' →</a>';echo '</div></div></div></section>';}
function lt_content(array $sections,string $variant='default'): void {$s=lt_section($sections,'content');if(!$s)return;echo '<section class="lt-section lt-content lt-content-'.$variant.'"><div class="lt-shell"><div class="lt-content-grid"><div>'.lt_heading_html($s).'</div><div class="lt-content-art" '.($s['image']?'style="background-image:url(\''.e($s['image']).'\')"':'').'></div></div></div></section>';}
function lt_spotlights(array $sections,string $variant='default'): void {$s=lt_section($sections,'spotlights');if(!$s)return;echo '<section class="lt-section lt-spots lt-spots-'.$variant.'"><div class="lt-shell">'.lt_heading_html($s).'<div class="lt-spot-grid">';foreach(lt_items($s) as $it)echo '<a href="'.e(safe_link($it['link'])).'" '.($it['image']?'style="background-image:url(\''.e($it['image']).'\')"':'').'><div><b>'.e($it['title']).'</b><span>'.e($it['text']).'</span><em>Explore →</em></div></a>';echo '</div></div></section>';}
function lt_gallery(array $sections,string $variant='default'): void {$s=lt_section($sections,'gallery');if(!$s||!feature_enabled('homepage_gallery_enabled',true))return;echo '<section class="lt-section lt-gallery lt-gallery-'.$variant.'"><div class="lt-shell">'.lt_heading_html($s).'<div class="lt-gallery-grid">';foreach(lt_items($s) as $it)echo '<article '.($it['image']?'style="background-image:url(\''.e($it['image']).'\')"':'').'><div><b>'.e($it['title']).'</b><span>'.e($it['text']).'</span></div></article>';echo '</div></div></section>';}
function lt_links(array $sections,string $variant='default'): void {$s=lt_section($sections,'links');if(!$s)return;echo '<section class="lt-section lt-links lt-links-'.$variant.'"><div class="lt-shell">'.lt_heading_html($s).'<div class="lt-link-grid">';foreach(lt_items($s) as $it)echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).'</a>';echo '</div></div></section>';}
function lt_footer(array $sections): void {$s=lt_section($sections,'footer');if(!$s)return;echo '<footer class="lt-footer"><div class="lt-shell"><div><b>'.e(setting('site_name',$s['title'])).'</b><span>'.e($s['subtitle']).'</span></div><nav>';foreach(lt_items($s) as $it)echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).'</a>';echo '</nav><small>'.e($s['content']).'</small></div></footer>';}


function lt_portal_heading(array $s,string $url,string $label='View All'): string {return '<div class="cgp-section-head">'.lt_heading_html($s).'<a href="'.e($url).'">'.e($label).' →</a></div>';}
function lt_category_explorer(array $sections,array $ctx): void {$s=lt_section($sections,'category_explorer');if(!$s||!setting_bool('homepage_category_explorer_enabled',true))return;echo '<section class="lt-section cgp-category-section"><div class="lt-shell">'.lt_portal_heading($s,'/search.php').'<div class="cgp-category-grid">';foreach(($ctx['categoryCounts'] ?? []) as $c)echo '<a href="/search.php?category='.urlencode($c['slug']).'"><i>'.e(mb_strtoupper(mb_substr($c['name'],0,1))).'</i><span><b>'.e($c['name']).'</b><small>'.e($c['business_count']).' local listings</small></span><em>→</em></a>';echo '</div></div></section>';}
function lt_deals(array $sections,array $ctx): void {$s=lt_section($sections,'deals');if(!$s||!setting_bool('homepage_deals_enabled',true))return;echo '<section class="lt-section cgp-deals"><div class="lt-shell">'.lt_portal_heading($s,'/deals.php').'<div class="cgp-deal-grid">';foreach(($ctx['deals'] ?? []) as $r){$m=is_array($r['meta']??null)?$r['meta']:[];echo '<article><div class="cgp-item-image" '.($r['image_url']?'style="background-image:url(\''.e($r['image_url']).'\')"':'').'><span>'.e(($m['discount_label']??'')?:'LOCAL DEAL').'</span></div><div><small>SPONSORED OFFER · '.e($r['business_name']?:$r['city_name']).'</small><h3>'.e($r['title']).'</h3><p>'.e($r['summary']).'</p>'.($r['secondary_price']>0?'<div class="cgp-price"><b>Rs '.e(number_format((float)$r['secondary_price'])).'</b><del>Rs '.e(number_format((float)$r['price'])).'</del></div>':'').'<a href="/deals.php?slug='.urlencode($r['slug']).'">View Offer →</a></div></article>';}if(empty($ctx['deals']))echo '<div class="lt-empty">Local deals will appear here when published from Admin.</div>';echo '</div></div></section>';}
function lt_sponsored_spotlight(array $sections): void {$s=lt_section($sections,'sponsored_spotlight');if(!$s||!setting_bool('homepage_sponsored_spotlight_enabled',true)||!feature_enabled('advertisements_enabled',true)||!function_exists('active_ad_for_placement'))return;$ad=active_ad_for_placement('sponsored_spotlight');if(!$ad)$ad=active_ad_for_placement('homepage_mid');if(!$ad)return;echo '<section class="cgp-sponsor-spotlight"><div class="lt-shell"><div class="cgp-sponsor-card"><div class="cgp-sponsor-art" '.($ad['image_url']?'style="background-image:url(\''.e($ad['image_url']).'\')"':'').'></div><div><span>SPONSORED BUSINESS SPOTLIGHT</span><h2>'.e($ad['title']).'</h2><p>'.e($ad['description']?:($ad['business_name']?:'Promoted on ShahkotPK')).'</p><a href="/ad-click.php?id='.e($ad['id']).'">View Promotion →</a></div><em>ADVERTISEMENT</em></div></div></section>';}
function lt_business_collection(array $s,array $rows,string $class,string $allUrl): void {echo '<section class="lt-section '.$class.'"><div class="lt-shell">'.lt_portal_heading($s,$allUrl).'<div class="cgp-local-business-grid">';foreach($rows as $b){$h=$b['open_status']??business_open_status((int)$b['id']);echo '<article data-near-business data-lat="'.e($b['latitude']??'').'" data-lng="'.e($b['longitude']??'').'"><div class="cgp-local-image" '.($b['image']?'style="background-image:url(\''.e($b['image']).'\')"':'').'></div><div><span class="'.($h['known']?($h['open']?'open':'closed'):'unknown').'">'.e($h['label']).'</span><h3>'.e($b['name']).'</h3><p>'.e($b['category_name']).' · '.e($b['address']).'</p><a href="/business.php?slug='.urlencode($b['slug']).'">View Business →</a></div></article>';}if(!$rows)echo '<div class="lt-empty">Listings will appear here as your local directory grows.</div>';echo '</div></div></section>';}
function lt_new_businesses(array $sections,array $ctx): void {$s=lt_section($sections,'new_businesses');if($s&&setting_bool('homepage_new_businesses_enabled',true))lt_business_collection($s,$ctx['newBusinesses']??[],'cgp-new-businesses','/search.php');}
function lt_nearby(array $sections,array $ctx): void {$s=lt_section($sections,'nearby');if($s&&setting_bool('homepage_nearby_enabled',true)){echo '<div id="nearbySection">';lt_business_collection($s,$ctx['nearbyBusinesses']??[],'cgp-nearby','/search.php');echo '</div>';}}
function lt_restaurants(array $sections,array $ctx): void {$s=lt_section($sections,'restaurants');if($s&&setting_bool('homepage_restaurants_enabled',true))lt_business_collection($s,$ctx['restaurants']??[],'cgp-restaurants','/search.php?q=restaurant');}
function lt_city_guide_portal(array $sections,array $ctx): void {$s=lt_section($sections,'city_guide');if(!$s||!setting_bool('homepage_city_guide_enabled',true))return;echo '<section class="lt-section cgp-guide"><div class="lt-shell">'.lt_portal_heading($s,'/city-guide.php').'<div class="cgp-guide-grid">';foreach(($ctx['guides']??[]) as $r)echo '<a href="/city-guide.php?slug='.urlencode($r['slug']).'" '.($r['image_url']?'style="background-image:url(\''.e($r['image_url']).'\')"':'').'><div><span>'.e($r['category']?:'CITY GUIDE').'</span><h3>'.e($r['title']).'</h3><p>'.e($r['summary']).'</p><em>Explore →</em></div></a>';if(empty($ctx['guides']))echo '<div class="lt-empty">Add hospitals, schools, markets, offices and important places from Admin → City Guide & Local Content.</div>';echo '</div></div></section>';}
function lt_events(array $sections,array $ctx): void {$s=lt_section($sections,'events');if(!$s||!setting_bool('homepage_events_enabled',true))return;echo '<section class="lt-section cgp-events"><div class="lt-shell">'.lt_portal_heading($s,'/events.php').'<div class="cgp-event-grid">';foreach(($ctx['events']??[]) as $r){$m=is_array($r['meta']??null)?$r['meta']:[];echo '<article><div class="cgp-event-date"><b>'.e($r['starts_at']?date('d',strtotime($r['starts_at'])):'—').'</b><span>'.e($r['starts_at']?date('M',strtotime($r['starts_at'])):'EVENT').'</span></div><div><small>'.e(($m['venue']??'')?:$r['address']).'</small><h3>'.e($r['title']).'</h3><p>'.e($r['summary']).'</p><a href="/events.php?slug='.urlencode($r['slug']).'">Event Details →</a></div></article>';}if(empty($ctx['events']))echo '<div class="lt-empty">Upcoming city events will appear here.</div>';echo '</div></div></section>';}
function lt_jobs(array $sections,array $ctx): void {$s=lt_section($sections,'jobs');if(!$s||!setting_bool('homepage_jobs_enabled',true))return;echo '<section class="lt-section cgp-jobs"><div class="lt-shell">'.lt_portal_heading($s,'/jobs.php').'<div class="cgp-job-list">';foreach(($ctx['jobs']??[]) as $r){$m=is_array($r['meta']??null)?$r['meta']:[];echo '<a href="/jobs.php?slug='.urlencode($r['slug']).'"><i>▣</i><span><b>'.e($r['title']).'</b><small>'.e($r['business_name']?:'Local Employer').' · '.e(($m['employment_type']??'')?:'Local Job').'</small></span><strong>'.e(($m['salary_text']??'')?:'View Details').'</strong><em>→</em></a>';}if(empty($ctx['jobs']))echo '<div class="lt-empty">Local vacancies will appear here.</div>';echo '</div></div></section>';}
function lt_property(array $sections,array $ctx): void {$s=lt_section($sections,'property');if(!$s||!setting_bool('homepage_property_enabled',true))return;echo '<section class="lt-section cgp-property"><div class="lt-shell">'.lt_portal_heading($s,'/property.php').'<div class="cgp-property-grid">';foreach(($ctx['properties']??[]) as $r){$m=is_array($r['meta']??null)?$r['meta']:[];echo '<article><div class="cgp-property-image" '.($r['image_url']?'style="background-image:url(\''.e($r['image_url']).'\')"':'').'><span>'.e(strtoupper(($m['listing_type']??'')?:'LISTING')).'</span></div><div><small>'.e(($m['property_type']??'')?:$r['category']).' · '.e(($m['area_text']??'')?:'Shahkot').'</small><h3>'.e($r['title']).'</h3><b>Rs '.e(number_format((float)$r['price'])).'</b><a href="/property.php?slug='.urlencode($r['slug']).'">View Property →</a></div></article>';}if(empty($ctx['properties']))echo '<div class="lt-empty">Property listings will appear here.</div>';echo '</div></div></section>';}
function lt_advertise_cta(array $sections): void {$s=lt_section($sections,'advertise_cta');if(!$s||!setting_bool('homepage_advertise_cta_enabled',true))return;echo '<section class="lt-section cgp-advertise"><div class="lt-shell"><div class="cgp-advertise-card"><div><span>LOCAL ADVERTISING</span><h2>'.e($s['title']).'</h2><p>'.e($s['content']).'</p></div><div class="cgp-ad-placements"><span>Hero Ads</span><span>Sponsored Businesses</span><span>Category Sponsor</span><span>Deals & Events</span></div><a href="/pricing.php">Advertise on ShahkotPK →</a></div></div></section>';}

function lt_news_widgets(array $sections): void {
    $s=lt_section($sections,'news_widgets');if(!$s||!setting_bool('news_portal_enabled',true)||!setting_bool('news_home_enabled',true))return;
    $widgets=news_widgets(true);if(!$widgets)return;
    echo '<section class="news-home-section"><div class="lt-shell"><div class="news-home-heading"><div><span>'.e($s['subtitle']?:'SHAHKOTPK NEWSROOM').'</span><h2>'.e($s['title']?:'Latest News & Video').'</h2><p>'.e($s['content']?:'Breaking, English, Urdu and video news from the local newsroom.').'</p></div><a href="/news.php">Open News Portal →</a></div>';
    foreach($widgets as $w){$posts=news_widget_posts($w);$type=(string)$w['widget_type'];$lang=(string)$w['language'];if(!$posts)continue;
        if($type==='breaking'&&setting_bool('news_breaking_enabled',true)){
            echo '<div class="news-breaking"><strong>'.e(news_widget_title($w,'en')).'</strong><div>';foreach($posts as $p)echo '<a href="/news.php?slug='.urlencode($p['slug']).'">'.e(news_display_title($p,$p['language']==='ur'?'ur':'en')).'</a>';echo '</div></div>';continue;
        }
        $rtl=$lang==='ur';echo '<div class="news-widget-block '.($rtl?'news-rtl':'').' news-widget-'.$type.'"><div class="news-widget-title"><div><span>'.e(strtoupper($type)).'</span><h3>'.e(news_widget_title($w,$rtl?'ur':'en')).'</h3></div><a href="/news.php'.($lang==='ur'?'?lang=ur':($lang==='en'?'?lang=en':'' )).'">View All →</a></div>';
        if($type==='featured'){
            $lead=array_shift($posts);echo '<div class="news-feature-layout"><article class="news-lead-card"><div class="news-media" '.(news_image($lead)?'style="background-image:url(\''.e(news_image($lead)).'\')"':'').'><span>'.e($lead['category_name_en']?:'News').'</span>'.($lead['post_type']==='video'?'<i>▶</i>':'').'</div><div><small>'.e($lead['city_name']?:'ShahkotPK').' · '.e(date('d M Y',strtotime((string)($lead['published_at']?:$lead['created_at'])))).'</small><h3>'.e(news_display_title($lead,$lead['language']==='ur'?'ur':'en')).'</h3><p>'.e(news_display_excerpt($lead,$lead['language']==='ur'?'ur':'en')).'</p><a href="/news.php?slug='.urlencode($lead['slug']).'">Read Story →</a></div></article><div class="news-side-list">';foreach($posts as $p)echo '<a href="/news.php?slug='.urlencode($p['slug']).'"><div class="news-thumb" '.(news_image($p)?'style="background-image:url(\''.e(news_image($p)).'\')"':'').'></div><span><small>'.e($p['category_name_en']?:'News').'</small><b>'.e(news_display_title($p,$p['language']==='ur'?'ur':'en')).'</b><em>'.e(date('d M',strtotime((string)($p['published_at']?:$p['created_at'])))).'</em></span></a>';echo '</div></div>';
        } elseif($type==='video'&&setting_bool('news_video_enabled',true)){
            echo '<div class="news-video-grid">';foreach($posts as $p)echo '<article><a class="news-video-media" href="/news.php?slug='.urlencode($p['slug']).'" '.(news_image($p)?'style="background-image:url(\''.e(news_image($p)).'\')"':'').'><i>▶</i></a><div><small>'.e($p['category_name_en']?:'Video News').'</small><h4>'.e(news_display_title($p,$p['language']==='ur'?'ur':'en')).'</h4><a href="/news.php?slug='.urlencode($p['slug']).'">Watch Story →</a></div></article>';echo '</div>';
        } else {
            echo '<div class="news-card-grid">';foreach($posts as $p){$plang=$rtl?'ur':($p['language']==='ur'?'ur':'en');echo '<article dir="'.($plang==='ur'?'rtl':'ltr').'"><div class="news-card-image" '.(news_image($p)?'style="background-image:url(\''.e(news_image($p)).'\')"':'').'>'.($p['post_type']==='video'?'<i>▶</i>':'').'</div><div><small>'.e(($plang==='ur'?($p['category_name_ur']?:$p['category_name_en']):$p['category_name_en'])?:'News').'</small><h4>'.e(news_display_title($p,$plang)).'</h4><p>'.e(news_display_excerpt($p,$plang)).'</p><a href="/news.php?slug='.urlencode($p['slug']).'&lang='.$plang.'">'.($plang==='ur'?'خبر پڑھیں ←':'Read News →').'</a></div></article>';}echo '</div>';
        }
        echo '</div>';
    }
    echo '</div></section>';
}
function lt_city_map(array $sections): void {$s=lt_section($sections,'city_map');if(!$s)return;$ready=function_exists('shahkot_maps_ready')?shahkot_maps_ready():(function_exists('google_maps_ready')&&google_maps_ready());if(!$ready)return;try{if(function_exists('google_maps_public_module'))google_maps_public_module($s['title']?:'Explore Shahkot on Map',function_exists('google_maps_allowed_types')?google_maps_allowed_types():['business','guide','event','job','property'],$s['content']?:'Search businesses, shops, events, property and city places around you.');}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Homepage map widget failed',$e);}}
function lt_blog_widgets(array $sections): void {
    $s=lt_section($sections,'blog_widgets');if(!$s||!setting_bool('blog_portal_enabled',true)||!setting_bool('blog_home_enabled',true))return;$widgets=blog_widgets(true);if(!$widgets)return;
    echo '<section class="blog-home"><div class="lt-shell"><div class="blog-home-head"><div><span>'.e($s['subtitle']?:'STORIES & GUIDES').'</span><h2>'.e($s['title']?:'From the ShahkotPK Blog').'</h2><p>'.e($s['content']?:'Useful articles from local writers and editors.').'</p></div><a href="/blog.php">Open Blog →</a></div>';
    foreach($widgets as $w){$posts=blog_widget_posts($w);if(!$posts)continue;echo '<div class="blog-home-widget"><div class="blog-home-widget-title"><h3>'.e($w['title']).'</h3><a href="/blog.php">View All →</a></div><div class="blog-home-grid">';foreach($posts as $p)echo '<article><a class="media" href="/blog.php?slug='.urlencode($p['slug']).'" '.($p['cover_image']?'style="background-image:url(\''.e($p['cover_image']).'\')"':'').'></a><div><small>'.e($p['category_name']?:'Blog').'</small><h3>'.e(blog_title($p,'en')).'</h3><p>'.e(blog_excerpt($p,'en')).'</p><span>By '.e($p['author_name']).'</span><a href="/blog.php?slug='.urlencode($p['slug']).'">Read →</a></div></article>';echo '</div></div>';}
    echo '</div></section>';
}

function lt_live_widget(array $sections): void {try{if(function_exists('live_render_widget'))live_render_widget($sections);}catch(Throwable $e){runtime_log('Homepage live widget failed',$e);}}
function lt_shop_widget(array $sections): void {try{if(function_exists('store_render_widget'))store_render_widget();}catch(Throwable $e){runtime_log('Homepage shop widget failed',$e);}}

function lt_custom_modules(array $sections): void {
    $known=['topbar','header','hero','info_cards','quick_services','cta','content','directory','featured','spotlights','gallery','links','footer','category_explorer','deals','sponsored_spotlight','new_businesses','city_guide','nearby','restaurants','events','jobs','property','advertise_cta','city_map','news_widgets','blog_widgets','live_widget','shop_widget'];
    foreach($sections as $s){
        if(empty($s['enabled']))continue;
        if(($s['type']??'')==='live_widget'){lt_live_widget($sections);continue;}
        if(($s['type']??'')==='shop_widget'){lt_shop_widget($sections);continue;}
        if(in_array((string)($s['type']??''),$known,true))continue;
        echo '<section class="lt-section lt-custom"><div class="lt-shell"><div class="lt-custom-card">'.lt_heading_html($s);
        $items=lt_items($s);
        if($items){echo '<div class="lt-custom-grid">';foreach($items as $it)echo '<article><b>'.e($it['title']).'</b><span>'.e($it['text']).'</span>'.($it['link']?'<a href="'.e(safe_link($it['link'])).'">Open →</a>':'').'</article>';echo '</div>';}
        echo '</div></div></section>';
    }
}
