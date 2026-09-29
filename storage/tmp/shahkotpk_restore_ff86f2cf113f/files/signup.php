<?php
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
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create Account - <?=e($siteName)?></title>
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
            <a href="/login.php">Already have an account?</a>
        </div>
    </div>

    <div class="auth-layout">
        <div class="auth-showcase">
            <span class="auth-badge">● Join the local business network</span>
            <h1>Create your account and start exploring or growing your business.</h1>
            <p><?=e($tagline)?> helps customers discover services and helps shopkeepers present their businesses professionally.</p>

            <div class="auth-points">
                <div class="auth-point">
                    <div>✓</div>
                    <div><strong>For customers</strong><span>Search businesses, compare services and connect with local shops easily.</span></div>
                </div>
                <div class="auth-point">
                    <div>✓</div>
                    <div><strong>For shopkeepers</strong><span>Create your profile, showcase products/services and build online visibility.</span></div>
                </div>
                <div class="auth-point">
                    <div>✓</div>
                    <div><strong>Simple & secure</strong><span>Fast signup flow with professional design and clean form structure.</span></div>
                </div>
            </div>

            <div class="auth-stats">
                <div class="auth-stat"><strong>24/7</strong><span>Anytime access</span></div>
                <div class="auth-stat"><strong>Local</strong><span>City-focused discovery</span></div>
                <div class="auth-stat"><strong>Easy</strong><span>Simple onboarding</span></div>
            </div>
        </div>

        <div class="auth-card">
            <h2>Create Account</h2>
            <p>Open your account to use the ShahkotPK platform as a customer or shopkeeper.</p>

            <?php if($error):?><div class="error auth-alert"><?=e($error)?></div><?php endif;?>

            <form class="auth-form" method="post" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

                <div class="auth-grid-2">
                    <div class="auth-field">
                        <label for="name">Full Name</label>
                        <input class="auth-input" id="name" name="name" value="<?=e($old['name'])?>" placeholder="Enter your full name" required>
                    </div>
                    <div class="auth-field">
                        <label for="email">Email<?=feature_enabled('email_required',false)?' *':''?></label>
                        <input class="auth-input" id="email" type="email" name="email" value="<?=e($old['email'])?>" placeholder="you@example.com" <?=feature_enabled('email_required',false)?'required':''?>>
                    </div>
                </div>

                <div class="auth-grid-2">
                    <div class="auth-field">
                        <label for="phone">Phone<?=feature_enabled('phone_required',false)?' *':''?></label>
                        <input class="auth-input" id="phone" name="phone" value="<?=e($old['phone'])?>" placeholder="03xx-xxxxxxx" <?=feature_enabled('phone_required',false)?'required':''?>>
                    </div>
                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input class="auth-input" id="password" type="password" name="password" placeholder="Use a strong password" minlength="<?=e(max(8,min(64,setting_int('password_min_length',8))))?>" required>
                        <small>Use a strong password for better security.</small>
                    </div>
                </div>

                <?php if($allowCustomer && $allowShopkeeper):?>
                <div class="auth-field">
                    <label>Choose Account Type</label>
                    <div class="auth-role-grid">
                        <label class="auth-role">
                            <input type="radio" name="role" value="customer" <?=$old['role']==='customer'?'checked':''?>>
                            <span class="auth-role-card">
                                <strong>Customer</strong>
                                <span>Browse the directory, search local businesses and connect with services.</span>
                            </span>
                        </label>
                        <label class="auth-role">
                            <input type="radio" name="role" value="shopkeeper" <?=$old['role']==='shopkeeper'?'checked':''?>>
                            <span class="auth-role-card">
                                <strong>Business / Shopkeeper</strong>
                                <span>Register your business, showcase products and increase local visibility.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <?php elseif($allowShopkeeper):?>
                    <input type="hidden" name="role" value="shopkeeper">
                <?php else:?>
                    <input type="hidden" name="role" value="customer">
                <?php endif;?>

                <button class="auth-submit" type="submit">Create Account</button>
            </form>

            <div class="auth-divider">or</div>

            <div class="auth-footer">
                Already have an account? <a href="/login.php">Login here</a>
            </div>

            <div class="auth-help-box">
                <strong>Need help?</strong>
                <div>Use your real contact information so you can receive updates and access your account smoothly.</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
