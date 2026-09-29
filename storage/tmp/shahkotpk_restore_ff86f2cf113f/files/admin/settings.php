<?php
require __DIR__.'/../app/bootstrap.php';require_permission('settings.manage');
$success=$error=null;ensure_default_settings();$defaults=shahkot_default_settings();

$modules=[
'general'=>[
    'icon'=>'⌂','title'=>'Core Platform','description'=>'Site identity, locale and platform defaults.','status_key'=>null,'link'=>'/',
    'fields'=>[
        'site_name'=>['text','Site Name','Public platform name'],
        'site_tagline'=>['text','Site Tagline','Short public description'],
        'default_city'=>['text','Default City','Primary city used by the portal'],
        'timezone'=>['text','Timezone','PHP timezone, e.g. Asia/Karachi'],
        'language'=>['select','Language','Default platform language',['en'=>'English','ur'=>'Urdu']],
        'currency'=>['select','Currency','Platform billing currency',['PKR'=>'PKR','USD'=>'USD']],
    ]],
'branding'=>[
    'icon'=>'◆','title'=>'Branding & Contact','description'=>'Logo, colors, office and support identity.','status_key'=>null,'link'=>'/admin/homepage.php?tab=global',
    'fields'=>[
        'support_phone'=>['text','Support Phone','Customer support number'],
        'support_email'=>['email','Support Email','Public support email'],
        'support_whatsapp'=>['text','Support WhatsApp','WhatsApp contact number'],
        'business_address'=>['textarea','Office Address','Public office/business address'],
        'logo_url'=>['text','Logo URL','Public logo path or URL'],
        'favicon_url'=>['text','Favicon URL','Browser favicon path'],
        'brand_primary_color'=>['color','Primary Color','Primary interface accent'],
        'brand_secondary_color'=>['color','Secondary Color','Secondary interface accent'],
    ]],
'seo'=>[
    'icon'=>'⌕','title'=>'SEO & Search Engines','description'=>'Metadata and search engine visibility.','status_key'=>'robots_index','link'=>'/',
    'fields'=>[
        'seo_title'=>['text','SEO Title','Default browser/search title'],
        'seo_description'=>['textarea','SEO Description','Default search description'],
        'seo_keywords'=>['textarea','SEO Keywords','Comma-separated discovery terms'],
        'og_image'=>['text','Social Share Image','Open Graph preview image'],
        'robots_index'=>['toggle','Search Engine Indexing','Allow search engines to index public pages'],
    ]],
'users'=>[
    'icon'=>'👥','title'=>'Users & Approvals','description'=>'Login, registration and account approval rules.','status_key'=>'registration_enabled','link'=>'/admin/users.php',
    'fields'=>[
        'login_enabled'=>['toggle','Public Login','Allow public login'],
        'registration_enabled'=>['toggle','Registration','Master registration switch'],
        'customer_signup_enabled'=>['toggle','Customer Signup','Allow new customer accounts'],
        'shopkeeper_signup_enabled'=>['toggle','Shopkeeper Signup','Allow new business-owner accounts'],
        'customer_approval_mode'=>['select','Customer Approval','New customer approval workflow',['auto'=>'Automatic','manual'=>'Admin Approval','package_payment'=>'Package / Payment']],
        'shopkeeper_approval_mode'=>['select','Shopkeeper Approval','New shopkeeper approval workflow',['auto'=>'Automatic','manual'=>'Admin Approval','package_payment'=>'Package / Payment']],
        'email_required'=>['toggle','Require Email','Require email during signup'],
        'phone_required'=>['toggle','Require Phone','Require phone during signup'],
        'password_min_length'=>['number','Minimum Password Length','Minimum signup/admin password characters'],
    ]],
'businesses'=>[
    'icon'=>'▦','title'=>'Business Directory','description'=>'Businesses, search, featured and verification behavior.','status_key'=>'directory_enabled','link'=>'/admin/businesses.php',
    'fields'=>[
        'business_registration_enabled'=>['toggle','Business Registration','Allow business registration/listing'],
        'directory_enabled'=>['toggle','Public Directory','Enable public directory'],
        'search_enabled'=>['toggle','Public Search','Enable business search'],
        'search_results_limit'=>['number','Search Result Limit','Maximum businesses returned per search'],
        'featured_businesses_enabled'=>['toggle','Featured Businesses','Enable featured business showcase'],
        'verified_badge_enabled'=>['toggle','Verified Badges','Display verified status'],
    ]],
'city_portal'=>[
    'icon'=>'◈','title'=>'Complete City Guide Portal','description'=>'Landing-page local discovery, deals, events, jobs, property and city content.','status_key'=>'city_portal_enabled','link'=>'/admin/city-guide.php',
    'fields'=>[
        'city_portal_enabled'=>['toggle','Complete City Portal','Master City Guide / local content system'],
        'city_weather_text'=>['text','Homepage Weather Text','Admin-managed city weather/status text'],
        'city_emergency_text'=>['text','Emergency / Utility Text','Top utility-bar emergency information'],
        'homepage_category_explorer_enabled'=>['toggle','Category Explorer','Show dynamic category explorer'],
        'homepage_deals_enabled'=>['toggle','Deals & Offers','Show local deals section'],
        'homepage_city_guide_enabled'=>['toggle','City Guide','Show city guide section'],
        'homepage_events_enabled'=>['toggle','Events','Show local events section'],
        'homepage_jobs_enabled'=>['toggle','Jobs','Show local jobs section'],
        'homepage_property_enabled'=>['toggle','Property','Show property/rentals section'],
        'homepage_new_businesses_enabled'=>['toggle','New Businesses','Show newly registered businesses'],
        'homepage_nearby_enabled'=>['toggle','Near Me','Enable browser-location nearby sorting'],
        'homepage_restaurants_enabled'=>['toggle','Restaurants','Show food/restaurants section'],
        'homepage_sponsored_spotlight_enabled'=>['toggle','Sponsored Spotlight','Enable premium sponsored homepage spotlight'],
        'homepage_advertise_cta_enabled'=>['toggle','Advertise CTA','Show advertising conversion section'],
        'homepage_deals_limit'=>['number','Deals Limit','Homepage deal cards'],
        'homepage_events_limit'=>['number','Events Limit','Homepage event cards'],
        'homepage_jobs_limit'=>['number','Jobs Limit','Homepage jobs'],
        'homepage_property_limit'=>['number','Property Limit','Homepage property cards'],
        'homepage_guide_limit'=>['number','Guide Limit','Homepage guide cards'],
        'homepage_new_business_limit'=>['number','New Business Limit','Recently added business cards'],
        'homepage_nearby_limit'=>['number','Near Me Limit','Businesses available for location sorting'],
        'homepage_restaurant_limit'=>['number','Restaurant Limit','Homepage restaurant cards'],
    ]],
'news'=>[
    'icon'=>'▰','title'=>'News Portal','description'=>'English/Urdu newsroom, breaking news, video stories and homepage widgets.','status_key'=>'news_portal_enabled','link'=>'/admin/news.php',
    'fields'=>[
        'news_portal_enabled'=>['toggle','News Portal','Master public News Portal switch'],
        'news_home_enabled'=>['toggle','Homepage News Widgets','Show configured newsroom widgets on landing page'],
        'news_breaking_enabled'=>['toggle','Breaking News','Enable breaking-news strip/widget'],
        'news_video_enabled'=>['toggle','Video News','Enable video-news posts and widgets'],
        'news_default_language'=>['select','Default News Language','Default public News Portal language',['en'=>'English','ur'=>'Urdu']],
        'news_items_per_page'=>['number','News Per Page','Default number of stories on news listing'],
        'news_show_views'=>['toggle','Show View Counts','Display public story views'],
        'news_allow_scheduled'=>['toggle','Scheduled Publishing','Automatically publish scheduled posts at their publish time'],
    ]],
'blog'=>[
    'icon'=>'✎','title'=>'Blogging System','description'=>'Public blog, authors, comments, publishing workflow and homepage blog widgets.','status_key'=>'blog_portal_enabled','link'=>'/admin/blog.php',
    'fields'=>[
        'blog_portal_enabled'=>['toggle','Blog Portal','Master public Blog switch'],
        'blog_home_enabled'=>['toggle','Homepage Blog Widgets','Show configured Blog widgets on landing page'],
        'blog_comments_enabled'=>['toggle','Public Comments','Allow comments on posts that enable comments'],
        'blog_comments_moderation'=>['toggle','Comment Moderation','New comments remain pending until approved'],
        'blog_video_enabled'=>['toggle','Video Blog Posts','Allow YouTube/Vimeo blog posts'],
        'blog_scheduled_publishing'=>['toggle','Scheduled Publishing','Publish scheduled articles automatically'],
        'blog_items_per_page'=>['number','Articles Per Page','Default public Blog listing size'],
        'blog_show_views'=>['toggle','Show View Counts','Display article view counts'],
    ]],
'maps'=>[
    'icon'=>'◎','title'=>'Google Maps & Discovery','description'=>'Google Maps API, default city center, marker visibility and interactive map controls.','status_key'=>'google_maps_enabled','link'=>'/admin/maps.php',
    'fields'=>[
        'google_maps_enabled'=>['toggle','Google Maps','Master Google Maps integration switch'],
        'google_maps_api_key'=>['password','Google Maps Browser API Key','Required for interactive ShahkotPK markers/search. Enable Maps JavaScript API and restrict the Browser key to your ShahkotPK domain.'],
        'google_maps_default_lat'=>['text','Default Latitude','Default map center latitude'],
        'google_maps_default_lng'=>['text','Default Longitude','Default map center longitude'],
        'google_maps_default_zoom'=>['number','Default Zoom','Initial Google Maps zoom level'],
        'google_maps_map_type'=>['select','Map Style','Google base map style',['roadmap'=>'Roadmap','satellite'=>'Satellite','hybrid'=>'Hybrid','terrain'=>'Terrain']],
        'google_maps_height'=>['number','Landing Map Height','Map height in pixels'],
        'google_maps_home_enabled'=>['toggle','Homepage Map','Show interactive discovery map on main landing page'],
        'google_maps_vertical_pages_enabled'=>['toggle','City Content Maps','Show maps on City Guide, Deals, Events, Jobs and Property pages'],
        'google_maps_businesses_enabled'=>['toggle','Businesses Map','Show map on business directory landing page'],
        'google_maps_admin_enabled'=>['toggle','Admin Map Dashboard','Enable Admin Map Control dashboard'],
        'google_maps_fit_markers'=>['toggle','Auto Fit Markers','Automatically fit visible markers into the viewport'],
        'google_maps_places_search'=>['toggle','Google Places Search','Enable address/places geocoding in admin map pickers'],
        'google_maps_show_businesses'=>['toggle','Business Markers','Display local business/shop markers'],
        'google_maps_show_guide'=>['toggle','City Guide Markers','Display city guide/place markers'],
        'google_maps_show_deals'=>['toggle','Deal Markers','Display deal markers when coordinates are assigned'],
        'google_maps_show_events'=>['toggle','Event Markers','Display event markers'],
        'google_maps_show_jobs'=>['toggle','Job Markers','Display job/location markers'],
        'google_maps_show_property'=>['toggle','Property Markers','Display property markers'],
    ]],
'cities'=>[
    'icon'=>'⌖','title'=>'Cities & Location','description'=>'City location tools and browser geolocation.','status_key'=>'city_location_features_enabled','link'=>'/admin/cities.php',
    'fields'=>[
        'city_location_features_enabled'=>['toggle','Location Features','Master city/location tools switch'],
        'browser_geolocation_enabled'=>['toggle','Browser Geolocation','Allow admin browser location capture'],
        'default_map_provider'=>['select','Map Provider','Default map provider',['google'=>'Google Maps','openstreetmap'=>'OpenStreetMap','manual'=>'Manual URL']],
        'default_map_zoom'=>['number','Default Map Zoom','Default location zoom level'],
    ]],
'homepage'=>[
    'icon'=>'▤','title'=>'Homepage & CMS','description'=>'Visual builder, media, sliders and public homepage behavior.','status_key'=>'homepage_search_enabled','link'=>'/admin/homepage.php',
    'fields'=>[
        'homepage_slider_enabled'=>['toggle','Homepage Slider','Display homepage slider'],
        'homepage_slider_interval_ms'=>['number','Slider Interval (ms)','Autoplay speed in milliseconds'],
        'homepage_search_enabled'=>['toggle','Homepage Search','Display public hero search'],
        'homepage_animations_enabled'=>['toggle','Homepage Animations','Enable public motion effects'],
        'mobile_menu_enabled'=>['toggle','Mobile Menu','Enable mobile navigation drawer'],
        'mobile_sticky_cta_enabled'=>['toggle','Mobile Sticky CTA','Show sticky mobile action bar'],
        'cms_media_uploads_enabled'=>['toggle','CMS Media Uploads','Allow uploads through Media Library'],
        'cms_theme_uploads_enabled'=>['toggle','CMS Theme ZIP Uploads','Allow secure CMS theme import'],
        'cms_custom_css_enabled'=>['toggle','Page Custom CSS','Allow page-specific CMS CSS'],
        'cms_revisions_limit'=>['number','CMS Revisions Retained','Recent revisions stored per page'],
    ]],
'themes'=>[
    'icon'=>'✦','title'=>'Themes & Interface','description'=>'Admin and landing presentation controls.','status_key'=>'landing_theme_animations','link'=>'/admin/themes.php',
    'fields'=>[
        'admin_theme_animations'=>['toggle','Admin Theme Animations','Enable dashboard theme motion'],
        'landing_theme_animations'=>['toggle','Landing Theme Animations','Enable landing template motion'],
        'admin_readable_typography_enabled'=>['toggle','Readable Admin Typography','Keep forms, tables, dashboards and helper text at a readable minimum size'],
        'admin_font_scale'=>['select','Admin Font Scale','Choose the main admin content typography scale',['compact'=>'Compact','comfortable'=>'Comfortable (Recommended)','large'=>'Large']],
        'show_login_in_header'=>['toggle','Header Login','Show login/dashboard action'],
        'show_pricing_in_header'=>['toggle','Header Plans','Show plans link'],
    ]],
'subscriptions'=>[
    'icon'=>'♢','title'=>'Subscriptions & Billing','description'=>'Plans, invoices, tax and expiry rules.','status_key'=>'subscriptions_enabled','link'=>'/admin/plans.php',
    'fields'=>[
        'subscriptions_enabled'=>['toggle','Subscriptions','Master subscriptions switch'],
        'free_plan_enabled'=>['toggle','Free Plans','Allow free packages'],
        'paid_plans_enabled'=>['toggle','Paid Plans','Allow paid packages'],
        'subscription_invoice_due_days'=>['number','Default Invoice Due Days','Days allowed before invoice becomes overdue'],
        'subscription_tax_percent'=>['number','Subscription Tax %','Default tax percentage added to admin-issued subscription invoices'],
        'subscription_auto_expire'=>['toggle','Auto Expire Subscriptions','Mark ended active subscriptions expired'],
        'subscription_expiry_reminder_days'=>['number','Renewal Reminder Days','Dashboard renewal warning window'],
    ]],
'advertising'=>[
    'icon'=>'▣','title'=>'Advertisement System','description'=>'Campaign delivery and revenue defaults.','status_key'=>'advertisements_enabled','link'=>'/admin/ads.php',
    'fields'=>[
        'advertisements_enabled'=>['toggle','Advertisements','Master ad system switch'],
        'sponsored_listings_enabled'=>['toggle','Sponsored Listings','Enable sponsored placements'],
        'advertisement_auto_schedule'=>['toggle','Automatic Scheduling','Activate/complete campaigns using schedule and limits'],
        'ad_default_priority'=>['number','Default Campaign Priority','Default new campaign priority, 1–100'],
        'ad_default_frequency_cap'=>['number','Default Frequency Cap','Default daily session frequency cap; 0 = unlimited'],
        'ad_allow_external_urls'=>['toggle','External Destination URLs','Allow ads to link outside ShahkotPK'],
    ]],
'payments'=>[
    'icon'=>'₨','title'=>'Payments & Gateways','description'=>'Payment orders, proof and gateway workflow.','status_key'=>'payment_system_enabled','link'=>'/admin/payments.php',
    'fields'=>[
        'payment_system_enabled'=>['toggle','Payment System','Master payment processing switch'],
        'payments_manual_review_enabled'=>['toggle','Manual Payment Review','Allow proof-based manual payments'],
        'payment_reference_prefix'=>['text','Payment Reference Prefix','Prefix used for new payment orders'],
        'payment_proof_max_mb'=>['number','Payment Proof Max MB','Maximum uploaded payment proof size'],
        'payment_proof_required'=>['toggle','Payment Proof Required','Require manual-payment proof where supported'],
    ]],
'accounts'=>[
    'icon'=>'▥','title'=>'Accounts & Ledger','description'=>'Accounting ledger and finance administration controls.','status_key'=>'accounts_manual_journal_enabled','link'=>'/admin/accounts.php',
    'fields'=>[
        'accounts_manual_journal_enabled'=>['toggle','Manual Journals','Allow admins to post manual balanced journals'],
        'accounts_csv_export_enabled'=>['toggle','CSV Exports','Allow trial balance and account ledger exports'],
        'accounts_business_ledger_enabled'=>['toggle','Business Statements','Enable business-wise ledger view'],
    ]],
'ticker'=>[
    'icon'=>'≋','title'=>'Ticker & Notices','description'=>'Public and administrator information ticker.','status_key'=>'ticker_public_enabled','link'=>'/admin/ticker.php',
    'fields'=>[
        'ticker_public_enabled'=>['toggle','Public Ticker','Show ticker on public website'],
        'ticker_admin_enabled'=>['toggle','Admin Ticker','Show ticker in administrator panel'],
    ]],
'email'=>[
    'icon'=>'✉','title'=>'Email & SMTP','description'=>'Transactional email and password recovery.','status_key'=>'smtp_enabled','link'=>'/admin/smtp-test.php',
    'fields'=>[
        'forgot_password_enabled'=>['toggle','Forgot Password','Enable password reset'],
        'password_reset_expiry_minutes'=>['number','Reset Link Expiry Minutes','Password reset token lifetime'],
        'smtp_enabled'=>['toggle','SMTP Enabled','Send real transactional emails'],
        'smtp_host'=>['text','SMTP Host','Mail server hostname'],
        'smtp_port'=>['number','SMTP Port','Mail server port'],
        'smtp_encryption'=>['select','Encryption','SMTP transport',['tls'=>'TLS','ssl'=>'SSL','none'=>'None']],
        'smtp_username'=>['text','SMTP Username','SMTP account username'],
        'smtp_password'=>['password','SMTP Password','Stored SMTP password'],
        'smtp_from_email'=>['email','From Email','Sender address'],
        'smtp_from_name'=>['text','From Name','Sender display name'],
        'smtp_timeout'=>['number','SMTP Timeout','Connection timeout seconds'],
    ]],
'uploads'=>[
    'icon'=>'⇧','title'=>'Uploads & Media','description'=>'Global image upload restrictions.','status_key'=>'image_uploads_enabled','link'=>'/admin/homepage.php?tab=media',
    'fields'=>[
        'image_uploads_enabled'=>['toggle','Image Uploads','Master image uploads switch'],
        'max_image_upload_mb'=>['number','Maximum Image MB','Global image size limit'],
        'allowed_image_types'=>['text','Allowed Image Extensions','Comma-separated extensions'],
    ]],
'security'=>[
    'icon'=>'◇','title'=>'Security & Sessions','description'=>'CSRF and session hardening controls.','status_key'=>'csrf_protection_enabled','link'=>'/admin/profile.php',
    'fields'=>[
        'csrf_protection_enabled'=>['toggle','CSRF Protection','Protect administrator/user POST forms'],
        'session_regenerate_on_login'=>['toggle','Regenerate Session on Login','Rotate session identifier after authentication'],
        'debug_mode'=>['toggle','Debug Mode','Display/record extra debugging information; keep OFF in production'],
    ]],
'growth_suite'=>[
    'icon'=>'◇','title'=>'Commercial Growth Suite','description'=>'Reviews, verification, CRM, loyalty, local services, PWA and commercial growth controls.','status_key'=>'growth_suite_enabled','link'=>'/admin/analytics.php',
    'fields'=>[
        'growth_suite_enabled'=>['toggle','Growth Suite','Master commercial growth features switch'],
        'reviews_enabled'=>['toggle','Ratings & Reviews','Allow customer business reviews'],
        'reviews_require_moderation'=>['toggle','Review Moderation','Hold new reviews for admin approval'],
        'loyalty_points_per_review'=>['number','Review Reward Points','Points awarded on first review submission'],
        'verification_enabled'=>['toggle','Business Verification','Enable verification requests'],
        'verification_fee'=>['number','Verification Fee PKR','Default verification fee'],
        'bookings_enabled'=>['toggle','Bookings & Appointments','Enable appointment requests'],
        'leads_enabled'=>['toggle','Lead CRM','Enable inquiry and quote forms'],
        'coupons_enabled'=>['toggle','Coupons','Enable business coupon system'],
        'loyalty_enabled'=>['toggle','Loyalty Wallet','Enable points and platform credit'],
        'referrals_enabled'=>['toggle','Referrals','Enable referral rewards'],
        'referral_reward_points'=>['number','Referral Reward Points','Points awarded for qualified registration'],
        'whatsapp_automation_enabled'=>['toggle','WhatsApp Automation','Enable provider-based WhatsApp queue'],
        'whatsapp_api_endpoint'=>['text','WhatsApp API Endpoint','Your approved messaging provider HTTPS endpoint'],
        'whatsapp_access_token'=>['password','WhatsApp Access Token','Provider bearer token; never hard-code in source'],
        'analytics_enabled'=>['toggle','Advanced Analytics','Record platform conversion and engagement events'],
        'emergency_portal_enabled'=>['toggle','Emergency Portal','Enable public emergency/public services page'],
        'restaurant_menu_enabled'=>['toggle','Restaurant Menus','Enable food/menu system'],
        'service_marketplace_enabled'=>['toggle','Service Marketplace','Enable service requests and provider quotes'],
        'classifieds_enabled'=>['toggle','Classified Ads','Enable community classified listings'],
        'subscription_entitlements_enabled'=>['toggle','Plan Entitlements','Restrict premium business features by plan'],
        'seller_staff_enabled'=>['toggle','Seller Staff','Allow delegated seller accounts'],
        'moderation_center_enabled'=>['toggle','Moderation Center','Enable report/fraud workflows'],
        'seo_automation_enabled'=>['toggle','SEO Automation','Enable entity metadata and schema overrides'],
        'pwa_enabled'=>['toggle','Progressive Web App','Enable installable PWA shell and service worker'],
        'push_notifications_enabled'=>['toggle','Push Notifications','Store browser subscriptions and campaigns'],
        'multi_city_management_enabled'=>['toggle','Multi-City Management','Enable city manager assignments'],
        'commercial_reports_enabled'=>['toggle','Commercial Reports','Enable revenue/growth reports'],
        'growth_home_enabled'=>['toggle','Homepage Growth Widget','Show reviews/services/rewards widget on homepage'],
    ]],
'recovery'=>[
    'icon'=>'↶','title'=>'Updates & Recovery','description'=>'Updater, automatic backups and history retention.','status_key'=>'update_system_enabled','link'=>'/admin/updates.php',
    'fields'=>[
        'update_system_enabled'=>['toggle','System Updater','Allow updater ZIP installation'],
        'updater_auto_backup'=>['toggle','Auto Backup Before Update','Create a restore point before new updates'],
        'updater_backup_database'=>['toggle','Backup Database Before Update','Include database in automatic restore point'],
        'updater_auto_rollback'=>['toggle','Automatic Rollback','Attempt rollback after failed update'],
        'updater_backup_retention'=>['number','Automatic Backup Retention','Automatic restore points retained'],
        'recovery_update_history_limit'=>['number','Update History Limit','Maximum activity records loaded in Recovery Center'],
        'recovery_backup_history_limit'=>['number','Backup History Limit','Maximum backup records loaded in Recovery Center'],
    ]],
'maintenance'=>[
    'icon'=>'⚙','title'=>'Maintenance & Runtime','description'=>'Maintenance mode and production runtime behavior.','status_key'=>'maintenance_mode','link'=>'/',
    'fields'=>[
        'maintenance_mode'=>['toggle','Maintenance Mode','Temporarily block public website while admin remains available'],
        'maintenance_message'=>['textarea','Maintenance Message','Message shown to public visitors during maintenance'],
    ]],
];

