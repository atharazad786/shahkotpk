<?php
// app/views/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = function_exists('current_user') ? current_user() : null;
$is_logged_in = (bool)$user;

$dashboard_url = '/account.php';
$dashboard_label = 'Login / Register';
if ($is_logged_in) {
    $dashboard_label = 'My Account';
    if (function_exists('can_access_admin_panel') && can_access_admin_panel($user)) {
        $dashboard_url = function_exists('staff_landing_url') ? staff_landing_url($user) : '/admin/';
    } elseif (isset($user['role']) && $user['role'] === 'shopkeeper') {
        $dashboard_url = '/shopkeeper.php';
    }
}

$hm702_file = __DIR__.'/../header_menu_v702.php';
if(file_exists($hm702_file)) require_once $hm702_file;

$site_logo = function_exists('hm702_logo_url') ? hm702_logo_url() : (function_exists('setting') ? setting('site_logo', '') : '');
$site_name = function_exists('hm702_brand_name') ? hm702_brand_name() : (function_exists('setting') ? setting('site_name', 'ShahkotPK') : 'ShahkotPK');

// Fetch categories dynamically
$categories = [];
try {
    if (function_exists('db')) {
        $categories = db()->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {}

// Dummy counts for badges if functions don't exist
$fav_count = 0;
$notif_count = class_exists('NotificationSystem') ? NotificationSystem::getUnreadCount($user['id'] ?? 0) : 0;

?>
<link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>">
<style>
/* New Header Styles based on Reference */
:root {
    --hdr-bg: var(--primary-color, #176b46); /* Match the primary theme color */
    --hdr-accent: #105234; /* Primary hover */
    --hdr-accent-hover: #0c3e27; 
    --hdr-text: #ffffff;
    --hdr-text-muted: rgba(255, 255, 255, 0.7);
    --hdr-radius: 16px;
    --hdr-shadow: 0 10px 30px rgba(23, 107, 70, 0.3);
    --hdr-trans: all 0.25s ease;
}

.ultra-modern-header {
    width: 100%;
    margin-bottom: 20px;
    font-family: 'Inter', system-ui, sans-serif;
    position: relative;
    z-index: 1000;
}

/* TOP TIER - Dark Purple */
.hdr-top-tier {
    background: var(--hdr-bg);
    padding: 15px 20px;
    border-radius: 0 0 var(--hdr-radius) var(--hdr-radius);
    box-shadow: var(--hdr-shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    color: var(--hdr-text);
}

.hdr-left, .hdr-center, .hdr-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.hdr-center {
    flex: 1;
    max-width: 650px;
    margin: 0 auto;
}

/* Logo */
.hdr-logo {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: var(--hdr-text);
    font-weight: 800;
    font-size: 22px;
    letter-spacing: -0.5px;
}
.hdr-logo img {
    height: 40px;
    margin-right: 10px;
}

/* Category Button */
.hdr-cat-btn {
    background: var(--hdr-accent);
    border: 1px solid var(--hdr-accent-hover);
    color: var(--hdr-text);
    padding: 10px 16px;
    border-radius: var(--hdr-radius);
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: var(--hdr-trans);
    white-space: nowrap;
}
.hdr-cat-btn:hover {
    background: var(--hdr-accent-hover);
    box-shadow: 0 0 15px var(--hdr-shadow);
}

/* Search Bar */
.hdr-search-wrapper {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 50px;
    padding: 4px;
    width: 100%;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}
.hdr-search-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 10px 20px;
    font-size: 14px;
    color: #333;
    outline: none;
}
.hdr-search-cat-select {
    border: none;
    background: transparent;
    color: #666;
    padding: 10px;
    font-size: 14px;
    border-left: 1px solid #eaeaea;
    outline: none;
    cursor: pointer;
    max-width: 140px;
}
.hdr-search-btn {
    background: var(--hdr-accent);
    color: #fff;
    border: none;
    border-radius: 50px;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--hdr-trans);
}
.hdr-search-btn:hover {
    background: var(--hdr-accent-hover);
    box-shadow: 0 0 15px var(--hdr-accent);
}

/* Right Actions */
.hdr-action {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--hdr-text);
    text-decoration: none;
    transition: var(--hdr-trans);
    cursor: pointer;
    position: relative;
}
.hdr-action:hover {
    color: var(--hdr-accent-hover);
    transform: translateY(-2px);
}
.hdr-action-icon {
    font-size: 20px;
    position: relative;
}
.hdr-action-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    background: var(--hdr-accent);
    color: #fff;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    border: 2px solid #191039;
}
.hdr-action-text {
    display: flex;
    flex-direction: column;
    font-size: 12px;
}
.hdr-action-text span {
    color: var(--hdr-text-muted);
    font-size: 11px;
}
.hdr-action-text b {
    font-size: 13px;
    font-weight: 600;
}

