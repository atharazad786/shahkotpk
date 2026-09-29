<?php
declare(strict_types=1);
/** ShahkotPK v12.6.1 — City Experience repair helpers. */
if (!function_exists('sk1261_h')) {
function sk1261_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function sk1261_tid(): int { try { if(function_exists('tenant_id'))return max(0,(int)tenant_id()); if(function_exists('current_tenant')){$t=current_tenant();return max(0,(int)($t['id']??0));} }catch(Throwable $e){} return 0; }
function sk1261_default_officer_image(string $designation='', string $roleKey=''): string {
    $s=strtolower($designation.' '.$roleKey);
    if(str_contains($s,'sho')||str_contains($s,'police')) return '/assets/officer-placeholder-police-v1261.svg';
    if(str_contains($s,'medical')||str_contains($s,'hospital')||str_contains($s,'health')) return '/assets/officer-placeholder-health-v1261.svg';
    if(str_contains($s,'mna')||str_contains($s,'mpa')||str_contains($s,'assembly')||str_contains($s,'representative')) return '/assets/officer-placeholder-representative-v1261.svg';
    return '/assets/officer-placeholder-civic-v1261.svg';
}
function sk1261_site_identity(): array {
    try{
        $f=__DIR__.'/unified_experience_v1100.php'; if(is_file($f))require_once $f;
        if(function_exists('sk1100_site_identity')) return sk1100_site_identity();
    }catch(Throwable $e){}
    $name='ShahkotPK';$logo='';$favicon='';$tagline='';
    try{ if(function_exists('setting')){ foreach(['site_name','app_name'] as $k){$v=setting($k,'');if($v){$name=(string)$v;break;}} foreach(['site_logo_url','site_logo','logo_url','logo','brand_logo','header_logo'] as $k){$v=setting($k,'');if($v){$logo=(string)$v;break;}} foreach(['favicon_url','favicon'] as $k){$v=setting($k,'');if($v){$favicon=(string)$v;break;}} }}catch(Throwable $e){}
    return ['name'=>$name,'logo'=>$logo,'favicon'=>$favicon,'tagline'=>$tagline];
}
function sk1261_public_officers(): array {
    $roles=function_exists('sk1260_roles')?sk1260_roles():[];
    $linked=[];$out=[];
    foreach($roles as $r){
        $oid=(int)($r['officer_id']??0); if($oid>0)$linked[$oid]=1;
        $hasLinked=$oid>0 && trim((string)($r['name']??''))!=='';
        if($hasLinked && isset($r['enabled']) && !$r['enabled']) continue;
        $name=$hasLinked?trim((string)$r['name']):'Officer details pending verification';
        $designation=trim((string)($r['designation']??'')); if($designation==='')$designation=(string)$r['official_title'];
        $photo=trim((string)($r['photo_url']??'')); if($photo==='')$photo=sk1261_default_officer_image($designation,(string)$r['role_key']);
        $out[]=[
            'id'=>$oid,'role_key'=>(string)$r['role_key'],'name'=>$name,'designation'=>$designation,
            'vision_message'=>(string)($r['vision_message']??''),'photo_url'=>$photo,
            'office_name'=>(string)($r['office_name']??''),'phone'=>(string)($r['phone']??''),'email'=>(string)($r['email']??''),
            'sort_order'=>(int)($r['sort_order']??100),'featured'=>!empty($r['featured'])?1:0,'enabled'=>1,
            'pending'=>$hasLinked?0:1,'sync_status'=>(string)($r['last_status']??'pending')
        ];
    }
    // Tenant-specific additional officers are appended without duplicating role-linked profiles.
    try{
        foreach(sk1251_officers(false) as $o){
            $id=(int)($o['id']??0); if($id>0 && isset($linked[$id]))continue;
            if(trim((string)($o['photo_url']??''))==='')$o['photo_url']=sk1261_default_officer_image((string)($o['designation']??''),'');
            $o['role_key']='';$o['pending']=0;$o['sync_status']='manual';$out[]=$o;
        }
    }catch(Throwable $e){}
    usort($out,static function($a,$b){$c=((int)$a['sort_order'])<=>((int)$b['sort_order']);if($c!==0)return $c;return ((int)($b['featured']??0))<=>((int)($a['featured']??0));});
    return $out;
}
function sk1261_upsert_tenant_role(array $role, int $sortOrder, ?int $officerId=null): void {
    $tid=sk1261_tid();
    if($tid===(int)($role['tenant_id']??0)){
        db()->prepare('UPDATE city_officer_sync_v1260 SET sort_order=?,officer_id=COALESCE(?,officer_id),updated_at=NOW() WHERE id=?')->execute([$sortOrder,$officerId,(int)$role['id']]);return;
    }
    db()->prepare("INSERT INTO city_officer_sync_v1260(tenant_id,role_key,officer_id,official_title,office_name,source_label,source_url,parser_key,auto_sync,last_status,last_message,source_verified_at,last_checked_at,last_changed_at,sort_order,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE officer_id=COALESCE(VALUES(officer_id),officer_id),official_title=VALUES(official_title),office_name=VALUES(office_name),source_label=VALUES(source_label),source_url=VALUES(source_url),parser_key=VALUES(parser_key),auto_sync=VALUES(auto_sync),sort_order=VALUES(sort_order),updated_at=NOW()")
      ->execute([$tid,(string)$role['role_key'],$officerId??((int)($role['officer_id']??0)?:null),(string)$role['official_title'],(string)($role['office_name']??''),(string)($role['source_label']??''),(string)($role['source_url']??''),(string)($role['parser_key']??'manual'),(int)($role['auto_sync']??0),(string)($role['last_status']??'manual'),(string)($role['last_message']??''),$role['source_verified_at']??null,$role['last_checked_at']??null,$role['last_changed_at']??null,$sortOrder]);
}
function sk1261_save_role_orders(array $orders): void {
    $roles=[];foreach(sk1260_roles() as $r)$roles[(string)$r['role_key']]=$r;
    $pos=1; foreach($orders as $key=>$raw){$key=preg_replace('/[^a-z0-9_\-]/i','',(string)$key);if(!isset($roles[$key]))continue;$n=max(1,min(99,(int)$raw));sk1261_upsert_tenant_role($roles[$key],$n*10,null);$pos++;}
}
function sk1261_save_role_profile(array $d,array $files,int $uid=0): void {
    $key=preg_replace('/[^a-z0-9_\-]/i','',(string)($d['role_key']??'')); if($key==='')throw new InvalidArgumentException('Invalid officer role.');
    $role=sk1260_role_by_key($key);if(!$role)throw new RuntimeException('Officer role not found.');
    $tid=sk1261_tid();$order=max(1,min(99,(int)($d['card_position']??max(1,(int)ceil(((int)$role['sort_order'])/10)))))*10;
    $payload=$d;$payload['designation']=trim((string)($d['designation']??$role['official_title']));$payload['office_name']=trim((string)($d['office_name']??$role['office_name']));$payload['enabled']=!empty($d['enabled'])?1:0;$payload['featured']=!empty($d['featured'])?1:0;$payload['sort_order']=$order;
    $oid=(int)($role['officer_id']??0);$roleTenant=(int)($role['tenant_id']??0);
    $editableOid=0;
    if($oid>0){
        try{$q=db()->prepare('SELECT id FROM city_officers_v1251 WHERE id=? AND tenant_id=? LIMIT 1');$q->execute([$oid,$tid]);$editableOid=(int)($q->fetchColumn()?:0);}catch(Throwable $e){}
    }
    if($editableOid>0){$payload['id']=$editableOid;sk1251_save_officer($payload,$files,$uid);sk1261_upsert_tenant_role($role,$order,$editableOid);return;}
    // Global/default linked officers are forked into current tenant only when edited; global defaults remain intact.
    $payload['id']=0;
    if(empty($payload['existing_photo'])&&!empty($role['photo_url']))$payload['existing_photo']=(string)$role['photo_url'];
    $name=trim((string)($payload['name']??''));if($name==='')throw new InvalidArgumentException('Officer name is required before publishing this role.');
    sk1251_save_officer($payload,$files,$uid);$newId=(int)db()->lastInsertId();
    if($newId<1){$q=db()->prepare('SELECT id FROM city_officers_v1251 WHERE tenant_id=? AND designation=? ORDER BY id DESC LIMIT 1');$q->execute([$tid,(string)$payload['designation']]);$newId=(int)($q->fetchColumn()?:0);}if($newId<1)throw new RuntimeException('Could not link the officer profile to this role.');
    sk1261_upsert_tenant_role($role,$order,$newId);
}
}
