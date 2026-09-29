<?php
declare(strict_types=1);

/**
 * ShahkotPK v5.3.0 Homepage Builder 2.0
 * Theme-specific, tenant-aware draft/publish homepage layouts.
 * Runtime is deliberately additive: if no v5.3 profile exists, the existing CMS homepage is untouched.
 */

function hb53_table_exists(string $table): bool {
    static $cache=[];
    if(isset($cache[$table])) return $cache[$table];
    try{
        $q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$table]);
        return $cache[$table]=(bool)$q->fetchColumn();
    }catch(Throwable $e){return $cache[$table]=false;}
}

function hb53_scope(): array {
    $tenant=function_exists('current_tenant')?current_tenant():null;
    $tid=(int)($tenant['id']??0);
    $isMaster=!empty($tenant['is_master']);
    if($tid<1 || $isMaster){
        return ['scope_key'=>'global','tenant_id'=>null,'is_global'=>true,'label'=>'Global / Master Template'];
    }
    return ['scope_key'=>'tenant:'.$tid,'tenant_id'=>$tid,'is_global'=>false,'label'=>(string)($tenant['site_name']??('Tenant #'.$tid))];
}

function hb53_section_registry(): array {
    return [
        'topbar'=>['label'=>'Top Information Bar','icon'=>'≋','title'=>'City Utility Bar','subtitle'=>'Live city information','content'=>'Weather, time, emergency and live shortcuts.','data'=>false],
        'header'=>['label'=>'Header / Navigation','icon'=>'☰','title'=>'ShahkotPK','subtitle'=>'Complete City Guide','content'=>'Primary public navigation and tenant branding.','data'=>false],
        'hero'=>['label'=>'Hero + Smart Search','icon'=>'⌕','title'=>'Everything you need in Shahkot','subtitle'=>'SHAHKOT LOCAL DISCOVERY','content'=>'Find businesses, services, offers, jobs, events and property.','data'=>true],
        'slider'=>['label'=>'Hero Slider / Banners','icon'=>'▰','title'=>'Featured Highlights','subtitle'=>'Latest highlights','content'=>'Homepage slider managed from Banner Manager.','data'=>false],
        'info_cards'=>['label'=>'City Information Cards','icon'=>'▦','title'=>'City at a Glance','subtitle'=>'Useful information','content'=>'Quick city facts and useful information.','data'=>false],
        'quick_services'=>['label'=>'Quick Services','icon'=>'⚡','title'=>'Quick Services','subtitle'=>'Useful shortcuts','content'=>'Open useful city services faster.','data'=>false],
        'category_explorer'=>['label'=>'Category Explorer','icon'=>'◫','title'=>'Explore Shahkot','subtitle'=>'Everything around you','content'=>'Browse popular local categories.','data'=>true],
        'directory'=>['label'=>'Business Categories','icon'=>'▤','title'=>'Business Directory','subtitle'=>'Browse by category','content'=>'Find local businesses by category.','data'=>true],
        'featured'=>['label'=>'Featured Businesses','icon'=>'★','title'=>'Featured Businesses','subtitle'=>'Recommended locally','content'=>'Discover promoted and trusted local businesses.','data'=>true],
        'sponsored_spotlight'=>['label'=>'Sponsored Spotlight','icon'=>'◆','title'=>'Sponsored Spotlight','subtitle'=>'Local promotion','content'=>'Premium local business advertising.','data'=>true],
        'ads'=>['label'=>'Advertisements','icon'=>'AD','title'=>'Local Promotions','subtitle'=>'Sponsored','content'=>'Active homepage advertisements from the Ads Manager.','data'=>false],
        'new_businesses'=>['label'=>'New Businesses','icon'=>'＋','title'=>'New in Shahkot','subtitle'=>'Recently added','content'=>'Discover businesses that recently joined.','data'=>true],
        'nearby'=>['label'=>'Nearby Businesses','icon'=>'◎','title'=>'Popular Near You','subtitle'=>'Local discovery','content'=>'Nearby listings based on city and location.','data'=>true],
        'restaurants'=>['label'=>'Food & Restaurants','icon'=>'☷','title'=>'Food & Restaurants','subtitle'=>'Eat local','content'=>'Restaurants, cafes and food businesses.','data'=>true],
        'city_guide'=>['label'=>'City Guide','icon'=>'⌖','title'=>'Complete City Guide','subtitle'=>'Useful city information','content'=>'Hospitals, schools, markets, transport and essentials.','data'=>true],
        'city_map'=>['label'=>'Advanced Location Discovery','icon'=>'◎','title'=>'Discover What Is Near You','subtitle'=>'Live map · radius · nearby','content'=>'Use live location, radius search and smart filters to find businesses, deals, events, jobs and property around you.','data'=>true],
        'deals'=>['label'=>'Deals & Offers','icon'=>'%','title'=>'Deals & Offers','subtitle'=>'Save locally','content'=>'Fresh promotions from local businesses.','data'=>true],
        'events'=>['label'=>'Events','icon'=>'◆','title'=>'Events & Announcements','subtitle'=>'What is happening','content'=>'Local events and important announcements.','data'=>true],
        'jobs'=>['label'=>'Jobs','icon'=>'▣','title'=>'Jobs in Shahkot','subtitle'=>'Local opportunities','content'=>'Vacancies from local businesses and offices.','data'=>true],
        'property'=>['label'=>'Property','icon'=>'⌂','title'=>'Property & Rentals','subtitle'=>'Buy, sell or rent','content'=>'Local homes, plots, shops and rentals.','data'=>true],
        'news_widgets'=>['label'=>'News','icon'=>'▰','title'=>'Latest News & Video','subtitle'=>'SHAHKOTPK NEWSROOM','content'=>'Breaking, English, Urdu and video news.','data'=>true],
        'blog_widgets'=>['label'=>'Blog','icon'=>'✎','title'=>'From the ShahkotPK Blog','subtitle'=>'Stories & guides','content'=>'Useful local stories, guides and perspectives.','data'=>true],
        'shop_widget'=>['label'=>'Shop / Marketplace','icon'=>'◇','title'=>'Shahkot Marketplace','subtitle'=>'New · Used · Digital','content'=>'Shop locally from verified sellers.','data'=>true],
        'live_widget'=>['label'=>'Live Broadcast','icon'=>'◉','title'=>'Live Shahkot','subtitle'=>'Watch live','content'=>'Live broadcasts and city streams.','data'=>true],
        'growth_widget'=>['label'=>'Growth / Commercial Widget','icon'=>'↗','title'=>'Local Growth','subtitle'=>'Business opportunities','content'=>'Commercial growth tools and local opportunities.','data'=>true],
        'spotlights'=>['label'=>'Visual Spotlights','icon'=>'✦','title'=>'Discover More','subtitle'=>'Popular around Shahkot','content'=>'Visual discovery cards.','data'=>false],
        'gallery'=>['label'=>'Gallery','icon'=>'▧','title'=>'City Gallery','subtitle'=>'See Shahkot','content'=>'Images and local highlights.','data'=>false],
        'content'=>['label'=>'Content Block','icon'=>'¶','title'=>'About ShahkotPK','subtitle'=>'Your digital city portal','content'=>'Useful city information from one platform.','data'=>false],
        'testimonials'=>['label'=>'Testimonials','icon'=>'❝','title'=>'What People Say','subtitle'=>'Community feedback','content'=>'Feedback from local users and businesses.','data'=>false],
        'cta'=>['label'=>'Call To Action','icon'=>'→','title'=>'Grow with ShahkotPK','subtitle'=>'Join the city platform','content'=>'List your business and reach more local customers.','data'=>false],
        'advertise_cta'=>['label'=>'Advertising CTA','icon'=>'AD','title'=>'Reach People in Shahkot','subtitle'=>'Advertise on ShahkotPK','content'=>'Promote your business with local advertising.','data'=>false],
        'links'=>['label'=>'Useful Links','icon'=>'↗','title'=>'Useful Links','subtitle'=>'Explore more','content'=>'Quick navigation to useful pages.','data'=>false],
        'custom_html'=>['label'=>'Custom HTML / Text','icon'=>'</>','title'=>'Custom Section','subtitle'=>'Custom content','content'=>'Add safe formatted custom content.','data'=>false],
        'footer'=>['label'=>'Footer','icon'=>'▾','title'=>'ShahkotPK','subtitle'=>'Complete City Guide','content'=>'© ShahkotPK. All rights reserved.','data'=>false],
    ];
}

