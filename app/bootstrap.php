<?php
declare(strict_types=1);
/* SHAHKOTPK_PUBLIC_RUNTIME_V1101_BOOTSTRAP */
require_once __DIR__.'/public_runtime_v1101.php';
require_once __DIR__.'/Security.php';
require_once __DIR__ . '/runtime_guard.php';

$configFile = __DIR__ . '/../config/config.php';
$lockFile = __DIR__ . '/../config/install.lock';

if (!is_file($lockFile) || !is_file($configFile)) {
    if (is_file(__DIR__ . '/../install.php')) {
        header('Location: /install.php', true, 302);
        exit;
    }
    http_response_code(500);
    exit('ShahkotPK is not installed correctly. Missing config.php or install.lock.');
}

$GLOBALS['config'] = require $configFile;

// License Guard
(function() {
    global $config;
    if (php_sapi_name() === 'cli' || strpos($_SERVER['REQUEST_URI'] ?? '', '/api.php') !== false) return;
    
    $cache_file = __DIR__ . '/../storage/tmp/license_guard.json';
    $cache_data = is_file($cache_file) ? json_decode(file_get_contents($cache_file), true) : null;
    $now = time();
    
    // Check every 12 hours (43200 seconds)
    if (!$cache_data || ($now - ($cache_data['last_check'] ?? 0)) > 43200) {
        $api_url = "https://licenses.shahkotpk.com/api.php";
        $domain = $_SERVER['HTTP_HOST'] ?? 'unknown';
        $key = $config['license_key'] ?? '';
        
        $ch = curl_init($api_url);
        $payload = json_encode(['license_key' => $key, 'domain' => $domain, 'action' => 'ping']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response && $http_code == 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['status'])) {
                $status = $data['status'];
                // Only cache for 12 hours if it's a valid response. If error, cache for 1 minute.
                $cache_time = ($status === 'error') ? ($now - 43140) : $now; 
                
                if (!is_dir(dirname($cache_file))) @mkdir(dirname($cache_file), 0755, true);
                @file_put_contents($cache_file, json_encode(['last_check' => $cache_time, 'status' => $status, 'message' => $data['message'] ?? '']));
            }
        }
    }
    
    $cached_status = is_file($cache_file) ? json_decode(file_get_contents($cache_file), true) : null;
    if ($cached_status && $cached_status['status'] !== 'active') {
        
        // Handle New License Key Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_license_key'])) {
            $new_key = trim($_POST['new_license_key']);
            
            // 1. Update config.php
            $config_path = __DIR__ . '/../config/config.php';
            $cfg = file_exists($config_path) ? include($config_path) : [];
            if (is_array($cfg)) {
                $cfg['license_key'] = $new_key;
                $written = @file_put_contents($config_path, "<?php\nreturn " . var_export($cfg, true) . ";\n");
                if ($written === false) {
                    die("<b>Permission Error:</b> Cannot write to <code>config/config.php</code>. Please change the file permissions to 0644 or 0777 temporarily on your cPanel, then try again.");
                }
            }
            
            // 2. Clear the cache so it re-checks immediately
            if (file_exists($cache_file)) {
                @unlink($cache_file);
                @file_put_contents($cache_file, json_encode(['last_check' => 0])); // Force overwrite if unlink fails
            }
            
            // 3. Refresh the page to trigger a new ping
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }

        http_response_code(403);
        $msg = htmlspecialchars($cached_status['message'] ?? 'Your license is invalid or expired.');
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>License Expired / Blocked</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #071425; color: #eaf2fb; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .box { background: rgba(14, 30, 50, 0.9); padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; max-width: 450px; width: 100%; border: 1px solid rgba(255,255,255,0.1); }
                h2 { color: #ff8a95; margin-top: 0; }
                p { color: #bcd; margin-bottom: 25px; line-height: 1.5; }
                input[type='text'] { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.3); color: #fff; box-sizing: border-box; }
                button { background: #176B46; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
                button:hover { background: #125536; }
            </style>
        </head>
        <body>
            <div class='box'>
                <h2>System Access Blocked</h2>
                <p>{$msg}</p>
                <form method='POST'>
                    <input type='text' name='new_license_key' placeholder='Enter your new License Key' required>
                    <button type='submit'>Verify & Activate</button>
                </form>
            </div>
        </body>
        </html>
        ");
    }
})();
$config = $GLOBALS['config'];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['session_name'] ?? 'cityhub_session');
    try { session_start(); } catch (Throwable $e) { runtime_log('Session start failed', $e); }
}

function db(): PDO {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;

    $configArray = $GLOBALS['config'] ?? require(__DIR__ . '/../config/config.php');
    $c = $configArray['db'];
    $pdo = new PDO(
        "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4",
        $c['user'],
        $c['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $pdo;
}

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}

function csrf_check(): void {
    if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid security token. Refresh and try again.');
    }
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    try {
        $q = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
        $q->execute([$_SESSION['user_id']]);
        $u=$q->fetch() ?: null;
        if(!$u) unset($_SESSION['user_id']);
        return $u;
    } catch (Throwable $e) {
        runtime_log('Unable to resolve current user', $e);
        return null;
    }
}

function require_login(): array {
    $u = current_user();
    if (!$u) {
        header('Location: /login.php', true, 302);
        exit;
    }
    return $u;
}

function require_admin(): array {
    // Legacy compatibility: authorization is permission-based after permissions.php is loaded.
    return require_staff();
}



require_once __DIR__ . '/migrations.php';
try {
    run_packaged_migrations(false);
} catch (Throwable $e) {
    migration_log('Bootstrap migration check failed: '.$e->getMessage());
}

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/tenancy_v5.php';
ensure_default_settings();

