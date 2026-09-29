<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
require_once __DIR__.'/../app/dashboard_v544.php';
$me=require_permission('dashboard.view');
$data=v544_dashboard_data();
$range=v544_range_days();
$dashboardTheme=(string)setting('admin_dashboard_theme_v544','neon-command');
if(!in_array($dashboardTheme,['neon-command','system'],true))$dashboardTheme='neon-command';
$cityName=(string)(function_exists('tenant_brand')?tenant_brand('city_name','Shahkot'):'Shahkot');
$siteName=(string)(function_exists('tenant_brand')?tenant_brand('site_name','ShahkotPK'):'ShahkotPK');
$mapReady=function_exists('google_maps_ready')&&google_maps_ready()&&(!function_exists('setting_bool')||setting_bool('google_maps_admin_enabled',true));
$mapCenter=function_exists('google_maps_default_center')?google_maps_default_center():['lat'=>31.5709,'lng'=>73.4853,'zoom'=>14];
$mapEmbed=function_exists('google_maps_embed_url')?google_maps_embed_url((float)$mapCenter['lat'],(float)$mapCenter['lng'],(int)$mapCenter['zoom']):('https://maps.google.com/maps?q='.rawurlencode((string)$mapCenter['lat'].','.(string)$mapCenter['lng']).'&z='.(int)$mapCenter['zoom'].'&output=embed');
$dashboardMapMarkers=[];$dashboardMapApiKey='';$dashboardMapType='roadmap';
if($mapReady){
    try{
        $dashboardMapApiKey=function_exists('google_maps_api_key')?(string)google_maps_api_key():'';
        $dashboardMapType=function_exists('setting')?(string)setting('google_maps_map_type','roadmap'):'roadmap';
        if(function_exists('google_maps_markers'))$dashboardMapMarkers=google_maps_markers(['business','guide','deal','event','job','property'],true);
        if($dashboardMapApiKey==='')$mapReady=false;
    }catch(Throwable $e){$mapReady=false;$dashboardMapMarkers=[];}
}
$dashboardMapPayload=[];
foreach($dashboardMapMarkers as $m){
    $lat=(float)($m['lat']??0);$lng=(float)($m['lng']??0);
    if(!$lat||!$lng)continue;
    $dashboardMapPayload[]=[
      'type'=>(string)($m['type']??'business'),'title'=>(string)($m['title']??'Listing'),'subtitle'=>(string)($m['subtitle']??''),
      'address'=>(string)($m['address']??''),'lat'=>$lat,'lng'=>$lng,'url'=>(string)($m['url']??''),
      'featured'=>!empty($m['featured']),'verified'=>!empty($m['verified'])
    ];
}


function v544_money($n): string {return 'Rs '.number_format((float)$n,0);}
function v544_change_text(array $c): string {$p=(float)($c['pct']??0);return ($p>=0?'↑ ':'↓ ').number_format(abs($p),1).'%';}
function v544_change_class(array $c): string {return (float)($c['pct']??0)>=0?'up':'down';}
function v544_img(string $url,string $fallback='/assets/demo-v543/business-tech.webp'): string {$u=trim($url);return $u!==''?$u:$fallback;}
function v544_item_title(array $r,string $type): string {
    if($type==='news')return trim((string)($r['title_en']??$r['title_ur']??'Untitled'));
    return trim((string)($r['title']??$r['name']??'Untitled'));
}
function v544_order_status_class(string $s): string {$s=strtolower($s);if(in_array($s,['completed','delivered','paid'],true))return 'success';if(in_array($s,['pending','confirmed'],true))return 'warning';if(in_array($s,['cancelled','failed','refunded'],true))return 'danger';return 'info';}
function v544_pct_share(int $n,int $total): float {return $total>0?($n/$total)*100:0;}