function hb53_default_builder_config(string $type): array {
    $gridTypes=['category_explorer','directory','featured','new_businesses','nearby','restaurants','city_guide','deals','events','property','news_widgets','blog_widgets','shop_widget','spotlights','gallery','testimonials'];
    $animation=in_array($type,['topbar','header','slider','footer'],true)?'none':($type==='hero'?'fade-in':'fade-up');
    return [
        'data_source'=>'latest',
        'filter_value'=>'',
        'manual_ids'=>'',
        'card_count'=>in_array($type,['jobs','events'],true)?5:6,
        'layout_mode'=>in_array($type,$gridTypes,true)?'grid':'default',
        'desktop_cols'=>in_array($type,['category_explorer','directory'],true)?4:3,
        'tablet_cols'=>2,
        'mobile_cols'=>1,
        'animation'=>$animation,
        'spacing'=>'normal',
        'background'=>'transparent',
    ];
}

function hb53_section_template(string $type,int $order=10): array {
    $r=hb53_section_registry();
    $d=$r[$type]??['label'=>ucwords(str_replace('_',' ',$type)),'title'=>ucwords(str_replace('_',' ',$type)),'subtitle'=>'','content'=>'','data'=>false];
    return [
        'id'=>$type,'type'=>$type,'enabled'=>1,'order'=>$order,
        'title'=>(string)($d['title']??$d['label']??''),'subtitle'=>(string)($d['subtitle']??''),'content'=>(string)($d['content']??''),
        'image'=>'','items'=>'','custom_html'=>'','_builder'=>hb53_default_builder_config($type),
    ];
}

