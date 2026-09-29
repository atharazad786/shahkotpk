<?php
declare(strict_types=1);


function city_portal_boost_order(string $entityType,string $alias='p'): string {
    if(!function_exists('v41_table_exists')||!v41_table_exists('listing_boosts'))return '';
    if(!in_array($entityType,['business','product','property','classified','job','deal'],true))return '';
    if(!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/',$alias))return '';
    return "(SELECT COUNT(*) FROM listing_boosts lb WHERE lb.entity_type='".$entityType."' AND lb.entity_id=".$alias.".id AND lb.status='active' AND lb.starts_at<=NOW() AND lb.ends_at>=NOW()) DESC,";
}
function city_portal_content_boost_order(string $type,string $alias='p'): string {
    $map=['property'=>'property','job'=>'job','deal'=>'deal'];
    return isset($map[$type])?city_portal_boost_order($map[$type],$alias):'';
}

function city_portal_types(): array {
    return [
        'guide'=>['label'=>'City Guide','url'=>'/city-guide.php','icon'=>'⌖'],
        'deal'=>['label'=>'Deals & Offers','url'=>'/deals.php','icon'=>'%'],
        'event'=>['label'=>'Events','url'=>'/events.php','icon'=>'◆'],
        'job'=>['label'=>'Local Jobs','url'=>'/jobs.php','icon'=>'▣'],
        'property'=>['label'=>'Property','url'=>'/property.php','icon'=>'⌂'],
    ];
}
function city_portal_type(string $type): array {return city_portal_types()[$type]??city_portal_types()['guide'];}
function city_portal_meta(array $item): array {$m=json_decode((string)($item['meta_json']??'{}'),true);return is_array($m)?$m:[];}
function city_portal_slug(string $title,int $ignore=0): string {
    $base=strtolower(trim((string)preg_replace('/[^a-z0-9]+/i','-',$title),'-'))?:'item';
    $slug=$base;$i=2;
    while(true){$q=db()->prepare("SELECT id FROM city_portal_items WHERE slug=?".($ignore?" AND id<>".(int)$ignore:"")." LIMIT 1");$q->execute([$slug]);if(!$q->fetchColumn())return $slug;$slug=$base.'-'.$i++;}
}
function city_portal_sync_statuses(): void {
    try{db()->exec("UPDATE city_portal_items SET status='expired' WHERE content_type IN ('deal','event','job') AND status='published' AND ends_at IS NOT NULL AND ends_at<NOW()");}catch(Throwable $e){}
}
function city_portal_items(string $type,int $limit=12,array $options=[]): array {
    city_portal_sync_statuses();if(!isset(city_portal_types()[$type]))return [];
    $where=["p.content_type=?","p.status='published'"];$params=[$type];
    if(!empty($options['city_id'])){$where[]='p.city_id=?';$params[]=(int)$options['city_id'];}elseif(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');
    if(!empty($options['featured']))$where[]='p.featured=1';
    if(in_array($type,['deal','event','job'],true))$where[]="(p.ends_at IS NULL OR p.ends_at>=NOW())";
    if($type==='event')$where[]="(p.starts_at IS NULL OR p.starts_at>=DATE_SUB(NOW(),INTERVAL 12 HOUR))";
    $sql="SELECT p.*,c.name city_name,b.name business_name,b.slug business_slug FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." ORDER BY ".city_portal_content_boost_order($type,'p')."p.featured DESC,p.sort_order ASC,COALESCE(p.starts_at,p.created_at) DESC,p.id DESC LIMIT ".max(1,min(100,$limit));
    $q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll();foreach($rows as &$r)$r['meta']=city_portal_meta($r);return $rows;
}
function city_portal_item(string $slug): ?array {
    city_portal_sync_statuses();$where=["p.slug=?","p.status='published'"];$params=[$slug];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');$q=db()->prepare("SELECT p.*,c.name city_name,b.name business_name,b.slug business_slug FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." LIMIT 1");$q->execute($params);$r=$q->fetch();if(!$r)return null;$r['meta']=city_portal_meta($r);return $r;
}
function business_hours_week(): array {return [0=>'Monday',1=>'Tuesday',2=>'Wednesday',3=>'Thursday',4=>'Friday',5=>'Saturday',6=>'Sunday'];}
function business_hours_for(int $businessId): array {
    $out=[];foreach(business_hours_week() as $i=>$day)$out[$i]=['weekday'=>$i,'day'=>$day,'is_closed'=>0,'open_time'=>'09:00','close_time'=>'21:00'];
    try{$q=db()->prepare("SELECT * FROM business_hours WHERE business_id=? ORDER BY weekday");$q->execute([$businessId]);foreach($q->fetchAll() as $r){$i=(int)$r['weekday'];$out[$i]=array_merge($out[$i],$r);$out[$i]['open_time']=$r['open_time']?substr((string)$r['open_time'],0,5):'';$out[$i]['close_time']=$r['close_time']?substr((string)$r['close_time'],0,5):'';}}catch(Throwable $e){}
    return $out;
}
function save_business_hours(int $businessId,array $posted): void {
    $q=db()->prepare("INSERT INTO business_hours(business_id,weekday,is_closed,open_time,close_time) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE is_closed=VALUES(is_closed),open_time=VALUES(open_time),close_time=VALUES(close_time)");
    foreach(business_hours_week() as $i=>$day){$r=is_array($posted[$i]??null)?$posted[$i]:[];$closed=isset($r['closed'])?1:0;$open=trim((string)($r['open']??''))?:null;$close=trim((string)($r['close']??''))?:null;if($closed){$open=null;$close=null;}$q->execute([$businessId,$i,$closed,$open,$close]);}
}
function business_open_status(int $businessId): array {
    try{
        $day=(int)date('N')-1;$q=db()->prepare("SELECT * FROM business_hours WHERE business_id=? AND weekday=? LIMIT 1");$q->execute([$businessId,$day]);$h=$q->fetch();
        if(!$h)return ['known'=>false,'open'=>false,'label'=>'Hours not set'];
        if($h['is_closed'])return ['known'=>true,'open'=>false,'label'=>'Closed today'];
        if(!$h['open_time']||!$h['close_time'])return ['known'=>false,'open'=>false,'label'=>'Hours not set'];
        $now=date('H:i:s');$open=$now>=$h['open_time']&&$now<=$h['close_time'];
        return ['known'=>true,'open'=>$open,'label'=>$open?'Open now':'Closed now','hours'=>substr($h['open_time'],0,5).'–'.substr($h['close_time'],0,5)];
    }catch(Throwable $e){return ['known'=>false,'open'=>false,'label'=>'Hours not set'];}
}
function city_portal_businesses(string $mode,int $limit=8): array {
    $where=["b.status=1","b.verification_status<>'suspended'"];$params=[];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'b.city_id');if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'b.city_id');
    $order=city_portal_boost_order('business','b')."b.is_featured DESC,b.id DESC";
    if($mode==='new')$order="b.created_at DESC";
    if($mode==='restaurants')$where[]="(LOWER(cat.slug) LIKE '%restaurant%' OR LOWER(cat.slug) LIKE '%food%' OR LOWER(cat.name) LIKE '%restaurant%' OR LOWER(cat.name) LIKE '%food%')";
    $sql="SELECT b.*,c.name city_name,cat.name category_name,bd.tagline,bd.latitude,bd.longitude,bd.map_url,bd.opening_hours FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id LEFT JOIN business_details bd ON bd.business_id=b.id WHERE ".implode(' AND ',$where)." ORDER BY ".$order." LIMIT ".max(1,min(50,$limit));
    try{$q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll();foreach($rows as &$b)$b['open_status']=business_open_status((int)$b['id']);return $rows;}catch(Throwable $e){return [];}
}
function city_portal_category_counts(int $limit=18): array {
    try{$city=function_exists('tenant_query_city_id')?tenant_query_city_id():0;$sql="SELECT cat.id,cat.name,cat.slug,COUNT(b.id) business_count FROM categories cat LEFT JOIN businesses b ON b.category_id=cat.id AND b.status=1".($city>0?' AND b.city_id=?':'')." WHERE cat.status=1 GROUP BY cat.id ORDER BY business_count DESC,cat.name LIMIT ".max(1,min(50,$limit));$q=db()->prepare($sql);$q->execute($city>0?[$city]:[]);return $q->fetchAll();}catch(Throwable $e){return [];}
}
function city_portal_cities(): array {try{$city=function_exists('tenant_query_city_id')?tenant_query_city_id():0;if($city>0){$q=db()->prepare("SELECT id,name,slug FROM cities WHERE status=1 AND id=? ORDER BY name");$q->execute([$city]);return $q->fetchAll();}return db()->query("SELECT id,name,slug FROM cities WHERE status=1 ORDER BY name")->fetchAll();}catch(Throwable $e){return [];}}
function city_portal_context(): array {
    return [
        'categoryCounts'=>city_portal_category_counts(18),
        'deals'=>city_portal_items('deal',setting_int('homepage_deals_limit',6)),
        'guides'=>city_portal_items('guide',setting_int('homepage_guide_limit',6)),
        'events'=>city_portal_items('event',setting_int('homepage_events_limit',4)),
        'jobs'=>city_portal_items('job',setting_int('homepage_jobs_limit',5)),
        'properties'=>city_portal_items('property',setting_int('homepage_property_limit',6)),
        'newBusinesses'=>city_portal_businesses('new',setting_int('homepage_new_business_limit',6)),
        'nearbyBusinesses'=>city_portal_businesses('nearby',setting_int('homepage_nearby_limit',8)),
        'restaurants'=>city_portal_businesses('restaurants',setting_int('homepage_restaurant_limit',6)),
        'cities'=>city_portal_cities(),
    ];
}
function city_portal_default_home_sections(): array {
    return [
        ['id'=>'category_explorer','type'=>'category_explorer','enabled'=>1,'order'=>42,'title'=>'Explore Shahkot','subtitle'=>'Everything around you','content'=>'Browse popular local categories and discover useful businesses faster.','image'=>'','items'=>''],
        ['id'=>'city_map','type'=>'city_map','enabled'=>1,'order'=>52,'title'=>'Explore Shahkot on Map','subtitle'=>'Interactive city discovery','content'=>'Search businesses, shops, City Guide places, events, jobs and property directly on Google Maps.','image'=>'','items'=>''],
        ['id'=>'deals_offers','type'=>'deals','enabled'=>1,'order'=>62,'title'=>'Deals & Offers','subtitle'=>'Save locally','content'=>'Fresh offers and promotions from Shahkot businesses.','image'=>'','items'=>''],
        ['id'=>'sponsored_spotlight','type'=>'sponsored_spotlight','enabled'=>1,'order'=>66,'title'=>'Sponsored Spotlight','subtitle'=>'Local promotion','content'=>'Premium local business advertising.','image'=>'','items'=>''],
        ['id'=>'new_businesses','type'=>'new_businesses','enabled'=>1,'order'=>74,'title'=>'New in Shahkot','subtitle'=>'Recently added','content'=>'Discover businesses that recently joined the city portal.','image'=>'','items'=>''],
        ['id'=>'city_guide_portal','type'=>'city_guide','enabled'=>1,'order'=>82,'title'=>'Complete City Guide','subtitle'=>'Useful Shahkot information','content'=>'Hospitals, schools, markets, government services, transport, places and city essentials.','image'=>'','items'=>''],
        ['id'=>'near_you','type'=>'nearby','enabled'=>1,'order'=>86,'title'=>'Popular Near You','subtitle'=>'Local discovery','content'=>'Allow location access to bring nearby listings to the top.','image'=>'','items'=>''],
        ['id'=>'food_restaurants','type'=>'restaurants','enabled'=>1,'order'=>88,'title'=>'Food & Restaurants','subtitle'=>'Eat local','content'=>'Find restaurants, cafes and food businesses around Shahkot.','image'=>'','items'=>''],
        ['id'=>'city_events','type'=>'events','enabled'=>1,'order'=>92,'title'=>'Events & Announcements','subtitle'=>'What is happening','content'=>'Local openings, community programs, sales and important events.','image'=>'','items'=>''],
        ['id'=>'local_jobs','type'=>'jobs','enabled'=>1,'order'=>94,'title'=>'Jobs in Shahkot','subtitle'=>'Local opportunities','content'=>'Vacancies from shops, offices and local businesses.','image'=>'','items'=>''],
        ['id'=>'property_rentals','type'=>'property','enabled'=>1,'order'=>96,'title'=>'Property & Rentals','subtitle'=>'Buy, sell or rent','content'=>'Explore local houses, shops, offices and property listings.','image'=>'','items'=>''],
        ['id'=>'advertise_cta','type'=>'advertise_cta','enabled'=>1,'order'=>104,'title'=>'Reach People in Shahkot','subtitle'=>'Advertise on ShahkotPK','content'=>'Promote your shop, offer, event or brand with measurable local advertising placements.','image'=>'','items'=>'Advertise Now||/pricing.php|'],
    ];
}
function city_portal_upgrade_homepage(): void {
    try{
        if(setting_int('city_portal_homepage_version',0)>=310)return;
        $page=cms_home_page();if(!$page)return;$sections=cms_page_sections($page);$ids=[];foreach($sections as $s)$ids[(string)($s['id']??'')]=1;
        foreach(city_portal_default_home_sections() as $s)if(empty($ids[$s['id']]))$sections[]=$s;
        usort($sections,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));$order=10;foreach($sections as &$s){$s['order']=$order;$order+=10;}unset($s);
        cms_create_revision($page,null);$json=cms_encode_layout($sections);
        db()->prepare("UPDATE cms_pages SET layout_json=?,theme_slug='city-guide-pro',updated_at=NOW() WHERE id=?")->execute([$json,$page['id']]);
        save_setting('homepage_sections',$json);save_setting('landing_theme_slug','city-guide-pro');save_setting('city_portal_homepage_version','310');
    }catch(Throwable $e){}
}
function city_portal_card_url(array $item): string {
    $type=(string)$item['content_type'];return city_portal_type($type)['url'].'?slug='.urlencode((string)$item['slug']);
}


