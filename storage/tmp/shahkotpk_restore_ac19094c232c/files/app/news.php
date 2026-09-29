<?php
declare(strict_types=1);

function news_categories(bool $activeOnly=true): array {
    try{return db()->query("SELECT * FROM news_categories".($activeOnly?" WHERE status=1":"")." ORDER BY sort_order,name_en")->fetchAll();}catch(Throwable $e){return [];}
}
function news_category(int $id): ?array {try{$q=db()->prepare("SELECT * FROM news_categories WHERE id=? LIMIT 1");$q->execute([$id]);return $q->fetch()?:null;}catch(Throwable $e){return null;}}
function news_slug(string $title,int $ignore=0): string {
    $base=strtolower(trim((string)preg_replace('/[^a-z0-9]+/i','-',$title),'-'));
    if($base==='')$base='news-'.date('Ymd-His');$slug=$base;$n=2;
    while(true){$q=db()->prepare("SELECT id FROM news_posts WHERE slug=?".($ignore?' AND id<>'.(int)$ignore:'')." LIMIT 1");$q->execute([$slug]);if(!$q->fetchColumn())return $slug;$slug=$base.'-'.$n++;}
}
function news_sync_scheduled(): void {
    if(!setting_bool('news_allow_scheduled',true))return;
    try{db()->exec("UPDATE news_posts SET status='published' WHERE status='scheduled' AND published_at IS NOT NULL AND published_at<=NOW()");}catch(Throwable $e){}
}
function news_posts(array $filters=[],int $limit=12,int $offset=0): array {
    news_sync_scheduled();$where=["n.status='published'","(n.published_at IS NULL OR n.published_at<=NOW())"];$params=[];
    if(!empty($filters['language'])){$lang=(string)$filters['language'];if(in_array($lang,['en','ur'],true)){$where[]="(n.language=? OR n.language='bilingual')";$params[]=$lang;}}
    if(!empty($filters['post_type'])){$where[]='n.post_type=?';$params[]=(string)$filters['post_type'];}
    if(!empty($filters['breaking']))$where[]='n.is_breaking=1';
    if(!empty($filters['featured']))$where[]='n.is_featured=1';
    if(!empty($filters['category_id'])){$where[]='n.category_id=?';$params[]=(int)$filters['category_id'];}
    if(!empty($filters['category_slug'])){$where[]='c.slug=?';$params[]=(string)$filters['category_slug'];}
    if(!empty($filters['city_id'])){$where[]='n.city_id=?';$params[]=(int)$filters['city_id'];}
    elseif(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'n.city_id');
    if(!empty($filters['q'])){$q='%'.trim((string)$filters['q']).'%';$where[]='(n.title_en LIKE ? OR n.title_ur LIKE ? OR n.excerpt_en LIKE ? OR n.excerpt_ur LIKE ?)';array_push($params,$q,$q,$q,$q);}
    $sql="SELECT n.*,c.name_en category_name_en,c.name_ur category_name_ur,c.slug category_slug,ci.name city_name,b.name business_name,b.slug business_slug FROM news_posts n LEFT JOIN news_categories c ON c.id=n.category_id LEFT JOIN cities ci ON ci.id=n.city_id LEFT JOIN businesses b ON b.id=n.business_id WHERE ".implode(' AND ',$where)." ORDER BY n.is_breaking DESC,n.is_featured DESC,COALESCE(n.published_at,n.created_at) DESC,n.id DESC LIMIT ".max(1,min(100,$limit))." OFFSET ".max(0,$offset);
    try{$st=db()->prepare($sql);$st->execute($params);return $st->fetchAll();}catch(Throwable $e){return [];}
}
function news_post(string $slug,bool $publishedOnly=true): ?array {
    news_sync_scheduled();$where=['n.slug=?'];$params=[$slug];if($publishedOnly){$where[]="n.status='published'";$where[]='(n.published_at IS NULL OR n.published_at<=NOW())';}if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'n.city_id');
    $sql="SELECT n.*,c.name_en category_name_en,c.name_ur category_name_ur,c.slug category_slug,ci.name city_name,b.name business_name,b.slug business_slug FROM news_posts n LEFT JOIN news_categories c ON c.id=n.category_id LEFT JOIN cities ci ON ci.id=n.city_id LEFT JOIN businesses b ON b.id=n.business_id WHERE ".implode(' AND ',$where)." LIMIT 1";
    try{$q=db()->prepare($sql);$q->execute($params);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function news_increment_view(int $id): void {try{db()->prepare("UPDATE news_posts SET views=views+1 WHERE id=?")->execute([$id]);}catch(Throwable $e){}}
function news_display_title(array $p,string $lang='en'): string {
    if($lang==='ur')return trim((string)($p['title_ur']??''))?:trim((string)($p['title_en']??''));
    return trim((string)($p['title_en']??''))?:trim((string)($p['title_ur']??''));
}
function news_display_excerpt(array $p,string $lang='en'): string {
    if($lang==='ur')return trim((string)($p['excerpt_ur']??''))?:trim((string)($p['excerpt_en']??''));
    return trim((string)($p['excerpt_en']??''))?:trim((string)($p['excerpt_ur']??''));
}
function news_display_body(array $p,string $lang='en'): string {
    if($lang==='ur')return trim((string)($p['body_ur']??''))?:trim((string)($p['body_en']??''));
    return trim((string)($p['body_en']??''))?:trim((string)($p['body_ur']??''));
}
function news_post_languages(array $p): array {return $p['language']==='bilingual'?['en','ur']:[(string)$p['language']];}
function news_youtube_id(string $url): ?string {
    if(preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i',$url,$m))return $m[1];return null;
}
function news_video_embed_url(string $url): ?string {
    $url=trim($url);if($url==='')return null;
    if($id=news_youtube_id($url))return 'https://www.youtube-nocookie.com/embed/'.$id;
    if(preg_match('~vimeo\.com/(?:video/)?([0-9]+)~i',$url,$m))return 'https://player.vimeo.com/video/'.$m[1];
    return null;
}
function news_image(array $p): string {
    if(!empty($p['image_url']))return (string)$p['image_url'];
    if(!empty($p['video_url'])&&($id=news_youtube_id((string)$p['video_url'])))return 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg';
    return '';
}
function news_stats(): array {
    try{$where=[];$params=[];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($where,$params,'city_id');$sql="SELECT COUNT(*) total,SUM(status='published') published_count,SUM(status='draft') draft_count,SUM(status='scheduled') scheduled_count,SUM(is_breaking=1 AND status='published') breaking_count,SUM(is_featured=1 AND status='published') featured_count,SUM(post_type='video' AND status='published') video_count,SUM((language='ur' OR language='bilingual') AND status='published') urdu_count,SUM((language='en' OR language='bilingual') AND status='published') english_count,COALESCE(SUM(views),0) total_views FROM news_posts".($where?' WHERE '.implode(' AND ',$where):'');$q=db()->prepare($sql);$q->execute($params);$r=$q->fetch();return $r?:[];}catch(Throwable $e){return [];}
}
function news_widgets(bool $enabledOnly=true): array {
    try{$sql="SELECT w.*,c.name_en category_name_en,c.name_ur category_name_ur FROM news_widgets w LEFT JOIN news_categories c ON c.id=w.category_id".($enabledOnly?' WHERE w.enabled=1':'')." ORDER BY w.sort_order,w.id";return db()->query($sql)->fetchAll();}catch(Throwable $e){return [];}
}
function news_widget_posts(array $widget): array {
    $filters=[];$type=(string)$widget['widget_type'];$lang=(string)$widget['language'];
    if(in_array($lang,['en','ur'],true))$filters['language']=$lang;
    if($type==='breaking')$filters['breaking']=1;
    elseif($type==='featured')$filters['featured']=1;
    elseif($type==='video')$filters['post_type']='video';
    elseif($type==='category'&&!empty($widget['category_id']))$filters['category_id']=(int)$widget['category_id'];
    return news_posts($filters,max(1,min(20,(int)$widget['item_limit'])));
}
function news_widget_title(array $w,string $lang='en'): string {return $lang==='ur'?(trim((string)$w['title_ur'])?:trim((string)$w['title_en'])):trim((string)$w['title_en']);}
function news_upgrade_homepage(): void {
    try{
        if(setting_int('news_homepage_version',0)>=320)return;
        $page=cms_home_page();if(!$page)return;$sections=cms_page_sections($page);$found=false;
        foreach($sections as $s)if(($s['type']??'')==='news_widgets'){$found=true;break;}
        if(!$found)$sections[]=['id'=>'news_portal_widgets','type'=>'news_widgets','enabled'=>1,'order'=>88,'title'=>'ShahkotPK Newsroom','subtitle'=>'Latest local updates','content'=>'Breaking, English, Urdu and video news from the city newsroom.','image'=>'','items'=>''];
        usort($sections,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));$order=10;foreach($sections as &$s){$s['order']=$order;$order+=10;}unset($s);
        $json=cms_encode_layout($sections);db()->prepare("UPDATE cms_pages SET layout_json=?,updated_at=NOW() WHERE id=?")->execute([$json,$page['id']]);save_setting('homepage_sections',$json);save_setting('news_homepage_version','320');
    }catch(Throwable $e){}
}
