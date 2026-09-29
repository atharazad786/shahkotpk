<?php
declare(strict_types=1);

function shahkot_default_settings(): array {
    return [
        // General
        'site_name' => 'ShahkotPK',
        'site_tagline' => 'Your Digital City Guide',
        'default_city' => 'Shahkot',
        'timezone' => 'Asia/Karachi',
        'language' => 'en',
        'currency' => 'PKR',

        // Contact
        'support_phone' => '',
        'support_email' => '',
        'support_whatsapp' => '',
        'business_address' => '',

        // Branding
        'logo_url' => '',
        'favicon_url' => '',
        'brand_primary_color' => '#176b46',
        'brand_secondary_color' => '#0f3d29',

        // SEO
        'seo_title' => 'ShahkotPK — Shahkot Business Directory & City Guide',
        'seo_description' => 'Discover Shahkot businesses, services, places, offers and useful city information.',
        'seo_keywords' => 'Shahkot, Shahkot businesses, Shahkot directory, Shahkot city guide',
        'og_image' => '',
        'robots_index' => '1',

        // Authentication / accounts
        'login_enabled' => '1',
        'registration_enabled' => '1',
        'customer_signup_enabled' => '1',
        'shopkeeper_signup_enabled' => '1',
        'email_required' => '0',
        'phone_required' => '0',
        'customer_approval_mode' => 'auto',
        'shopkeeper_approval_mode' => 'manual',
        'show_login_in_header' => '1',
        'show_pricing_in_header' => '1',

        // Business & directory
        'business_registration_enabled' => '1',
        'directory_enabled' => '1',
        'search_enabled' => '1',
        'featured_businesses_enabled' => '1',
        'verified_badge_enabled' => '1',

        // Commercial modules
        'subscriptions_enabled' => '1',
        'free_plan_enabled' => '1',
        'paid_plans_enabled' => '1',
        'advertisements_enabled' => '1',
        'sponsored_listings_enabled' => '1',
        'ticker_public_enabled' => '1',
        'ticker_admin_enabled' => '1',
        'payment_system_enabled' => '1',
        'payments_manual_review_enabled' => '1',
        'payfast_enabled' => '0',

        // Homepage global switches
        'admin_theme_slug' => 'aurora-command',
        'landing_theme_slug' => 'metro-portal',
        'admin_theme_animations' => '1',
        'landing_theme_animations' => '1',
        'admin_readable_typography_enabled' => '1',
        'admin_font_scale' => 'comfortable',
        'homepage_slider_enabled' => '1',
        'homepage_slider_interval_ms' => '5500',
        'homepage_search_enabled' => '1',
        'homepage_animations_enabled' => '1',
        'mobile_menu_enabled' => '1',
        'mobile_sticky_cta_enabled' => '1',
        'homepage_directory_enabled' => '1',
        'homepage_featured_enabled' => '1',
        'homepage_gallery_enabled' => '1',
        'homepage_city_info_enabled' => '1',

        // Uploads
        'image_uploads_enabled' => '1',
        'max_image_upload_mb' => '5',
        'allowed_image_types' => 'jpg,jpeg,png,webp,gif',

        // Complete city guide portal
        'city_portal_enabled' => '1',
        'city_weather_text' => 'Weather updates available from Admin',
        'city_emergency_text' => 'Emergency: Rescue 1122',
        'homepage_category_explorer_enabled' => '1',
        'homepage_deals_enabled' => '1',
        'homepage_city_guide_enabled' => '1',
        'homepage_events_enabled' => '1',
        'homepage_jobs_enabled' => '1',
        'homepage_property_enabled' => '1',
        'homepage_new_businesses_enabled' => '1',
        'homepage_nearby_enabled' => '1',
        'homepage_restaurants_enabled' => '1',
        'homepage_sponsored_spotlight_enabled' => '1',
        'homepage_advertise_cta_enabled' => '1',
        'homepage_deals_limit' => '6',
        'homepage_events_limit' => '4',
        'homepage_jobs_limit' => '5',
        'homepage_property_limit' => '6',
        'homepage_guide_limit' => '6',
        'homepage_new_business_limit' => '6',
        'homepage_nearby_limit' => '8',
        'homepage_restaurant_limit' => '6',

        // Blogging System
        'blog_portal_enabled' => '1',
        'blog_home_enabled' => '1',
        'blog_comments_enabled' => '1',
        'blog_comments_moderation' => '1',
        'blog_video_enabled' => '1',
        'blog_scheduled_publishing' => '1',
        'blog_items_per_page' => '12',
        'blog_show_views' => '1',
        'blog_homepage_version' => '0',

        // News Portal
        'news_portal_enabled' => '1',
        'news_home_enabled' => '1',
        'news_breaking_enabled' => '1',
        'news_video_enabled' => '1',
        'news_default_language' => 'en',
        'news_items_per_page' => '12',
        'news_show_views' => '1',
        'news_allow_scheduled' => '1',


        // Live Broadcast Center
        'live_portal_enabled' => '1',
        'live_home_enabled' => '1',
        'live_items_limit' => '4',
        'live_autoplay_muted' => '1',

        // Engagement / popups / notifications
        'engagement_popups_enabled' => '1',
        'engagement_notifications_enabled' => '1',
        'engagement_browser_alerts_enabled' => '0',

        // Marketplace / E-commerce
        'store_enabled' => '1',
        'store_home_enabled' => '1',
        'store_items_limit' => '8',
        'store_seller_approval_required' => '1',
        'store_cod_enabled' => '1',
        'store_advance_enabled' => '1',
        'store_auction_enabled' => '1',
        'store_digital_enabled' => '1',
        'store_homepage_version' => '0',
        'live_homepage_version' => '0',

        // Commercial Growth Suite
        'growth_suite_enabled' => '1',
        'reviews_enabled' => '1',
        'reviews_require_moderation' => '1',
        'loyalty_points_per_review' => '20',
        'verification_enabled' => '1',
        'verification_fee' => '0',
        'bookings_enabled' => '1',
        'leads_enabled' => '1',
        'coupons_enabled' => '1',
        'loyalty_enabled' => '1',
        'referrals_enabled' => '1',
        'referral_reward_points' => '100',
        'whatsapp_automation_enabled' => '0',
        'whatsapp_api_endpoint' => '',
        'whatsapp_access_token' => '',
        'analytics_enabled' => '1',
        'emergency_portal_enabled' => '1',
        'restaurant_menu_enabled' => '1',
        'service_marketplace_enabled' => '1',
        'classifieds_enabled' => '1',
        'subscription_entitlements_enabled' => '1',
        'seller_staff_enabled' => '1',
        'moderation_center_enabled' => '1',
        'seo_automation_enabled' => '1',
        'pwa_enabled' => '1',
        'push_notifications_enabled' => '1',
        'multi_city_management_enabled' => '1',
        'commercial_reports_enabled' => '1',
        'growth_home_enabled' => '1',
        'growth_homepage_version' => '0',

        // Commercial Operations & Automation Suite
        'commercial_operations_enabled' => '1',
        'marketplace_default_commission_percent' => '10',
        'seller_min_payout' => '1000',
        'seller_payout_fee' => '0',
        'support_center_enabled' => '1',
        'disputes_enabled' => '1',
        'delivery_management_enabled' => '1',
        'inventory_management_enabled' => '1',
        'vendor_purchasing_enabled' => '1',
        'queue_enabled' => '1',
        'queue_batch_size' => '10',
        'backup_retention_days' => '14',
        'operations_health_enabled' => '1',
        'operations_cron_token' => '',

        // Unified Platform v4.0.0 - Security, AI, Franchise, Mobile API & POS
        'security_suite_enabled' => '1',
        'security_2fa_enabled' => '0',
        'security_2fa_roles' => 'admin,editor,shopkeeper',
        'security_otp_minutes' => '10',
        'security_login_window_minutes' => '15',
        'security_login_max_failures' => '8',
        'security_headers_enabled' => '1',
        'security_csp_report_only' => '1',
        'security_session_tracking_enabled' => '1',
        'security_backup_scheduler_enabled' => '1',
        'security_backup_hour' => '3',
        'security_backup_retention_days' => '14',
        'ai_suite_enabled' => '1',
        'ai_provider' => 'local',
        'ai_endpoint' => '',
        'ai_api_key' => '',
        'ai_model' => '',
        'ai_timeout_seconds' => '25',
        'ai_smart_search_enabled' => '1',
        'ai_content_enabled' => '1',
        'ai_recommendations_enabled' => '1',
        'franchise_suite_enabled' => '1',
        'mobile_api_enabled' => '1',
        'mobile_api_token_days' => '90',
        'seller_pos_enabled' => '1',
        'pos_receipt_footer' => 'Thank you for shopping local with ShahkotPK.',

        // Commercial Growth & Monetization v4.1.0
        'commercial_growth_v41_enabled' => '1',
        'self_service_ads_enabled' => '1',
        'listing_boosts_enabled' => '1',
        'subscription_auto_renew_enabled' => '1',
        'subscription_grace_days' => '7',
        'subscription_renewal_reminder_days' => '14',
        'monetization_commission_enabled' => '1',
        'qr_center_enabled' => '1',
        'qr_provider_base' => 'https://quickchart.io/qr',
        'bulk_import_enabled' => '1',
        'media_library_enabled' => '1',
        'media_max_upload_mb' => '12',
        'redirect_manager_enabled' => '1',
        'seo_health_enabled' => '1',
        'campaign_center_enabled' => '1',
        'notification_rules_enabled' => '1',
        'unified_inbox_enabled' => '1',
        'api_v2_enabled' => '1',
        'regression_center_enabled' => '1',
        'seller_growth_dashboard_enabled' => '1',
        'customer_super_dashboard_enabled' => '1',
        'franchise_settlement_enabled' => '1',

        // Google Maps & city discovery
        'google_maps_enabled' => '1',
        'google_maps_api_key' => '',
        'google_maps_default_lat' => '31.5709000',
        'google_maps_default_lng' => '73.4853000',
        'google_maps_default_zoom' => '14',
        'google_maps_map_type' => 'roadmap',
        'google_maps_home_enabled' => '1',
        'google_maps_vertical_pages_enabled' => '1',
        'google_maps_businesses_enabled' => '1',
        'google_maps_admin_enabled' => '1',
        'google_maps_show_businesses' => '1',
        'google_maps_show_guide' => '1',
        'google_maps_show_deals' => '1',
        'google_maps_show_events' => '1',
        'google_maps_show_jobs' => '1',
        'google_maps_show_property' => '1',
        'google_maps_height' => '560',
        'google_maps_fit_markers' => '1',
        'google_maps_places_search' => '1',

        // City & location
        'city_location_features_enabled' => '1',
        'browser_geolocation_enabled' => '1',
        'default_map_provider' => 'google',
        'default_map_zoom' => '14',

        // CMS / builder
        'cms_media_uploads_enabled' => '1',
        'cms_theme_uploads_enabled' => '1',
        'cms_custom_css_enabled' => '1',
        'cms_revisions_limit' => '30',

        // Subscription billing
        'subscription_invoice_due_days' => '7',
        'subscription_tax_percent' => '0',
        'subscription_auto_expire' => '1',
        'subscription_expiry_reminder_days' => '30',

        // Advertisement delivery
        'advertisement_auto_schedule' => '1',
        'ad_default_priority' => '10',
        'ad_default_frequency_cap' => '0',
        'ad_allow_external_urls' => '1',

        // Payment controls
        'payment_reference_prefix' => 'SHP',
        'payment_proof_max_mb' => '5',
        'payment_proof_required' => '0',

        // Accounts
        'accounts_manual_journal_enabled' => '1',
        'accounts_csv_export_enabled' => '1',
        'accounts_business_ledger_enabled' => '1',

        // Directory/search
        'search_results_limit' => '100',

        // Authentication security
        'password_min_length' => '8',

        // Recovery Center
        'updater_auto_backup' => '1',
        'updater_auto_rollback' => '1',
        'updater_backup_database' => '1',
        'updater_backup_retention' => '15',
        'recovery_update_history_limit' => '150',
        'recovery_backup_history_limit' => '200',

        // Maintenance
        'maintenance_message' => 'Website is temporarily under maintenance. Please check again shortly.',

        // System / security
        'update_system_enabled' => '1',
        'maintenance_mode' => '0',
        'debug_mode' => '0',
        'csrf_protection_enabled' => '1',
        'session_regenerate_on_login' => '1',

        // Email / SMTP
        'forgot_password_enabled' => '1',
        'password_reset_expiry_minutes' => '60',
        'smtp_enabled' => '0',
        'smtp_host' => '',
        'smtp_port' => '587',
        'smtp_encryption' => 'tls',
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_from_email' => '',
        'smtp_from_name' => 'ShahkotPK',
        'smtp_timeout' => '15',
    ];
}

