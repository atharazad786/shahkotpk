<?php
declare(strict_types=1);
/**
 * ShahkotPK v13.8.3 — bounded production audit compatibility + one-time safe cleanup.
 * No full-tree scanner runs on ordinary requests.
 */
if(!function_exists('sk1383_root')){
function sk1383_root(): string { return dirname(__DIR__); }
function sk1383_h($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function sk1383_db(): ?PDO { try{return function_exists('db')?db():null;}catch(Throwable $e){return null;} }
function sk1383_setting(string $key,?string $default=null): ?string {
    try{$pdo=sk1383_db();if(!$pdo)return $default;$q=$pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}catch(Throwable $e){return $default;}
}
function sk1383_set_setting(string $key,string $value): void {
    try{$pdo=sk1383_db();if(!$pdo)return;$q=$pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$q->execute([$key,$value]);}catch(Throwable $e){}
}
function sk1383_bool(string $key,bool $default=false): bool { return in_array(strtolower((string)sk1383_setting($key,$default?'1':'0')),['1','true','yes','on','enabled'],true); }
function sk1383_table(string $table): bool {
    static $c=[];if(isset($c[$table]))return $c[$table];
    if(!preg_match('/^[A-Za-z0-9_]+$/',$table))return false;
    try{$pdo=sk1383_db();if(!$pdo)return $c[$table]=false;$q=$pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $c[$table]=(bool)$q->fetchColumn();}catch(Throwable $e){return $c[$table]=false;}
}
function sk1383_stale_assets(): array { return [
 'assets/google-maps-3.1.0.css','assets/google-maps-3.1.0.js',
 'assets/google-maps-compat-v1333.js','assets/google-maps-compat-v1334.js','assets/google-maps-compat-v1336.js',
 'assets/map-platform-v1331.css','assets/map-platform-v1331.js','assets/map-platform-v1332.css','assets/map-platform-v1332.js',
 'assets/map-platform-v1333.css','assets/map-platform-v1333.js','assets/map-platform-v1334.css','assets/map-platform-v1334.js',
 'assets/map-platform-v1336.css','assets/map-platform-v1336.js',
 'assets/platform-settings-diagnostic-v1313.js',
 'assets/platform-settings-map-fix-v1311.css','assets/platform-settings-map-fix-v1311.js',
 'assets/platform-settings-visual-restore-v1312.css','assets/platform-settings-visual-restore-v1312.js',
 'assets/platform-settings-root-v1314.css','assets/platform-settings-root-v1314.js','assets/platform-settings-root-v1331.js'
]; }
function sk1383_safe_rel(string $rel): bool {
    $rel=str_replace('\\','/',$rel);return $rel!==''&&!str_starts_with($rel,'/')&&!str_contains($rel,'..')&&preg_match('~^(?:[A-Za-z0-9_.-]+\.php|(?:assets|app|admin|api|data|database)/[A-Za-z0-9_./-]+)$~',$rel)===1;
}
function sk1383_source_backup(string $path,string $rel): void {
    if(!is_file($path))return;$dir=sk1383_root().'/storage/audit-backups-v1383';if(!is_dir($dir))@mkdir($dir,0750,true);if(!is_dir($dir)||!is_writable($dir))return;
    $safe=preg_replace('/[^A-Za-z0-9_.-]+/','_',str_replace('/','__',$rel))?:'source.php';$dest=$dir.'/'.$safe.'.'.date('YmdHis').'.bak';@copy($path,$dest);
}
function sk1383_patch_text_file(string $rel,callable $transform,array &$notes): bool {
    if(!sk1383_safe_rel($rel))return false;$path=sk1383_root().'/'.$rel;if(!is_file($path)||!is_readable($path)||!is_writable($path))return false;
    $old=@file_get_contents($path);if(!is_string($old)||$old==='')return false;
    try{$out=$transform($old);}catch(Throwable $e){$notes[]=$rel.': patch skipped ('.$e->getMessage().')';return false;}
    if(!is_string($out)||$out===$old)return false;
    sk1383_source_backup($path,$rel);$tmp=$path.'.v1383tmp';if(@file_put_contents($tmp,$out,LOCK_EX)===false)return false;
    if(!@rename($tmp,$path)){@unlink($tmp);return false;}$notes[]=$rel.': repaired';return true;
}
function sk1383_apply_source_repairs(): array {
    $notes=[];$changed=0;
    // current_tenant() historically returned false from tenant_master() despite ?array declaration.
    $changed+=sk1383_patch_text_file('app/tenancy_v5.php',static function(string $s): string {
        if(!str_contains($s,'return $cache=tenant_master();'))return $s;
        return str_replace('return $cache=tenant_master();','$tm=tenant_master();return $cache=is_array($tm)?$tm:null;',$s);
    },$notes)?1:0;
    // Historical DB schema mismatch: advertisements owns budget; daily/stat alias did not.
    $changed+=sk1383_patch_text_file('ad-click.php',static function(string $s): string {
        if(!str_contains($s,'d.budget'))return $s;if(!preg_match('/(?:FROM|JOIN)\s+`?advertisements`?\s+a\b/i',$s))return $s;return str_replace('d.budget','a.budget',$s);
    },$notes)?1:0;
    // Missing lang query must never pass null to strict blog helpers.
    $changed+=sk1383_patch_text_file('blog.php',static function(string $s): string {
        $n=preg_replace('~blog_title\(([^,()]+),\s*\$_GET\[\'lang\'\]\)~','blog_title($1,(string)($_GET[\'lang\']??\'en\'))',$s);
        return is_string($n)?$n:$s;
    },$notes)?1:0;
    // Duplicate payment helper definitions were caused by repeat include chains.
    $changed+=sk1383_patch_text_file('payment-return.php',static function(string $s): string {
        $s=preg_replace('~\brequire\s+(__DIR__\s*\.\s*[\'\"]/app/(?:payments|payfast)\.php[\'\"]\s*);~i','require_once $1;',$s)??$s;
        return $s;
    },$notes)?1:0;
    // PHP 8 strict type: area unit can be NULL in legacy property records.
    $changed+=sk1383_patch_text_file('app/property_v560.php',static function(string $s): string {
        $old='function pr560_area(float $area,string $unit): string {';if(!str_contains($s,$old))return $s;
        $new='function pr560_area(float $area,?string $unit=null): string {$unit=(string)($unit?:\'marla\');';
        return str_replace($old,$new,$s);
    },$notes)?1:0;
    // Prepared placeholders are invalid inside MySQL INTERVAL grammar; clamp and interpolate integer only.
    $changed+=sk1383_patch_text_file('app/security_performance_v1000.php',static function(string $s): string {
        $needle='DATE_ADD(NOW(),INTERVAL'.' ? MINUTE)';
        if(!str_contains($s,$needle))return $s;
        $s=str_replace($needle,"DATE_ADD(NOW(),INTERVAL '.\$minutes.' MINUTE)",$s);
        $s=str_replace("mb_substr(trim(\$reason)?:'Manual security block',0,255),\$minutes,\$actor","mb_substr(trim(\$reason)?:'Manual security block',0,255),\$actor",$s);
        return $s;
    },$notes)?1:0;
    sk1383_set_setting('full_project_audit_source_repairs',(string)$changed);
    sk1383_set_setting('full_project_audit_source_repair_notes',json_encode($notes,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?:'[]');
    return ['changed'=>$changed,'notes'=>$notes];
}
function sk1383_write_installed_manifest(): bool {
    $path=sk1383_root().'/update.json';$data=[
      'app'=>'ShahkotPK','version'=>'13.8.3','installed_state'=>true,'release_channel'=>'stable',
      'notes'=>'Installed production state after v13.8.3 full project audit and stabilization.',
      'generated_at'=>date('c')
    ];
    $json=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if(!$json)return false;
    $ok=@file_put_contents($path,$json."\n",LOCK_EX)!==false;if($ok)sk1383_set_setting('full_project_audit_manifest_refreshed_at',date('Y-m-d H:i:s'));return $ok;
}
function sk1383_cleanup(bool $force=false): array {
    $root=sk1383_root();$removed=[];$failed=[];
    foreach(sk1383_stale_assets() as $rel){$p=$root.'/'.$rel;if(!is_file($p))continue;if(@unlink($p))$removed[]=$rel;else $failed[]=$rel;}
    $repairs=sk1383_apply_source_repairs();$manifest=sk1383_write_installed_manifest();
    $status=$failed?'attention':'complete';sk1383_set_setting('full_project_audit_cleanup_pending','0');sk1383_set_setting('full_project_audit_cleanup_status',$status);sk1383_set_setting('full_project_audit_cleanup_at',date('Y-m-d H:i:s'));
    return ['status'=>$status,'removed'=>$removed,'failed'=>$failed,'source_repairs'=>$repairs,'manifest'=>$manifest];
}
function sk1383_maybe_run_cleanup(): void {
    static $done=false;if($done)return;$done=true;if(!sk1383_db())return;if(sk1383_bool('full_project_audit_cleanup_pending',false))sk1383_cleanup(false);
}
function sk1383_boot_runtime_compat(): void {
    if(!function_exists('csrf_field')){function csrf_field(): string {$t=function_exists('csrf_token')?(string)csrf_token():'';return '<input type="hidden" name="_csrf" value="'.htmlspecialchars($t,ENT_QUOTES,'UTF-8').'">';}}
    if(!function_exists('spn1100_core_routes')){function spn1100_core_routes(): array {return [];}}
    if(!function_exists('shahkot_maps_ready')){function shahkot_maps_ready(): bool {$p=strtolower(trim((string)(function_exists('setting')?setting('map_provider','openfreemap'):'openfreemap')));if($p===''||$p==='auto')$p='openfreemap';if(in_array($p,['openfreemap','leaflet'],true))return true;return function_exists('google_maps_ready')?google_maps_ready():false;}}
}
function sk1383_updater_delete_support(): bool {
    $p=sk1383_root().'/app/updater.php';if(!is_file($p))return false;$s=@file_get_contents($p);return is_string($s)&&str_contains($s,'delete_files')&&(str_contains($s,'updater_apply_delete_files')||str_contains($s,'deleteFiles'));
}
function sk1383_manifest_version(): string {
    $p=sk1383_root().'/update.json';if(!is_file($p))return '';$j=json_decode((string)@file_get_contents($p),true);return is_array($j)?(string)($j['version']??''):'';
}
function sk1383_snapshot(): array {
    $root=sk1383_root();$checks=[];$add=static function(string $key,string $label,bool $ok,string $detail='')use(&$checks){$checks[]=['key'=>$key,'label'=>$label,'ok'=>$ok,'detail'=>$detail];};
    $add('php','PHP 8+',version_compare(PHP_VERSION,'8.0.0','>='),PHP_VERSION);
    foreach(['pdo','pdo_mysql','json'] as $ext)$add('ext_'.$ext,'PHP extension '.$ext,extension_loaded($ext),extension_loaded($ext)?'loaded':'missing');
    $add('ext_zip','PHP extension zip',class_exists('ZipArchive'),class_exists('ZipArchive')?'loaded':'missing');
    foreach(['mbstring','fileinfo','curl','openssl'] as $ext)$add('optional_'.$ext,'Optional extension '.$ext,extension_loaded($ext),extension_loaded($ext)?'loaded':'recommended for full feature coverage');
    foreach(['storage','uploads'] as $d){$p=$root.'/'.$d;$add('write_'.$d,ucfirst($d).' writable',is_dir($p)&&is_writable($p),$p);}
    foreach(['settings','users','access_features_v1300','access_roles_v1300','access_role_features_v1300'] as $t)$add('table_'.$t,'DB table '.$t,sk1383_table($t),sk1383_table($t)?'available':'missing');
    $critical=['app/bootstrap.php','app/layout.php','app/tenancy_v5.php','app/updater.php','app/maps.php','app/map_platform_v1340.php','admin/settings.php','admin/maps.php','admin/services.php','admin/classifieds.php','admin/lms.php','services.php','classifieds.php','education-network.php','city-map.php','register.php','dashboard.php'];
    foreach($critical as $rel)$add('file_'.str_replace(['/','.'],['_','_'],$rel),'Critical route/file '.$rel,is_file($root.'/'.$rel),is_file($root.'/'.$rel)?'present':'missing');
    $installed=(string)sk1383_setting('installed_app_version','');$add('version','DB installed version 13.8.3',$installed==='13.8.3',$installed?:'unknown');
    $mv=sk1383_manifest_version();$add('manifest','Root installed manifest 13.8.3',$mv==='13.8.3',$mv?:'missing/old');
    $stale=array_values(array_filter(sk1383_stale_assets(),fn($r)=>is_file($root.'/'.$r)));$add('stale','Superseded assets removed',count($stale)===0,count($stale).' remaining');
    $provider=strtolower((string)sk1383_setting('map_provider','openfreemap'));$add('map','No-key map provider active',in_array($provider,['openfreemap','leaflet'],true),$provider?:'unknown');
    $add('dummy','Automatic dummy reseeding disabled',!sk1383_bool('dummy_data_autoload_enabled',false),sk1383_setting('dummy_data_autoload_enabled','0')??'0');
    $add('cleanup','v13.8.3 one-time cleanup complete',sk1383_setting('full_project_audit_cleanup_pending','0')!=='1',(string)sk1383_setting('full_project_audit_cleanup_status','unknown'));
    $add('updater_delete','Updater supports explicit delete_files',sk1383_updater_delete_support(),sk1383_updater_delete_support()?'supported':'future updater packages should use v13.8.3 safe cleanup unless updater core is upgraded');
    $pass=count(array_filter($checks,fn($x)=>$x['ok']));
    return ['version'=>'13.8.3','generated_at'=>date('c'),'checks'=>$checks,'pass'=>$pass,'total'=>count($checks),'attention'=>count($checks)-$pass,'stale'=>$stale,'source_repairs'=>(int)sk1383_setting('full_project_audit_source_repairs','0')];
}
sk1383_boot_runtime_compat();
}
