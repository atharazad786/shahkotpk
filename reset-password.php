<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/app/password_recovery_v634.php';
require_once __DIR__.'/app/global_brand_v704.php';
$token=trim((string)($_GET['token']??$_POST['token']??''));
$row=pr634_get_token($token);$done=false;$error='';
$adminContext=(($_GET['area']??$_POST['area']??'')==='admin') || (!empty($row['admin_context']));
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    try{
        csrf_check();
        $row=pr634_get_token($token);
        if(!$row) throw new RuntimeException('This reset link is invalid or has expired. Request a new one.');
        $p=(string)($_POST['password']??'');$c=(string)($_POST['confirm_password']??'');
        if(strlen($p)<8) throw new RuntimeException('Use at least 8 characters for your new password.');
        if(strlen($p)>200) throw new RuntimeException('Password is too long.');
        if($p!==$c) throw new RuntimeException('The two passwords do not match.');
        db()->beginTransaction();
        try{pr634_update_password((int)$row['user_id'],$p);pr634_mark_used((int)$row['id'],(int)$row['user_id']);db()->commit();}
        catch(Throwable $e){if(db()->inTransaction())db()->rollBack();throw $e;}
        $done=true;$row=null;
    }catch(Throwable $e){$error=$e->getMessage();}
}
$site=pr634_setting(['site_name','app_name'],'ShahkotPK');$login=$adminContext?'/admin/login.php':'/login.php';$forgot=$adminContext?'/admin/forgot-password.php':'/forgot-password.php';
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Password · <?=e($site)?></title><link rel="stylesheet" href="/assets/password-recovery-6.3.4.css?v=634"><?=function_exists('gb704_asset_tag')?gb704_asset_tag():''?></head><body class="pr634-body"><main class="pr634-shell"><section class="pr634-card"><a class="pr634-brand gb704-brand" href="/"><?php if(function_exists('gb704_logo_url')&&gb704_logo_url()!==''):?><?=gb704_picture('password',$site)?><?php else:?><span>S</span><b><?=e($site)?></b><?php endif;?></a><?php if($done):?><div class="pr634-icon ok">✓</div><h1>Password updated</h1><p class="pr634-lead">Your password has been changed successfully. You can sign in with the new password now.</p><a class="pr634-button" href="<?=e($login)?>">Continue to sign in</a><?php elseif(!$row):?><div class="pr634-icon bad">!</div><h1>Reset link expired</h1><p class="pr634-lead">This password reset link is invalid, already used, or expired.</p><a class="pr634-button" href="<?=e($forgot)?>">Request a new reset link</a><?php else:?><div class="pr634-icon">⌁</div><h1>Create a new password</h1><p class="pr634-lead">Choose a strong password for <b><?=e((string)$row['email'])?></b>.</p><?php if($error):?><div class="pr634-alert err"><?=e($error)?></div><?php endif;?><form method="post" class="pr634-form"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="token" value="<?=e($token)?>"><input type="hidden" name="area" value="<?=$adminContext?'admin':''?>"><label>New password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label><label>Confirm new password<input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></label><button type="submit">Update password</button></form><?php endif;?><div class="pr634-security">🔒 Your password is stored as a secure one-way hash.</div></section></main></body></html>