function site_settings(bool $refresh=false): array {
    static $cache=null;
    if($cache!==null && !$refresh) return $cache;

    $cache=shahkot_default_settings();

    try{
        $rows=db()->query("SELECT setting_key,setting_value FROM settings")->fetchAll();
        foreach($rows as $row){
            $cache[(string)$row['setting_key']] = (string)($row['setting_value'] ?? '');
        }
    }catch(Throwable $e){}

    return $cache;
}

function setting(string $key, $default=null) {
    $settings=site_settings();
    $value=array_key_exists($key,$settings) ? $settings[$key] : $default;
    if(function_exists('tenant_brand')){
        if($key==='site_name')return tenant_brand('site_name',$value);
        if($key==='default_city')return tenant_brand('city_name',$value);
        if($key==='timezone')return tenant_brand('timezone',$value);
        if($key==='currency')return tenant_brand('currency',$value);
        if($key==='support_email')return tenant_brand('support_email',$value);
        if($key==='support_phone')return tenant_brand('support_phone',$value);
    }
    return $value;
}

function setting_bool(string $key, bool $default=false): bool {
    $fallback=$default?'1':'0';
    return in_array((string)setting($key,$fallback),['1','true','yes','on'],true);
}

function setting_int(string $key, int $default=0): int {
    return (int)setting($key,(string)$default);
}

