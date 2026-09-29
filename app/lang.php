<?php
declare(strict_types=1);

/**
 * Basic Multilingual Support Module (English & Urdu)
 */

session_start();

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ur'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // redirect back
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $referer");
    exit;
}

function current_lang(): string {
    return $_SESSION['lang'] ?? 'en';
}

function __t(string $key): string {
    $translations = [
        'en' => [
            'welcome' => 'Welcome to ShahkotPK',
            'search' => 'What are you looking for?',
            'deals' => 'Today\'s Deals',
            'doctors' => 'Find a Doctor',
            'jobs' => 'Local Jobs'
        ],
        'ur' => [
            'welcome' => 'شاہکوٹ پی کے میں خوش آمدید',
            'search' => 'آپ کیا تلاش کر رہے ہیں؟',
            'deals' => 'آج کی ڈیلز',
            'doctors' => 'ڈاکٹر تلاش کریں',
            'jobs' => 'مقامی نوکریاں'
        ]
    ];
    
    $lang = current_lang();
    return $translations[$lang][$key] ?? $key;
}
