<?php
declare(strict_types=1);
/** ShahkotPK v13.1.4 — canonical Platform Settings registry + safe settings service. */
if (!function_exists('sk1314ps_registry')) {
function sk1314ps_db(): PDO {
    if (!function_exists('db')) throw new RuntimeException('Database connection is unavailable.');
    return db();
}
function sk1314ps_table(string $table): bool {
    static $cache=[];
    if (array_key_exists($table,$cache)) return $cache[$table];
    try {
        $q=sk1314ps_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$table]);
        return $cache[$table]=(bool)$q->fetchColumn();
    } catch(Throwable $e){ return $cache[$table]=false; }
}
function sk1314ps_registry(): array {
    return [
      ['id'=>0,'key'=>'platform-core','name'=>'Platform Core','icon'=>'▦','description'=>'Site identity, locale, timezone and core public-platform behavior.','prefixes'=>['site_','default_','timezone','charset','locale','currency','maintenance_'],'managers'=>[]],
      ['id'=>1,'key'=>'brand-theme','name'=>'Brand & Theme','icon'=>'◇','description'=>'Brand identity, logo, colors, appearance and theme preferences.','prefixes'=>['brand_','logo_','favicon_','primary_','accent_','theme_','appearance_'],'managers'=>['/admin/themes.php']],
      ['id'=>2,'key'=>'city-tenant','name'=>'City & Tenant','icon'=>'⌖','description'=>'Default city, tenant identity, geography and city-level configuration.','prefixes'=>['city_','default_city','tenant_','geo_'],'managers'=>['/admin/tenants.php','/admin/cities.php']],
      ['id'=>3,'key'=>'navigation','name'=>'Navigation & Menus','icon'=>'☰','description'=>'Header, footer, navigation and menu-builder behavior.','prefixes'=>['menu_','nav_','navigation_','header_','footer_'],'managers'=>['/admin/header-menu.php','/admin/landing-menu.php']],
      ['id'=>4,'key'=>'directory','name'=>'Directory & Listings','icon'=>'◇','description'=>'Directory, listing categories and public discovery defaults.','prefixes'=>['directory_','listing_','category_'],'managers'=>['/admin/businesses.php','/admin/categories.php']],
      ['id'=>5,'key'=>'homepage','name'=>'Homepage CMS','icon'=>'✦','description'=>'Homepage, hero, sections and CMS presentation settings.','prefixes'=>['homepage_','home_','hero_','cms_'],'managers'=>['/admin/homepage-builder.php','/admin/homepage.php']],
      ['id'=>6,'key'=>'map-platform','name'=>'Map Platform','icon'=>'📍','description'=>'Map provider, center, zoom, clustering, heatmap, geolocation and search radius.','prefixes'=>['map_','maps_','default_map_'],'managers'=>['/admin/maps.php','/admin/location-discovery.php','/admin/location-intelligence.php']],
      ['id'=>7,'key'=>'business-platform','name'=>'Business Platform','icon'=>'▦','description'=>'Business directory, seller and merchant platform behavior.','prefixes'=>['business_','seller_','merchant_','shopkeeper_'],'managers'=>['/admin/businesses.php','/admin/business-crm.php']],
      ['id'=>8,'key'=>'content','name'=>'Content & Publishing','icon'=>'▤','description'=>'News, blog, events, jobs and editorial publishing defaults.','prefixes'=>['content_','news_','blog_','event_','job_'],'managers'=>['/admin/news.php','/admin/blog.php','/admin/city-guide.php']],
      ['id'=>9,'key'=>'search','name'=>'Search & Discovery','icon'=>'⌕','description'=>'Public search, discovery, recommendation and result behavior.','prefixes'=>['search_','discover_','recommendation_'],'managers'=>['/admin/location-intelligence.php','/admin/city-discovery.php']],
      ['id'=>10,'key'=>'business-crm','name'=>'Business CRM','icon'=>'CRM','description'=>'Business CRM, leads, opportunities and pipeline settings.','prefixes'=>['crm_','lead_','opportunity_','pipeline_'],'managers'=>['/admin/business-crm.php']],
      ['id'=>11,'key'=>'commerce','name'=>'Commerce & Cart','icon'=>'Cart','description'=>'Shop, cart, checkout, orders and commerce defaults.','prefixes'=>['shop_','cart_','checkout_','commerce_','order_'],'managers'=>['/admin/shop.php','/admin/commerce-orders.php']],
      ['id'=>12,'key'=>'payments','name'=>'Payments & Wallet','icon'=>'◎','description'=>'Payment, wallet, currency, tax and transaction preferences.','prefixes'=>['payment_','wallet_','currency_','tax_'],'managers'=>['/admin/payments.php','/admin/accounts.php']],
      ['id'=>13,'key'=>'marketing','name'=>'Marketing & Ads','icon'=>'✦','description'=>'Advertisements, campaigns, banners and promotion defaults.','prefixes'=>['ad_','ads_','advertising_','campaign_','banner_','promo_'],'managers'=>['/admin/ads.php','/admin/campaigns.php']],
      ['id'=>14,'key'=>'membership','name'=>'Membership & Plans','icon'=>'Plus','description'=>'Membership, subscriptions, plans and premium-feature settings.','prefixes'=>['membership_','subscription_','plan_','plus_'],'managers'=>['/admin/plans.php','/admin/membership-club.php']],
      ['id'=>15,'key'=>'deals','name'=>'Deals & Coupons','icon'=>'%','description'=>'Deals, coupons, offers and discount behavior.','prefixes'=>['deal_','coupon_','offer_','discount_'],'managers'=>['/admin/deals.php','/admin/deals-loyalty.php']],
      ['id'=>16,'key'=>'transport','name'=>'Ride & Transport','icon'=>'Ride','description'=>'Ride, rider, transport and delivery platform defaults.','prefixes'=>['ride_','transport_','delivery_','rider_'],'managers'=>['/admin/transport-network.php']],
      ['id'=>17,'key'=>'auth-access','name'=>'Authentication & Access','icon'=>'🔐','description'=>'Authentication, login and account-access behavior.','prefixes'=>['auth_','login_','two_factor_','2fa_'],'managers'=>['/admin/users.php','/admin/security-integrity.php']],
      ['id'=>18,'key'=>'messaging','name'=>'Notifications & Messaging','icon'=>'✦','description'=>'Email, SMTP, SMS, WhatsApp, push and notification defaults.','prefixes'=>['notification_','email_','smtp_','mail_','sms_','whatsapp_','push_'],'managers'=>['/admin/notification-control.php','/admin/background-integrity.php']],
      ['id'=>19,'key'=>'moderation','name'=>'Moderation & Verification','icon'=>'✓','description'=>'Reviews, verification, moderation and complaint handling defaults.','prefixes'=>['moderation_','review_','verify_','verification_','complaint_'],'managers'=>['/admin/reviews.php','/admin/permissions-integrity.php']],
      ['id'=>20,'key'=>'property','name'=>'Property Platform','icon'=>'⌂','description'=>'Property and real-estate module defaults.','prefixes'=>['property_','real_estate_'],'managers'=>['/admin/property.php']],
      ['id'=>21,'key'=>'analytics','name'=>'Growth & Analytics','icon'=>'↗','description'=>'Analytics, reports, tracking and growth preferences.','prefixes'=>['analytics_','growth_','report_','tracking_'],'managers'=>['/admin/reports.php','/admin/control-center.php']],
      ['id'=>22,'key'=>'ai-automation','name'=>'AI & Automation','icon'=>'✨','description'=>'AI assistant, automation and recommendation controls.','prefixes'=>['ai_','automation_','assistant_'],'managers'=>['/admin/ai.php','/admin/ai-copilot.php','/admin/control-center.php']],
      ['id'=>23,'key'=>'integrations','name'=>'Integrations & API','icon'=>'◆','description'=>'API, integration, webhook and external-service settings.','prefixes'=>['api_','integration_','webhook_','external_'],'managers'=>['/admin/mobile-api.php','/admin/enterprise.php']],
      ['id'=>24,'key'=>'system-integrity','name'=>'System Integrity','icon'=>'◎','description'=>'Core runtime/file integrity diagnostics.','prefixes'=>[],'managers'=>['/admin/system-integrity.php'],'readonly'=>true],
      ['id'=>25,'key'=>'module-linkage','name'=>'Module Linkage','icon'=>'↔','description'=>'Admin ↔ public module linkage and route integrity.','prefixes'=>[],'managers'=>['/admin/module-linkage.php'],'readonly'=>true],
      ['id'=>26,'key'=>'data-consistency','name'=>'Data Consistency','icon'=>'≋','description'=>'Admin CRUD ↔ public data consistency diagnostics.','prefixes'=>[],'managers'=>['/admin/data-consistency.php'],'readonly'=>true],
      ['id'=>27,'key'=>'data-repair','name'=>'Data Repair','icon'=>'✓','description'=>'Preview-first controlled data repair center.','prefixes'=>[],'managers'=>['/admin/data-repair.php'],'readonly'=>true],
      ['id'=>28,'key'=>'media-integrity','name'=>'Media Integrity','icon'=>'◫','description'=>'Media/file path and upload integrity diagnostics.','prefixes'=>[],'managers'=>['/admin/media-integrity.php'],'readonly'=>true],
      ['id'=>29,'key'=>'runtime-integrity','name'=>'Runtime Performance','icon'=>'⚡','description'=>'Cache, session and runtime performance integrity.','prefixes'=>[],'managers'=>['/admin/runtime-integrity.php'],'readonly'=>true],
      ['id'=>30,'key'=>'permissions-integrity','name'=>'Permissions Integrity','icon'=>'🛡️','description'=>'Roles, permissions and admin access integrity.','prefixes'=>[],'managers'=>['/admin/permissions-integrity.php'],'readonly'=>true],
      ['id'=>31,'key'=>'security-integrity','name'=>'Security Integrity','icon'=>'🔐','description'=>'Authentication, session and security integrity diagnostics.','prefixes'=>[],'managers'=>['/admin/security-integrity.php'],'readonly'=>true],
      ['id'=>32,'key'=>'database-integrity','name'=>'Database Integrity','icon'=>'🗄️','description'=>'Database metadata integrity and optimization audit.','prefixes'=>[],'managers'=>['/admin/database-integrity.php'],'readonly'=>true],
      ['id'=>33,'key'=>'public-seo','name'=>'Public SEO & Routing','icon'=>'🔎','description'=>'Public page, SEO and routing integrity diagnostics.','prefixes'=>[],'managers'=>['/admin/public-seo-routing.php'],'readonly'=>true],
      ['id'=>34,'key'=>'background-integrity','name'=>'Background Jobs','icon'=>'⏱️','description'=>'Notifications, cron and background-job integrity.','prefixes'=>[],'managers'=>['/admin/background-integrity.php'],'readonly'=>true],
      ['id'=>35,'key'=>'regression-integrity','name'=>'Full Regression','icon'=>'✅','description'=>'Consolidated regression test and broken-feature cleanup center.','prefixes'=>[],'managers'=>['/admin/regression-integrity.php'],'readonly'=>true],
      ['id'=>36,'key'=>'production-readiness','name'=>'Production Readiness','icon'=>'🛡️','description'=>'Final production hardening and stable-release readiness.','prefixes'=>[],'managers'=>['/admin/production-hardening.php'],'readonly'=>true],
    ];
}
function sk1314ps_sensitive(string $key): bool {
    $k=strtolower($key);
    if (preg_match('~(?:password|passwd|secret|token|private|credential|smtp_pass|api[_-]?key|signing|salt|hash|webhook_secret|client_secret|access_key)~',$k)) return true;
    if (preg_match('~^(?:installed_app_version|runtime_cache_contract_version|public_data_cache_version|stable_release_|step\d+_|security_integrity_|regression_integrity_|database_integrity_|public_seo_|background_integrity_|permissions_integrity_|production_hardening_|platform_settings_)~',$k)) return true;
    return false;
}
function sk1314ps_all_safe_settings(): array {
    if (!sk1314ps_table('settings')) return [];
    try {
        $q=sk1314ps_db()->query('SELECT setting_key,setting_value FROM settings ORDER BY setting_key ASC LIMIT 1200');
        $out=[];
        foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r){
            $k=(string)($r['setting_key']??'');
            if($k===''||sk1314ps_sensitive($k)) continue;
            $v=(string)($r['setting_value']??'');
            if(strlen($v)>8192) $v=substr($v,0,8192);
            $out[$k]=$v;
        }
        return $out;
    } catch(Throwable $e){ return []; }
}
function sk1314ps_matches(string $key,array $prefixes): bool {
    foreach($prefixes as $p){
        $p=(string)$p;
        if($p==='') continue;
        if(str_ends_with($p,'_')) { if(str_starts_with($key,$p)) return true; }
        elseif($key===$p || str_starts_with($key,$p.'_')) return true;
    }
    return false;
}
function sk1314ps_manager_url(array $module): string {
    $root=dirname(__DIR__);
    foreach(($module['managers']??[]) as $url){
        $rel=ltrim((string)$url,'/');
        if($rel!=='' && is_file($root.'/'.$rel)) return '/'.$rel;
    }
    return '';
}
function sk1314ps_map_payload(): array {
    $helper=__DIR__.'/map_platform_settings_v1311.php';
    if(is_file($helper)) require_once $helper;
    if(function_exists('sk1311map_payload')) return sk1311map_payload();
    return [];
}
function sk1314ps_module_payload(string|int|null $selector=null): array {
    $registry=sk1314ps_registry();
    $all=sk1314ps_all_safe_settings();
    $selected=null;
    foreach($registry as $m){
        if($selector===null) continue;
        if((string)$m['id']===(string)$selector || (string)$m['key']===(string)$selector){$selected=$m;break;}
    }
    if($selected===null) return ['registry'=>sk1314ps_registry_payload($registry,$all)];
    $values=[];
    if($selected['key']==='map-platform'){
        $values=sk1314ps_map_payload();
    } elseif(empty($selected['readonly'])) {
        foreach($all as $k=>$v) if(sk1314ps_matches($k,$selected['prefixes']??[])) $values[$k]=$v;
        if($selected['key']==='platform-core'){
            foreach(['site_name'=>'ShahkotPK','default_city'=>'Shahkot','timezone'=>'Asia/Karachi','charset'=>'utf8mb4'] as $k=>$d){ if(!array_key_exists($k,$values)) $values[$k]=$d; }
        }
    }
    $m=sk1314ps_public_module($selected,$all);
    $m['values']=$values;
    return ['module'=>$m];
}
function sk1314ps_public_module(array $m,array $all): array {
    $count=0;
    foreach($all as $k=>$v) if(sk1314ps_matches($k,$m['prefixes']??[])) $count++;
    $manager=sk1314ps_manager_url($m);
    $available=$manager!=='' || $count>0 || $m['key']==='platform-core' || $m['key']==='map-platform';
    return [
      'id'=>(int)$m['id'],'key'=>(string)$m['key'],'name'=>(string)$m['name'],'icon'=>(string)$m['icon'],'description'=>(string)$m['description'],
      'readonly'=>!empty($m['readonly']),'manager_url'=>$manager,'settings_count'=>$count,'available'=>$available
    ];
}
function sk1314ps_registry_payload(?array $registry=null,?array $all=null): array {
    $registry=$registry??sk1314ps_registry();$all=$all??sk1314ps_all_safe_settings();$out=[];
    foreach($registry as $m)$out[]=sk1314ps_public_module($m,$all);
    return $out;
}
function sk1314ps_set(string $key,string $value): void {
    if(sk1314ps_sensitive($key)) throw new InvalidArgumentException('This setting is protected and cannot be edited here.');
    if(!sk1314ps_table('settings')) throw new RuntimeException('Settings table is unavailable.');
    $q=sk1314ps_db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    $q->execute([$key,$value]);
}
function sk1314ps_save(string $moduleKey,array $values): array {
    $registry=sk1314ps_registry();$module=null;
    foreach($registry as $m)if($m['key']===$moduleKey){$module=$m;break;}
    if(!$module)throw new InvalidArgumentException('Unknown settings module.');
    if(!empty($module['readonly']))throw new InvalidArgumentException('This diagnostic module is read-only here. Open its manager instead.');
    if($moduleKey==='map-platform'){
        $helper=__DIR__.'/map_platform_settings_v1311.php';if(is_file($helper))require_once $helper;
        if(!function_exists('sk1311map_save'))throw new RuntimeException('Map settings service is unavailable.');
        return sk1311map_save($values);
    }
    if(count($values)>120)throw new InvalidArgumentException('Too many settings in one request.');
    $existing=sk1314ps_all_safe_settings();
    $allowedNew=$moduleKey==='platform-core'?['site_name','default_city','timezone','charset']:[];
    $pdo=sk1314ps_db();$pdo->beginTransaction();
    try{
        foreach($values as $k=>$v){
            $k=(string)$k;
            if(!sk1314ps_matches($k,$module['prefixes']??[]))continue;
            if(!array_key_exists($k,$existing)&&!in_array($k,$allowedNew,true))continue;
            if(is_bool($v))$v=$v?'1':'0';
            elseif(is_array($v)||is_object($v))$v=json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $v=trim((string)$v);
            if(strlen($v)>4096)throw new InvalidArgumentException('Setting '.$k.' is too long.');
            sk1314ps_set($k,$v);
        }
        $pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    return sk1314ps_module_payload($moduleKey)['module']['values']??[];
}
}
