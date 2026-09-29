<?php
declare(strict_types=1);

/**
 * ShahkotPK v5.2 landing reference pack.
 * These are original ShahkotPK adaptations inspired by public layout references.
 * No third-party template source code, media or bundled assets are included.
 */
function shahkot_reference_landing_catalog(): array {
    return [
        'city-listing-motion' => [
            'slug' => 'city-listing-motion',
            'name' => 'Shahkot Pulse 3D Pro+',
            'reference' => 'ShahkotPK premium digital-city portal',
            'description' => 'Premium digital-city portal with interactive 3D hero motion, category-colored depth cards, polished marketplace sections and fully integrated Growth/Trusted Local Discovery cards.',
            'accent' => '#ff6b2c',
            'dark' => '#0b1f3a',
            'css' => '/assets/themes/landing/city-listing-motion.css?v=1392',
            'new' => true,
        ],
        'townhub-explorer' => [
            'slug' => 'townhub-explorer',
            'name' => 'TownHub Explorer',
            'reference' => 'TownHub city directory layout',
            'description' => 'Deep city hero, powerful search treatment, elevated listing cards, city/stat blocks and modern directory spacing.',
            'accent' => '#ff5b64',
            'dark' => '#24355f',
            'css' => '/assets/themes/landing/townhub-explorer.css?v=520',
            'new' => true,
        ],
        'urbango-spots' => [
            'slug' => 'urbango-spots',
            'name' => 'Urban Spots',
            'reference' => 'UrbanGo visual directory direction',
            'description' => 'Editorial dark hero, punchy search CTA, floating white category treatment and image-first discovery cards.',
            'accent' => '#ff315f',
            'dark' => '#111111',
            'css' => '/assets/themes/landing/urbango-spots.css?v=520',
            'new' => true,
        ],
    ];
}

function shahkot_is_reference_landing_theme(string $slug): bool {
    return isset(shahkot_reference_landing_catalog()[$slug]);
}

function shahkot_reference_theme_css_url(string $slug): ?string {
    $catalog=shahkot_reference_landing_catalog();
    return isset($catalog[$slug]) ? (string)$catalog[$slug]['css'] : null;
}

function shahkot_home_theme_slug(): string {
    try {
        if(function_exists('cms_home_page')){
            $page=cms_home_page();
            $slug=trim((string)($page['theme_slug']??''));
            if($slug!=='')return $slug;
        }
    } catch(Throwable $e) {
        if(function_exists('runtime_log'))runtime_log('Unable to resolve homepage theme for reference pack',$e);
    }
    return function_exists('active_landing_theme') ? (string)active_landing_theme() : (string)setting('landing_theme_slug','metro-portal');
}

/**
 * User-visible landing catalog for v5.1.
 * Requirement: retire all previous landing choices except the currently active one,
 * while adding the three new reference themes.
 */
function shahkot_visible_landing_catalog(?string $activeSlug=null): array {
    $activeSlug=trim((string)($activeSlug??shahkot_home_theme_slug()));
    $visible=[];
    $existing=[];
    try {
        if(function_exists('theme_catalog_index'))$existing=(array)theme_catalog_index('landing');
    } catch(Throwable $e) {
        $existing=[];
    }

    if($activeSlug!=='' && !shahkot_is_reference_landing_theme($activeSlug)){
        $def=(array)($existing[$activeSlug]??[]);
        $visible[$activeSlug]=array_merge([
            'slug'=>$activeSlug,
            'name'=>(string)($def['name']??ucwords(str_replace(['-','_'],' ',$activeSlug))),
            'description'=>'Currently active legacy landing theme. Preserved automatically during the v5.1 theme cleanup.',
            'reference'=>'Preserved active ShahkotPK theme',
            'accent'=>'#0f766e',
            'dark'=>'#0f172a',
            'css'=>function_exists('cms_theme_css_url')?(string)cms_theme_css_url($activeSlug):('/assets/themes/landing/'.rawurlencode($activeSlug).'.css'),
            'legacy_active'=>true,
        ],$def);
    }

    foreach(shahkot_reference_landing_catalog() as $slug=>$def)$visible[$slug]=$def;
    return $visible;
}

function shahkot_apply_landing_theme(string $slug): void {
    $allowed=shahkot_visible_landing_catalog(shahkot_home_theme_slug());
    if(!isset($allowed[$slug]))throw new InvalidArgumentException('This landing theme is not available in the v5.2 Theme Studio.');

    save_setting('landing_theme_slug',$slug);
    try {
        if(function_exists('cms_home_page')){
            $page=cms_home_page();
            if($page && !empty($page['id'])){
                db()->prepare('UPDATE cms_pages SET theme_slug=?,render_mode=\'theme\',updated_at=NOW() WHERE id=?')->execute([$slug,(int)$page['id']]);
            }
        }
    } catch(Throwable $e) {
        if(function_exists('runtime_log'))runtime_log('Landing theme setting saved but CMS homepage sync failed',$e);
    }
}


