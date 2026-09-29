<?php
declare(strict_types=1);
/** ShahkotPK v12.6.0 — Safe official-source city/officer sync. */
if (!function_exists('sk1260_h')) {
function sk1260_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function sk1260_tid(): int { try { if(function_exists('tenant_id')) return max(0,(int)tenant_id()); if(function_exists('current_tenant')){$t=current_tenant();return max(0,(int)($t['id']??0));} } catch(Throwable $e){} return 0; }
function sk1260_table(string $t): bool { static $c=[]; if(isset($c[$t]))return $c[$t]; if(!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/',$t))return false; try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$t]);return $c[$t]=(bool)$q->fetchColumn();}catch(Throwable $e){return $c[$t]=false;} }
function sk1260_settings(): array {
    $d=['greeting_enabled'=>1,'weather_clock_enabled'=>1,'weather_motion_enabled'=>1,'official_sync_enabled'=>1,'scheduled_sync_enabled'=>0,'last_sync_at'=>null];
    if(!sk1260_table('city_smart_settings_v1260')) return $d;
    try{$tid=sk1260_tid();$q=db()->prepare('SELECT * FROM city_smart_settings_v1260 WHERE tenant_id IN (0,?) ORDER BY tenant_id DESC LIMIT 1');$q->execute([$tid]);return array_merge($d,$q->fetch()?:[]);}catch(Throwable $e){return $d;}
}
function sk1260_save_settings(array $d,int $uid=0): void {
    $tid=sk1260_tid();
    db()->prepare('INSERT INTO city_smart_settings_v1260(tenant_id,greeting_enabled,weather_clock_enabled,weather_motion_enabled,official_sync_enabled,scheduled_sync_enabled,updated_by,updated_at) VALUES(?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE greeting_enabled=VALUES(greeting_enabled),weather_clock_enabled=VALUES(weather_clock_enabled),weather_motion_enabled=VALUES(weather_motion_enabled),official_sync_enabled=VALUES(official_sync_enabled),scheduled_sync_enabled=VALUES(scheduled_sync_enabled),updated_by=VALUES(updated_by),updated_at=NOW()')->execute([$tid,!empty($d['greeting_enabled'])?1:0,!empty($d['weather_clock_enabled'])?1:0,!empty($d['weather_motion_enabled'])?1:0,!empty($d['official_sync_enabled'])?1:0,!empty($d['scheduled_sync_enabled'])?1:0,$uid?:null]);
}
function sk1260_roles(): array {
    if(!sk1260_table('city_officer_sync_v1260')) return [];
    try{$tid=sk1260_tid();$q=db()->prepare('SELECT r.*,o.name,o.designation,o.vision_message,o.photo_url,o.phone,o.email,o.enabled,o.featured FROM city_officer_sync_v1260 r LEFT JOIN city_officers_v1251 o ON o.id=r.officer_id WHERE r.tenant_id IN (0,?) ORDER BY r.tenant_id DESC,r.sort_order ASC,r.id ASC');$q->execute([$tid]);$rows=$q->fetchAll()?:[];$seen=[];$out=[];foreach($rows as $r){$k=(string)$r['role_key'];if(isset($seen[$k]))continue;$seen[$k]=1;$out[]=$r;}return $out;}catch(Throwable $e){return [];}
}
function sk1260_sync_runs(int $limit=12): array { if(!sk1260_table('city_sync_runs_v1260'))return[];try{$q=db()->prepare('SELECT * FROM city_sync_runs_v1260 WHERE tenant_id=? ORDER BY id DESC LIMIT '.max(1,min(50,$limit)));$q->execute([sk1260_tid()]);return $q->fetchAll()?:[];}catch(Throwable $e){return [];} }
function sk1260_http_get(string $url): string {
    $parts=parse_url($url);$host=strtolower((string)($parts['host']??''));
    $allowed=['nankana.punjab.gov.pk','www.nankana.punjab.gov.pk','na.gov.pk','www.na.gov.pk','pshealthpunjab.gov.pk','www.pshealthpunjab.gov.pk','pap.gov.pk','www.pap.gov.pk','punjabpolice.gov.pk','www.punjabpolice.gov.pk','lgcd.punjab.gov.pk','www.lgcd.punjab.gov.pk'];
    if(($parts['scheme']??'')!=='https'||!in_array($host,$allowed,true)) throw new RuntimeException('Source host is not on the official allowlist.');
    if(function_exists('curl_init')){
        $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_USERAGENT=>'ShahkotPK-CitySync/12.6 (+https://shahkotpk.com)',CURLOPT_HTTPHEADER=>['Accept: text/html,application/xhtml+xml;q=0.9,*/*;q=0.5']]);$body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$err=(string)curl_error($ch);curl_close($ch);if($body===false||$code<200||$code>=400)throw new RuntimeException('Official source request failed'.($err?': '.$err:''));return (string)$body;
    }
    $ctx=stream_context_create(['http'=>['timeout'=>10,'user_agent'=>'ShahkotPK-CitySync/12.6','follow_location'=>1],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);$body=@file_get_contents($url,false,$ctx);if($body===false)throw new RuntimeException('Official source request failed.');return (string)$body;
}
function sk1260_plain(string $html): string { $html=preg_replace('/<script\b[^>]*>.*?<\/script>/is',' ',$html)??$html;$html=preg_replace('/<style\b[^>]*>.*?<\/style>/is',' ',$html)??$html;return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($html),ENT_QUOTES|ENT_HTML5,'UTF-8'))??''); }
function sk1260_clean_name(string $name): string {
    $name=trim(preg_replace('/\s+/u',' ',$name)??$name);$name=preg_replace('/^(Mr\.?|Ms\.?|Mrs\.?|Mian|Ch\.?|Chaudhry)\s+/i','',$name)??$name;return mb_substr(trim($name),0,160);
}
function sk1260_parse_role(string $parser,string $html): array {
    $text=sk1260_plain($html);$out=['name'=>'','phone'=>'','message'=>'','stale'=>false];
    if($parser==='dc_nankana'){
        if(preg_match('/([A-Z][A-Za-z.\'-]+(?:\s+[A-Z][A-Za-z.\'-]+){1,4})\s+Deputy Commissioner\b/i',$text,$m))$out['name']=sk1260_clean_name($m[1]);
        if($out['name']==='' && preg_match('/Deputy Commissioner[^A-Za-z]{0,20}([A-Z][A-Za-z.\'-]+(?:\s+[A-Z][A-Za-z.\'-]+){1,4})/i',$text,$m))$out['name']=sk1260_clean_name($m[1]);
    } elseif($parser==='ac_shahkot'){
        if(preg_match('/Assistant Commissioners.*?Nankana Sahib\s+([A-Z][A-Za-z.\'-]+(?:\s+[A-Z][A-Za-z.\'-]+){1,4})\s+Shahkot\b/i',$text,$m))$out['name']=sk1260_clean_name($m[1]);
        if($out['name']==='' && preg_match('/([A-Z][A-Za-z.\'-]+(?:\s+[A-Z][A-Za-z.\'-]+){1,3})\s+Shahkot\b/i',$text,$m) && stripos($m[1],'Commissioner')===false)$out['name']=sk1260_clean_name($m[1]);
    } elseif($parser==='mna_na111'){
        if(preg_match('/NA-111\s+Nankana Sahib-I\s+(?:Mr\.?\s+)?([A-Z][A-Za-z.\'-]+(?:\s+[A-Z][A-Za-z.\'-]+){1,5})\s+(?:IND|PML|PPP|PTI|IPP|MQM|JUI|ANP|BNP)/i',$text,$m))$out['name']=sk1260_clean_name($m[1]);
        if(preg_match('/NA-111.*?(03\d{2}[-\s]?\d{7})/i',$text,$m))$out['phone']=trim($m[1]);
    } elseif($parser==='ms_thq'){
        if(preg_match('/Dated\s+Lahore,?\s+the\s+3(?:rd|th)?\s*,?\s*October\s+2025/i',$text))$out['stale']=true;
        if(preg_match('/(Dr\.?\s+[A-Z][A-Za-z.\'-]+(?:\s+[A-Z][A-Za-z.\'-]+){1,4}).{0,120}?Medical Superintendent,?\s+THQ Hospital Shahkot/is',$text,$m))$out['name']=sk1260_clean_name($m[1]);
    }
    if($out['name']!=='' && (mb_strlen($out['name'])<4 || mb_strlen($out['name'])>90))$out['name']='';
    return $out;
}
function sk1260_upsert_role_officer(array $role,array $parsed,int $uid=0): bool {
    $tid=sk1260_tid();$name=trim((string)($parsed['name']??''));if($name==='')return false;
    $oid=(int)($role['officer_id']??0);$changed=false;
    if($oid>0){
        $q=db()->prepare('SELECT name,phone FROM city_officers_v1251 WHERE id=? AND tenant_id=? LIMIT 1');$q->execute([$oid,$tid]);$old=$q->fetch();if(!$old && $tid!==0){$q=db()->prepare('SELECT name,phone FROM city_officers_v1251 WHERE id=? AND tenant_id=0 LIMIT 1');$q->execute([$oid]);$old=$q->fetch();}
        if($old && trim((string)$old['name'])!==$name){db()->prepare('UPDATE city_officers_v1251 SET name=?,phone=CASE WHEN ?<>\'\' THEN ? ELSE phone END,updated_by=?,updated_at=NOW() WHERE id=?')->execute([$name,(string)($parsed['phone']??''),(string)($parsed['phone']??''),$uid?:null,$oid]);$changed=true;}
        elseif($old && !empty($parsed['phone']) && trim((string)$old['phone'])===''){db()->prepare('UPDATE city_officers_v1251 SET phone=?,updated_by=?,updated_at=NOW() WHERE id=?')->execute([(string)$parsed['phone'],$uid?:null,$oid]);$changed=true;}
    } else {
        db()->prepare('INSERT INTO city_officers_v1251(tenant_id,name,designation,vision_message,photo_url,office_name,phone,email,sort_order,featured,enabled,updated_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')->execute([$tid,$name,(string)$role['official_title'],'','',(string)$role['office_name'],(string)($parsed['phone']??''),'',(int)$role['sort_order'],0,1,$uid?:null]);$oid=(int)db()->lastInsertId();db()->prepare('UPDATE city_officer_sync_v1260 SET officer_id=? WHERE id=?')->execute([$oid,(int)$role['id']]);$changed=true;
    }
    return $changed;
}
function sk1260_sync_all(string $runType='manual',int $uid=0): array {
    $settings=sk1260_settings();if(empty($settings['official_sync_enabled']))throw new RuntimeException('Official source sync is disabled in City Content Studio.');
    $tid=sk1260_tid();$started=date('Y-m-d H:i:s');$runId=0;if(sk1260_table('city_sync_runs_v1260')){db()->prepare('INSERT INTO city_sync_runs_v1260(tenant_id,run_type,status,started_at) VALUES(?,?,\'running\',NOW())')->execute([$tid,$runType]);$runId=(int)db()->lastInsertId();}
    $roles=sk1260_roles();$checked=0;$changed=0;$failed=0;$details=[];
    foreach($roles as $role){if(empty($role['auto_sync'])||trim((string)$role['parser_key'])===''||(string)$role['parser_key']==='manual')continue;$checked++;$status='ok';$message='Checked official source; no verified change detected.';$didChange=false;
        try{$html=sk1260_http_get((string)$role['source_url']);$parsed=sk1260_parse_role((string)$role['parser_key'],$html);if(!empty($parsed['stale'])){$status='stale';$message='Official source was found but is older; current public profile was not overwritten.';}elseif(empty($parsed['name'])){$status='review';$message='Source loaded, but a current officer name could not be verified automatically.';}else{$didChange=sk1260_upsert_role_officer($role,$parsed,$uid);$status=$didChange?'updated':'verified';$message=($didChange?'Updated':'Verified').' from official source: '.$parsed['name'];if($didChange)$changed++;}
        }catch(Throwable $e){$failed++;$status='failed';$message=mb_substr($e->getMessage(),0,480);}
        try{db()->prepare('UPDATE city_officer_sync_v1260 SET last_status=?,last_message=?,last_checked_at=NOW(),source_verified_at=CASE WHEN ? IN (\'verified\',\'updated\') THEN NOW() ELSE source_verified_at END,last_changed_at=CASE WHEN ?=1 THEN NOW() ELSE last_changed_at END,updated_at=NOW() WHERE id=?')->execute([$status,$message,$status,$didChange?1:0,(int)$role['id']]);}catch(Throwable $e){}
        $details[]=['role_key'=>$role['role_key'],'status'=>$status,'message'=>$message];
    }
    $final=$failed>0?($changed>0?'partial':'warning'):'ok';
    try{db()->prepare('UPDATE city_smart_settings_v1260 SET last_sync_at=NOW() WHERE tenant_id=?')->execute([$tid]);if($runId>0)db()->prepare('UPDATE city_sync_runs_v1260 SET status=?,checked_count=?,changed_count=?,failed_count=?,details_json=?,finished_at=NOW() WHERE id=?')->execute([$final,$checked,$changed,$failed,json_encode($details,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),$runId]);}catch(Throwable $e){}
    return ['status'=>$final,'checked'=>$checked,'changed'=>$changed,'failed'=>$failed,'details'=>$details,'started_at'=>$started];
}