function hb53_current_cms_sections(): array {
    try{
        if(function_exists('cms_home_page')&&function_exists('cms_page_sections')){
            $p=cms_home_page();
            if($p){$rows=cms_page_sections($p);if(is_array($rows)&&$rows)return $rows;}
        }
    }catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('HB53 could not read CMS homepage',$e);}
    try{
        $raw=(string)setting('homepage_sections','');
        $d=json_decode($raw,true);
        return is_array($d)?$d:[];
    }catch(Throwable $e){return [];}
}

function hb53_recommended_types(string $theme): array {
    if($theme==='townhub-explorer')return ['topbar','header','hero','slider','category_explorer','city_map','sponsored_spotlight','ads','featured','new_businesses','property','deals','events','restaurants','jobs','news_widgets','blog_widgets','shop_widget','advertise_cta','footer'];
    if($theme==='urbango-spots')return ['topbar','header','hero','slider','quick_services','featured','category_explorer','city_map','spotlights','ads','deals','restaurants','events','city_guide','property','shop_widget','news_widgets','blog_widgets','advertise_cta','footer'];
    return ['topbar','header','hero','slider','category_explorer','city_map','featured','ads','deals','new_businesses','city_guide','nearby','events','jobs','property','news_widgets','shop_widget','blog_widgets','advertise_cta','footer'];
}

function hb53_normalize_section(array $s,int $order): array {
    $type=preg_replace('/[^a-z0-9_\-]/i','',(string)($s['type']??''));
    if($type==='')$type='content';
    $base=hb53_section_template($type,$order);
    foreach(['id','type','title','subtitle','content','image','items','custom_html'] as $k){if(array_key_exists($k,$s))$base[$k]=(string)$s[$k];}
    $base['enabled']=empty($s['enabled'])?0:1;
    $base['order']=$order;
    $incoming=is_array($s['_builder']??null)?$s['_builder']:[];
    $b=array_merge(hb53_default_builder_config($type),$incoming);
    $b['data_source']=in_array((string)$b['data_source'],['latest','featured','popular','random','manual','category','city'],true)?(string)$b['data_source']:'latest';
    $b['filter_value']=substr(trim((string)$b['filter_value']),0,120);
    $b['manual_ids']=preg_replace('/[^0-9,\s]/','',(string)$b['manual_ids']);
    $b['card_count']=max(1,min(24,(int)$b['card_count']));
    $b['layout_mode']=in_array((string)$b['layout_mode'],['default','grid','slider','list'],true)?(string)$b['layout_mode']:'default';
    $b['desktop_cols']=max(1,min(6,(int)$b['desktop_cols']));
    $b['tablet_cols']=max(1,min(4,(int)$b['tablet_cols']));
    $b['mobile_cols']=max(1,min(2,(int)$b['mobile_cols']));
    $b['animation']=in_array((string)$b['animation'],['none','fade-up','fade-in','zoom-in','slide-left','slide-right'],true)?(string)$b['animation']:'fade-up';
    $b['spacing']=in_array((string)$b['spacing'],['compact','normal','relaxed'],true)?(string)$b['spacing']:'normal';
    $bg=trim((string)$b['background']);
    $b['background']=($bg==='transparent'||preg_match('/^#[0-9a-fA-F]{6}$/',$bg))?$bg:'transparent';
    $base['_builder']=$b;
    return $base;
}

