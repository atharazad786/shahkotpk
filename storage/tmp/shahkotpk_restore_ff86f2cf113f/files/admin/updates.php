<?php
require __DIR__.'/../app/bootstrap.php';require_permission('updates.manage');require __DIR__.'/../app/updater.php';
$ok=$err=null;$updaterEnabled=feature_enabled('update_system_enabled',true);ensure_recovery_tables();register_legacy_update_backups();

function format_backup_size(int $bytes): string {
    if($bytes>=1073741824)return number_format($bytes/1073741824,2).' GB';
    if($bytes>=1048576)return number_format($bytes/1048576,1).' MB';
    if($bytes>=1024)return number_format($bytes/1024,1).' KB';
    return $bytes.' B';
}
function backup_type_label(string $type): string {
    return match($type){'pre_update'=>'Pre-Update','pre_restore'=>'Safety Restore','manual_full'=>'Full Manual','manual_system'=>'System Manual','uploaded'=>'Uploaded','legacy'=>'Legacy App',default=>ucwords(str_replace('_',' ',$type))};
}
function recovery_age(?string $date): string {
    if(!$date)return 'Never';$d=max(0,time()-strtotime($date));
    if($d<3600)return max(1,(int)($d/60)).' min ago';
    if($d<86400)return (int)($d/3600).' hr ago';
    return (int)($d/86400).' days ago';
}

if(isset($_GET['download_backup'])){
    $key=preg_replace('/[^a-f0-9]/','',strtolower((string)$_GET['download_backup']));$record=backup_record_by_key($key);
    if(!$record)throw new RuntimeException('Backup not found.');$path=safe_registered_backup_path($record);
    header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="'.basename($record['file_name']).'"');header('Content-Length: '.filesize($path));header('X-Content-Type-Options: nosniff');readfile($path);exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();$action=(string)($_POST['action']??'install_update');
        if(!$updaterEnabled&&$action==='install_update')throw new RuntimeException('System Updater is disabled in Platform Settings.');
        if($action==='install_update'){
            if(empty($_FILES['update_zip']['tmp_name']))throw new RuntimeException('Choose an updater-compatible ZIP.');
            if(strtolower(pathinfo((string)$_FILES['update_zip']['name'],PATHINFO_EXTENSION))!=='zip')throw new RuntimeException('Only ZIP update packages are accepted.');
            $dir=__DIR__.'/../storage/updates';if(!is_dir($dir))mkdir($dir,0755,true);$dest=$dir.'/update_'.bin2hex(random_bytes(8)).'.zip';
            if(!move_uploaded_file($_FILES['update_zip']['tmp_name'],$dest))throw new RuntimeException('Could not store update ZIP.');
            try{$result=install_update($dest);$ok='Update v'.$result['version'].' installed successfully.';if(!empty($result['migrations']))$ok.=' Automatic SQL: '.implode(', ',$result['migrations']).'.';if(!empty($result['backup']))$ok.=' Restore point created.';}finally{if(is_file($dest))@unlink($dest);}
        }elseif($action==='create_system_backup'){
            $b=create_system_backup('manual_system',trim((string)($_POST['notes']??'Manual system backup')),false,true,true);$ok='System backup created: '.$b['file_name'];
        }elseif($action==='create_full_backup'){
            $b=create_system_backup('manual_full',trim((string)($_POST['notes']??'Manual full backup')),true,true,true);$ok='Full backup created: '.$b['file_name'];
        }elseif($action==='restore_backup'){
            if(empty($_POST['confirm_restore']))throw new RuntimeException('Confirm the restore checkbox first.');
            $record=backup_record_by_key((string)$_POST['backup_key']);if(!$record)throw new RuntimeException('Backup not found.');
            if($record['backup_type']==='legacy')$result=restore_legacy_application_backup($record);
            else $result=restore_system_backup($record,['restore_database'=>isset($_POST['restore_database']),'restore_uploads'=>isset($_POST['restore_uploads']),'restore_config'=>isset($_POST['restore_config']),'create_safety_backup'=>true,'auto_recover_on_failure'=>true]);
            $ok='Restore completed. ShahkotPK is now on v'.$result['version'].'.';
        }elseif($action==='upload_backup'){
            $record=import_backup_upload($_FILES['backup_zip']??[]);$ok='Backup uploaded and verified: '.$record['file_name'];
        }elseif($action==='upload_restore'){
            if(empty($_POST['confirm_upload_restore']))throw new RuntimeException('Confirm direct restore first.');
            $record=import_backup_upload($_FILES['backup_zip']??[]);$result=restore_system_backup($record,['restore_database'=>isset($_POST['restore_database']),'restore_uploads'=>isset($_POST['restore_uploads']),'restore_config'=>isset($_POST['restore_config']),'create_safety_backup'=>true,'auto_recover_on_failure'=>true]);$ok='Uploaded backup restored to v'.$result['version'].'.';
        }elseif($action==='delete_backup'){
            $record=backup_record_by_key((string)$_POST['backup_key']);if(!$record)throw new RuntimeException('Backup not found.');delete_system_backup($record);$ok='Backup deleted.';
        }elseif($action==='save_recovery_settings'){
            save_setting('updater_auto_backup',isset($_POST['updater_auto_backup'])?'1':'0');
            save_setting('updater_auto_rollback',isset($_POST['updater_auto_rollback'])?'1':'0');
            save_setting('updater_backup_database',isset($_POST['updater_backup_database'])?'1':'0');
            save_setting('updater_backup_retention',(string)max(3,min(100,(int)($_POST['updater_backup_retention']??15))));
            $ok='Recovery settings saved.';
        }
    }catch(Throwable $e){$err=$e->getMessage();}
    register_legacy_update_backups();
}
$backupLimit=max(20,min(500,setting_int('recovery_backup_history_limit',200)));
$updateLimit=max(20,min(500,setting_int('recovery_update_history_limit',150)));
$backups=system_backups($backupLimit);
try{$updates=db()->query("SELECT * FROM system_updates ORDER BY id DESC LIMIT ".$updateLimit)->fetchAll();}catch(Throwable $e){$updates=[];}
$currentVersion=installed_app_version();
$dbBackups=count(array_filter($backups,fn($b)=>!empty($b['includes_database'])));
$restorePoints=count(array_filter($backups,fn($b)=>in_array($b['backup_type'],['pre_update','legacy'],true)));
$successUpdates=count(array_filter($updates,fn($u)=>$u['status']==='success'));
$failedUpdates=count(array_filter($updates,fn($u)=>$u['status']==='failed'));
$backupBytes=array_sum(array_map(fn($b)=>(int)$b['file_size'],$backups));
$latestBackup=$backups[0]??null;