function sk1260_role_by_key(string $key): ?array { foreach(sk1260_roles() as $r) if((string)$r['role_key']===$key)return $r; return null; }
function sk1260_save_role_profile(array $d,array $files,int $uid=0): void {
    $key=preg_replace('/[^a-z0-9_\-]/i','',(string)($d['role_key']??''));if($key==='')throw new InvalidArgumentException('Invalid officer role.');$role=sk1260_role_by_key($key);if(!$role)throw new RuntimeException('Officer role not found.');
    $tid=sk1260_tid();$oid=(int)($role['officer_id']??0);$payload=$d;$payload['designation']=trim((string)($d['designation']??$role['official_title']));$payload['office_name']=trim((string)($d['office_name']??$role['office_name']));$payload['enabled']=!empty($d['enabled'])?1:0;$payload['featured']=!empty($d['featured'])?1:0;$payload['sort_order']=(int)($role['sort_order']??10);
    if($oid>0){$payload['id']=$oid;sk1251_save_officer($payload,$files,$uid);return;}
    $name=trim((string)($payload['name']??''));if($name==='')throw new InvalidArgumentException('Officer name is required before publishing this role.');
    sk1251_save_officer($payload,$files,$uid);$newId=(int)db()->lastInsertId();if($newId<1){$q=db()->prepare('SELECT id FROM city_officers_v1251 WHERE tenant_id=? AND designation=? ORDER BY id DESC LIMIT 1');$q->execute([$tid,(string)$payload['designation']]);$newId=(int)($q->fetchColumn()?:0);}if($newId<1)throw new RuntimeException('Could not link the officer profile to this role.');
    db()->prepare('UPDATE city_officer_sync_v1260 SET officer_id=?,last_status=\'manual\',last_message=\'Profile updated manually in City Content Studio.\',last_changed_at=NOW(),updated_at=NOW() WHERE role_key=? AND tenant_id IN (0,?) ORDER BY tenant_id DESC LIMIT 1')->execute([$newId,$key,$tid]);
}
}
