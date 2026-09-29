<?php
declare(strict_types=1);

/** ShahkotPK v5.4.0 built-in landing slider banner pack. */
function v54_banner_pack_enabled(): bool {
    return function_exists('setting_bool') ? setting_bool('v54_banner_pack_enabled', true) : true;
}

function v54_banner_pack_slides(): array {
    if (!v54_banner_pack_enabled()) return [];
    $defs = [
        1 => [
            'image' => '/assets/banners/v5.4/01-discover-shahkot.webp',
            'title' => 'Discover Shahkot',
            'subtitle' => 'YOUR CITY · ONE PLATFORM',
            'text' => 'Everything you need in one place.',
            'button_text' => 'Explore Now',
            'button_url' => (string)setting('v54_banner_1_url', '/search.php'),
        ],
        2 => [
            'image' => '/assets/banners/v5.4/02-everything-in-shahkot.webp',
            'title' => 'Everything You Need in Shahkot',
            'subtitle' => 'LOCAL · TRUSTED · USEFUL',
            'text' => 'Businesses, events, jobs, property, deals and city services.',
            'button_text' => 'Browse City',
            'button_url' => (string)setting('v54_banner_2_url', '/businesses.php'),
        ],
        3 => [
            'image' => '/assets/banners/v5.4/03-explore-connect-shahkot.webp',
            'title' => 'Explore. Connect. Experience.',
            'subtitle' => 'SMART CITY DISCOVERY',
            'text' => 'Discover what is around you with live location and map search.',
            'button_text' => 'Explore Map',
            'button_url' => (string)setting('v54_banner_3_url', '/city-discovery.php'),
        ],
    ];
    $out = [];
    foreach ($defs as $n => $slide) {
        if (!setting_bool('v54_banner_'.$n.'_enabled', true)) continue;
        $slide['visual_only'] = setting_bool('v54_banner_visual_only', true);
        $slide['source'] = 'v54-banner-pack';
        $slide['pack_order'] = $n;
        $out[] = $slide;
    }
    return $out;
}

function v54_merge_slider_banners(array $existing): array {
    $pack = v54_banner_pack_slides();
    if (!$pack) return $existing;

    $packImages = array_fill_keys(array_map(static fn($s) => (string)($s['image'] ?? ''), $pack), true);
    $existing = array_values(array_filter($existing, static function($s) use ($packImages) {
        return !isset($packImages[(string)($s['image'] ?? '')]);
    }));

    $mode = strtolower(trim((string)setting('v54_banner_pack_position', 'prepend')));
    if ($mode === 'replace') return $pack;
    if ($mode === 'append') return array_merge($existing, $pack);
    return array_merge($pack, $existing);
}
