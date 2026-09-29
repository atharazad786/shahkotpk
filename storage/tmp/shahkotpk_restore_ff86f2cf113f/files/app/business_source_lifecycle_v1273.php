<?php
declare(strict_types=1);
$bs1278=__DIR__.'/business_publish_index_v1278.php';if(is_file($bs1278))require_once $bs1278;
/** ShahkotPK v12.7.3 — lifecycle controls for Google-source-linked Business Directory records. */
if(!function_exists('bs1270_h')){ $f=__DIR__.'/business_source_import_v1270.php'; if(is_file($f)) require_once $f; }
if(!function_exists('bs1273_source_tag')){
function bs1273_now(): string { return date('Y-m-d H:i:s'); }
function bs1273_lifecycle(int $bid): array {
 if($bid<1||!bs1270_table('business_source_links_v1270'))return [];
 try{
  $q=db()->prepare('SELECT l.*,x.imported_by_extractor,x.owner_user_id lifecycle_owner,x.source_tag,x.managed_status,x.last_admin_edit_at,x.last_owner_edit_at FROM business_source_links_v1270 l LEFT JOIN business_source_lifecycle_v1273 x ON x.tenant_id=l.tenant_id AND x.business_id=l.business_id WHERE l.business_id=? AND l.tenant_id IN (?,0) AND l.provider=? ORDER BY l.tenant_id DESC LIMIT 1');
  $q->execute([$bid,bs1270_tid(),'google_places']);$r=$q->fetch()?:[];
  if(!$r)return [];
  if(!isset($r['imported_by_extractor'])||$r['imported_by_extractor']===null){
   $r['imported_by_extractor']=0;
   try{$s=db()->prepare("SELECT 1 FROM business_source_candidates_v1270 WHERE tenant_id IN (?,0) AND provider=? AND provider_place_id=? AND matched_business_id=? AND status='imported' LIMIT 1");$s->execute([bs1270_tid(),'google_places',(string)$r['provider_place_id'],$bid]);$r['imported_by_extractor']=$s->fetchColumn()?1:0;}catch(Throwable $e){}
  }
  return $r;
 }catch(Throwable $e){return [];}
}
function bs1273_source_tag(int $bid): array {
 $r=bs1273_lifecycle($bid); if(!$r)return ['linked'=>false,'imported'=>false,'label'=>'','place_id'=>'','last_sync'=>''];
 $imported=!empty($r['imported_by_extractor']);
 return ['linked'=>true,'imported'=>$imported,'label'=>$imported?'Google-imported · Managed listing':'Google source-linked','place_id'=>(string)($r['provider_place_id']??''),'last_sync'=>(string)($r['last_website_sync_at']??$r['last_source_check_at']??''),'managed_status'=>(string)($r['managed_status']??'linked')];
}
function bs1273_source_tags(array $ids): array {
 $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($x)=>$x>0)));if(!$ids)return [];
 $out=[];foreach($ids as $id)$out[$id]=bs1273_source_tag($id);return $out;
}
function bs1273_business_owner_id(int $bid): int {
 if($bid<1||!bs1270_table('businesses'))return 0;
 $c=bs1270_cols('businesses');$field=isset($c['owner_user_id'])?'owner_user_id':(isset($c['owner_id'])?'owner_id':'');if($field==='')return 0;
 try{$q=db()->prepare('SELECT `'.$field.'` FROM businesses WHERE id=? LIMIT 1');$q->execute([$bid]);return (int)($q->fetchColumn()?:0);}catch(Throwable $e){return 0;}
}
function bs1273_mark_business_source(int $bid,bool $imported,int $ownerUid=0,string $status='linked'): void {
 if($bid<1||!bs1270_table('business_source_lifecycle_v1273'))return;
 $tid=bs1270_tid(); if($ownerUid<1)$ownerUid=bs1273_business_owner_id($bid);
 try{$q=db()->prepare('SELECT id FROM business_source_lifecycle_v1273 WHERE tenant_id=? AND business_id=? LIMIT 1');$q->execute([$tid,$bid]);$id=(int)($q->fetchColumn()?:0);
  $d=['tenant_id'=>$tid,'business_id'=>$bid,'imported_by_extractor'=>$imported?1:0,'owner_user_id'=>$ownerUid?:null,'source_tag'=>$imported?'google_imported':'google_linked','managed_status'=>$status,'updated_at'=>bs1273_now()];
  if($id)bs1270_update('business_source_lifecycle_v1273',$id,$d);else{$d['created_at']=bs1273_now();bs1270_insert('business_source_lifecycle_v1273',$d);}
 }catch(Throwable $e){}
}
function bs1273_managed_businesses(string $search='',int $limit=200): array {
 if(!bs1270_table('business_source_links_v1270')||!bs1270_table('businesses'))return [];
 $bc=bs1270_cols('businesses');$owner=isset($bc['owner_user_id'])?'owner_user_id':(isset($bc['owner_id'])?'owner_id':'');
 $joins=' LEFT JOIN business_source_lifecycle_v1273 x ON x.tenant_id=l.tenant_id AND x.business_id=l.business_id';
 if(bs1270_table('categories')&&isset($bc['category_id']))$joins.=' LEFT JOIN categories c ON c.id=b.category_id';
 if($owner!==''&&bs1270_table('users'))$joins.=' LEFT JOIN users u ON u.id=b.`'.$owner.'`';
 $sel='l.id source_link_id,l.provider_place_id,l.last_source_check_at,l.last_website_sync_at,l.verification_mode,b.*,.0 dummy';$sel=str_replace(', .0','',$sel);
 if(bs1270_table('categories')&&isset($bc['category_id']))$sel.=',c.name category_name';
 if($owner!==''&&bs1270_table('users'))$sel.=',u.name owner_name,u.email owner_email,u.phone owner_phone';
 $sel.=',COALESCE(x.imported_by_extractor,0) imported_by_extractor,x.source_tag,x.managed_status,x.last_admin_edit_at,x.last_owner_edit_at';
 $w=['l.provider=?','l.tenant_id IN (?,0)'];$p=['google_places',bs1270_tid()];
 if($search!==''&&isset($bc['name'])){$parts=['b.name LIKE ?'];$p[]='%'.$search.'%';foreach(['phone','email','address'] as $f)if(isset($bc[$f])){$parts[]='b.`'.$f.'` LIKE ?';$p[]='%'.$search.'%';}$w[]='('.implode(' OR ',$parts).')';}
 try{$q=db()->prepare('SELECT '.$sel.' FROM business_source_links_v1270 l JOIN businesses b ON b.id=l.business_id'.$joins.' WHERE '.implode(' AND ',$w).' ORDER BY l.id DESC LIMIT '.max(1,min(500,$limit)));$q->execute($p);$rows=$q->fetchAll()?:[];foreach($rows as &$r){if(empty($r['imported_by_extractor'])){try{$s=db()->prepare("SELECT 1 FROM business_source_candidates_v1270 WHERE tenant_id IN (?,0) AND provider_place_id=? AND matched_business_id=? AND status='imported' LIMIT 1");$s->execute([bs1270_tid(),(string)$r['provider_place_id'],(int)$r['id']]);}catch(Throwable $e){}}}unset($r);return $rows;}catch(Throwable $e){return [];}
}
function bs1273_managed_business(int $bid): array {foreach(bs1273_managed_businesses('',500) as $r)if((int)$r['id']===$bid)return $r;return [];}
function bs1273_user_owns_business(int $bid,int $uid): bool {return $bid>0&&$uid>0&&bs1273_business_owner_id($bid)===$uid;}
function bs1273_upload_business_image(string $field,string $old=''): string {
 if(empty($_FILES[$field])||!is_array($_FILES[$field])||((int)($_FILES[$field]['error']??UPLOAD_ERR_NO_FILE))===UPLOAD_ERR_NO_FILE)return $old;
 $f=$_FILES[$field];if((int)$f['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Business image upload failed.');if((int)$f['size']>8*1024*1024)throw new RuntimeException('Business image must be 8 MB or smaller.');
 $fi=new finfo(FILEINFO_MIME_TYPE);$mime=(string)$fi->file((string)$f['tmp_name']);$map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($map[$mime]))throw new RuntimeException('Use JPG, PNG or WebP business images.');
 $dir=dirname(__DIR__).'/uploads/businesses/imported';if(!is_dir($dir)&&!@mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('Business image upload directory is not writable.');
 $name='biz-'.date('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$map[$mime];$dest=$dir.'/'.$name;if(!move_uploaded_file((string)$f['tmp_name'],$dest))throw new RuntimeException('Unable to store business image.');return '/uploads/businesses/imported/'.$name;
}
function bs1273_save_profile_payload(int $bid,array $p): void {
 if($bid<1)return;
 $services=array_values(array_filter(array_map('trim',preg_split('/[\r\n]+/',(string)($p['services']??''))?:[])));
 $hours=trim((string)($p['hours_text']??''));
 if(bs1270_table('business_details')){try{$q=db()->prepare('SELECT id FROM business_details WHERE business_id=? LIMIT 1');$q->execute([$bid]);$id=(int)($q->fetchColumn()?:0);$d=['business_id'=>$bid,'tagline'=>trim((string)($p['tagline']??'')),'website'=>trim((string)($p['website']??'')),'map_url'=>trim((string)($p['map_url']??'')),'opening_hours'=>$hours,'hours'=>$hours,'working_hours'=>$hours,'services_json'=>json_encode($services,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)];if($id)bs1270_update('business_details',$id,$d);else bs1270_insert('business_details',$d);}catch(Throwable $e){}}
 if(bs1270_table('business_admin_profiles_v1140')){try{$q=db()->prepare('SELECT id FROM business_admin_profiles_v1140 WHERE business_id=? AND tenant_id IN (?,0) ORDER BY tenant_id DESC LIMIT 1');$q->execute([$bid,bs1270_tid()]);$id=(int)($q->fetchColumn()?:0);$d=['tenant_id'=>bs1270_tid(),'business_id'=>$bid,'services_json'=>json_encode($services,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'hours_json'=>json_encode(['text'=>$hours],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'updated_by'=>bs1270_uid()?:null,'updated_at'=>bs1273_now()];if($id)bs1270_update('business_admin_profiles_v1140',$id,$d);else{$d['created_at']=bs1273_now();bs1270_insert('business_admin_profiles_v1140',$d);}}catch(Throwable $e){}}
}
function bs1273_update_business(int $bid,array $p,int $actorUid=0,bool $ownerMode=false): array {
 if($bid<1||!bs1270_table('businesses'))throw new RuntimeException('Business not found.');$tag=bs1273_source_tag($bid);if(!$tag['linked'])throw new RuntimeException('This business is not managed by the Business Importer.');
 if($ownerMode&&!bs1273_user_owns_business($bid,$actorUid))throw new RuntimeException('You do not manage this business.');
 try{$q=db()->prepare('SELECT * FROM businesses WHERE id=? LIMIT 1');$q->execute([$bid]);$b=$q->fetch()?:[];}catch(Throwable $e){$b=[];}if(!$b)throw new RuntimeException('Business not found.');
 $name=trim((string)($p['name']??$b['name']??''));if($name==='')throw new RuntimeException('Business name is required.');
 $email=trim((string)($p['email']??''));if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Business email is invalid.');$website=trim((string)($p['website']??''));if($website!==''&&!filter_var($website,FILTER_VALIDATE_URL))throw new RuntimeException('Business website URL is invalid.');
 $d=['name'=>$name,'category_id'=>(int)($p['category_id']??0)?:null,'description'=>trim((string)($p['description']??'')),'tagline'=>trim((string)($p['tagline']??'')),'phone'=>trim((string)($p['phone']??'')),'whatsapp'=>trim((string)($p['whatsapp']??'')),'email'=>$email,'website'=>$website,'address'=>trim((string)($p['address']??'')),'area'=>trim((string)($p['area']??'')),'latitude'=>trim((string)($p['latitude']??'')),'longitude'=>trim((string)($p['longitude']??''))];
 if(!$ownerMode&&isset($p['status']))$d['status']=(string)$p['status'];
 $old=(string)($b['image_url']??$b['image']??'');$image=bs1273_upload_business_image('business_image',$old);if($image!==$old)$d+=['image'=>$image,'image_url'=>$image,'thumbnail_url'=>$image,'cover_url'=>$image];
 bs1270_update('businesses',$bid,$d);bs1273_save_profile_payload($bid,$p);
 if(bs1270_table('business_source_lifecycle_v1273')){try{$q=db()->prepare('SELECT id FROM business_source_lifecycle_v1273 WHERE tenant_id=? AND business_id=? LIMIT 1');$q->execute([bs1270_tid(),$bid]);$id=(int)($q->fetchColumn()?:0);$u=['owner_user_id'=>bs1273_business_owner_id($bid)?:null,'managed_status'=>$ownerMode?'owner_edited':'admin_edited','updated_at'=>bs1273_now(),$ownerMode?'last_owner_edit_at':'last_admin_edit_at'=>bs1273_now()];if($id)bs1270_update('business_source_lifecycle_v1273',$id,$u);else bs1273_mark_business_source($bid,!empty($tag['imported']),bs1273_business_owner_id($bid),$ownerMode?'owner_edited':'admin_edited');}catch(Throwable $e){}}
 if(function_exists('bs1278_finalize_business')){try{bs1278_finalize_business($bid,$ownerMode?'owner_edit':'admin_edit',false);}catch(Throwable $x){}}bs1270_audit($ownerMode?'business.owner_edited':'business.admin_edited',$bid,0,['fields'=>array_keys(array_filter($d,fn($v)=>$v!==''&&$v!==null))]);return ['business_id'=>$bid,'image'=>$image];
}
function bs1273_set_status(int $bid,string $status): void {if(!in_array($status,['active','pending','inactive'],true))throw new RuntimeException('Invalid status.');if(!bs1273_source_tag($bid)['linked'])throw new RuntimeException('Business is not source-linked.');bs1270_update('businesses',$bid,['status'=>$status]);bs1270_audit('business.bulk_status',$bid,0,['status'=>$status]);}
function bs1273_set_category(int $bid,int $cat): void {if($cat<1)throw new RuntimeException('Select a category.');if(!bs1273_source_tag($bid)['linked'])throw new RuntimeException('Business is not source-linked.');bs1270_update('businesses',$bid,['category_id'=>$cat]);bs1270_audit('business.bulk_category',$bid,0,['category_id'=>$cat]);}
function bs1273_dependency_summary(int $bid): array {
 $checks=[['store_products','business_id'],['local_deals_v1050','business_id'],['reviews','business_id'],['business_site_leads_v1170','business_id'],['business_crm_contacts_v1180','business_id'],['business_crm_conversations_v1180','business_id'],['business_quotes_v1180','business_id'],['business_followups_v1180','business_id'],['commerce_order_items_v1230','business_id'],['commerce_orders_v1230','business_id']];$out=[];
 foreach($checks as [$t,$c]){if(!bs1270_table($t)||!isset(bs1270_cols($t)[$c]))continue;try{$q=db()->prepare('SELECT COUNT(*) FROM `'.$t.'` WHERE `'.$c.'`=?');$q->execute([$bid]);$n=(int)$q->fetchColumn();if($n>0)$out[$t]=$n;}catch(Throwable $e){}}
 return $out;
}
function bs1273_delete_imported_business(int $bid): void {
 $life=bs1273_lifecycle($bid);if(!$life)throw new RuntimeException('No extractor source link exists.');if(empty($life['imported_by_extractor']))throw new RuntimeException('Delete blocked: this was an existing Business Directory record that was only source-linked. Use Edit/Archive instead.');
 $deps=bs1273_dependency_summary($bid);if($deps)throw new RuntimeException('Delete blocked because this business has operational data: '.implode(', ',array_map(fn($k,$v)=>$k.' '.$v,array_keys($deps),array_values($deps))).'. Set it Inactive instead.');
 $place=(string)($life['provider_place_id']??'');$pdo=db();$pdo->beginTransaction();try{
  bs1270_audit('business.import_delete',$bid,0,['place_id'=>$place]);
  foreach(['business_site_builder_v1170','business_microsites_v1130','business_admin_profiles_v1140','business_details','marketplace_shops_v550'] as $t){if(bs1270_table($t)&&isset(bs1270_cols($t)['business_id'])){$q=$pdo->prepare('DELETE FROM `'.$t.'` WHERE business_id=?');$q->execute([$bid]);}}
  if(bs1270_table('business_source_credentials_v1270')){$q=$pdo->prepare('DELETE FROM business_source_credentials_v1270 WHERE business_id=?');$q->execute([$bid]);}
  if(bs1270_table('business_source_lifecycle_v1273')){$q=$pdo->prepare('DELETE FROM business_source_lifecycle_v1273 WHERE business_id=? AND tenant_id IN (?,0)');$q->execute([$bid,bs1270_tid()]);}
  if(bs1270_table('business_source_links_v1270')){$q=$pdo->prepare('DELETE FROM business_source_links_v1270 WHERE business_id=? AND tenant_id IN (?,0)');$q->execute([$bid,bs1270_tid()]);}
  if($place!==''&&bs1270_table('business_source_candidates_v1270')){$q=$pdo->prepare("UPDATE business_source_candidates_v1270 SET status='verified',matched_business_id=NULL,updated_at=? WHERE provider_place_id=? AND matched_business_id=? AND tenant_id IN (?,0)");$q->execute([bs1273_now(),$place,$bid,bs1270_tid()]);}
  $q=$pdo->prepare('DELETE FROM businesses WHERE id=? LIMIT 1');$q->execute([$bid]);$pdo->commit();
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function bs1273_bulk(array $ids,string $action,int $categoryId=0): array {$ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($x)=>$x>0)));if(!$ids)throw new RuntimeException('Select at least one imported/source-linked business.');$ok=0;$errors=[];foreach($ids as $bid){try{if($action==='sync')bs1270_sync_business($bid);elseif($action==='active')bs1273_set_status($bid,'active');elseif($action==='pending')bs1273_set_status($bid,'pending');elseif($action==='inactive')bs1273_set_status($bid,'inactive');elseif($action==='category')bs1273_set_category($bid,$categoryId);elseif($action==='delete')bs1273_delete_imported_business($bid);else throw new RuntimeException('Choose a bulk action.');$ok++;}catch(Throwable $e){$errors[]='#'.$bid.' '.$e->getMessage();}}return ['processed'=>$ok,'errors'=>$errors];}
}