function hb53_recommended_layout(string $theme): array {
    $existing=hb53_current_cms_sections();$byType=[];$unknown=[];
    foreach($existing as $s){if(!is_array($s))continue;$t=(string)($s['type']??'');if($t!==''&&!isset($byType[$t]))$byType[$t]=$s;else $unknown[]=$s;}
    $out=[];$used=[];$order=10;
    foreach(hb53_recommended_types($theme) as $type){$row=$byType[$type]??hb53_section_template($type,$order);$row['type']=$type;$out[]=hb53_normalize_section($row,$order);$used[$type]=1;$order+=10;}
    // Preserve existing custom/unknown sections rather than silently deleting user work.
    foreach($existing as $s){if(!is_array($s))continue;$t=(string)($s['type']??'');if($t===''||isset($used[$t]))continue;$out[]=hb53_normalize_section($s,$order);$order+=10;}
    return $out;
}

function hb53_decode_layout(?string $raw): array {
    if(!$raw)return [];$d=json_decode($raw,true);if(!is_array($d))return [];$out=[];$order=10;
    foreach($d as $s){if(is_array($s)){$out[]=hb53_normalize_section($s,$order);$order+=10;}}
    return $out;
}
function hb53_encode_layout(array $layout): string {return json_encode(array_values($layout),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}

function hb53_profile(string $theme,string $scopeKey): ?array {
    if(!hb53_table_exists('homepage_builder_profiles'))return null;
    try{$q=db()->prepare('SELECT * FROM homepage_builder_profiles WHERE scope_key=? AND theme_slug=? LIMIT 1');$q->execute([$scopeKey,$theme]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}

function hb53_upsert_profile(string $theme,string $scopeKey,?int $tenantId,bool $inherit,array $draft,array $settings=[],?int $userId=null): int {
    if(!hb53_table_exists('homepage_builder_profiles'))throw new RuntimeException('Homepage Builder database migration has not been installed yet.');
    $row=hb53_profile($theme,$scopeKey);$layout=hb53_encode_layout($draft);$sj=json_encode($settings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if($row){
        db()->prepare('UPDATE homepage_builder_profiles SET tenant_id=?,inherit_global=?,draft_layout_json=?,draft_settings_json=?,draft_updated_at=NOW(),updated_by=?,updated_at=NOW() WHERE id=?')->execute([$tenantId,$inherit?1:0,$layout,$sj,$userId,$row['id']]);
        return (int)$row['id'];
    }
    db()->prepare('INSERT INTO homepage_builder_profiles(scope_key,tenant_id,theme_slug,inherit_global,draft_layout_json,draft_settings_json,draft_updated_at,updated_by,created_at,updated_at) VALUES(?,?,?,?,?,?,NOW(),?,NOW(),NOW())')->execute([$scopeKey,$tenantId,$theme,$inherit?1:0,$layout,$sj,$userId]);
    return (int)db()->lastInsertId();
}

function hb53_publish(string $theme,string $scopeKey,?int $tenantId,bool $inherit,array $layout,array $settings=[],?int $userId=null): int {
    $id=hb53_upsert_profile($theme,$scopeKey,$tenantId,$inherit,$layout,$settings,$userId);$row=hb53_profile($theme,$scopeKey);
    if(!$row)throw new RuntimeException('Unable to load Homepage Builder profile after saving.');
    if(!empty($row['published_layout_json'])&&hb53_table_exists('homepage_builder_revisions')){
        try{
            $q=db()->prepare('SELECT COALESCE(MAX(revision_no),0)+1 FROM homepage_builder_revisions WHERE profile_id=?');$q->execute([$id]);$rev=(int)$q->fetchColumn();
            db()->prepare('INSERT INTO homepage_builder_revisions(profile_id,revision_no,layout_json,settings_json,action_key,created_by,created_at) VALUES(?,?,?,?,?,?,NOW())')->execute([$id,$rev,$row['published_layout_json'],$row['published_settings_json'],'publish_backup',$userId]);
        }catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('HB53 revision backup failed',$e);}
    }
    db()->prepare('UPDATE homepage_builder_profiles SET inherit_global=?,published_layout_json=?,published_settings_json=?,published_at=NOW(),draft_layout_json=?,draft_settings_json=?,draft_updated_at=NOW(),updated_by=?,updated_at=NOW() WHERE id=?')
        ->execute([$inherit?1:0,hb53_encode_layout($layout),json_encode($settings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),hb53_encode_layout($layout),json_encode($settings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$userId,$id]);
    if(function_exists('tenant_audit'))tenant_audit('homepage_builder.publish','homepage_builder_profile',$id,'Published Homepage Builder 2.0 layout',['theme'=>$theme,'scope'=>$scopeKey]);
    return $id;
}

function hb53_effective_profile(string $theme,bool $draft=false): ?array {
    $scope=hb53_scope();
    if(!$scope['is_global']){
        $local=hb53_profile($theme,$scope['scope_key']);
        if($draft && $local && !empty($local['draft_layout_json']))return $local;
        if($local && empty($local['inherit_global'])){
            $field=$draft?'draft_layout_json':'published_layout_json';
            if(!empty($local[$field]))return $local;
        }
    }
    $global=hb53_profile($theme,'global');
    if($global){$field=$draft?'draft_layout_json':'published_layout_json';if(!empty($global[$field]))return $global;}
    return null;
}

function hb53_effective_layout(string $theme,bool $draft=false): array {
    $p=hb53_effective_profile($theme,$draft);if(!$p)return [];
    $raw=(string)($draft?($p['draft_layout_json']?:$p['published_layout_json']):$p['published_layout_json']);
    return hb53_decode_layout($raw);
}

function hb53_maybe_seed_profiles(): void {
    if(!hb53_table_exists('homepage_builder_profiles'))return;
    try{if((string)setting('homepage_builder53_seeded','0')==='1')return;}catch(Throwable $e){}
    $themes=function_exists('shahkot_reference_landing_catalog')?array_keys(shahkot_reference_landing_catalog()):['city-listing-motion','townhub-explorer','urbango-spots'];
    foreach($themes as $theme){
        if(hb53_profile($theme,'global'))continue;
        $layout=hb53_recommended_layout($theme);
        try{hb53_publish($theme,'global',null,false,$layout,['seeded'=>true],null);}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('HB53 default profile seed failed',$e);}
    }
    try{save_setting('homepage_builder53_seeded','1');}catch(Throwable $e){}
}

function hb53_apply_runtime_page(array $page,string $theme,bool $draft=false): array {
    $layout=hb53_effective_layout($theme,$draft);if(!$layout)return $page;
    $page['layout_json']=hb53_encode_layout($layout);
    return $page;
}

function hb53_rows_sort(array $rows,string $source,string $filter=''): array {
    if(!$rows)return [];
    $value=function(array $r,array $keys){foreach($keys as $k)if(isset($r[$k])&&$r[$k]!==null&&$r[$k]!=='')return $r[$k];return null;};
    if($source==='manual'){
        $ids=array_values(array_filter(array_map('intval',preg_split('/\s*,\s*/',$filter,-1,PREG_SPLIT_NO_EMPTY))));
        if(!$ids)return $rows;$pos=array_flip($ids);$rows=array_values(array_filter($rows,fn($r)=>isset($pos[(int)($r['id']??0)])));usort($rows,fn($a,$b)=>($pos[(int)$a['id']]??9999)<=>($pos[(int)$b['id']]??9999));return $rows;
    }
    if($source==='category'&&$filter!==''){$f=mb_strtolower($filter);$rows=array_values(array_filter($rows,function($r)use($f){foreach(['category_id','category','category_name','category_slug','slug'] as $k){if(isset($r[$k])&&str_contains(mb_strtolower((string)$r[$k]),$f))return true;}return false;}));}
    if($source==='city'&&$filter!==''){$f=mb_strtolower($filter);$rows=array_values(array_filter($rows,function($r)use($f){foreach(['city_id','city_name','city_slug'] as $k){if(isset($r[$k])&&str_contains(mb_strtolower((string)$r[$k]),$f))return true;}return false;}));}
    if($source==='random'){shuffle($rows);return $rows;}
    if($source==='featured')usort($rows,function($a,$b)use($value){$av=(int)($value($a,['featured','is_featured','is_breaking'])??0);$bv=(int)($value($b,['featured','is_featured','is_breaking'])??0);return $bv<=>$av ?: ((int)($b['id']??0)<=> (int)($a['id']??0));});
    elseif($source==='popular')usort($rows,function($a,$b)use($value){$av=(float)($value($a,['views','review_count','business_count','rating','clicks'])??0);$bv=(float)($value($b,['views','review_count','business_count','rating','clicks'])??0);return $bv<=>$av ?: ((int)($b['id']??0)<=> (int)($a['id']??0));});
    elseif($source==='latest')usort($rows,function($a,$b)use($value){$ad=(string)($value($a,['published_at','created_at','starts_at','updated_at'])??'');$bd=(string)($value($b,['published_at','created_at','starts_at','updated_at'])??'');$cmp=strcmp($bd,$ad);return $cmp!==0?$cmp:((int)($b['id']??0)<=> (int)($a['id']??0));});
    return $rows;
}

function hb53_context_key_for_type(string $type): ?string {
    return [
        'category_explorer'=>'categoryCounts','directory'=>'categories','featured'=>'featured','deals'=>'deals','new_businesses'=>'newBusinesses','nearby'=>'nearbyBusinesses','restaurants'=>'restaurants','city_guide'=>'guides','events'=>'events','jobs'=>'jobs','property'=>'properties'
    ][$type]??null;
}

function hb53_transform_context(array $ctx,array $page): array {
    $layout=hb53_decode_layout((string)($page['layout_json']??''));
    foreach($layout as $s){
        if(empty($s['enabled']))continue;$type=(string)$s['type'];$key=hb53_context_key_for_type($type);if(!$key||!isset($ctx[$key])||!is_array($ctx[$key]))continue;
        $b=$s['_builder']??hb53_default_builder_config($type);$source=(string)($b['data_source']??'latest');$filter=$source==='manual'?(string)($b['manual_ids']??''):(string)($b['filter_value']??'');
        $rows=hb53_rows_sort($ctx[$key],$source,$filter);$ctx[$key]=array_slice($rows,0,max(1,min(24,(int)($b['card_count']??6))));
    }
    return $ctx;
}

function hb53_public_css(array $page): string {
    $layout=hb53_decode_layout((string)($page['layout_json']??''));$css='';
    $classMap=[
      'category_explorer'=>'.cgp-category-grid','directory'=>'.lt-category-grid','featured'=>'.cgp-business-grid','deals'=>'.cgp-deal-grid','new_businesses'=>'.cgp-local-business-grid','nearby'=>'.cgp-local-business-grid','restaurants'=>'.cgp-local-business-grid','city_guide'=>'.cgp-guide-grid','events'=>'.cgp-event-grid','property'=>'.cgp-property-grid','news_widgets'=>'.news-card-grid','blog_widgets'=>'.blog-home-grid','spotlights'=>'.lt-spot-grid','gallery'=>'.lt-gallery-grid','quick_services'=>'.lt-service-grid','testimonials'=>'.hb53-testimonial-grid'
    ];
    foreach($layout as $i=>$s){$type=(string)$s['type'];$b=$s['_builder']??[];$token=preg_replace('/[^a-z0-9_-]/i','-',(string)($s['id']??($type.'-'.$i)));$selector='body .hb53-id-'.$token;
        $space=['compact'=>'32px','normal'=>'64px','relaxed'=>'96px'][(string)($b['spacing']??'normal')]??'64px';$bg=(string)($b['background']??'transparent');
        $css.=$selector.'{--hb53-space:'.$space.';--hb53-bg:'.$bg.';--hb53-cols:'.(int)($b['desktop_cols']??3).';--hb53-cols-tablet:'.(int)($b['tablet_cols']??2).';--hb53-cols-mobile:'.(int)($b['mobile_cols']??1).';}';
        if(isset($classMap[$type])){$grid=$classMap[$type];$css.=$selector.' '.$grid.'{grid-template-columns:repeat(var(--hb53-cols),minmax(0,1fr))!important;}@media(max-width:980px){'.$selector.' '.$grid.'{grid-template-columns:repeat(var(--hb53-cols-tablet),minmax(0,1fr))!important;}}@media(max-width:620px){'.$selector.' '.$grid.'{grid-template-columns:repeat(var(--hb53-cols-mobile),minmax(0,1fr))!important;}}';}
    }
    return $css;
}

function hb53_sanitize_custom_html(string $html): string {
    $html=preg_replace('~<(script|style)[^>]*>.*?</\1>~is','',$html)??$html;
    $html=strip_tags($html,'<p><div><span><strong><b><em><i><a><ul><ol><li><h2><h3><h4><br><hr><blockquote>');
    $html=preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i','',$html)??$html;
    $html=preg_replace('/javascript\s*:/i','',$html)??$html;
    return $html;
}

/** Wrap a section so Builder 2.0 layout/animation settings can style the existing renderer without rewriting module internals. */
function hb53_render_section_open(array $s): void {
    $b=$s['_builder']??hb53_default_builder_config((string)($s['type']??''));$id=preg_replace('/[^a-z0-9_-]/i','-',(string)($s['id']??$s['type']??'section'));
    $bg=(string)($b['background']??'transparent');$style=$bg!=='transparent'?'background:'.e($bg).';':'';
    echo '<div class="hb53-section-wrap hb53-id-'.e($id).' hb53-layout-'.e((string)($b['layout_mode']??'default')).' hb53-anim-'.e((string)($b['animation']??'none')).'" data-hb53-section="'.e((string)($s['type']??'')).'" style="'.$style.'">';
}
function hb53_render_section_close(): void {echo '</div>';}

/**
 * Optional ordered renderer used by Homepage Builder 2.0 for the three v5 reference themes.
 * It delegates every real module to the existing landing component functions so business logic remains centralized.
 */
function hb53_render_page_body(array $page,array $ctx,string $theme): void {
    $sections=hb53_decode_layout((string)($page['layout_json']??''));
    if(!$sections){if(function_exists('cms_render_page_body')){cms_render_page_body($page,$ctx);}return;}

    // v5.8.3 front-flow guard: structural chrome may not be dragged into the middle
    // of page content. This repairs rendering at runtime without rewriting user data.
    $rank=static function(array $row): int {
        $type=(string)($row['type']??'');
        if($type==='topbar')return -300000;
        if($type==='header')return -200000;
        if($type==='footer')return 300000;
        return (int)($row['order']??0);
    };
    usort($sections,static function($a,$b)use($rank){$r=$rank($a)<=>$rank($b);return $r!==0?$r:((int)($a['order']??0)<=>(int)($b['order']??0));});

    $hasLiveSection=false;
    foreach($sections as $probe){if(!empty($probe['enabled'])&&(string)($probe['type']??'')==='live_widget'){$hasLiveSection=true;break;}}
    $seenTopbar=false;$seenHeader=false;$seenFooter=false;$liveFallbackRendered=false;
    $GLOBALS['hb53_v583_flow_guard']=true;

    foreach($sections as $s){
        if(empty($s['enabled']))continue;
        $type=(string)($s['type']??'');
        if($type==='topbar'){if($seenTopbar)continue;$seenTopbar=true;}
        if($type==='header'){if($seenHeader)continue;$seenHeader=true;}
        if($type==='footer'){
            if($seenFooter)continue;
            $seenFooter=true;
            // The legacy v5.4.5 live fallback used to render after the footer.
            // Render it immediately BEFORE the terminal footer instead.
            if(!$hasLiveSection&&!$liveFallbackRendered){
                try{if(function_exists('live_render_widget')&&setting_bool('live_portal_enabled',true)&&setting_bool('live_home_enabled',true))live_render_widget([]);}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('v5.8.3 homepage live fallback failed',$e);}
                $liveFallbackRendered=true;
            }
        }
        $one=[$s];$wrapped=!in_array($type,['topbar','header','footer'],true);if($wrapped)hb53_render_section_open($s);
        try{
            switch($type){
                case 'topbar': if(function_exists('lt_topbar'))lt_topbar($one); break;
                case 'header': if(function_exists('lt_header'))lt_header($one,$theme); break;
                case 'hero': if(function_exists('lt_hero'))lt_hero($one,(array)($ctx['landingStats']??[]),$theme); break;
                case 'slider': if(function_exists('lt_slider'))lt_slider((array)($ctx['slides']??[]),$theme); break;
                case 'info_cards': if(function_exists('lt_info'))lt_info($one,$theme); break;
                case 'quick_services': if(function_exists('lt_services'))lt_services($one,$theme); break;
                case 'directory': if(function_exists('lt_directory'))lt_directory($one,(array)($ctx['categories']??[]),$theme); break;
                case 'featured': if(function_exists('lt_featured'))lt_featured($one,(array)($ctx['featured']??[]),$theme); break;
                case 'cta': if(function_exists('lt_cta'))lt_cta($one,$theme); break;
                case 'content': if(function_exists('lt_content'))lt_content($one,$theme); break;
                case 'spotlights': if(function_exists('lt_spotlights'))lt_spotlights($one,$theme); break;
                case 'gallery': if(function_exists('lt_gallery'))lt_gallery($one,$theme); break;
                case 'links': if(function_exists('lt_links'))lt_links($one,$theme); break;
                case 'footer': if(function_exists('lt_footer'))lt_footer($one); break;
                case 'category_explorer': if(function_exists('lt_category_explorer'))lt_category_explorer($one,$ctx); break;
                case 'deals': if(function_exists('lt_deals'))lt_deals($one,$ctx); break;
                case 'sponsored_spotlight': if(function_exists('lt_sponsored_spotlight'))lt_sponsored_spotlight($one); break;
                case 'ads':
                    if(function_exists('active_ad_for_placement')){
                        $ad=active_ad_for_placement('homepage_mid');if(!$ad)$ad=active_ad_for_placement('homepage_top');if(!$ad)$ad=active_ad_for_placement('homepage_bottom');
                        if($ad){$img=(string)($ad['image_url']??'');$title=(string)($ad['title']??'Sponsored Promotion');$desc=(string)($ad['description']??$ad['business_name']??'');$id=(int)($ad['id']??0);echo '<section class="lt-section hb53-ad-section"><div class="lt-shell"><a class="hb53-ad-card" href="/ad-click.php?id='.$id.'"'.($img!==''?' style="background-image:url(\''.e($img).'\')"':'').'><span>ADVERTISEMENT</span><div><h3>'.e($title).'</h3><p>'.e($desc).'</p></div></a></div></section>';}
                    } break;
                case 'new_businesses': if(function_exists('lt_new_businesses'))lt_new_businesses($one,$ctx); break;
                case 'nearby': if(function_exists('lt_nearby'))lt_nearby($one,$ctx); break;
                case 'restaurants': if(function_exists('lt_restaurants'))lt_restaurants($one,$ctx); break;
                case 'city_guide': if(function_exists('lt_city_guide_portal'))lt_city_guide_portal($one,$ctx); break;
                case 'events': if(function_exists('lt_events'))lt_events($one,$ctx); break;
                case 'jobs': if(function_exists('lt_jobs'))lt_jobs($one,$ctx); break;
                case 'property': if(function_exists('lt_property'))lt_property($one,$ctx); break;
                case 'advertise_cta': if(function_exists('lt_advertise_cta'))lt_advertise_cta($one); break;
                case 'city_map': if(function_exists('lt_city_map'))lt_city_map($one); break;
                case 'news_widgets': if(function_exists('lt_news_widgets'))lt_news_widgets($one); break;
                case 'blog_widgets': if(function_exists('lt_blog_widgets'))lt_blog_widgets($one); break;
                case 'live_widget': if(function_exists('lt_live_widget'))lt_live_widget($one); break;
                case 'shop_widget': if(function_exists('lt_shop_widget'))lt_shop_widget($one); break;
                case 'growth_widget': if(function_exists('lt_growth_widget'))lt_growth_widget($one); break;
                case 'testimonials':
                    $items=function_exists('lt_items')?lt_items($s):[];echo '<section class="lt-section hb53-testimonials"><div class="lt-shell">'.(function_exists('lt_heading_html')?lt_heading_html($s):'').'<div class="hb53-testimonial-grid">';foreach($items as $it)echo '<article><b>“</b><p>'.e($it['text']??'').'</p><strong>'.e($it['title']??'Local User').'</strong></article>';echo '</div></div></section>';break;
                case 'custom_html': echo '<section class="lt-section hb53-custom-html"><div class="lt-shell"><div class="hb53-custom-card">'.hb53_sanitize_custom_html((string)($s['custom_html']?:$s['content'])).'</div></div></section>';break;
                default: if(function_exists('lt_custom_modules'))lt_custom_modules($one); break;
            }
        }catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('HB53 section render failed: '.$type,$e);}
        if($wrapped)hb53_render_section_close();
    }
    // A layout can intentionally hide its footer; keep the Live fallback inside
    // the normal page flow in that case rather than appending it after the page.
    if(!$hasLiveSection&&!$liveFallbackRendered){
        try{if(function_exists('live_render_widget')&&setting_bool('live_portal_enabled',true)&&setting_bool('live_home_enabled',true))live_render_widget([]);}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('v5.8.3 homepage live fallback failed',$e);}
    }
}
