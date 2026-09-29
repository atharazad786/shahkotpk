<?php
declare(strict_types=1);
if(is_file(__DIR__.'/security_performance_v1000.php')) require_once __DIR__.'/security_performance_v1000.php';

/**
 * ShahkotPK v9.7.5 Admin Tools Lock
 *
 * Protects Admin Tools, Database & Maintenance, and Storage Control Center
 * with a second password gate after the normal admin permission check.
 * The configured password is never stored in plaintext.
 */

function sk975_session_start(): void {
    if(session_status()!==PHP_SESSION_ACTIVE){
        @session_start();
    }
}

function sk975_password_hash(): string {
    return '$2y$12$732YJbspiqe8YVvFbV4sHuUTQuZuPN94SlRbQxnJGDtOFCZqiPtam';
}

function sk975_user_id(array $me): int {
    return (int)($me['id']??0);
}

function sk975_is_unlocked(array $me): bool {
    sk975_session_start();
    $uid=sk975_user_id($me);
    $sessionUid=(int)($_SESSION['sk975_admin_tools_uid']??0);
    $last=(int)($_SESSION['sk975_admin_tools_last_activity']??0);
    if($uid<1 || $sessionUid!==$uid || $last<1) return false;
    if((time()-$last)>1800){
        sk975_lock();
        return false;
    }
    $_SESSION['sk975_admin_tools_last_activity']=time();
    return true;
}

function sk975_lock(): void {
    sk975_session_start();
    unset(
        $_SESSION['sk975_admin_tools_uid'],
        $_SESSION['sk975_admin_tools_last_activity'],
        $_SESSION['sk975_admin_tools_failures'],
        $_SESSION['sk975_admin_tools_lock_until']
    );
}

function sk975_current_path(): string {
    $uri=(string)($_SERVER['REQUEST_URI']??'/admin/admin-tools.php');
    $path=(string)(parse_url($uri,PHP_URL_PATH)?:'/admin/admin-tools.php');
    if(!str_starts_with($path,'/admin/')) return '/admin/admin-tools.php';
    return $path;
}

function sk975_redirect(string $path): void {
    if(!str_starts_with($path,'/admin/')) $path='/admin/admin-tools.php';
    if(!headers_sent()){
        header('Location: '.$path, true, 302);
        exit;
    }
    echo '<script>location.href='.json_encode($path).';</script>';
    exit;
}

