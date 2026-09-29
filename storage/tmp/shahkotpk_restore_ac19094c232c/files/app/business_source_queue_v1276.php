<?php
declare(strict_types=1);
/** ShahkotPK v12.7.6 — city-id/blank-phone constraint repair + bulk-20 workflow. */
$v1277=__DIR__.'/business_source_publish_v1277.php'; if(is_file($v1277)) require_once $v1277;
$v1283=__DIR__.'/shopkeeper_auth_v1283.php'; if(is_file($v1283)) require_once $v1283;
$v1284=__DIR__.'/business_geo_map_bridge_v1284.php'; if(is_file($v1284)) require_once $v1284;
if (!function_exists('bs1276_resolve_shahkot_city_id')) {
function bs1276_resolve_shahkot_city_id(): int {
    if (!bs1270_table('cities')) {
        throw new RuntimeException('Business import requires the existing cities table, but it is unavailable.');
    }
    $cols = bs1270_cols('cities');
    $nameCol = isset($cols['name']) ? 'name' : (isset($cols['city_name']) ? 'city_name' : (isset($cols['title']) ? 'title' : ''));
    if ($nameCol === '') throw new RuntimeException('Unable to resolve Shahkot city: the cities table has no supported name column.');
    $tid = bs1270_tid();
    $tenantSql = '';
    $params = [];
    if (isset($cols['tenant_id'])) {
        $tenantSql = ' AND (`tenant_id`=? OR `tenant_id`=0 OR `tenant_id` IS NULL)';
        $params[] = $tid;
    }
    try {
        $sql = 'SELECT id FROM `cities` WHERE LOWER(REPLACE(TRIM(`'.$nameCol.'`),\' \',\'\')) IN (\'shahkot\',\'shahkott\')'.$tenantSql.' ORDER BY '.(isset($cols['tenant_id'])?'(`tenant_id`='.$tid.') DESC, ':'').'id ASC LIMIT 1';
        $q = db()->prepare($sql); $q->execute($params); $id = (int)($q->fetchColumn() ?: 0);
        if ($id > 0) return $id;
    } catch (Throwable $e) {}

    // Safe fallback: reuse the city_id already used by genuine Shahkot Business Directory records.
    if (bs1270_table('businesses') && isset(bs1270_cols('businesses')['city_id'])) {
        try {
            $bcols = bs1270_cols('businesses');
            $parts=[]; $p=[];
            foreach (['area','address','name'] as $c) if (isset($bcols[$c])) { $parts[]='LOWER(`'.$c.'`) LIKE ?'; $p[]='%shahkot%'; }
            if ($parts) {
                $q=db()->prepare('SELECT city_id,COUNT(*) n FROM businesses WHERE city_id IS NOT NULL AND city_id>0 AND ('.implode(' OR ',$parts).') GROUP BY city_id ORDER BY n DESC LIMIT 1');
                $q->execute($p); $id=(int)($q->fetchColumn()?:0); if($id>0)return $id;
            }
        } catch(Throwable $e) {}
    }

    // Create the Shahkot city row only when the schema can be satisfied safely from known facts.
    $known = [
        'name'=>'Shahkot','city_name'=>'Shahkot','title'=>'Shahkot','slug'=>'shahkot','code'=>'SHAHKOT',
        'country'=>'Pakistan','country_name'=>'Pakistan','country_code'=>'PK','province'=>'Punjab','state'=>'Punjab',
        'district'=>'Nankana Sahib','latitude'=>'31.5708985233','longitude'=>'73.4853065014',
        'lat'=>'31.5708985233','lng'=>'73.4853065014','status'=>'active','is_active'=>1,'active'=>1,
        'tenant_id'=>$tid ?: 0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')
    ];
    $row=[]; $missing=[];
    foreach($cols as $c=>$meta){
        if($c==='id' || str_contains(strtolower((string)($meta['EXTRA']??'')),'auto_increment')) continue;
        if(array_key_exists($c,$known)) $row[$c]=$known[$c];
        $required = strtoupper((string)($meta['IS_NULLABLE']??'YES'))==='NO' && ($meta['COLUMN_DEFAULT']??null)===null && !str_contains(strtolower((string)($meta['EXTRA']??'')),'auto_increment');
        if($required && !array_key_exists($c,$row)) $missing[]=$c;
    }
    if(!$missing){
        try{$id=bs1270_insert('cities',$row);if($id>0){bs1270_audit('city.shahkot_created',0,0,['city_id'=>$id]);return $id;}}catch(Throwable $e){}
    }
    throw new RuntimeException('Shahkot city record could not be resolved safely. Open Business Directory once and ensure Shahkot exists in the existing Cities list.');
}

function bs1276_nullable_value(string $table,string $column,string $value) {
    $value=trim($value); if($value!=='') return $value;
    $cols=bs1270_cols($table); if(!isset($cols[$column])) return '';
    return strtoupper((string)($cols[$column]['IS_NULLABLE']??'NO'))==='YES' ? null : '';
}

function bs1276_user_create(string $businessName,array $source,string $temp): int {
    if(!bs1270_table('users')) throw new RuntimeException('Users table unavailable.');
    $cols=bs1270_cols('users');
    $phone=trim((string)($source['phone']??''));
    $email=trim((string)($source['email']??''));
    if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL))$email='';
    if($email!==''&&isset($cols['email'])){try{$q=db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');$q->execute([$email]);if($u=(int)($q->fetchColumn()?:0))return $u;}catch(Throwable $e){}}
    if($phone!==''&&isset($cols['phone'])){try{$q=db()->query("SELECT id,phone FROM users WHERE phone IS NOT NULL AND phone<>'' ORDER BY id DESC LIMIT 1000");foreach($q->fetchAll() as $r)if(bs1270_norm_phone((string)$r['phone'])===bs1270_norm_phone($phone))return (int)$r['id'];}catch(Throwable $e){}}
    if($email==='')$email='import+'.substr(hash('sha256',$businessName.'|'.($phone?:'no-phone').'|'.microtime(true)),0,18).'@shopkeeper.shahkotpk.local';
    $hash=password_hash($temp,PASSWORD_DEFAULT);
    $data=['name'=>$businessName.' Shopkeeper','email'=>$email,'password'=>$hash,'password_hash'=>$hash,'role'=>'seller','role_key'=>'seller','status'=>'active','is_verified'=>1,'verified'=>1,'must_reset_password'=>0];
    if(isset($cols['phone'])) $data['phone']=bs1276_nullable_value('users','phone',$phone);
    $uid=bs1270_insert('users',$data); if($uid<1)throw new RuntimeException('Unable to create shopkeeper user.');
    if(function_exists('bs1283_normalize_user'))bs1283_normalize_user($uid,$businessName,$temp);
    if(function_exists('bm1140_ensure_membership'))bm1140_ensure_membership($uid);elseif(bs1270_table('tenant_members')&&bs1270_tid()>0){try{bs1270_insert('tenant_members',['tenant_id'=>bs1270_tid(),'user_id'=>$uid,'member_role'=>'seller','role_key'=>'seller','role'=>'seller','status'=>1]);}catch(Throwable $e){}}
    return $uid;
}

function bs1276_save_import_business(array $p): array {
    $bm=dirname(__DIR__).'/app/business_admin_v1140.php'; if(is_file($bm)) require_once $bm;
    if(!function_exists('bm1140_unique_slug')) throw new RuntimeException('Existing Professional Business Manager helper is unavailable.');
    if(!bs1270_table('businesses')) throw new RuntimeException('Businesses table is unavailable.');
    $owner=(int)($p['owner_user_id']??0); if($owner<1)throw new RuntimeException('Please select or create a shopkeeper / business owner.');
    if(function_exists('bm1140_ensure_membership'))bm1140_ensure_membership($owner);
    $name=trim((string)($p['name']??'')); if($name==='')throw new RuntimeException('Business name is required.');
    $city=(int)($p['city_id']??0); if($city<1)throw new RuntimeException('A valid Shahkot city_id is required.');
    $category=(int)($p['category_id']??0);
    $slug=function_exists('bm1140_unique_slug')?bm1140_unique_slug($name,0):strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
    $phone=trim((string)($p['phone']??''));
    $businessData=[
      'tenant_id'=>bs1270_tid()?:null,'owner_id'=>$owner,'owner_user_id'=>$owner,'city_id'=>$city,'category_id'=>$category?:null,
      'name'=>$name,'slug'=>$slug,'description'=>trim((string)($p['description']??'')),'tagline'=>trim((string)($p['tagline']??'')),
      'phone'=>bs1276_nullable_value('businesses','phone',$phone),'whatsapp'=>bs1276_nullable_value('businesses','whatsapp',trim((string)($p['whatsapp']??''))),
      'email'=>bs1276_nullable_value('businesses','email',trim((string)($p['email']??''))),'website'=>bs1276_nullable_value('businesses','website',trim((string)($p['website']??''))),
      'address'=>trim((string)($p['address']??'')),'area'=>trim((string)($p['area']??'Shahkot')),'latitude'=>trim((string)($p['latitude']??'')),'longitude'=>trim((string)($p['longitude']??'')),
      'image'=>'','image_url'=>'','thumbnail_url'=>'','cover_url'=>'','verification_status'=>'pending','verified'=>0,'is_verified'=>0,'featured'=>0,'is_featured'=>0,'status'=>(string)($p['status']??'pending')
    ];
    $pdo=db();$pdo->beginTransaction();
    try{
      $bid=bs1270_insert('businesses',$businessData); if($bid<1)throw new RuntimeException('Unable to create business.');
      $hours=[];$services=[];
      if(function_exists('bm1140_upsert_business_detail')) bm1140_upsert_business_detail($bid,['business_id'=>$bid,'tagline'=>'','website'=>trim((string)($p['website']??'')),'map_url'=>trim((string)($p['map_url']??'')),'opening_hours'=>json_encode($hours),'hours'=>json_encode($hours),'working_hours'=>json_encode($hours),'price_range'=>'','latitude'=>trim((string)($p['latitude']??'')),'longitude'=>trim((string)($p['longitude']??'')),'services_json'=>$services]);
      $shop=0;if(function_exists('bm1140_link_shop'))$shop=bm1140_link_shop($bid,$owner,$p,'');
      if(function_exists('bm1140_save_profile')){ $pp=$p;$pp['shop_id']=$shop;$pp['owner_user_id']=$owner;$pp['image_existing']='';bm1140_save_profile($bid,$pp); }
      if(function_exists('bm1140_microsite'))bm1140_microsite($bid,$p);
      $pdo->commit();return ['id'=>$bid,'shop_id'=>$shop,'owner_user_id'=>$owner,'image'=>''];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}

function bs1276_import_candidate_admin_approved(int $candidateId): array {
    if($candidateId<1) throw new RuntimeException('Invalid candidate.');
    $live=bs1270_live_candidate($candidateId);
    $c=(array)($live['candidate']??[]); $g=(array)($live['google']??[]); $geo=(array)($live['geo']??[]); $web=(array)($live['website']??[]);
    if(empty($geo['ok'])) throw new RuntimeException('Add blocked: this place is outside the strict Shahkot, Punjab, Pakistan boundary.');
    $status=strtoupper(trim((string)($g['businessStatus']??'')));if($status==='CLOSED_PERMANENTLY')throw new RuntimeException('Add blocked: Google marks this business permanently closed.');
    $confidence=(int)($live['confidence']??0); if($confidence<60)throw new RuntimeException('Add blocked: confidence is below 60%. Verify the candidate again before approval.');
    $source=!empty($web['verified'])?$web:bs1275_google_source_record($g);
    if(trim((string)($source['name']??''))==='')throw new RuntimeException('Add blocked: Google did not return a usable business name.');
    if(trim((string)($source['phone']??''))===''&&trim((string)($source['address']??''))==='')throw new RuntimeException('Add blocked: not enough live contact/location data was returned to identify the business safely.');
    $match=bs1270_existing_match($source,$g);
    if($match>0){
      bs1270_link_business($match,(string)($c['provider_place_id']??''),!empty($web['verified'])?'official_website':'google_admin_approved');
      if(function_exists('bs1273_mark_business_source'))bs1273_mark_business_source($match,false,bs1273_business_owner_id($match),'matched');
      bs1270_update('business_source_candidates_v1270',$candidateId,['status'=>'matched','matched_business_id'=>$match,'confidence'=>$confidence,'updated_at'=>date('Y-m-d H:i:s')]);
      $repair=function_exists('bs1277_finalize_google_business')?bs1277_finalize_google_business($match,$candidateId,$g,false):[]; if(function_exists('bs1284_sync_business_geo')){try{$repair['geo_v1284']=bs1284_sync_business_geo($match,true);}catch(Throwable $ignored){}}
      bs1270_audit('candidate.admin_linked',$match,$candidateId,['confidence'=>$confidence,'geo_repair'=>$repair]);
      return ['business_id'=>$match,'user_id'=>0,'temp_password'=>'','matched'=>true,'admin_approved'=>true,'repair'=>$repair];
    }
    $settings=bs1270_setting_row();$temp=bs1270_random_password();
    $uid=!empty($settings['create_shopkeeper'])?bs1276_user_create((string)$source['name'],$source,$temp):0;
    if(!empty($settings['create_shopkeeper'])&&$uid<1)throw new RuntimeException('Shopkeeper account could not be created.');
    $cityId=bs1276_resolve_shahkot_city_id();
    $p=['business_id'=>0,'name'=>(string)$source['name'],'slug'=>'','category_id'=>(int)($c['category_id']??0),'city_id'=>$cityId,'tagline'=>'','description'=>'','owner_user_id'=>$uid,
      'phone'=>(string)($source['phone']??''),'whatsapp'=>(string)($source['phone']??''),'email'=>(string)($source['email']??''),'website'=>(string)($source['website']??''),'address'=>(string)($source['address']??''),'area'=>'Shahkot',
      'latitude'=>(string)($source['latitude']??''),'longitude'=>(string)($source['longitude']??''),'map_url'=>(string)($g['googleMapsUri']??''),'services'=>'','gallery_urls'=>'','image_existing'=>'',
      'create_marketplace_shop'=>!empty($settings['create_marketplace_shop'])?1:0,'microsite_enabled'=>!empty($settings['create_business_website'])?1:0,'microsite_ai'=>1,'microsite_theme'=>'auto','verified'=>0,'featured'=>0,
      'status'=>!empty($settings['auto_publish_verified'])?'active':'pending','internal_notes'=>'Admin-approved Shahkot Google Places source-linked import (v12.7.6). Live source was re-verified inside the strict Shahkot boundary. Google photo content is not copied into local storage.'
    ];
    $r=bs1276_save_import_business($p);$bid=(int)($r['id']??0);if($bid<1)throw new RuntimeException('Business creation failed.');
    bs1270_link_business($bid,(string)($c['provider_place_id']??''),!empty($web['verified'])?'official_website':'google_admin_approved');
    if(function_exists('bs1273_mark_business_source'))bs1273_mark_business_source($bid,true,$uid,'imported');
    bs1270_update('business_source_candidates_v1270',$candidateId,['status'=>'imported','matched_business_id'=>$bid,'confidence'=>$confidence,'updated_at'=>date('Y-m-d H:i:s')]);
    if($uid>0)bs1270_store_credential($bid,$uid,$temp);
    $repair=function_exists('bs1277_finalize_google_business')?bs1277_finalize_google_business($bid,$candidateId,$g,true):[]; if(function_exists('bs1284_sync_business_geo')){try{$repair['geo_v1284']=bs1284_sync_business_geo($bid,true);}catch(Throwable $ignored){}}
    bs1270_audit('candidate.admin_imported',$bid,$candidateId,['user_id'=>$uid,'category_id'=>(int)($c['category_id']??0),'city_id'=>$cityId,'confidence'=>$confidence,'post_import_repair'=>$repair]);
    return ['business_id'=>$bid,'user_id'=>$uid,'temp_password'=>$uid>0?$temp:'','matched'=>false,'admin_approved'=>true,'repair'=>$repair];
}

function bs1276_candidate_bulk(array $ids,string $action): array {
    $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($v)=>$v>0)));
    if(!$ids)throw new RuntimeException('Select at least one candidate.');
    $action=trim($action);if(!in_array($action,['verify','import','reject'],true))throw new RuntimeException('Select a valid candidate bulk action.');
    $limit=$action==='reject'?100:20;if(count($ids)>$limit)throw new RuntimeException(($action==='reject'?'Reject':'Verify/Add').' supports up to '.$limit.' selected candidates per run.');
    if(function_exists('set_time_limit')){@set_time_limit(120);} @ini_set('max_execution_time','120');
    $ok=0;$matched=0;$imported=0;$outside=0;$errors=[];
    foreach($ids as $id){try{
      if($action==='verify'){$r=bs1270_live_candidate($id);if(empty($r['geo']['ok']))$outside++;else{$ok++;if(!empty($r['matched_business_id']))$matched++;}}
      elseif($action==='import'){$r=bs1276_import_candidate_admin_approved($id);$ok++;if(!empty($r['matched']))$matched++;else$imported++;}
      else{bs1270_update('business_source_candidates_v1270',$id,['status'=>'rejected','updated_at'=>date('Y-m-d H:i:s')]);$ok++;}
    }catch(Throwable $e){$errors[]='Candidate #'.$id.': '.$e->getMessage();}}
    if($action==='verify')$message='Bulk verify completed: '.$ok.' Shahkot candidates verified'.($matched?' · '.$matched.' matched existing businesses':'').($outside?' · '.$outside.' outside-city blocked':'').'.';
    elseif($action==='import')$message='Bulk add/link completed: '.$imported.' new businesses added · '.$matched.' existing businesses linked. Auto-created shopkeeper passwords can be revealed from Managed Imports.';
    else$message='Bulk reject completed: '.$ok.' candidates rejected.';
    if($errors)$message.=' Skipped: '.implode(' | ',array_slice($errors,0,8));
    return ['processed'=>$ok,'matched'=>$matched,'imported'=>$imported,'outside'=>$outside,'errors'=>$errors,'message'=>$message];
}
}
