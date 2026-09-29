<?php
declare(strict_types=1);

// v7.0.5 — adaptive tenant-aware brand assets shared across public/admin/auth/module shells.
if (is_file(__DIR__.'/header_menu_v702.php')) {
    require_once __DIR__.'/header_menu_v702.php';
}

function gb704_brand_name(string $fallback='ShahkotPK'): string {
    if (function_exists('hm702_brand_name')) return hm702_brand_name($fallback);
    if (function_exists('tenant_brand')) return (string)tenant_brand('site_name',$fallback);
    if (function_exists('setting')) return (string)setting('site_name',$fallback);
    return $fallback;
}
function gb704_logo_url(): string {
    if (function_exists('hm702_logo_dark_url')) return trim((string)hm702_logo_dark_url());
    if (function_exists('hm702_logo_url')) return trim((string)hm702_logo_url());
    if (function_exists('tenant_brand')) {
        $v=trim((string)tenant_brand('logo_url',''));
        if ($v!=='') return $v;
    }
    if (function_exists('setting')) return trim((string)setting('logo_url',''));
    return '';
}
function gb704_light_logo_url(): string {
    if (function_exists('hm702_logo_light_url')) return trim((string)hm702_logo_light_url());
    return gb704_logo_url();
}
function gb704_mobile_logo_url(): string {
    if (function_exists('hm702_mobile_logo_dark_url')) return trim((string)hm702_mobile_logo_dark_url());
    if (function_exists('hm702_mobile_logo_url')) return trim((string)hm702_mobile_logo_url());
    return gb704_logo_url();
}
function gb704_mobile_light_logo_url(): string {
    if (function_exists('hm702_mobile_logo_light_url')) return trim((string)hm702_mobile_logo_light_url());
    return gb704_light_logo_url();
}
function gb704_favicon_dark_url(): string {
    if (function_exists('hm702_favicon_dark_url')) return trim((string)hm702_favicon_dark_url());
    return gb704_mobile_logo_url();
}
function gb704_favicon_light_url(): string {
    if (function_exists('hm702_favicon_light_url')) return trim((string)hm702_favicon_light_url());
    return gb704_mobile_light_logo_url();
}
function gb704_auto_adapt(): bool {
    return !function_exists('hm702_brand_auto_adapt') || hm702_brand_auto_adapt();
}
function gb704_any_logo_url(): string {
    foreach([gb704_logo_url(),gb704_light_logo_url(),gb704_mobile_logo_url(),gb704_mobile_light_logo_url()] as $v){if(trim($v)!=='')return trim($v);}return '';
}
function gb704_picture(string $context='default', string $alt=''): string {
    $dark=gb704_logo_url();
    $light=gb704_light_logo_url();
    $mobileDark=gb704_mobile_logo_url();
    $mobileLight=gb704_mobile_light_logo_url();
    $fallback=$dark!==''?$dark:($light!==''?$light:($mobileDark!==''?$mobileDark:$mobileLight));
    if ($fallback==='') return '';
    if($dark==='')$dark=$fallback;if($light==='')$light=$dark;if($mobileDark==='')$mobileDark=$dark;if($mobileLight==='')$mobileLight=$light;
    $alt=$alt!==''?$alt:gb704_brand_name('ShahkotPK');
    $ctx=preg_replace('/[^a-z0-9_-]/i','',$context) ?: 'default';
    $adaptive=gb704_auto_adapt()?'1':'0';
    return '<picture class="gb704-picture gb704-'.$ctx.' gb705-adaptive" data-gb-context="'.e($ctx).'" data-gb-auto="'.$adaptive.'"><img src="'.e($fallback).'" alt="'.e($alt).'" loading="eager" decoding="async" data-gb-desktop-dark="'.e($dark).'" data-gb-desktop-light="'.e($light).'" data-gb-mobile-dark="'.e($mobileDark).'" data-gb-mobile-light="'.e($mobileLight).'" data-gb-current=""></picture>';
}
function gb704_favicon_tags(): string {
    $dark=gb704_favicon_dark_url();$light=gb704_favicon_light_url();$fallback=$dark!==''?$dark:$light;
    if($fallback==='')return '';
    if($dark==='')$dark=$fallback;if($light==='')$light=$fallback;
    return '<link rel="icon" id="gb705Favicon" href="'.e($fallback).'" data-gb-favicon-dark="'.e($dark).'" data-gb-favicon-light="'.e($light).'"><link rel="apple-touch-icon" href="'.e($fallback).'">';
}
function gb704_asset_tag(): string {
    return gb704_favicon_tags().'<link rel="stylesheet" href="/assets/global-brand-7.0.5.css?v=705"><script defer src="/assets/global-brand-7.0.5.js?v=705"></script>';
}
