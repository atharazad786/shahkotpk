<?php
require __DIR__.'/app/bootstrap.php';
$u=require_login();

if(can_access_admin_panel($u)){header('Location: '.staff_landing_url($u),true,302);exit;}
if($u['role']==='shopkeeper'){
    header('Location: /shopkeeper.php', true, 302);
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

        if($newPassword !== '' && strlen($newPassword) < 8){
            throw new RuntimeException('New password must be at least 8 characters.');
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

$totalBusinesses=0;
$totalCategories=0;
$featuredBusinesses=[];
$favorites=[];
$favoriteCount=0;
try{
    $totalBusinesses=(int)db()->query("SELECT COUNT(*) FROM businesses WHERE status=1")->fetchColumn();
    $totalCategories=(int)db()->query("SELECT COUNT(*) FROM categories WHERE status=1")->fetchColumn();
    $featuredBusinesses=db()->query("SELECT b.name,b.slug,c.name AS city_name,cat.name AS category_name FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id WHERE b.status=1 ORDER BY b.is_featured DESC,b.id DESC LIMIT 5")->fetchAll();

    $fq=db()->prepare("SELECT b.id,b.name,b.slug,b.image,c.name AS city_name,cat.name AS category_name FROM favorite_businesses f JOIN businesses b ON b.id=f.business_id LEFT JOIN cities c ON c.id=b.city_id LEFT JOIN categories cat ON cat.id=b.category_id WHERE f.user_id=? AND b.status=1 ORDER BY f.id DESC LIMIT 12");
    $fq->execute([$u['id']]);
    $favorites=$fq->fetchAll();

    $fq=db()->prepare("SELECT COUNT(*) FROM favorite_businesses WHERE user_id=?");
    $fq->execute([$u['id']]);
    $favoriteCount=(int)$fq->fetchColumn();
}catch(Throwable $e){}

$siteName=setting('site_name','ShahkotPK');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Account - <?=e($siteName)?></title>
<link rel="stylesheet" href="/assets/shahkotpk-1.6.0.css?v=160">
<link rel="stylesheet" href="/assets/shahkotpk-userdash-1.7.0.css?v=170">
<?php if(setting('favicon_url','')):?><link rel="icon" href="<?=e(setting('favicon_url',''))?>"><?php endif;?>
</head>
<body class="dashboard-page">
<div class="dash-shell">
    <div class="dash-topbar">
        <a class="dash-back" href="/">← Back to Homepage</a>
        <div class="dash-actions">
            <span class="dash-chip">Customer Dashboard</span>
            <a class="dash-chip" href="/search.php">Explore Directory</a>
            <a class="dash-chip" href="/support.php">Support Center</a>
            <a class="dash-chip" href="/logout.php">Logout</a>
        </div>
    </div>

    <section class="dash-hero">
        <div class="dash-hero-grid">
            <div>
                <span class="dash-badge">● Welcome back</span>
                <h1>Hello, <?=e($u['name'])?></h1>
                <p>Manage your personal profile, browse local businesses and stay connected with useful city information from <?=e($siteName)?>.</p>
            </div>
            <div class="dash-hero-side">
                <div class="dash-mini-stat"><strong><?=e($u['role'])?></strong><span>Account Type</span></div>
                <div class="dash-mini-stat"><strong><?=e($u['status'])?></strong><span>Account Status</span></div>
                <div class="dash-mini-stat"><strong><?=e(date('d M Y',strtotime($u['created_at'])))?></strong><span>Member Since</span></div>
                <div class="dash-mini-stat"><strong><?=e($totalCategories)?></strong><span>Browse Categories</span></div>
            </div>
        </div>
    </section>

    <div class="dash-stats-grid">
        <div class="dash-stat"><div class="dash-stat-label">Directory Businesses</div><div class="dash-stat-number"><?=e($totalBusinesses)?></div><div class="dash-stat-note">Available local listings</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Categories</div><div class="dash-stat-number"><?=e($totalCategories)?></div><div class="dash-stat-note">Explore services faster</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Saved Businesses</div><div class="dash-stat-number"><?=e($favoriteCount)?></div><div class="dash-stat-note">Your favorite local listings</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Profile Control</div><div class="dash-stat-number">Easy</div><div class="dash-stat-note">Update your details anytime</div></div>
    </div>

    <div class="dash-grid">
        <div class="dash-card">
            <div class="dash-section-head">
                <div>
                    <h2>Profile Settings</h2>
                    <p>Keep your personal account information accurate and secure.</p>
                </div>
            </div>

            <?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
            <?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

            <form method="post">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
                <div class="dash-profile-grid">
                    <div class="dash-field">
                        <label>Full Name</label>
                        <input class="dash-input" name="name" value="<?=e($u['name'])?>" required>
                    </div>
                    <div class="dash-field">
                        <label>Email</label>
                        <input class="dash-input" type="email" name="email" value="<?=e($u['email'])?>">
                    </div>
                    <div class="dash-field">
                        <label>Phone</label>
                        <input class="dash-input" name="phone" value="<?=e($u['phone'])?>">
                    </div>
                    <div class="dash-field">
                        <label>New Password</label>
                        <input class="dash-input" type="password" name="new_password" placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <div class="dash-btn-line">
                    <button class="dash-btn primary" type="submit">Save Changes</button>
                    <a class="dash-btn soft" href="/search.php">Search Businesses</a>
                </div>
            </form>

            <div class="dash-help">
                <strong>Quick help</strong>
                <div>Use this dashboard to update your information and explore businesses in your city. If you want to list a business, register a shopkeeper/business account.</div>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-section-head">
                <div>
                    <h3>Quick Actions</h3>
                    <p>Useful shortcuts for your account.</p>
                </div>
            </div>

            <div class="dash-list">
                <div class="dash-item">
                    <div class="dash-item-icon">▦</div>
                    <div><strong>My Super Dashboard</strong><span>Orders, bookings, wallet, auction bids and notifications in one view.</span></div>
                    <a class="dash-btn primary" href="/customer-dashboard.php">Open</a>
                </div>
                <div class="dash-item">
                    <div class="dash-item-icon">⌕</div>
                    <div><strong>Search the Directory</strong><span>Find shops, services and useful city places.</span></div>
                    <a class="dash-btn soft" href="/search.php">Open</a>
                </div>
                <div class="dash-item">
                    <div class="dash-item-icon">★</div>
                    <div><strong>Featured Businesses</strong><span>See recommended and highlighted listings.</span></div>
                    <a class="dash-btn soft" href="/search.php">Browse</a>
                </div>
                <div class="dash-item">
                    <div class="dash-item-icon">↗</div>
                    <div><strong>Business Signup</strong><span>Create a business-oriented account to grow visibility.</span></div>
                    <a class="dash-btn soft" href="/signup.php">Register</a>
                </div>
            </div>
        </div>
    </div>


    <div class="dash-card" style="margin-top:18px">
        <div class="dash-section-head">
            <div>
                <h2>Saved / Favorite Businesses</h2>
                <p>Your personally saved local business listings.</p>
            </div>
            <a class="dash-btn dark" href="/search.php">Find More Businesses</a>
        </div>

        <?php if($favorites):?>
        <div class="dash-list">
            <?php foreach($favorites as $fav):?>
            <div class="dash-item">
                <div class="dash-item-icon"><?=e(mb_strtoupper(mb_substr($fav['name'],0,1)))?></div>
                <div>
                    <strong><?=e($fav['name'])?></strong>
                    <span><?=e($fav['category_name']?:'—')?> · <?=e($fav['city_name']?:'—')?></span>
                </div>
                <div class="dash-btn-line" style="margin:0">
                    <a class="dash-btn soft" href="/business.php?slug=<?=urlencode($fav['slug'])?>">View</a>
                    <form method="post" action="/favorite.php">
                        <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
                        <input type="hidden" name="business_id" value="<?=e($fav['id'])?>">
                        <input type="hidden" name="return" value="/account.php">
                        <button class="dash-btn soft" type="submit">Remove</button>
                    </form>
                </div>
            </div>
            <?php endforeach;?>
        </div>
        <?php else:?><div class="dash-empty">You have not saved any businesses yet. Open a business profile and click “Save Business”.</div><?php endif;?>
    </div>

    <div class="dash-card" style="margin-top:18px">
        <div class="dash-section-head">
            <div>
                <h2>Popular Businesses</h2>
                <p>Some recent and featured listings from the platform.</p>
            </div>
            <a class="dash-btn dark" href="/search.php">View Directory</a>
        </div>

        <?php if($featuredBusinesses):?>
        <div class="dash-list">
            <?php foreach($featuredBusinesses as $biz):?>
            <div class="dash-item">
                <div class="dash-item-icon"><?=e(mb_strtoupper(mb_substr($biz['name'],0,1)))?></div>
                <div>
                    <strong><?=e($biz['name'])?></strong>
                    <span><?=e($biz['category_name'])?> · <?=e($biz['city_name'])?></span>
                </div>
                <a class="dash-btn soft" href="/business.php?slug=<?=urlencode($biz['slug'])?>">View</a>
            </div>
            <?php endforeach;?>
        </div>
        <?php else:?><div class="dash-empty">Businesses will appear here once listings are available.</div><?php endif;?>
    </div>
</div>
</body>
</html>
