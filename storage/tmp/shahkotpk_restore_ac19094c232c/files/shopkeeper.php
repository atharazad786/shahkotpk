<?php
require __DIR__.'/app/bootstrap.php';
$u=require_login();

if($u['role']!=='shopkeeper'){
    header('Location: /account.php', true, 302);
    exit;
}

$success=$error=null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();
        $name=trim((string)($_POST['name']??''));
        $email=trim((string)($_POST['email']??''));
        $phone=trim((string)($_POST['phone']??''));
        $newPassword=(string)($_POST['new_password']??'');

        if($name==='') throw new RuntimeException('Name is required.');
        if($email==='' && $phone==='') throw new RuntimeException('Please enter at least email or phone number.');

        $check=db()->prepare("SELECT id FROM users WHERE id<>? AND ((email=? AND ?<>'') OR (phone=? AND ?<>'')) LIMIT 1");
        $check->execute([$u['id'],$email,$email,$phone,$phone]);
        if($check->fetch()) throw new RuntimeException('This email or phone is already used by another account.');

        $minPassword=max(8,min(64,setting_int('password_min_length',8)));
        if($newPassword !== '' && strlen($newPassword) < $minPassword){
            throw new RuntimeException('New password must be at least '.$minPassword.' characters.');
        }

        if($newPassword !== ''){
            $q=db()->prepare("UPDATE users SET name=?, email=?, phone=?, password_hash=? WHERE id=?");
            $q->execute([$name,$email?:null,$phone?:null,password_hash($newPassword,PASSWORD_DEFAULT),$u['id']]);
        }else{
            $q=db()->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
            $q->execute([$name,$email?:null,$phone?:null,$u['id']]);
        }

        $success='Profile updated successfully.';
        $u=current_user();
    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

$summary=[
    'total_businesses'=>0,
    'verified_businesses'=>0,
    'pending_businesses'=>0,
    'featured_businesses'=>0,
    'active_subscriptions'=>0,
    'ads_count'=>0,
    'ad_budget'=>0.0,
];
$businesses=[]; $subscriptions=[]; $ads=[];
$storeSummary=setting_bool('store_enabled',true)?store_seller_stats((int)$u['id']):[];

try{
    $q=db()->prepare("SELECT COUNT(*) FROM businesses WHERE owner_id=?");
    $q->execute([$u['id']]); $summary['total_businesses']=(int)$q->fetchColumn();

    $q=db()->prepare("SELECT COUNT(*) FROM businesses WHERE owner_id=? AND verification_status='verified'");
    $q->execute([$u['id']]); $summary['verified_businesses']=(int)$q->fetchColumn();

    $q=db()->prepare("SELECT COUNT(*) FROM businesses WHERE owner_id=? AND verification_status='pending'");
    $q->execute([$u['id']]); $summary['pending_businesses']=(int)$q->fetchColumn();

    $q=db()->prepare("SELECT COUNT(*) FROM businesses WHERE owner_id=? AND is_featured=1");
    $q->execute([$u['id']]); $summary['featured_businesses']=(int)$q->fetchColumn();

    $q=db()->prepare("SELECT COUNT(*) FROM subscriptions s JOIN businesses b ON b.id=s.business_id WHERE b.owner_id=? AND s.status='active'");
    $q->execute([$u['id']]); $summary['active_subscriptions']=(int)$q->fetchColumn();

    $q=db()->prepare("SELECT COUNT(*), COALESCE(SUM(a.budget),0) FROM advertisements a JOIN businesses b ON b.id=a.business_id WHERE b.owner_id=?");
    $q->execute([$u['id']]); [$summary['ads_count'],$summary['ad_budget']] = array_values($q->fetch(PDO::FETCH_NUM));

    $q=db()->prepare("SELECT b.*,c.name city_name,cat.name category_name FROM businesses b LEFT JOIN cities c ON c.id=b.city_id LEFT JOIN categories cat ON cat.id=b.category_id WHERE b.owner_id=? ORDER BY b.id DESC LIMIT 8");
    $q->execute([$u['id']]); $businesses=$q->fetchAll();

    $q=db()->prepare("SELECT s.*,p.name AS plan_name,b.name AS business_name FROM subscriptions s JOIN businesses b ON b.id=s.business_id LEFT JOIN subscription_plans p ON p.id=s.plan_id WHERE b.owner_id=? ORDER BY s.id DESC LIMIT 6");
    $q->execute([$u['id']]); $subscriptions=$q->fetchAll();

    $q=db()->prepare("SELECT a.*,b.name AS business_name FROM advertisements a JOIN businesses b ON b.id=a.business_id WHERE b.owner_id=? ORDER BY a.id DESC LIMIT 6");
    $q->execute([$u['id']]); $ads=$q->fetchAll();
}catch(Throwable $e){}

