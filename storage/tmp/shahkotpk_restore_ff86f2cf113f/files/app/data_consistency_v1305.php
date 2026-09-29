<?php
declare(strict_types=1);
/** ShahkotPK v13.0.5.0 — Admin CRUD ↔ Public Data Consistency audit. */
if (!function_exists('sk1305_scan')) {
function sk1305_db(): PDO { return function_exists('sk1301_db') ? sk1301_db() : db(); }
function sk1305_tid(): int { return function_exists('sk1301_tid') ? sk1301_tid() : (function_exists('tenant_id') ? (int)tenant_id() : 0); }
function sk1305_table(string $table): bool {
    if (function_exists('sk1301_table')) return sk1301_table($table);
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $table)) return false;
    try { $q=sk1305_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1'); $q->execute([$table]); return (bool)$q->fetchColumn(); }
    catch (Throwable $e) { return false; }
}
function sk1305_cols(string $table): array {
    if (function_exists('sk1301_cols')) return sk1301_cols($table);
    $out=[]; try { $q=sk1305_db()->prepare('SELECT COLUMN_NAME,DATA_TYPE,IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'); $q->execute([$table]); foreach($q->fetchAll()?:[] as $r)$out[(string)$r['COLUMN_NAME']]=$r; } catch(Throwable $e){} return $out;
}
function sk1305_ident(string $name): string { if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name)) throw new InvalidArgumentException('Unsafe SQL identifier'); return '`'.$name.'`'; }
function sk1305_first(array $cols,array $names): string { foreach($names as $n) if(isset($cols[$n])) return $n; return ''; }
function sk1305_modules(): array { return [
    ['key'=>'businesses','label'=>'Businesses','tables'=>['businesses'],'title'=>['name','business_name','title'],'slug'=>['slug'],'media'=>['logo','logo_url','image','image_url','cover_image','featured_image'],'category_tables'=>['business_categories','categories']],
    ['key'=>'events','label'=>'Events','tables'=>['events'],'title'=>['title','name'],'slug'=>['slug'],'media'=>['image','image_url','cover_image','featured_image','banner'],'category_tables'=>['event_categories','categories']],
    ['key'=>'jobs','label'=>'Jobs','tables'=>['jobs','job_listings'],'title'=>['title','job_title','name'],'slug'=>['slug'],'media'=>['image','image_url','company_logo','logo'],'category_tables'=>['job_categories','categories']],
    ['key'=>'properties','label'=>'Property','tables'=>['properties','property_listings'],'title'=>['title','name'],'slug'=>['slug'],'media'=>['image','image_url','cover_image','featured_image'],'category_tables'=>['property_categories','categories']],
    ['key'=>'marketplace','label'=>'Marketplace','tables'=>['marketplace_listings','marketplace_items','listings'],'title'=>['title','name'],'slug'=>['slug'],'media'=>['image','image_url','cover_image','featured_image','thumbnail'],'category_tables'=>['marketplace_categories','categories']],
    ['key'=>'news','label'=>'News / Updates','tables'=>['news','news_posts','posts'],'title'=>['title','name'],'slug'=>['slug'],'media'=>['image','image_url','cover_image','featured_image','thumbnail'],'category_tables'=>['news_categories','categories']],
    ['key'=>'doctors','label'=>'Doctors','tables'=>['doctors'],'title'=>['name','doctor_name','title'],'slug'=>['slug'],'media'=>['photo','photo_url','image','image_url','avatar'],'category_tables'=>['doctor_categories','categories']],
    ['key'=>'blood','label'=>'Blood Donors','tables'=>['blood_donors','donors'],'title'=>['name','donor_name','title'],'slug'=>['slug'],'media'=>['photo','photo_url','image','image_url','avatar'],'category_tables'=>[]],
    ['key'=>'ads','label'=>'Advertisements','tables'=>['ads','advertisements','self_service_ad_campaigns'],'title'=>['title','name','campaign_name'],'slug'=>['slug'],'media'=>['image','image_url','banner','creative_url'],'category_tables'=>[]],
    ['key'=>'deals','label'=>'Deals','tables'=>['deals','offers'],'title'=>['title','name'],'slug'=>['slug'],'media'=>['image','image_url','banner','featured_image'],'category_tables'=>['deal_categories','categories']],
    ['key'=>'products','label'=>'Products','tables'=>['products'],'title'=>['name','title','product_name'],'slug'=>['slug'],'media'=>['image','image_url','thumbnail','featured_image'],'category_tables'=>['product_categories','categories']],
    ['key'=>'services','label'=>'Services','tables'=>['services'],'title'=>['name','title','service_name'],'slug'=>['slug'],'media'=>['image','image_url','thumbnail','icon'],'category_tables'=>['service_categories','categories']],
]; }
function sk1305_existing_table(array $names): string { foreach($names as $t) if(sk1305_table($t)) return $t; return ''; }
function sk1305_scope(array $cols,array &$params): string { $tid=sk1305_tid(); if($tid>0 && isset($cols['tenant_id'])) { $params[]=$tid; return sk1305_ident('tenant_id').'=?'; } return '1=1'; }
function sk1305_visibility(array $cols,array &$params): array {
    $status=sk1305_first($cols,['status','publication_status','state']);
    if($status!=='') return ['sql'=>'LOWER(COALESCE('.sk1305_ident($status).",'')) IN ('active','approved','published','live','enabled','public')",'field'=>$status,'mode'=>'status'];
    foreach(['is_published','published','is_approved','approved','is_active','active','enabled','is_enabled','visible','is_visible'] as $f) if(isset($cols[$f])) return ['sql'=>'COALESCE('.sk1305_ident($f).',0)=1','field'=>$f,'mode'=>'boolean'];
    return ['sql'=>'1=1','field'=>'','mode'=>'all'];
}
function sk1305_count(string $table,string $where,array $params=[]): int { try{$q=sk1305_db()->prepare('SELECT COUNT(*) FROM '.sk1305_ident($table).' WHERE '.$where);$q->execute($params);return (int)$q->fetchColumn();}catch(Throwable $e){return 0;} }
function sk1305_duplicate_count(string $table,string $field,string $where,array $params=[]): int {
    if($field==='')return 0; try{$id=sk1305_ident($field);$sql='SELECT COUNT(*) FROM (SELECT '.$id.' FROM '.sk1305_ident($table).' WHERE '.$where.' AND TRIM(COALESCE('.$id.",''))<>'' GROUP BY ".$id.' HAVING COUNT(*)>1) d';$q=sk1305_db()->prepare($sql);$q->execute($params);return (int)$q->fetchColumn();}catch(Throwable $e){return 0;}
}
function sk1305_orphan_count(string $table,array $cols,string $where,array $params,array $module): array {
    $result=['user'=>0,'category'=>0];
    $userField=sk1305_first($cols,['user_id','owner_id','created_by','seller_id','vendor_id']);
    if($userField!=='' && sk1305_table('users')) { try{$f=sk1305_ident($userField);$sql='SELECT COUNT(*) FROM '.sk1305_ident($table).' x LEFT JOIN `users` u ON u.id=x.'.$f.' WHERE '.$where.' AND x.'.$f.' IS NOT NULL AND x.'.$f.'<>0 AND u.id IS NULL';$q=sk1305_db()->prepare($sql);$q->execute($params);$result['user']=(int)$q->fetchColumn();}catch(Throwable $e){} }
    if(isset($cols['category_id'])) { $cat=sk1305_existing_table($module['category_tables']??[]); if($cat!==''){ try{$sql='SELECT COUNT(*) FROM '.sk1305_ident($table).' x LEFT JOIN '.sk1305_ident($cat).' c ON c.id=x.`category_id` WHERE '.$where.' AND x.`category_id` IS NOT NULL AND x.`category_id`<>0 AND c.id IS NULL';$q=sk1305_db()->prepare($sql);$q->execute($params);$result['category']=(int)$q->fetchColumn();}catch(Throwable $e){} } }
    return $result;
}
function sk1305_module_scan(array $module): ?array {
    $table=sk1305_existing_table($module['tables']); if($table==='')return null; $cols=sk1305_cols($table); $params=[];$scope=sk1305_scope($cols,$params);$vis=sk1305_visibility($cols,$params);$visibleWhere='('.$scope.') AND ('.$vis['sql'].')';
    $title=sk1305_first($cols,$module['title']);$slug=sk1305_first($cols,$module['slug']);$media=sk1305_first($cols,$module['media']);
    $total=sk1305_count($table,$scope,$params);$visible=sk1305_count($table,$visibleWhere,$params);$hidden=max(0,$total-$visible);
    $missingTitle=$title!==''?sk1305_count($table,$visibleWhere.' AND TRIM(COALESCE('.sk1305_ident($title).",''))=''",$params):0;
    $missingMedia=$media!==''?sk1305_count($table,$visibleWhere.' AND TRIM(COALESCE('.sk1305_ident($media).",''))=''",$params):0;
    $dupes=$slug!==''?sk1305_duplicate_count($table,$slug,$visibleWhere,$params):0;
    $orph=sk1305_orphan_count($table,$cols,$visibleWhere,$params,$module);
    $issues=[]; if($missingTitle)$issues[]=['severity'=>'high','type'=>'missing_title','count'=>$missingTitle,'message'=>$missingTitle.' public record(s) have no title/name.']; if($dupes)$issues[]=['severity'=>'high','type'=>'duplicate_slug','count'=>$dupes,'message'=>$dupes.' duplicate public slug group(s) can resolve to the same URL.']; if($orph['user'])$issues[]=['severity'=>'high','type'=>'orphan_user','count'=>$orph['user'],'message'=>$orph['user'].' public record(s) reference a missing user/owner.']; if($orph['category'])$issues[]=['severity'=>'medium','type'=>'orphan_category','count'=>$orph['category'],'message'=>$orph['category'].' public record(s) reference a missing category.']; if($missingMedia)$issues[]=['severity'=>'low','type'=>'missing_media','count'=>$missingMedia,'message'=>$missingMedia.' public record(s) have no primary media value.'];
    $penalty=$missingTitle*4+$dupes*8+$orph['user']*6+$orph['category']*3+min($missingMedia,10);$score=max(0,100-min(100,$penalty));
    return ['key'=>$module['key'],'label'=>$module['label'],'table'=>$table,'total'=>$total,'visible'=>$visible,'hidden'=>$hidden,'title_field'=>$title,'slug_field'=>$slug,'media_field'=>$media,'visibility_field'=>$vis['field'],'visibility_mode'=>$vis['mode'],'missing_title'=>$missingTitle,'missing_media'=>$missingMedia,'duplicate_slugs'=>$dupes,'orphan_user'=>$orph['user'],'orphan_category'=>$orph['category'],'score'=>$score,'issues'=>$issues];
}
function sk1305_scan(): array {
    $modules=[];$issues=[];$totals=['records'=>0,'public'=>0,'hidden'=>0,'missing_title'=>0,'missing_media'=>0,'duplicate_slugs'=>0,'orphan_user'=>0,'orphan_category'=>0];
    foreach(sk1305_modules() as $m){$r=sk1305_module_scan($m);if(!$r)continue;$modules[]=$r;foreach($r['issues'] as $i){$i['module']=$r['label'];$i['table']=$r['table'];$issues[]=$i;} $totals['records']+=$r['total'];$totals['public']+=$r['visible'];$totals['hidden']+=$r['hidden'];foreach(['missing_title','missing_media','duplicate_slugs','orphan_user','orphan_category'] as $k)$totals[$k]+=$r[$k];}
    $high=count(array_filter($issues,static fn($x)=>$x['severity']==='high'));$medium=count(array_filter($issues,static fn($x)=>$x['severity']==='medium'));$low=count(array_filter($issues,static fn($x)=>$x['severity']==='low'));$score=max(0,100-($high*10+$medium*5+$low*2));
    usort($issues,static function($a,$b){$w=['high'=>0,'medium'=>1,'low'=>2];return [$w[$a['severity']]??9,$a['module'],$a['type']]<=>[$w[$b['severity']]??9,$b['module'],$b['type']];});
    return ['version'=>'13.0.5.0','score'=>$score,'modules'=>$modules,'totals'=>$totals,'issues'=>$issues,'high'=>$high,'medium'=>$medium,'low'=>$low];
}
function sk1305_refresh_public_runtime(): array {
    $r=['settings'=>0,'cache_rows'=>0,'logged'=>0,'errors'=>[]]; $pdo=sk1305_db(); $stamp=gmdate('Y-m-d H:i:s').' UTC';
    try { if(sk1305_table('settings')) { $st=$pdo->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('public_data_cache_version',?),('public_data_cache_bust',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$st->execute(['13.0.5.0',$stamp]);$r['settings']=$st->rowCount(); } } catch(Throwable $e){$r['errors'][]='Settings: '.$e->getMessage();}
    try { if(sk1305_table('ai_recommendation_cache')) { $c=sk1305_cols('ai_recommendation_cache'); if(isset($c['tenant_id']) && sk1305_tid()>0){$st=$pdo->prepare('DELETE FROM `ai_recommendation_cache` WHERE `tenant_id`=?');$st->execute([sk1305_tid()]);$r['cache_rows']=$st->rowCount();} } } catch(Throwable $e){$r['errors'][]='Cache: '.$e->getMessage();}
    try { if(sk1305_table('data_consistency_runs_v1305')) { $scan=sk1305_scan(); $st=$pdo->prepare('INSERT INTO data_consistency_runs_v1305(tenant_id,actor_user_id,score,summary_json,created_at) VALUES(?,?,?,?,NOW())');$u=function_exists('current_user')?(current_user()?:[]):[];$st->execute([sk1305_tid(),isset($u['id'])?(int)$u['id']:null,(int)$scan['score'],json_encode(['totals'=>$scan['totals'],'issues'=>count($scan['issues'])],JSON_UNESCAPED_SLASHES)]);$r['logged']=1; } } catch(Throwable $e){$r['errors'][]='Audit log: '.$e->getMessage();}
    return $r;
}
}
