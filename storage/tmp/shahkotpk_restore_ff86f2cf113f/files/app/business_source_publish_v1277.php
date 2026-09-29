<?php
declare(strict_types=1);
$bs1278=__DIR__.'/business_publish_index_v1278.php';if(is_file($bs1278))require_once $bs1278;
/** ShahkotPK v12.7.7 — imported-business public publish, geo/map and live photo sync repair. */
if (!function_exists('bs1270_h')) {
    $f=__DIR__.'/business_source_import_v1270.php';
    if (is_file($f)) require_once $f;
}
if (!function_exists('bs1277_now')) {
function bs1277_now(): string { return date('Y-m-d H:i:s'); }

function bs1277_business(int $bid): array {
    if ($bid<1 || !bs1270_table('businesses')) return [];
    try { $q=db()->prepare('SELECT * FROM businesses WHERE id=? LIMIT 1'); $q->execute([$bid]); return $q->fetch()?:[]; }
    catch(Throwable $e){ return []; }
}

function bs1277_city_id(): int {
    if (function_exists('bs1276_resolve_shahkot_city_id')) return bs1276_resolve_shahkot_city_id();
    if (!bs1270_table('cities')) throw new RuntimeException('Shahkot city record is unavailable.');
    $c=bs1270_cols('cities'); $name=isset($c['name'])?'name':(isset($c['city_name'])?'city_name':(isset($c['title'])?'title':''));
    if ($name==='') throw new RuntimeException('Cities table has no supported city-name field.');
    try {
        $sql='SELECT id FROM cities WHERE LOWER(REPLACE(TRIM(`'.$name.'`),\' \',\'\')) IN (\'shahkot\',\'shahkott\') ORDER BY id ASC LIMIT 1';
        $id=(int)(db()->query($sql)->fetchColumn()?:0); if($id>0)return $id;
    } catch(Throwable $e){}
    throw new RuntimeException('Shahkot city_id could not be resolved.');
}

function bs1277_link_for_business(int $bid): array {
    if($bid<1 || !bs1270_table('business_source_links_v1270')) return [];
    try{$q=db()->prepare('SELECT * FROM business_source_links_v1270 WHERE business_id=? AND provider=? AND tenant_id IN (?,0) ORDER BY tenant_id DESC,id DESC LIMIT 1');$q->execute([$bid,'google_places',bs1270_tid()]);return $q->fetch()?:[];}catch(Throwable $e){return [];}
}

function bs1277_candidate_category(int $candidateId): int {
    if($candidateId<1 || !bs1270_table('business_source_candidates_v1270'))return 0;
    try{$q=db()->prepare('SELECT category_id FROM business_source_candidates_v1270 WHERE id=? AND tenant_id IN (?,0) LIMIT 1');$q->execute([$candidateId,bs1270_tid()]);return (int)($q->fetchColumn()?:0);}catch(Throwable $e){return 0;}
}

function bs1277_phone_available(int $bid,string $phone): bool {
    $phone=trim($phone); if($phone==='' || !bs1270_table('businesses') || !isset(bs1270_cols('businesses')['phone']))return true;
    $norm=bs1270_norm_phone($phone); if($norm==='')return true;
    try{$q=db()->query("SELECT id,phone FROM businesses WHERE phone IS NOT NULL AND phone<>'' ORDER BY id DESC LIMIT 5000");foreach($q->fetchAll()?:[] as $r){if((int)$r['id']!==$bid && bs1270_norm_phone((string)$r['phone'])===$norm)return false;}}catch(Throwable $e){}
    return true;
}

function bs1277_has_owner_image(array $b): bool {
    foreach(['image','image_url','thumbnail_url','cover_url','cover_image','featured_image','logo','logo_url'] as $k){$v=trim((string)($b[$k]??''));if($v!=='' && !str_contains($v,'business-source-photo-public-v1277.php'))return true;}
    return false;
}

function bs1277_photo_proxy_url(int $bid,int $index=0): string {
    return '/api/business-source-photo-public-v1277.php?business_id='.$bid.'&index='.max(0,min(5,$index));
}

function bs1277_health_save(int $bid,array $d): void {
    if($bid<1 || !bs1270_table('business_source_health_v1277'))return;
    $tid=bs1270_tid();
    try{$q=db()->prepare('SELECT id FROM business_source_health_v1277 WHERE tenant_id=? AND business_id=? LIMIT 1');$q->execute([$tid,$bid]);$id=(int)($q->fetchColumn()?:0);
        $d=array_merge(['tenant_id'=>$tid,'business_id'=>$bid,'updated_at'=>bs1277_now()],$d);
        if($id)bs1270_update('business_source_health_v1277',$id,$d);else{$d['created_at']=bs1277_now();bs1270_insert('business_source_health_v1277',$d);}
    }catch(Throwable $e){}
}

function bs1277_health(int $bid): array {
    if($bid<1 || !bs1270_table('business_source_health_v1277'))return [];
    try{$q=db()->prepare('SELECT * FROM business_source_health_v1277 WHERE business_id=? AND tenant_id IN (?,0) ORDER BY tenant_id DESC,id DESC LIMIT 1');$q->execute([$bid,bs1270_tid()]);return $q->fetch()?:[];}catch(Throwable $e){return [];}
}

function bs1277_upsert_details(int $bid,float $lat,float $lng,string $mapUrl,string $address,string $website): void {
    if(!bs1270_table('business_details'))return;
    try{$q=db()->prepare('SELECT id FROM business_details WHERE business_id=? LIMIT 1');$q->execute([$bid]);$id=(int)($q->fetchColumn()?:0);
        $d=['business_id'=>$bid,'latitude'=>$lat?:null,'longitude'=>$lng?:null,'lat'=>$lat?:null,'lng'=>$lng?:null,'map_url'=>$mapUrl,'google_maps_url'=>$mapUrl,'address'=>$address,'website'=>$website];
        if($id)bs1270_update('business_details',$id,$d); else bs1270_insert('business_details',$d);
    }catch(Throwable $e){}
}

function bs1277_ensure_microsite(int $bid): void {
    if($bid<1 || !bs1270_table('business_microsites_v1130'))return;
    $tid=bs1270_tid();
    try{$q=db()->prepare('SELECT id FROM business_microsites_v1130 WHERE business_id=? AND tenant_id IN (?,0) ORDER BY tenant_id DESC LIMIT 1');$q->execute([$bid,$tid]);$id=(int)($q->fetchColumn()?:0);
        $d=['tenant_id'=>$tid,'business_id'=>$bid,'enabled'=>1,'theme_mode'=>'auto','theme_key'=>'auto','layout_variant'=>'auto','ai_enabled'=>1,'updated_at'=>bs1277_now()];
        if($id)bs1270_update('business_microsites_v1130',$id,$d);else{$d['created_at']=bs1277_now();bs1270_insert('business_microsites_v1130',$d);}
    }catch(Throwable $e){}
}

function bs1277_finalize_google_business(int $bid,int $candidateId,array $g,bool $newImported): array {
    if($bid<1)throw new RuntimeException('Invalid imported business.');
    $b=bs1277_business($bid); if(!$b)throw new RuntimeException('Imported business record could not be loaded.');
    $geo=bs1270_shahkot_check($g); if(empty($geo['ok']))throw new RuntimeException('Google source no longer matches Shahkot; publish/geo repair was not applied.');
    $lat=(float)($g['location']['latitude']??0);$lng=(float)($g['location']['longitude']??0);
    if(!$lat || !$lng)throw new RuntimeException('Google source did not return usable latitude/longitude.');
    $address=trim((string)($g['formattedAddress']??''));
    $phone=trim((string)($g['nationalPhoneNumber']??$g['internationalPhoneNumber']??''));
    $website=trim((string)($g['websiteUri']??''));
    $mapUrl=trim((string)($g['googleMapsUri']??''));
    $cityId=bs1277_city_id();
    $categoryId=(int)($b['category_id']??0); if($categoryId<1)$categoryId=bs1277_candidate_category($candidateId);
    $d=['city_id'=>$cityId,'category_id'=>$categoryId?:null,
        'latitude'=>$lat,'longitude'=>$lng,'lat'=>$lat,'lng'=>$lng,'map_latitude'=>$lat,'map_longitude'=>$lng,'location_lat'=>$lat,'location_lng'=>$lng,
        'map_url'=>$mapUrl,'google_maps_url'=>$mapUrl,'updated_at'=>bs1277_now()];
    if(trim((string)($b['address']??''))==='' && $address!=='')$d['address']=$address;
    if(trim((string)($b['website']??''))==='' && $website!=='')$d['website']=$website;
    if(trim((string)($b['phone']??''))==='' && $phone!=='' && bs1277_phone_available($bid,$phone)){$d['phone']=$phone;$d['whatsapp']=$phone;}
    if($newImported){
        $d += ['status'=>'active','listing_status'=>'active','publish_status'=>'published','moderation_status'=>'approved','active'=>1,'is_active'=>1,'published'=>1,'is_published'=>1,'approved'=>1,'is_approved'=>1,'hidden'=>0,'is_hidden'=>0,'deleted'=>0,'is_deleted'=>0,'visibility'=>'public','verification_status'=>'verified','verified'=>1,'is_verified'=>1,'approval_status'=>'approved','verified_at'=>bs1277_now(),'published_at'=>bs1277_now(),'approved_at'=>bs1277_now()];
    }
    $photos=(array)($g['photos']??[]);$photoCount=min(6,count($photos));
    if($photoCount>0 && !bs1277_has_owner_image($b)){
        $proxy=bs1277_photo_proxy_url($bid,0);
        $d += ['image'=>$proxy,'image_url'=>$proxy,'thumbnail_url'=>$proxy,'cover_url'=>$proxy,'cover_image'=>$proxy,'featured_image'=>$proxy];
    }
    bs1270_update('businesses',$bid,$d);
    bs1277_upsert_details($bid,$lat,$lng,$mapUrl,$address,$website);
    bs1277_ensure_microsite($bid);
    bs1277_health_save($bid,['geo_status'=>'ok','latitude'=>$lat,'longitude'=>$lng,'photo_count'=>$photoCount,'photo_status'=>$photoCount>0?'live':'none','public_status'=>$newImported?'published':'linked','last_geo_sync_at'=>bs1277_now(),'last_photo_sync_at'=>bs1277_now(),'last_repair_at'=>bs1277_now(),'last_error'=>null]);
    bs1270_audit('business.publish_geo_photo_repaired',$bid,$candidateId,['new_import'=>$newImported?1:0,'city_id'=>$cityId,'latitude'=>$lat,'longitude'=>$lng,'photo_count'=>$photoCount]);
    $unified=[];if(function_exists('bs1278_finalize_business')){try{$unified=bs1278_finalize_business($bid,$newImported?'google_import':'google_link',false);}catch(Throwable $x){}}
    return ['business_id'=>$bid,'city_id'=>$cityId,'latitude'=>$lat,'longitude'=>$lng,'photo_count'=>$photoCount,'published'=>$newImported,'map_url'=>$mapUrl,'publish_health'=>$unified];
}

function bs1277_repair_business(int $bid): array {
    $link=bs1277_link_for_business($bid); if(!$link)throw new RuntimeException('No Google source link exists for this business.');
    $g=bs1270_google_detail((string)$link['provider_place_id']);
    $new=false; if(function_exists('bs1273_lifecycle')){$life=bs1273_lifecycle($bid);$new=!empty($life['imported_by_extractor']);}
    else{try{$q=db()->prepare("SELECT 1 FROM business_source_candidates_v1270 WHERE matched_business_id=? AND status='imported' LIMIT 1");$q->execute([$bid]);$new=(bool)$q->fetchColumn();}catch(Throwable $e){}}
    return bs1277_finalize_google_business($bid,0,$g,$new);
}

function bs1277_sync_business(int $bid): array {
    $legacy=[]; try{$legacy=bs1270_sync_business($bid);}catch(Throwable $e){
        // Continue to Google geo/photo repair only for soft website-sync failures; hard source/geofence errors remain fatal.
        $m=mb_strtolower($e->getMessage()); if(str_contains($m,'outside')||str_contains($m,'no google source')||str_contains($m,'invalid business'))throw $e;
    }
    $r=bs1277_repair_business($bid);
    $r['message']='Source sync complete: public visibility checked, Shahkot city/map coordinates repaired, and '.(int)$r['photo_count'].' live Google photo(s) available.'.(!empty($legacy['website_synced'])?' Official website fields were also refreshed.':'');
    return $r;
}

function bs1277_bulk_repair(array $ids): array {
    $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($x)=>$x>0))); if(!$ids)throw new RuntimeException('Select at least one business.');
    if(count($ids)>20)throw new RuntimeException('Repair supports up to 20 businesses per run.');
    if(function_exists('set_time_limit'))@set_time_limit(180); @ini_set('max_execution_time','180');
    $ok=0;$photos=0;$errors=[];foreach($ids as $bid){try{$r=bs1277_repair_business($bid);$ok++;$photos+=(int)($r['photo_count']??0);}catch(Throwable $e){$errors[]='#'.$bid.' '.$e->getMessage();}}
    return ['processed'=>$ok,'photos'=>$photos,'errors'=>$errors];
}

