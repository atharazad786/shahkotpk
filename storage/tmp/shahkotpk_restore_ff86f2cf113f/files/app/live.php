<?php
declare(strict_types=1);

/**
 * ShahkotPK v5.4.5 - Live Broadcast Center Pro
 * Replaces the legacy live module with a tenant-aware, public-display-safe implementation.
 */

function live_v545_table_exists(string $table): bool {
    static $cache=[];
    if (isset($cache[$table])) return $cache[$table];
    try {
        $q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$table]);
        return $cache[$table]=(bool)$q->fetchColumn();
    } catch (Throwable $e) { return $cache[$table]=false; }
}
function live_v545_columns(string $table): array {
    static $cache=[];
    if(isset($cache[$table])) return $cache[$table];
    if(!preg_match('/^[A-Za-z0-9_]+$/',$table)||!live_v545_table_exists($table)) return $cache[$table]=[];
    try{$rows=db()->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();$out=[];foreach($rows as $r)$out[(string)$r['Field']]=true;return $cache[$table]=$out;}catch(Throwable $e){return $cache[$table]=[];}
}
function live_v545_first_existing(array $cols,array $candidates): ?string { foreach($candidates as $c) if(isset($cols[$c])) return $c; return null; }
function live_v545_value(array $row,?string $column,$default=null){ return $column!==null&&array_key_exists($column,$row)?$row[$column]:$default; }
function live_v545_slugify(string $s): string { $s=strtolower(trim($s));$s=preg_replace('/[^a-z0-9]+/','-',$s)??'';$s=trim($s,'-');return $s!==''?$s:'broadcast'; }
function live_v545_unique_slug(string $title,int $ignoreId=0): string {
    $base=live_v545_slugify($title);$slug=$base;$n=2;
    while(true){try{$q=db()->prepare('SELECT id FROM live_broadcasts_v545 WHERE slug=?'.($ignoreId>0?' AND id<>?':'').' LIMIT 1');$p=[$slug];if($ignoreId>0)$p[]=$ignoreId;$q->execute($p);if(!$q->fetchColumn())return $slug;}catch(Throwable $e){return $slug;}$slug=$base.'-'.$n++;if($n>999)return $base.'-'.bin2hex(random_bytes(3));}
}
function live_v545_safe_url(string $url): string { $url=trim($url);if($url==='')return '';if(preg_match('/[\x00-\x1F\x7F\"\'<>]/',$url))return '';if(str_starts_with($url,'//'))return '';if(str_starts_with($url,'/'))return $url;$p=@parse_url($url);$scheme=strtolower((string)($p['scheme']??''));if($scheme==='')return '/'.ltrim($url,'/');if(!$p||!in_array($scheme,['http','https'],true))return '';return $url; }
function live_v545_detect_source_type(string $url,string $hint=''): string {
    $hint=strtolower(trim($hint));$allowed=['youtube','hls','video','mjpeg','embed','rtmp','rtsp'];if(in_array($hint,$allowed,true))return $hint;
    $u=strtolower($url);if(str_contains($u,'youtube.com')||str_contains($u,'youtu.be'))return 'youtube';if(str_contains($u,'.m3u8'))return 'hls';if(preg_match('/\.(mp4|webm|ogg)(\?|$)/',$u))return 'video';if(str_contains($u,'rtmp://')||str_contains($u,'rtmps://'))return 'rtmp';if(str_contains($u,'rtsp://')||str_contains($u,'rtsps://'))return 'rtsp';if(preg_match('/\.(mjpg|mjpeg)(\?|$)/',$u))return 'mjpeg';return 'embed';
}
function live_v545_youtube_id(string $url): string {
    $url=trim($url);if(preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~',$url,$m))return $m[1];if(preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~',$url,$m))return $m[1];if(preg_match('~/embed/([A-Za-z0-9_-]{6,})~',$url,$m))return $m[1];if(preg_match('~/live/([A-Za-z0-9_-]{6,})~',$url,$m))return $m[1];return '';
}
function live_v545_tenant_scope(string $alias='b',bool $admin=false): array {
    $tid=function_exists('tenant_id')?tenant_id():0;$cid=function_exists('tenant_city_id')?tenant_city_id():0;
    if($admin&&function_exists('is_super_admin')&&is_super_admin()&&function_exists('tenant_is_master')&&tenant_is_master()) return ['1=1',[]];
    if($tid>0&&$cid>0)return ["({$alias}.tenant_id=? OR ({$alias}.tenant_id IS NULL AND ({$alias}.city_id=? OR {$alias}.city_id IS NULL)))",[$tid,$cid]];
    if($cid>0)return ["({$alias}.city_id=? OR {$alias}.city_id IS NULL)",[$cid]];
    return ['1=1',[]];
}
function live_v545_source_url(array $b): string {
    $type=(string)($b['source_type']??'embed');$source=(string)($b['source_url']??'');$fallback=(string)($b['fallback_url']??'');
    if(in_array($type,['rtmp','rtsp'],true)&&$fallback!=='')return $fallback;return $source;
}
function live_v545_status_label(string $status): string {return match($status){'live'=>'LIVE','scheduled'=>'UPCOMING','ended'=>'REPLAY','offline'=>'OFFLINE',default=>strtoupper($status)};}
function live_v545_is_public(array $b): bool {return in_array((string)($b['status']??''),['live','scheduled','ended'],true);}