$siteName=setting('site_name','ShahkotPK');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shopkeeper Dashboard - <?=e($siteName)?></title>
<link rel="stylesheet" href="/assets/shahkotpk-1.6.0.css?v=160">
<link rel="stylesheet" href="/assets/shahkotpk-userdash-1.7.0.css?v=170">
<?php if(setting('favicon_url','')):?><link rel="icon" href="<?=e(setting('favicon_url',''))?>"><?php endif;?>
</head>
<body class="dashboard-page">
<div class="dash-shell">
    <div class="dash-topbar">
        <a class="dash-back" href="/">← Back to Homepage</a>
        <div class="dash-actions">
            <span class="dash-chip">Shopkeeper Dashboard</span>
            <a class="dash-chip" href="/shopkeeper-business.php">＋ Add Business</a><?php if(setting_bool('store_enabled',true)):?><a class="dash-chip" href="/shopkeeper-products.php">Marketplace Products</a><a class="dash-chip" href="/shopkeeper-orders.php">Orders</a><a class="dash-chip" href="/shopkeeper-auctions.php">Auctions</a><a class="dash-chip" href="/seller-inventory.php">Inventory</a><a class="dash-chip" href="/seller-finance.php">Finance & Payouts</a><?php endif;?><a class="dash-chip" href="/pricing.php">Upgrade Plan</a><a class="dash-chip" href="/businesses.php">View Directory</a>
            <a class="dash-chip" href="/logout.php">Logout</a>
        </div>
    </div>

    <section class="dash-hero">
        <div class="dash-hero-grid">
            <div>
                <span class="dash-badge">● Business owner access</span>
                <h1>Welcome, <?=e($u['name'])?></h1>
                <p>Track your listings, view subscription activity and monitor your advertisement visibility from one professional dashboard.</p>
            </div>
            <div class="dash-hero-side">
                <div class="dash-mini-stat"><strong><?=e($summary['total_businesses'])?></strong><span>Your Businesses</span></div>
                <div class="dash-mini-stat"><strong><?=e($summary['verified_businesses'])?></strong><span>Verified Listings</span></div>
                <div class="dash-mini-stat"><strong><?=e($summary['active_subscriptions'])?></strong><span>Active Subscriptions</span></div>
                <div class="dash-mini-stat"><strong>Rs <?=e(number_format((float)$summary['ad_budget'],0))?></strong><span>Total Ad Budget</span></div>
            </div>
        </div>
    </section>

    <div class="dash-stats-grid">
        <div class="dash-stat"><div class="dash-stat-label">Businesses</div><div class="dash-stat-number"><?=e($summary['total_businesses'])?></div><div class="dash-stat-note">Registered on the platform</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Pending Verification</div><div class="dash-stat-number"><?=e($summary['pending_businesses'])?></div><div class="dash-stat-note">Need admin review</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Featured</div><div class="dash-stat-number"><?=e($summary['featured_businesses'])?></div><div class="dash-stat-note">Promoted business listings</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Advertisements</div><div class="dash-stat-number"><?=e((int)$summary['ads_count'])?></div><div class="dash-stat-note">Campaign records</div></div>
    </div>

    <div class="dash-grid">
        <div class="dash-card">
            <div class="dash-section-head">
                <div>
                    <h2>My Business Listings</h2>
                    <p>Recent businesses connected to your shopkeeper account.</p>
                </div>
                <div class="dash-btn-line" style="margin:0"><a class="dash-btn primary" href="/shopkeeper-business.php">＋ Add Business</a><a class="dash-btn soft" href="/search.php">Public Directory</a></div>
            </div>

            <?php if($businesses):?>
            <div class="dash-list">
                <?php foreach($businesses as $biz):
                    $statusClass = $biz['verification_status']==='verified' ? 'success' : ($biz['verification_status']==='pending' ? 'warning' : 'danger');
                ?>
                <div class="dash-item">
                    <div class="dash-item-icon"><?=e(mb_strtoupper(mb_substr($biz['name'],0,1)))?></div>
                    <div>
                        <strong><?=e($biz['name'])?></strong>
                        <span><?=e($biz['category_name']?:'—')?> · <?=e($biz['city_name']?:'—')?> · <?=e($biz['address']?:'No address added')?></span>
                    </div>
                    <div><span class="dash-badge-pill <?=$statusClass?>"><?=e($biz['verification_status'])?></span><div style="height:8px"></div><a class="dash-btn soft" href="/shopkeeper-business.php?id=<?=e($biz['id'])?>">Edit</a></div>
                </div>
                <?php endforeach;?>
            </div>
            <?php else:?><div class="dash-empty">No businesses are linked to your account yet. Admin can assign/add your business listings.</div><?php endif;?>
        </div>

        <div class="dash-card">
            <div class="dash-section-head">
                <div>
                    <h3>Profile Settings</h3>
                    <p>Update your basic login information.</p>
                </div>
            </div>

            <?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
            <?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

            <form method="post">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
                <div class="dash-profile-grid" style="grid-template-columns:1fr">
                    <div class="dash-field"><label>Full Name</label><input class="dash-input" name="name" value="<?=e($u['name'])?>" required></div>
                    <div class="dash-field"><label>Email</label><input class="dash-input" type="email" name="email" value="<?=e($u['email'])?>"></div>
                    <div class="dash-field"><label>Phone</label><input class="dash-input" name="phone" value="<?=e($u['phone'])?>"></div>
                    <div class="dash-field"><label>New Password</label><input class="dash-input" type="password" name="new_password" placeholder="Leave blank to keep current password"></div>
                </div>
                <div class="dash-btn-line">
                    <button class="dash-btn primary" type="submit">Save Profile</button>
                </div>
            </form>

            <div class="dash-help">
                <strong>Need more growth?</strong>
                <div>Use featured subscriptions and advertisements to improve local visibility. Contact the site admin to activate premium listing support for your business.</div>
            </div>
        </div>
    </div>

    <?php if(setting_bool('store_enabled',true)):?>
    <div class="dash-card" style="margin-top:18px">
        <div class="dash-section-head"><div><h2>Marketplace Seller Center</h2><p>Add new, used or digital products, manage customer orders and run auctions.</p></div><div class="dash-btn-line" style="margin:0"><a class="dash-btn primary" href="/shopkeeper-products.php">Manage Products</a><a class="dash-btn soft" href="/shopkeeper-orders.php">Orders</a><a class="dash-btn soft" href="/shopkeeper-auctions.php">Auctions</a><a class="dash-btn soft" target="_blank" href="/shop.php">Public Shop</a></div></div>
        <div class="dash-stats-grid" style="margin:0">
          <div class="dash-stat"><div class="dash-stat-label">Products</div><div class="dash-stat-number"><?=e($storeSummary['products']??0)?></div><div class="dash-stat-note">Your marketplace listings</div></div>
          <div class="dash-stat"><div class="dash-stat-label">Published</div><div class="dash-stat-number"><?=e($storeSummary['published']??0)?></div><div class="dash-stat-note">Visible to buyers</div></div>
          <div class="dash-stat"><div class="dash-stat-label">Open Orders</div><div class="dash-stat-number"><?=e($storeSummary['open_orders']??0)?></div><div class="dash-stat-note">Need fulfilment</div></div>
          <div class="dash-stat"><div class="dash-stat-label">Gross Orders</div><div class="dash-stat-number">Rs <?=e(number_format((float)($storeSummary['gross']??0),0))?></div><div class="dash-stat-note">Non-cancelled order value</div></div>
        </div>
    </div>
    <?php endif;?>

    <div class="dash-grid" style="margin-top:18px">
        <div class="dash-card">
            <div class="dash-section-head">
                <div>
                    <h2>Subscription Activity</h2>
                    <p>Your latest subscription records.</p>
                </div>
            </div>
            <?php if($subscriptions):?>
            <div class="dash-table-wrap"><table class="dash-table">
                <tr><th>Business</th><th>Plan</th><th>Amount</th><th>Status</th><th>Period</th></tr>
                <?php foreach($subscriptions as $sub):
                    $statusClass = $sub['status']==='active' ? 'success' : ($sub['status']==='pending' ? 'warning' : 'info');
                ?>
                <tr>
                    <td><?=e($sub['business_name'])?></td>
                    <td><?=e($sub['plan_name']?:'—')?></td>
                    <td>Rs <?=e(number_format((float)$sub['amount'],0))?></td>
                    <td><span class="dash-badge-pill <?=$statusClass?>"><?=e($sub['status'])?></span></td>
                    <td><?=e($sub['start_date'])?> → <?=e($sub['end_date'])?></td>
                </tr>
                <?php endforeach;?>
            </table></div>
            <?php else:?><div class="dash-empty">No subscriptions found yet.</div><?php endif;?>
        </div>

        <div class="dash-card">
            <div class="dash-section-head">
                <div>
                    <h2>Advertisement Activity</h2>
                    <p>Your recent campaigns and placements.</p>
                </div>
            </div>
            <?php if($ads):?>
            <div class="dash-table-wrap"><table class="dash-table">
                <tr><th>Campaign</th><th>Business</th><th>Placement</th><th>Status</th><th>Budget</th></tr>
                <?php foreach($ads as $ad):
                    $statusClass = $ad['status']==='active' ? 'success' : ($ad['status']==='scheduled' ? 'info' : 'warning');
                ?>
                <tr>
                    <td><?=e($ad['title'])?></td>
                    <td><?=e($ad['business_name'])?></td>
                    <td><?=e($ad['placement'])?></td>
                    <td><span class="dash-badge-pill <?=$statusClass?>"><?=e($ad['status'])?></span></td>
                    <td>Rs <?=e(number_format((float)$ad['budget'],0))?></td>
                </tr>
                <?php endforeach;?>
            </table></div>
            <?php else:?><div class="dash-empty">No advertisement records found yet.</div><?php endif;?>
        </div>
    </div>
</div>
</body>
</html>
