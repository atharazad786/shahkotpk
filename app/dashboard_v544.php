<?php
declare(strict_types=1);

/**
 * ShahkotPK v5.4.4 Neon Command Dashboard data adapter.
 * All queries fail soft so the dashboard never becomes a single point of failure.
 */
function v544_table_exists(string $table): bool {
    static $cache=[];
    if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$table))return false;
    if(array_key_exists($table,$cache))return $cache[$table];
    try{$q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$q->execute([$table]);return $cache[$table]=(bool)$q->fetchColumn();}
    catch(Throwable $e){return $cache[$table]=false;}
}
function v544_columns(string $table): array {
    static $cache=[];if(isset($cache[$table]))return $cache[$table];if(!v544_table_exists($table))return $cache[$table]=[];
    try{$q=db()->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$table]);return $cache[$table]=array_fill_keys(array_map('strval',$q->fetchAll(PDO::FETCH_COLUMN)),true);}catch(Throwable $e){return $cache[$table]=[];}
}
function v544_has_column(string $table,string $column): bool {return isset(v544_columns($table)[$column]);}
function v544_scalar(string $sql,array $params=[],$default=0){try{$q=db()->prepare($sql);$q->execute($params);$v=$q->fetchColumn();return $v===false?$default:$v;}catch(Throwable $e){return $default;}}
function v544_rows(string $sql,array $params=[]): array {try{$q=db()->prepare($sql);$q->execute($params);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}}
function v544_city_id(): int {return function_exists('tenant_query_city_id')?(int)tenant_query_city_id():0;}
function v544_tenant_id(): int {return function_exists('tenant_id')?(int)tenant_id():0;}
function v544_range_days(): int {$r=(int)($_GET['range']??7);return in_array($r,[7,30,90],true)?$r:7;}
function v544_pct_change(float $current,float $previous): float {if(abs($previous)<0.00001)return $current>0?100.0:0.0;return (($current-$previous)/abs($previous))*100;}
function v544_period_metric(string $table,string $aggregate='COUNT(*)',string $extraWhere='1=1',array $params=[],string $dateColumn='created_at'): array {
    if(!v544_table_exists($table)||!v544_has_column($table,$dateColumn))return ['current'=>0,'previous'=>0,'pct'=>0.0];
    $days=v544_range_days();
    $cur=(float)v544_scalar("SELECT {$aggregate} FROM `{$table}` WHERE {$extraWhere} AND `{$dateColumn}`>=DATE_SUB(NOW(),INTERVAL {$days} DAY)",$params,0);
    $prev=(float)v544_scalar("SELECT {$aggregate} FROM `{$table}` WHERE {$extraWhere} AND `{$dateColumn}`<DATE_SUB(NOW(),INTERVAL {$days} DAY) AND `{$dateColumn}`>=DATE_SUB(NOW(),INTERVAL ".($days*2)." DAY)",$params,0);
    return ['current'=>$cur,'previous'=>$prev,'pct'=>v544_pct_change($cur,$prev)];
}
function v544_users_total(): int {
    $tid=v544_tenant_id();
    if($tid>0&&v544_table_exists('tenant_members'))return (int)v544_scalar('SELECT COUNT(DISTINCT user_id) FROM tenant_members WHERE tenant_id=? AND status=1',[$tid],0);
    return v544_table_exists('users')?(int)v544_scalar('SELECT COUNT(*) FROM users',[],0):0;
}
function v544_active_subscriptions(): int {
    if(!v544_table_exists('subscriptions'))return 0;$city=v544_city_id();
    if($city>0&&v544_table_exists('businesses'))return (int)v544_scalar("SELECT COUNT(*) FROM subscriptions s JOIN businesses b ON b.id=s.business_id WHERE s.status='active' AND b.city_id=?",[$city],0);
    return (int)v544_scalar("SELECT COUNT(*) FROM subscriptions WHERE status='active'",[],0);
}
function v544_store_stats(): array {
    if(function_exists('store_stats')){$r=store_stats();if(is_array($r))return $r;}
    return ['orders'=>0,'gross_value'=>0,'products'=>0,'published'=>0];
}
function v544_page_views(): int {
    if(v544_table_exists('analytics_events')){
        $cols=v544_columns('analytics_events');$eventCol='';foreach(['event_name','event_type','event','name'] as $c)if(isset($cols[$c])){$eventCol=$c;break;}
        if($eventCol!==''){
            $v=(int)v544_scalar("SELECT COUNT(*) FROM analytics_events WHERE LOWER(`{$eventCol}`) IN ('page_view','page.view','view','pageview')",[],0);if($v>0)return $v;
        }
        return (int)v544_scalar('SELECT COUNT(*) FROM analytics_events',[],0);
    }
    if(function_exists('news_stats')){$n=news_stats();return (int)($n['total_views']??0);}return 0;
}
function v544_business_change(): array {
    if(!v544_table_exists('businesses'))return ['current'=>0,'previous'=>0,'pct'=>0];$city=v544_city_id();$where='1=1';$p=[];if($city>0){$where='city_id=?';$p[]=$city;}return v544_period_metric('businesses','COUNT(*)',$where,$p);
}
function v544_user_change(): array {return v544_period_metric('users','COUNT(*)');}
function v544_subscription_change(): array {return v544_period_metric('subscriptions','COUNT(*)',"status='active'");}
function v544_order_change(): array {
    if(!v544_table_exists('store_orders'))return ['current'=>0,'previous'=>0,'pct'=>0];$city=v544_city_id();
    if($city>0&&v544_table_exists('store_products'))return v544_period_metric('store_orders','COUNT(*)','EXISTS(SELECT 1 FROM store_products p WHERE p.seller_user_id=store_orders.seller_user_id AND p.city_id=?)',[$city]);
    return v544_period_metric('store_orders');
}
function v544_revenue_change(): array {
    if(!v544_table_exists('store_orders')||!v544_has_column('store_orders','grand_total'))return ['current'=>0,'previous'=>0,'pct'=>0];$city=v544_city_id();$where="status NOT IN ('cancelled')";$p=[];
    if($city>0&&v544_table_exists('store_products')){$where.=' AND EXISTS(SELECT 1 FROM store_products p WHERE p.seller_user_id=store_orders.seller_user_id AND p.city_id=?)';$p[]=$city;}
    return v544_period_metric('store_orders','COALESCE(SUM(grand_total),0)',$where,$p);
}
function v544_view_change(): array {
    if(v544_table_exists('analytics_events')&&v544_has_column('analytics_events','created_at'))return v544_period_metric('analytics_events');
    return ['current'=>0,'previous'=>0,'pct'=>0.0];
}
function v544_business_rating(int $businessId): float {
    if($businessId<1)return 0;
    if(v544_table_exists('business_reviews')&&v544_has_column('business_reviews','rating'))return (float)v544_scalar("SELECT COALESCE(AVG(rating),0) FROM business_reviews WHERE business_id=?". (v544_has_column('business_reviews','status')?" AND status IN ('published','approved')":''),[$businessId],0);
    if(v544_table_exists('reviews')&&v544_has_column('reviews','rating'))return (float)v544_scalar("SELECT COALESCE(AVG(rating),0) FROM reviews WHERE business_id=?". (v544_has_column('reviews','status')?" AND status IN ('published','approved')":''),[$businessId],0);
    return 0;
}
function v544_recent_businesses(int $limit=5): array {
    if(function_exists('city_business_list')){$rows=city_business_list(['sort'=>'new'],$limit);foreach($rows as &$r)$r['rating']=v544_business_rating((int)($r['id']??0));return $rows;}
    return [];
}
function v544_latest_orders(int $limit=5): array {
    if(!v544_table_exists('store_orders'))return [];$city=v544_city_id();$where=[];$p=[];
    if($city>0&&v544_table_exists('store_products')){$where[]='EXISTS(SELECT 1 FROM store_products sp WHERE sp.seller_user_id=o.seller_user_id AND sp.city_id=?)';$p[]=$city;}
    $sql='SELECT o.*'.(v544_table_exists('users')?',COALESCE(NULLIF(o.customer_name,\'\'),u.name) display_name':'').' FROM store_orders o'.(v544_table_exists('users')?' LEFT JOIN users u ON u.id=o.buyer_user_id':'').($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY o.id DESC LIMIT '.max(1,min(20,$limit));
    $rows=v544_rows($sql,$p);
    if(v544_table_exists('store_order_items')&&v544_table_exists('store_products'))foreach($rows as &$r){$img=v544_scalar('SELECT p.image_url FROM store_order_items oi JOIN store_products p ON p.id=oi.product_id WHERE oi.order_id=? ORDER BY oi.id LIMIT 1',[(int)$r['id']],'');$r['image_url']=(string)$img;}
    return $rows;
}
function v544_top_categories(int $limit=6): array {
    if(!v544_table_exists('businesses')||!v544_table_exists('categories'))return [];$city=v544_city_id();$where='b.status=1';$p=[];if($city>0){$where.=' AND b.city_id=?';$p[]=$city;}
    return v544_rows('SELECT c.id,c.name,COUNT(*) total FROM categories c JOIN businesses b ON b.category_id=c.id WHERE '.$where.' GROUP BY c.id,c.name ORDER BY total DESC,c.name LIMIT '.max(1,min(12,$limit)),$p);
}
function v544_subscription_statuses(): array {
    if(!v544_table_exists('subscriptions'))return [];$city=v544_city_id();$p=[];$join='';$where='1=1';if($city>0&&v544_table_exists('businesses')){$join=' JOIN businesses b ON b.id=s.business_id';$where='b.city_id=?';$p[]=$city;}
    return v544_rows('SELECT s.status,COUNT(*) total FROM subscriptions s'.$join.' WHERE '.$where.' GROUP BY s.status ORDER BY total DESC',$p);
}
function v544_daily_activity(int $days=7): array {
    $days=max(7,min(30,$days));$labels=[];$values=[];for($i=$days-1;$i>=0;$i--){$d=date('Y-m-d',strtotime('-'.$i.' days'));$labels[]=$days<=7?date('D',strtotime($d)):date('d M',strtotime($d));$values[$d]=0;}
    $table='';foreach(['analytics_events','tenant_audit_logs','activity_logs','ai_request_logs'] as $t)if(v544_table_exists($t)&&v544_has_column($t,'created_at')){$table=$t;break;}
    if($table!==''){$where='created_at>=DATE_SUB(CURDATE(),INTERVAL '.($days-1).' DAY)';$p=[];if($table==='tenant_audit_logs'&&v544_tenant_id()>0&&v544_has_column($table,'tenant_id')){$where.=' AND tenant_id=?';$p[]=v544_tenant_id();}$rows=v544_rows("SELECT DATE(created_at) d,COUNT(*) total FROM `{$table}` WHERE {$where} GROUP BY DATE(created_at) ORDER BY d",$p);foreach($rows as $r)if(isset($values[(string)$r['d']]))$values[(string)$r['d']]=(int)$r['total'];}
    return ['labels'=>$labels,'values'=>array_values($values),'source'=>$table?:'none'];
}
function v544_module_items(string $type,int $limit=3): array {
    if(in_array($type,['guide','event','job','property','deal'],true)&&function_exists('city_portal_filtered_items'))return city_portal_filtered_items($type,[],$limit);
    if($type==='news'&&function_exists('news_posts'))return news_posts([],$limit);
    if($type==='classified'&&v544_table_exists('classifieds')){$city=v544_city_id();$where="status IN ('published','active')";$p=[];if($city>0&&v544_has_column('classifieds','city_id')){$where.=' AND city_id=?';$p[]=$city;}return v544_rows('SELECT * FROM classifieds WHERE '.$where.' ORDER BY id DESC LIMIT '.max(1,min(12,$limit)),$p);}
    return [];
}
function v544_dashboard_data(): array {
    $biz=function_exists('city_business_stats')?city_business_stats():[];$store=v544_store_stats();$news=function_exists('news_stats')?news_stats():[];
    return [
      'businesses'=>(int)($biz['total']??0),'users'=>v544_users_total(),'subscriptions'=>v544_active_subscriptions(),'orders'=>(int)($store['orders']??0),
      'revenue'=>(float)($store['gross_value']??0),'views'=>v544_page_views(),'products'=>(int)($store['products']??0),'news_views'=>(int)($news['total_views']??0),
      'changes'=>['businesses'=>v544_business_change(),'users'=>v544_user_change(),'subscriptions'=>v544_subscription_change(),'orders'=>v544_order_change(),'revenue'=>v544_revenue_change(),'views'=>v544_view_change()],
      'recent_businesses'=>v544_recent_businesses(5),'latest_orders'=>v544_latest_orders(5),'categories'=>v544_top_categories(6),'subscription_status'=>v544_subscription_statuses(),'activity'=>v544_daily_activity(7),
      'map_stats'=>function_exists('google_maps_stats')?google_maps_stats():[],'news'=>v544_module_items('news',3),'events'=>v544_module_items('event',3),'jobs'=>v544_module_items('job',3),'property'=>v544_module_items('property',3),'classifieds'=>v544_module_items('classified',3),'guide'=>v544_module_items('guide',3),
    ];
}
