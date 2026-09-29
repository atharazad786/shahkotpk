<?php
require __DIR__.'/../app/bootstrap.php';

$existing=current_user();
if($existing && ($existing['role']??'')==='admin'){
    header('Location: /admin/index.php');
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
            throw new RuntimeException('Enter your administrator email and password.');
        }

        $q=db()->prepare("SELECT * FROM users WHERE email=? AND role='admin' LIMIT 1");
        $q->execute([$login]);
        $u=$q->fetch();

        if(!$u || $u['status']!=='active' || !password_verify($password,$u['password_hash'])){
            throw new RuntimeException('Invalid administrator credentials.');
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
            throw new RuntimeException('This administrator account is not approved.');
        }

        if(feature_enabled('session_regenerate_on_login',true)){
            session_regenerate_id(true);
        }
        $_SESSION['user_id']=$u['id'];
        header('Location: /admin/index.php');
        exit;
    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

$siteName=setting('site_name','ShahkotPK');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin Sign In - <?=e($siteName)?></title>
<style>
*{box-sizing:border-box}
:root{--ink:#172033;--muted:#748198;--line:#dfe6ef;--blue:#2563eb;--violet:#7c3aed;--green:#10b981}
html,body{margin:0;min-height:100%;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;background:#eef3f9;color:var(--ink)}
body{min-height:100vh;display:grid;place-items:center;padding:28px;overflow-x:hidden}
.login-bg{position:fixed;inset:0;z-index:-3;background:
 radial-gradient(circle at 12% 20%,rgba(37,99,235,.16),transparent 25%),
 radial-gradient(circle at 88% 75%,rgba(124,58,237,.14),transparent 26%),
 linear-gradient(135deg,#edf4ff,#f8fbff 45%,#eef8f4)}
.login-bg:after{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(15,23,42,.028) 1px,transparent 1px),linear-gradient(90deg,rgba(15,23,42,.028) 1px,transparent 1px);background-size:36px 36px;mask-image:linear-gradient(to bottom,black,transparent 82%)}
.auth-shell{width:min(1160px,100%);display:grid;grid-template-columns:1.08fr .92fr;background:rgba(255,255,255,.91);border:1px solid rgba(255,255,255,.75);border-radius:30px;overflow:hidden;box-shadow:0 32px 80px rgba(15,23,42,.16);backdrop-filter:blur(18px)}
.auth-showcase{position:relative;overflow:hidden;background:linear-gradient(145deg,#0b1220,#132645 55%,#214fc2);color:#fff;padding:48px;min-height:650px;display:flex;flex-direction:column}
.auth-showcase:before{content:"";position:absolute;width:330px;height:330px;border-radius:50%;right:-120px;top:-100px;background:rgba(96,165,250,.16);box-shadow:0 0 80px rgba(96,165,250,.14)}
.auth-showcase:after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;left:-100px;bottom:-120px;background:rgba(124,58,237,.18);filter:blur(3px)}
.brand{position:relative;z-index:2;display:flex;gap:13px;align-items:center}.brand-mark{width:50px;height:50px;border-radius:16px;display:grid;place-items:center;background:linear-gradient(135deg,#2563eb,#7c3aed);font-size:20px;font-weight:950;box-shadow:0 14px 30px rgba(37,99,235,.34)}.brand strong{font-size:24px}.brand small{display:block;color:#b9c8e0;margin-top:3px}
.auth-showcase h1{position:relative;z-index:2;font-size:clamp(38px,4.5vw,58px);line-height:1.02;letter-spacing:-.05em;margin:72px 0 18px;max-width:650px}
.auth-showcase>p{position:relative;z-index:2;color:#c8d6ea;font-size:16px;line-height:1.7;max-width:650px;margin:0}
.feature-grid{position:relative;z-index:2;display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:auto;padding-top:42px}
.feature{border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.07);border-radius:17px;padding:15px;backdrop-filter:blur(8px)}
.feature b{display:block;font-size:13px}.feature span{display:block;color:#aebed5;font-size:11px;margin-top:5px;line-height:1.5}
.auth-panel{padding:48px;display:flex;align-items:center;background:rgba(255,255,255,.94)}
.auth-card{width:100%;max-width:430px;margin:auto}
.auth-kicker{display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:#eaf2ff;color:#1d4ed8;font-size:11px;font-weight:900}
.auth-kicker:before{content:"";width:7px;height:7px;border-radius:50%;background:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.11)}
.auth-card h2{font-size:34px;letter-spacing:-.04em;margin:17px 0 7px}.auth-card>p{color:var(--muted);font-size:14px;line-height:1.6;margin:0 0 25px}
.alert{border-radius:14px;padding:12px 14px;margin-bottom:16px;background:#fee2e2;border:1px solid #fecaca;color:#991b1b;font-size:13px}
.field{margin-bottom:16px}.field label{display:block;font-size:12px;font-weight:850;color:#334155;margin-bottom:7px}
.input-wrap{position:relative}.input{width:100%;height:52px;border:1px solid #ced9e6;border-radius:14px;background:#fbfdff;padding:0 15px;color:#172033;font:inherit;outline:none;transition:.2s ease}.input:focus{background:#fff;border-color:#73a0f6;box-shadow:0 0 0 4px rgba(37,99,235,.10)}
.password-toggle{position:absolute;right:9px;top:9px;height:34px;padding:0 10px;border:0;border-radius:9px;background:#eef3f8;color:#526277;cursor:pointer;font-weight:800;font-size:11px}
.submit{width:100%;height:52px;border:0;border-radius:14px;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;font-weight:900;font-size:14px;cursor:pointer;box-shadow:0 15px 30px rgba(37,99,235,.22);transition:.2s ease}
.submit:hover{transform:translateY(-2px);box-shadow:0 19px 35px rgba(37,99,235,.28)}
.auth-footer{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:20px;padding-top:18px;border-top:1px solid #e7edf4;font-size:12px;color:#7b8798}.auth-footer a{color:#1d4ed8;text-decoration:none;font-weight:850}
.security-note{margin-top:20px;padding:13px 14px;border-radius:14px;background:#f7fafc;border:1px solid #e4ebf3;font-size:11px;color:#718096;line-height:1.55}
@media(max-width:900px){body{padding:16px}.auth-shell{grid-template-columns:1fr}.auth-showcase{min-height:auto;padding:28px}.auth-showcase h1{margin-top:38px;font-size:38px}.feature-grid{margin-top:28px;padding-top:0}.auth-panel{padding:30px}}
@media(max-width:540px){.feature-grid{grid-template-columns:1fr}.auth-showcase{display:none}.auth-shell{border-radius:22px}.auth-panel{padding:26px 20px;min-height:calc(100vh - 32px)}.auth-card h2{font-size:30px}}
</style>
</head>
<body>
<div class="login-bg"></div>
<main class="auth-shell">
    <section class="auth-showcase">
        <div class="brand">
            <div class="brand-mark">S</div>
            <div><strong><?=e($siteName)?></strong><small>City Portal Administration</small></div>
        </div>

        <h1>Manage your city platform from one secure control center.</h1>
        <p>Business directory, users, subscriptions, payments, advertisements, homepage content and system operations — all managed from your ShahkotPK administration panel.</p>

        <div class="feature-grid">
            <div class="feature"><b>Live Analytics</b><span>Operational metrics, revenue indicators and activity widgets.</span></div>
            <div class="feature"><b>Business Control</b><span>Verification, packages, advertisements and directory management.</span></div>
            <div class="feature"><b>Role Based Access</b><span>Custom staff permissions with secure administrator access.</span></div>
            <div class="feature"><b>Automatic Updates</b><span>Versioned updater packages with automatic SQL migrations.</span></div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <span class="auth-kicker">Administrator Access</span>
            <h2>Welcome back</h2>
            <p>Sign in with your authorized administrator account to continue.</p>

            <?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>

            <form method="post" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

                <div class="field">
                    <label for="adminEmail">Admin Email</label>
                    <div class="input-wrap">
                        <input class="input" id="adminEmail" type="email" name="login" value="<?=e($login)?>" placeholder="name@example.com" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label for="adminPassword">Password</label>
                    <div class="input-wrap">
                        <input class="input" id="adminPassword" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" id="passwordToggle">Show</button>
                    </div>
                </div>

                <button class="submit" type="submit">Sign In to Admin Panel</button>
            </form>

            <div class="security-note">For security, administrator credentials are never displayed on this page. Use an authorized admin account created in your system.</div>

            <div class="auth-footer">
                <a href="/">← Back to ShahkotPK</a>
                <span>Secure administration portal</span>
            </div>
        </div>
    </section>
</main>

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