$archive=[];$seen=[];
foreach($updates as $u){
    $v=(string)$u['version'];if(isset($seen[$v]))continue;$seen[$v]=1;
    $restore=null;foreach($backups as $b)if((string)$b['source_version']===$v&&in_array($b['backup_type'],['pre_update','legacy','manual_system','manual_full'],true)){$restore=$b;break;}
    $archive[]=['version'=>$v,'date'=>$u['created_at'],'status'=>$u['status'],'file'=>$u['file_name'],'notes'=>$u['notes'],'restore'=>$restore];
}
$archiveJson=json_encode(array_map(function($r){return ['version'=>$r['version'],'date'=>date('d M Y h:i A',strtotime($r['date'])),'status'=>$r['status'],'file'=>$r['file'],'notes'=>$r['notes'],'restore_key'=>$r['restore']['backup_key']??'','restore_type'=>$r['restore']?backup_type_label($r['restore']['backup_type']):'','restore_size'=>$r['restore']?format_backup_size((int)$r['restore']['file_size']):''];},$archive),JSON_UNESCAPED_SLASHES|JSON_HEX_APOS|JSON_HEX_QUOT);

$tab=in_array(($_GET['tab']??''),['overview','backups','restore','activity'],true)?$_GET['tab']:'overview';
require __DIR__.'/../app/layout.php';page_start('Updates & Recovery',true);
?>
<link rel="stylesheet" href="/assets/recovery-center-2.8.0.css?v=280">
<div class="recovery28-hero"><div><span>RECOVERY CENTER 3.0</span><h2>System Updates, Backups & Restore</h2><p>Cleaner version archive, compact history dropdowns, recovery statistics, protected backups and professional update deployment controls.</p></div><div class="recovery28-version"><small>Installed Release</small><b>v<?=e($currentVersion)?></b><span>SQL migrations + rollback protection</span></div></div>
<?php if($ok):?><div class="success"><?=e($ok)?></div><?php endif;?><?php if($err):?><div class="error"><?=e($err)?></div><?php endif;?>
<div class="recovery28-stats">
<article><i>V</i><div><b>v<?=e($currentVersion)?></b><span>Current Version</span><small><?=e($successUpdates)?> successful updates</small></div></article>
<article><i>↶</i><div><b><?=e($restorePoints)?></b><span>Restore Points</span><small><?=e($failedUpdates)?> failed activity records</small></div></article>
<article><i>DB</i><div><b><?=e($dbBackups)?></b><span>Database Backups</span><small><?=e(count($backups))?> backups total</small></div></article>
<article><i>GB</i><div><b><?=e(format_backup_size($backupBytes))?></b><span>Backup Storage</span><small>Registered backup files</small></div></article>
<article><i>⏱</i><div><b><?=e(recovery_age($latestBackup['created_at']??null))?></b><span>Latest Backup</span><small><?=$latestBackup?e(backup_type_label($latestBackup['backup_type'])):'No backup yet'?></small></div></article>
</div>
<div class="recovery28-tabs"><a class="<?=$tab==='overview'?'active':''?>" href="?tab=overview">Overview</a><a class="<?=$tab==='backups'?'active':''?>" href="?tab=backups">Backup Library <span><?=e(count($backups))?></span></a><a class="<?=$tab==='restore'?'active':''?>" href="?tab=restore">Restore Center</a><a class="<?=$tab==='activity'?'active':''?>" href="?tab=activity">Update Archive <span><?=e(count($archive))?></span></a><a href="/admin/settings.php?module=recovery">Recovery Settings ↗</a></div>

