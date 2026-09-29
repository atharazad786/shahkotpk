<?php
declare(strict_types=1);
/** ShahkotPK v13.3.0 — notification control-plane helpers. No sender/worker is installed here. */
if (!function_exists('sk1330nc_snapshot')) {
function sk1330nc_db(): PDO { return db(); }
function sk1330nc_tid(): int {
    try { if (function_exists('tenant_id')) return max(0,(int)tenant_id()); if (function_exists('current_tenant')) { $t=current_tenant(); return max(0,(int)($t['id']??0)); } } catch(Throwable $e){}
    return 0;
}
function sk1330nc_h($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function sk1330nc_table(string $t): bool {
    static $c=[]; if(array_key_exists($t,$c)) return $c[$t];
    try{$q=sk1330nc_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$t]);return $c[$t]=(bool)$q->fetchColumn();}catch(Throwable $e){return $c[$t]=false;}
}
function sk1330nc_allowed(): bool {
    try{$me=function_exists('current_user')?current_user():null;}catch(Throwable $e){$me=null;}
    if(!$me)return false;
    if(function_exists('sk1300_super_admin')&&sk1300_super_admin($me))return true;
    if(function_exists('sk1300_tenant_admin')&&sk1300_tenant_admin($me))return true;
    if(function_exists('sk1300_can')&&sk1300_can('admin.notification_control',$me))return true;
    if(function_exists('has_permission')&&(has_permission('settings.manage',$me)||has_permission('admin.settings',$me)))return true;
    return in_array(strtolower((string)($me['role']??'')),['admin','super_admin','administrator'],true);
}
function sk1330nc_seed_channels(): void {
    $tid=sk1330nc_tid(); if($tid<1||!sk1330nc_table('notification_channels_v1330'))return;
    $defs=[['in_app','In-App Notifications'],['email','Email'],['push','Push Notifications']];
    try{$q=sk1330nc_db()->prepare('INSERT IGNORE INTO notification_channels_v1330(tenant_id,channel_key,label,enabled,quiet_hours_enabled) VALUES(?,?,?,?,0)');foreach($defs as $d)$q->execute([$tid,$d[0],$d[1],1]);}catch(Throwable $e){}
}
function sk1330nc_channels(): array {
    sk1330nc_seed_channels();$tid=sk1330nc_tid();if($tid<1||!sk1330nc_table('notification_channels_v1330'))return [];
    try{$q=sk1330nc_db()->prepare('SELECT channel_key,label,enabled,sender_name,sender_identity,quiet_hours_enabled,quiet_hours_start,quiet_hours_end,updated_at FROM notification_channels_v1330 WHERE tenant_id=? ORDER BY FIELD(channel_key,"in_app","email","push"),label LIMIT 30');$q->execute([$tid]);return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}
}
function sk1330nc_rules(): array {
    $tid=sk1330nc_tid();if($tid<1||!sk1330nc_table('notification_rules_v1330'))return [];
    try{$q=sk1330nc_db()->prepare('SELECT event_key,label,module_group,channel_key,enabled,priority,delivery_mode,updated_at FROM notification_rules_v1330 WHERE tenant_id=? ORDER BY module_group,label,channel_key LIMIT 500');$q->execute([$tid]);return $q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return [];}
}
function sk1330nc_set_channel(string $key,array $data): void {
    $tid=sk1330nc_tid();if($tid<1)throw new RuntimeException('Tenant context is unavailable.');
    $valid=['in_app','email','push'];if(!in_array($key,$valid,true))throw new InvalidArgumentException('Unknown notification channel.');
    $enabled=!empty($data['enabled'])?1:0;$sender=trim((string)($data['sender_name']??''));$identity=trim((string)($data['sender_identity']??''));
    if(strlen($sender)>190||strlen($identity)>190)throw new InvalidArgumentException('Sender fields are too long.');
    $qh=!empty($data['quiet_hours_enabled'])?1:0;$start=trim((string)($data['quiet_hours_start']??''));$end=trim((string)($data['quiet_hours_end']??''));
    $timeOk=static fn($v)=>$v===''||preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$v);if(!$timeOk($start)||!$timeOk($end))throw new InvalidArgumentException('Quiet-hour time must be HH:MM.');
    if(!$qh){$start='';$end='';}
    $q=sk1330nc_db()->prepare('UPDATE notification_channels_v1330 SET enabled=?,sender_name=?,sender_identity=?,quiet_hours_enabled=?,quiet_hours_start=?,quiet_hours_end=?,updated_at=NOW() WHERE tenant_id=? AND channel_key=?');
    $q->execute([$enabled,$sender!==''?$sender:null,$identity!==''?$identity:null,$qh,$start!==''?$start:null,$end!==''?$end:null,$tid,$key]);
}
function sk1330nc_set_rule(array $data): void {
    $tid=sk1330nc_tid();if($tid<1)throw new RuntimeException('Tenant context is unavailable.');
    $event=strtolower(trim((string)($data['event_key']??'')));$event=preg_replace('/[^a-z0-9._-]+/','_',$event)??'';
    $label=trim((string)($data['label']??''));$group=trim((string)($data['module_group']??''))?:'Platform';$channel=(string)($data['channel_key']??'in_app');
    if($event===''||strlen($event)>120)throw new InvalidArgumentException('A valid event key is required.');if($label===''||strlen($label)>190)throw new InvalidArgumentException('A valid label is required.');if(strlen($group)>120)throw new InvalidArgumentException('Module group is too long.');
    if(!in_array($channel,['in_app','email','push'],true))throw new InvalidArgumentException('Unknown channel.');
    $priority=(string)($data['priority']??'normal');if(!in_array($priority,['low','normal','high','urgent'],true))$priority='normal';$mode=(string)($data['delivery_mode']??'instant');if(!in_array($mode,['instant','digest'],true))$mode='instant';$enabled=!empty($data['enabled'])?1:0;
    $q=sk1330nc_db()->prepare('INSERT INTO notification_rules_v1330(tenant_id,event_key,label,module_group,channel_key,enabled,priority,delivery_mode,updated_at) VALUES(?,?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE label=VALUES(label),module_group=VALUES(module_group),enabled=VALUES(enabled),priority=VALUES(priority),delivery_mode=VALUES(delivery_mode),updated_at=NOW()');
    $q->execute([$tid,$event,$label,$group,$channel,$enabled,$priority,$mode]);
}
function sk1330nc_delete_rule(string $event,string $channel): void {
    $tid=sk1330nc_tid();if($tid<1)return;if(!in_array($channel,['in_app','email','push'],true))return;$q=sk1330nc_db()->prepare('DELETE FROM notification_rules_v1330 WHERE tenant_id=? AND event_key=? AND channel_key=?');$q->execute([$tid,$event,$channel]);
}
/** Reusable policy helper for future/existing modules that explicitly include this file. */
function sk1330nc_policy(string $eventKey,string $channel='in_app'): array {
    $tid=sk1330nc_tid();$channelEnabled=true;$ruleEnabled=true;$priority='normal';$mode='instant';
    foreach(sk1330nc_channels() as $c)if((string)$c['channel_key']===$channel){$channelEnabled=!empty($c['enabled']);break;}
    foreach(sk1330nc_rules() as $r)if((string)$r['event_key']===$eventKey&&(string)$r['channel_key']===$channel){$ruleEnabled=!empty($r['enabled']);$priority=(string)$r['priority'];$mode=(string)$r['delivery_mode'];break;}
    return ['tenant_id'=>$tid,'event_key'=>$eventKey,'channel'=>$channel,'enabled'=>$channelEnabled&&$ruleEnabled,'channel_enabled'=>$channelEnabled,'rule_enabled'=>$ruleEnabled,'priority'=>$priority,'delivery_mode'=>$mode];
}
function sk1330nc_snapshot(): array {
    $channels=sk1330nc_channels();$rules=sk1330nc_rules();$enabled=0;foreach($channels as $c)if(!empty($c['enabled']))$enabled++;$activeRules=0;foreach($rules as $r)if(!empty($r['enabled']))$activeRules++;
    return ['version'=>'13.3.0','tenant_id'=>sk1330nc_tid(),'channels'=>$channels,'rules'=>$rules,'summary'=>['channels'=>count($channels),'enabled_channels'=>$enabled,'rules'=>count($rules),'active_rules'=>$activeRules]];
}
}