function setting_slug(string $v): string{return preg_replace('/[^a-z0-9_-]/','',strtolower($v));}
function setting_clamp(string $key,string $value): string {
    $intRules=[
        'password_min_length'=>[8,64],
        'search_results_limit'=>[10,500],
        'default_map_zoom'=>[1,22],
        'homepage_slider_interval_ms'=>[2000,30000],
        'cms_revisions_limit'=>[5,100],
        'subscription_invoice_due_days'=>[1,365],
        'subscription_expiry_reminder_days'=>[1,180],
        'ad_default_priority'=>[1,100],
        'ad_default_frequency_cap'=>[0,100],
        'payment_proof_max_mb'=>[1,50],
        'smtp_port'=>[1,65535],
        'smtp_timeout'=>[5,60],
        'password_reset_expiry_minutes'=>[5,1440],
        'max_image_upload_mb'=>[1,50],
        'updater_backup_retention'=>[3,100],
        'recovery_update_history_limit'=>[20,500],
        'recovery_backup_history_limit'=>[20,500],
        'loyalty_points_per_review'=>[0,10000],
        'referral_reward_points'=>[0,100000],
        'homepage_deals_limit'=>[1,24],
        'homepage_events_limit'=>[1,24],
        'homepage_jobs_limit'=>[1,30],
        'homepage_property_limit'=>[1,24],
        'homepage_guide_limit'=>[1,24],
        'homepage_new_business_limit'=>[1,24],
        'homepage_nearby_limit'=>[1,30],
        'homepage_restaurant_limit'=>[1,24],
        'news_items_per_page'=>[4,50],
        'google_maps_default_zoom'=>[2,21],
        'google_maps_height'=>[360,850],
    ];
    if(isset($intRules[$key]))return (string)max($intRules[$key][0],min($intRules[$key][1],(int)$value));
    if($key==='subscription_tax_percent')return (string)max(0,min(100,(float)$value));
    if($key==='payment_reference_prefix'){return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$value)?:'SHP',0,10));}
    return trim($value);
}