function save_setting(string $key, string $value): void {
    $q=db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $q->execute([$key,$value]);
}

function save_settings(array $values): void {
    db()->beginTransaction();
    try{
        $q=db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        foreach($values as $key=>$value){
            $q->execute([(string)$key,(string)$value]);
        }
        db()->commit();
    }catch(Throwable $e){
        if(db()->inTransaction()) db()->rollBack();
        throw $e;
    }
    site_settings(true);
}

function ensure_default_settings(): void {
    $defaults=shahkot_default_settings();
    try{
        $q=db()->prepare("INSERT IGNORE INTO settings(setting_key,setting_value) VALUES(?,?)");
        foreach($defaults as $key=>$value) $q->execute([$key,$value]);
        site_settings(true);
    }catch(Throwable $e){}
}

function feature_enabled(string $key, bool $default=true): bool {
    $enabled=setting_bool($key,$default);
    if(function_exists('tenant_feature_enabled')) return tenant_feature_enabled($key,$enabled);
    return $enabled;
}

function maintenance_should_block(): bool {
    if(!setting_bool('maintenance_mode',false)) return false;
    $path=$_SERVER['PHP_SELF']??'';
    if(str_contains($path,'/admin/')) return false;
    if(str_ends_with($path,'/logout.php')) return false;
    return true;
}

function output_maintenance_page(): never {
    http_response_code(503);
    header('Retry-After: 3600');
    $name=htmlspecialchars((string)setting('site_name','ShahkotPK'),ENT_QUOTES,'UTF-8');
    $message=htmlspecialchars((string)setting('maintenance_message','Website is temporarily under maintenance. Please check again shortly.'),ENT_QUOTES,'UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Maintenance - '.$name.'</title><style>body{margin:0;font-family:Arial;background:#f3f6fb;color:#172033;display:grid;place-items:center;min-height:100vh}.box{max-width:580px;background:white;border:1px solid #e2e8f0;border-radius:20px;padding:34px;box-shadow:0 18px 45px #0001;text-align:center}h1{margin-top:0}</style></head><body><div class="box"><h1>'.$name.'</h1><p>'.$message.'</p></div></body></html>';
    exit;
}
