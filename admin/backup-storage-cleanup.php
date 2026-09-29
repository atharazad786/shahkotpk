<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
$me=current_user();
if(!$me || !(has_permission('settings.manage',$me)||has_permission('updates.manage',$me)||has_permission('tenants.manage',$me))){http_response_code(403);exit('Forbidden');}
$lock=__DIR__.'/../app/admin_tools_lock_v975.php';if(is_file($lock)){require_once $lock;if(function_exists('sk975_admin_tools_gate'))sk975_admin_tools_gate($me);}
require_once __DIR__.'/../app/recovery_storage_cleanup_v1280.php';
$uid=(int)($me['id']??0);$flash='';$error='';$result=null;
$keep=max(5,min(50,(int)($_REQUEST['keep_latest']??10)));$backupDays=max(3,min(365,(int)($_REQUEST['backup_days']??21)));$updateDays=max(3,min(180,(int)($_REQUEST['update_days']??14)));$logDays=max(7,min(365,(int)($_REQUEST['log_days']??30)));
try{
 if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
  csrf_check();$action=(string)($_POST['action']??'');
  if($action==='clean_selected'){
    if((string)($_POST['confirm']??'')!=='CLEAN STORAGE')throw new RuntimeException('Type CLEAN STORAGE to confirm.');
    $ids=is_array($_POST['item_ids']??null)?$_POST['item_ids']:[];
    if(!$ids)throw new RuntimeException('Select at least one cleanup item.');
    $result=sk1280_execute($ids,$uid,'manual',['keep_latest'=>$keep,'backup_days'=>$backupDays,'update_days'=>$updateDays,'log_days'=>$logDays]);
    $flash='Cleanup completed: '.(int)$result['deleted'].' item(s) deleted · '.sk1280_fmt((int)$result['bytes_freed']).' freed.';
  }
 }
}catch(Throwable $e){$error=$e->getMessage();}
$scan=sk1280_scan($keep,$backupDays,$updateDays,$logDays);$runs=sk1280_recent_runs();
page_start('Backup Storage Cleanup',true);
?>
<link rel="stylesheet" href="/assets/recovery-cleanup-v1280.css?v=1280">
<script defer src="/assets/recovery-cleanup-v1280.js?v=1280"></script>
<div class="sk1280">
 <section class="hero"><div><small>RECOVERY CENTER · STORAGE CLEANUP</small><h1>Reduce backup storage safely</h1><p>Preview reclaimable files before deleting anything. Current application files, config, uploads, sessions, secrets and recent safety restore points are protected.</p></div><a class="back" href="/admin/backup-restore.php">← Recovery Center</a></section>
 <?php if($flash):?><div class="alert ok"><?=e($flash)?></div><?php endif;?><?php if($error):?><div class="alert bad"><?=e($error)?></div><?php endif;?>
 <section class="stats">
  <article><b><?=e(sk1280_fmt((int)$scan['backup_bytes']))?></b><span>Detected backup folder</span></article>
  <article><b><?=e(sk1280_fmt((int)$scan['reclaimable']))?></b><span>Currently reclaimable</span></article>
  <article><b><?=count($scan['items'])?></b><span>Cleanup candidates</span></article>
  <article><b><?=count($scan['protected'])?></b><span>Protected recent items</span></article>
 </section>
 <section class="card"><div class="head"><div><small>SCAN RULES</small><h2>What should be considered old?</h2></div><span class="safe">Protected by default</span></div>
  <form method="get" class="rules"><label>Keep latest restore points<input type="number" name="keep_latest" min="5" max="50" value="<?=$keep?>"></label><label>Backup age<input type="number" name="backup_days" min="3" max="365" value="<?=$backupDays?>"><small>days</small></label><label>Updater ZIP age<input type="number" name="update_days" min="3" max="180" value="<?=$updateDays?>"><small>days</small></label><label>Log age<input type="number" name="log_days" min="7" max="365" value="<?=$logDays?>"><small>days</small></label><button>Rescan Storage</button></form>
 </section>
 <section class="card"><div class="head"><div><small>SAFE CLEANUP QUEUE</small><h2>Select what to delete</h2></div><label class="selectall"><input type="checkbox" id="sk1280-all"> Select all shown</label></div>
  <div class="tips"><b>Recommended first:</b> cache/temp, old updater ZIPs and old logs. Delete old restore points only after confirming you still have recent working backups.</div>
  <form method="post" id="sk1280-clean"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="clean_selected"><input type="hidden" name="keep_latest" value="<?=$keep?>"><input type="hidden" name="backup_days" value="<?=$backupDays?>"><input type="hidden" name="update_days" value="<?=$updateDays?>"><input type="hidden" name="log_days" value="<?=$logDays?>">
   <div class="tablewrap"><table><thead><tr><th></th><th>Type</th><th>Item</th><th>Size</th></tr></thead><tbody>
   <?php if(!$scan['items']):?><tr><td colspan="4" class="empty">No safe cleanup candidates found with the current rules.</td></tr><?php endif;?>
   <?php foreach($scan['items'] as $it):?><tr><td><input class="sk1280-item" type="checkbox" name="item_ids[]" value="<?=e($it['id'])?>"></td><td><span class="kind <?=e($it['kind'])?>"><?=e(str_replace('_',' ',$it['kind']))?></span></td><td><b><?=e($it['label'])?></b><small><?=e($it['detail'])?></small></td><td><strong><?=e(sk1280_fmt((int)$it['bytes']))?></strong></td></tr><?php endforeach;?>
   </tbody></table></div>
   <?php if($scan['items']):?><div class="cleanupbar"><div><b id="sk1280-count">0 selected</b><span>Only currently eligible items will be removed.</span></div><input name="confirm" placeholder="Type CLEAN STORAGE" autocomplete="off"><button class="danger">Delete Selected</button></div><?php endif;?>
  </form>
 </section>
 <section class="grid2"><article class="card"><div class="head"><div><small>WHAT IS SAFE TO REMOVE?</small><h2>Storage reduction guide</h2></div></div><ul class="guide"><li><b>Old updater ZIPs:</b> usually safe after the update is confirmed working. This tool always keeps the newest five per detected updater folder.</li><li><b>Cache / temporary files:</b> regenerable files; safest first cleanup target.</li><li><b>Old logs:</b> useful for diagnostics, but rotated logs older than your chosen age can be removed.</li><li><b>Expired restore points:</b> biggest savings, but keep recent known-good restore points.</li><li><b>Uploads:</b> customer/business media is intentionally excluded and never offered for cleanup.</li></ul></article>
 <article class="card"><div class="head"><div><small>PROTECTED</small><h2>Never auto-cleaned here</h2></div></div><ul class="guide"><li>Current application/code files</li><li><code>config/config.php</code> and secure secrets</li><li><code>uploads/</code> business/customer media</li><li>Sessions and authentication data</li><li>Newest restore points and recent safety backups</li><li>Unknown backup formats not proven to be orphaned</li></ul></article></section>
 <section class="card"><div class="head"><div><small>RECENT CLEANUPS</small><h2>Cleanup history</h2></div></div><div class="tablewrap"><table><thead><tr><th>Date</th><th>Selected</th><th>Deleted</th><th>Freed</th></tr></thead><tbody><?php if(!$runs):?><tr><td colspan="4" class="empty">No cleanup run yet.</td></tr><?php endif;?><?php foreach($runs as $r):?><tr><td><?=e((string)$r['created_at'])?></td><td><?=(int)$r['selected_count']?></td><td><?=(int)$r['deleted_count']?></td><td><b><?=e(sk1280_fmt((int)$r['bytes_freed']))?></b></td></tr><?php endforeach;?></tbody></table></div></section>
</div>
<?php page_end(); ?>