<?php if($tab==='overview'):?>
<div class="recovery28-grid">
<section class="card recovery28-install"><div class="r28-head"><div><h3>Install ShahkotPK Update</h3><span>Validate → Backup → Install → SQL → Verify → Auto rollback if required.</span></div><em>ENGINE 3.0</em></div>
<?php if($updaterEnabled):?><form method="post" enctype="multipart/form-data" class="r28-upload"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="install_update"><div><label>Updater ZIP</label><input class="input" type="file" name="update_zip" accept=".zip" required></div><button class="btn">Upload & Install</button></form><?php else:?><div class="error">Updater disabled in Platform Settings.</div><?php endif;?>
<div class="r28-pipeline"><span>ZIP Validation</span><b>›</b><span>Restore Point</span><b>›</b><span>Files</span><b>›</b><span>Database</span><b>›</b><span>Health Check</span></div>
</section>
<section class="card"><div class="r28-head"><div><h3>Quick Backup</h3><span>Create a recovery point before risky work.</span></div></div><div class="r28-backup-actions"><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="create_system_backup"><button><i>⚙</i><span><b>System Backup</b><small>App + DB + config</small></span></button></form><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="create_full_backup"><button class="full"><i>◆</i><span><b>Full Backup</b><small>App + DB + config + uploads</small></span></button></form></div></section>
</div>

<section class="card r28-version-archive">
<div class="r28-head"><div><h3>Previous Versions Archive</h3><span>Old updates are collapsed into a version dropdown instead of a long history table.</span></div></div>
<?php if($archive):?>
<div class="version-selector"><div><label>Select Installed / Historical Version</label><select class="input" id="releaseSelect"><?php foreach($archive as $i=>$r):?><option value="<?=$i?>">v<?=e($r['version'])?> — <?=e(date('d M Y',strtotime($r['date'])))?> — <?=e(ucfirst($r['status']))?></option><?php endforeach;?></select></div><div class="version-current-badge">Current: v<?=e($currentVersion)?></div></div>
<div class="release-detail" id="releaseDetail"><div><span>Version</span><b id="rdVersion"></b></div><div><span>Installed / Activity Date</span><b id="rdDate"></b></div><div><span>Status</span><b id="rdStatus"></b></div><div><span>Package / Action</span><b id="rdFile"></b></div><div class="full"><span>Notes</span><p id="rdNotes"></p></div><div class="full restore-detail" id="rdRestoreWrap"><span>Restore Point</span><div><b id="rdRestore"></b><small id="rdRestoreSize"></small></div></div></div>
<?php else:?><div class="empty-state">No update history is available yet.</div><?php endif;?>
</section>

