<?php
declare(strict_types=1);

function cms_public_context(array $page): array {
    $sections=cms_page_sections($page);
    usort($sections,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));

    $categories=[];$featured=[];$totalBusinesses=0;$totalCategories=0;$totalCities=0;
    try{
        $categories=db()->query("SELECT id,name,slug FROM categories WHERE status=1 ORDER BY name ASC LIMIT 24")->fetchAll();
        $featured=db()->query("SELECT b.*,c.name city_name,cat.name category_name FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id WHERE b.status=1 AND (b.verification_status='verified' OR b.is_featured=1) ORDER BY b.is_featured DESC,b.id DESC LIMIT 12")->fetchAll();
        $totalBusinesses=(int)db()->query("SELECT COUNT(*) FROM businesses WHERE status=1")->fetchColumn();
        $totalCategories=(int)db()->query("SELECT COUNT(*) FROM categories WHERE status=1")->fetchColumn();
        $totalCities=(int)db()->query("SELECT COUNT(*) FROM cities WHERE status=1")->fetchColumn();
    }catch(Throwable $e){}

    $slides=array_values(array_filter(banner_slides(),fn($slide)=>!empty($slide['enabled'])));
    usort($slides,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));

    $portal=function_exists('city_portal_context')?city_portal_context():[];
    return array_merge([
        'sections'=>$sections,
        'categories'=>$categories,
        'featured'=>$featured,
        'slides'=>$slides,
        'landingStats'=>[
            'Active Businesses'=>number_format($totalBusinesses),
            'Categories'=>number_format($totalCategories),
            'City Coverage'=>number_format($totalCities),
            'Featured'=>number_format(count($featured)),
        ],
    ],$portal);
}

function cms_style_attr(array $section): string {
    $style=is_array($section['style']??null)?$section['style']:[];
    $parts=[];
    if(!empty($style['background']))$parts[]='--cms-bg:'.cms_clean_css_color((string)$style['background']);
    if(!empty($style['text_color']))$parts[]='--cms-text:'.cms_clean_css_color((string)$style['text_color']);
    if(!empty($style['columns']))$parts[]='--cms-cols:'.max(1,min(6,(int)$style['columns']));
    return $parts?' style="'.e(implode(';',$parts)).'"':'';
}

function cms_section_classes(array $section): string {
    $style=is_array($section['style']??null)?$section['style']:[];
    $classes=['cms-block','cms-block-'.preg_replace('/[^a-z0-9_-]/','',(string)($section['type']??'custom'))];
    $classes[]='cms-space-'.($style['spacing']??'normal');
    if(!empty($style['variant']))$classes[]='cms-variant-'.preg_replace('/[^a-zA-Z0-9_-]/','',(string)$style['variant']);
    if(!empty($style['css_class']))$classes[]=preg_replace('/[^a-zA-Z0-9 _-]/','',(string)$style['css_class']);
    return implode(' ',$classes);
}

function cms_manual_items(array $section): array {
    return section_items((string)($section['items']??''));
}

