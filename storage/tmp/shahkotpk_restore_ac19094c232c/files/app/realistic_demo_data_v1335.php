<?php
declare(strict_types=1);
/** ShahkotPK v13.3.6 — schema-adaptive verified public-map demo data seeder. */
if(!function_exists('sk1335rd_preview')){
function sk1335rd_db(): PDO { if(!function_exists('db')) throw new RuntimeException('Database unavailable.'); return db(); }
function sk1335rd_ident(string $s): string { return preg_match('/^[A-Za-z0-9_]+$/',$s)?'`'.$s.'`':''; }
function sk1335rd_table(string $t): bool { static $c=[]; if(isset($c[$t]))return $c[$t]; try{$q=sk1335rd_db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$t]);return $c[$t]=(bool)$q->fetchColumn();}catch(Throwable $e){return $c[$t]=false;} }
function sk1335rd_cols(string $t): array { try{$q=sk1335rd_db()->prepare('SELECT COLUMN_NAME,IS_NULLABLE,COLUMN_DEFAULT,DATA_TYPE,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');$q->execute([$t]);$o=[];foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r)$o[(string)$r['COLUMN_NAME']]=$r;return $o;}catch(Throwable $e){return [];} }
function sk1335rd_tenant(): ?int { foreach(['sk1300_current_tenant_id','current_tenant_id','tenant_id'] as $fn){try{if(function_exists($fn)){$v=$fn();if(is_numeric($v)&&(int)$v>0)return (int)$v;}}catch(Throwable $e){}} if(session_status()===PHP_SESSION_ACTIVE){foreach(['tenant_id','current_tenant_id'] as $k){$v=$_SESSION[$k]??null;if(is_numeric($v)&&(int)$v>0)return (int)$v;}} return null; }
function sk1335rd_dataset(): array { static $d=null;if(is_array($d))return $d;$f=dirname(__DIR__).'/data/shahkot-verified-map-v1336.json';if(!is_file($f))throw new RuntimeException('Research dataset file missing.');$d=json_decode((string)file_get_contents($f),true);if(!is_array($d)||!isset($d['records']))throw new RuntimeException('Research dataset is invalid.');return $d; }
function sk1335rd_first(array $cols,array $names): string { foreach($names as $n)if(isset($cols[$n]))return $n;return ''; }
function sk1335rd_primary_table(): array {
  $candidates=['businesses','directory_listings','business_listings','places','shops','services'];$best=['table'=>'','score'=>-1,'cols'=>[]];
  foreach($candidates as $t){if(!sk1335rd_table($t))continue;$c=sk1335rd_cols($t);$score=0;
    if(sk1335rd_first($c,['name','business_name','title','listing_title']))$score+=6;
    if(sk1335rd_first($c,['latitude','lat','map_lat','geo_lat','location_lat']))$score+=2;
    if(sk1335rd_first($c,['longitude','lng','lon','long','map_lng','geo_lng','location_lng']))$score+=2;
    if(sk1335rd_first($c,['address','location','address_line','street_address']))$score+=2;
    if(isset($c['tenant_id']))$score+=1;
    $requiredUnmapped=0;foreach($c as $n=>$m){$auto=str_contains(strtolower((string)($m['EXTRA']??'')),'auto_increment');if($auto)continue;if(($m['IS_NULLABLE']??'YES')==='NO'&&$m['COLUMN_DEFAULT']===null&&!in_array($n,['id'],true)){
      $known=['name','business_name','title','listing_title','slug','description','short_description','about','details','address','location','address_line','street_address','city','district','province','state','country','postal_code','zip','latitude','lat','map_lat','geo_lat','location_lat','longitude','lng','lon','long','map_lng','geo_lng','location_lng','status','is_active','active','published','is_published','verified','is_verified','tenant_id','created_at','updated_at','category','category_name','type','business_type','source','data_source','import_source','seed_source','is_demo','is_dummy'];
      if(!in_array($n,$known,true))$requiredUnmapped++;
    }}
    $score-=$requiredUnmapped*5;if($score>$best['score'])$best=['table'=>$t,'score'=>$score,'cols'=>$c,'required_unmapped'=>$requiredUnmapped];
  }
  return $best;
}
function sk1335rd_slug(string $s): string {$s=strtolower(trim($s));$s=preg_replace('/[^a-z0-9]+/','-',$s)??'';$s=trim($s,'-');return substr($s?:'shahkot-place',0,180);}
function sk1335rd_demo_where(string $table,array $cols,?int $tenant): array {
  $parts=[];$params=[];
  foreach(['is_demo','is_dummy','demo_data','sample_data'] as $c)if(isset($cols[$c]))$parts[]='`'.$c.'`=1';
  foreach(['source','data_source','import_source','seed_source'] as $c)if(isset($cols[$c])){$parts[]="LOWER(CAST(`$c` AS CHAR)) IN ('dummy','demo','sample','dummy_data','sample_data','shahkotpk_dummy','v52_dummy','research_demo_v1335','verified_osm_v1336')";}
  $slug=sk1335rd_first($cols,['slug']);if($slug)$parts[]="LOWER(CAST(`$slug` AS CHAR)) REGEXP '^(dummy|demo|sample)-'";
  $name=sk1335rd_first($cols,['name','business_name','title','listing_title']);if($name)$parts[]="LOWER(TRIM(CAST(`$name` AS CHAR))) REGEXP '^(demo|dummy|sample|test) (business|shop|listing|place)( [0-9]+)?$'";
  if(!$parts)return ['sql'=>'0=1','params'=>[]];$sql='('.implode(' OR ',$parts).')';
  if(isset($cols['tenant_id'])){if($tenant===null)return ['sql'=>'0=1','params'=>[]];$sql='`tenant_id`=? AND '.$sql;$params[]=$tenant;}
  return ['sql'=>$sql,'params'=>$params];
}
function sk1335rd_preview(): array {
  $d=sk1335rd_dataset();$p=sk1335rd_primary_table();$tenant=sk1335rd_tenant();$dummy=0;
  if($p['table']!==''){$w=sk1335rd_demo_where($p['table'],$p['cols'],$tenant);try{$q=sk1335rd_db()->prepare('SELECT COUNT(*) FROM '.sk1335rd_ident($p['table']).' WHERE '.$w['sql']);$q->execute($w['params']);$dummy=(int)$q->fetchColumn();}catch(Throwable $e){}}
  return ['dataset_count'=>count($d['records']??[]),'target_table'=>$p['table'],'compatibility_score'=>$p['score'],'required_unmapped'=>$p['required_unmapped']??0,'detectable_dummy_rows'=>$dummy,'tenant_id'=>$tenant,'safe_to_apply'=>$p['table']!==''&&($p['score']??-1)>=6];
}
function sk1335rd_map_row(array $r,array $cols,?int $tenant): array {
  $v=[];$put=function(array $names,$val)use(&$v,$cols){foreach($names as $n)if(isset($cols[$n])){$v[$n]=$val;return;}};
  $put(['name','business_name','title','listing_title'],$r['name']??'');$put(['slug'],sk1335rd_slug((string)($r['name']??'')));
  $put(['description','about','details'],(string)($r['description']??''));$put(['short_description'],mb_substr((string)($r['description']??''),0,250));
  $put(['address','location','address_line','street_address'],(string)($r['address']??''));$put(['city'],(string)($r['city']??'Shahkot'));$put(['district'],(string)($r['district']??'Nankana Sahib'));$put(['province','state'],(string)($r['province']??'Punjab'));$put(['country'],(string)($r['country']??'Pakistan'));$put(['postal_code','zip'],(string)($r['postal_code']??'39630'));
  $put(['latitude','lat','map_lat','geo_lat','location_lat'],(string)($r['latitude']??''));$put(['longitude','lng','lon','long','map_lng','geo_lng','location_lng'],(string)($r['longitude']??''));
  $put(['category','category_name','type','business_type'],(string)($r['category']??'Local Business'));
  foreach(['status'] as $n)if(isset($cols[$n]))$v[$n]='published';foreach(['is_active','active','published','is_published'] as $n)if(isset($cols[$n]))$v[$n]=1;foreach(['verified','is_verified'] as $n)if(isset($cols[$n]))$v[$n]=0;
  foreach(['source','data_source','import_source','seed_source'] as $n)if(isset($cols[$n]))$v[$n]='verified_osm_v1336';foreach(['is_demo','is_dummy'] as $n)if(isset($cols[$n]))$v[$n]=1;
  if(isset($cols['tenant_id'])&&$tenant!==null)$v['tenant_id']=$tenant;if(isset($cols['created_at']))$v['created_at']=date('Y-m-d H:i:s');if(isset($cols['updated_at']))$v['updated_at']=date('Y-m-d H:i:s');
  return $v;
}
function sk1335rd_apply(): array {
  $pre=sk1335rd_preview();if(!$pre['safe_to_apply'])throw new RuntimeException('No compatible business/directory table was found. Nothing was changed.');
  $p=sk1335rd_primary_table();$table=$p['table'];$cols=$p['cols'];$tenant=sk1335rd_tenant();if(isset($cols['tenant_id'])&&$tenant===null)throw new RuntimeException('Tenant context could not be resolved for the selected table. Nothing was changed.');
  $pdo=sk1335rd_db();$d=sk1335rd_dataset();$inserted=0;$deleted=0;$skipped=0;$pdo->beginTransaction();
  try{
    $w=sk1335rd_demo_where($table,$cols,$tenant);if($w['sql']!=='0=1'){$q=$pdo->prepare('DELETE FROM '.sk1335rd_ident($table).' WHERE '.$w['sql']);$q->execute($w['params']);$deleted=$q->rowCount();}
    foreach(($d['records']??[]) as $r){$vals=sk1335rd_map_row($r,$cols,$tenant);if(!$vals){$skipped++;continue;}
      // If required columns cannot be supplied, skip this record rather than fabricating values.
      $bad=false;foreach($cols as $n=>$m){$auto=str_contains(strtolower((string)($m['EXTRA']??'')),'auto_increment');if($auto||$n==='id'||array_key_exists($n,$vals)||($m['IS_NULLABLE']??'YES')==='YES'||$m['COLUMN_DEFAULT']!==null)continue;$bad=true;break;}if($bad){$skipped++;continue;}
      $names=array_keys($vals);$sql='INSERT INTO '.sk1335rd_ident($table).' ('.implode(',',array_map('sk1335rd_ident',$names)).') VALUES ('.implode(',',array_fill(0,count($names),'?')).')';
      try{$q=$pdo->prepare($sql);$q->execute(array_values($vals));$inserted++;}catch(Throwable $e){$skipped++;}
    }
    if(sk1335rd_table('settings')){$q=$pdo->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('research_demo_dataset_status',?),('research_demo_dataset_applied_at',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$q->execute(['applied:'.$inserted,date('c')]);}
    $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  return ['target_table'=>$table,'deleted_dummy_rows'=>$deleted,'inserted'=>$inserted,'skipped'=>$skipped,'dataset_total'=>count($d['records']??[])];
}
}
