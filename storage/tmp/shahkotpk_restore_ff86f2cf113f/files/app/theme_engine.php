<?php
declare(strict_types=1);

function admin_theme_catalog(): array {
    return [
        ['slug'=>'aurora-command','name'=>'Aurora Command','description'=>'3D command center with animated left rail, layered KPI cards and floating widgets.','layout'=>'Left Command Rail','visual'=>'3D Analytics'],
        ['slug'=>'executive-glass','name'=>'Executive Glass','description'=>'Glassmorphism executive suite with horizontal navigation and transparent analytics panels.','layout'=>'Top Glass Navigation','visual'=>'Executive Boards'],
        ['slug'=>'midnight-ops','name'=>'Midnight Ops','description'=>'Dark operations center with status consoles, radar-like graphs and dense live intelligence.','layout'=>'Dark Ops Rail','visual'=>'Command Console'],
        ['slug'=>'finance-console','name'=>'Finance Console','description'=>'Revenue-first financial dashboard with right-side navigation, ledgers and compact financial KPIs.','layout'=>'Right Finance Rail','visual'=>'Revenue First'],
        ['slug'=>'metro-blocks','name'=>'Metro Blocks','description'=>'Bold modular dashboard using tile-based metro blocks, large numeric stats and snap animations.','layout'=>'Metro Tile Rail','visual'=>'Block Analytics'],
        ['slug'=>'minimal-pro','name'=>'Minimal Pro','description'=>'Clean professional workspace with light navigation, generous spacing and simplified charts.','layout'=>'Clean Light Sidebar','visual'=>'Minimal Analytics'],
        ['slug'=>'neon-matrix','name'=>'Neon Matrix','description'=>'Futuristic neon control room with glowing grid navigation, animated graph lines and high-contrast widgets.','layout'=>'Neon Grid Rail','visual'=>'Futuristic Metrics'],
        ['slug'=>'clay-studio','name'=>'Clay Studio','description'=>'Soft clay/neumorphic admin workspace with rounded floating navigation and tactile metric cards.','layout'=>'Floating Clay Rail','visual'=>'Soft 3D Cards'],
        ['slug'=>'compact-control','name'=>'Compact Control','description'=>'Dense productivity dashboard with compact rail, tight tables and maximum information per screen.','layout'=>'Compact Icon Rail','visual'=>'Dense Operations'],
        ['slug'=>'horizon-board','name'=>'Horizon Board','description'=>'Wide horizontal dashboard with bottom command dock, panoramic charts and full-width sections.','layout'=>'Bottom Command Dock','visual'=>'Panoramic Analytics'],
    ];
}

function landing_theme_catalog(): array {
    $catalog=[
        ['slug'=>'city-guide-pro','name'=>'City Guide Pro','description'=>'Complete local city guide with smart search, sponsored inventory, deals, events, jobs, property, nearby discovery and rich business showcases.','layout'=>'Complete City Guide','visual'=>'Local Portal 3.0'],
        ['slug'=>'metro-portal','name'=>'Metro Portal','description'=>'Modern city portal with large search hero, quick-service tiles and balanced directory sections.','layout'=>'Search-first Portal','visual'=>'Complete Template'],
        ['slug'=>'city-magazine','name'=>'City Magazine','description'=>'Editorial city magazine with split headline layouts, featured stories and visual business showcases.','layout'=>'Editorial Magazine','visual'=>'Complete Template'],
        ['slug'=>'glass-city','name'=>'Glass City','description'=>'Premium glassmorphism homepage with floating information cards, translucent slider and layered discovery panels.','layout'=>'Glass Showcase','visual'=>'Complete Template'],
        ['slug'=>'commerce-grid','name'=>'Commerce Grid','description'=>'Business-first marketplace style with subscription CTA, featured shops, categories and conversion-focused sections.','layout'=>'Marketplace Grid','visual'=>'Complete Template'],
        ['slug'=>'civic-hub','name'=>'Civic Hub','description'=>'Structured city-information portal with service rail, useful links, city data and directory discovery.','layout'=>'Civic Information Hub','visual'=>'Complete Template'],
        ['slug'=>'neon-local','name'=>'Neon Local','description'=>'Dark animated local discovery website with glowing slider, search, categories and featured business panels.','layout'=>'Dark Neon Discovery','visual'=>'Complete Template'],
        ['slug'=>'heritage-shahkot','name'=>'Heritage Shahkot','description'=>'Warm editorial heritage template with community storytelling, city gallery and local business discovery.','layout'=>'Heritage Editorial','visual'=>'Complete Template'],
        ['slug'=>'minimal-search','name'=>'Minimal Search','description'=>'Ultra-clean search-focused landing page with whitespace, prominent directory discovery and minimal distractions.','layout'=>'Minimal Search','visual'=>'Complete Template'],
        ['slug'=>'skyline-stories','name'=>'Skyline Stories','description'=>'Immersive full-bleed slider, city spotlight storytelling, photo gallery and cinematic business sections.','layout'=>'Cinematic Storytelling','visual'=>'Complete Template'],
        ['slug'=>'social-city','name'=>'Social City','description'=>'Community-oriented portal with social-style cards, city activity side panel, services and business discovery feed.','layout'=>'Community Feed','visual'=>'Complete Template'],
    ];
    if(function_exists('cms_custom_themes')){
        try{
            foreach(cms_custom_themes() as $t){
                $catalog[]=[
                    'slug'=>(string)$t['slug'],
                    'name'=>(string)$t['name'],
                    'description'=>(string)($t['description']??'Imported CMS landing theme'),
                    'layout'=>'CMS Imported Theme',
                    'visual'=>'Custom Template',
                    'custom'=>1,
                    'preview_url'=>(string)($t['preview_url']??'')
                ];
            }
        }catch(Throwable $e){}
    }
    return $catalog;
}

function theme_catalog_index(string $scope): array {
    $catalog=$scope==='admin'?admin_theme_catalog():landing_theme_catalog();
    $out=[];
    foreach($catalog as $theme)$out[$theme['slug']]=$theme;
    return $out;
}

function active_admin_theme(): string {
    $catalog=theme_catalog_index('admin');
    $saved=(string)setting('admin_theme_slug','aurora-command');
    $preview=trim((string)($_GET['theme_preview']??''));
    $u=function_exists('current_user')?current_user():null;
    if($preview!=='' && $u && has_permission('themes.manage',$u) && isset($catalog[$preview])) return $preview;
    return isset($catalog[$saved])?$saved:'aurora-command';
}

function active_landing_theme(): string {
    $catalog=theme_catalog_index('landing');
    $saved=(string)setting('landing_theme_slug','metro-portal');
    $preview=trim((string)($_GET['theme_preview']??''));
    $u=function_exists('current_user')?current_user():null;
    if($preview!=='' && $u && has_permission('themes.manage',$u) && isset($catalog[$preview])) return $preview;
    return isset($catalog[$saved])?$saved:'metro-portal';
}

function theme_definition(string $scope,string $slug): array {
    $catalog=theme_catalog_index($scope);
    return $catalog[$slug]??reset($catalog);
}

function theme_animation_enabled(string $scope): bool {
    return feature_enabled($scope==='admin'?'admin_theme_animations':'landing_theme_animations',true);
}
