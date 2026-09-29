<?php
require_once __DIR__.'/app/bootstrap.php';

// Require login and business owner role (Mock check)
$user = current_user();
if (!$user) {
    header('Location: /login.php');
    exit;
}

// Fetch analytics for their business
$businessId = $_GET['id'] ?? 0; // Assume they select their business or it's inferred
$stats = [
    'profile_views' => rand(150, 5000),
    'search_appearances' => rand(300, 10000),
    'phone_clicks' => rand(20, 200),
    'direction_clicks' => rand(10, 150)
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Analytics - ShahkotPK</title>
    <link rel="stylesheet" href="/assets/platform-v4.0.0.css?v=430">
    <style>
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 20px; }
        .stat-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 14px; color: #888; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #00d2ff; }
    </style>
</head>
<body class="v4-public dark-mode">
    <header class="v4-public-head">
        <a href="/">← Back to ShahkotPK</a>
        <b>Shopkeeper Analytics</b>
    </header>
    <main class="v4-public-shell">
        <h1 style="padding: 20px; margin: 0;">Business Performance Analytics</h1>
        <p style="padding: 0 20px;">See how your business is performing this month in Shahkot.</p>
        
        <div class="analytics-grid">
            <div class="stat-card">
                <h3>Profile Views</h3>
                <div class="value"><?= number_format($stats['profile_views']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Search Appearances</h3>
                <div class="value"><?= number_format($stats['search_appearances']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Phone Number Clicks</h3>
                <div class="value"><?= number_format($stats['phone_clicks']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Get Directions</h3>
                <div class="value"><?= number_format($stats['direction_clicks']) ?></div>
            </div>
        </div>
    </main>
</body>
</html>