if(isset($_GET['export_settings'])){
    $data=site_settings(true);unset($data['smtp_password']);
    header('Content-Type: application/json');header('Content-Disposition: attachment; filename="ShahkotPK-platform-settings-'.date('Ymd').'.json"');
    echo json_encode(['app'=>'ShahkotPK','version'=>setting('installed_app_version',''),'exported_at'=>date('c'),'settings'=>$data],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();$action=(string)($_POST['action']??'save_module');$module=setting_slug((string)($_POST['module']??''));
        if(!isset($modules[$module]))throw new RuntimeException('Unknown settings module.');
        $fields=$modules[$module]['fields'];
        if($action==='reset_module'){
            $vals=[];foreach($fields as $k=>$m)if(array_key_exists($k,$defaults))$vals[$k]=(string)$defaults[$k];
            save_settings($vals);$success=$modules[$module]['title'].' reset to defaults.';
        }else{
            $vals=[];
            foreach($fields as $k=>$m){
                $type=$m[0];
                if($type==='toggle')$vals[$k]=isset($_POST[$k])?'1':'0';
                else{
                    $value=(string)($_POST[$k]??'');
                    if($type==='password'&&$value==='')$value=(string)setting($k,'');
                    $vals[$k]=setting_clamp($k,$value);
                }
            }
            save_settings($vals);$success=$modules[$module]['title'].' settings saved.';
        }
    }catch(Throwable $e){$error=$e->getMessage();}
}
$s=site_settings(true);
$module=setting_slug((string)($_GET['module']??'overview'));
if($module!=='overview'&&!isset($modules[$module]))$module='overview';
$enabledCount=0;foreach($modules as $m){if(!$m['status_key']||setting_bool($m['status_key'],false))$enabledCount++;}
$maintenance=setting_bool('maintenance_mode',false);$smtp=setting_bool('smtp_enabled',false);$updater=setting_bool('update_system_enabled',true);
require __DIR__.'/../app/layout.php';page_start('Platform Settings',true);
?>
<link rel="stylesheet" href="/assets/platform-settings-2.8.0.css?v=280">
<div class="settings-hero">
<div><span>ADMIN CONTROL CENTER</span><h2>Platform Settings</h2><p>Professional module-by-module configuration for ShahkotPK. Search a module, open its settings, make focused changes and manage every major platform system from one control center.</p></div>
<div class="settings-hero-status"><small>Platform Modules</small><b><?=e($enabledCount)?> / <?=e(count($modules))?></b><span><?=$maintenance?'Maintenance Active':'Production Ready'?></span></div>
</div>
<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?><?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

