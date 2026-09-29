<?php
declare(strict_types=1);
function platform_v4_cron_tick(): array {
    $r=['health'=>false,'backup'=>false,'pruned'=>false,'ai'=>null];
    try{security_health_snapshot_v4();$r['health']=true;}catch(Throwable $e){runtime_log('v4 health cron failed',$e);}
    try{if(security_backup_due()&&function_exists('operations_backup_database')){operations_backup_database(null);$r['backup']=true;}security_prune_backups();$r['pruned']=true;}catch(Throwable $e){runtime_log('v4 backup cron failed',$e);}
    try{db()->exec("DELETE FROM mobile_api_challenges WHERE expires_at<DATE_SUB(NOW(),INTERVAL 1 DAY)");db()->exec("DELETE FROM security_otp_codes WHERE expires_at<DATE_SUB(NOW(),INTERVAL 7 DAY)");db()->exec("DELETE FROM security_login_events WHERE created_at<DATE_SUB(NOW(),INTERVAL 180 DAY)");}catch(Throwable $e){}
    try{if(function_exists('activity_cleanup')){$r['activity_cleanup']=activity_cleanup();}}catch(Throwable $e){runtime_log('Activity log cleanup failed',$e);}
    try{if(function_exists('ai_automation_tick'))$r['ai']=ai_automation_tick(false);}catch(Throwable $e){runtime_log('AI automation cron failed',$e);$r['ai']=['status'=>'failed','error'=>$e->getMessage()];}
    return $r;
}
