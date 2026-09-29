<?php
declare(strict_types=1);
/** ShahkotPK v12.7.5 — candidate verify/import workflow helpers. */
if (!function_exists('bs1275_google_source_record')) {
function bs1275_google_source_record(array $g): array {
    $name=trim((string)($g['displayName']['text']??''));
    $phone=trim((string)($g['nationalPhoneNumber']??$g['internationalPhoneNumber']??''));
    $address=trim((string)($g['formattedAddress']??''));
    $website=trim((string)($g['websiteUri']??''));
    $lat=(string)($g['location']['latitude']??'');
    $lng=(string)($g['location']['longitude']??'');
    return ['verified'=>false,'name'=>$name,'phone'=>$phone,'email'=>'','website'=>$website,'address'=>$address,'latitude'=>$lat,'longitude'=>$lng,'image'=>''];
}
function bs1275_import_candidate_admin_approved(int $candidateId): array {
    if($candidateId<1) throw new RuntimeException('Invalid candidate.');
    $live=bs1270_live_candidate($candidateId);
    $c=(array)($live['candidate']??[]); $g=(array)($live['google']??[]); $geo=(array)($live['geo']??[]); $web=(array)($live['website']??[]);
    if(empty($geo['ok'])) throw new RuntimeException('Add blocked: this place is outside the strict Shahkot, Punjab, Pakistan boundary.');
    $status=strtoupper(trim((string)($g['businessStatus']??'')));
    if($status==='CLOSED_PERMANENTLY') throw new RuntimeException('Add blocked: Google marks this business permanently closed.');
    if(!empty($web['verified'])) return bs1270_import_candidate($candidateId);
    $confidence=(int)($live['confidence']??0);
    if($confidence<60) throw new RuntimeException('Add blocked: confidence is below 60%. Verify the candidate again before approval.');
    $source=bs1275_google_source_record($g);
    if(trim((string)$source['name'])==='') throw new RuntimeException('Add blocked: Google did not return a usable business name.');
    if(trim((string)$source['phone'])==='' && trim((string)$source['address'])==='') throw new RuntimeException('Add blocked: not enough live contact/location data was returned to identify the business safely.');
    $match=bs1270_existing_match($source,$g);
    if($match>0){
        bs1270_link_business($match,(string)($c['provider_place_id']??''),'google_admin_approved');
        if(function_exists('bs1273_mark_business_source')) bs1273_mark_business_source($match,false,bs1273_business_owner_id($match),'matched');
        bs1270_update('business_source_candidates_v1270',$candidateId,['status'=>'matched','matched_business_id'=>$match,'confidence'=>$confidence,'updated_at'=>date('Y-m-d H:i:s')]);
        bs1270_audit('candidate.admin_linked',$match,$candidateId,['confidence'=>$confidence,'verification_mode'=>'google_admin_approved']);
        return ['business_id'=>$match,'user_id'=>0,'temp_password'=>'','matched'=>true,'admin_approved'=>true];
    }
    $settings=bs1270_setting_row();
    $temp=bs1270_random_password();
    $uid=!empty($settings['create_shopkeeper'])?bs1270_user_create((string)$source['name'],$source,$temp):0;
    if(!empty($settings['create_shopkeeper']) && $uid<1) throw new RuntimeException('Shopkeeper account could not be created.');
    $bm=dirname(__DIR__).'/app/business_admin_v1140.php'; if(is_file($bm)) require_once $bm;
    if(!function_exists('bm1140_save')) throw new RuntimeException('Existing Professional Business Manager helper is unavailable.');
    $p=[
      'business_id'=>0,'name'=>(string)$source['name'],'slug'=>'','category_id'=>(int)($c['category_id']??0),'city_id'=>0,
      'tagline'=>'','description'=>'','owner_user_id'=>$uid,'phone'=>(string)$source['phone'],'whatsapp'=>(string)$source['phone'],
      'email'=>'','website'=>(string)$source['website'],'address'=>(string)$source['address'],'area'=>'Shahkot',
      'latitude'=>(string)$source['latitude'],'longitude'=>(string)$source['longitude'],'map_url'=>(string)($g['googleMapsUri']??''),
      'services'=>'','gallery_urls'=>'','image_existing'=>'',
      'create_marketplace_shop'=>!empty($settings['create_marketplace_shop'])?1:0,
      'microsite_enabled'=>!empty($settings['create_business_website'])?1:0,'microsite_ai'=>1,'microsite_theme'=>'auto',
      'verified'=>0,'featured'=>0,'status'=>!empty($settings['auto_publish_verified'])?'active':'pending',
      'internal_notes'=>'Admin-approved Shahkot Google Places source-linked import (v12.7.5). Candidate was live re-verified inside the strict Shahkot boundary. Google photo content is not copied into local storage. Owner/admin should confirm business details after account claim.'
    ];
    $r=bm1140_save($p); $bid=(int)($r['id']??0); if($bid<1) throw new RuntimeException('Business creation failed.');
    bs1270_link_business($bid,(string)($c['provider_place_id']??''),'google_admin_approved');
    if(function_exists('bs1273_mark_business_source')) bs1273_mark_business_source($bid,true,$uid,'imported');
    bs1270_update('business_source_candidates_v1270',$candidateId,['status'=>'imported','matched_business_id'=>$bid,'confidence'=>$confidence,'updated_at'=>date('Y-m-d H:i:s')]);
    if($uid>0) bs1270_store_credential($bid,$uid,$temp);
    bs1270_audit('candidate.admin_imported',$bid,$candidateId,['user_id'=>$uid,'category_id'=>(int)($c['category_id']??0),'confidence'=>$confidence,'verification_mode'=>'google_admin_approved']);
    return ['business_id'=>$bid,'user_id'=>$uid,'temp_password'=>$uid>0?$temp:'','matched'=>false,'admin_approved'=>true];
}
function bs1275_candidate_bulk(array $ids,string $action): array {
    $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($v)=>$v>0)));
    if(!$ids) throw new RuntimeException('Select at least one candidate.');
    $action=trim($action); if(!in_array($action,['verify','import','reject'],true)) throw new RuntimeException('Select a valid candidate bulk action.');
    $networkAction=in_array($action,['verify','import'],true);
    if($networkAction && count($ids)>3) throw new RuntimeException('Verify/Add supports up to 3 candidates per batch on this server. Select 3 or fewer and run the next batch after it finishes.');
    if($action==='reject' && count($ids)>100) $ids=array_slice($ids,0,100);
    $ok=0;$matched=0;$imported=0;$outside=0;$errors=[];
    foreach($ids as $id){
      try{
        if($action==='verify'){
          $r=bs1270_live_candidate($id); if(empty($r['geo']['ok']))$outside++; else {$ok++; if(!empty($r['matched_business_id']))$matched++;}
        }elseif($action==='import'){
          $r=bs1275_import_candidate_admin_approved($id);$ok++; if(!empty($r['matched']))$matched++;else$imported++;
        }else{
          bs1270_update('business_source_candidates_v1270',$id,['status'=>'rejected','updated_at'=>date('Y-m-d H:i:s')]);$ok++;
        }
      }catch(Throwable $e){$errors[]='Candidate #'.$id.': '.$e->getMessage();}
    }
    if($action==='verify')$message='Bulk verify completed: '.$ok.' Shahkot candidates verified'.($matched?' · '.$matched.' matched existing businesses':'').($outside?' · '.$outside.' outside-city blocked':'').'.';
    elseif($action==='import')$message='Bulk add/link completed: '.$imported.' new businesses added · '.$matched.' existing businesses linked. Auto-created shopkeeper passwords can be revealed from Managed Imports.';
    else $message='Bulk reject completed: '.$ok.' candidates rejected.';
    if($errors)$message.=' Skipped: '.implode(' | ',array_slice($errors,0,3));
    return ['processed'=>$ok,'matched'=>$matched,'imported'=>$imported,'outside'=>$outside,'errors'=>$errors,'message'=>$message];
}
}