date_default_timezone_set((string)tenant_brand('timezone',setting('timezone','Asia/Karachi')));
$config['app_name']=(string)tenant_brand('site_name',setting('site_name',$config['app_name']??'ShahkotPK'));

if (maintenance_should_block()) {
    output_maintenance_page();
}

require_once __DIR__ . '/permissions.php';
if(function_exists('tenant_boot_user_context'))tenant_boot_user_context();
if(function_exists('tenant_enforce_admin_request_scope'))tenant_enforce_admin_request_scope();
if(function_exists('tenant_brand')){
    $config['app_name']=(string)tenant_brand('site_name',setting('site_name',$config['app_name']??'ShahkotPK'));
    date_default_timezone_set((string)tenant_brand('timezone',setting('timezone','Asia/Karachi')));
}

require_once __DIR__ . '/ticker.php';

require_once __DIR__ . '/theme_engine.php';

require_once __DIR__ . '/admin_profile.php';

require_once __DIR__ . '/cms.php';

require_once __DIR__ . '/ads.php';

runtime_safe_require(__DIR__ . '/city_portal.php','City Portal');
runtime_safe_require(__DIR__ . '/maps.php','Google Maps');
runtime_safe_require(__DIR__ . '/news.php','News Portal');
runtime_safe_require(__DIR__ . '/blog.php','Blogging');
runtime_safe_require(__DIR__ . '/live.php','Live Broadcast Center');
runtime_safe_require(__DIR__ . '/engagement.php','Engagement Center');
runtime_safe_require(__DIR__ . '/store.php','Marketplace');
runtime_safe_require(__DIR__ . '/growth.php','Commercial Growth Suite');
runtime_safe_require(__DIR__ . '/operations.php','Commercial Operations Suite');
runtime_safe_require(__DIR__ . '/security_v4.php','Security & Reliability Suite');
runtime_safe_require(__DIR__ . '/activity_log.php','Activity Logs & IP Location History');
runtime_safe_require(__DIR__ . '/ai_v4.php','AI Suite');
runtime_safe_require(__DIR__ . '/franchise_v4.php','Multi-City Franchise Suite');
runtime_safe_require(__DIR__ . '/pos_v4.php','Seller POS Suite');
runtime_safe_require(__DIR__ . '/platform_v4_cron.php','Unified Platform Cron');
runtime_safe_require(__DIR__ . '/monetization_v41.php','Commercial Growth & Monetization Suite');
if(function_exists('security_apply_headers'))security_apply_headers();
if(function_exists('security_enforce_current_session'))security_enforce_current_session();
if(function_exists('activity_register_request_logger'))activity_register_request_logger();
if(!function_exists('operations_module_loaded')){function operations_module_loaded(): bool {return false;}}

// Public fallbacks: a broken optional module must not make the whole site HTTP 500.
if(!function_exists('city_portal_upgrade_homepage')){function city_portal_upgrade_homepage(): void {}}
if(!function_exists('city_portal_context')){function city_portal_context(): array {return [];}}
if(!function_exists('city_portal_cities')){function city_portal_cities(): array {return [];}}
if(!function_exists('city_portal_category_counts')){function city_portal_category_counts(int $limit=30): array {return [];}}
if(!function_exists('news_upgrade_homepage')){function news_upgrade_homepage(): void {}}
if(!function_exists('blog_upgrade_homepage')){function blog_upgrade_homepage(): void {}}
if(!function_exists('live_upgrade_homepage')){function live_upgrade_homepage(): void {}}
if(!function_exists('store_upgrade_homepage')){function store_upgrade_homepage(): void {}}
if(!function_exists('live_render_widget')){function live_render_widget(array $sections=[]): void {}}
if(!function_exists('store_render_widget')){function store_render_widget(): void {}}
if(!function_exists('engagement_render')){function engagement_render(): void {}}
if(!function_exists('google_maps_ready')){function google_maps_ready(): bool {return false;}}
if(!function_exists('render_google_maps_assets')){function render_google_maps_assets(): void {}}
if(!function_exists('google_maps_public_module')){function google_maps_public_module(string $title,array $types=[],string $description=''): void {}}
if(!function_exists('google_maps_allowed_types')){function google_maps_allowed_types(): array {return [];}}
if(!function_exists('growth_enabled')){function growth_enabled(string $key='growth_suite_enabled',bool $default=true): bool {return false;}}
if(!function_exists('growth_upgrade_homepage')){function growth_upgrade_homepage(): void {}}
if(!function_exists('lt_growth_widget')){function lt_growth_widget(array $sections): void {}}
if(!function_exists('growth_track')){function growth_track(string $event,string $entityType='',?int $entityId=null,?int $businessId=null,array $meta=[]): void {}}
if(!function_exists('growth_register_referral_click')){function growth_register_referral_click(string $code): void {}}
if(!function_exists('growth_attach_referral')){function growth_attach_referral(int $newUserId): void {}}
if(!function_exists('growth_render_seo')){function growth_render_seo(string $type,int $id,array $fallback=[]): string {return '';}}

// Pre-render Universal Header & Footer for the public runtime injector
register_shutdown_function(function() {
    ob_start();
    $hf = __DIR__.'/views/header.php';
    if(file_exists($hf)) require $hf;
    $GLOBALS['skUniversalHeader'] = ob_get_clean();

    ob_start();
    $ff = __DIR__.'/views/footer.php';
    if(file_exists($ff)) require $ff;
    $GLOBALS['skUniversalFooter'] = ob_get_clean();
});