function live_v545_import_legacy(): array {
    if(!live_v545_table_exists('live_broadcasts_v545'))return ['imported'=>0,'checked'=>0];
    $candidates=['live_broadcasts','live_streams','live_channels','broadcasts','streams','live_tv','live_items'];$imported=0;$checked=0;
    foreach($candidates as $table){
        if($table==='live_broadcasts_v545'||!live_v545_table_exists($table))continue;$cols=live_v545_columns($table);if(!$cols)continue;
        $idCol=live_v545_first_existing($cols,['id','stream_id','broadcast_id']);$titleCol=live_v545_first_existing($cols,['title','name','channel_name','stream_name']);$urlCol=live_v545_first_existing($cols,['stream_url','source_url','url','video_url','media_url','live_url','channel_url','stream_link','link','embed_url','youtube_url','m3u_url','m3u8_url','hls_url','rtmp_url','rtsp_url']);
        if(!$idCol||!$urlCol)continue;$typeCol=live_v545_first_existing($cols,['stream_type','source_type','type','platform']);$posterCol=live_v545_first_existing($cols,['poster_url','thumbnail_url','thumbnail','poster','cover_image','banner','image_url','image']);$descCol=live_v545_first_existing($cols,['description','details','content']);$statusCol=live_v545_first_existing($cols,['status','is_live','is_active','active','enabled','published']);$featuredCol=live_v545_first_existing($cols,['is_featured','featured']);$homeCol=live_v545_first_existing($cols,['show_on_home','homepage','is_home']);$cityCol=live_v545_first_existing($cols,['city_id']);$tenantCol=live_v545_first_existing($cols,['tenant_id']);$startCol=live_v545_first_existing($cols,['start_at','starts_at','scheduled_at','scheduled_for']);
        try{$rows=db()->query("SELECT * FROM `{$table}` ORDER BY `{$idCol}` DESC LIMIT 1000")->fetchAll();}catch(Throwable $e){continue;}
        foreach($rows as $row){$checked++;$legacyKey=$table.':'.(string)$row[$idCol];$url=trim((string)live_v545_value($row,$urlCol,''));if($url==='')continue;$title=trim((string)live_v545_value($row,$titleCol,''));if($title==='')$title='Imported Live Stream #'.(string)$row[$idCol];$rawStatus=live_v545_value($row,$statusCol,'live');$status='live';if(is_numeric($rawStatus))$status=((int)$rawStatus===1?'live':'offline');else{$s=strtolower(trim((string)$rawStatus));if(in_array($s,['draft','scheduled','live','ended','offline'],true))$status=$s;elseif(in_array($s,['active','published','enabled','on'],true))$status='live';elseif(in_array($s,['inactive','disabled','off'],true))$status='offline';}
            $type=live_v545_detect_source_type($url,(string)live_v545_value($row,$typeCol,''));$fallback='';if(in_array($type,['rtmp','rtsp'],true)){$fbCol=live_v545_first_existing($cols,['hls_url','playback_url','fallback_url']);if($fbCol)$fallback=(string)live_v545_value($row,$fbCol,'');}
            try{$q=db()->prepare("INSERT IGNORE INTO live_broadcasts_v545(tenant_id,city_id,title,slug,description,source_type,source_url,fallback_url,poster_url,status,is_featured,show_on_home,chat_enabled,analytics_enabled,start_at,legacy_source_key,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())");$q->execute([($tenantCol?((int)$row[$tenantCol]?:null):null),($cityCol?((int)$row[$cityCol]?:null):null),$title,live_v545_unique_slug($title),substr((string)live_v545_value($row,$descCol,''),0,5000),$type,$url,$fallback,live_v545_safe_url((string)live_v545_value($row,$posterCol,'')),$status,!empty($featuredCol)&&!empty($row[$featuredCol])?1:0,$homeCol===null?1:(!empty($row[$homeCol])?1:0),1,1,$startCol?(string)($row[$startCol]?:null):null,$legacyKey]);if($q->rowCount()>0)$imported++;}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Live legacy import row failed',$e);}
        }
    }
    return ['imported'=>$imported,'checked'=>$checked];
}
function live_v545_maybe_upgrade(): void {
    if(!live_v545_table_exists('live_broadcasts_v545'))return;
    try{$version=(int)setting('live_homepage_version','0');if($version<545){live_v545_import_legacy();save_settings(['live_homepage_version'=>'545','live_portal_enabled'=>'1','live_home_enabled'=>'1']);}}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Live v5.4.5 upgrade failed',$e);}
}
function live_upgrade_homepage(): void { live_v545_maybe_upgrade(); }

