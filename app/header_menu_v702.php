<?php
declare(strict_types=1);

function hm702_tid(): int { return function_exists('tenant_id') ? max(0,(int)tenant_id()) : 0; }
function hm702_table_exists(string $table): bool {
    static $cache=[]; if(array_key_exists($table,$cache)) return $cache[$table];
    try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();}catch(Throwable $e){return $cache[$table]=false;}
}
function hm702_header_config(): array {
    $fallback=[
      'tenant_id'=>hm702_tid(),'header_style'=>'premium','sticky'=>1,'show_tagline'=>1,'show_topbar'=>1,
      'logo_path'=>'','mobile_logo_path'=>'','logo_width'=>148,'menu_align'=>'right','header_bg'=>'#ffffff','header_text'=>'#0f2e28',
      'ticker_enabled'=>1,'ticker_speed'=>38,'ticker_label'=>'CITY UPDATE','ticker_position'=>'below_topbar','ticker_pause_hover'=>1
    ];
    if(!hm702_table_exists('tenant_header_settings_v702')) return $fallback;
    try{$q=db()->prepare('SELECT * FROM tenant_header_settings_v702 WHERE tenant_id=? LIMIT 1');$q->execute([hm702_tid()]);$r=$q->fetch();return $r?array_merge($fallback,$r):$fallback;}catch(Throwable $e){return $fallback;}
}
function hm702_brand_assets(): array {
    $fallback=['tenant_id'=>hm702_tid(),'desktop_dark_logo_path'=>'','desktop_light_logo_path'=>'','mobile_dark_logo_path'=>'','mobile_light_logo_path'=>'','favicon_dark_path'=>'','favicon_light_path'=>'','auto_adapt'=>1];
    if(!hm702_table_exists('tenant_brand_assets_v705')) return $fallback;
    try{$q=db()->prepare('SELECT * FROM tenant_brand_assets_v705 WHERE tenant_id=? LIMIT 1');$q->execute([hm702_tid()]);$r=$q->fetch();return $r?array_merge($fallback,$r):$fallback;}catch(Throwable $e){return $fallback;}
}
function hm702_legacy_logo_url(): string {
    $cfg=hm702_header_config();$custom=trim((string)($cfg['logo_path']??''));if($custom!=='')return $custom;
    if(function_exists('tenant_brand')){$u=trim((string)tenant_brand('logo_url',''));if($u!=='')return $u;}
    return '';
}
function hm702_logo_dark_url(): string { $a=hm702_brand_assets();$v=trim((string)($a['desktop_dark_logo_path']??''));return $v!==''?$v:hm702_legacy_logo_url(); }
function hm702_logo_light_url(): string { $a=hm702_brand_assets();$v=trim((string)($a['desktop_light_logo_path']??''));return $v!==''?$v:hm702_logo_dark_url(); }
function hm702_mobile_logo_dark_url(): string { $a=hm702_brand_assets();$v=trim((string)($a['mobile_dark_logo_path']??''));if($v!=='')return $v;$cfg=hm702_header_config();$legacy=trim((string)($cfg['mobile_logo_path']??''));return $legacy!==''?$legacy:hm702_logo_dark_url(); }
function hm702_mobile_logo_light_url(): string { $a=hm702_brand_assets();$v=trim((string)($a['mobile_light_logo_path']??''));return $v!==''?$v:hm702_logo_light_url(); }
function hm702_favicon_dark_url(): string { $a=hm702_brand_assets();$v=trim((string)($a['favicon_dark_path']??''));return $v!==''?$v:hm702_mobile_logo_dark_url(); }
function hm702_favicon_light_url(): string { $a=hm702_brand_assets();$v=trim((string)($a['favicon_light_path']??''));return $v!==''?$v:hm702_mobile_logo_light_url(); }
function hm702_brand_auto_adapt(): bool { $a=hm702_brand_assets();return !array_key_exists('auto_adapt',$a)||!empty($a['auto_adapt']); }
function hm702_logo_url(): string { return hm702_logo_dark_url(); }
function hm702_mobile_logo_url(): string { return hm702_mobile_logo_dark_url(); }
function hm702_brand_name(string $fallback='ShahkotPK'): string { return function_exists('tenant_brand')?(string)tenant_brand('site_name',$fallback):(function_exists('setting')?(string)setting('site_name',$fallback):$fallback); }
function hm702_tagline(string $fallback='Complete City Guide'): string { return function_exists('tenant_brand')?(string)tenant_brand('tagline',$fallback):$fallback; }
function hm702_safe_url(string $url): string {
    $url=trim($url); if($url==='')return '#';
    if(str_starts_with($url,'/'))return $url;
    if(preg_match('~^https?://~i',$url))return $url;
    return '#';
}
function hm702_resolve_url(string $url,?array $user=null): string {
    if($url==='@account'){
        if($user===null && function_exists('current_user'))$user=current_user();
        if(!$user)return '/login.php';
        if(function_exists('can_access_admin_panel')&&can_access_admin_panel($user))return function_exists('staff_landing_url')?staff_landing_url($user):'/admin/index.php';
        return (($user['role']??'')==='shopkeeper')?'/shopkeeper.php':'/account.php';
    }
    return hm702_safe_url($url);
}
function hm702_active(string $href): bool {
    $href=hm702_safe_url($href); $path=(string)(parse_url((string)($_SERVER['REQUEST_URI']??'/'),PHP_URL_PATH)??'/');
    if($href==='/')return $path==='/'||$path==='/index.php';
    return $path===$href;
}
function hm702_visibility_ok(string $visibility,?array $user): bool {
    $v=strtolower(trim($visibility)); if($v===''||$v==='all')return true;
    if($v==='guest')return !$user; if($v==='logged_in')return (bool)$user;
    if($v==='staff')return $user && function_exists('can_access_admin_panel') && can_access_admin_panel($user);
    return true;
}
function hm702_feature_allowed(string $key): bool {
    try{
        if(in_array($key,['citylife','education','transport','tourism'],true)&&!function_exists('v700_module_enabled')){try{require_once __DIR__.'/super_app_v700.php';}catch(Throwable $e){}}
        return match($key){
            'shop' => !function_exists('feature_enabled')||feature_enabled('store_enabled',true),
            'property' => !function_exists('feature_enabled')||feature_enabled('homepage_property_enabled',true),
            'doctor' => !function_exists('setting_bool')||setting_bool('doctor_online_v580_enabled',true),
            'blood' => !function_exists('setting_bool')||setting_bool('bloodbank_v570_enabled',true),
            'health','health-network','pharmacies','labs','ambulance' => !function_exists('setting_bool')||setting_bool('health_network_v590_enabled',true),
            'smart','smart-city','complaints','wallet','assistant' => !function_exists('setting_bool')||setting_bool('smart_city_v630_enabled',true),
            'citylife','education' => !function_exists('v700_module_enabled')||v700_module_enabled('education'),
            'transport' => !function_exists('v700_module_enabled')||v700_module_enabled('transport'),
            'tourism' => !function_exists('v700_module_enabled')||v700_module_enabled('tourism'),
            'discover','city-guide','businesses','business-directory' => !function_exists('feature_enabled')||feature_enabled('directory_enabled',true),
            'city-map' => !function_exists('feature_enabled')||feature_enabled('google_maps_enabled',true),
            'smart-search' => !function_exists('feature_enabled')||feature_enabled('ai_smart_search_enabled',true),
            'news' => !function_exists('feature_enabled')||feature_enabled('news_portal_enabled',true),
            'blog' => !function_exists('feature_enabled')||feature_enabled('blog_portal_enabled',true),
            'food' => !function_exists('feature_enabled')||feature_enabled('restaurants_enabled',true),
            'services' => !function_exists('feature_enabled')||feature_enabled('services_enabled',true),
            'classifieds' => !function_exists('feature_enabled')||feature_enabled('classifieds_enabled',true),
            'jobs' => !function_exists('feature_enabled')||feature_enabled('homepage_jobs_enabled',true),
            'plans' => !function_exists('feature_enabled')||feature_enabled('subscriptions_enabled',true),
            'signup' => (!function_exists('feature_enabled')||feature_enabled('business_registration_enabled',true)) && (!function_exists('feature_enabled')||feature_enabled('shopkeeper_signup_enabled',true)),
            default => true,
        };
    }catch(Throwable $e){return true;}
}
function hm702_menu_rows(string $location='main'): array {
    if(!hm702_table_exists('tenant_menu_items_v702'))return [];
    try{$q=db()->prepare("SELECT * FROM tenant_menu_items_v702 WHERE tenant_id=? AND location=? ORDER BY sort_order,id");$q->execute([hm702_tid(),$location]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function hm702_all_menu_rows(): array {
    if(!hm702_table_exists('tenant_menu_items_v702'))return [];
    try{$q=db()->prepare("SELECT * FROM tenant_menu_items_v702 WHERE tenant_id=? ORDER BY FIELD(location,'main','topbar','footer','hidden'),sort_order,id");$q->execute([hm702_tid()]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function hm702_menu_html(string $location='main',?array $user=null): string {
    if($user===null && function_exists('current_user'))$user=current_user();
    $rows=hm702_menu_rows($location); if(!$rows)return '';
    $filtered=[];foreach($rows as $r){if(empty($r['enabled'])||!hm702_visibility_ok((string)$r['visibility'],$user)||!hm702_feature_allowed((string)$r['item_key']))continue;$filtered[]=$r;}
    if($location!=='main'){$html='';foreach($filtered as $r){$url=hm702_resolve_url((string)$r['url'],$user);if($url==='#')continue;$label=(string)$r['label'];$icon=(string)($r['icon']??'');$target=(string)($r['target']??'_self');$html.='<a class="hm702-'.$location.'-link" href="'.e($url).'"'.($target==='_blank'?' target="_blank" rel="noopener"':'').'>'.($icon!==''?'<i>'.e($icon).'</i> ':'').e($label).'</a>';}return $html;}
    $byParent=[];$roots=[];
    foreach($filtered as $r){$pk=trim((string)($r['parent_key']??''));if($pk==='')$roots[]=$r;else $byParent[$pk][]=$r;}
    $html='';
    foreach($roots as $r){
        $key=(string)$r['item_key'];$children=$byParent[$key]??[];$label=(string)$r['label'];$icon=(string)($r['icon']??'');$url=hm702_resolve_url((string)$r['url'],$user);$target=(string)($r['target']??'_self');
        if($location==='main' && $children){
            $active=false;foreach($children as $c){if(hm702_active(hm702_resolve_url((string)$c['url'],$user))){$active=true;break;}}
            $html.='<div class="lt-nav-group'.($active?' is-current':'').' hm702-menu-group" data-nav-group><button class="lt-nav-group-toggle" type="button" data-nav-toggle aria-expanded="false"><span>'.e($label).'</span><i aria-hidden="true">⌄</i></button><div class="lt-submenu" role="menu">';
            foreach($children as $c){$cu=hm702_resolve_url((string)$c['url'],$user);$html.='<a class="lt-submenu-link'.(hm702_active($cu)?' is-active':'').'" href="'.e($cu).'"'.(((string)$c['target'])==='_blank'?' target="_blank" rel="noopener"':'').' role="menuitem"><b><i>'.e((string)($c['icon']?:'•')).'</i><span>'.e((string)$c['label']).'</span></b>'.(trim((string)$c['description'])!==''?'<small>'.e((string)$c['description']).'</small>':'').'</a>';}
            $html.='</div></div>';
        }else{
            if($url==='#' && $children)continue;
            $cls=$location==='main'?'lt-nav-link hm702-nav-link':'hm702-'.$location.'-link';
            if($key==='account')$cls.=' lt-login'; if(hm702_active($url))$cls.=' is-active';
            $html.='<a class="'.e($cls).'" href="'.e($url).'"'.($target==='_blank'?' target="_blank" rel="noopener"':'').'>'.($icon!==''?'<span class="lt-nav-symbol">'.e($icon).'</span>':'').'<span>'.e($key==='account'?($user?'Dashboard':'Login'):$label).'</span>'.($key==='account'?'<i aria-hidden="true">↗</i>':'').'</a>';
        }
    }
    return $html;
}
function hm702_legacy_ticker_items(): array {
    $out=[];$candidates=['information_ticker','information_tickers','ticker_items','site_ticker','ticker'];
    foreach($candidates as $table){ if(!hm702_table_exists($table))continue; try{$cols=db()->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);$text='';foreach(['text','title','message','content','headline'] as $c)if(in_array($c,$cols,true)){$text=$c;break;}if($text==='')continue;$link='';foreach(['link_url','url','link','href'] as $c)if(in_array($c,$cols,true)){$link=$c;break;}$status='';foreach(['enabled','is_active','status'] as $c)if(in_array($c,$cols,true)){$status=$c;break;}$sql="SELECT `{$text}` t".($link!==''?",`{$link}` l":",'' l")." FROM `{$table}`";if($status==='enabled'||$status==='is_active')$sql.=" WHERE `{$status}`=1";elseif($status==='status')$sql.=" WHERE `status` IN ('active','published','1')";$sql.=' ORDER BY id DESC LIMIT 12';foreach(db()->query($sql)->fetchAll() as $r){$t=trim((string)$r['t']);if($t!=='')$out[]=['text'=>$t,'link_url'=>(string)$r['l'],'tone'=>'info'];}if($out)break;}catch(Throwable $e){} }
    return $out;
}
function hm702_ticker_items(): array {
    $items=[]; if(hm702_table_exists('tenant_ticker_items_v702'))try{$q=db()->prepare("SELECT * FROM tenant_ticker_items_v702 WHERE tenant_id=? AND enabled=1 AND (starts_at IS NULL OR starts_at<=NOW()) AND (ends_at IS NULL OR ends_at>=NOW()) ORDER BY sort_order,id DESC LIMIT 20");$q->execute([hm702_tid()]);$items=$q->fetchAll()?:[];}catch(Throwable $e){}
    return $items?:hm702_legacy_ticker_items();
}
function hm702_public_assets(): void { static $done=false;if($done)return;$done=true;echo '<link rel="stylesheet" href="/assets/header-menu-7.0.2.css?v=702">'; }
function hm702_ticker_rendered(bool $set=false): bool { static $v=false;if($set)$v=true;return $v; }
function hm702_render_ticker(string $where): void {
    $cfg=hm702_header_config(); if(empty($cfg['ticker_enabled'])||hm702_ticker_rendered())return; $pos=(string)($cfg['ticker_position']??'below_topbar'); if($pos==='hidden'||$pos!==$where)return;
    $items=hm702_ticker_items(); if(!$items)return; hm702_public_assets();hm702_ticker_rendered(true);
    $speed=max(15,min(120,(int)($cfg['ticker_speed']??38)));$label=trim((string)($cfg['ticker_label']??'CITY UPDATE'))?:'CITY UPDATE';$pause=!empty($cfg['ticker_pause_hover']);
    $set='';foreach($items as $it){$text=trim((string)($it['text']??$it['title']??''));if($text==='')continue;$url=hm702_safe_url((string)($it['link_url']??''));$tone=preg_replace('/[^a-z0-9_-]/i','',(string)($it['tone']??'info'));$body='<span class="hm702-ticker-dot tone-'.e($tone).'"></span><span>'.e($text).'</span>';$set.=$url!=='#'?'<a href="'.e($url).'">'.$body.'</a>':'<span class="hm702-ticker-item">'.$body.'</span>';}
    if($set==='')return;echo '<div class="hm702-ticker'.($pause?' pause-hover':'').'" style="--hm702-duration:'.$speed.'s"><b class="hm702-ticker-label">'.e($label).'</b><div class="hm702-ticker-viewport"><div class="hm702-ticker-track"><div class="hm702-ticker-set">'.$set.'</div><div class="hm702-ticker-set" aria-hidden="true">'.$set.'</div></div></div></div>';
}
function hm702_footer_menu(): string {return hm702_menu_html('footer',function_exists('current_user')?current_user():null);}
function hm702_topbar_menu(): string {return hm702_menu_html('topbar',function_exists('current_user')?current_user():null);}
