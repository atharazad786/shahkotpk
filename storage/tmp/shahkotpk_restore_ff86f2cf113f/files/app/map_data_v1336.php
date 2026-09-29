<?php
declare(strict_types=1);
/** ShahkotPK v13.3.6 — verified Shahkot public-map overlay + bounded tenant-safe database coordinates. */
if(!function_exists('sk1336_map_data')){
function sk1336_md_ident(string $s): string { return preg_match('/^[A-Za-z0-9_]+$/',$s)?'`'.$s.'`':''; }
function sk1336_md_tenant_id(): ?int {
    foreach(['sk1300_current_tenant_id','current_tenant_id','tenant_id'] as $fn){try{if(function_exists($fn)){$v=$fn();if(is_numeric($v)&&(int)$v>0)return (int)$v;}}catch(Throwable $e){}}
    if(session_status()===PHP_SESSION_ACTIVE){foreach(['tenant_id','current_tenant_id'] as $k){$v=$_SESSION[$k]??null;if(is_numeric($v)&&(int)$v>0)return (int)$v;}}
    return null;
}
function sk1336_md_type(string $table): string {
    $t=strtolower($table); if(str_contains($t,'event'))return 'event'; if(str_contains($t,'propert')||str_contains($t,'estate'))return 'property';
    if(str_contains($t,'doctor')||str_contains($t,'clinic')||str_contains($t,'hospital'))return 'service'; if(str_contains($t,'job'))return 'job';
    if(str_contains($t,'service'))return 'service'; if(str_contains($t,'business')||str_contains($t,'shop')||str_contains($t,'restaurant')||str_contains($t,'vendor'))return 'business'; return 'place';
}
function sk1336_verified_records(): array {
    $f=dirname(__DIR__).'/data/shahkot-verified-map-v1336.json'; if(!is_file($f))return [];
    $d=json_decode((string)file_get_contents($f),true);$out=[];
    foreach((array)($d['records']??[]) as $i=>$r){$la=(float)($r['latitude']??0);$lo=(float)($r['longitude']??0);$lab=trim((string)($r['name']??''));if(!$lab||$la<-90||$la>90||$lo<-180||$lo>180)continue;
      $out[]=['id'=>'verified-'.$i,'label'=>$lab,'lat'=>$la,'lng'=>$lo,'type'=>(string)($r['map_type']??'business'),'category'=>(string)($r['category']??''),'url'=>(string)($r['source_url']??''),'verified'=>true,'source'=>'verified_public_map_v1336'];}
    return $out;
}
function sk1336_map_data(?PDO $pdo=null): array {
    $verified=sk1336_verified_records();$records=$verified;$seen=[];$counts=[];
    foreach($records as $r){$seen[round((float)$r['lat'],6).'|'.round((float)$r['lng'],6).'|'.strtolower((string)$r['label'])]=1;$counts[$r['type']]=($counts[$r['type']]??0)+1;}
    $pdo=$pdo?:((function_exists('db'))?db():null); if(!$pdo instanceof PDO)return ['ok'=>true,'version'=>'13.3.6','records'=>$records,'counts'=>$counts,'verifiedCount'=>count($verified),'databaseCount'=>0];
    $tenant=sk1336_md_tenant_id();
    $wanted=['id','name','title','business_name','event_title','property_title','doctor_name','listing_title','service_name','latitude','lat','map_lat','geo_lat','location_lat','longitude','lng','lon','long','map_lng','geo_lng','location_lng','tenant_id','status','is_active','active','url','link','slug','source','data_source','import_source','seed_source','is_demo','is_dummy'];
    $marks=implode(',',array_fill(0,count($wanted),'?'));
    try{$sql="SELECT TABLE_NAME,COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME IN ($marks) AND TABLE_NAME REGEXP '(business|event|propert|estate|doctor|clinic|hospital|job|place|listing|service|shop|restaurant|vendor|directory)' ORDER BY TABLE_NAME,ORDINAL_POSITION";$q=$pdo->prepare($sql);$q->execute($wanted);$meta=$q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){return ['ok'=>true,'version'=>'13.3.6','records'=>$records,'counts'=>$counts,'verifiedCount'=>count($verified),'databaseCount'=>0];}
    $tables=[];foreach($meta as $r){$t=(string)($r['TABLE_NAME']??'');$c=(string)($r['COLUMN_NAME']??'');if($t!==''&&$c!=='')$tables[$t][$c]=true;}
    $latC=['latitude','lat','map_lat','geo_lat','location_lat'];$lngC=['longitude','lng','lon','long','map_lng','geo_lng','location_lng'];$nameC=['name','title','business_name','event_title','property_title','doctor_name','listing_title','service_name'];
    $dbAdded=0;
    foreach($tables as $table=>$cols){if(count($records)>=300)break;$lat=$lng=$label=null;foreach($latC as $c)if(isset($cols[$c])){$lat=$c;break;}foreach($lngC as $c)if(isset($cols[$c])){$lng=$c;break;}foreach($nameC as $c)if(isset($cols[$c])){$label=$c;break;}if(!$lat||!$lng)continue;if(isset($cols['tenant_id'])&&$tenant===null)continue;
      $qt=sk1336_md_ident($table);$qlat=sk1336_md_ident($lat);$qlng=sk1336_md_ident($lng);if(!$qt||!$qlat||!$qlng)continue;$select=[];$select[]=(isset($cols['id'])?'`id`':'NULL').' AS rid';$select[]=($label?sk1336_md_ident($label):"''").' AS label';$select[]="$qlat AS lat";$select[]="$qlng AS lng";$select[]=(isset($cols['url'])?'`url`':(isset($cols['link'])?'`link`':"''")).' AS url';
      $src='';foreach(['source','data_source','import_source','seed_source'] as $c)if(isset($cols[$c])){$src=$c;break;}$select[]=($src?sk1336_md_ident($src):"''").' AS source_tag';$select[]=(isset($cols['is_demo'])?'`is_demo`':(isset($cols['is_dummy'])?'`is_dummy`':'0')).' AS demo_flag';
      $where=["CAST($qlat AS DECIMAL(12,8)) BETWEEN -90 AND 90","CAST($qlng AS DECIMAL(12,8)) BETWEEN -180 AND 180","$qlat IS NOT NULL","$qlng IS NOT NULL"];$params=[];if(isset($cols['tenant_id'])&&$tenant!==null){$where[]='`tenant_id`=?';$params[]=$tenant;}if(isset($cols['is_active']))$where[]='(`is_active`=1 OR `is_active` IS NULL)';elseif(isset($cols['active']))$where[]='(`active`=1 OR `active` IS NULL)';if(isset($cols['status']))$where[]="(`status` IS NULL OR LOWER(CAST(`status` AS CHAR)) IN ('1','active','published','approved','live','open','available'))";
      $limit=min(80,300-count($records));$sql='SELECT '.implode(',',$select).' FROM '.$qt.' WHERE '.implode(' AND ',$where).' LIMIT '.$limit;try{$q=$pdo->prepare($sql);$q->execute($params);$rows=$q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){continue;}$type=sk1336_md_type($table);
      foreach($rows as $r){$source=strtolower(trim((string)($r['source_tag']??'')));$demo=(int)($r['demo_flag']??0)===1;if($source==='research_demo_v1335'||($demo&&$source!=='verified_osm_v1336'))continue;$la=(float)($r['lat']??0);$lo=(float)($r['lng']??0);if(($la===0.0&&$lo===0.0)||$la<-90||$la>90||$lo<-180||$lo>180)continue;$lab=trim((string)($r['label']??''));if($lab==='')$lab=ucfirst($type).' #'.(string)($r['rid']??'');$key=round($la,6).'|'.round($lo,6).'|'.strtolower($lab);if(isset($seen[$key]))continue;$seen[$key]=1;$url=trim((string)($r['url']??''));if($url!==''&&!preg_match('~^(?:/|https?://)~i',$url))$url='';$records[]=['id'=>(string)($r['rid']??''),'label'=>$lab,'lat'=>$la,'lng'=>$lo,'type'=>$type,'url'=>$url,'verified'=>false,'source'=>'database'];$counts[$type]=($counts[$type]??0)+1;$dbAdded++;if(count($records)>=300)break;}
    }
    return ['ok'=>true,'version'=>'13.3.6','tenantScoped'=>$tenant!==null,'records'=>$records,'counts'=>$counts,'total'=>count($records),'verifiedCount'=>count($verified),'databaseCount'=>$dbAdded,'approximateV1335Excluded'=>true];
}
}