/** v5.2 per-theme visual controls. */
function shahkot_theme52_defaults(string $slug): array {
    $catalog=shahkot_reference_landing_catalog();$accent=(string)($catalog[$slug]['accent']??'#0f766e');
    $defaults=[
        'accent'=>$accent,'card_radius'=>16,'content_width'=>1240,'hero_overlay'=>62,
        'hero_media'=>'','header_style'=>'solid','search_style'=>'boxed','card_shadow'=>'medium'
    ];
    if($slug==='city-listing-motion')$defaults=array_merge($defaults,['card_radius'=>14,'content_width'=>1260,'hero_overlay'=>70,'header_style'=>'solid','search_style'=>'boxed']);
    if($slug==='townhub-explorer')$defaults=array_merge($defaults,['card_radius'=>18,'content_width'=>1280,'hero_overlay'=>72,'header_style'=>'floating','search_style'=>'pill']);
    if($slug==='urbango-spots')$defaults=array_merge($defaults,['card_radius'=>22,'content_width'=>1320,'hero_overlay'=>64,'header_style'=>'floating','search_style'=>'glass']);
    return $defaults;
}
function shahkot_theme52_settings(string $slug): array {
    $defaults=shahkot_theme52_defaults($slug);if(!shahkot_is_reference_landing_theme($slug))return $defaults;
    try{$raw=(string)setting('landing_theme52_'.$slug,'');$saved=json_decode($raw,true);if(is_array($saved))$defaults=array_merge($defaults,$saved);}catch(Throwable $e){}
    $defaults['accent']=preg_match('/^#[0-9a-fA-F]{6}$/',(string)$defaults['accent'])?(string)$defaults['accent']:(string)shahkot_theme52_defaults($slug)['accent'];
    $defaults['card_radius']=max(0,min(36,(int)$defaults['card_radius']));
    $defaults['content_width']=max(980,min(1600,(int)$defaults['content_width']));
    $defaults['hero_overlay']=max(20,min(90,(int)$defaults['hero_overlay']));
    $defaults['header_style']=in_array((string)$defaults['header_style'],['solid','floating'],true)?(string)$defaults['header_style']:'solid';
    $defaults['search_style']=in_array((string)$defaults['search_style'],['boxed','pill','glass'],true)?(string)$defaults['search_style']:'boxed';
    $defaults['card_shadow']=in_array((string)$defaults['card_shadow'],['soft','medium','strong'],true)?(string)$defaults['card_shadow']:'medium';
    $media=trim((string)$defaults['hero_media']);if($media!=='' && !str_starts_with($media,'/') && !preg_match('~^https://~i',$media))$media='';$defaults['hero_media']=$media;
    return $defaults;
}
function shahkot_theme52_save_settings(string $slug,array $input): void {
    if(!shahkot_is_reference_landing_theme($slug))throw new InvalidArgumentException('Design controls are available for the three v5.2 reference themes only.');
    $accent=trim((string)($input['accent']??''));if(!preg_match('/^#[0-9a-fA-F]{6}$/',$accent))throw new InvalidArgumentException('Accent color must be a 6-digit hex color.');
    $media=trim((string)($input['hero_media']??''));if($media!==''&&!str_starts_with($media,'/')&&!preg_match('~^https://~i',$media))throw new InvalidArgumentException('Hero media must be a local /path or an HTTPS URL.');
    $data=[
      'accent'=>$accent,'card_radius'=>max(0,min(36,(int)($input['card_radius']??16))),
      'content_width'=>max(980,min(1600,(int)($input['content_width']??1240))),
      'hero_overlay'=>max(20,min(90,(int)($input['hero_overlay']??65))),'hero_media'=>$media,
      'header_style'=>in_array((string)($input['header_style']??''),['solid','floating'],true)?(string)$input['header_style']:'solid',
      'search_style'=>in_array((string)($input['search_style']??''),['boxed','pill','glass'],true)?(string)$input['search_style']:'boxed',
      'card_shadow'=>in_array((string)($input['card_shadow']??''),['soft','medium','strong'],true)?(string)$input['card_shadow']:'medium',
    ];
    save_setting('landing_theme52_'.$slug,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
}
function shahkot_theme52_inline_css(string $slug): string {
    if(!shahkot_is_reference_landing_theme($slug))return '';$s=shahkot_theme52_settings($slug);
    $accent=(string)$s['accent'];$radius=(int)$s['card_radius'];$width=(int)$s['content_width'];$overlay=(int)$s['hero_overlay']/100;
    $shadow=['soft'=>'0 10px 28px rgba(15,23,42,.07)','medium'=>'0 18px 42px rgba(15,23,42,.11)','strong'=>'0 24px 58px rgba(15,23,42,.17)'][(string)$s['card_shadow']];
    $css="body.landing-theme-{$slug}{--sk52-accent:{$accent};--sk52-radius:{$radius}px;--sk52-shell:{$width}px;--sk52-card-shadow:{$shadow};--sk52-overlay:{$overlay}}";
    if((string)$s['hero_media']!==''){$u=str_replace(["\\","'",'\n','\r'],['\\\\',"\\'",'',''],(string)$s['hero_media']);$rgb=$slug==='urbango-spots'?'10,10,12':($slug==='townhub-explorer'?'17,26,51':'10,34,48');$css.="body.landing-theme-{$slug} .lt-hero{background-image:linear-gradient(rgba({$rgb},{$overlay}),rgba({$rgb},{$overlay})),url('{$u}')!important;background-size:cover!important;background-position:center!important}";}
    return $css;
}
