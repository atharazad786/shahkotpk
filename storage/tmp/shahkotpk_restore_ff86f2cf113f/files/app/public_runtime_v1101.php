<?php
declare(strict_types=1);
/** ShahkotPK v13.0.3.4 — native-first public runtime stabilization. */
if(!function_exists('sk1101_public_transform')){
$sk1383Audit=__DIR__.'/full_project_audit_v1383.php';if(is_file($sk1383Audit)){require_once $sk1383Audit;if(function_exists('sk1383_boot_runtime_compat'))sk1383_boot_runtime_compat();if(function_exists('sk1383_maybe_run_cleanup'))sk1383_maybe_run_cleanup();}
function sk1101_root(): string{return dirname(__DIR__);}
function sk1101_h($v): string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function sk1101_is_public_html_request(): bool {$uri=(string)($_SERVER['REQUEST_URI']??'/');$path=(string)(parse_url($uri,PHP_URL_PATH)?:'/');return PHP_SAPI!=='cli'&&!preg_match('~^/(?:admin|api|cron)(?:/|$)~i',$path)&&in_array(strtoupper((string)($_SERVER['REQUEST_METHOD']??'GET')),['GET','HEAD'],true);}
function sk1101_is_home_request(): bool {$p=(string)(parse_url((string)($_SERVER['REQUEST_URI']??'/'),PHP_URL_PATH)?:'/');return $p==='/'||$p==='/index.php';}
function sk1101_boot_dependencies(): void {foreach(['session_security_v13151.php','map_platform_v1340.php','core_sync_v1301.php','unified_experience_v1100.php','public_nav_v1100.php','ai_copilot_v1100.php','landing_theme_parity_v1110.php','maps_guard_v1111.php','user_experience_v1120.php','business_microsite_v1130.php','business_geo_map_bridge_v1284.php','map_data_v1340.php','city_discovery_v1350.php','access_control_v1300.php','landing_sync_v1303.php'] as $f){$p=__DIR__.'/'.$f;if(is_file($p))require_once $p;}}
function sk1101_has_native_search_html(string $html): bool {
    if(stripos($html,'data-sk1110-search')!==false||stripos($html,'data-smart-search')!==false)return true;
    if(preg_match('~<input[^>]+type=["\\\']search["\\\'][^>]*>~i',$html))return true;
    if(preg_match('~<form[^>]+(?:action=["\\\'][^"\\\']*(?:search|find|discover)[^"\\\']*["\\\']|class=["\\\'][^"\\\']*(?:search|finder)[^"\\\']*["\\\'])[^>]*>~i',$html))return true;
    if(preg_match('~<input[^>]+(?:name=["\\\'](?:q|query|search|keyword|keywords)["\\\']|placeholder=["\\\'][^"\\\']*(?:search|doctor|shop|service|product|job|property|restaurant)[^"\\\']*["\\\'])[^>]*>~i',$html))return true;
    if(stripos($html,'All Categories')!==false&&preg_match('~>\\s*Search\\s*(?:→|&rarr;|</button>|</a>)~i',$html))return true;
    return false;
}
function sk1101_public_config(): array {
    sk1101_boot_dependencies();
    $identity=function_exists('sk1301_identity')?sk1301_identity():(function_exists('sk1100_site_identity')?sk1100_site_identity():['name'=>'ShahkotPK','logo'=>'','favicon'=>'','tagline'=>'']);
    $items=[];try{if(function_exists('spn1100_items'))$items=spn1100_items();}catch(Throwable $e){}
    $safe=[];foreach($items as $x){if(empty($x['enabled']))continue;$navUrl=(string)($x['url']??'#');if(function_exists('sk1300_nav_url_allowed')&&!sk1300_nav_url_allowed($navUrl))continue;$safe[]=['key'=>(string)($x['nav_key']??''),'label'=>(string)($x['label']??''),'url'=>$navUrl,'group'=>(string)($x['group_key']??'primary'),'parent'=>(string)($x['parent_key']??''),'order'=>(int)($x['sort_order']??100)];}
    return ['identity'=>$identity,'items'=>$safe,'home'=>sk1101_is_home_request(),'version'=>'13.0.3.4','theme'=>function_exists('sk1301_theme')?sk1301_theme():[],'businessMicrosites'=>function_exists('sk1130_public_config')?sk1130_public_config():[],'ux'=>function_exists('sk1120_public_config')?sk1120_public_config():[],'maps'=>function_exists('sk1111_maps_public_config')?sk1111_maps_public_config():['configured'=>true],'parity'=>function_exists('sk1110_runtime_config')?sk1110_runtime_config():[],'businessMap'=>function_exists('sk1284_public_map_config')?sk1284_public_map_config():[],'landingSync'=>function_exists('sk1303_landing_config')?sk1303_landing_config():[],'mapPlatform'=>function_exists('sk1340_map_public_config')?sk1340_map_public_config():[],'cityDiscovery'=>function_exists('sk1350cd_config')?sk1350cd_config():[]];
}
function sk1101_feature_strip_html(): string {
    return '<section class="sk1101-explore" data-sk1101-feature-strip><div class="sk1101-wrap"><div class="sk1101-section-head"><span>SHAHKOTPK QUICK ACCESS</span><h2>What do you need today?</h2><p>Useful ShahkotPK services from the current live homepage.</p></div><div class="sk1101-quick-grid"><a href="/assistant-v1040.php"><b>✦ Ask ShahkotPK</b><small>Search local services &amp; information</small></a><a href="/my-shahkot.php"><b>⌂ My Shahkot</b><small>Requests, nearby &amp; daily utilities</small></a><a href="/deals-v1050.php"><b>％ Local Deals</b><small>Coupons, offers &amp; rewards</small></a><a href="/my-bookings-v1040.php"><b>✓ My Bookings</b><small>Track appointments &amp; bookings</small></a><a href="/my-wallet-v1050.php"><b>★ Rewards Wallet</b><small>Saved coupons &amp; loyalty points</small></a><a href="/my-shahkot.php#need-something"><b>⚡ Need a Service?</b><small>Ask local businesses for help</small></a></div></div></section>';
}
function sk1101_ai_html(): string {
    return '<section class="sk1100-ai" data-ai-copilot="public" aria-live="polite"><button class="sk1100-ai-launch" type="button" aria-expanded="false"><span>✦</span> Ask ShahkotPK</button><div class="sk1100-ai-panel" hidden><header><div><b>ShahkotPK Assistant</b><small>Website help, discovery &amp; information</small></div><button type="button" data-ai-close>×</button></header><div class="sk1100-ai-messages" data-ai-messages><div class="ai-msg bot">Assalam-o-Alaikum! ShahkotPK par kisi business, product, property, doctor, deal, booking ya local service ke bare mein pooch sakte hain.</div></div><form data-ai-form><textarea name="message" rows="2" maxlength="1200" placeholder="Type or speak your question…" required></textarea><div class="ai-actions"><button type="button" data-ai-voice title="Voice input">🎙 Voice</button><button type="button" data-ai-speak title="Read last answer">🔊 Read</button><button type="submit">Send</button></div></form><small class="ai-status" data-ai-status></small></div></section>';
}
function sk1101_strip_step3_assets(string $html): string {
    $names='(?:core-sync-v1301|public-header-sync-v1302|landing-section-sync-v1303)';
    $html=preg_replace('~<script[^>]+src=["\\\'][^"\\\']*'.$names.'\\.js[^"\\\']*["\\\'][^>]*>\\s*</script>~i','',$html)??$html;
    $html=preg_replace('~<link[^>]+href=["\\\'][^"\\\']*'.$names.'\\.css[^"\\\']*["\\\'][^>]*>~i','',$html)??$html;
    return $html;
}
function sk1101_google_maps_nokey_transform(string $html,array $mapPlatform): string {
    $provider=strtolower((string)($mapPlatform['provider']??'openfreemap'));
    if($provider==='google')return $html;
    // Replace every direct Google Maps JS dependency with the local compatibility shim, preserving callback names.
    $html=preg_replace_callback('~<script\\b[^>]*\\bsrc=["\\\']([^"\\\']*(?:maps\\.googleapis\\.com|maps\\.google\\.com)/maps/api/js[^"\\\']*)["\\\'][^>]*>\\s*</script>~i',static function(array $m): string {
        $cb='';$u=html_entity_decode((string)($m[1]??''),ENT_QUOTES,'UTF-8');$q=(string)(parse_url($u,PHP_URL_QUERY)??'');if($q!==''){$a=[];parse_str($q,$a);$cb=isset($a['callback'])?preg_replace('~[^A-Za-z0-9_.$]~','',(string)$a['callback']):'';}
        return '<script src="/assets/google-maps-compat-v1340.js?v=1340"'.($cb!==''?' data-google-callback="'.sk1101_h($cb).'"':'').'></script>';
    },$html)??$html;
    // Remove stale compatibility shims from earlier releases; v13.3.6 is the only authoritative bridge.
    $html=preg_replace('~<script[^>]+src=["\'][^"\']*google-maps-compat-v133[1-6]\.js[^"\']*["\'][^>]*>\s*</script>~i','',$html)??$html;
    // Old maps guard is Google-specific and can recreate the authentication warning after the no-key map has loaded.
    $html=preg_replace('~<script[^>]+src=["\\\'][^"\\\']*maps-guard-v1111\\.js[^"\\\']*["\\\'][^>]*>\\s*</script>~i','',$html)??$html;
    $html=preg_replace('~<link[^>]+href=["\\\'][^"\\\']*maps-guard-v1111\\.css[^"\\\']*["\\\'][^>]*>~i','',$html)??$html;
    if(stripos($html,'google-maps-compat-v1340.js')===false){$html=preg_replace('~<head([^>]*)>~i','<head$1><script src="/assets/google-maps-compat-v1340.js?v=1340"></script>', $html,1)??$html;}
    return $html;
}
function sk1101_public_transform(string $html): string {
    $original=$html;
    try{
        if($html===''||stripos($html,'<html')===false||stripos($html,'</body>')===false)return $html;
        $cfg=sk1101_public_config();
        $mapPlatform=(array)($cfg['mapPlatform']??[]);
        $html=sk1101_google_maps_nokey_transform($html,$mapPlatform);
        $json=json_encode($cfg,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?:'{}';
        if(stripos($html,'name="viewport"')===false&&stripos($html,"name='viewport'")===false){$html=preg_replace('~</head>~i','<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"></head>',$html,1)??$html;}

        // Always replace Step-3 runtime assets with one current copy. This prevents mixed cached versions.
        $html=sk1101_strip_step3_assets($html);
        $smartHome=function_exists('sk1110_is_smart_home_html')&&sk1110_is_smart_home_html($html);

        // Legacy enhancement bundle remains guarded; native/smart themes are not given duplicate homepage parity CSS.
        if(stripos($html,'active-landing-v1101.css')===false){
            $csrf=function_exists('csrf_token')?(string)csrf_token():'';
            $parityAssets=($smartHome||empty($cfg['home']))?'':
                  "<link rel=\"stylesheet\" href=\"/assets/homepage-v1010.css?v=1110\">\n".
                  "<link rel=\"stylesheet\" href=\"/assets/growth-v1020.css?v=1110\">\n".
                  "<link rel=\"stylesheet\" href=\"/assets/homepage-utility-v1030.css?v=1110\">\n".
                  "<link rel=\"stylesheet\" href=\"/assets/homepage-assistant-v1040.css?v=1110\">\n".
                  "<link rel=\"stylesheet\" href=\"/assets/deals-loyalty-v1050.css?v=1110\">\n".
                  "<link rel=\"stylesheet\" href=\"/assets/landing-theme-parity-v1110.css?v=1117\">\n".
                  "<script defer src=\"/assets/growth-v1020.js?v=1110\"></script>\n".
                  "<script defer src=\"/assets/landing-theme-parity-v1110.js?v=1110\"></script>\n";
            $mapsCfg=(array)($cfg['maps']??['configured'=>true]);$mapsJson=json_encode($mapsCfg,JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?:'{}';
            $noKeyMaps=strtolower((string)($mapPlatform['provider']??'openfreemap'))!=='google';
            $mapsGuardHead=$noKeyMaps?"\n<script>window.ShahkotPKMapsGuard={configured:true,provider:'openfreemap'};</script>\n":"\n<script>window.ShahkotPKMapsGuard={$mapsJson};window.gm_authFailure=window.gm_authFailure||function(){document.documentElement.classList.add('sk1111-maps-auth-failed');};</script>\n<link rel=\"stylesheet\" href=\"/assets/maps-guard-v1111.css?v=1111\">\n<script defer src=\"/assets/maps-guard-v1111.js?v=1111\"></script>\n";
            $legacyHead=$mapsGuardHead."<link rel=\"stylesheet\" href=\"/assets/active-landing-v1101.css?v=1117\">\n".
                  $parityAssets.
                  "<link rel=\"stylesheet\" href=\"/assets/ai-copilot-public-v1100.css?v=1110\">\n".
                  "<link rel=\"stylesheet\" href=\"/assets/user-experience-v1120.css?v=1120\">\n".
                  ($csrf!==''?'<meta name="csrf-token" content="'.sk1101_h($csrf).'">'."\n":'').
                  "<script defer src=\"/assets/active-landing-v1101.js?v=1117\"></script>\n".
                  "<script defer src=\"/assets/ai-copilot-public-v1100.js?v=1110\"></script>\n".
                  "<script defer src=\"/assets/user-experience-v1120.js?v=1120\"></script>\n".
                  "<script defer src=\"/assets/business-directory-links-v1130.js?v=1130\"></script>\n".
                  "<link rel=\"stylesheet\" href=\"/assets/city-info-header-v1242.css?v=1242\">\n".
                  "<script defer src=\"/assets/city-info-header-v1242.js?v=1242\"></script>\n".
                  "<link rel=\"stylesheet\" href=\"/assets/city-updates-scroll-v1261.css?v=1261\">\n".
                  "<script defer src=\"/assets/city-updates-scroll-v1261.js?v=1261\"></script>\n".
                  "<script>window.ShahkotPKBusinessMapV1284=".json_encode((array)($cfg['businessMap']??[]),JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).";</script>\n".
                  "<script defer src=\"/assets/business-map-bridge-v1284.js?v=1284\"></script>\n";
            $html=preg_replace('~</head>~i',$legacyHead.'</head>',$html,1)??$html;
        }

        // v13.3.6 full-widget + marker-data no-key map platform: replace Google maps across all public HTML surfaces.
        // Remove stale map-platform bridge copies before injecting the current one.
        $html=preg_replace('~<script[^>]+src=["\'][^"\']*map-platform-v133[1-6]\.js[^"\']*["\'][^>]*>\s*</script>~i','',$html)??$html;
        $html=preg_replace('~<link[^>]+href=["\'][^"\']*map-platform-v133[1-6]\.css[^"\']*["\'][^>]*>~i','',$html)??$html;
        if(stripos($html,'map-platform-v1340.js')===false){
            $mapPlatformJson=json_encode($mapPlatform,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?:'{}';
            $mapHead="\n<script>window.ShahkotPKMapPlatformV1340={$mapPlatformJson};</script>\n<link rel=\"stylesheet\" href=\"/assets/map-platform-v1340.css?v=1340\">\n<script defer src=\"/assets/map-platform-v1340.js?v=1340\"></script>\n";
            $html=preg_replace('~</head>~i',$mapHead.'</head>',$html,1)??$html;
        }

        // v13.5.0 City Discovery: homepage-only content layer powered by map-ready records. Native menu is not changed.
        $cityDiscovery=(array)($cfg['cityDiscovery']??[]);
        if(!empty($cfg['home'])&&!empty($cityDiscovery['enabled'])&&!empty($cityDiscovery['homeEnabled'])){
            $html=preg_replace('~<script[^>]+src=["\'][^"\']*city-discovery-v1350\.js[^"\']*["\'][^>]*>\s*</script>~i','',$html)??$html;
            $html=preg_replace('~<link[^>]+href=["\'][^"\']*city-discovery-v1350\.css[^"\']*["\'][^>]*>~i','',$html)??$html;
            $cdJson=json_encode($cityDiscovery,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?:'{}';
            $cdHead="\n<script>window.ShahkotPKCityDiscoveryV1350={$cdJson};</script>\n<link rel=\"stylesheet\" href=\"/assets/city-discovery-v1350.css?v=1350\"><script defer src=\"/assets/city-discovery-v1350.js?v=1350\"></script>\n";
            $html=preg_replace('~</head>~i',$cdHead.'</head>',$html,1)??$html;
            if(stripos($html,'data-sk1350-city-discovery')===false){$slot='<section data-sk1350-city-discovery aria-live="polite"></section>';$fp=stripos($html,'<footer');if($fp!==false)$html=substr($html,0,$fp).$slot.substr($html,$fp);else $html=preg_replace('~</body>~i',$slot.'</body>',$html,1)??$html;}
        }

        // Native menu stays authoritative. Step-3 only restores stale v13.0.3.2 markers and syncs identity/theme.
        $step3Head="\n<meta name=\"shahkotpk-step3-runtime\" content=\"13.0.3.4\">\n".
            "<script>window.ShahkotPKActiveLandingV1101={$json};</script>\n".
            "<link rel=\"stylesheet\" href=\"/assets/core-sync-v1301.css?v=13034\"><script defer src=\"/assets/core-sync-v1301.js?v=13034\"></script>\n".
            "<link rel=\"stylesheet\" href=\"/assets/public-header-sync-v1302.css?v=13034\"><script defer src=\"/assets/public-header-sync-v1302.js?v=13034\"></script>\n";
        if(!empty($cfg['home']))$step3Head.="<link rel=\"stylesheet\" href=\"/assets/landing-section-sync-v1303.css?v=13034\"><script defer src=\"/assets/landing-section-sync-v1303.js?v=13034\"></script>\n";
        $html=preg_replace('~</head>~i',$step3Head.'</head>',$html,1)??$html;

        if(!empty($cfg['home'])&&function_exists('sk1110_config')){
            $pc=sk1110_config();
            if(!empty($pc['smart_search'])&&!sk1101_has_native_search_html($html)){
                $search=sk1110_search_html();$hp=stripos($html,'</header>');
                if($hp!==false)$html=substr($html,0,$hp+9).$search.substr($html,$hp+9);
                else{$mp=stripos($html,'<main');if($mp!==false){$gt=strpos($html,'>',$mp);$html=$gt!==false?substr($html,0,$gt+1).$search.substr($html,$gt+1):$html;}else $html=preg_replace('~<body([^>]*)>~i','<body$1>'.$search,$html,1)??$html;}
            }
            if(!empty($pc['inject_missing_sections'])&&strpos($html,'data-sk1112-parity-loader')===false){$loader='<div data-sk1112-parity-loader aria-live="polite"></div>';$pos=stripos($html,'<footer');if($pos!==false)$html=substr($html,0,$pos).$loader.substr($html,$pos);else $html=preg_replace('~</body>~i',$loader.'</body>',$html,1)??$html;}
        }elseif(!empty($cfg['home'])&&strpos($html,'data-sk1101-feature-strip')===false){$strip=sk1101_feature_strip_html();$pos=stripos($html,'<footer');if($pos!==false)$html=substr($html,0,$pos).$strip.substr($html,$pos);else $html=preg_replace('~</body>~i',$strip.'</body>',$html,1)??$html;}

        if(!empty($cfg['home'])&&(!isset($cfg['parity']['ai'])||$cfg['parity']['ai'])&&strpos($html,'data-ai-copilot="public"')===false&&strpos($html,"data-ai-copilot='public'")===false){$bot=sk1101_ai_html();if($bot!=='')$html=preg_replace('~</body>~i',$bot.'</body>',$html,1)??$html;}
        return $html;
    }catch(Throwable $e){try{if(function_exists('runtime_log'))runtime_log('v13.0.3.4 public runtime recovered from transform error',$e);}catch(Throwable $ignored){}return $original;}
}
sk1101_boot_dependencies();if(function_exists('sk1300_guard_public_request'))sk1300_guard_public_request();
if(sk1101_is_public_html_request()&&!defined('SHAHKOTPK_PUBLIC_RUNTIME_V1101_STARTED')){define('SHAHKOTPK_PUBLIC_RUNTIME_V1101_STARTED',true);ob_start('sk1101_public_transform');}
}
