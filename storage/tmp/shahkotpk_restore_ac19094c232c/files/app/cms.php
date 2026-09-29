<?php
declare(strict_types=1);

function cms_tables_ready(): bool {
    try{
        db()->query("SELECT id FROM cms_pages LIMIT 1");
        return true;
    }catch(Throwable $e){return false;}
}

function cms_clean_slug(string $slug,string $fallback='page'): string {
    $slug=strtolower(trim($slug));
    $slug=preg_replace('/[^a-z0-9]+/','-',$slug);
    $slug=trim((string)$slug,'-');
    return $slug!==''?mb_substr($slug,0,180):$fallback;
}

function cms_decode_layout(?string $json): array {
    if(!$json || !function_exists('json_decode')) return [];
    $data=json_decode($json,true);
    return is_array($data)?array_values($data):[];
}

function cms_encode_layout(array $sections): string {
    if(!function_exists('json_encode')){
        if(function_exists('runtime_log')) runtime_log('JSON extension unavailable while encoding CMS layout. Falling back to empty layout.');
        return '[]';
    }
    $encoded=json_encode(array_values($sections),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    return is_string($encoded)?$encoded:'[]';
}

function cms_legacy_home_sections(): array {
    try{
        $q=db()->prepare("SELECT setting_value FROM settings WHERE setting_key='homepage_sections' LIMIT 1");
        $q->execute();
        $raw=$q->fetchColumn();
        if($raw){
            $data=json_decode((string)$raw,true);
            if(is_array($data)) return array_values($data);
        }
    }catch(Throwable $e){}
    return function_exists('default_homepage_sections')?default_homepage_sections():[];
}

function cms_ensure_home_page(): ?array {
    if(!cms_tables_ready()) return null;

    try{
        $q=db()->query("SELECT * FROM cms_pages WHERE is_home=1 ORDER BY id LIMIT 1");
        $page=$q->fetch();
        if($page) return $page;

        $sections=cms_legacy_home_sections();
        $theme=(string)setting('landing_theme_slug','metro-portal');
        $uid=(int)(current_user()['id']??0);
        $q=db()->prepare("INSERT INTO cms_pages(slug,title,page_type,status,is_home,render_mode,theme_slug,layout_json,seo_title,seo_description,seo_keywords,created_by,updated_by,published_at) VALUES('home',?,'home','published',1,'theme',?,?,?,?,?,?,?,NOW())");
        $q->execute([
            setting('site_name','ShahkotPK').' Home',
            $theme,
            cms_encode_layout($sections),
            setting('seo_title',setting('site_name','ShahkotPK')),
            setting('seo_description','Discover Shahkot businesses, services and city information.'),
            setting('seo_keywords','Shahkot, business directory'),
            $uid?:null,
            $uid?:null
        ]);
        return cms_page((int)db()->lastInsertId());
    }catch(Throwable $e){return null;}
}

function cms_page(int $id): ?array {
    try{$q=db()->prepare("SELECT * FROM cms_pages WHERE id=? LIMIT 1");$q->execute([$id]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}

function cms_page_by_slug(string $slug,bool $publishedOnly=true): ?array {
    try{
        $sql="SELECT * FROM cms_pages WHERE slug=?";
        if($publishedOnly)$sql.=" AND status='published'";
        $sql.=" LIMIT 1";
        $q=db()->prepare($sql);$q->execute([$slug]);return $q->fetch()?:null;
    }catch(Throwable $e){return null;}
}

function cms_home_page(): ?array {
    $page=cms_ensure_home_page();
    if(!$page) return null;
    return $page;
}

function cms_pages(): array {
    cms_ensure_home_page();
    try{return db()->query("SELECT * FROM cms_pages ORDER BY is_home DESC,updated_at DESC,id DESC")->fetchAll();}catch(Throwable $e){return [];}
}

function cms_page_sections(array $page): array {
    return cms_decode_layout((string)($page['layout_json']??''));
}

function cms_next_revision(int $pageId): int {
    try{$q=db()->prepare("SELECT COALESCE(MAX(version_no),0)+1 FROM cms_revisions WHERE page_id=?");$q->execute([$pageId]);return (int)$q->fetchColumn();}catch(Throwable $e){return 1;}
}

function cms_create_revision(array $page,?int $userId=null): void {
    if(empty($page['id'])) return;
    $snapshot=[
        'title'=>$page['title']??'',
        'slug'=>$page['slug']??'',
        'page_type'=>$page['page_type']??'page',
        'status'=>$page['status']??'draft',
        'render_mode'=>$page['render_mode']??'theme',
        'theme_slug'=>$page['theme_slug']??'metro-portal',
        'layout_json'=>$page['layout_json']??'[]',
        'custom_css'=>$page['custom_css']??'',
        'seo_title'=>$page['seo_title']??'',
        'seo_description'=>$page['seo_description']??'',
        'seo_keywords'=>$page['seo_keywords']??'',
    ];
    try{
        $q=db()->prepare("INSERT INTO cms_revisions(page_id,version_no,snapshot_json,created_by) VALUES(?,?,?,?)");
        $q->execute([$page['id'],cms_next_revision((int)$page['id']),json_encode($snapshot,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$userId?:null]);
        $limit=max(5,min(100,setting_int('cms_revisions_limit',30)));
        db()->prepare("DELETE FROM cms_revisions WHERE page_id=? AND id NOT IN (SELECT id FROM (SELECT id FROM cms_revisions WHERE page_id=? ORDER BY id DESC LIMIT ".$limit.") x)")->execute([$page['id'],$page['id']]);
    }catch(Throwable $e){}
}

function cms_sync_legacy_home(array $page): void {
    if(empty($page['is_home'])) return;
    try{
        save_setting('homepage_sections',(string)$page['layout_json']);
        save_setting('landing_theme_slug',(string)$page['theme_slug']);
        site_settings(true);
    }catch(Throwable $e){}
}

function cms_save_page(array $page,array $sections,?int $userId=null): array {
    $existing=!empty($page['id'])?cms_page((int)$page['id']):null;
    if($existing) cms_create_revision($existing,$userId);

    $title=trim((string)($page['title']??'Untitled Page'));
    if($title==='')$title='Untitled Page';
    $slug=cms_clean_slug((string)($page['slug']??$title));
    $pageType=in_array(($page['page_type']??'page'),['home','landing','page'],true)?$page['page_type']:'page';
    $status=($page['status']??'draft')==='published'?'published':'draft';
    $renderMode=($page['render_mode']??'theme')==='manual'?'manual':'theme';
    $theme=trim((string)($page['theme_slug']??'metro-portal'))?:'metro-portal';
    $layout=cms_encode_layout($sections);
    $css=cms_sanitize_page_css((string)($page['custom_css']??''));
    $seoTitle=mb_substr(trim((string)($page['seo_title']??'')),0,255);
    $seoDescription=mb_substr(trim((string)($page['seo_description']??'')),0,700);
    $seoKeywords=mb_substr(trim((string)($page['seo_keywords']??'')),0,700);
    $isHome=!empty($page['is_home'])?1:0;

    if($isHome){
        try{db()->exec("UPDATE cms_pages SET is_home=0,page_type=IF(page_type='home','page',page_type) WHERE is_home=1".($existing?" AND id<>".(int)$existing['id']:""));}catch(Throwable $e){}
        $pageType='home';
        $status='published';
        $slug='home';
    }

    if($existing){
        $q=db()->prepare("UPDATE cms_pages SET slug=?,title=?,page_type=?,status=?,is_home=?,render_mode=?,theme_slug=?,layout_json=?,custom_css=?,seo_title=?,seo_description=?,seo_keywords=?,updated_by=?,published_at=IF(?='published',COALESCE(published_at,NOW()),published_at) WHERE id=?");
        $q->execute([$slug,$title,$pageType,$status,$isHome,$renderMode,$theme,$layout,$css,$seoTitle?:null,$seoDescription?:null,$seoKeywords?:null,$userId?:null,$status,$existing['id']]);
        $saved=cms_page((int)$existing['id']);
    }else{
        $q=db()->prepare("INSERT INTO cms_pages(slug,title,page_type,status,is_home,render_mode,theme_slug,layout_json,custom_css,seo_title,seo_description,seo_keywords,created_by,updated_by,published_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,IF(?='published',NOW(),NULL))");
        $q->execute([$slug,$title,$pageType,$status,$isHome,$renderMode,$theme,$layout,$css,$seoTitle?:null,$seoDescription?:null,$seoKeywords?:null,$userId?:null,$userId?:null,$status]);
        $saved=cms_page((int)db()->lastInsertId());
    }

    if($saved) cms_sync_legacy_home($saved);
    return $saved?:[];
}

function cms_delete_page(int $id): void {
    $page=cms_page($id);
    if(!$page) return;
    if(!empty($page['is_home'])) throw new RuntimeException('The active homepage cannot be deleted.');
    db()->prepare("DELETE FROM cms_revisions WHERE page_id=?")->execute([$id]);
    db()->prepare("DELETE FROM cms_pages WHERE id=?")->execute([$id]);
}

function cms_duplicate_page(int $id,?int $userId=null): array {
    $page=cms_page($id);
    if(!$page) throw new RuntimeException('Page not found.');
    $copy=$page;
    unset($copy['id']);
    $copy['title']=$page['title'].' Copy';
    $copy['slug']=cms_clean_slug($page['slug'].'-copy-'.substr(bin2hex(random_bytes(3)),0,6));
    $copy['status']='draft';
    $copy['is_home']=0;
    $copy['page_type']=$page['page_type']==='home'?'landing':$page['page_type'];
    return cms_save_page($copy,cms_page_sections($page),$userId);
}

function cms_revisions(int $pageId): array {
    try{$limit=max(5,min(100,setting_int('cms_revisions_limit',30)));$q=db()->prepare("SELECT * FROM cms_revisions WHERE page_id=? ORDER BY id DESC LIMIT ".$limit);$q->execute([$pageId]);return $q->fetchAll();}catch(Throwable $e){return [];}
}

function cms_restore_revision(int $revisionId,?int $userId=null): array {
    $q=db()->prepare("SELECT * FROM cms_revisions WHERE id=? LIMIT 1");$q->execute([$revisionId]);$rev=$q->fetch();
    if(!$rev) throw new RuntimeException('Revision not found.');
    $page=cms_page((int)$rev['page_id']);
    if(!$page) throw new RuntimeException('Page not found.');
    $snap=json_decode((string)$rev['snapshot_json'],true);
    if(!is_array($snap)) throw new RuntimeException('Revision data is invalid.');
    $page=array_merge($page,$snap);
    $page['is_home']=(int)($page['is_home']??0);
    return cms_save_page($page,cms_decode_layout((string)($snap['layout_json']??'[]')),$userId);
}

function cms_section_defaults(string $type): array {
    $id='cms_'.preg_replace('/[^a-z0-9_]/','_',strtolower($type)).'_'.bin2hex(random_bytes(4));
    $common=['id'=>$id,'type'=>$type,'enabled'=>1,'order'=>10,'title'=>'New '.ucwords(str_replace('_',' ',$type)),'subtitle'=>'','content'=>'','image'=>'','items'=>'','style'=>['variant'=>'default','background'=>'','text_color'=>'','spacing'=>'normal','columns'=>'3','css_class'=>'']];
    $presets=[
        'hero'=>['title'=>'Discover Shahkot','subtitle'=>'Your city, easier to explore','content'=>'Search businesses, services, places and useful city information.'],
        'content'=>['title'=>'About This Section','subtitle'=>'Tell your story','content'=>'Add your content here.'],
        'cta'=>['title'=>'Grow with ShahkotPK','subtitle'=>'Take the next step','content'=>'Add a strong call to action here.','items'=>"Get Started||/signup.php|"],
        'info_cards'=>['title'=>'City Information','items'=>"Time in Shahkot|Pakistan Standard Time||\nEmergency|Important numbers||\nWeather|Update from Admin||"],
        'quick_services'=>['title'=>'Popular Services','items'=>"Hospitals|Healthcare services|/search.php?q=hospital|\nSchools|Education|/search.php?q=school|\nRestaurants|Food & dining|/search.php?category=restaurants|"],
        'gallery'=>['title'=>'Gallery','items'=>"Gallery Image|Description|||"],
        'links'=>['title'=>'Useful Links','items'=>"About||#|\nContact||#|"],
        'spotlights'=>['title'=>'City Spotlights','items'=>"Local Market|Discover local shopping|/search.php||"],
        'testimonials'=>['title'=>'What People Say','items'=>"Local Customer|Great local discovery experience|||"],
        'faq'=>['title'=>'Frequently Asked Questions','items'=>"How does ShahkotPK work?|Browse businesses and city information from one place|||"],
        'stats'=>['title'=>'ShahkotPK in Numbers','items'=>"Businesses|100+|||\nCategories|20+|||\nVisitors|Growing||"],
        'category_explorer'=>['title'=>'Explore Shahkot','subtitle'=>'Popular categories','content'=>'Dynamic local category explorer.'],
        'city_map'=>['title'=>'Explore Shahkot on Map','subtitle'=>'Interactive city discovery','content'=>'Google Maps discovery for shops, events, property and city places.'],
        'news_widgets'=>['title'=>'ShahkotPK Newsroom','subtitle'=>'Latest local updates','content'=>'Breaking, English, Urdu and video news widgets managed from News Portal.'],
        'blog_widgets'=>['title'=>'From the ShahkotPK Blog','subtitle'=>'Stories, guides & local perspectives','content'=>'Useful articles from local writers, editors and city experts.'],
        'live_widget'=>['title'=>'Live Now','subtitle'=>'Live broadcasts','content'=>'Authorized live channels, local events and programs.'],
        'shop_widget'=>['title'=>'Shahkot Marketplace','subtitle'=>'New · Used · Digital · Auctions','content'=>'Local marketplace products and auctions.'],
        'growth_widget'=>['title'=>'Trusted Local Discovery','subtitle'=>'Reviews, services & rewards','content'=>'Top-rated businesses, emergency information, services and community tools.'],
        'deals'=>['title'=>'Deals & Offers','subtitle'=>'Save locally','content'=>'Dynamic local deals from City Content Manager.'],
        'sponsored_spotlight'=>['title'=>'Sponsored Spotlight','subtitle'=>'Local advertising','content'=>'Dynamic premium advertisement placement.'],
        'new_businesses'=>['title'=>'New in Shahkot','subtitle'=>'Recently added','content'=>'Recently added local businesses.'],
        'city_guide'=>['title'=>'Complete City Guide','subtitle'=>'Useful information','content'=>'Dynamic city guide content.'],
        'nearby'=>['title'=>'Popular Near You','subtitle'=>'Location discovery','content'=>'Businesses can be sorted using browser location.'],
        'restaurants'=>['title'=>'Food & Restaurants','subtitle'=>'Eat local','content'=>'Restaurant and food business showcase.'],
        'events'=>['title'=>'Events & Announcements','subtitle'=>'What is happening','content'=>'Dynamic local events.'],
        'jobs'=>['title'=>'Jobs in Shahkot','subtitle'=>'Local opportunities','content'=>'Dynamic local jobs.'],
        'property'=>['title'=>'Property & Rentals','subtitle'=>'Buy, sell or rent','content'=>'Dynamic property listings.'],
        'advertise_cta'=>['title'=>'Reach People in Shahkot','subtitle'=>'Local advertising','content'=>'Promote your business with ShahkotPK.','items'=>"Advertise Now||/pricing.php|"],
        'newsletter'=>['title'=>'Stay Connected','subtitle'=>'Local updates','content'=>'Get ShahkotPK updates and useful city information.'],
        'spacer'=>['title'=>'Spacer','content'=>''],
        'custom'=>['title'=>'Custom Section','content'=>'Build this section manually.'],
    ];
    return array_merge($common,$presets[$type]??[]);
}

function cms_normalize_sections(array $posted,array $files=[]): array {
    $out=[];$order=10;
    foreach($posted as $id=>$p){
        if(!is_array($p))continue;
        $cleanId=preg_replace('/[^a-zA-Z0-9_-]/','',(string)$id);
        if($cleanId==='')$cleanId='cms_'.bin2hex(random_bytes(4));
        $type=preg_replace('/[^a-z0-9_]/','',strtolower((string)($p['type']??'custom')));
        $style=is_array($p['style']??null)?$p['style']:[];
        $section=[
            'id'=>$cleanId,
            'type'=>$type?:'custom',
            'enabled'=>isset($p['enabled'])?1:0,
            'order'=>$order,
            'title'=>trim((string)($p['title']??'')),
            'subtitle'=>trim((string)($p['subtitle']??'')),
            'content'=>trim((string)($p['content']??'')),
            'image'=>trim((string)($p['image']??'')),
            'items'=>trim((string)($p['items']??'')),
            'style'=>[
                'variant'=>preg_replace('/[^a-zA-Z0-9_-]/','',(string)($style['variant']??'default'))?:'default',
                'background'=>cms_clean_css_color((string)($style['background']??'')),
                'text_color'=>cms_clean_css_color((string)($style['text_color']??'')),
                'spacing'=>in_array(($style['spacing']??'normal'),['compact','normal','large'],true)?$style['spacing']:'normal',
                'columns'=>(string)max(1,min(6,(int)($style['columns']??3))),
                'css_class'=>preg_replace('/[^a-zA-Z0-9 _-]/','',(string)($style['css_class']??'')),
            ]
        ];
        $fileKey='section_image_'.$cleanId;
        if(isset($files[$fileKey]) && is_array($files[$fileKey])){
            $uploaded=upload_homepage_image($files[$fileKey]);
            if($uploaded)$section['image']=$uploaded;
        }
        $out[]=$section;
        $order+=10;
    }
    return $out;
}

function cms_clean_css_color(string $value): string {
    $value=trim($value);
    if($value==='')return '';
    if(preg_match('/^#[0-9a-fA-F]{3,8}$/',$value))return $value;
    if(preg_match('/^(rgb|rgba|hsl|hsla)\([0-9.,%\s-]+\)$/',$value))return $value;
    return '';
}

function cms_sanitize_page_css(string $css): string {
    $css=trim($css);
    if($css==='')return '';
    if(strlen($css)>50000) throw new RuntimeException('Custom CSS is too large.');
    $bad=['</style','<script','javascript:','expression(','@import','behavior:','-moz-binding','url(http://','url(https://','url(//'];
    $lower=strtolower($css);
    foreach($bad as $needle)if(str_contains($lower,$needle))throw new RuntimeException('Custom CSS contains a blocked rule: '.$needle);
    return $css;
}

function cms_upload_media(array $file,?int $userId=null): array {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) throw new RuntimeException('Choose an image to upload.');
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK) throw new RuntimeException('Media upload failed.');
    $max=max(1,setting_int('max_image_upload_mb',5))*1024*1024;
    if(($file['size']??0)>$max) throw new RuntimeException('Image exceeds the configured upload size.');

    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $finfo=new finfo(FILEINFO_MIME_TYPE);$mime=(string)$finfo->file($file['tmp_name']);
    if(!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
    if(!@getimagesize($file['tmp_name'])) throw new RuntimeException('Uploaded file is not a valid image.');

    $dir=__DIR__.'/../uploads/cms-media';
    if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Cannot create CMS media directory.');
    if(!is_file($dir.'/.htaccess'))@file_put_contents($dir.'/.htaccess',"Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|cgi|pl|py|sh)$\">\nRequire all denied\n</FilesMatch>\n",LOCK_EX);

    $name=date('YmdHis').'-'.bin2hex(random_bytes(6)).'.'.$allowed[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name))throw new RuntimeException('Could not store CMS media.');
    @chmod($dir.'/'.$name,0644);
    $url='/uploads/cms-media/'.$name;

    $q=db()->prepare("INSERT INTO cms_media(file_url,file_name,mime_type,file_size,alt_text,created_by) VALUES(?,?,?,?,?,?)");
    $q->execute([$url,basename((string)($file['name']??$name)),$mime,(int)($file['size']??0),trim((string)($_POST['alt_text']??'')),$userId?:null]);
    return ['id'=>(int)db()->lastInsertId(),'file_url'=>$url];
}

function cms_media(): array {
    try{return db()->query("SELECT * FROM cms_media ORDER BY id DESC LIMIT 200")->fetchAll();}catch(Throwable $e){return [];}
}

function cms_delete_media(int $id): void {
    $q=db()->prepare("SELECT * FROM cms_media WHERE id=? LIMIT 1");$q->execute([$id]);$m=$q->fetch();
    if(!$m)return;
    $url=(string)$m['file_url'];
    if(str_starts_with($url,'/uploads/cms-media/')){
        $path=__DIR__.'/..'.$url;
        if(is_file($path))@unlink($path);
    }
    db()->prepare("DELETE FROM cms_media WHERE id=?")->execute([$id]);
}

function cms_builtin_theme_slugs(): array {
    return ['city-guide-pro','metro-portal','city-magazine','glass-city','commerce-grid','civic-hub','neon-local','heritage-shahkot','minimal-search','skyline-stories','social-city'];
}

function cms_custom_themes(): array {
    try{return db()->query("SELECT * FROM cms_themes WHERE enabled=1 ORDER BY name")->fetchAll();}catch(Throwable $e){return [];}
}

function cms_custom_theme(string $slug): ?array {
    try{$q=db()->prepare("SELECT * FROM cms_themes WHERE slug=? AND enabled=1 LIMIT 1");$q->execute([$slug]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}

function cms_theme_css_url(string $slug): string {
    $slug=cms_clean_slug($slug,'metro-portal');
    if(in_array($slug,cms_builtin_theme_slugs(),true)){
        $file=__DIR__.'/../assets/themes/landing/'.$slug.'.css';
        if(is_file($file))return '/assets/themes/landing/'.$slug.'.css?v='.(string)@filemtime($file);
    }
    $theme=cms_custom_theme($slug);
    if($theme)return (string)$theme['css_url'];
    $fallback=__DIR__.'/../assets/themes/landing/metro-portal.css';
    return '/assets/themes/landing/metro-portal.css?v='.(is_file($fallback)?(string)@filemtime($fallback):'240');
}

function cms_allowed_theme_blocks(): array {
    return ['topbar','header','slider','hero','info','services','directory','featured','category_explorer','city_map','news_widgets','blog_widgets','live_widget','shop_widget','growth_widget','deals','sponsored_spotlight','new_businesses','city_guide','nearby','restaurants','events','jobs','property','advertise_cta','cta','content','spotlights','gallery','custom_modules','links','footer'];
}

function cms_import_theme_zip(array $file,?int $userId=null): array {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)throw new RuntimeException('Choose a theme ZIP package.');
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK)throw new RuntimeException('Theme package upload failed.');
    if(($file['size']??0)>8*1024*1024)throw new RuntimeException('Theme ZIP must be 8 MB or smaller.');
    if(strtolower(pathinfo((string)($file['name']??''),PATHINFO_EXTENSION))!=='zip')throw new RuntimeException('Upload a .zip theme package.');

    $zip=new ZipArchive();
    if($zip->open($file['tmp_name'])!==true)throw new RuntimeException('Unable to open theme ZIP.');

    $allowedFiles=['theme.json','theme.css','preview.png','preview.jpg','preview.jpeg','preview.webp'];
    $contents=[];
    for($i=0;$i<$zip->numFiles;$i++){
        $name=str_replace('\\','/',$zip->getNameIndex($i));
        if(str_contains($name,'../')||str_starts_with($name,'/')){$zip->close();throw new RuntimeException('Theme ZIP contains an unsafe path.');}
        $base=basename($name);
        if($base===''||str_ends_with($name,'/'))continue;
        if(!in_array($base,$allowedFiles,true)){$zip->close();throw new RuntimeException('Unsupported file in theme ZIP: '.$base);}
        $contents[$base]=$zip->getFromIndex($i);
    }
    $zip->close();

    if(empty($contents['theme.json'])||empty($contents['theme.css']))throw new RuntimeException('Theme ZIP requires theme.json and theme.css.');
    $manifest=json_decode((string)$contents['theme.json'],true);
    if(!is_array($manifest))throw new RuntimeException('theme.json is invalid.');

    $rawSlug=cms_clean_slug((string)($manifest['slug']??$manifest['name']??'theme'),'theme');
    $slug=str_starts_with($rawSlug,'custom-')?$rawSlug:'custom-'.$rawSlug;
    if(strlen($slug)>100)throw new RuntimeException('Theme slug is too long.');
    $name=mb_substr(trim((string)($manifest['name']??'Custom Theme')),0,180);
    $description=mb_substr(trim((string)($manifest['description']??'Imported CMS landing theme')),0,700);
    $layout=$manifest['layout']??[];
    if(!is_array($layout)||!$layout)$layout=['topbar','header','slider','hero','info','services','directory','featured','cta','content','spotlights','gallery','custom_modules','links','footer'];
    $allowed=cms_allowed_theme_blocks();
    $cleanLayout=[];
    foreach($layout as $block){
        $block=preg_replace('/[^a-z_]/','',strtolower((string)$block));
        if(!in_array($block,$allowed,true))throw new RuntimeException('Unsupported theme layout block: '.$block);
        $cleanLayout[]=$block;
    }

    $css=cms_sanitize_page_css((string)$contents['theme.css']);
    $dir=__DIR__.'/../uploads/cms-themes/'.$slug;
    if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Unable to create theme folder.');
    if(!is_file(dirname($dir).'/.htaccess'))@file_put_contents(dirname($dir).'/.htaccess',"Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|cgi|pl|py|sh)$\">\nRequire all denied\n</FilesMatch>\n",LOCK_EX);
    file_put_contents($dir.'/theme.css',$css,LOCK_EX);
    @chmod($dir.'/theme.css',0644);
    $cssUrl='/uploads/cms-themes/'.$slug.'/theme.css?v='.time();

    $previewUrl=null;
    foreach(['preview.png','preview.jpg','preview.jpeg','preview.webp'] as $preview){
        if(empty($contents[$preview]))continue;
        $ext=strtolower(pathinfo($preview,PATHINFO_EXTENSION));
        $path=$dir.'/preview.'.$ext;
        file_put_contents($path,$contents[$preview],LOCK_EX);
        $previewUrl='/uploads/cms-themes/'.$slug.'/preview.'.$ext;
        break;
    }

    $q=db()->prepare("INSERT INTO cms_themes(slug,name,description,css_url,preview_url,layout_json,enabled,created_by) VALUES(?,?,?,?,?,?,1,?) ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),css_url=VALUES(css_url),preview_url=VALUES(preview_url),layout_json=VALUES(layout_json),enabled=1");
    $q->execute([$slug,$name,$description,$cssUrl,$previewUrl,json_encode($cleanLayout),$userId?:null]);
    return cms_custom_theme($slug)?:[];
}

function cms_delete_theme(string $slug): void {
    $theme=cms_custom_theme($slug);
    if(!$theme) return;
    $q=db()->prepare("SELECT COUNT(*) FROM cms_pages WHERE theme_slug=?");$q->execute([$slug]);
    if((int)$q->fetchColumn()>0)throw new RuntimeException('This theme is assigned to a CMS page. Change that page theme first.');
    $dir=__DIR__.'/../uploads/cms-themes/'.$slug;
    if(is_dir($dir)){
        foreach(glob($dir.'/*')?:[] as $f)if(is_file($f))@unlink($f);
        @rmdir($dir);
    }
    db()->prepare("DELETE FROM cms_themes WHERE slug=?")->execute([$slug]);
}

function cms_page_public_url(array $page): string {
    return !empty($page['is_home'])?'/':'/page.php?slug='.urlencode((string)$page['slug']);
}

function cms_page_preview_url(array $page): string {
    $url=cms_page_public_url($page);
    return $url.(str_contains($url,'?')?'&':'?').'cms_preview='.(int)$page['id'];
}