function cms_render_manual_section(array $section,array $ctx): void {
    if(empty($section['enabled']))return;
    $type=(string)($section['type']??'custom');
    $title=(string)($section['title']??'');
    $subtitle=(string)($section['subtitle']??'');
    $content=(string)($section['content']??'');
    $image=(string)($section['image']??'');
    $items=cms_manual_items($section);
    $cls=cms_section_classes($section);
    $style=cms_style_attr($section);

    if($type==='topbar'){
        echo '<div class="cms-manual-topbar '.$cls.'"'.$style.'><div class="lt-shell"><span>'.e($content?:$subtitle).'</span><div>';
        foreach($items as $it)echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).'</a>';
        echo '</div></div></div>';
        if(function_exists('render_ticker'))render_ticker('public');
        return;
    }

    if($type==='header'){
        $user=current_user();
        echo '<header class="cms-manual-header '.$cls.'"'.$style.'><div class="lt-shell cms-manual-header-inner"><a class="lt-brand" href="/"><i>S</i><span><b>'.e(setting('site_name',$title?:'ShahkotPK')).'</b><small>'.e($subtitle).'</small></span></a><button class="lt-menu-btn" type="button" data-lt-menu>☰</button><nav class="lt-nav" data-lt-nav>';
        if(function_exists('lt_public_navigation'))echo lt_public_navigation($user);
        else {foreach($items as $it){if(strtolower(trim($it['title']))==='login')continue;echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).'</a>';}echo $user?'<a class="lt-login" href="'.(can_access_admin_panel($user)?staff_landing_url($user):($user['role']==='shopkeeper'?'/shopkeeper.php':'/account.php')).'">Dashboard</a>':'<a class="lt-login" href="/login.php">Login</a>';}
        echo '</nav></div></header>';return;
    }

    if($type==='slider'){lt_slider($ctx['slides'],'cms-manual');return;}


    if($type==='footer'){
        echo '<footer class="lt-footer '.$cls.'"'.$style.'><div class="lt-shell"><div><b>'.e(setting('site_name',$title?:'ShahkotPK')).'</b><span>'.e($subtitle).'</span></div><nav>';
        foreach($items as $it)echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).'</a>';
        echo '</nav><small>'.e($content).'</small></div></footer>';return;
    }


    if($type==='hero'){
        echo '<section class="'.$cls.' cms-manual-hero"'.$style.'><div class="lt-shell cms-manual-hero-grid"><div>';
        echo '<span class="lt-kicker">'.e($subtitle).'</span><h1>'.e($title).'</h1><p>'.e($content).'</p>';
        echo '<form class="lt-search" action="/search.php"><input name="q" placeholder="Search Shahkot..."><button>Search</button></form>';
        if($items){echo '<div class="lt-popular">';foreach($items as $it)echo '<a href="'.e(safe_link($it['link'])).'">'.e($it['title']).'</a>';echo '</div>';}
        echo '</div><aside class="cms-manual-hero-art" '.($image?'style="background-image:url(\''.e($image).'\')"':'').'>';
        foreach($ctx['landingStats'] as $k=>$v)echo '<div><b>'.e($v).'</b><span>'.e($k).'</span></div>';
        echo '</aside></div></section>';return;
    }

    if($type==='directory'){
        echo '<section class="'.$cls.'"'.$style.'><div class="lt-shell">'.cms_heading($subtitle,$title,$content).'<div class="cms-card-grid">';
        foreach($ctx['categories'] as $c)echo '<a class="cms-directory-card" href="/search.php?category='.urlencode($c['slug']).'"><i>'.e(mb_strtoupper(mb_substr($c['name'],0,1))).'</i><b>'.e($c['name']).'</b><span>Explore listings →</span></a>';
        echo '</div></div></section>';return;
    }

    if($type==='featured'){
        echo '<section class="'.$cls.'"'.$style.'><div class="lt-shell">'.cms_heading($subtitle,$title,$content).'<div class="cms-business-grid">';
        if(!$ctx['featured'])echo '<div class="lt-empty">Featured businesses will appear here.</div>';
        foreach($ctx['featured'] as $b)echo '<article><div class="cms-business-cover" '.($b['image']?'style="background-image:url(\''.e($b['image']).'\')"':'').'></div><div><span>'.e($b['verification_status']==='verified'?'✓ Verified':'Featured').'</span><h3>'.e($b['name']).'</h3><p>'.e($b['category_name']).' · '.e($b['city_name']).'</p><a href="/business.php?slug='.urlencode($b['slug']).'">View Business →</a></div></article>';
        echo '</div></div></section>';return;
    }

    if($type==='category_explorer'){lt_category_explorer([$section],$ctx);return;}
    if($type==='city_map'){lt_city_map([$section]);return;}
    if($type==='news_widgets'){lt_news_widgets([$section]);return;}
    if($type==='blog_widgets'){lt_blog_widgets([$section]);return;}
    if($type==='live_widget'){lt_live_widget([$section]);return;}
    if($type==='shop_widget'){lt_shop_widget([$section]);return;}
    if($type==='growth_widget'){lt_growth_widget([$section]);return;}
    if($type==='deals'){lt_deals([$section],$ctx);return;}
    if($type==='sponsored_spotlight'){lt_sponsored_spotlight([$section]);return;}
    if($type==='new_businesses'){lt_new_businesses([$section],$ctx);return;}
    if($type==='city_guide'){lt_city_guide_portal([$section],$ctx);return;}
    if($type==='nearby'){lt_nearby([$section],$ctx);return;}
    if($type==='restaurants'){lt_restaurants([$section],$ctx);return;}
    if($type==='events'){lt_events([$section],$ctx);return;}
    if($type==='jobs'){lt_jobs([$section],$ctx);return;}
    if($type==='property'){lt_property([$section],$ctx);return;}
    if($type==='advertise_cta'){lt_advertise_cta([$section]);return;}

    if($type==='spacer'){
        $spacing=(string)(($section['style']['spacing']??'normal'));
        $height=$spacing==='large'?120:($spacing==='compact'?35:70);
        echo '<div class="cms-spacer" style="height:'.$height.'px"></div>';return;
    }

    if($type==='newsletter'){
        echo '<section class="'.$cls.' cms-newsletter"'.$style.'><div class="lt-shell"><div>'.cms_heading($subtitle,$title,$content).'</div><form onsubmit="return false"><input type="email" placeholder="Your email address"><button>Subscribe</button></form></div></section>';return;
    }

    echo '<section class="'.$cls.'"'.$style.'><div class="lt-shell">';
    echo cms_heading($subtitle,$title,$content);

    if($image!=='')echo '<div class="cms-section-image" style="background-image:url(\''.e($image).'\')"></div>';

    if($items){
        if($type==='faq'){
            echo '<div class="cms-faq">';
            foreach($items as $it)echo '<details><summary>'.e($it['title']).'</summary><p>'.e($it['text']).'</p></details>';
            echo '</div>';
        }elseif($type==='testimonials'){
            echo '<div class="cms-testimonials">';
            foreach($items as $it)echo '<article><p>“'.e($it['text']).'”</p><b>'.e($it['title']).'</b></article>';
            echo '</div>';
        }elseif($type==='stats'){
            echo '<div class="cms-stats">';
            foreach($items as $it)echo '<article><b>'.e($it['text']?:$it['title']).'</b><span>'.e($it['text']?$it['title']:'').'</span></article>';
            echo '</div>';
        }elseif($type==='gallery'){
            echo '<div class="cms-gallery">';
            foreach($items as $it)echo '<article '.($it['image']?'style="background-image:url(\''.e($it['image']).'\')"':'').'><div><b>'.e($it['title']).'</b><span>'.e($it['text']).'</span></div></article>';
            echo '</div>';
        }else{
            echo '<div class="cms-card-grid">';
            foreach($items as $it)echo '<article class="cms-generic-card">'.($it['image']?'<div class="cms-item-image" style="background-image:url(\''.e($it['image']).'\')"></div>':'').'<b>'.e($it['title']).'</b><span>'.e($it['text']).'</span>'.($it['link']?'<a href="'.e(safe_link($it['link'])).'">Open →</a>':'').'</article>';
            echo '</div>';
        }
    }
    echo '</div></section>';
}