<div class="recovery28-grid lower">
<section class="card"><div class="r28-head"><div><h3>Recent Recovery Points</h3><span>Latest backups available for restore.</span></div><a href="?tab=backups">Backup Library</a></div><div class="r28-mini-list"><?php foreach(array_slice($backups,0,6) as $b):?><div><span><b><?=e(backup_type_label($b['backup_type']))?> · v<?=e($b['source_version'])?></b><small><?=e(date('d M Y h:i A',strtotime($b['created_at'])))?></small></span><strong><?=e(format_backup_size((int)$b['file_size']))?></strong><a href="?tab=restore&backup=<?=e($b['backup_key'])?>">Restore</a></div><?php endforeach;?></div></section>
<section class="card"><div class="r28-head"><div><h3>Protection Status</h3><span>Automatic update safety configuration.</span></div></div><div class="protection-list"><div><span>Auto backup before update</span><b class="<?=updater_setting_bool('updater_auto_backup',true)?'on':'off'?>"><?=updater_setting_bool('updater_auto_backup',true)?'ON':'OFF'?></b></div><div><span>Database in restore point</span><b class="<?=updater_setting_bool('updater_backup_database',true)?'on':'off'?>"><?=updater_setting_bool('updater_backup_database',true)?'ON':'OFF'?></b></div><div><span>Automatic rollback</span><b class="<?=updater_setting_bool('updater_auto_rollback',true)?'on':'off'?>"><?=updater_setting_bool('updater_auto_rollback',true)?'ON':'OFF'?></b></div><div><span>Automatic retention</span><strong><?=e(updater_setting_int('updater_backup_retention',15))?> backups</strong></div></div></section>
</div>

<?php elseif($tab==='backups'):?>
<section class="card"><div class="r28-head"><div><h3>Backup Library</h3><span>All registered recovery packages. Filter by backup type.</span></div><div><select class="input compact-select" id="backupTypeFilter"><option value="">All Types</option><option value="pre-update">Pre-Update</option><option value="safety-restore">Safety Restore</option><option value="full-manual">Full Manual</option><option value="system-manual">System Manual</option><option value="uploaded">Uploaded</option><option value="legacy-app">Legacy App</option></select></div></div>
<div class="r28-backup-grid" id="backupGrid"><?php foreach($backups as $b):$type=strtolower(str_replace(' ','-',backup_type_label($b['backup_type'])));?><article data-backup-type="<?=e($type)?>"><div class="backup-card-top"><i><?=!empty($b['includes_database'])?'DB':'ZIP'?></i><span class="status28 <?=e($b['status'])?>"><?=e($b['status'])?></span></div><h4><?=e(backup_type_label($b['backup_type']))?></h4><b>v<?=e($b['source_version'])?></b><p><?=e(date('d M Y · h:i A',strtotime($b['created_at'])))?></p><div class="backup-card-meta"><span><?=e(format_backup_size((int)$b['file_size']))?></span><span><?=$b['includes_database']?'Database':''?><?=$b['includes_uploads']?' + Uploads':''?></span></div><div class="backup-card-actions"><a href="?download_backup=<?=e($b['backup_key'])?>">Download</a><a href="?tab=restore&backup=<?=e($b['backup_key'])?>">Restore</a><form method="post" onsubmit="return confirm('Delete this backup permanently?')"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_backup"><input type="hidden" name="backup_key" value="<?=e($b['backup_key'])?>"><button>Delete</button></form></div></article><?php endforeach;?></div></section>

