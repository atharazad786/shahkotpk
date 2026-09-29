<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/app/password_recovery_v634.php';
require_once __DIR__.'/app/global_brand_v704.php';
$adminContext=(defined('AUTH634_ADMIN_CONTEXT') && AUTH634_ADMIN_CONTEXT) || (($_GET['area']??'')==='admin');
$sent=false;$error='';$email='';
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    try{
        csrf_check();
        $email=trim((string)($_POST['email']??''));
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
        pr634_request_reset($email,$adminContext);
        $sent=true;
    }catch(Throwable $e){$error=$e->getMessage();}
}
$site=pr634_setting(['site_name','app_name'],'ShahkotPK');
$login=$adminContext?'/admin/login.php':'/login.php';
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot Password · <?=e($site)?></title><link rel="stylesheet" href="/assets/password-recovery-6.3.4.css?v=634"><?=function_exists('gb704_asset_tag')?gb704_asset_tag():''?></head><body class="pr634-body"><main class="pr634-shell"><section class="pr634-card"><a class="pr634-brand gb704-brand" href="/"><?php if(function_exists('gb704_logo_url')&&gb704_logo_url()!==''):?><?=gb704_picture('password',$site)?><?php else:?><span>S</span><b><?=e($site)?></b><?php endif;?></a><div class="pr634-icon">↻</div><h1>Forgot your password?</h1><p class="pr634-lead">Enter your account email and we’ll send a secure one-time reset link.</p><?php if($sent):?><div class="pr634-alert ok"><b>Check your email</b><span>If an account matches that address, a reset link has been sent. The link expires in 60 minutes.</span></div><?php else:?><?php if($error):?><div class="pr634-alert err"><?=e($error)?></div><?php endif;?><form method="post" class="pr634-form"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><label>Email address<input type="email" name="email" required autocomplete="email" value="<?=e($email)?>" placeholder="you@example.com"></label><button type="submit">Send reset link</button></form><?php endif;?><a class="pr634-back" href="<?=e($login)?>">← Back to sign in</a><div class="pr634-security">🔒 Reset links are single-use and expire automatically.</div></section></main></body></html>