/* BOTTOM TIER - Existing Navigation Tabs */
.hdr-bottom-tier {
    background: #ffffff;
    border-bottom: 1px solid #eaeaea;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.hdr-nav-link {
    color: #444;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    padding: 15px 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: var(--hdr-trans);
    position: relative;
}
.hdr-nav-link:hover {
    color: var(--hdr-accent);
}
.hdr-nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 3px;
    background: var(--hdr-accent);
    transition: var(--hdr-trans);
    border-radius: 3px 3px 0 0;
}
.hdr-nav-link:hover::after {
    width: 100%;
}
.hdr-nav-emergency {
    color: #ff4757;
}

/* LIVE Button */
.hdr-nav-live-btn {
    background: #e11d48;
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.3);
    transition: var(--hdr-trans);
    letter-spacing: 0.5px;
}
.hdr-nav-live-btn:hover {
    background: #be123c;
    box-shadow: 0 4px 15px rgba(225, 29, 72, 0.5);
    transform: translateY(-1px);
    color: #fff;
}
.live-dot {
    width: 6px;
    height: 6px;
    background: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: blinker 1.5s linear infinite;
}
@keyframes blinker {
    50% { opacity: 0.2; }
}

/* Mobile Responsive */
@media (max-width: 991px) {
    .hdr-top-tier { flex-wrap: wrap; border-radius: 0; padding: 10px 15px; }
    .hdr-center { order: 3; width: 100%; max-width: 100%; margin-top: 10px; }
    .hdr-action-text { display: none; }
    .hdr-cat-btn span { display: none; }
    .hdr-search-cat-select { display: none; }
    .hdr-bottom-tier { padding: 10px; gap: 10px; overflow-x: auto; justify-content: flex-start; }
    .hdr-nav-link { padding: 8px 10px; white-space: nowrap; }
}
</style>

<header class="ultra-modern-header">
    
    <!-- TOP TIER: Dark Purple -->
    <div class="hdr-top-tier">
        
        <div class="hdr-left">
            <a href="/" class="hdr-logo">
                <?php if (!empty($site_logo)): ?>
                    <img src="<?= htmlspecialchars($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>">
                <?php else: ?>
                    <?= htmlspecialchars($site_name) ?>
                <?php endif; ?>
            </a>
            
            <div class="hdr-cat-btn" title="All Categories">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                <span>All Categories</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
        </div>

        <div class="hdr-center">
            <form action="/search.php" method="GET" class="hdr-search-wrapper">
                <input type="text" name="q" class="hdr-search-input" placeholder="What are you looking for in Shahkot?" required>
                <select name="category_id" class="hdr-search-cat-select">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="hdr-search-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
        </div>

        <div class="hdr-right">
            <a href="<?= htmlspecialchars($dashboard_url) ?>" class="hdr-action">
                <div class="hdr-action-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div class="hdr-action-text">
                    <span>Account</span>
                    <b><?= htmlspecialchars($dashboard_label) ?></b>
                </div>
            </a>
            
            <?php if($is_logged_in): ?>
            <a href="/notifications.php" class="hdr-action">
                <div class="hdr-action-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <?php if($notif_count > 0): ?><span class="hdr-action-badge"><?= $notif_count ?></span><?php endif; ?>
                </div>
            </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- BOTTOM TIER: Existing Navigation Menus -->
    <div class="hdr-bottom-tier">
        <a href="/doctor-online.php" class="hdr-nav-link hdr-nav-emergency">DOCTORS</a>
        <a href="/businesses.php" class="hdr-nav-link">Directory</a>
        <a href="/shop.php" class="hdr-nav-link">Shop</a>
        <a href="/city-guide.php" class="hdr-nav-link">City Guide</a>
        <a href="/classifieds.php" class="hdr-nav-link">Classifieds</a>
        <a href="/property.php" class="hdr-nav-link">Property</a>
        <a href="/deals.php" class="hdr-nav-link">Deals</a>
        <a href="/events.php" class="hdr-nav-link">Events</a>
        <a href="/jobs.php" class="hdr-nav-link">Jobs</a>
        <a href="/news.php" class="hdr-nav-link">News</a>
        <a href="/blog.php" class="hdr-nav-link">Blog</a>
        <a href="/city-map.php" class="hdr-nav-link">Map</a>
        <?php if(function_exists('feature_enabled') && feature_enabled('subscriptions_enabled', true)): ?>
            <a href="/pricing.php" class="hdr-nav-link">Plans</a>
        <?php endif; ?>
        <a href="/live.php" class="hdr-nav-live-btn"><span class="live-dot"></span> LIVE</a>
    </div>

</header>
