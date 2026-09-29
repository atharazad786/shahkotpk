<?php
define('SHAHKOTPK_PUBLIC_RUNTIME_V1101_STARTED', true);
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
$sideImg = setting('auth_side_image', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop');
$bgImg = setting('auth_bg_image', '');
$logo = setting('logo_url', '');

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Log In - <?=e($siteName)?></title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; }
body { 
    margin: 0; min-height: 100vh; font-family: 'Outfit', sans-serif; 
    background-color: #f1f5f9; 
    <?php if($bgImg): ?>
    background-image: url('<?=e($bgImg)?>'); background-size: cover; background-position: center;
    <?php endif; ?>
    display: flex; align-items: center; justify-content: center; padding: 20px;
}
.auth-container {
    width: 100%; max-width: 1000px; min-height: 600px;
    background: #ffffff; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    display: flex; overflow: hidden;
}
.auth-banner {
    flex: 1; background-image: url('<?=e($sideImg)?>'); background-size: cover; background-position: center;
    position: relative; display: none;
}
@media (min-width: 768px) { .auth-banner { display: block; } }
.auth-banner::after {
    content: ''; position: absolute; inset: 0; 
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.4), rgba(59, 130, 246, 0.2));
}

.auth-form-wrapper {
    flex: 1; padding: 60px 40px; display: flex; flex-direction: column; justify-content: center;
    max-width: 500px; margin: 0 auto;
}

.brand { text-align: center; margin-bottom: 30px; }
.brand img { max-height: 60px; max-width: 200px; }
.brand-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #6366f1, #3b82f6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 24px; margin: 0 auto; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }

h2 { font-size: 28px; font-weight: 700; color: #0f172a; margin: 0 0 8px; text-align: center; }
.subtitle { color: #64748b; font-size: 15px; margin: 0 0 30px; text-align: center; }

.alert { padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
.alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; }
.alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #22c55e; }

.field { margin-bottom: 20px; }
.field label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.input-wrap { position: relative; }
.input { width: 100%; height: 50px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0 16px; font-family: inherit; font-size: 15px; color: #0f172a; transition: all 0.2s; }
.input:focus { outline: none; border-color: #6366f1; background: #fff; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 13px; font-weight: 600; cursor: pointer; }
.password-toggle:hover { color: #6366f1; }

.forgot-link { font-size: 13px; color: #6366f1; text-decoration: none; font-weight: 600; float: right; margin-top: 5px; }
.forgot-link:hover { text-decoration: underline; }

.submit { width: 100%; height: 50px; background: #6366f1; border: none; border-radius: 12px; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; margin-top: 10px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
.submit:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.4); }

.divider { display: flex; align-items: center; text-align: center; margin: 30px 0; color: #94a3b8; font-size: 13px; font-weight: 500; }
.divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e2e8f0; }
.divider::before { margin-right: 15px; }
.divider::after { margin-left: 15px; }

.footer-link { text-align: center; margin-top: 20px; font-size: 14px; color: #64748b; }
.footer-link a { color: #6366f1; text-decoration: none; font-weight: 600; }
.footer-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-banner"></div>
        <div class="auth-form-wrapper">
            <div class="brand">
                <?php if($logo): ?>
                    <img src="<?=e($logo)?>" alt="Logo">
                <?php else: ?>
                    <div class="brand-icon">SK</div>
                <?php endif; ?>
            </div>
            
            <h2>Welcome Back</h2>
            <p class="subtitle">Log in to your <?=e($siteName)?> account</p>

            <?php if($success):?>
                <div class="alert alert-success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    <?=e($success)?>
                </div>
            <?php endif;?>

            <?php if($error):?>
                <div class="alert alert-error">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <?=e($error)?>
                </div>
            <?php endif;?>

            <form method="post" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

                <div class="field">
                    <label>Email Address or Phone</label>
                    <div class="input-wrap">
                        <input class="input" type="text" name="login" value="<?=e($oldLogin)?>" placeholder="Enter email or phone number" required autofocus>
                    </div>
                </div>

                <div class="field" style="margin-bottom: 25px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 6px;">
                        <label style="margin-bottom:0;">Password</label>
                        <a href="forgot.php" class="forgot-link">Forgot?</a>
                    </div>
                    <div class="input-wrap">
                        <input class="input" id="userPassword" type="password" name="password" placeholder="Enter your password" required>
                        <button class="password-toggle" type="button" id="passwordToggle">Show</button>
                    </div>
                </div>

                <button class="submit" type="submit">Log In to Account</button>
            </form>

            <div class="divider">Or</div>

            <div class="footer-link">
                New to <?=e($siteName)?>? <a href="register.php">Create an account</a>
            </div>
            
            <div class="footer-link" style="margin-top:15px; font-size:13px;">
                <a href="/" style="color:#94a3b8; font-weight:500;">← Return to website</a>
            </div>
        </div>
    </div>
<script>
document.getElementById('passwordToggle').addEventListener('click',function(){
    const input=document.getElementById('userPassword');
    const show=input.type==='password';
    input.type=show?'text':'password';
    this.textContent=show?'Hide':'Show';
});
</script>
</body>
</html>