function city_portal_type_stats(string $type): array {
    if(!isset(city_portal_types()[$type])) return ['total'=>0,'published_count'=>0,'featured_count'=>0,'draft_count'=>0,'expired_count'=>0,'city_count'=>0,'latest_at'=>null];
    try{
        $where=['content_type=?'];$params=[$type];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'city_id');$q=db()->prepare("SELECT COUNT(*) total, SUM(status='published') published_count, SUM(featured=1) featured_count, SUM(status='draft') draft_count, SUM(status='expired') expired_count, COUNT(DISTINCT city_id) city_count, MAX(COALESCE(starts_at,created_at)) latest_at FROM city_portal_items WHERE ".implode(' AND ',$where));
        $q->execute($params);
        return $q->fetch()?:['total'=>0,'published_count'=>0,'featured_count'=>0,'draft_count'=>0,'expired_count'=>0,'city_count'=>0,'latest_at'=>null];
    }catch(Throwable $e){return ['total'=>0,'published_count'=>0,'featured_count'=>0,'draft_count'=>0,'expired_count'=>0,'city_count'=>0,'latest_at'=>null];}
}
function city_portal_featured_item(string $type): ?array {
    if(!isset(city_portal_types()[$type])) return null;
    city_portal_sync_statuses();
    try{
        $where=["p.content_type=?","p.status='published'","(p.ends_at IS NULL OR p.ends_at>=NOW())"];$params=[$type];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');$q=db()->prepare("SELECT p.*,c.name city_name,b.name business_name,b.slug business_slug FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." ORDER BY ".city_portal_content_boost_order($type,'p')."p.featured DESC,p.sort_order ASC,COALESCE(p.starts_at,p.created_at) DESC,p.id DESC LIMIT 1");
        $q->execute($params);$r=$q->fetch();if(!$r)return null;$r['meta']=city_portal_meta($r);return $r;
    }catch(Throwable $e){return null;}
}
function city_portal_related_items(string $type,string $excludeSlug,int $limit=3): array {
    city_portal_sync_statuses();
    if(!isset(city_portal_types()[$type])) return [];
    try{
        $where=["p.content_type=?","p.status='published'","p.slug<>?","(p.ends_at IS NULL OR p.ends_at>=NOW())"];$params=[$type,$excludeSlug];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');$q=db()->prepare("SELECT p.*,c.name city_name,b.name business_name,b.slug business_slug FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." ORDER BY ".city_portal_content_boost_order($type,'p')."p.featured DESC,p.sort_order ASC,COALESCE(p.starts_at,p.created_at) DESC,p.id DESC LIMIT ".max(1,min(12,$limit)));
        $q->execute($params);$rows=$q->fetchAll();foreach($rows as &$r)$r['meta']=city_portal_meta($r);return $rows;
    }catch(Throwable $e){return [];}
}
function city_portal_grouped_cities(string $type,int $limit=6): array {
    if(!isset(city_portal_types()[$type])) return [];
    try{
        $where=["p.content_type=?","p.status='published'"];$params=[$type];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');$q=db()->prepare("SELECT c.name city_name,c.slug city_slug,COUNT(*) total FROM city_portal_items p JOIN cities c ON c.id=p.city_id WHERE ".implode(' AND ',$where)." GROUP BY c.id,c.name,c.slug ORDER BY total DESC,c.name LIMIT ".max(1,min(20,$limit)));
        $q->execute($params);return $q->fetchAll();
    }catch(Throwable $e){return [];}
}
function city_portal_grouped_categories(string $type,int $limit=8): array {
    if(!isset(city_portal_types()[$type])) return [];
    try{
        $q=db()->prepare("SELECT COALESCE(NULLIF(category,''),'General') category_name,COUNT(*) total FROM city_portal_items WHERE content_type=? AND status='published' GROUP BY COALESCE(NULLIF(category,''),'General') ORDER BY total DESC,category_name LIMIT ".max(1,min(20,$limit)));
        $q->execute([$type]);return $q->fetchAll();
    }catch(Throwable $e){return [];}
}
function city_portal_upcoming_expiry(string $type,int $limit=5): array {
    if(!isset(city_portal_types()[$type])) return [];
    try{
        $q=db()->prepare("SELECT id,title,slug,ends_at,city_id FROM city_portal_items WHERE content_type=? AND status='published' AND ends_at IS NOT NULL AND ends_at>=NOW() ORDER BY ends_at ASC LIMIT ".max(1,min(20,$limit)));
        $q->execute([$type]);return $q->fetchAll();
    }catch(Throwable $e){return [];}
}
function city_business_stats(): array {
    try{
        $where=['1=1'];$params=[];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'city_id');$q=db()->prepare("SELECT COUNT(*) total, SUM(status=1) active_count, SUM(verification_status='verified') verified_count, SUM(is_featured=1) featured_count, COUNT(DISTINCT city_id) city_count, COUNT(DISTINCT category_id) category_count FROM businesses WHERE ".implode(' AND ',$where));$q->execute($params);$row=$q->fetch();
        return $row?:['total'=>0,'active_count'=>0,'verified_count'=>0,'featured_count'=>0,'city_count'=>0,'category_count'=>0];
    }catch(Throwable $e){return ['total'=>0,'active_count'=>0,'verified_count'=>0,'featured_count'=>0,'city_count'=>0,'category_count'=>0];}
}
function city_business_list(array $filters=[],int $limit=12): array {
    $where=["b.status=1","b.verification_status<>'suspended'"];$params=[];
    if(!empty($filters['featured']))$where[]='b.is_featured=1';
    if(!empty($filters['verified']))$where[]="b.verification_status='verified'";
    if(!empty($filters['category_slug'])){$where[]='cat.slug=?';$params[]=(string)$filters['category_slug'];}
    $order=city_portal_boost_order('business','b').'b.is_featured DESC,b.id DESC';
    if(($filters['sort']??'')==='new')$order='b.id DESC';
    if(($filters['sort']??'')==='alphabet')$order='b.name ASC';
    $sql="SELECT b.*,c.name city_name,c.slug city_slug,cat.name category_name,cat.slug category_slug,bd.tagline,bd.map_url,bd.website,bd.price_range FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id LEFT JOIN business_details bd ON bd.business_id=b.id WHERE ".implode(' AND ',$where)." ORDER BY ".$order." LIMIT ".max(1,min(60,$limit));
    try{$q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll();foreach($rows as &$b)$b['open_status']=business_open_status((int)$b['id']);return $rows;}catch(Throwable $e){return [];}
}


