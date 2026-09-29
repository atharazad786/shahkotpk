<?php
declare(strict_types=1);
if(!function_exists('sk1312_db')){
function sk1312_db(): PDO { global $pdo,$db; if(isset($pdo)&&$pdo instanceof PDO)return $pdo;if(isset($db)&&$db instanceof PDO)return $db;if(function_exists('db')){$x=db();if($x instanceof PDO)return $x;}throw new RuntimeException('Database connection unavailable'); }
function sk1312_driver(): string { try{return (string)sk1312_db()->getAttribute(PDO::ATTR_DRIVER_NAME);}catch(Throwable $e){return '';} }
function sk1312_table(string $t): bool { if(!preg_match('/^[A-Za-z0-9_]+$/',$t))return false;try{$d=sk1312_driver();if($d==='mysql'){$q=sk1312_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$t]);return (bool)$q->fetchColumn();}$q=sk1312_db()->query("SELECT 1 FROM `".$t."` LIMIT 1");return (bool)$q;}catch(Throwable $e){return false;} }
function sk1312_setting(string $k,string $default=''): string { if(function_exists('setting')){try{$v=setting($k);if($v!==null&&$v!=='')return (string)$v;}catch(Throwable $e){}}if(!sk1312_table('settings'))return $default;try{$q=sk1312_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$k]);$v=$q->fetchColumn();return $v===false?$default:(string)$v;}catch(Throwable $e){return $default;} }
function sk1312_tid(): int { if(function_exists('current_tenant_id')){try{return (int)current_tenant_id();}catch(Throwable $e){}}return isset($_SESSION['tenant_id'])?(int)$_SESSION['tenant_id']:0; }
function sk1312_actor_id(): ?int { if(function_exists('current_user')){try{$u=current_user();if(is_array($u)&&isset($u['id']))return (int)$u['id'];if(is_object($u)&&isset($u->id))return (int)$u->id;}catch(Throwable $e){}}return isset($_SESSION['user_id'])?(int)$_SESSION['user_id']:null; }
function sk1312_root(): string { return dirname(__DIR__); }
function sk1312_route_path(string $file): string { $root=rtrim(str_replace('\\','/',sk1312_root()),'/');$p=str_replace('\\','/',$file);if(strpos($p,$root)===0)$p=substr($p,strlen($root));$p='/'.ltrim($p,'/');return $p==='/index.php'?'/':$p; }
function sk1312_source_excerpt(string $file,int $limit=262144): string { if(!is_file($file)||!is_readable($file))return ''; $fh=@fopen($file,'rb');if(!$fh)return ''; $s=(string)fread($fh,$limit);fclose($fh);return $s; }
function sk1312_meta_hint(string $src,string $kind): string {
    $patterns=[
      'title'=>'~<title[^>]*>(.*?)</title>~is',
      'description'=>'~<meta[^>]+name=[\"\']description[\"\'][^>]+content=[\"\']([^\"\']*)[\"\']|<meta[^>]+content=[\"\']([^\"\']*)[\"\'][^>]+name=[\"\']description[\"\']~is',
      'canonical'=>'~<link[^>]+rel=[\"\']canonical[\"\'][^>]+href=[\"\']([^\"\']+)[\"\']|<link[^>]+href=[\"\']([^\"\']+)[\"\'][^>]+rel=[\"\']canonical[\"\']~is',
      'og_title'=>'~<meta[^>]+property=[\"\']og:title[\"\'][^>]+content=[\"\']([^\"\']*)[\"\']|<meta[^>]+content=[\"\']([^\"\']*)[\"\'][^>]+property=[\"\']og:title[\"\']~is'
    ];
    if(!isset($patterns[$kind]))return '';if(!preg_match($patterns[$kind],$src,$m))return '';for($i=1;$i<count($m);$i++){if(isset($m[$i])&&trim((string)$m[$i])!=='')return trim(strip_tags((string)$m[$i]));}return '';
}
function sk1312_has_dynamic_meta(string $src): bool { return (bool)preg_match('/page_start\s*\(|seo_|meta_|canonical|og:title|set_title|page_title/i',$src); }
function sk1312_public_files(): array {
    $root=sk1312_root();$files=[];
    foreach(glob($root.'/*.php')?:[] as $f){$bn=basename($f);if($bn==='installer.php'||$bn==='update.php')continue;$files[$f]=true;}
    foreach(['pages','public'] as $dir){$base=$root.'/'.$dir;if(!is_dir($base))continue;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS));foreach($it as $fi){if(!$fi->isFile()||strtolower($fi->getExtension())!=='php')continue;$files[$fi->getPathname()]=true;if(count($files)>=260)break 2;}}
    $out=array_keys($files);sort($out,SORT_NATURAL|SORT_FLAG_CASE);if(count($out)>260)$out=array_slice($out,0,260);return $out;
}
function sk1312_known_routes(): array {
    // Only invariant public entrypoints belong here. Optional modules are audited only when actually referenced.
    return [
      '/'=>'index.php','/businesses.php'=>'businesses.php','/marketplace.php'=>'marketplace.php','/events.php'=>'events.php','/jobs.php'=>'jobs.php','/property.php'=>'property.php','/news.php'=>'news.php','/services.php'=>'services.php'
    ];
}
function sk1312_route_registry_hints(): array {
    $root=sk1312_root();$paths=[];
    foreach([$root.'/app/access_control_v1300.php',$root.'/app/core_sync_v1301.php',$root.'/app/layout.php'] as $f){
        if(!is_file($f))continue;$src=sk1312_source_excerpt($f,524288);if($src==='')continue;
        if(preg_match_all('~["\'](/(?:[a-z0-9][a-z0-9._-]*/)*[a-z0-9._-]+\.php)["\']~i',$src,$m)){
            foreach($m[1] as $p){
                if(strpos($p,'/admin/')===0||strpos($p,'/api/')===0)continue;
                $bn=basename($p);
                // A quoted /helper_vNN.php can be an include basename, not a web route. If the same basename
                // exists under app/includes/config, treat it as internal and do not report it as a missing URL.
                $internal=false;foreach(['app','includes','config'] as $d)if(is_file($root.'/'.$d.'/'.$bn)){$internal=true;break;}
                if($internal)continue;
                $paths[$p]=true;if(count($paths)>=180)break 2;
            }
        }
    }
    return array_keys($paths);
}
function sk1312_audit_meta(): array {
    $root=sk1312_root();$rows=[];$canonicalMap=[];$files=sk1312_public_files();
    foreach($files as $f){$src=sk1312_source_excerpt($f);$route=sk1312_route_path($f);$title=sk1312_meta_hint($src,'title');$desc=sk1312_meta_hint($src,'description');$can=sk1312_meta_hint($src,'canonical');$og=sk1312_meta_hint($src,'og_title');$dynamic=sk1312_has_dynamic_meta($src);$rows[]=['route'=>$route,'file'=>str_replace('\\','/',substr($f,strlen($root)+1)),'title'=>$title,'description'=>$desc,'canonical'=>$can,'og_title'=>$og,'dynamic_hint'=>$dynamic,'bytes'=>(int)@filesize($f)];if($can!=='')$canonicalMap[strtolower(rtrim($can,'/'))][]=$route;}
    $known=[];foreach(sk1312_known_routes() as $route=>$rel){$known[]=['route'=>$route,'file'=>$rel,'exists'=>is_file($root.'/'.$rel)];}
    $registry=[];foreach(sk1312_route_registry_hints() as $route)$registry[]=['route'=>$route,'exists'=>is_file($root.'/'.ltrim($route,'/'))];
    $dups=[];foreach($canonicalMap as $can=>$routes)if(count($routes)>1)$dups[]=['canonical'=>$can,'routes'=>$routes];
    return [
      'files'=>$rows,'known_routes'=>$known,'registry_routes'=>$registry,'duplicate_canonicals'=>$dups,
      'robots'=>['exists'=>is_file($root.'/robots.txt'),'bytes'=>is_file($root.'/robots.txt')?(int)filesize($root.'/robots.txt'):0,'has_sitemap'=>is_file($root.'/robots.txt')&&stripos((string)@file_get_contents($root.'/robots.txt'),'sitemap:')!==false],
      'sitemap'=>['exists'=>is_file($root.'/sitemap.xml')||is_file($root.'/sitemap.php'),'type'=>is_file($root.'/sitemap.xml')?'xml':(is_file($root.'/sitemap.php')?'dynamic':'missing'),'path'=>is_file($root.'/sitemap.xml')?'sitemap.xml':(is_file($root.'/sitemap.php')?'sitemap.php':''),'bytes'=>is_file($root.'/sitemap.xml')?(int)filesize($root.'/sitemap.xml'):(is_file($root.'/sitemap.php')?(int)filesize($root.'/sitemap.php'):0)],
      'installed_version'=>sk1312_setting('installed_app_version','')
    ];
}
function sk1312_findings(array $m): array {
    $f=[];$missingKnown=array_values(array_filter($m['known_routes'],fn($x)=>!$x['exists']));if($missingKnown)$f[]=['severity'=>'medium','message'=>count($missingKnown).' common public route file(s) are missing. Review only routes actually enabled in this installation.'];
    $missingReg=array_values(array_filter($m['registry_routes'],fn($x)=>!$x['exists']));if($missingReg)$f[]=['severity'=>'high','message'=>count($missingReg).' public PHP route reference(s) found in core route/layout source do not have a physical target file.'];
    if(!$m['robots']['exists'])$f[]=['severity'=>'medium','message'=>'robots.txt is not present at the application root.'];elseif(!$m['robots']['has_sitemap'])$f[]=['severity'=>'low','message'=>'robots.txt exists but no Sitemap: directive was detected.'];
    if(!$m['sitemap']['exists'])$f[]=['severity'=>'medium','message'=>'sitemap.xml is not present at the application root.'];
    if($m['duplicate_canonicals'])$f[]=['severity'=>'medium','message'=>count($m['duplicate_canonicals']).' duplicate canonical target group(s) were detected from static source hints.'];
    $noTitle=0;$noDesc=0;$noCan=0;$noOg=0;foreach($m['files'] as $x){if($x['dynamic_hint'])continue;if($x['title']==='')$noTitle++;if($x['description']==='')$noDesc++;if($x['canonical']==='')$noCan++;if($x['og_title']==='')$noOg++;}
    if($noTitle)$f[]=['severity'=>'low','message'=>$noTitle.' public PHP source file(s) have no static title hint. Dynamic templates may still provide one at runtime.'];
    if($noDesc)$f[]=['severity'=>'low','message'=>$noDesc.' public PHP source file(s) have no static meta-description hint.'];
    if($noCan)$f[]=['severity'=>'low','message'=>$noCan.' public PHP source file(s) have no static canonical hint.'];
    if($noOg)$f[]=['severity'=>'low','message'=>$noOg.' public PHP source file(s) have no static og:title hint.'];
    $iv=(string)$m['installed_version'];if($iv!==''&&version_compare($iv,'13.0.12.0','<'))$f[]=['severity'=>'medium','message'=>'installed_app_version reports '.$iv.' while Step 12 expects 13.0.12.0 after migration.'];
    return $f;
}
function sk1312_score(array $findings): int { $s=100;foreach($findings as $x)$s-=($x['severity']==='high'?15:($x['severity']==='medium'?7:2));return max(0,min(100,$s)); }
function sk1312_save_run(array $r): int { if(!sk1312_table('public_seo_routing_runs_v1312'))return 0;try{$h=0;$m=0;foreach($r['findings'] as $x){if($x['severity']==='high')$h++;elseif($x['severity']==='medium')$m++;}$q=sk1312_db()->prepare('INSERT INTO public_seo_routing_runs_v1312(tenant_id,actor_user_id,score,high_count,medium_count,summary_json,created_at) VALUES(?,?,?,?,?,?,NOW())');$q->execute([sk1312_tid(),sk1312_actor_id(),$r['score'],$h,$m,json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);return (int)sk1312_db()->lastInsertId();}catch(Throwable $e){return 0;} }
function sk1312_fresh_audit(): array { $meta=sk1312_audit_meta();$find=sk1312_findings($meta);$r=['version'=>'13.0.12.0','tenant_id'=>sk1312_tid(),'created_at'=>date('c'),'score'=>sk1312_score($find),'meta'=>$meta,'findings'=>$find];$r['run_id']=sk1312_save_run($r);return $r; }
function sk1312_last_audit(): ?array { if(!sk1312_table('public_seo_routing_runs_v1312'))return null;try{$q=sk1312_db()->prepare('SELECT summary_json FROM public_seo_routing_runs_v1312 WHERE tenant_id=? ORDER BY id DESC LIMIT 1');$q->execute([sk1312_tid()]);$j=$q->fetchColumn();if(!$j)return null;$r=json_decode((string)$j,true);if(!is_array($r))return null;$saved=(string)($r['meta']['installed_version']??'');$current=sk1312_setting('installed_app_version','');if($current!==''&&$saved!==''&&$saved!==$current)return null;return $r;}catch(Throwable $e){return null;} }
function sk1312_log_action(string $key,array $r): void { if(!sk1312_table('public_seo_routing_actions_v1312'))return;try{$q=sk1312_db()->prepare('INSERT INTO public_seo_routing_actions_v1312(tenant_id,actor_user_id,action_key,result_json,created_at) VALUES(?,?,?,?,NOW())');$q->execute([sk1312_tid(),sk1312_actor_id(),$key,json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);}catch(Throwable $e){} }
function sk1312_safe_metadata_refresh(): array { $r=['changed'=>[],'errors'=>[],'routes_rewritten'=>false,'menu_touched'=>false,'content_touched'=>false];if(sk1312_table('settings')){try{$now=date('Y-m-d H:i:s');$cache='13120-'.time();$q=sk1312_db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('step12_public_seo_routing_version','13.0.12.0'),('public_seo_audit_cache_version',?),('public_seo_last_safe_refresh_at',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$q->execute([$cache,$now]);$r['changed']=['Step-12 metadata','SEO audit cache version'];}catch(Throwable $e){$r['errors'][]=$e->getMessage();}}sk1312_log_action('safe_metadata_refresh',$r);return $r; }
}
