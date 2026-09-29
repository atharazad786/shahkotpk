<?php
declare(strict_types=1);
/** ShahkotPK v13.3.4 — bounded, tenant-safe coordinate discovery for public map surfaces. */
if(!function_exists('sk1334_map_data')){
function sk1334_md_ident(string $s): string { return preg_match('/^[A-Za-z0-9_]+$/',$s)?'`'.$s.'`':''; }
function sk1334_md_tenant_id(): ?int {
    foreach(['sk1300_current_tenant_id','current_tenant_id','tenant_id'] as $fn){try{if(function_exists($fn)){$v=$fn();if(is_numeric($v)&&(int)$v>0)return (int)$v;}}catch(Throwable $e){}}
    if(session_status()===PHP_SESSION_ACTIVE){foreach(['tenant_id','current_tenant_id'] as $k){$v=$_SESSION[$k]??null;if(is_numeric($v)&&(int)$v>0)return (int)$v;}}
    return null;
}
function sk1334_md_type(string $table): string {
    $t=strtolower($table);
    if(str_contains($t,'event'))return 'event'; if(str_contains($t,'propert')||str_contains($t,'estate'))return 'property';
    if(str_contains($t,'doctor')||str_contains($t,'clinic')||str_contains($t,'hospital'))return 'doctor'; if(str_contains($t,'job'))return 'job';
    if(str_contains($t,'service'))return 'service'; if(str_contains($t,'business')||str_contains($t,'shop')||str_contains($t,'restaurant')||str_contains($t,'vendor'))return 'business';
    return 'place';
}
function sk1334_map_data(?PDO $pdo=null): array {
    $pdo=$pdo?:((function_exists('db'))?db():null); if(!$pdo instanceof PDO)return ['ok'=>false,'version'=>'13.3.4','records'=>[],'counts'=>[]];
    $tenant=sk1334_md_tenant_id();
    $wanted=['id','name','title','business_name','event_title','property_title','doctor_name','listing_title','service_name','latitude','lat','map_lat','geo_lat','location_lat','longitude','lng','lon','long','map_lng','geo_lng','location_lng','tenant_id','status','is_active','active','url','link','slug'];
    $marks=implode(',',array_fill(0,count($wanted),'?'));
    try{
        $sql="SELECT TABLE_NAME,COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME IN ($marks) AND TABLE_NAME REGEXP '(business|event|propert|estate|doctor|clinic|hospital|job|place|listing|service|shop|restaurant|vendor|directory)' ORDER BY TABLE_NAME,ORDINAL_POSITION";
        $q=$pdo->prepare($sql);$q->execute($wanted);$meta=$q->fetchAll(PDO::FETCH_ASSOC)?:[];
    }catch(Throwable $e){return ['ok'=>false,'version'=>'13.3.4','records'=>[],'counts'=>[]];}
    $tables=[];foreach($meta as $r){$t=(string)($r['TABLE_NAME']??'');$c=(string)($r['COLUMN_NAME']??'');if($t!==''&&$c!=='')$tables[$t][$c]=true;}
    $latC=['latitude','lat','map_lat','geo_lat','location_lat'];$lngC=['longitude','lng','lon','long','map_lng','geo_lng','location_lng'];
    $nameC=['name','title','business_name','event_title','property_title','doctor_name','listing_title','service_name'];
    $records=[];$seen=[];$counts=[];
    foreach($tables as $table=>$cols){
        if(count($records)>=300)break;
        $lat=null;$lng=null;$label=null;foreach($latC as $c)if(isset($cols[$c])){$lat=$c;break;}foreach($lngC as $c)if(isset($cols[$c])){$lng=$c;break;}foreach($nameC as $c)if(isset($cols[$c])){$label=$c;break;}
        if(!$lat||!$lng)continue;
        // Never expose cross-tenant rows when the table is tenant-scoped and tenant context is unavailable.
        if(isset($cols['tenant_id'])&&$tenant===null)continue;
        $qt=sk1334_md_ident($table);$qlat=sk1334_md_ident($lat);$qlng=sk1334_md_ident($lng);if(!$qt||!$qlat||!$qlng)continue;
        $select=[];$select[]=(isset($cols['id'])?'`id`':'NULL').' AS rid';$select[]=($label?sk1334_md_ident($label):"''").' AS label';$select[]="$qlat AS lat";$select[]="$qlng AS lng";
        if(isset($cols['url']))$select[]='`url` AS url';elseif(isset($cols['link']))$select[]='`link` AS url';else $select[]="'' AS url";
        $where=["CAST($qlat AS DECIMAL(12,8)) BETWEEN -90 AND 90","CAST($qlng AS DECIMAL(12,8)) BETWEEN -180 AND 180","$qlat IS NOT NULL","$qlng IS NOT NULL"];$params=[];
        if(isset($cols['tenant_id'])&&$tenant!==null){$where[]='`tenant_id`=?';$params[]=$tenant;}
        if(isset($cols['is_active']))$where[]='(`is_active`=1 OR `is_active` IS NULL)';elseif(isset($cols['active']))$where[]='(`active`=1 OR `active` IS NULL)';
        if(isset($cols['status']))$where[]="(`status` IS NULL OR LOWER(CAST(`status` AS CHAR)) IN ('1','active','published','approved','live','open','available'))";
        $limit=min(80,300-count($records));$sql='SELECT '.implode(',',$select).' FROM '.$qt.' WHERE '.implode(' AND ',$where).' LIMIT '.$limit;
        try{$q=$pdo->prepare($sql);$q->execute($params);$rows=$q->fetchAll(PDO::FETCH_ASSOC)?:[];}catch(Throwable $e){continue;}
        $type=sk1334_md_type($table);
        foreach($rows as $r){$la=(float)($r['lat']??0);$lo=(float)($r['lng']??0);if($la===0.0&&$lo===0.0)continue;$lab=trim((string)($r['label']??''));if($lab==='')$lab=ucfirst($type).' #'.(string)($r['rid']??'');$key=round($la,6).'|'.round($lo,6).'|'.$lab;if(isset($seen[$key]))continue;$seen[$key]=1;$url=trim((string)($r['url']??''));if($url!==''&&!preg_match('~^(?:/|https?://)~i',$url))$url='';$records[]=['id'=>(string)($r['rid']??''),'label'=>$lab,'lat'=>$la,'lng'=>$lo,'type'=>$type,'url'=>$url];$counts[$type]=($counts[$type]??0)+1;if(count($records)>=300)break;}
    }
    return ['ok'=>true,'version'=>'13.3.4','tenantScoped'=>$tenant!==null,'records'=>$records,'counts'=>$counts,'total'=>count($records)];
}
}