function live_v545_public_broadcasts(int $limit=20,bool $homeOnly=false): array {
    if(!live_v545_table_exists('live_broadcasts_v545'))return [];$limit=max(1,min(100,$limit));[$scope,$params]=live_v545_tenant_scope('b',false);$where=[$scope,"b.status IN ('live','scheduled','ended')"];
    if($homeOnly)$where[]='b.show_on_home=1';
    try{$sql='SELECT b.* FROM live_broadcasts_v545 b WHERE '.implode(' AND ',$where)." ORDER BY FIELD(b.status,'live','scheduled','ended'),b.is_featured DESC,CASE WHEN b.start_at IS NULL THEN 1 ELSE 0 END,b.start_at ASC,b.sort_order ASC,b.id DESC LIMIT {$limit}";$q=db()->prepare($sql);$q->execute($params);return $q->fetchAll()?:[];}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Live public list failed',$e);return [];}
}
function live_v545_find($idOrSlug): ?array {
    if(!live_v545_table_exists('live_broadcasts_v545'))return null;[$scope,$params]=live_v545_tenant_scope('b',false);$isId=is_numeric($idOrSlug);$where=$isId?'b.id=?':'b.slug=?';array_unshift($params,$isId?(int)$idOrSlug:(string)$idOrSlug);
    try{$q=db()->prepare("SELECT b.* FROM live_broadcasts_v545 b WHERE {$where} AND {$scope} LIMIT 1");$q->execute($params);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function live_v545_admin_find(int $id): ?array {
    if($id<1||!live_v545_table_exists('live_broadcasts_v545'))return null;[$scope,$params]=live_v545_tenant_scope('b',true);array_unshift($params,$id);try{$q=db()->prepare("SELECT b.* FROM live_broadcasts_v545 b WHERE b.id=? AND {$scope} LIMIT 1");$q->execute($params);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function live_v545_admin_broadcasts(int $limit=200): array {
    if(!live_v545_table_exists('live_broadcasts_v545'))return [];$limit=max(1,min(500,$limit));[$scope,$params]=live_v545_tenant_scope('b',true);try{$q=db()->prepare("SELECT b.*,(SELECT COUNT(*) FROM live_chat_messages_v545 c WHERE c.broadcast_id=b.id AND c.status<>'hidden') chat_count FROM live_broadcasts_v545 b WHERE {$scope} ORDER BY FIELD(b.status,'live','scheduled','draft','ended','offline'),b.id DESC LIMIT {$limit}");$q->execute($params);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function live_v545_session_key(): string {if(empty($_SESSION['live_v545_session']))$_SESSION['live_v545_session']=bin2hex(random_bytes(20));return hash('sha256',(string)$_SESSION['live_v545_session']);}
function live_v545_ip_hash(): string {$ip=(string)($_SERVER['REMOTE_ADDR']??'');return $ip!==''?hash('sha256',$ip.'|'.(string)($GLOBALS['config']['session_name']??'shahkotpk')):'';}
function live_v545_device(): string {$ua=strtolower((string)($_SERVER['HTTP_USER_AGENT']??''));if(str_contains($ua,'mobile')||str_contains($ua,'android')||str_contains($ua,'iphone'))return 'mobile';if(str_contains($ua,'tablet')||str_contains($ua,'ipad'))return 'tablet';return 'desktop';}
function live_v545_track_view(int $broadcastId): void {
    if($broadcastId<1||!live_v545_table_exists('live_view_sessions_v545'))return;$key=live_v545_session_key();$u=function_exists('current_user')?current_user():null;$tid=function_exists('tenant_id')?tenant_id():0;$ref=substr((string)($_SERVER['HTTP_REFERER']??''),0,500);$inserted=false;
    try{$q=db()->prepare("INSERT IGNORE INTO live_view_sessions_v545(broadcast_id,tenant_id,session_key,user_id,first_seen,last_seen,watch_seconds,device,referrer,ip_hash,created_at,updated_at) VALUES(?,?,?,?,NOW(),NOW(),0,?,?,?,NOW(),NOW())");$q->execute([$broadcastId,$tid?:null,$key,$u?(int)$u['id']:null,live_v545_device(),$ref?:null,live_v545_ip_hash()?:null]);$inserted=$q->rowCount()>0;if(!$inserted)db()->prepare('UPDATE live_view_sessions_v545 SET last_seen=NOW(),updated_at=NOW() WHERE broadcast_id=? AND session_key=?')->execute([$broadcastId,$key]);if($inserted)db()->prepare('UPDATE live_broadcasts_v545 SET total_views=total_views+1 WHERE id=?')->execute([$broadcastId]);live_v545_refresh_peak($broadcastId);}catch(Throwable $e){}
}
function live_v545_refresh_peak(int $broadcastId): int {try{$q=db()->prepare('SELECT COUNT(*) FROM live_view_sessions_v545 WHERE broadcast_id=? AND last_seen>=DATE_SUB(NOW(),INTERVAL 45 SECOND)');$q->execute([$broadcastId]);$online=(int)$q->fetchColumn();db()->prepare('UPDATE live_broadcasts_v545 SET viewer_count_cached=?,peak_viewers=GREATEST(peak_viewers,?) WHERE id=?')->execute([$online,$online,$broadcastId]);return $online;}catch(Throwable $e){return 0;}}
function live_v545_heartbeat(int $broadcastId,int $seconds=10): array {$seconds=max(0,min(30,$seconds));live_v545_track_view($broadcastId);try{db()->prepare('UPDATE live_view_sessions_v545 SET last_seen=NOW(),watch_seconds=watch_seconds+?,updated_at=NOW() WHERE broadcast_id=? AND session_key=?')->execute([$seconds,$broadcastId,live_v545_session_key()]);}catch(Throwable $e){}return ['online'=>live_v545_refresh_peak($broadcastId)];}
function live_v545_chat_messages(int $broadcastId,int $limit=60,bool $admin=false): array {if(!live_v545_table_exists('live_chat_messages_v545'))return [];$limit=max(1,min(200,$limit));$status=$admin?"status<>'deleted'":"status='approved'";try{$q=db()->prepare("SELECT id,broadcast_id,user_id,display_name,message,status,created_at FROM live_chat_messages_v545 WHERE broadcast_id=? AND {$status} ORDER BY id DESC LIMIT {$limit}");$q->execute([$broadcastId]);return array_reverse($q->fetchAll()?:[]);}catch(Throwable $e){return [];}}
function live_v545_send_chat(int $broadcastId,string $name,string $message): array {
    $b=live_v545_find($broadcastId);if(!$b||!live_v545_is_public($b))return ['ok'=>false,'message'=>'Broadcast not available.'];if(empty($b['chat_enabled'])||!setting_bool('live_chat_enabled',true))return ['ok'=>false,'message'=>'Live chat is disabled.'];
    $u=function_exists('current_user')?current_user():null;if(!$u&&!setting_bool('live_chat_guest_enabled',true))return ['ok'=>false,'message'=>'Please sign in to chat.'];$name=$u?(string)($u['name']??'Member'):trim(strip_tags($name));$message=trim(strip_tags($message));if($name==='')$name='Guest';$name=mb_substr($name,0,60);$message=mb_substr($message,0,500);if($message==='')return ['ok'=>false,'message'=>'Write a message first.'];
    $key=live_v545_session_key();try{$q=db()->prepare('SELECT created_at FROM live_chat_messages_v545 WHERE broadcast_id=? AND session_key=? ORDER BY id DESC LIMIT 1');$q->execute([$broadcastId,$key]);$last=$q->fetchColumn();if($last&&strtotime((string)$last)>time()-3)return ['ok'=>false,'message'=>'Please wait a moment before sending again.'];}catch(Throwable $e){}
    $status=setting_bool('live_chat_moderation',false)?'pending':'approved';try{db()->prepare('INSERT INTO live_chat_messages_v545(broadcast_id,tenant_id,user_id,session_key,display_name,message,status,ip_hash,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())')->execute([$broadcastId,function_exists('tenant_id')?(tenant_id()?:null):null,$u?(int)$u['id']:null,$key,$name,$message,$status,live_v545_ip_hash()?:null]);return ['ok'=>true,'status'=>$status,'message'=>$status==='pending'?'Message sent for moderation.':'Sent'];}catch(Throwable $e){return ['ok'=>false,'message'=>'Unable to send message right now.'];}
}
function live_v545_stats(int $days=30): array {
    $days=max(1,min(365,$days));$out=['broadcasts'=>0,'live'=>0,'views'=>0,'unique_viewers'=>0,'watch_seconds'=>0,'chat'=>0,'peak'=>0,'trend'=>[]];if(!live_v545_table_exists('live_broadcasts_v545'))return $out;[$scope,$params]=live_v545_tenant_scope('b',true);
    try{$q=db()->prepare("SELECT COUNT(*) total,SUM(status='live') live_count,COALESCE(SUM(total_views),0) views,COALESCE(MAX(peak_viewers),0) peak FROM live_broadcasts_v545 b WHERE {$scope}");$q->execute($params);$r=$q->fetch()?:[];$out['broadcasts']=(int)($r['total']??0);$out['live']=(int)($r['live_count']??0);$out['views']=(int)($r['views']??0);$out['peak']=(int)($r['peak']??0);}catch(Throwable $e){}
    try{$q=db()->prepare("SELECT COUNT(*) sessions,COALESCE(SUM(v.watch_seconds),0) watch_seconds FROM live_view_sessions_v545 v JOIN live_broadcasts_v545 b ON b.id=v.broadcast_id WHERE {$scope} AND v.created_at>=DATE_SUB(NOW(),INTERVAL {$days} DAY)");$q->execute($params);$r=$q->fetch()?:[];$out['unique_viewers']=(int)($r['sessions']??0);$out['watch_seconds']=(int)($r['watch_seconds']??0);}catch(Throwable $e){}
    try{$q=db()->prepare("SELECT COUNT(*) FROM live_chat_messages_v545 c JOIN live_broadcasts_v545 b ON b.id=c.broadcast_id WHERE {$scope} AND c.created_at>=DATE_SUB(NOW(),INTERVAL {$days} DAY) AND c.status<>'deleted'");$q->execute($params);$out['chat']=(int)$q->fetchColumn();}catch(Throwable $e){}
    try{$q=db()->prepare("SELECT DATE(v.created_at) d,COUNT(*) views,COALESCE(SUM(v.watch_seconds),0) watch_seconds FROM live_view_sessions_v545 v JOIN live_broadcasts_v545 b ON b.id=v.broadcast_id WHERE {$scope} AND v.created_at>=DATE_SUB(CURDATE(),INTERVAL 13 DAY) GROUP BY DATE(v.created_at) ORDER BY d");$q->execute($params);$out['trend']=$q->fetchAll()?:[];}catch(Throwable $e){}
    return $out;
}
function live_v545_broadcast_analytics(int $id): array {$o=['online'=>0,'unique'=>0,'watch'=>0,'chat'=>0,'devices'=>[]];$o['online']=live_v545_refresh_peak($id);try{$q=db()->prepare('SELECT COUNT(*),COALESCE(SUM(watch_seconds),0) FROM live_view_sessions_v545 WHERE broadcast_id=?');$q->execute([$id]);$r=$q->fetch(PDO::FETCH_NUM);$o['unique']=(int)($r[0]??0);$o['watch']=(int)($r[1]??0);$q=db()->prepare("SELECT device,COUNT(*) c FROM live_view_sessions_v545 WHERE broadcast_id=? GROUP BY device");$q->execute([$id]);$o['devices']=$q->fetchAll()?:[];$q=db()->prepare("SELECT COUNT(*) FROM live_chat_messages_v545 WHERE broadcast_id=? AND status<>'deleted'");$q->execute([$id]);$o['chat']=(int)$q->fetchColumn();}catch(Throwable $e){}return $o;}

function live_v545_player_markup(array $b,bool $adminPreview=false): string {
    $id=(int)($b['id']??0);$title=e((string)($b['title']??'Live Broadcast'));$type=(string)($b['source_type']??'embed');$source=live_v545_source_url($b);$poster=live_v545_safe_url((string)($b['poster_url']??''));$autoplay=!$adminPreview&&setting_bool('live_autoplay_muted',true);$mute=$autoplay?' muted':'';$auto=$autoplay?' autoplay playsinline':'';$safeSource=e(live_v545_safe_url($source));$posterAttr=$poster!==''?' poster="'.e($poster).'"':'';
    if($type==='youtube'){$yt=live_v545_youtube_id($source);if($yt!==''){$src='https://www.youtube-nocookie.com/embed/'.rawurlencode($yt).'?rel=0&modestbranding=1'.($autoplay?'&autoplay=1&mute=1':'');return '<div class="live545-player-frame"><iframe src="'.e($src).'" title="'.$title.'" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen loading="eager"></iframe></div>';}}
    if($type==='hls'||(in_array($type,['rtmp','rtsp'],true)&&str_contains(strtolower($source),'.m3u8'))){return '<div class="live545-player-frame"><video class="live545-video live545-hls" controls'.$auto.$mute.$posterAttr.' data-hls="'.$safeSource.'"></video><div class="live545-player-fallback" hidden>Browser could not play this HLS stream. <a target="_blank" rel="noopener" href="'.$safeSource.'">Open stream</a></div></div>';}
    if($type==='video'){return '<div class="live545-player-frame"><video class="live545-video" controls'.$auto.$mute.$posterAttr.' src="'.$safeSource.'"></video></div>';}
    if($type==='mjpeg'){return '<div class="live545-player-frame live545-mjpeg"><img src="'.$safeSource.'" alt="'.$title.'"></div>';}
    if(in_array($type,['rtmp','rtsp'],true)){$raw=e((string)($b['source_url']??''));return '<div class="live545-player-frame live545-unsupported"><div><span>STREAM INGEST</span><h3>'.$title.'</h3><p>RTMP/RTSP cannot play directly in a web browser. Add an HLS playback URL in <b>Browser Playback / Fallback URL</b>.</p><code>'.$raw.'</code></div></div>';}
    if($safeSource!==''){return '<div class="live545-player-frame"><iframe src="'.$safeSource.'" title="'.$title.'" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen loading="eager" sandbox="allow-scripts allow-same-origin allow-forms allow-presentation"></iframe></div>';}
    return '<div class="live545-player-frame live545-unsupported"><div><span>NO SOURCE</span><h3>'.$title.'</h3><p>Add a playback URL from Live Broadcast Center.</p></div></div>';
}
function live_v545_render_assets(bool $admin=false): void {static $done=false;if($done)return;$done=true;echo '<link rel="stylesheet" href="/assets/live-broadcast-5.4.5.css?v=545"><script defer src="/assets/live-broadcast-5.4.5.js?v=545"></script>'; }
function live_render_widget(array $sections=[]): void {
    if(!setting_bool('live_portal_enabled',true)||!setting_bool('live_home_enabled',true))return;$rows=live_v545_public_broadcasts(max(1,setting_int('live_items_limit',4)),true);if(!$rows)return;live_v545_render_assets(false);$lead=$rows[0];
    echo '<section class="live545-home"><div class="live545-home-head"><div><span class="live545-eyebrow"><i></i> LIVE BROADCAST CENTER</span><h2>'.e((string)($sections['live_title']??'Live from '.(function_exists('tenant_brand')?tenant_brand('city_name','Shahkot'):'Shahkot'))).'</h2><p>Watch current broadcasts, community streams and scheduled city coverage.</p></div><a class="live545-viewall" href="/live.php">Open Live Center →</a></div><div class="live545-home-grid">';
    echo '<a class="live545-feature-card" href="/live.php?watch='.(int)$lead['id'].'"><div class="live545-feature-poster"'.(!empty($lead['poster_url'])?' style="background-image:url(\''.e((string)$lead['poster_url']).'\')"':'').'><span class="live545-status '.e((string)$lead['status']).'">'.e(live_v545_status_label((string)$lead['status'])).'</span><span class="live545-play">▶</span></div><div class="live545-feature-copy"><h3>'.e((string)$lead['title']).'</h3><p>'.e(mb_substr(strip_tags((string)($lead['description']??'')),0,120)).'</p><small>'.(int)($lead['total_views']??0).' views · '.e(ucfirst((string)$lead['source_type'])).'</small></div></a>';
    if(count($rows)>1){echo '<div class="live545-mini-list">';foreach(array_slice($rows,1) as $r){echo '<a href="/live.php?watch='.(int)$r['id'].'"><div class="live545-mini-poster"'.(!empty($r['poster_url'])?' style="background-image:url(\''.e((string)$r['poster_url']).'\')"':'').'><span>▶</span></div><div><b>'.e((string)$r['title']).'</b><small>'.e(live_v545_status_label((string)$r['status'])).' · '.(int)($r['total_views']??0).' views</small></div></a>';}echo '</div>';}
    echo '</div></section>';
}

live_v545_maybe_upgrade();
