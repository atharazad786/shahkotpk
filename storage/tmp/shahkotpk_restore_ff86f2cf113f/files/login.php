<?php
require __DIR__.'/app/bootstrap.php';
if(!feature_enabled('login_enabled',true)){
    http_response_code(403);
    exit('Public login is currently disabled by the administrator.');
}

$error=null;
$success=null;
$oldLogin='';

if(isset($_GET['registered'])){
    $success='Account created successfully. Please login to continue.';
}
if(isset($_GET['reset'])){
    $success='Password updated successfully. Please login with your new password.';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();
        $oldLogin=trim($_POST['login']??'');
        $p=$_POST['password']??'';

        if($oldLogin==='' || $p==='') throw new RuntimeException('Email/phone and password are required.');

        if(function_exists('security_assert_login_allowed')) security_assert_login_allowed($oldLogin);
        $q=db()->prepare("SELECT * FROM users WHERE email=? OR phone=? LIMIT 1");
        $q->execute([$oldLogin,$oldLogin]);
        $u=$q->fetch();

        if($u && $u['status']==='active' && password_verify($p,$u['password_hash'])){
            ensure_user_profile_row((int)$u['id']);
            $profile=get_user_profile((int)$u['id']);
            if(($profile['approval_status']??'approved')==='rejected') throw new RuntimeException('Your account registration was not approved. Please contact support.');
            if(($profile['approval_status']??'approved')==='pending'){
                if(in_array(($profile['approval_source']??''),['package','payment'],true)){
                    if(feature_enabled('session_regenerate_on_login',true))session_regenerate_id(true);$_SESSION['user_id']=$u['id'];header('Location: /pricing.php?approval=1');exit;
                }
                throw new RuntimeException('Your account is waiting for administrator approval.');
            }
            if(can_access_admin_panel($u)){ $redirect=staff_landing_url($u); }
            elseif($u['role']==='shopkeeper'){ $redirect='/shopkeeper.php'; }
            elseif(growth_enabled('seller_staff_enabled',true)&&growth_seller_staff_permissions((int)$u['id'])){ $redirect='/seller.php'; }
            else { $redirect='/account.php'; }
            if(function_exists('security_login_success')) security_login_success($u,$redirect,'web');
            if(feature_enabled('session_regenerate_on_login',true)) session_regenerate_id(true);$_SESSION['user_id']=$u['id'];header('Location: '.$redirect);exit;
        }

        if(function_exists('security_record_login')) security_record_login(false,$oldLogin,$u?(int)$u['id']:null,'web','Invalid credentials');
        $error='Invalid login credentials. Please check your email/phone and password.';
    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

$siteName=setting('site_name','ShahkotPK');
$tagline=setting('site_tagline','Your Digital City Guide');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - <?=e($siteName)?></title>
<link rel="stylesheet" href="/assets/shahkotpk-1.5.0.css?v=150">
<link rel="stylesheet" href="/assets/shahkotpk-auth-1.5.1.css?v=151">
<?php if(setting('favicon_url','')):?><link rel="icon" href="<?=e(setting('favicon_url',''))?>"><?php endif;?>
</head>
<body class="auth-page">
<div class="auth-shell">
    <div class="auth-topbar">
        <a class="auth-back" href="/">← Back to <?=e($siteName)?></a>
        <div class="auth-mini-links">
            <a href="/search.php">Explore Directory</a>
            <a href="/signup.php">Create Account</a>
        </div>
    </div>

    <div class="auth-layout">
        <div class="auth-showcase">
            <span class="auth-badge">● Secure account access</span>
            <h1>Welcome back to <?=e($siteName)?>.</h1>
            <p>Login to manage your account, access your business profile and continue exploring your city's digital directory.</p>

            <div class="auth-points">
                <div class="auth-point">
                    <div>✓</div>
                    <div><strong>Customer access</strong><span>Return to your saved account and continue browsing the local directory.</span></div>
                </div>
                <div class="auth-point">
                    <div>✓</div>
                    <div><strong>Business dashboard</strong><span>Manage listings, update business details and stay visible online.</span></div>
                </div>
                <div class="auth-point">
                    <div>✓</div>
                    <div><strong>Safe login</strong><span>Use your registered email or phone number with your password.</span></div>
                </div>
            </div>

            <div class="auth-stats">
                <div class="auth-stat"><strong>Fast</strong><span>Quick access</span></div>
                <div class="auth-stat"><strong>Secure</strong><span>Protected sessions</span></div>
                <div class="auth-stat"><strong>Simple</strong><span>Easy experience</span></div>
            </div>
        </div>

        <div class="auth-card">
            <h2>Login</h2>
            <p>Use your registered email address or phone number to access your account.</p>

            <?php if($success):?><div class="success auth-alert"><?=e($success)?></div><?php endif;?>
            <?php if($error):?><div class="error auth-alert"><?=e($error)?></div><?php endif;?>

            <form class="auth-form" method="post" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

                <div class="auth-field">
                    <label for="login">Email or Phone</label>
                    <input class="auth-input" id="login" name="login" value="<?=e($oldLogin)?>" placeholder="Enter your email or phone number" required>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input class="auth-input" id="password" type="password" name="password" placeholder="Enter your password" required>
                    <div class="auth-inline-link"><a href="/forgot-password.php">Forgot password?</a></div>
                </div>

                <button class="auth-submit" type="submit">Login to Account</button>
            </form>

            <div class="auth-divider">or</div>

            <div class="auth-footer">
                Don't have an account? <a href="/signup.php">Create one now</a>
            </div>

            <div class="auth-help-box">
                <strong>Login help</strong>
                <div>Use the same email or phone number you registered with. If login fails, verify your password carefully.</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