<div class="settings-health">
<article><i>WEB</i><div><b><?=$maintenance?'Maintenance':'Online'?></b><span>Public Platform</span></div></article>
<article><i>MAIL</i><div><b><?=$smtp?'Configured':'Disabled'?></b><span>SMTP Service</span></div></article>
<article><i>UP</i><div><b><?=$updater?'Enabled':'Disabled'?></b><span>Recovery Updater</span></div></article>
<article><i>CMS</i><div><b><?=setting_bool('cms_media_uploads_enabled',true)?'Active':'Restricted'?></b><span>Homepage CMS</span></div></article>
</div>

<div class="platform-settings-layout">
<aside class="settings-sidebar">
<div class="settings-search"><input id="settingsSearch" placeholder="Search settings module..."></div>
<a class="<?=$module==='overview'?'active':''?>" data-settings-search="overview dashboard modules" href="?module=overview"><i>▦</i><span><b>Overview</b><small>All modules</small></span></a>
<?php foreach($modules as $slug=>$m):?>
<a class="<?=$module===$slug?'active':''?>" data-settings-search="<?=e(strtolower($m['title'].' '.$m['description']))?>" href="?module=<?=e($slug)?>"><i><?=e($m['icon'])?></i><span><b><?=e($m['title'])?></b><small><?=e($m['description'])?></small></span><?php if($m['status_key']):?><em class="<?=setting_bool($m['status_key'],false)?'on':'off'?>"><?=setting_bool($m['status_key'],false)?'ON':'OFF'?></em><?php endif;?></a>
<?php endforeach;?>
</aside>

