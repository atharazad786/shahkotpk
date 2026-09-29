<?php
define('SHAHKOTPK_PUBLIC_RUNTIME_V1101_STARTED', true);
require __DIR__.'/app/bootstrap.php';

if(!feature_enabled('registration_enabled',true)){
    http_response_code(403);
    exit('Registration is currently disabled by the administrator.');
}

$allowCustomer=feature_enabled('customer_signup_enabled',true);
$allowShopkeeper=feature_enabled('shopkeeper_signup_enabled',true) && feature_enabled('business_registration_enabled',true);

if(!$allowCustomer && !$allowShopkeeper){
    http_response_code(403);
    exit('New account registration is currently unavailable.');
}

$error=null;
$old=[
    'name'=>'',
    'email'=>'',
    'phone'=>'',
    'role'=>$allowCustomer ? 'customer' : 'shopkeeper',
];
if(!$allowCustomer && $allowShopkeeper) $old['role']='shopkeeper';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();

        $old['name']=trim($_POST['name']??'');
        $old['email']=trim($_POST['email']??'');
        $old['phone']=trim($_POST['phone']??'');
        $old['role']=$_POST['role']??$old['role'];
        $pw=$_POST['password']??'';

        if($old['role']==='customer' && !$allowCustomer) throw new RuntimeException('Customer registration is disabled.');
        if($old['role']==='shopkeeper' && !$allowShopkeeper) throw new RuntimeException('Shopkeeper registration is disabled.');
        if(!in_array($old['role'],['customer','shopkeeper'],true)) throw new RuntimeException('Invalid account type.');

        $minPassword=max(8,min(64,setting_int('password_min_length',8)));if(!$old['name'] || strlen($pw)<$minPassword) throw new RuntimeException('Name and password are required. Password must be at least '.$minPassword.' characters.');
        if(feature_enabled('email_required',false) && !$old['email']) throw new RuntimeException('Email is required.');
        if(feature_enabled('phone_required',false) && !$old['phone']) throw new RuntimeException('Phone is required.');
        if(!$old['email'] && !$old['phone']) throw new RuntimeException('Enter at least an email or phone number.');

        $check=db()->prepare("SELECT id FROM users WHERE (email=? AND ? <> '') OR (phone=? AND ? <> '') LIMIT 1");
        $check->execute([$old['email'],$old['email'],$old['phone'],$old['phone']]);
        if($check->fetch()) throw new RuntimeException('An account with this email or phone already exists.');

        $q=db()->prepare("INSERT INTO users(name,email,phone,password_hash,role,status) VALUES(?,?,?,?,?,'active')");
        $q->execute([$old['name'],$old['email']?:null,$old['phone']?:null,password_hash($pw,PASSWORD_DEFAULT),$old['role']]);
        $userId=(int)db()->lastInsertId();
        if(function_exists('activity_log_event'))activity_log_event('account.created','auth','user',$userId,['role'=>$old['role']],$userId,'signup');
        growth_attach_referral($userId);
        $mode=approval_mode_for_role($old['role']);
        $approval=$mode==='auto'?'approved':'pending';
        $source=$mode==='auto'?'auto':($mode==='package_payment'?'package':'manual');
        db()->prepare("INSERT INTO user_profiles(user_id,approval_status,approval_source,approved_at) VALUES(?,?,?,?)")->execute([$userId,$approval,$source,$approval==='approved'?date('Y-m-d H:i:s'):null]);
        if($mode==='package_payment'){$_SESSION['user_id']=$userId;header('Location: /pricing.php?approval=1');exit;}
        header('Location: /login.php?registered=1&approval='.$approval);
        exit;
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
<title>Create an account - <?=e($siteName)?></title>
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
    flex: 1; padding: 40px; display: flex; flex-direction: column; justify-content: center;
    max-width: 500px; margin: 0 auto;
}

.brand { text-align: center; margin-bottom: 20px; }
.brand img { max-height: 60px; max-width: 200px; }
.brand-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #6366f1, #3b82f6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 24px; margin: 0 auto; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }

h2 { font-size: 28px; font-weight: 700; color: #0f172a; margin: 0 0 8px; text-align: center; }
.subtitle { color: #64748b; font-size: 15px; margin: 0 0 20px; text-align: center; }

.alert { background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.field { margin-bottom: 15px; }
.field label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.input-wrap { position: relative; }
.input { width: 100%; height: 50px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0 16px; font-family: inherit; font-size: 15px; color: #0f172a; transition: all 0.2s; }
.input:focus { outline: none; border-color: #6366f1; background: #fff; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 13px; font-weight: 600; cursor: pointer; }
.password-toggle:hover { color: #6366f1; }

.submit { width: 100%; height: 50px; background: #6366f1; border: none; border-radius: 12px; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; margin-top: 10px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
.submit:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.4); }

.divider { display: flex; align-items: center; text-align: center; margin: 20px 0; color: #94a3b8; font-size: 13px; font-weight: 500; }
.divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e2e8f0; }
.divider::before { margin-right: 15px; }
.divider::after { margin-left: 15px; }

.footer-link { text-align: center; margin-top: 15px; font-size: 14px; color: #64748b; }
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
            
            <h2>Create an account</h2>
            <p class="subtitle">Join the <?=e($siteName)?> community</p>

            <?php if($error):?>
                <div class="alert">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <?=e($error)?>
                </div>
            <?php endif;?>

            <form method="post" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

                <div class="field">
                    <label>Full Name</label>
                    <div class="input-wrap">
                        <input class="input" type="text" name="name" value="<?=e($old['name'])?>" placeholder="Enter your full name" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <input class="input" type="email" name="email" value="<?=e($old['email'])?>" placeholder="Enter your email address" <?=feature_enabled('email_required',false)?'required':''?>>
                    </div>
                </div>
                
                <div class="field">
                    <label>Phone Number</label>
                    <div class="input-wrap">
                        <input class="input" type="text" name="phone" value="<?=e($old['phone'])?>" placeholder="03xx xxxxxxx" <?=feature_enabled('phone_required',false)?'required':''?>>
                    </div>
                </div>

                <?php if($allowCustomer && $allowShopkeeper): ?>
                <div class="field">
                    <label>Account Type</label>
                    <div class="input-wrap">
                        <select name="role" class="input" style="appearance:none; cursor:pointer;" required>
                            <option value="customer" <?=$old['role']==='customer'?'selected':''?>>Regular User</option>
                            <option value="shopkeeper" <?=$old['role']==='shopkeeper'?'selected':''?>>Business Owner</option>
                        </select>
                    </div>
                </div>
                <?php else: ?>
                <input type="hidden" name="role" value="<?=e($old['role'])?>">
                <?php endif; ?>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <input class="input" id="userPassword" type="password" name="password" placeholder="Create your password" required>
                        <button class="password-toggle" type="button" id="passwordToggle">Show</button>
                    </div>
                </div>

                <button class="submit" type="submit">Create an account</button>
            </form>

            <div class="divider">Or</div>

            <div class="footer-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
            
            <div class="footer-link" style="margin-top:10px; font-size:13px;">
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
