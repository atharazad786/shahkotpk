<?php
declare(strict_types=1);

function default_banner_slides(): array {
    return [
        [
            'id'=>'banner_discover',
            'enabled'=>1,
            'order'=>10,
            'title'=>'Discover the Best of Shahkot',
            'subtitle'=>'Your city. Your businesses. Your guide.',
            'text'=>'Explore local shops, trusted services, restaurants, important places and useful city information from one modern portal.',
            'button_text'=>'Explore Directory',
            'button_url'=>'/search.php',
            'image'=>''
        ],
        [
            'id'=>'banner_business',
            'enabled'=>1,
            'order'=>20,
            'title'=>'Grow Your Business Locally',
            'subtitle'=>'Put your business where Shahkot can find it.',
            'text'=>'Create a professional business presence, showcase services and connect with more customers across the city.',
            'button_text'=>'Register Business',
            'button_url'=>'/signup.php',
            'image'=>''
        ],
        [
            'id'=>'banner_city',
            'enabled'=>1,
            'order'=>30,
            'title'=>'Everything Shahkot in One Place',
            'subtitle'=>'Useful information when you need it.',
            'text'=>'Browse city essentials, popular categories, featured businesses, useful services and community resources.',
            'button_text'=>'Browse Services',
            'button_url'=>'/search.php',
            'image'=>''
        ],
    ];
}

function banner_slides(): array {
    try{
        $q=db()->prepare("SELECT setting_value FROM settings WHERE setting_key='homepage_banner_slides' LIMIT 1");
        $q->execute();
        $json=$q->fetchColumn();
        if($json){
            $slides=json_decode($json,true);
            if(is_array($slides)){
                usort($slides,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));
                return $slides;
            }
        }
    }catch(Throwable $e){}
    return default_banner_slides();
}

function save_banner_slides(array $slides): void {
    usort($slides,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));
    $json=json_encode(array_values($slides),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $q=db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('homepage_banner_slides',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $q->execute([$json]);
}