<main class="settings-main">
<?php if($module==='overview'):?>
<div class="settings-overview-head"><div><h3>System Modules</h3><p>Open any module to configure its behavior. Manager shortcuts take you directly to operational screens.</p></div><a class="btn ghost" href="?export_settings=1">Export Safe Settings JSON</a></div>
<div class="settings-module-grid">
<?php foreach($modules as $slug=>$m):$status=$m['status_key']?setting_bool($m['status_key'],false):true;?>
<article data-module-card="<?=e(strtolower($m['title'].' '.$m['description']))?>">
<div class="module-card-top"><i><?=e($m['icon'])?></i><span class="module-status <?=$status?'on':'off'?>"><?=$status?'ACTIVE':'DISABLED'?></span></div>
<h3><?=e($m['title'])?></h3><p><?=e($m['description'])?></p>
<div class="module-actions"><a href="?module=<?=e($slug)?>">Configure</a><?php if($m['link']):?><a target="<?=$m['link']==='/'?'_blank':'_self'?>" href="<?=e($m['link'])?>">Open Module ↗</a><?php endif;?></div>
</article>
<?php endforeach;?>
</div>
<div class="settings-manager-shortcuts card">
<div><h3>Operational Managers</h3><p>Settings control behavior; manager screens control records and day-to-day operations.</p></div>
<nav><a href="/admin/users.php">Users</a><a href="/admin/businesses.php">Businesses</a><a href="/admin/categories.php">Categories</a><a href="/admin/cities.php">Cities</a><a href="/admin/plans.php">Subscriptions</a><a href="/admin/ads.php">Ads</a><a href="/admin/payments.php">Payments</a><a href="/admin/accounts.php">Accounts</a><a href="/admin/homepage.php">Homepage CMS</a><a href="/admin/themes.php">Themes</a><a href="/admin/updates.php">Recovery</a></nav>
</div>
<?php else:$m=$modules[$module];?>
<section class="card module-settings-card">
<div class="module-settings-header"><div class="module-icon"><?=e($m['icon'])?></div><div><span>PLATFORM MODULE</span><h3><?=e($m['title'])?></h3><p><?=e($m['description'])?></p></div><div class="module-header-actions"><?php if($m['link']):?><a class="btn ghost" href="<?=e($m['link'])?>">Open Manager ↗</a><?php endif;?></div></div>
<form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="module" value="<?=e($module)?>"><input type="hidden" name="action" value="save_module">
<div class="module-field-grid">
<?php foreach($m['fields'] as $k=>$meta):$type=$meta[0];$label=$meta[1];$help=$meta[2];$value=(string)($s[$k]??$defaults[$k]??'');?>
<?php if($type==='toggle'):?>
<div class="professional-toggle full"><div><label><?=e($label)?></label><p><?=e($help)?></p><code><?=e($k)?></code></div><label class="pro-switch"><input type="checkbox" name="<?=e($k)?>" <?=$value==='1'?'checked':''?>><span></span></label></div>
<?php elseif($type==='textarea'):?>
<div class="setting-field full"><label><?=e($label)?></label><p><?=e($help)?></p><textarea class="input" rows="4" name="<?=e($k)?>"><?=e($value)?></textarea></div>
<?php elseif($type==='select'):?>
<div class="setting-field"><label><?=e($label)?></label><p><?=e($help)?></p><select class="input" name="<?=e($k)?>"><?php foreach($meta[3] as $v=>$text):?><option value="<?=e($v)?>" <?=$value===(string)$v?'selected':''?>><?=e($text)?></option><?php endforeach;?></select></div>
<?php elseif($type==='color'):?>
<div class="setting-field"><label><?=e($label)?></label><p><?=e($help)?></p><div class="color-input"><input type="color" name="<?=e($k)?>" value="<?=e($value?:'#176b46')?>"><input class="input" value="<?=e($value)?>" readonly></div></div>
<?php elseif($type==='password'):?>
<div class="setting-field"><label><?=e($label)?></label><p><?=e($help)?></p><input class="input" type="password" name="<?=e($k)?>" placeholder="<?=$value!==''?'Saved — leave blank to keep':'Enter secret'?>" autocomplete="new-password"></div>
<?php else:?>
<div class="setting-field"><label><?=e($label)?></label><p><?=e($help)?></p><input class="input" type="<?=e($type)?>" name="<?=e($k)?>" value="<?=e($value)?>"></div>
<?php endif;?>
<?php endforeach;?>
</div>
<div class="module-save-bar"><span>Changes apply immediately after saving.</span><div><button class="btn" type="submit">Save <?=e($m['title'])?></button><button class="btn ghost" type="submit" name="action" value="reset_module" onclick="return confirm('Reset this module to ShahkotPK defaults?')">Reset Module</button></div></div>
</form>
</section>
<?php endif;?>
</main>
</div>
<script>
(function(){
 const q=document.getElementById('settingsSearch');
 q?.addEventListener('input',()=>{const s=q.value.trim().toLowerCase();document.querySelectorAll('[data-settings-search]').forEach(x=>x.style.display=x.dataset.settingsSearch.includes(s)?'flex':'none');document.querySelectorAll('[data-module-card]').forEach(x=>x.style.display=x.dataset.moduleCard.includes(s)?'block':'none')});
 document.querySelectorAll('.color-input input[type=color]').forEach(c=>c.addEventListener('input',()=>c.nextElementSibling.value=c.value));
})();
</script>
<?php require __DIR__.'/../app/end.php';?>
