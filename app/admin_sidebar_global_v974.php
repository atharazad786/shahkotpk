<?php
declare(strict_types=1);
/** ShahkotPK v13.4.0 — full-widget no-key maps + marker recovery for admin + Platform Settings. */
$sk1383Audit=__DIR__.'/full_project_audit_v1383.php';if(is_file($sk1383Audit)){require_once $sk1383Audit;if(function_exists('sk1383_boot_runtime_compat'))sk1383_boot_runtime_compat();if(function_exists('sk1383_maybe_run_cleanup'))sk1383_maybe_run_cleanup();}
(static function(): void {
if(!defined('SHAHKOTPK_ADMIN_SHELL_V13034')){
define('SHAHKOTPK_ADMIN_SHELL_V13034',true);
if(!defined('SHAHKOTPK_ADMIN_SHELL_V13031'))define('SHAHKOTPK_ADMIN_SHELL_V13031',true);
if(!defined('SHAHKOTPK_ADMIN_SHELL_V1303'))define('SHAHKOTPK_ADMIN_SHELL_V1303',true);
$uri=(string)($_SERVER['REQUEST_URI']??'');$path=(string)(parse_url($uri,PHP_URL_PATH)?:'');
if(PHP_SAPI!=='cli'&&preg_match('~(?:^|/)admin(?:/|$)~i',$path)){
    foreach(['core_sync_v1301.php','access_control_v1300.php','admin_sidebar_native_modules_v1303.php','map_platform_v1340.php'] as $f){$p=__DIR__.'/'.$f;if(is_file($p))require_once $p;}
    $identity=function_exists('sk1301_identity')?sk1301_identity():[];$theme=function_exists('sk1301_theme')?sk1301_theme():[];$modules=function_exists('sk1303_native_module_items')?sk1303_native_module_items():[];
    $enc=static fn($v):string=>json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?:'[]';
    $identityJson=$enc($identity);$themeJson=$enc($theme);$modulesJson=$enc($modules);
    $routes=[];try{if(function_exists('sk1302_dashboard_routes'))$routes=sk1302_dashboard_routes();}catch(Throwable $e){$routes=[];}
    $routesJson=$enc($routes);$csrfJson=$enc(function_exists('csrf_token')?csrf_token():'');$mapCfg=function_exists('sk1340_map_public_config')?sk1340_map_public_config():[];$mapJson=$enc($mapCfg);$mapNoKey=strtolower((string)($mapCfg['provider']??'openfreemap'))!=='google';
    ob_start(static function(string $html) use($identityJson,$themeJson,$modulesJson,$routesJson,$csrfJson,$mapJson,$mapNoKey):string{
        if($html===''||stripos($html,'<html')===false)return $html;
        try{
            if($mapNoKey){
                $html=preg_replace_callback('~<script\b[^>]*\bsrc=["\']([^"\']*(?:maps\.googleapis\.com|maps\.google\.com)/maps/api/js[^"\']*)["\'][^>]*>\s*</script>~i',static function(array $m): string {$cb='';$u=html_entity_decode((string)($m[1]??''),ENT_QUOTES,'UTF-8');$q=(string)(parse_url($u,PHP_URL_QUERY)??'');if($q!==''){$a=[];parse_str($q,$a);$cb=isset($a['callback'])?preg_replace('~[^A-Za-z0-9_.$]~','',(string)$a['callback']):'';}return '<script src="/assets/google-maps-compat-v1340.js?v=1340"'.($cb!==''?' data-google-callback="'.htmlspecialchars($cb,ENT_QUOTES,'UTF-8').'"':'').'></script>';},$html)??$html;
                $html=preg_replace('~<script[^>]+src=["\'][^"\']*google-maps-compat-v133[1-6]\.js[^"\']*["\'][^>]*>\s*</script>~i','',$html)??$html;
                $html=preg_replace('~<script[^>]+src=["\'][^"\']*maps-guard-v1111\.js[^"\']*["\'][^>]*>\s*</script>~i','',$html)??$html;
                $html=preg_replace('~<link[^>]+href=["\'][^"\']*maps-guard-v1111\.css[^"\']*["\'][^>]*>~i','',$html)??$html;
                if(stripos($html,'google-maps-compat-v1340.js')===false)$html=preg_replace('~<head([^>]*)>~i','<head$1><script src="/assets/google-maps-compat-v1340.js?v=1340"></script>',$html,1)??$html;
            }
            $html=preg_replace('~<script[^>]+src=["\'][^"\']*(?:admin-sidebar-tools-v97[234]|admin-homepage-sidebar-v1010|admin-growth-sidebar-v1020|admin-sidebar-registry-v1021|admin-sidebar-registry-v1022|admin-sidebar-native-recovery-v13023|admin-shell-stability-v1303|admin-native-module-restore-v1303|admin-dashboard-sync-v1302|core-sync-v1301|platform-settings-map-fix-v1311|platform-settings-visual-restore-v1312|platform-settings-root-v1314|platform-settings-root-v1331|platform-settings-root-v1332|map-platform-v1331|map-platform-v1332|map-platform-v1333|map-platform-v1334|map-platform-v1335|map-platform-v1336|map-platform-v1340|platform-settings-diagnostic-v1313)\.js[^"\']*["\'][^>]*>\s*</script>~i','',$html)??$html;
            $html=preg_replace('~<link[^>]+href=["\'][^"\']*(?:admin-sidebar-tools-v97[234]|admin-sidebar-registry-v1021|admin-sidebar-registry-v1022|admin-sidebar-native-recovery-v13023|admin-shell-stability-v1303|admin-native-module-restore-v1303|admin-dashboard-sync-v1302|core-sync-v1301|platform-settings-map-fix-v1311|platform-settings-visual-restore-v1312|platform-settings-root-v1314|platform-settings-root-v1331|platform-settings-root-v1332|map-platform-v1331|map-platform-v1332|map-platform-v1333|map-platform-v1334|map-platform-v1335|map-platform-v1336|map-platform-v1340)\.css[^"\']*["\'][^>]*>~i','',$html)??$html;
            $assets="\n<link rel=\"stylesheet\" href=\"/assets/admin-shell-stability-v1303.css?v=13034\">\n<link rel=\"stylesheet\" href=\"/assets/admin-native-module-restore-v1303.css?v=13034\">\n<script>window.ShahkotPKNativeModulesV1303={$modulesJson};window.ShahkotPKCoreSyncV1301={identity:{$identityJson},theme:{$themeJson}};window.ShahkotPKAdminSyncV1302={routes:{$routesJson}};window.SKAI1100Admin={csrf:{$csrfJson}};window.ShahkotPKMapPlatformV1340={$mapJson};</script>\n<link rel=\"stylesheet\" href=\"/assets/map-platform-v1340.css?v=1384\"><script defer src=\"/assets/map-platform-v1340.js?v=1384\"></script>\n<script defer src=\"/assets/admin-shell-stability-v1303.js?v=13034\"></script>\n<script defer src=\"/assets/admin-native-module-restore-v1303.js?v=13034\"></script>\n<link rel=\"stylesheet\" href=\"/assets/admin-dashboard-sync-v1302.css?v=13034\"><script defer src=\"/assets/admin-dashboard-sync-v1302.js?v=13034\"></script>\n<link rel=\"stylesheet\" href=\"/assets/core-sync-v1301.css?v=13034\"><script defer src=\"/assets/core-sync-v1301.js?v=13034\"></script>\n<link rel=\"stylesheet\" href=\"/assets/platform-settings-root-v1332.css?v=1332\"><script defer src=\"/assets/platform-settings-root-v1332.js?v=1332\"></script>\n<link rel=\"stylesheet\" href=\"/assets/ai-copilot-admin-float-v1100.css?v=1100\"><script defer src=\"/assets/ai-copilot-admin-float-v1100.js?v=1100\"></script>\n";
            $p=stripos($html,'</head>');if($p!==false)return substr($html,0,$p).$assets.substr($html,$p);
            $p=stripos($html,'</body>');return $p!==false?substr($html,0,$p).$assets.substr($html,$p):$html;
        }catch(Throwable $e){return $html;}
    });
}}
})();