function sk975_render_lock(array $me,string $error=''): void {
    sk975_session_start();
    $until=(int)($_SESSION['sk975_admin_tools_lock_until']??0);
    $wait=max(0,$until-time());
    page_start('Admin Tools Locked',true);
    ?>
    <style>
    .sk975-lock-wrap{min-height:62vh;display:grid;place-items:center;padding:28px 16px}.sk975-lock-card{width:min(470px,100%);border:1px solid rgba(128,145,170,.22);border-radius:24px;background:var(--panel,#fff);box-shadow:0 24px 70px rgba(5,18,38,.16);overflow:hidden}.sk975-lock-head{padding:26px 26px 22px;background:linear-gradient(135deg,#071425,#173b68);color:#fff}.sk975-lock-head span{font-size:11px;font-weight:900;letter-spacing:.15em;color:#8fc3ff}.sk975-lock-head h2{margin:8px 0 6px;font-size:25px}.sk975-lock-head p{margin:0;color:#d6e6f8;line-height:1.55}.sk975-lock-body{padding:24px 26px}.sk975-lock-body label{display:block;font-weight:800;color:var(--text,#172133)}.sk975-lock-body input[type=password]{box-sizing:border-box;width:100%;margin-top:9px;padding:13px 14px;border:1px solid rgba(110,130,155,.35);border-radius:12px;background:var(--input-bg,#fff);color:inherit;font-size:16px;outline:none}.sk975-lock-body input:focus{border-color:#4f8ff7;box-shadow:0 0 0 3px rgba(79,143,247,.14)}.sk975-lock-body button{width:100%;margin-top:14px;padding:13px 16px;border:0;border-radius:12px;background:#1769e0;color:#fff;font-weight:900;cursor:pointer}.sk975-lock-note{margin:14px 0 0;font-size:12px;color:#6c7c91;line-height:1.5}.sk975-lock-error{margin:0 0 14px;padding:11px 12px;border-radius:11px;background:#fff1f2;color:#a51d33;font-weight:800}.sk975-lock-badge{display:inline-flex;align-items:center;gap:7px;margin-top:14px;padding:7px 10px;border-radius:999px;background:rgba(255,255,255,.09);font-size:12px;font-weight:800}
    </style>
    <div class="sk975-lock-wrap"><section class="sk975-lock-card">
      <div class="sk975-lock-head"><span>SHAHKOTPK v9.7.5</span><h2>Admin Tools Locked</h2><p>Enter the Admin Tools password to continue to database, cache, backup, storage and server/system controls.</p><div class="sk975-lock-badge">🔒 Secondary admin protection</div></div>
      <div class="sk975-lock-body">
        <?php if($error!==''):?><div class="sk975-lock-error"><?=e($error)?></div><?php endif;?>
        <?php if($wait>0):?>
          <div class="sk975-lock-error">Too many incorrect attempts. Try again in <?=number_format($wait)?> seconds.</div>
        <?php else:?>
          <form method="post" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
            <input type="hidden" name="sk975_action" value="unlock">
            <label>Admin Tools Password<input type="password" name="sk975_password" required autofocus autocomplete="current-password"></label>
            <button type="submit">Unlock Admin Tools</button>
          </form>
        <?php endif;?>
        <p class="sk975-lock-note">The password is checked against a one-way password hash. Successful unlock lasts up to 30 minutes of inactivity for the current signed-in admin session.</p>
      </div>
    </section></div>
    <?php
    require __DIR__.'/end.php';
    exit;
}

function sk975_admin_tools_gate(array $me): void {
    sk975_session_start();
    $uid=sk975_user_id($me);
    if($uid<1){http_response_code(403);exit('Forbidden');}

    if(isset($_GET['sk975_lock']) && (string)$_GET['sk975_lock']==='1'){
        sk975_lock();
        sk975_redirect('/admin/admin-tools.php');
    }

    if(sk975_is_unlocked($me)) return;

    $error='';
    $lockUntil=(int)($_SESSION['sk975_admin_tools_lock_until']??0);
    if($lockUntil>time()){
        sk975_render_lock($me,'');
    }

    if(($_SERVER['REQUEST_METHOD']??'')==='POST' && (string)($_POST['sk975_action']??'')==='unlock'){
        if(function_exists('csrf_check')) csrf_check();
        $password=(string)($_POST['sk975_password']??'');
        if(password_verify($password,sk975_password_hash())){
            if(function_exists('sk1000_event'))sk1000_event('admin_tools.unlock','Admin Tools secondary lock unlocked.','info',[],$uid);
            $_SESSION['sk975_admin_tools_uid']=$uid;
            $_SESSION['sk975_admin_tools_last_activity']=time();
            $_SESSION['sk975_admin_tools_failures']=0;
            unset($_SESSION['sk975_admin_tools_lock_until']);
            sk975_redirect(sk975_current_path());
        }
        if(function_exists('sk1000_event'))sk1000_event('admin_tools.unlock_failed','Incorrect Admin Tools secondary password attempt.','warning',[],$uid);
        $fails=(int)($_SESSION['sk975_admin_tools_failures']??0)+1;
        $_SESSION['sk975_admin_tools_failures']=$fails;
        if($fails>=5){
            $_SESSION['sk975_admin_tools_lock_until']=time()+300;
            $_SESSION['sk975_admin_tools_failures']=0;
            if(function_exists('sk1000_event'))sk1000_event('admin_tools.lockout','Admin Tools secondary password temporarily locked after repeated failures.','warning',[],$uid);
            $error='Too many incorrect attempts. Access is temporarily locked.';
        }else{
            $error='Incorrect Admin Tools password. '.(5-$fails).' attempt(s) remaining before temporary lockout.';
        }
    }
    sk975_render_lock($me,$error);
}
