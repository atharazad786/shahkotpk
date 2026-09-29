<?php
require __DIR__.'/../app/bootstrap.php';require_admin();require __DIR__.'/../app/updater.php';
$ok=$err=null;
$updaterEnabled=feature_enabled('update_system_enabled',true);
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!$updaterEnabled){$err='System Updater is disabled in Admin Settings.';} else {
 try{
  csrf_check();
  if(empty($_FILES['update_zip']['tmp_name'])) throw new RuntimeException('Choose an update ZIP.');
  if(strtolower(pathinfo($_FILES['update_zip']['name'],PATHINFO_EXTENSION))!=='zip') throw new RuntimeException('Only ZIP allowed.');
  $dest=__DIR__.'/../storage/updates/'.bin2hex(random_bytes(8)).'.zip';
  if(!move_uploaded_file($_FILES['update_zip']['tmp_name'],$dest)) throw new RuntimeException('Could not save update ZIP.');
  $result=install_update($dest);
  $ok='Update '.$result['version'].' installed successfully.';
  if(!empty($result['migrations'])){$ok.=' Database migrations applied automatically: '.implode(', ',$result['migrations']).'.';}
 }catch(Throwable $e){
  $err=$e->getMessage();
 }finally{
  if(isset($dest) && is_string($dest) && is_file($dest)) @unlink($dest);
 }
}
}
require __DIR__.'/../app/layout.php';page_start('System Updates',true);?>
<div class="card"><h1>System Updates</h1><div class="success" style="margin-bottom:12px"><b>Updater Engine 2.0.2 Active</b> — cleanup bug fixed. Automatic SQL migrations remain enabled.</div><p class="muted">Upload updater-compatible ZIP packages. Your database, config and uploads remain preserved.</p>
<?php if($ok):?><div class="success"><?=e($ok)?></div><?php endif;?>
<?php if($err):?><div class="error"><?=e($err)?></div><?php endif;?>
<?php if($updaterEnabled):?><form method="post" enctype="multipart/form-data"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<input class="input" type="file" name="update_zip" accept=".zip" required>
<button class="btn">Upload & Install</button></form><?php else:?><div class="error">System Updater is disabled. Re-enable it from Admin → Settings → System & Security.</div><?php endif;?><p class="admin-note"><b>Database migrations:</b> Required SQL is now executed automatically by System Updater. No manual phpMyAdmin import is required for updater ZIPs.</p><p class="admin-note"><b>UI updates:</b> This system uses versioned CSS assets to avoid browser/CDN cache. Normally no hard refresh is required.</p></div>
<?php require __DIR__.'/../app/end.php';?>