function bs1277_bulk_sync(array $ids): array {
    $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($x)=>$x>0))); if(!$ids)throw new RuntimeException('Select at least one business.');
    if(count($ids)>20)throw new RuntimeException('Sync supports up to 20 businesses per run.');
    if(function_exists('set_time_limit'))@set_time_limit(180); @ini_set('max_execution_time','180');
    $ok=0;$errors=[];foreach($ids as $bid){try{bs1277_sync_business($bid);$ok++;}catch(Throwable $e){$errors[]='#'.$bid.' '.$e->getMessage();}}
    return ['processed'=>$ok,'errors'=>$errors];
}

function bs1277_live_photo_gallery(int $bid,int $limit=4): array {
    $limit=max(1,min(6,$limit)); $link=bs1277_link_for_business($bid); if(!$link)return [];
    try{$g=bs1270_google_detail((string)$link['provider_place_id']);$photos=(array)($g['photos']??[]);$out=[];
        foreach(array_slice($photos,0,$limit) as $i=>$p){$a=(array)($p['authorAttributions'][0]??[]);$out[]=['url'=>bs1277_photo_proxy_url($bid,$i),'author'=>(string)($a['displayName']??''),'author_uri'=>(string)($a['uri']??''),'maps_url'=>(string)($g['googleMapsUri']??'')];}
        return $out;
    }catch(Throwable $e){return [];}
}
}
