<?php
declare(strict_types=1);
/** ShahkotPK v12.5.1 — Unified City Portal extension. */
if (!function_exists('sk1251_h')) {
function sk1251_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function sk1251_tid(): int { try { if(function_exists('tenant_id')) return (int)tenant_id(); if(function_exists('current_tenant')){$t=current_tenant();return (int)($t['id']??0);} } catch(Throwable $e){} return 0; }
function sk1251_table(string $t): bool { static $c=[]; if(isset($c[$t]))return $c[$t]; if(!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/',$t))return false; try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$t]);return $c[$t]=(bool)$q->fetchColumn();}catch(Throwable $e){return $c[$t]=false;} }
function sk1251_settings(): array {
    $d=['weather_enabled'=>1,'weather_title'=>'Live Shahkot Weather','latitude'=>'31.570900','longitude'=>'73.485300','cards_3d_enabled'=>1,'officers_enabled'=>1];
    if(!sk1251_table('city_portal_settings_v1251')) return $d;
    try{$tid=sk1251_tid();$q=db()->prepare('SELECT * FROM city_portal_settings_v1251 WHERE tenant_id IN (0,?) ORDER BY tenant_id DESC LIMIT 1');$q->execute([$tid]);return array_merge($d,$q->fetch()?:[]);}catch(Throwable $e){return $d;}
}
function sk1251_save_settings(array $d,int $uid=0): void {
    $tid=max(0,sk1251_tid());
    $lat=(float)($d['latitude']??31.5709);$lon=(float)($d['longitude']??73.4853);
    if($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) throw new InvalidArgumentException('Invalid weather coordinates.');
    db()->prepare('INSERT INTO city_portal_settings_v1251(tenant_id,weather_enabled,weather_title,latitude,longitude,cards_3d_enabled,officers_enabled,updated_by,updated_at) VALUES(?,?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE weather_enabled=VALUES(weather_enabled),weather_title=VALUES(weather_title),latitude=VALUES(latitude),longitude=VALUES(longitude),cards_3d_enabled=VALUES(cards_3d_enabled),officers_enabled=VALUES(officers_enabled),updated_by=VALUES(updated_by),updated_at=NOW()')->execute([$tid,!empty($d['weather_enabled'])?1:0,mb_substr(trim((string)($d['weather_title']??'Live Shahkot Weather')),0,160),$lat,$lon,!empty($d['cards_3d_enabled'])?1:0,!empty($d['officers_enabled'])?1:0,$uid?:null]);
}
function sk1251_upload(array $file,string $prefix='city'): string {
    $err=(int)($file['error']??UPLOAD_ERR_NO_FILE); if($err===UPLOAD_ERR_NO_FILE) return ''; if($err!==UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed.');
    $tmp=(string)($file['tmp_name']??'');$size=(int)($file['size']??0); if($tmp===''||!is_uploaded_file($tmp)) throw new RuntimeException('Invalid uploaded image.'); if($size<1||$size>5*1024*1024) throw new RuntimeException('Image must be under 5 MB.');
    $mime=''; if(function_exists('finfo_open')){$f=finfo_open(FILEINFO_MIME_TYPE);if($f){$mime=(string)finfo_file($f,$tmp);finfo_close($f);}} elseif(function_exists('mime_content_type')){$mime=(string)mime_content_type($tmp);} 
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp']; if(!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG and WEBP images are allowed.');
    $root=dirname(__DIR__);$dir=$root.'/uploads/city-information'; if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir)) throw new RuntimeException('Could not create city upload directory.');
    $name=preg_replace('/[^a-z0-9_-]+/i','-',trim($prefix))?:'city';$fn=$name.'-'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.'.$allowed[$mime];$dest=$dir.'/'.$fn;
    if(!move_uploaded_file($tmp,$dest)) throw new RuntimeException('Could not save uploaded image.'); return '/uploads/city-information/'.$fn;
}
function sk1251_media(bool $all=false): array {
    if(!sk1251_table('city_information_media_v1251')) return [];
    try{$tid=sk1251_tid();$q=db()->prepare('SELECT * FROM city_information_media_v1251 WHERE tenant_id IN (0,?) ORDER BY tenant_id DESC,sort_order ASC,id ASC');$q->execute([$tid]);$rows=$q->fetchAll()?:[];$seen=[];$out=[];foreach($rows as $r){$finger=(string)$r['id'].'@'.(string)$r['tenant_id'];if(isset($seen[$finger]))continue;$seen[$finger]=1;if(!$all&&!$r['enabled'])continue;$out[]=$r;}return $out;}catch(Throwable $e){return [];}
}
function sk1251_media_map(): array { $o=[];foreach(sk1251_media() as $m)$o[(string)$m['item_key']][]=$m;return $o; }
function sk1251_save_media(array $d,array $files): void {
    $item=preg_replace('/[^a-z0-9_:\-]/i','',(string)($d['item_key']??''));if($item==='') throw new InvalidArgumentException('Invalid media target.');
    $url='';if(isset($files['image']))$url=sk1251_upload($files['image'],'city-'.$item);if($url==='') throw new RuntimeException('Choose an image first.');
    $tid=max(0,sk1251_tid());db()->prepare('INSERT INTO city_information_media_v1251(tenant_id,item_key,media_type,file_url,caption,sort_order,enabled,created_at,updated_at) VALUES(?,?,\'image\',?,?,?,?,NOW(),NOW())')->execute([$tid,$item,$url,mb_substr(trim((string)($d['caption']??'')),0,240),max(0,min(9999,(int)($d['sort_order']??10))),1]);
}
function sk1251_delete_media(int $id): void { if($id<1)return;$tid=max(0,sk1251_tid());$q=db()->prepare('SELECT file_url FROM city_information_media_v1251 WHERE id=? AND tenant_id=? LIMIT 1');$q->execute([$id,$tid]);$url=(string)($q->fetchColumn()?:'');db()->prepare('DELETE FROM city_information_media_v1251 WHERE id=? AND tenant_id=?')->execute([$id,$tid]);if(str_starts_with($url,'/uploads/city-information/')){$f=dirname(__DIR__).$url;if(is_file($f))@unlink($f);} }
function sk1251_officers(bool $all=false): array {
    if(!sk1251_table('city_officers_v1251')) return [];
    try{$tid=sk1251_tid();$q=db()->prepare('SELECT * FROM city_officers_v1251 WHERE tenant_id=?'.($all?'':' AND enabled=1').' ORDER BY featured DESC,sort_order ASC,id ASC');$q->execute([max(0,$tid)]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}
}
function sk1251_save_officer(array $d,array $files,int $uid=0): void {
    $tid=max(0,sk1251_tid());$id=max(0,(int)($d['id']??0));$name=mb_substr(trim((string)($d['name']??'')),0,160);$des=mb_substr(trim((string)($d['designation']??'')),0,190);$vision=mb_substr(trim((string)($d['vision_message']??'')),0,1800);if($name===''||$des==='')throw new InvalidArgumentException('Officer name and designation are required.');
    $photo=mb_substr(trim((string)($d['existing_photo']??'')),0,500);if(isset($files['photo'])&&(int)($files['photo']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE)$photo=sk1251_upload($files['photo'],'officer');
    $vals=[$name,$des,$vision,$photo,mb_substr(trim((string)($d['office_name']??'')),0,190),mb_substr(trim((string)($d['phone']??'')),0,80),mb_substr(trim((string)($d['email']??'')),0,190),max(0,min(9999,(int)($d['sort_order']??10))),!empty($d['featured'])?1:0,!empty($d['enabled'])?1:0,$uid?:null];
    if($id>0){$sql='UPDATE city_officers_v1251 SET name=?,designation=?,vision_message=?,photo_url=?,office_name=?,phone=?,email=?,sort_order=?,featured=?,enabled=?,updated_by=?,updated_at=NOW() WHERE id=? AND tenant_id=?';$vals[]=$id;$vals[]=$tid;db()->prepare($sql)->execute($vals);}else{$sql='INSERT INTO city_officers_v1251(tenant_id,name,designation,vision_message,photo_url,office_name,phone,email,sort_order,featured,enabled,updated_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())';array_unshift($vals,$tid);db()->prepare($sql)->execute($vals);} 
}
function sk1251_delete_officer(int $id): void {if($id<1)return;$tid=max(0,sk1251_tid());$q=db()->prepare('SELECT photo_url FROM city_officers_v1251 WHERE id=? AND tenant_id=? LIMIT 1');$q->execute([$id,$tid]);$url=(string)($q->fetchColumn()?:'');db()->prepare('DELETE FROM city_officers_v1251 WHERE id=? AND tenant_id=?')->execute([$id,$tid]);if(str_starts_with($url,'/uploads/city-information/')){$f=dirname(__DIR__).$url;if(is_file($f))@unlink($f);} }
}
