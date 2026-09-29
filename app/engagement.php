<?php
declare(strict_types=1);

function engagement_safe_link(string $url): string {
    $url=trim($url);if($url==='')return '';
    if(str_starts_with($url,'/'))return $url;
    if(filter_var($url,FILTER_VALIDATE_URL)&&in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true))return $url;
    return '';
}
function engagement_popup(?array $u=null): ?array {
    if(!setting_bool('engagement_popups_enabled',true))return null;$u=$u?:current_user();
    try{
        $rows=db()->query("SELECT * FROM popup_campaigns WHERE enabled=1 AND (starts_at IS NULL OR starts_at<=NOW()) AND (ends_at IS NULL OR ends_at>=NOW()) ORDER BY priority DESC,id DESC LIMIT 30")->fetchAll();
        foreach($rows as $r){$a=$r['audience'];if($a==='guests'&&$u)continue;if($a==='users'&&!$u)continue;if($a==='role'&&(!$u||$u['role']!==$r['target_role']))continue;return $r;}
    }catch(Throwable $e){}
    return null;
}
function engagement_notifications(?array $u=null,int $limit=15): array {
    if(!setting_bool('engagement_notifications_enabled',true))return [];$u=$u?:current_user();$where=["n.status='published'","(n.starts_at IS NULL OR n.starts_at<=NOW())","(n.expires_at IS NULL OR n.expires_at>=NOW())"];$params=[];
    if($u){$where[]="(n.target_type='all' OR (n.target_type='user' AND n.target_user_id=?) OR (n.target_type='role' AND n.target_role=?))";$params[]=(int)$u['id'];$params[]=(string)$u['role'];}
    else $where[]="n.target_type='all'";
    $sql="SELECT n.*".($u?",IF(r.user_id IS NULL,0,1) is_read":"")." FROM site_notifications n".($u?" LEFT JOIN notification_reads r ON r.notification_id=n.id AND r.user_id=".(int)$u['id']:"")." WHERE ".implode(' AND ',$where)." ORDER BY n.id DESC LIMIT ".max(1,min(100,$limit));
    try{$q=db()->prepare($sql);$q->execute($params);return $q->fetchAll();}catch(Throwable $e){return [];}
}
function engagement_unread_count(?array $u=null): int {$u=$u?:current_user();if(!$u)return 0;$rows=engagement_notifications($u,100);return count(array_filter($rows,fn($r)=>(int)($r['is_read']??0)===0));}
function engagement_mark_read(int $id,int $uid): void {try{db()->prepare('INSERT IGNORE INTO notification_reads(notification_id,user_id,read_at) VALUES(?,?,NOW())')->execute([$id,$uid]);}catch(Throwable $e){}}
function engagement_render(): void {
    try{
        $u=current_user();
        $popup=engagement_popup($u);
        $notifications=$u?engagement_notifications($u,8):[];
        $unread=$u?count(array_filter($notifications,fn($n)=>(int)($n['is_read']??0)===0)):0;
        if($u&&$notifications){
            echo '<div class="lc-notification-fab" data-notification-panel><button type="button" class="lc-bell" aria-label="Notifications">🔔'.($unread?'<b>'.e($unread).'</b>':'').'</button><div class="lc-notification-panel"><div><strong>Notifications</strong><a href="/notifications.php">View all</a></div>';
            foreach($notifications as $n){
                $link=engagement_safe_link((string)($n['action_url']??''));
                $tag=$link?'a':'div';
                $href=$link?' href="'.e($link).'"':'';
                echo '<'.$tag.$href.' class="lc-notification-item '.((int)($n['is_read']??0)?'read':'unread').'"><span>'.e($n['icon']?:'●').'</span><div><b>'.e($n['title']).'</b><small>'.e(runtime_excerpt((string)($n['body']??''),100)).'</small></div></'.$tag.'>';
            }
            echo '</div></div>';
        }
        if($popup){
            $id=(int)$popup['id'];$url=engagement_safe_link((string)($popup['action_url']??''));
            echo '<div class="lc-popup lc-popup-'.e($popup['display_type']).'" data-popup-id="'.e($id).'" data-popup-repeat="'.e($popup['repeat_policy']).'" hidden><div class="lc-popup-card">'.($popup['image_url']?'<div class="lc-popup-image" style="background-image:url(\''.e($popup['image_url']).'\')"></div>':'').'<button class="lc-popup-close" type="button">×</button><div class="lc-popup-copy"><span>PROMOTION / NOTICE</span><h3>'.e($popup['title']).'</h3><p>'.e($popup['body']).'</p>'.($url?'<a href="'.e($url).'">'.e($popup['action_label']?:'Learn More').' →</a>':'').'</div></div></div>';
        }
        echo '<script src="/assets/live-commerce-3.4.0.js?v=341"></script>';
    }catch(Throwable $e){
        runtime_log('Engagement rendering failed', $e);
    }
}
