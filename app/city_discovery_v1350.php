<?php
declare(strict_types=1);
/** ShahkotPK v13.5.0 — City Discovery Content Engine. */
require_once __DIR__.'/map_data_v1340.php';
if(!function_exists('sk1350cd_config')){
function sk1350cd_db(): ?PDO {try{return function_exists('db')?db():null;}catch(Throwable $e){return null;}}
function sk1350cd_setting(string $key,string $default=''): string {static $c=[];if(array_key_exists($key,$c))return $c[$key];$pdo=sk1350cd_db();if(!$pdo)return $c[$key]=$default;try{$q=$pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1');$q->execute([$key]);$v=$q->fetchColumn();return $c[$key]=$v===false?$default:(string)$v;}catch(Throwable $e){return $c[$key]=$default;}}
function sk1350cd_bool(string $key,bool $default=true): bool {$v=strtolower(trim(sk1350cd_setting($key,$default?'1':'0')));return in_array($v,['1','true','yes','on','enabled'],true);}
function sk1350cd_int(string $key,int $default,int $min,int $max): int {$v=sk1350cd_setting($key,(string)$default);$n=is_numeric($v)?(int)$v:$default;return max($min,min($max,$n));}
function sk1350cd_config(): array {return [
 'version'=>'13.5.0','enabled'=>sk1350cd_bool('city_discovery_enabled',true),'homeEnabled'=>sk1350cd_bool('city_discovery_home_enabled',true),
 'verifiedFirst'=>sk1350cd_bool('city_discovery_verified_first',true),'itemLimit'=>sk1350cd_int('city_discovery_item_limit',12,4,24),
 'categoryLimit'=>sk1350cd_int('city_discovery_category_limit',8,3,16),'title'=>trim(sk1350cd_setting('city_discovery_title','Explore Shahkot'))?:'Explore Shahkot',
 'subtitle'=>trim(sk1350cd_setting('city_discovery_subtitle','Verified and map-ready local places from ShahkotPK.'))?:'Verified and map-ready local places from ShahkotPK.',
 'endpoint'=>'/api/city-discovery-v1350.php'];}
function sk1350cd_cut(string $s,int $n): string {return function_exists('mb_substr')?mb_substr($s,0,$n,'UTF-8'):substr($s,0,$n);}
function sk1350cd_clean_category(string $s): string {$s=trim(preg_replace('~\s+~',' ',$s)??$s);return sk1350cd_cut($s,80);}
function sk1350cd_safe_url(string $u): string {$u=trim($u);if($u===''||str_starts_with($u,'javascript:')||str_starts_with($u,'data:'))return '';if(str_starts_with($u,'/'))return $u;if(preg_match('~^https?://~i',$u))return $u;return '';}
function sk1350cd_payload(array $filters=[]): array {
 $cfg=sk1350cd_config();if(!$cfg['enabled'])return ['ok'=>true,'version'=>'13.5.0','enabled'=>false,'categories'=>[],'items'=>[],'total'=>0];
 $type=trim((string)($filters['type']??''));$q=trim((string)($filters['q']??''));$limit=max(1,min(100,(int)($filters['limit']??max(30,$cfg['itemLimit']*3))));
 $base=sk1340_location_data(['type'=>$type,'q'=>$q,'limit'=>min(500,max($limit,100))]);$records=is_array($base['records']??null)?$base['records']:[];
 if($cfg['verifiedFirst'])usort($records,static function($a,$b){$va=!empty($a['verified'])?0:1;$vb=!empty($b['verified'])?0:1;return $va<=>$vb ?: strcasecmp((string)($a['label']??''),(string)($b['label']??''));});
 $cats=[];$items=[];$seen=[];
 foreach($records as $r){if(!is_array($r))continue;$label=trim((string)($r['label']??''));if($label==='')continue;$cat=sk1350cd_clean_category((string)($r['category']??$r['type']??'Place'));if($cat==='')$cat='Place';$cats[$cat]=($cats[$cat]??0)+1;
   $key=strtolower($label).'|'.round((float)($r['lat']??0),5).'|'.round((float)($r['lng']??0),5);if(isset($seen[$key]))continue;$seen[$key]=1;
   $items[]=['label'=>$label,'category'=>$cat,'type'=>(string)($r['type']??'place'),'lat'=>(float)($r['lat']??0),'lng'=>(float)($r['lng']??0),'url'=>sk1350cd_safe_url((string)($r['url']??'')),'verified'=>!empty($r['verified']),'source'=>(string)($r['source']??'')];
 }
 arsort($cats);$categories=[];foreach(array_slice($cats,0,$cfg['categoryLimit'],true) as $name=>$count)$categories[]=['name'=>$name,'count'=>$count];
 $items=array_slice($items,0,$cfg['itemLimit']);
 return ['ok'=>true,'version'=>'13.5.0','enabled'=>true,'config'=>$cfg,'categories'=>$categories,'items'=>$items,'total'=>(int)($base['total']??count($records)),'verifiedCount'=>(int)($base['verifiedCount']??0),'databaseCount'=>(int)($base['databaseCount']??0),'ranking'=>'verified-first then alphabetical; no fabricated popularity'];
}
}
