<?php
declare(strict_types=1);

function google_maps_enabled(): bool {return setting_bool('google_maps_enabled',true);}

function google_maps_api_key(): string {
    $stored=trim((string)setting('google_maps_api_key',''));
    if($stored!=='')return $stored;
    foreach(['google_maps_browser_api_key','google_map_api_key','maps_api_key'] as $legacy){$v=trim((string)setting($legacy,''));if($v!=='')return $v;}
    if(defined('GOOGLE_MAPS_API_KEY') && trim((string)GOOGLE_MAPS_API_KEY)!=='')return trim((string)GOOGLE_MAPS_API_KEY);
    $env=trim((string)(getenv('GOOGLE_MAPS_API_KEY')?:''));if($env!=='')return $env;
    return '';
}
function google_maps_ready(): bool {return google_maps_enabled() && google_maps_api_key()!=='';}
function google_maps_key_masked(): string {$k=google_maps_api_key();if($k==='')return 'Not configured';return substr($k,0,6).'••••••••'.substr($k,-4);}
function google_maps_embed_url(?float $lat=null,?float $lng=null,?int $zoom=null): string {
    $c=google_maps_default_center();$lat=$lat??$c['lat'];$lng=$lng??$c['lng'];$zoom=$zoom??$c['zoom'];
    return 'https://maps.google.com/maps?q='.rawurlencode((string)$lat.','.(string)$lng).'&z='.max(2,min(21,$zoom)).'&output=embed';
}
function google_maps_external_url(?float $lat=null,?float $lng=null): string {$c=google_maps_default_center();$lat=$lat??$c['lat'];$lng=$lng??$c['lng'];return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode((string)$lat.','.(string)$lng);}

