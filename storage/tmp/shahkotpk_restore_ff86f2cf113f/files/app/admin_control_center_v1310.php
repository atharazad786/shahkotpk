<?php
declare(strict_types=1);
/** ShahkotPK v13.1.0 — Unified Admin Control Center (read-only snapshot + module directory). */
if (!function_exists('sk1310_snapshot')) {
function sk1310_db(): PDO {
    if (function_exists('sk1308_db')) return sk1308_db();
    if (function_exists('sk1301_db')) return sk1301_db();
    return db();
}
function sk1310_tid(): int {
    if (function_exists('sk1308_tid')) return sk1308_tid();
    if (function_exists('sk1301_tid')) return sk1301_tid();
    return function_exists('tenant_id') ? (int)tenant_id() : 0;
}
function sk1310_ident(string $name): string {
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $name)) throw new InvalidArgumentException('Unsafe SQL identifier');
    return '`'.$name.'`';
}
function sk1310_table(string $table): bool {
    static $cache=[];
    if (array_key_exists($table,$cache)) return $cache[$table];
    try {
        $q=sk1310_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$table]);
        return $cache[$table]=(bool)$q->fetchColumn();
    } catch (Throwable $e) { return $cache[$table]=false; }
}
function sk1310_cols(string $table): array {
    static $cache=[];
    if (isset($cache[$table])) return $cache[$table];
    $out=[];
    try {
        $q=sk1310_db()->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $q->execute([$table]);
        foreach ($q->fetchAll()?:[] as $r) $out[(string)$r['COLUMN_NAME']]=true;
    } catch (Throwable $e) {}
    return $cache[$table]=$out;
}
function sk1310_setting(string $key,string $default=''): string {
    if (!sk1310_table('settings')) return $default;
    try {
        $q=sk1310_db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');
        $q->execute([$key]);
        $v=$q->fetchColumn();
        return $v===false?$default:(string)$v;
    } catch (Throwable $e) { return $default; }
}
function sk1310_latest(string $table): array {
    if (!sk1310_table($table)) return ['exists'=>false,'score'=>null,'created_at'=>'','id'=>0];
    $cols=sk1310_cols($table);$where='';$params=[];$tid=sk1310_tid();
    if ($tid>0 && isset($cols['tenant_id'])) {$where=' WHERE tenant_id=?';$params[]=$tid;}
    $select=['id'];
    if (isset($cols['score'])) $select[]='score';
    if (isset($cols['created_at'])) $select[]='created_at';
    try {
        $sql='SELECT '.implode(',',$select).' FROM '.sk1310_ident($table).$where.' ORDER BY id DESC LIMIT 1';
        $q=sk1310_db()->prepare($sql);$q->execute($params);$r=$q->fetch(PDO::FETCH_ASSOC)?:[];
        return ['exists'=>true,'score'=>array_key_exists('score',$r)?(int)$r['score']:null,'created_at'=>(string)($r['created_at']??''),'id'=>(int)($r['id']??0)];
    } catch (Throwable $e) { return ['exists'=>true,'score'=>null,'created_at'=>'','id'=>0]; }
}
function sk1310_health_centers(): array {
    $defs=[
      ['key'=>'production','label'=>'Production Readiness','table'=>'production_hardening_runs_v1315','url'=>'/admin/production-hardening.php','target'=>90],
      ['key'=>'regression','label'=>'Full Regression','table'=>'regression_integrity_runs_v1314','url'=>'/admin/regression-integrity.php','target'=>90],
      ['key'=>'runtime','label'=>'Runtime','table'=>'runtime_integrity_runs_v1308','url'=>'/admin/runtime-integrity.php','target'=>85],
      ['key'=>'security','label'=>'Security','table'=>'security_integrity_runs_v1310','url'=>'/admin/security-integrity.php','target'=>85],
      ['key'=>'database','label'=>'Database','table'=>'database_integrity_runs_v1311','url'=>'/admin/database-integrity.php','target'=>85],
      ['key'=>'seo','label'=>'Public SEO','table'=>'public_seo_routing_runs_v1312','url'=>'/admin/public-seo-routing.php','target'=>80],
      ['key'=>'background','label'=>'Background Jobs','table'=>'background_integrity_runs_v1313','url'=>'/admin/background-integrity.php','target'=>85],
      ['key'=>'permissions','label'=>'Permissions','table'=>'permission_integrity_runs_v1309','url'=>'/admin/permissions-integrity.php','target'=>85],
      ['key'=>'media','label'=>'Media','table'=>'media_integrity_runs_v1307','url'=>'/admin/media-integrity.php','target'=>85],
      ['key'=>'data','label'=>'Data Consistency','table'=>'data_consistency_runs_v1305','url'=>'/admin/data-consistency.php','target'=>85],
    ];
    $out=[];
    foreach($defs as $d){$last=sk1310_latest($d['table']);$d=array_merge($d,$last);$score=$d['score'];$d['state']=$score===null?'not-run':($score>=$d['target']?'good':($score>=70?'review':'attention'));$out[]=$d;}
    return $out;
}
function sk1310_sidebar_modules(): array {
    $dir=__DIR__.'/sidebar-modules';$root=dirname(__DIR__);$rows=[];$seen=[];$files=0;
    if(!is_dir($dir)) return [];
    foreach(glob($dir.'/*.json')?:[] as $file){if(++$files>120)break;$j=json_decode((string)@file_get_contents($file),true);if(!is_array($j))continue;$items=(isset($j['items'])&&is_array($j['items']))?$j['items']:[$j];foreach($items as $it){if(!is_array($it)||empty($it['enabled']))continue;$url=trim((string)($it['url']??''));$label=trim((string)($it['label']??''));if($url===''||$label===''||!str_starts_with($url,'/admin/'))continue;if(isset($seen[$url]))continue;$seen[$url]=1;$path=parse_url($url,PHP_URL_PATH)?:$url;$physical=is_file($root.'/'.ltrim($path,'/'));$feature='';$allowed=true;try{if(function_exists('sk1300_path_feature')){$f=sk1300_path_feature($path,true);if(is_array($f)&&$f){$feature=(string)($f['feature_key']??'');if($feature!==''&&function_exists('sk1300_can'))$allowed=sk1300_can($feature,function_exists('current_user')?current_user():null);}}}catch(Throwable $e){}$rows[]=['label'=>$label,'url'=>$url,'category'=>(string)($it['category_label']??$it['category_key']??'Admin'),'icon'=>(string)($it['icon']??'•'),'physical'=>$physical,'feature'=>$feature,'allowed'=>$allowed,'sort'=>(int)($it['sort_order']??1000)];if(count($rows)>=240)break 2;}}
    usort($rows,static fn($a,$b)=>[$a['category'],$a['sort'],$a['label']]<=>[$b['category'],$b['sort'],$b['label']]);return $rows;
}
function sk1310_snapshot(): array {
    $centers=sk1310_health_centers();$mods=sk1310_sidebar_modules();$good=0;$review=0;$attention=0;$notRun=0;
    foreach($centers as $c){if($c['state']==='good')$good++;elseif($c['state']==='review')$review++;elseif($c['state']==='attention')$attention++;else$notRun++;}
    $missing=0;$blocked=0;$mapped=0;foreach($mods as $m){if(!$m['physical'])$missing++;if(!$m['allowed'])$blocked++;if($m['feature']!=='')$mapped++;}
    return [
      'version'=>'13.1.0','generated_at'=>gmdate('c'),'tenant_id'=>sk1310_tid(),
      'installed'=>sk1310_setting('installed_app_version','unknown'),
      'stable_baseline'=>sk1310_setting('stable_release_baseline',''),
      'stable_readiness'=>sk1310_setting('stable_release_readiness',''),
      'runtime_contract'=>sk1310_setting('runtime_cache_contract_version',''),
      'public_cache'=>sk1310_setting('public_data_cache_version',''),
      'centers'=>$centers,'modules'=>$mods,
      'summary'=>['good'=>$good,'review'=>$review,'attention'=>$attention,'not_run'=>$notRun,'modules'=>count($mods),'missing_routes'=>$missing,'blocked'=>$blocked,'mapped'=>$mapped],
      'read_only'=>true,
    ];
}
}
