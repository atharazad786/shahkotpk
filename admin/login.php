<?php
require __DIR__.'/../app/bootstrap.php';

$existing=current_user();
if($existing && can_access_admin_panel($existing)){
    header('Location: '.staff_landing_url($existing));
    exit;
}

$error=null;
$login='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();
        $login=trim((string)($_POST['login']??''));
        $password=(string)($_POST['password']??'');

        if($login==='' || $password===''){
            throw new RuntimeException('Enter your staff/admin email and password.');
        }

        if(function_exists('security_assert_login_allowed')) security_assert_login_allowed($login);
        $q=db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $q->execute([$login]);
        $u=$q->fetch();

        if(!$u || $u['status']!=='active' || !password_verify($password,$u['password_hash'])){
            if(function_exists('security_record_login')) security_record_login(false,$login,$u?(int)$u['id']:null,'admin','Invalid credentials');
            throw new RuntimeException('Invalid staff/admin credentials.');
        }

        if(function_exists('ensure_user_profile_row')){
            ensure_user_profile_row((int)$u['id']);
        }

        $approved=true;
        if(function_exists('user_is_approved')){
            $approved=user_is_approved((int)$u['id']);
        }elseif(function_exists('get_user_profile')){
            $profile=get_user_profile((int)$u['id']);
            $approved=(($profile['approval_status']??'approved')==='approved');
        }

        if(!$approved){
            throw new RuntimeException('This staff account is not approved.');
        }

        $redirect=staff_landing_url($u);
        if(function_exists('security_login_success')) security_login_success($u,$redirect,'admin');
        if(feature_enabled('session_regenerate_on_login',true)) session_regenerate_id(true);
        $_SESSION['user_id']=$u['id'];
        $fresh=current_user();
        if(!$fresh||!can_access_admin_panel($fresh)){unset($_SESSION['user_id']);throw new RuntimeException('This account does not have admin-panel permissions.');}
        header('Location: '.staff_landing_url($fresh));exit;
    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

$siteName = setting('site_name','ShahkotPK');
$sideImg = setting('auth_side_image', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop');
$bgImg = setting('auth_bg_image', '');
$logo = setting('logo_url', '');

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Sign In - <?=e($siteName)?></title>
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

.alert { background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
.field { margin-bottom: 20px; }
.field label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.input-wrap { position: relative; }
.input { width: 100%; height: 50px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0 16px; font-family: inherit; font-size: 15px; color: #0f172a; transition: all 0.2s; }
.input:focus { outline: none; border-color: #6366f1; background: #fff; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 13px; font-weight: 600; cursor: pointer; }
.password-toggle:hover { color: #6366f1; }

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
            
            <h2>Admin Portal</h2>
            <p class="subtitle">Secure access to the control center</p>

            <?php if($error):?>
                <div class="alert">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <?=e($error)?>
                </div>
            <?php endif;?>

            <form method="post" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

                <div class="field">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <input class="input" type="email" name="login" value="<?=e($login)?>" placeholder="admin@domain.com" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <input class="input" id="adminPassword" type="password" name="password" placeholder="Create your password" required>
                        <button class="password-toggle" type="button" id="passwordToggle">Show</button>
                    </div>
                </div>

                <button class="submit" type="submit">Log in to Account</button>
            </form>

            <div class="footer-link">
                <a href="/">← Back to <?=e($siteName)?></a>
            </div>
        </div>
    </div>
<script>
document.getElementById('passwordToggle').addEventListener('click',function(){
    const input=document.getElementById('adminPassword');
    const show=input.type==='password';
    input.type=show?'text':'password';
    this.textContent=show?'Hide':'Show';
});
</script>
</body>
</html>
