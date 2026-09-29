<?php
declare(strict_types=1);

/* ShahkotPK v12.7.9 — business page media manager.
 * Separate logo, hero banners and gallery media without changing the existing Business Directory identity model.
 */
function bm1279_table_exists(string $table): bool {
    static $cache=[]; if(isset($cache[$table])) return $cache[$table];
    try{$q=db()->prepare('SHOW TABLES LIKE ?');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();}catch(Throwable $e){return $cache[$table]=false;}
}
function bm1279_cols(string $table): array {
    static $cache=[];if(isset($cache[$table]))return $cache[$table];$out=[];
    try{$q=db()->query('SHOW COLUMNS FROM `'.str_replace('`','',$table).'`');foreach($q?:[] as $r)$out[(string)$r['Field']]=$r;}catch(Throwable $e){}
    return $cache[$table]=$out;
}
function bm1279_tid(): int {return function_exists('tenant_current_id')?(int)tenant_current_id():0;}
function bm1279_uid(): int {$u=function_exists('current_user')?current_user():null;return is_array($u)?(int)($u['id']??0):0;}
function bm1279_json($v,array $d=[]): array {if(is_array($v))return $v;if(!is_string($v)||trim($v)==='')return $d;$x=json_decode($v,true);return is_array($x)?$x:$d;}
function bm1279_media(int $bid): array {
    $d=['logo_path'=>'','banner_json'=>[],'gallery_json'=>[],'template_key'=>'premium'];
    if($bid<1||!bm1279_table_exists('business_page_media_v1279'))return $d;
    try{$q=db()->prepare('SELECT * FROM business_page_media_v1279 WHERE tenant_id=? AND business_id=? LIMIT 1');$q->execute([bm1279_tid(),$bid]);$r=$q->fetch();if(!$r)return $d;$r['banner_json']=bm1279_json($r['banner_json']??'[]');$r['gallery_json']=bm1279_json($r['gallery_json']??'[]');return array_merge($d,$r);}catch(Throwable $e){return $d;}
}
function bm1279_safe_url(string $u): string {$u=trim($u);if($u==='' )return '';return (str_starts_with($u,'/')||filter_var($u,FILTER_VALIDATE_URL))?$u:'';}
function bm1279_upload_dir(int $bid): array {
    $rel='uploads/business-media/'.$bid; $abs=dirname(__DIR__).'/'.$rel;
    if(!is_dir($abs)&&!@mkdir($abs,0775,true)&&!is_dir($abs))throw new RuntimeException('Unable to create business media folder.');
    return [$abs,'/'.$rel];
}
function bm1279_open_image(string $tmp,string $mime){
    return match($mime){'image/jpeg'=>@imagecreatefromjpeg($tmp),'image/png'=>@imagecreatefrompng($tmp),'image/webp'=>function_exists('imagecreatefromwebp')?@imagecreatefromwebp($tmp):false,'image/gif'=>@imagecreatefromgif($tmp),default=>false};
}
function bm1279_store_processed(string $tmp,int $bid,string $kind,int $index=0): string {
    if(!is_file($tmp))throw new RuntimeException('Uploaded image is missing.');
    $info=@getimagesize($tmp); if(!$info)throw new RuntimeException('Invalid image file.');
    [$w,$h]=$info; $mime=(string)($info['mime']??'');
    if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'],true))throw new RuntimeException('Only JPG, PNG, WebP or GIF images are allowed.');
    if($w<1||$h<1||($w*$h)>40000000)throw new RuntimeException('Image dimensions are too large.');
    [$dir,$urlbase]=bm1279_upload_dir($bid);$stamp=date('YmdHis').'-'.bin2hex(random_bytes(3));
    if(!function_exists('imagecreatetruecolor')){
        $ext=$mime==='image/png'?'png':($mime==='image/webp'?'webp':($mime==='image/gif'?'gif':'jpg'));
        $file=$kind.'-'.$stamp.'-'.$index.'.'.$ext;if(!@copy($tmp,$dir.'/'.$file))throw new RuntimeException('Unable to save uploaded image.');return $urlbase.'/'.$file;
    }
    $src=bm1279_open_image($tmp,$mime);if(!$src)throw new RuntimeException('Unable to decode uploaded image.');
    if($kind==='logo'){$tw=800;$th=800;$margin=64;$scale=min(($tw-2*$margin)/$w,($th-2*$margin)/$h,1.0);$nw=max(1,(int)round($w*$scale));$nh=max(1,(int)round($h*$scale));$dx=(int)(($tw-$nw)/2);$dy=(int)(($th-$nh)/2);$dst=imagecreatetruecolor($tw,$th);imagealphablending($dst,false);imagesavealpha($dst,true);$clear=imagecolorallocatealpha($dst,255,255,255,127);imagefilledrectangle($dst,0,0,$tw,$th,$clear);imagealphablending($dst,true);imagecopyresampled($dst,$src,$dx,$dy,0,0,$nw,$nh,$w,$h);}
    elseif($kind==='banner'){$tw=1920;$th=720;$scale=max($tw/$w,$th/$h);$sw=(int)round($tw/$scale);$sh=(int)round($th/$scale);$sx=max(0,(int)(($w-$sw)/2));$sy=max(0,(int)(($h-$sh)/2));$dst=imagecreatetruecolor($tw,$th);imagecopyresampled($dst,$src,0,0,$sx,$sy,$tw,$th,$sw,$sh);}
    else{$max=1600;$scale=min($max/$w,$max/$h,1.0);$tw=max(1,(int)round($w*$scale));$th=max(1,(int)round($h*$scale));$dst=imagecreatetruecolor($tw,$th);imagealphablending($dst,false);imagesavealpha($dst,true);$clear=imagecolorallocatealpha($dst,255,255,255,127);imagefilledrectangle($dst,0,0,$tw,$th,$clear);imagealphablending($dst,true);imagecopyresampled($dst,$src,0,0,0,0,$tw,$th,$w,$h);}
    $ext=function_exists('imagewebp')?'webp':'jpg';$file=$kind.'-'.$stamp.'-'.$index.'.'.$ext;$dest=$dir.'/'.$file;
    $ok=$ext==='webp'?@imagewebp($dst,$dest,86):@imagejpeg($dst,$dest,88);imagedestroy($src);imagedestroy($dst);if(!$ok)throw new RuntimeException('Unable to process uploaded image.');return $urlbase.'/'.$file;
}
function bm1279_file_items(string $field): array {
    if(empty($_FILES[$field])||!is_array($_FILES[$field]))return [];$f=$_FILES[$field];$out=[];
    if(is_array($f['name']??null)){foreach($f['name'] as $i=>$name){if((int)($f['error'][$i]??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)continue;$out[]=['name'=>$name,'tmp_name'=>$f['tmp_name'][$i]??'','error'=>(int)($f['error'][$i]??1),'size'=>(int)($f['size'][$i]??0)];}}
    elseif((int)($f['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE)$out[]=['name'=>$f['name']??'','tmp_name'=>$f['tmp_name']??'','error'=>(int)($f['error']??1),'size'=>(int)($f['size']??0)];
    return $out;
}
function bm1279_process_files(string $field,int $bid,string $kind,int $max): array {$out=[];foreach(array_slice(bm1279_file_items($field),0,$max) as $i=>$f){if($f['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Image upload failed for '.$f['name'].'.');if($f['size']>10*1024*1024)throw new RuntimeException('Each image must be 10 MB or smaller.');$out[]=bm1279_store_processed((string)$f['tmp_name'],$bid,$kind,$i);}return $out;}
function bm1279_sync_business_media_fields(int $bid,array $m): void {
    $cols=bm1279_cols('businesses');$d=[];$logo=(string)($m['logo_path']??'');$banners=(array)($m['banner_json']??[]);$cover=(string)($banners[0]??'');
    if($logo!==''){foreach(['logo_url','logo'] as $c)if(isset($cols[$c]))$d[$c]=$logo;if(isset($cols['image'])&&trim((string)($d['image']??''))===''){} }
    if($cover!=='')foreach(['cover_url','banner_url','cover_image'] as $c)if(isset($cols[$c]))$d[$c]=$cover;
    if($logo!==''&&isset($cols['image_url']))$d['image_url']=$logo;if($logo!==''&&isset($cols['image']))$d['image']=$logo;
    if($d){$set=[];$vals=[];foreach($d as $k=>$v){$set[]='`'.$k.'`=?';$vals[]=$v;}$vals[]=$bid;try{$q=db()->prepare('UPDATE businesses SET '.implode(',',$set).' WHERE id=?');$q->execute($vals);}catch(Throwable $e){}}
    if(bm1279_table_exists('marketplace_shops_v550')){try{$sc=bm1279_cols('marketplace_shops_v550');$sd=[];if($logo!==''&&isset($sc['logo_url']))$sd['logo_url']=$logo;if($cover!==''&&isset($sc['banner_url']))$sd['banner_url']=$cover;if($sd){$set=[];$vals=[];foreach($sd as $k=>$v){$set[]='`'.$k.'`=?';$vals[]=$v;}$vals[]=$bid;$q=db()->prepare('UPDATE marketplace_shops_v550 SET '.implode(',',$set).' WHERE business_id=?');$q->execute($vals);}}catch(Throwable $e){}}
}
function bm1279_save_media(int $bid,array $post): array {
    if($bid<1)throw new RuntimeException('Save the business before media can be attached.');if(!bm1279_table_exists('business_page_media_v1279'))throw new RuntimeException('Business media migration is not installed.');
    $old=bm1279_media($bid);$logo=(string)($old['logo_path']??'');$banners=array_values(array_filter((array)($old['banner_json']??[]),'is_string'));$gallery=array_values(array_filter((array)($old['gallery_json']??[]),'is_string'));
    $newLogo=bm1279_process_files('business_logo',$bid,'logo',1);if($newLogo)$logo=$newLogo[0];
    $newBanners=bm1279_process_files('business_banners',$bid,'banner',5);if($newBanners)$banners=array_slice(array_values(array_unique(array_merge($banners,$newBanners))),0,8);
    $newGallery=bm1279_process_files('business_gallery_files',$bid,'gallery',12);if($newGallery)$gallery=array_slice(array_values(array_unique(array_merge($gallery,$newGallery))),0,24);
    $removeB=array_map('intval',(array)($post['remove_banner']??[]));if($removeB)$banners=array_values(array_filter($banners,fn($v,$i)=>!in_array($i,$removeB,true),ARRAY_FILTER_USE_BOTH));
    $removeG=array_map('intval',(array)($post['remove_gallery']??[]));if($removeG)$gallery=array_values(array_filter($gallery,fn($v,$i)=>!in_array($i,$removeG,true),ARRAY_FILTER_USE_BOTH));
    if(!empty($post['remove_logo']))$logo='';
    $template=(string)($post['website_template']??($old['template_key']??'premium'));if(!in_array($template,['premium','cinematic','clean','storefront'],true))$template='premium';
    $row=['tenant_id'=>bm1279_tid(),'business_id'=>$bid,'logo_path'=>$logo?:null,'banner_json'=>json_encode($banners,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),'gallery_json'=>json_encode($gallery,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),'template_key'=>$template,'updated_by'=>bm1279_uid()?:null,'updated_at'=>date('Y-m-d H:i:s')];
    try{$q=db()->prepare('SELECT id FROM business_page_media_v1279 WHERE tenant_id=? AND business_id=? LIMIT 1');$q->execute([bm1279_tid(),$bid]);$id=(int)($q->fetchColumn()?:0);if($id){$q=db()->prepare('UPDATE business_page_media_v1279 SET logo_path=?,banner_json=?,gallery_json=?,template_key=?,updated_by=?,updated_at=? WHERE id=?');$q->execute([$row['logo_path'],$row['banner_json'],$row['gallery_json'],$row['template_key'],$row['updated_by'],$row['updated_at'],$id]);}else{$q=db()->prepare('INSERT INTO business_page_media_v1279 (tenant_id,business_id,logo_path,banner_json,gallery_json,template_key,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?)');$q->execute([$row['tenant_id'],$bid,$row['logo_path'],$row['banner_json'],$row['gallery_json'],$row['template_key'],$row['updated_by'],date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);}}
    catch(Throwable $e){throw new RuntimeException('Unable to save business media: '.$e->getMessage());}
    $m=bm1279_media($bid);bm1279_sync_business_media_fields($bid,$m);return $m;
}
