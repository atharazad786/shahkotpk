<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only');}
require __DIR__.'/../app/bootstrap.php';require_once __DIR__.'/../app/growth_v1020.php';
$result=['checked'=>0,'notifications'=>0,'saved_searches'=>0,'pruned'=>0,'warnings'=>[]];
try{
  if(sk1020_table_exists('growth_alerts_v1020')){
    $q=db()->query("SELECT * FROM growth_alerts_v1020 WHERE enabled=1 AND alert_type IN ('price_drop','updates') ORDER BY id LIMIT 500");
    foreach($q->fetchAll() as $a){$result['checked']++;$r=sk1020_fetch_entity((string)$a['entity_type'],(int)$a['entity_id'],(int)$a['tenant_id']);if(!$r)continue;$now=sk1020_value($r,['sale_price','price','amount'],null);$old=$a['last_value'];if($a['alert_type']==='price_drop'&&is_numeric($now)&&is_numeric($old)&&(float)$now<(float)$old){db()->prepare('INSERT INTO growth_notifications_v1020(tenant_id,visitor_key,user_id,notification_type,title,body,url) VALUES(?,?,?,?,?,?,?)')->execute([(int)$a['tenant_id'],$a['visitor_key'],(int)$a['user_id'],'price_drop','Price dropped: '.sk1020_title($r),'New price: PKR '.number_format((float)$now),(string)($r['_url']??'/')]);$result['notifications']++;}if(is_numeric($now))db()->prepare('UPDATE growth_alerts_v1020 SET last_value=?,updated_at=NOW() WHERE id=?')->execute([$now,(int)$a['id']]);}
  }
  if(sk1020_table_exists('growth_saved_searches_v1020')){
    $q=db()->query('SELECT * FROM growth_saved_searches_v1020 WHERE alerts_enabled=1 ORDER BY COALESCE(last_checked_at,\'2000-01-01\') ASC LIMIT 250');
    foreach($q->fetchAll() as $ss){$result['saved_searches']++;$last=(string)($ss['last_checked_at']??'');$type=(string)($ss['search_type']??'all');$query=trim((string)$ss['query_text']);$found=0;$url='/search.php?q='.rawurlencode($query);$maps=[];if($type==='business')$maps=[['businesses',['name','title'],[]]];elseif($type==='product')$maps=[['store_products',['title','name'],[]]];elseif($type==='property')$maps=[['city_portal_items',['title','name'],['content_type'=>'property']]];elseif($type==='job')$maps=[['city_portal_items',['title','name'],['content_type'=>'job']]];else $maps=[['businesses',['name','title'],[]],['store_products',['title','name'],[]],['city_portal_items',['title','name'],[]]];
      if($last!=='')foreach($maps as [$table,$names,$extra]){if(!sk1020_table_exists($table))continue;$cols=sk1020_columns($table);$titleCol='';foreach($names as $n)if(in_array($n,$cols,true)){$titleCol=$n;break;}if($titleCol===''||!in_array('created_at',$cols,true))continue;$w=["`$titleCol` LIKE ?",'created_at>?'];$params=['%'.$query.'%',$last];if((int)$ss['tenant_id']>0&&in_array('tenant_id',$cols,true)){$w[]='tenant_id=?';$params[]=(int)$ss['tenant_id'];}foreach($extra as $k=>$v)if(in_array($k,$cols,true)){$w[]="`$k`=?";$params[]=$v;}try{$st=db()->prepare('SELECT COUNT(*) FROM `'.$table.'` WHERE '.implode(' AND ',$w));$st->execute($params);$found+=(int)$st->fetchColumn();}catch(Throwable $e){}}
      if($last!==''&&$found>0){db()->prepare('INSERT INTO growth_notifications_v1020(tenant_id,visitor_key,user_id,notification_type,title,body,url) VALUES(?,?,?,?,?,?,?)')->execute([(int)$ss['tenant_id'],$ss['visitor_key'],(int)$ss['user_id'],'new_listing','New matches for “'.$query.'”',$found.' new listing'.($found===1?'':'s').' matched your saved search.',$url]);$result['notifications']++;}
      db()->prepare('UPDATE growth_saved_searches_v1020 SET last_checked_at=NOW() WHERE id=?')->execute([(int)$ss['id']]);
    }
  }
  if(sk1020_table_exists('visitor_events_v1020')){$n=db()->exec('DELETE FROM visitor_events_v1020 WHERE created_at<DATE_SUB(NOW(),INTERVAL 180 DAY)');$result['pruned']=(int)$n;}
}catch(Throwable $e){$result['warnings'][]=$e->getMessage();}
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