function cms_heading(string $subtitle,string $title,string $content): string {
    return '<div class="lt-heading"><span>'.e($subtitle).'</span><h2>'.e($title).'</h2><p>'.e($content).'</p></div>';
}

function cms_render_manual_page(array $sections,array $ctx): void {
    usort($sections,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));
    foreach($sections as $section)cms_render_manual_section($section,$ctx);
}

function cms_render_imported_theme(array $theme,array $ctx): void {
    $layout=json_decode((string)($theme['layout_json']??'[]'),true);
    if(!is_array($layout)||!$layout)$layout=['topbar','header','slider','hero','info','services','directory','featured','cta','content','spotlights','gallery','custom_modules','links','footer'];

    $sections=$ctx['sections'];$categories=$ctx['categories'];$featured=$ctx['featured'];$slides=$ctx['slides'];$landingStats=$ctx['landingStats'];
    foreach($layout as $block){
        switch($block){
            case 'topbar':lt_topbar($sections);break;
            case 'header':lt_header($sections,'custom');break;
            case 'slider':lt_slider($slides,'custom');break;
            case 'hero':lt_hero($sections,$landingStats,'custom');break;
            case 'info':lt_info($sections,'custom');break;
            case 'services':lt_services($sections,'custom');break;
            case 'directory':lt_directory($sections,$categories,'custom');break;
            case 'featured':lt_featured($sections,$featured,'custom');break;
            case 'cta':lt_cta($sections,'custom');break;
            case 'content':lt_content($sections,'custom');break;
            case 'spotlights':lt_spotlights($sections,'custom');break;
            case 'gallery':lt_gallery($sections,'custom');break;
            case 'custom_modules':lt_custom_modules($sections);break;
            case 'links':lt_links($sections,'custom');break;
            case 'footer':lt_footer($sections);break;
        }
    }
}

function cms_render_page_body(array $page,array $ctx): void {
    if(($page['render_mode']??'theme')==='manual'){
        cms_render_manual_page($ctx['sections'],$ctx);
        return;
    }

    $theme=(string)($page['theme_slug']??'metro-portal');
    $custom=cms_custom_theme($theme);
    if($custom){
        cms_render_imported_theme($custom,$ctx);
        return;
    }

    $sections=$ctx['sections'];$categories=$ctx['categories'];$featured=$ctx['featured'];$slides=$ctx['slides'];$landingStats=$ctx['landingStats'];
    $template=__DIR__.'/../themes/landing/'.$theme.'.php';
    if(!is_file($template))$template=__DIR__.'/../themes/landing/metro-portal.php';
    require $template;
}
