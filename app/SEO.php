<?php
declare(strict_types=1);

/**
 * SEO & OpenGraph Meta Tags Generator
 */
class SEO {
    public static function generateMetaTags(string $title, string $description, string $image = '', string $url = ''): string {
        $siteName = 'ShahkotPK';
        $fullTitle = htmlspecialchars($title . ' - ' . $siteName, ENT_QUOTES);
        $desc = htmlspecialchars($description, ENT_QUOTES);
        $img = htmlspecialchars($image ?: '/assets/images/default-share.jpg', ENT_QUOTES);
        $url = htmlspecialchars($url ?: 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES);
        
        return <<<HTML
        <title>{$fullTitle}</title>
        <meta name="description" content="{$desc}">
        
        <!-- OpenGraph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{$url}">
        <meta property="og:title" content="{$fullTitle}">
        <meta property="og:description" content="{$desc}">
        <meta property="og:image" content="{$img}">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{$url}">
        <meta name="twitter:title" content="{$fullTitle}">
        <meta name="twitter:description" content="{$desc}">
        <meta name="twitter:image" content="{$img}">
HTML;
    }
}
