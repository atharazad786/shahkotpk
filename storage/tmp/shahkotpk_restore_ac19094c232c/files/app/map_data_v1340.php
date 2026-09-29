<?php
declare(strict_types=1);
/** ShahkotPK v13.4.0 — bounded location discovery over verified + genuine coordinate data. */
require_once __DIR__.'/map_data_v1336.php';
if(!function_exists('sk1340_location_data')){
function sk1340_haversine(float $lat1,float $lng1,float $lat2,float $lng2): float {
    $r=6371.0088;$dLat=deg2rad($lat2-$lat1);$dLng=deg2rad($lng2-$lng1);$a=sin($dLat/2)**2+cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLng/2)**2;return 2*$r*asin(min(1,sqrt($a)));
}
function sk1340_clean_type(string $v): string {$v=strtolower(trim($v));$aliases=['businesses'=>'business','shops'=>'business','restaurant'=>'business','restaurants'=>'business','events'=>'event','properties'=>'property','jobs'=>'job','services'=>'service','places'=>'place','city guide'=>'place','guide'=>'place'];return $aliases[$v]??$v;}
function sk1340_location_data(array $filters=[]): array {
    $base=sk1336_map_data();$records=is_array($base['records']??null)?$base['records']:[];
    $q=strtolower(trim((string)($filters['q']??'')));if(strlen($q)>100)$q=substr($q,0,100);
    $type=sk1340_clean_type((string)($filters['type']??''));if(in_array($type,['all','all types','*'],true))$type='';
    $lat=is_numeric($filters['lat']??null)?(float)$filters['lat']:null;$lng=is_numeric($filters['lng']??null)?(float)$filters['lng']:null;
    if($lat!==null&&($lat<-90||$lat>90))$lat=null;if($lng!==null&&($lng<-180||$lng>180))$lng=null;
    $radius=is_numeric($filters['radius_km']??null)?(float)$filters['radius_km']:null;if($radius!==null)$radius=max(.1,min(250,$radius));
    $limit=is_numeric($filters['limit']??null)?(int)$filters['limit']:300;$limit=max(1,min(500,$limit));
    $out=[];$counts=[];
    foreach($records as $r){
      if(!is_array($r))continue;$rt=sk1340_clean_type((string)($r['type']??'place'));$label=trim((string)($r['label']??''));$category=trim((string)($r['category']??''));
      if($type!==''&&$rt!==$type&&strtolower($category)!==strtolower($type))continue;
      if($q!==''&&!str_contains(strtolower($label.' '.$category.' '.$rt),$q))continue;
      $la=(float)($r['lat']??0);$lo=(float)($r['lng']??0);if($la<-90||$la>90||$lo<-180||$lo>180)continue;
      $distance=null;if($lat!==null&&$lng!==null){$distance=sk1340_haversine($lat,$lng,$la,$lo);if($radius!==null&&$distance>$radius)continue;}
      $r['type']=$rt;if($distance!==null)$r['distance_km']=round($distance,3);$out[]=$r;$counts[$rt]=($counts[$rt]??0)+1;
    }
    usort($out,static function($a,$b) use($lat,$lng){if($lat!==null&&$lng!==null)return (($a['distance_km']??INF)<=>($b['distance_km']??INF));$va=!empty($a['verified'])?0:1;$vb=!empty($b['verified'])?0:1;return $va<=>$vb ?: strcasecmp((string)($a['label']??''),(string)($b['label']??''));});
    $total=count($out);if($total>$limit)$out=array_slice($out,0,$limit);
    return ['ok'=>true,'version'=>'13.4.0','records'=>$out,'counts'=>$counts,'total'=>$total,'returned'=>count($out),'filters'=>['q'=>$q,'type'=>$type,'radius_km'=>$radius,'near'=>($lat!==null&&$lng!==null)],'verifiedCount'=>(int)($base['verifiedCount']??0),'databaseCount'=>(int)($base['databaseCount']??0),'approximateV1335Excluded'=>true];
}
}