function city_portal_filtered_items(string $type,array $filters=[],int $limit=60): array {
    city_portal_sync_statuses();
    if(!isset(city_portal_types()[$type]))return [];
    $where=["p.content_type=?","p.status='published'"];$params=[$type];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');
    if(in_array($type,['deal','event','job'],true))$where[]="(p.ends_at IS NULL OR p.ends_at>=NOW())";
    $q=trim((string)($filters['q']??''));
    if($q!==''){$where[]='(p.title LIKE ? OR p.summary LIKE ? OR p.description LIKE ? OR p.category LIKE ? OR b.name LIKE ?)';$like='%'.$q.'%';array_push($params,$like,$like,$like,$like,$like);}
    $city=trim((string)($filters['city']??''));if($city!==''){$where[]='c.slug=?';$params[]=$city;}
    $category=trim((string)($filters['category']??''));if($category!==''){$where[]='p.category=?';$params[]=$category;}
    $sql="SELECT p.*,c.name city_name,c.slug city_slug,b.name business_name,b.slug business_slug FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." ORDER BY ".city_portal_content_boost_order($type,'p')."p.featured DESC,p.sort_order ASC,COALESCE(p.starts_at,p.created_at) DESC,p.id DESC LIMIT ".max(1,min(200,$limit));
    try{$st=db()->prepare($sql);$st->execute($params);$rows=$st->fetchAll();foreach($rows as &$r)$r['meta']=city_portal_meta($r);return $rows;}catch(Throwable $e){return [];}
}
function city_portal_filter_categories(string $type): array {
    if(!isset(city_portal_types()[$type]))return [];
    try{$where=["content_type=?","status='published'","category IS NOT NULL","category<>''"];$params=[$type];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'city_id');$q=db()->prepare("SELECT category,COUNT(*) total FROM city_portal_items WHERE ".implode(' AND ',$where)." GROUP BY category ORDER BY total DESC,category LIMIT 40");$q->execute($params);return $q->fetchAll();}catch(Throwable $e){return [];}
}
function city_portal_monthly_trend(string $type,int $months=6): array {
    $months=max(3,min(12,$months));$out=[];
    for($i=$months-1;$i>=0;$i--){$ts=strtotime('-'.$i.' months');$ym=date('Y-m',$ts);$out[$ym]=['ym'=>$ym,'label'=>date('M',$ts),'total'=>0,'published'=>0];}
    try{$q=db()->prepare("SELECT DATE_FORMAT(created_at,'%Y-%m') ym,COUNT(*) total,SUM(status='published') published FROM city_portal_items WHERE content_type=? AND created_at>=DATE_SUB(CURDATE(),INTERVAL ".($months-1)." MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m')");$q->execute([$type]);foreach($q->fetchAll() as $r)if(isset($out[$r['ym']])){$out[$r['ym']]['total']=(int)$r['total'];$out[$r['ym']]['published']=(int)$r['published'];}}catch(Throwable $e){}
    return array_values($out);
}
function city_portal_admin_records(string $type,array $filters=[],int $limit=300): array {
    if(!isset(city_portal_types()[$type]))return [];
    $where=['p.content_type=?'];$params=[$type];
    $q=trim((string)($filters['q']??''));if($q!==''){$where[]='(p.title LIKE ? OR p.summary LIKE ? OR p.category LIKE ? OR b.name LIKE ?)';$like='%'.$q.'%';array_push($params,$like,$like,$like,$like);}
    $status=trim((string)($filters['status']??''));if(in_array($status,['draft','published','expired'],true)){$where[]='p.status=?';$params[]=$status;}
    $city=(int)($filters['city_id']??0);if($city){$where[]='p.city_id=?';$params[]=$city;}elseif(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'p.city_id');
    if(!empty($filters['featured']))$where[]='p.featured=1';
    $sql="SELECT p.*,c.name city_name,b.name business_name FROM city_portal_items p JOIN cities c ON c.id=p.city_id LEFT JOIN businesses b ON b.id=p.business_id WHERE ".implode(' AND ',$where)." ORDER BY p.featured DESC,p.sort_order ASC,p.id DESC LIMIT ".max(1,min(1000,$limit));
    try{$st=db()->prepare($sql);$st->execute($params);return $st->fetchAll();}catch(Throwable $e){return [];}
}