page_start('Dashboard',true);
?>
<script>document.body.classList.add('v544-dashboard-page');document.body.dataset.v544DashboardTheme=<?=json_encode($dashboardTheme)?>;</script>
<link rel="stylesheet" href="/assets/admin-dashboard-neon-5.4.4.css?v=544">
<link rel="stylesheet" href="/assets/admin-dashboard-map-6.3.3.css?v=633">
<div class="v544-dashboard" id="v544Dashboard">
  <header class="v544-commandbar">
    <div class="v544-command-left">
      <button type="button" class="v544-icon-button" data-sidebar-toggle aria-label="Toggle sidebar">☰</button>
      <div class="v544-global-search" id="v544GlobalSearch"><span>⌕</span><input id="v544SearchInput" placeholder="Search businesses, orders, users, modules..." autocomplete="off"><kbd>Ctrl + K</kbd><div class="v544-search-results" id="v544SearchResults"></div></div>
    </div>
    <div class="v544-command-right">
      <div class="v544-quick-wrap"><button type="button" class="v544-quick-add" id="v544QuickAdd">✦ Quick Add <span>⌄</span></button><div class="v544-quick-menu" id="v544QuickMenu">
        <?php if(has_permission('businesses.manage')):?><a href="/admin/businesses.php?add=1">▤ Add Business</a><?php endif;?>
        <?php if(has_permission('cities.manage')):?><a href="/admin/cities.php?add=1">⌖ Add City</a><?php endif;?>
        <?php if(has_permission('users.manage')):?><a href="/admin/users.php?add=1">◉ Add User</a><?php endif;?>
        <?php if(has_permission('deals.manage')):?><a href="/admin/deals.php?add=1">% Add Deal</a><?php endif;?>
        <?php if(has_permission('news.manage')):?><a href="/admin/news.php?add=1">▰ Add News</a><?php endif;?>
        <?php if(has_permission('events.manage')):?><a href="/admin/events.php?add=1">◆ Add Event</a><?php endif;?>
      </div></div>
      <a class="v544-icon-button" href="/" target="_blank" title="View website">↗</a>
      <button type="button" class="v544-icon-button" id="v544Fullscreen" title="Fullscreen">⛶</button>
      <a class="v544-admin-chip" href="/admin/profile.php"><span class="v544-admin-avatar"><?=e(strtoupper(substr((string)($me['name']??'A'),0,1)))?></span><span><b><?=e((string)($me['name']??'System Administrator'))?></b><small><?=e((string)(core_role_registry()[$me['role']??'admin']['label']??'Administrator'))?></small></span><i>⌄</i></a>
    </div>
  </header>

  <section class="v544-page-head">
    <div><h2>Dashboard Overview <span>👋</span></h2><p>Welcome back, <?=e((string)($me['name']??'Administrator'))?>. Here is what is happening across <?=e($cityName)?>.</p></div>
    <div class="v544-filters">
      <div class="v544-select"><span>⌖</span><b><?=e($cityName)?></b><small>Active city</small></div>
      <form method="get" class="v544-range-form"><select name="range" onchange="this.form.submit()"><option value="7" <?=$range===7?'selected':''?>>Last 7 days</option><option value="30" <?=$range===30?'selected':''?>>Last 30 days</option><option value="90" <?=$range===90?'selected':''?>>Last 90 days</option></select></form>
      <a class="v544-refresh" href="?range=<?=$range?>&refresh=<?=time()?>">↻ Refresh</a>
      <?php if(has_permission('themes.manage')):?><a class="v544-icon-button" href="/admin/themes.php#dashboard-theme" title="Dashboard theme settings">⚙</a><?php endif;?>
    </div>
  </section>

  <?php
    $cards=[
      ['key'=>'businesses','label'=>'Total Businesses','value'=>number_format($data['businesses']),'icon'=>'▤','tone'=>'blue','link'=>'/admin/businesses.php'],
      ['key'=>'users','label'=>'Total Users','value'=>number_format($data['users']),'icon'=>'◉','tone'=>'purple','link'=>'/admin/users.php'],
      ['key'=>'subscriptions','label'=>'Active Subscriptions','value'=>number_format($data['subscriptions']),'icon'=>'♛','tone'=>'orange','link'=>'/admin/plans.php'],
      ['key'=>'orders','label'=>'Total Orders','value'=>number_format($data['orders']),'icon'=>'▣','tone'=>'green','link'=>'/admin/shop.php'],
      ['key'=>'revenue','label'=>'Total Revenue','value'=>v544_money($data['revenue']),'icon'=>'₨','tone'=>'pink','link'=>'/admin/payments.php'],
      ['key'=>'views','label'=>'Total Page Views','value'=>number_format($data['views']),'icon'=>'◉','tone'=>'cyan','link'=>'/admin/activity-logs.php'],
    ];
  ?>
  <section class="v544-kpis">
    <?php foreach($cards as $card):$chg=$data['changes'][$card['key']]??['pct'=>0];?>
    <a href="<?=e($card['link'])?>" class="v544-kpi tone-<?=e($card['tone'])?>">
      <div class="v544-kpi-top"><span class="v544-kpi-icon"><?=e($card['icon'])?></span><div><small><?=e($card['label'])?></small><strong><?=e($card['value'])?></strong></div></div>
      <div class="v544-kpi-change <?=v544_change_class($chg)?>"><b><?=e(v544_change_text($chg))?></b><span>vs previous <?=$range?> days</span></div>
      <svg class="v544-spark" viewBox="0 0 180 34" preserveAspectRatio="none" data-spark="<?=e($card['key'])?>"><path d="M0 28 C18 24 28 30 42 22 S70 25 82 18 112 24 124 16 150 20 180 10" fill="none" vector-effect="non-scaling-stroke"/></svg>
    </a><?php endforeach;?>
  </section>

  <section class="v544-main-grid">
    <article class="v544-panel v544-map-panel">
      <div class="v544-panel-head"><div><h3>Live Business Map <span class="v544-live">● Live</span></h3><p>Mapped businesses and city content around <?=e($cityName)?></p></div><a href="/city-discovery.php" target="_blank">View All</a></div>
      <div class="v544-map-wrap">
        <?php if($mapReady):?><div id="v633DashboardMap" class="v544-map v633-dashboard-map" aria-label="Live business map"></div><div id="v633MapStatus" class="v633-map-status" hidden></div>
        <?php else:?><iframe class="v544-map-fallback" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?=e($mapEmbed)?>"></iframe><div class="v544-map-notice"><b>Map preview mode</b><span>Interactive dashboard map is unavailable. Check Map Control and the Browser API key.</span></div><?php endif;?>
      </div>
      <div class="v544-map-legend">
        <?php $ms=$data['map_stats'];$legend=[['business','Business'],['deal','Deals'],['guide','City Guide'],['event','Events'],['job','Jobs'],['property','Property']];foreach($legend as $i=>$x):$m=(int)($ms[$x[0]]['mapped']??0);?><span class="c<?=$i+1?>"><i></i><?=e($x[1])?> <b><?=number_format($m)?></b></span><?php endforeach;?>
      </div>
    </article>

    <article class="v544-panel">
      <div class="v544-panel-head"><div><h3>Recent Businesses</h3><p>Newest directory listings</p></div><a href="/admin/businesses.php">View All</a></div>
      <div class="v544-business-list">
        <?php if(!$data['recent_businesses']):?><div class="v544-empty">No business listings yet.</div><?php endif;?>
        <?php foreach($data['recent_businesses'] as $b):?><a href="/admin/businesses.php?edit=<?=(int)$b['id']?>" class="v544-business-row"><img src="<?=e(v544_img((string)($b['image']??'')))?>" alt=""><div><b><?=e((string)$b['name'])?></b><span><?=e((string)($b['category_name']??'Business'))?></span><small><?=e((string)($b['address']??$b['city_name']??$cityName))?></small></div><div class="v544-rating">★ <?=number_format((float)($b['rating']??0),1)?><em><?=!empty($b['is_featured'])?'Featured':(($b['verification_status']??'')==='verified'?'Verified':'Active')?></em></div></a><?php endforeach;?>
      </div>
    </article>

    <article class="v544-panel">
      <div class="v544-panel-head"><div><h3>Latest Orders</h3><p>Marketplace activity</p></div><a href="/admin/shop.php">View All</a></div>
      <div class="v544-order-list">
        <?php if(!$data['latest_orders']):?><div class="v544-empty">No orders available yet.</div><?php endif;?>
        <?php foreach($data['latest_orders'] as $o):$st=(string)($o['status']??'pending');?><a href="/admin/shop.php?order=<?=(int)$o['id']?>" class="v544-order-row"><img src="<?=e(v544_img((string)($o['image_url']??''),'/assets/demo-v543/product-local.webp'))?>" alt=""><div><b><?=e((string)($o['order_number']??('#'.(int)$o['id'])))?></b><span><?=e((string)($o['display_name']??$o['customer_name']??'Customer'))?></span></div><strong><?=v544_money((float)($o['grand_total']??0))?></strong><em class="<?=e(v544_order_status_class($st))?>"><?=e(ucfirst($st))?></em></a><?php endforeach;?>
      </div>
    </article>
  </section>

  <section class="v544-analytics-grid">
    <article class="v544-panel v544-category-panel">
      <div class="v544-panel-head"><div><h3>Top Categories</h3><p>Business distribution</p></div><a href="/admin/categories.php">View All</a></div>
      <?php $catTotal=array_sum(array_map(fn($r)=>(int)$r['total'],$data['categories']));$stops=[];$acc=0;foreach($data['categories'] as $i=>$c){$pct=v544_pct_share((int)$c['total'],$catTotal);$stops[]='var(--cat'.(($i%6)+1).') '.$acc.'% '.($acc+$pct).'%';$acc+=$pct;}?>
      <div class="v544-donut-row"><div class="v544-donut" style="--donut:conic-gradient(<?=e($stops?implode(',',$stops):'#26354f 0 100%')?>)"><span><b><?=number_format($catTotal)?></b><small>Total</small></span></div><div class="v544-cat-list"><?php foreach($data['categories'] as $i=>$c):?><div><span><i class="cat<?=($i%6)+1?>"></i><?=e((string)$c['name'])?></span><b><?=number_format((int)$c['total'])?></b><small><?=number_format(v544_pct_share((int)$c['total'],$catTotal),0)?>%</small></div><?php endforeach;?></div></div>
    </article>

    <article class="v544-panel v544-visitor-panel">
      <div class="v544-panel-head"><div><h3>Visitors Overview</h3><p>Live platform activity · <?=e((string)$data['activity']['source'])?></p></div><span class="v544-total-pill"><?=number_format(array_sum($data['activity']['values']))?></span></div>
      <div class="v544-line-chart" data-values='<?=e(json_encode($data['activity']['values']))?>' data-labels='<?=e(json_encode($data['activity']['labels']))?>'><svg viewBox="0 0 520 190" preserveAspectRatio="none"><g class="gridlines"><line x1="0" y1="35" x2="520" y2="35"/><line x1="0" y1="80" x2="520" y2="80"/><line x1="0" y1="125" x2="520" y2="125"/><line x1="0" y1="170" x2="520" y2="170"/></g><polyline class="area-line" points=""></polyline><polyline class="main-line" points=""></polyline><g class="dots"></g></svg><div class="v544-chart-labels"></div></div>
      <div class="v544-chart-legend"><span><i></i>This period</span><span><i></i>Live database activity</span></div>
    </article>

    <article class="v544-panel v544-sub-panel">
      <div class="v544-panel-head"><div><h3>Subscription Status</h3><p>Current business plans</p></div><a href="/admin/plans.php">View All</a></div>
      <?php $subTotal=array_sum(array_map(fn($r)=>(int)$r['total'],$data['subscription_status']));$subColors=['#12d87b','#f5a623','#ff4d62','#8057ff','#21c7d9','#7b879d'];$ss=[];$a=0;foreach($data['subscription_status'] as $i=>$s){$p=v544_pct_share((int)$s['total'],$subTotal);$ss[]=$subColors[$i%count($subColors)].' '.$a.'% '.($a+$p).'%';$a+=$p;}?>
      <div class="v544-donut-row"><div class="v544-donut sub" style="--donut:conic-gradient(<?=e($ss?implode(',',$ss):'#26354f 0 100%')?>)"><span><b><?=number_format($subTotal)?></b><small>Total</small></span></div><div class="v544-sub-list"><?php foreach($data['subscription_status'] as $i=>$s):?><div><span><i style="background:<?=$subColors[$i%count($subColors)]?>"></i><?=e(ucwords(str_replace('_',' ',(string)$s['status'])))?></span><b><?=number_format((int)$s['total'])?></b><small><?=number_format(v544_pct_share((int)$s['total'],$subTotal),0)?>%</small></div><?php endforeach;?><?php if(!$data['subscription_status']):?><div class="v544-empty">No subscription records yet.</div><?php endif;?></div></div>
    </article>

    <article class="v544-panel v544-quick-panel">
      <div class="v544-panel-head"><div><h3>Quick Actions</h3><p>Common admin tasks</p></div></div>
      <div class="v544-quick-grid">
        <?php if(has_permission('businesses.manage')):?><a href="/admin/businesses.php?add=1"><i>▤</i><b>Add Business</b></a><?php endif;?>
        <?php if(has_permission('cities.manage')):?><a href="/admin/cities.php?add=1"><i>⌖</i><b>Add City</b></a><?php endif;?>
        <?php if(has_permission('users.manage')):?><a href="/admin/users.php?add=1"><i>◉</i><b>Add User</b></a><?php endif;?>
        <?php if(has_permission('deals.manage')):?><a href="/admin/deals.php?add=1"><i>%</i><b>Add Deal</b></a><?php endif;?>
        <?php if(has_permission('news.manage')):?><a href="/admin/news.php?add=1"><i>▰</i><b>Add News</b></a><?php endif;?>
        <?php if(has_permission('engagement.manage')):?><a href="/admin/engagement.php"><i>✉</i><b>Notification</b></a><?php endif;?>
      </div>
    </article>
  </section>

  <?php $moduleCards=[['news','News Portal','▰','/admin/news.php'],['events','Events','◆','/admin/events.php'],['jobs','Jobs Board','▣','/admin/jobs.php'],['property','Property','⌂','/admin/property.php'],['classifieds','Classifieds / Ads','◇','/admin/shop.php'],['guide','City Guide','⌖','/admin/city-guide.php']];?>
  <section class="v544-modules-row">
    <?php foreach($moduleCards as $idx=>$mc):$rows=$data[$mc[0]]??[];?><article class="v544-mini-panel tone<?=$idx+1?>"><div class="v544-mini-head"><span><?=$mc[2]?></span><b><?=e($mc[1])?></b><a href="<?=e($mc[3])?>">View All</a></div><ol><?php foreach(array_slice($rows,0,3) as $r):?><li><?=e(v544_item_title($r,$mc[0]==='classifieds'?'classified':$mc[0]))?></li><?php endforeach;?><?php if(!$rows):?><li class="empty">No records yet</li><?php endif;?></ol></article><?php endforeach;?>
  </section>

  <footer class="v544-footer"><span>© <?=date('Y')?> <?=e($siteName)?>. All rights reserved.</span><nav><a href="/admin/system-health.php">System Health</a><a href="/admin/regression-tests.php">Regression Tests</a><a href="/admin/updates.php">Updates</a><b>v6.3.3</b></nav><span>Crafted for <strong><?=e($cityName)?></strong> ♥</span></footer>
</div>
<script>window.ShahkotDashboard544={range:<?=$range?>,city:<?=json_encode($cityName)?>};window.ShahkotDashboardMap633=<?=json_encode(['enabled'=>$mapReady,'apiKey'=>$dashboardMapApiKey,'center'=>$mapCenter,'mapType'=>$dashboardMapType,'fitMarkers'=>function_exists('setting_bool')?setting_bool('google_maps_fit_markers',true):true,'markers'=>$dashboardMapPayload,'fallbackUrl'=>$mapEmbed],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?>;</script>
<script src="/assets/admin-dashboard-neon-5.4.4.js?v=544"></script>
<script src="/assets/admin-dashboard-map-6.3.3.js?v=633"></script>
<?php require __DIR__.'/../app/end.php'; ?>
