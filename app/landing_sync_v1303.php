<?php
declare(strict_types=1);
/** ShahkotPK v13.0.3.4 — tenant-aware landing section/data sync. */
if (!function_exists('sk1303_landing_config')) {
function sk1303_tid(): int { return function_exists('sk1301_tid') ? (int)sk1301_tid() : 0; }
function sk1303_setting(string $key,string $default=''): string {
    $tid=sk1303_tid();
    if(function_exists('sk1301_setting')){
        if($tid>0){$v=sk1301_setting('tenant_'.$tid.'_'.$key,'');if($v!=='')return $v;}
        return sk1301_setting($key,$default);
    }
    return $default;
}
function sk1303_save_setting(string $key,string $value): void {
    if(!function_exists('sk1301_table')||!sk1301_table('settings'))throw new RuntimeException('Settings table is unavailable.');
    $c=sk1301_cols('settings');$kc=isset($c['setting_key'])?'setting_key':(isset($c['key'])?'key':'');$vc=isset($c['setting_value'])?'setting_value':(isset($c['value'])?'value':'');
    if($kc===''||$vc==='')throw new RuntimeException('Settings schema is not supported.');
    $tid=sk1303_tid();$storeKey=$tid>0?'tenant_'.$tid.'_'.$key:$key;
    $db=sk1301_db();$q=$db->prepare('SELECT COUNT(*) FROM settings WHERE `'.$kc.'`=?');$q->execute([$storeKey]);
    if((int)$q->fetchColumn()>0){$q=$db->prepare('UPDATE settings SET `'.$vc.'`=? WHERE `'.$kc.'`=?');$q->execute([$value,$storeKey]);}
    else{$q=$db->prepare('INSERT INTO settings(`'.$kc.'`,`'.$vc.'`) VALUES(?,?)');$q->execute([$storeKey,$value]);}
}
function sk1303_active_business_condition(string $alias='b'): string {
    if(!function_exists('sk1301_cols'))return '1=1';$c=sk1301_cols('businesses');if(!isset($c['status']))return '1=1';
    $type=strtolower((string)($c['status']['DATA_TYPE']??''));
    if(in_array($type,['tinyint','smallint','mediumint','int','bigint','bit','boolean'],true))return $alias.'.status=1';
    return "LOWER(CAST(".$alias.".status AS CHAR)) IN ('1','active','published','approved')";
}
function sk1303_business_counts(): array {
    $out=['total'=>0,'active'=>0,'categories'=>[]];
    if(!function_exists('sk1301_db')||!sk1301_table('businesses'))return $out;
    try{$db=sk1301_db();$tid=sk1303_tid();$bc=sk1301_cols('businesses');$where=[];$args=[];
        if(isset($bc['tenant_id'])&&$tid>0){$where[]='b.tenant_id=?';$args[]=$tid;}
        $w=$where?' WHERE '.implode(' AND ',$where):'';$q=$db->prepare('SELECT COUNT(*) FROM businesses b'.$w);$q->execute($args);$out['total']=(int)$q->fetchColumn();
        $wa=$where;$wa[]=sk1303_active_business_condition('b');$q=$db->prepare('SELECT COUNT(*) FROM businesses b WHERE '.implode(' AND ',$wa));$q->execute($args);$out['active']=(int)$q->fetchColumn();
        if(isset($bc['category_id'])&&sk1301_table('categories')){$cc=sk1301_cols('categories');$name=isset($cc['name'])?'name':(isset($cc['title'])?'title':'');if($name){$sql='SELECT c.`'.$name.'` label,COUNT(*) n FROM businesses b JOIN categories c ON c.id=b.category_id WHERE '.implode(' AND ',$wa).' GROUP BY c.id,c.`'.$name.'`';$q=$db->prepare($sql);$q->execute($args);foreach($q->fetchAll()?:[] as $r)$out['categories'][(string)$r['label']]=(int)$r['n'];}}
    }catch(Throwable $e){}return $out;
}
function sk1303_landing_sections(): array {
    $d=[
      'hero'=>['label'=>'Hero','enabled'=>1,'order'=>10],
      'search'=>['label'=>'Search','enabled'=>1,'order'=>20],
      'categories'=>['label'=>'Popular Categories','enabled'=>1,'order'=>30],
      'businesses'=>['label'=>'Businesses','enabled'=>1,'order'=>40],
      'city_updates'=>['label'=>'City Updates','enabled'=>1,'order'=>50],
      'deals'=>['label'=>'Deals & Offers','enabled'=>1,'order'=>60],
      'services'=>['label'=>'Customer Services','enabled'=>1,'order'=>70],
      'map'=>['label'=>'Explore Shahkot Map','enabled'=>1,'order'=>80]
    ];
    foreach($d as $k=>&$v){$v['enabled']=(int)sk1303_setting('landing_sync_'.$k.'_enabled',(string)$v['enabled']);$v['order']=(int)sk1303_setting('landing_sync_'.$k.'_order',(string)$v['order']);}unset($v);return $d;
}
function sk1303_save_sections(array $post): int {$n=0;foreach(sk1303_landing_sections() as $k=>$v){$enabled=isset($post['enabled'][$k])?'1':'0';$order=(string)max(1,min(999,(int)($post['order'][$k]??$v['order'])));sk1303_save_setting('landing_sync_'.$k.'_enabled',$enabled);sk1303_save_setting('landing_sync_'.$k.'_order',$order);$n++;}return $n;}
function sk1303_landing_config(): array {return ['version'=>'13.0.3.4','sections'=>sk1303_landing_sections(),'business'=>sk1303_business_counts(),'identity'=>function_exists('sk1301_identity')?sk1301_identity():[],'theme'=>function_exists('sk1301_theme')?sk1301_theme():[]];}
function sk1303_landing_audit(): array {$c=sk1303_landing_config();$issues=[];if(empty($c['identity']['logo']))$issues[]='No resolved Logo Manager logo.';if(($c['business']['total']??0)>0&&($c['business']['active']??0)===0)$issues[]='Businesses exist but active/public count is zero.';return ['issues'=>$issues,'config'=>$c];}
}