<?php elseif($tab==='restore'):?>
<?php $selectedBackup=null;if(isset($_GET['backup']))$selectedBackup=backup_record_by_key((string)$_GET['backup']);?>
<div class="recovery28-grid">
<section class="card"><div class="r28-head"><div><h3>Restore Existing Backup</h3><span>Select a stored recovery point. An emergency safety backup is created first.</span></div></div><form method="post" class="r28-restore-form"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="restore_backup"><label>Recovery Point</label><select class="input" name="backup_key" id="restoreBackupSelect" required><option value="">Choose backup...</option><?php foreach($backups as $b):?><option value="<?=e($b['backup_key'])?>" <?=$selectedBackup&&$selectedBackup['backup_key']===$b['backup_key']?'selected':''?> data-db="<?=e($b['includes_database'])?>" data-uploads="<?=e($b['includes_uploads'])?>" data-config="<?=e($b['includes_config'])?>">v<?=e($b['source_version'])?> · <?=e(backup_type_label($b['backup_type']))?> · <?=e(date('d M Y H:i',strtotime($b['created_at'])))?></option><?php endforeach;?></select><div class="restore-options"><label><input type="checkbox" name="restore_database" checked> Restore database if included</label><label><input type="checkbox" name="restore_uploads"> Restore uploads if included</label><label><input type="checkbox" name="restore_config"> Restore config <small>(advanced)</small></label></div><label class="restore-confirm"><input type="checkbox" name="confirm_restore" value="1" required> I understand this will replace the selected system state.</label><button class="btn danger" type="submit" onclick="return confirm('Start system restore?')">Create Safety Backup & Restore</button></form></section>
<section class="card"><div class="r28-head"><div><h3>Manual Backup Upload</h3><span>Upload a ShahkotPK Recovery Center backup ZIP.</span></div></div><form method="post" enctype="multipart/form-data" class="r28-restore-form"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input class="input" type="file" name="backup_zip" accept=".zip" required><div class="restore-options"><label><input type="checkbox" name="restore_database" checked> Database</label><label><input type="checkbox" name="restore_uploads"> Uploads</label><label><input type="checkbox" name="restore_config"> Config</label></div><button class="btn ghost" name="action" value="upload_backup">Upload & Verify Only</button><label class="restore-confirm"><input type="checkbox" name="confirm_upload_restore" value="1"> Confirm direct restore</label><button class="btn danger" name="action" value="upload_restore">Upload & Restore</button></form><div class="r28-security-note"><b>Verified packages only.</b> Unsafe ZIP paths and packages without a valid backup.json are rejected.</div></section>
</div>

<?php else:?>
<section class="card"><div class="r28-head"><div><h3>Update & Restore Archive</h3><span>Historical activity is collapsed. Open a record only when details are needed.</span></div></div><div class="activity-accordion"><?php foreach($updates as $u):?><details><summary><span><b>v<?=e($u['version'])?></b><small><?=e(date('d M Y h:i A',strtotime($u['created_at'])))?> · <?=e($u['file_name'])?></small></span><em class="<?=e($u['status'])?>"><?=e(strtoupper($u['status']))?></em><i>⌄</i></summary><div><p><?=e($u['notes']?:'No additional notes.')?></p><?php if($u['backup_path']):?><small>Associated safety backup: <?=e(basename($u['backup_path']))?></small><?php endif;?></div></details><?php endforeach;?></div></section>
<?php endif;?>

<div class="r28-warning"><b>Security note:</b> backups can contain database content and configuration credentials. Keep downloaded recovery packages in secure private storage.</div>
<?php if($archive):?><script>
const releaseArchive=<?=$archiveJson?>;
(function(){const select=document.getElementById('releaseSelect');if(!select)return;function show(){const r=releaseArchive[Number(select.value)]||releaseArchive[0];if(!r)return;document.getElementById('rdVersion').textContent='v'+r.version;document.getElementById('rdDate').textContent=r.date;document.getElementById('rdStatus').textContent=r.status.toUpperCase();document.getElementById('rdFile').textContent=r.file;document.getElementById('rdNotes').textContent=r.notes||'No additional notes.';const w=document.getElementById('rdRestoreWrap');if(r.restore_key){w.style.display='flex';document.getElementById('rdRestore').textContent=r.restore_type;document.getElementById('rdRestoreSize').textContent=r.restore_size}else w.style.display='none'}select.addEventListener('change',show);show()})();
</script><?php endif;?>
<script>document.getElementById('backupTypeFilter')?.addEventListener('change',e=>{const q=e.target.value;document.querySelectorAll('[data-backup-type]').forEach(x=>x.style.display=!q||x.dataset.backupType===q?'block':'none')});</script>
<?php require __DIR__.'/../app/end.php';?>
