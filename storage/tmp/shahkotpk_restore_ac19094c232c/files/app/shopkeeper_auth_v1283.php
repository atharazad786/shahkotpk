<?php
declare(strict_types=1);
/** ShahkotPK v12.8.3 — schema-aware imported shopkeeper authentication & credential manager. */
if(!function_exists('bs1270_table')){ $f=__DIR__.'/business_source_import_v1270.php'; if(is_file($f)) require_once $f; }
$ac1300=__DIR__.'/access_control_v1300.php';if(is_file($ac1300))require_once $ac1300;
if(!function_exists('bs1283_account_for_business')){
function bs1283_business_owner_id(int $bid): int {
    if($bid<1||!bs1270_table('businesses'))return 0;
    $c=bs1270_cols('businesses');$field=isset($c['owner_user_id'])?'owner_user_id':(isset($c['owner_id'])?'owner_id':'');if($field==='')return 0;
    try{$q=db()->prepare('SELECT `'.$field.'` FROM businesses WHERE id=? LIMIT 1');$q->execute([$bid]);return (int)($q->fetchColumn()?:0);}catch(Throwable $e){return 0;}
}
function bs1283_user(int $uid): array {if($uid<1||!bs1270_table('users'))return [];try{$q=db()->prepare('SELECT * FROM users WHERE id=? LIMIT 1');$q->execute([$uid]);return $q->fetch()?:[];}catch(Throwable $e){return [];}}
function bs1283_business_name(int $bid): string {try{$q=db()->prepare('SELECT name FROM businesses WHERE id=? LIMIT 1');$q->execute([$bid]);return trim((string)($q->fetchColumn()?:''));}catch(Throwable $e){return '';}}
function bs1283_field_active(string $table,string $field,$active='active'){
    $c=bs1270_cols($table);if(!isset($c[$field]))return $active;$t=strtolower((string)($c[$field]['DATA_TYPE']??''));return in_array($t,['tinyint','smallint','mediumint','int','bigint','bit'],true)?1:$active;
}
function bs1283_slug(string $s): string {$s=strtolower(trim($s));$s=preg_replace('/[^a-z0-9]+/','-',$s)?:'shopkeeper';return trim($s,'-')?:'shopkeeper';}
function bs1283_unique_value(string $field,string $value,int $uid=0): string {
    $cols=bs1270_cols('users');if(!isset($cols[$field]))return $value;$base=$value;$i=0;
    while(true){$try=$i===0?$base:$base.$i;try{$q=db()->prepare('SELECT id FROM users WHERE `'.$field.'`=?'.($uid>0?' AND id<>?':'').' LIMIT 1');$p=[$try];if($uid>0)$p[]=$uid;$q->execute($p);if(!$q->fetchColumn())return $try;}catch(Throwable $e){return $try;}$i++;if($i>999)return $base.bin2hex(random_bytes(2));}
}
function bs1283_default_username(string $businessName,int $uid=0): string {return bs1283_unique_value('username','shop-'.substr(bs1283_slug($businessName),0,28).'-', $uid);}
function bs1283_default_email(string $businessName,int $uid=0): string {
    $local='shop.'.substr(str_replace('-','.',bs1283_slug($businessName)),0,35).'.'.substr(hash('sha256',$businessName.'|'.$uid.'|shahkotpk'),0,8).'@accounts.shahkotpk.local';
    return bs1283_unique_value('email',$local,$uid);
}
function bs1283_raw_credential(int $bid): string {if($bid<1||!bs1270_table('business_source_credentials_v1270'))return '';try{$q=db()->prepare('SELECT credential_cipher FROM business_source_credentials_v1270 WHERE tenant_id IN (?,0) AND business_id=? ORDER BY id DESC LIMIT 1');$q->execute([bs1270_tid(),$bid]);$c=(string)($q->fetchColumn()?:'');return function_exists('bs1270_decrypt')?bs1270_decrypt($c):'';}catch(Throwable $e){return '';}}
function bs1283_store_credential(int $bid,int $uid,string $plain): void {
    if($bid<1||$uid<1||$plain===''||!bs1270_table('business_source_credentials_v1270'))return;$cipher=bs1270_encrypt($plain);if(!$cipher)throw new RuntimeException('Unable to encrypt the shopkeeper credential.');$tid=bs1270_tid();
    try{$q=db()->prepare('SELECT id FROM business_source_credentials_v1270 WHERE tenant_id IN (?,0) AND (business_id=? OR user_id=?) ORDER BY tenant_id DESC,id DESC LIMIT 1');$q->execute([$tid,$bid,$uid]);$id=(int)($q->fetchColumn()?:0);$d=['tenant_id'=>$tid,'business_id'=>$bid,'user_id'=>$uid,'credential_cipher'=>$cipher,'revealed_at'=>null,'created_at'=>date('Y-m-d H:i:s')];if($id)bs1270_update('business_source_credentials_v1270',$id,$d);else bs1270_insert('business_source_credentials_v1270',$d);}catch(Throwable $e){throw new RuntimeException('Unable to store the temporary password securely: '.$e->getMessage());}
}
function bs1283_ensure_membership(int $uid): void {
    if($uid<1||!bs1270_table('tenant_members')||bs1270_tid()<1)return;$cols=bs1270_cols('tenant_members');if(!isset($cols['tenant_id'],$cols['user_id']))return;
    try{$q=db()->prepare('SELECT id FROM tenant_members WHERE tenant_id=? AND user_id=? LIMIT 1');$q->execute([bs1270_tid(),$uid]);$id=(int)($q->fetchColumn()?:0);$d=['tenant_id'=>bs1270_tid(),'user_id'=>$uid,'member_role'=>'seller','role_key'=>'seller','role'=>'seller','status'=>bs1283_field_active('tenant_members','status'),'is_active'=>1,'active'=>1];if($id)bs1270_update('tenant_members',$id,$d);else bs1270_insert('tenant_members',$d);}catch(Throwable $e){} if(function_exists('sk1300_assign_default_package'))sk1300_assign_default_package($uid,'shopkeeper',bs1270_tid());
}
function bs1283_normalize_user(int $uid,string $businessName='',string $knownPassword=''): array {
    $u=bs1283_user($uid);if(!$u)throw new RuntimeException('Shopkeeper user account was not found.');$cols=bs1270_cols('users');$d=[];
    if($businessName==='' )$businessName=(string)($u['name']??'Shopkeeper');
    if(isset($cols['name'])&&trim((string)($u['name']??''))==='')$d['name']=$businessName.' Shopkeeper';
    if(isset($cols['username'])&&trim((string)($u['username']??''))==='')$d['username']=bs1283_unique_value('username','shop-'.substr(bs1283_slug($businessName),0,24).'-'.substr((string)$uid,-4),$uid);
    if(isset($cols['email'])){$email=trim((string)($u['email']??''));if($email===''||!filter_var($email,FILTER_VALIDATE_EMAIL))$d['email']=bs1283_default_email($businessName,$uid);}
    foreach(['role','role_key'] as $f)if(isset($cols[$f]))$d[$f]='seller';
    if(isset($cols['status']))$d['status']=bs1283_field_active('users','status');
    foreach(['is_active','active','enabled'] as $f)if(isset($cols[$f]))$d[$f]=1;
    foreach(['is_verified','verified','email_verified','approved','is_approved'] as $f)if(isset($cols[$f]))$d[$f]=1;
    foreach(['blocked','is_blocked','deleted','is_deleted','suspended','is_suspended'] as $f)if(isset($cols[$f]))$d[$f]=0;
    if(isset($cols['must_reset_password']))$d['must_reset_password']=0;
    if(isset($cols['approval_status']))$d['approval_status']=bs1283_field_active('users','approval_status','approved');
    if($knownPassword!==''){$hash=(string)($u['password_hash']??$u['password']??'');if($hash===''||!password_verify($knownPassword,$hash)){$newHash=password_hash($knownPassword,PASSWORD_DEFAULT);if(isset($cols['password']))$d['password']=$newHash;if(isset($cols['password_hash']))$d['password_hash']=$newHash;}}
    if($d)bs1270_update('users',$uid,$d);bs1283_ensure_membership($uid);return bs1283_user($uid);
}
function bs1283_account_for_business(int $bid): array {$uid=bs1283_business_owner_id($bid);$u=bs1283_user($uid);if(!$u)return ['business_id'=>$bid,'user_id'=>0,'login'=>'','user'=>[]];$login=trim((string)($u['username']??''));if($login==='')$login=trim((string)($u['email']??''));if($login==='')$login=trim((string)($u['phone']??''));return ['business_id'=>$bid,'user_id'=>$uid,'login'=>$login,'user'=>$u];}
function bs1283_repair_business_account(int $bid,bool $resetIfMissing=true): array {
    $uid=bs1283_business_owner_id($bid);if($uid<1)throw new RuntimeException('This business has no attached shopkeeper account.');$name=bs1283_business_name($bid);$plain=bs1283_raw_credential($bid);$generated='';if($plain===''&&$resetIfMissing){$generated=bs1270_random_password();$plain=$generated;bs1283_store_credential($bid,$uid,$plain);}bs1283_normalize_user($uid,$name,$plain);if(function_exists('bs1270_audit'))bs1270_audit('shopkeeper.login_repaired',$bid,0,['user_id'=>$uid,'generated_password'=>$generated!==''?1:0]);$a=bs1283_account_for_business($bid);$a['temp_password']=$generated;return $a;
}
function bs1283_reset_password(int $bid,string $password=''): array {
    $uid=bs1283_business_owner_id($bid);if($uid<1)throw new RuntimeException('No shopkeeper is attached to this business.');if($password==='')$password=bs1270_random_password();if(strlen($password)<8)throw new RuntimeException('Password must be at least 8 characters.');$cols=bs1270_cols('users');$hash=password_hash($password,PASSWORD_DEFAULT);$d=[];if(isset($cols['password']))$d['password']=$hash;if(isset($cols['password_hash']))$d['password_hash']=$hash;if(isset($cols['must_reset_password']))$d['must_reset_password']=0;if(!$d)throw new RuntimeException('No supported password column exists in users table.');bs1270_update('users',$uid,$d);bs1283_store_credential($bid,$uid,$password);bs1283_normalize_user($uid,bs1283_business_name($bid),$password);if(function_exists('bs1270_audit'))bs1270_audit('shopkeeper.password_reset',$bid,0,['user_id'=>$uid]);$a=bs1283_account_for_business($bid);$a['temp_password']=$password;return $a;
}
function bs1283_save_account(int $bid,array $p): array {
    $uid=bs1283_business_owner_id($bid);if($uid<1)throw new RuntimeException('No shopkeeper is attached to this business.');$u=bs1283_user($uid);$cols=bs1270_cols('users');$d=[];
    if(isset($cols['username'])&&array_key_exists('username',$p)){$v=trim((string)$p['username']);if($v!==''){$v=preg_replace('/[^A-Za-z0-9._-]/','',$v)?:'';if(strlen($v)<3)throw new RuntimeException('Username must be at least 3 characters.');$d['username']=bs1283_unique_value('username',$v,$uid);}}
    if(isset($cols['email'])&&array_key_exists('email',$p)){$v=trim((string)$p['email']);if($v!==''&&!filter_var($v,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Shopkeeper email is invalid.');if($v!=='')$d['email']=bs1283_unique_value('email',$v,$uid);}
    if(isset($cols['phone'])&&array_key_exists('phone',$p))$d['phone']=trim((string)$p['phone']);
    if($d)bs1270_update('users',$uid,$d);$new=(string)($p['new_password']??'');$temp='';if($new!==''){$r=bs1283_reset_password($bid,$new);$temp=(string)$r['temp_password'];}else bs1283_normalize_user($uid,bs1283_business_name($bid),bs1283_raw_credential($bid));if(function_exists('bs1270_audit'))bs1270_audit('shopkeeper.credentials_edited',$bid,0,['user_id'=>$uid,'password_changed'=>$new!==''?1:0]);$a=bs1283_account_for_business($bid);$a['temp_password']=$temp;return $a;
}
function bs1283_repair_batch(int $limit=20): array {$limit=max(1,min(50,$limit));$rows=function_exists('bs1273_managed_businesses')?bs1273_managed_businesses('',$limit):[];$ok=0;$errors=[];$generated=[];foreach($rows as $r){$bid=(int)($r['id']??0);if($bid<1)continue;try{$x=bs1283_repair_business_account($bid,true);$ok++;if(!empty($x['temp_password']))$generated[$bid]=$x['temp_password'];}catch(Throwable $e){$errors[]='#'.$bid.' '.$e->getMessage();}}return ['processed'=>$ok,'errors'=>$errors,'generated'=>$generated];}
}