function google_maps_default_center(): array {
    return ['lat'=>(float)setting('google_maps_default_lat','31.5709000'),'lng'=>(float)setting('google_maps_default_lng','73.4853000'),'zoom'=>max(2,min(21,setting_int('google_maps_default_zoom',14)))];
}
function google_maps_type_catalog(): array {
    return [
      'business'=>['label'=>'Businesses','short'=>'B','color'=>'#0f766e','enabled_key'=>'google_maps_show_businesses'],
      'guide'=>['label'=>'City Guide','short'=>'G','color'=>'#2563eb','enabled_key'=>'google_maps_show_guide'],
      'deal'=>['label'=>'Deals','short'=>'%','color'=>'#7c3aed','enabled_key'=>'google_maps_show_deals'],
      'event'=>['label'=>'Events','short'=>'E','color'=>'#0284c7','enabled_key'=>'google_maps_show_events'],
      'job'=>['label'=>'Jobs','short'=>'J','color'=>'#d97706','enabled_key'=>'google_maps_show_jobs'],
      'property'=>['label'=>'Property','short'=>'P','color'=>'#0369a1','enabled_key'=>'google_maps_show_property'],
    ];
}
function google_maps_allowed_types(?array $types=null): array {
    $catalog=google_maps_type_catalog();$out=[];
    foreach($catalog as $k=>$v){if($types!==null&&!in_array($k,$types,true))continue;if(setting_bool($v['enabled_key'],true))$out[]=$k;}
    return $out;
}
function google_maps_business_markers(bool $publicOnly=true): array {
    $where=['bd.latitude IS NOT NULL','bd.longitude IS NOT NULL'];$params=[];if($publicOnly){$where[]='b.status=1';$where[]="b.verification_status<>'suspended'";}if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'b.city_id');
    $sql="SELECT b.id,b.name,b.slug,b.address,b.phone,b.whatsapp,b.image,b.verification_status,b.is_featured,c.name city_name,cat.name category_name,bd.tagline,bd.latitude,bd.longitude,bd.map_url FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id JOIN business_details bd ON bd.business_id=b.id WHERE ".implode(' AND ',$where)." ORDER BY b.is_featured DESC,b.id DESC LIMIT 1500";
    try{$q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll();}catch(Throwable $e){return [];}
    $out=[];foreach($rows as $r){$out[]=['id'=>'business-'.(int)$r['id'],'record_id'=>(int)$r['id'],'type'=>'business','title'=>(string)$r['name'],'subtitle'=>(string)$r['category_name'],'city'=>(string)$r['city_name'],'address'=>(string)$r['address'],'lat'=>(float)$r['latitude'],'lng'=>(float)$r['longitude'],'url'=>'/business.php?slug='.urlencode((string)$r['slug']),'phone'=>(string)$r['phone'],'whatsapp'=>(string)$r['whatsapp'],'image'=>(string)$r['image'],'featured'=>(bool)$r['is_featured'],'verified'=>$r['verification_status']==='verified'];}return $out;
}
function google_maps_portal_markers(array $types,bool $publicOnly=true): array {
    $types=array_values(array_intersect(['guide','deal','event','job','property'],$types));if(!$types)return [];
    $placeholders=implode(',',array_fill(0,count($types),'?'));$where=["p.content_type IN ($placeholders)",'p.latitude IS NOT NULL','p.longitude IS NOT NULL'];$params=$types;
    if($publicOnly){$where[]="p.status='published'";$where[]="(p.ends_at IS NULL OR p.ends_at>=NOW())";}if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');
    $sql="SELECT p.*,c.name city_name,b.name business_name,b.slug business_slug FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." ORDER BY p.featured DESC,p.sort_order,p.id DESC LIMIT 1500";
    try{$q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll();}catch(Throwable $e){return [];}
    $out=[];foreach($rows as $r){$def=city_portal_type((string)$r['content_type']);$out[]=['id'=>$r['content_type'].'-'.(int)$r['id'],'record_id'=>(int)$r['id'],'type'=>(string)$r['content_type'],'title'=>(string)$r['title'],'subtitle'=>(string)($r['category']?:$def['label']),'city'=>(string)$r['city_name'],'address'=>(string)$r['address'],'lat'=>(float)$r['latitude'],'lng'=>(float)$r['longitude'],'url'=>$def['url'].'?slug='.urlencode((string)$r['slug']),'phone'=>(string)$r['phone'],'whatsapp'=>(string)$r['whatsapp'],'image'=>(string)$r['image_url'],'featured'=>(bool)$r['featured'],'verified'=>false];}return $out;
}
function google_maps_markers(array $types=[],bool $publicOnly=true): array {
    $types=$types?:google_maps_allowed_types();$types=google_maps_allowed_types($types);$out=[];
    if(in_array('business',$types,true))$out=array_merge($out,google_maps_business_markers($publicOnly));
    $portal=array_values(array_diff($types,['business']));if($portal)$out=array_merge($out,google_maps_portal_markers($portal,$publicOnly));return $out;
}
function google_maps_stats(): array {
    $stats=['business'=>['total'=>0,'mapped'=>0,'missing'=>0]];foreach(['guide','deal','event','job','property'] as $t)$stats[$t]=['total'=>0,'mapped'=>0,'missing'=>0];$city=function_exists('tenant_query_city_id')?tenant_query_city_id():0;
    try{$where=[];$params=[];if($city>0){$where[]='b.city_id=?';$params[]=$city;}$sql="SELECT COUNT(*) total,SUM(bd.latitude IS NOT NULL AND bd.longitude IS NOT NULL) mapped FROM businesses b LEFT JOIN business_details bd ON bd.business_id=b.id".($where?' WHERE '.implode(' AND ',$where):'');$q=db()->prepare($sql);$q->execute($params);$r=$q->fetch();$stats['business']['total']=(int)($r['total']??0);$stats['business']['mapped']=(int)($r['mapped']??0);$stats['business']['missing']=$stats['business']['total']-$stats['business']['mapped'];}catch(Throwable $e){}
    try{$where=[];$params=[];if($city>0){$where[]='city_id=?';$params[]=$city;}$sql="SELECT content_type,COUNT(*) total,SUM(latitude IS NOT NULL AND longitude IS NOT NULL) mapped FROM city_portal_items".($where?' WHERE '.implode(' AND ',$where):'')." GROUP BY content_type";$q=db()->prepare($sql);$q->execute($params);foreach($q->fetchAll() as $r){$t=(string)$r['content_type'];if(isset($stats[$t])){$stats[$t]['total']=(int)$r['total'];$stats[$t]['mapped']=(int)$r['mapped'];$stats[$t]['missing']=$stats[$t]['total']-$stats[$t]['mapped'];}}}catch(Throwable $e){}
    return $stats;
}
function google_maps_missing_records(int $limit=100): array {
    $out=[];$city=function_exists('tenant_query_city_id')?tenant_query_city_id():0;
    try{$where=['(bd.latitude IS NULL OR bd.longitude IS NULL)'];$params=[];if($city>0){$where[]='b.city_id=?';$params[]=$city;}$sql="SELECT b.id,'business' type,b.name title,b.address,'' category FROM businesses b LEFT JOIN business_details bd ON bd.business_id=b.id WHERE ".implode(' AND ',$where)." ORDER BY b.id DESC LIMIT ".max(1,min(500,$limit));$q=db()->prepare($sql);$q->execute($params);foreach($q->fetchAll() as $r){$r['edit_url']='/admin/businesses.php?edit='.$r['id'];$out[]=$r;}}catch(Throwable $e){}
    try{$where=['(latitude IS NULL OR longitude IS NULL)'];$params=[];if($city>0){$where[]='city_id=?';$params[]=$city;}$sql="SELECT id,content_type type,title,address,category FROM city_portal_items WHERE ".implode(' AND ',$where)." ORDER BY id DESC LIMIT ".max(1,min(500,$limit));$q=db()->prepare($sql);$q->execute($params);foreach($q->fetchAll() as $r){$map=['guide'=>'city-guide.php','deal'=>'deals.php','event'=>'events.php','job'=>'jobs.php','property'=>'property.php'];$r['edit_url']='/admin/'.($map[$r['type']]??'city-guide.php').'?edit='.$r['id'];$out[]=$r;}}catch(Throwable $e){}
    return array_slice($out,0,$limit);
}
function google_maps_script_url(): string {return 'https://maps.googleapis.com/maps/api/js?key='.rawurlencode(google_maps_api_key()).'&libraries=places&callback=ShahkotMapsInit';}
function google_maps_config(): array {$c=google_maps_default_center();return ['defaultLat'=>$c['lat'],'defaultLng'=>$c['lng'],'defaultZoom'=>$c['zoom'],'mapType'=>(string)setting('google_maps_map_type','roadmap'),'fitMarkers'=>setting_bool('google_maps_fit_markers',true),'placesSearch'=>setting_bool('google_maps_places_search',true),'catalog'=>google_maps_type_catalog()];}
function render_google_maps_assets(): void {
    if(!google_maps_ready())return;$config=json_encode(google_maps_config(),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    echo '<link rel="stylesheet" href="/assets/google-maps-3.1.1.css?v=311"><script>window.ShahkotMapsConfig='.$config.';window.gm_authFailure=function(){document.querySelectorAll(".shahkot-map").forEach(function(el){el.innerHTML="<div class=\"map-api-error\"><b>Google Maps could not authenticate.</b><span>Check the Browser API key, domain restriction, billing and Maps JavaScript API status in Admin → Map Control.</span></div>";});};</script><script src="/assets/google-maps-3.1.1.js?v=311"></script><script async defer src="'.e(google_maps_script_url()).'"></script>';
}
function google_maps_public_module(string $title='Explore Shahkot on Map',array $types=[],string $subtitle='Search businesses, events, property and city places around you.'): void {
    if(!google_maps_ready())return;$types=$types?:google_maps_allowed_types();$typeString=implode(',',$types);$catalog=google_maps_type_catalog();
    echo '<section class="lt-section city-map-module"><div class="lt-shell"><div class="city-map-heading"><div><span>INTERACTIVE CITY MAP</span><h2>'.e($title).'</h2><p>'.e($subtitle).'</p></div><a href="/city-map.php">Open Full Map →</a></div><div class="city-map-toolbar"><div class="city-map-search"><span>⌕</span><input data-map-search placeholder="Search shop, event, property, place..."></div><select data-map-type-filter><option value="">All Map Types</option>';
    foreach($types as $type){$m=$catalog[$type]??null;if($m)echo '<option value="'.e($type).'">'.e($m['label']).'</option>';}
    echo '</select><button type="button" data-map-near>◎ Near Me</button></div><div class="shahkot-map" data-map-types="'.e($typeString).'" style="height:'.e((string)max(360,min(850,setting_int('google_maps_height',560)))).'px"></div><div class="city-map-legend">';foreach($types as $type){$m=$catalog[$type]??null;if($m)echo '<span><i style="--marker-color:'.e($m['color']).'"></i>'.e($m['label']).'</span>'; }echo '</div></div></section>';
}
?>
