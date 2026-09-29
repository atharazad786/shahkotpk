<?php
declare(strict_types=1);

function default_homepage_sections(): array {
    return [
        [
            'id'=>'top_notice','type'=>'topbar','enabled'=>1,'order'=>10,
            'title'=>'Welcome to ShahkotPK',
            'subtitle'=>'Your digital city information & business directory',
            'content'=>'Stay connected with Shahkot.',
            'image'=>'','items'=>'Facebook|Follow Us|#|\nBusiness Registration|List Your Business|/signup.php|'
        ],
        [
            'id'=>'main_header','type'=>'header','enabled'=>1,'order'=>20,
            'title'=>'ShahkotPK',
            'subtitle'=>'Discover Shahkot',
            'content'=>'Businesses • Places • Services • City Information',
            'image'=>'',
            'items'=>'Home||/|\nBusinesses||/search.php|\nRegister Business||/signup.php|\nLogin||/login.php|'
        ],
        [
            'id'=>'hero_search','type'=>'hero','enabled'=>1,'order'=>30,
            'title'=>'Your Search Made Easier',
            'subtitle'=>'Find it when you need it.',
            'content'=>'Search shops, services, restaurants and useful city information across Shahkot.',
            'image'=>'',
            'items'=>'Popular: Restaurants||/search.php?category=restaurants|\nMobile Shops||/search.php?category=mobile-shops|\nServices||/search.php?category=services|'
        ],
        [
            'id'=>'city_info','type'=>'info_cards','enabled'=>1,'order'=>40,
            'title'=>'Shahkot at a Glance',
            'subtitle'=>'Useful city information',
            'content'=>'',
            'image'=>'',
            'items'=>'Time in Shahkot|Pakistan Standard Time (PKT)||\nWeather|Update weather text from Admin||\nEmergency|Add important helpline information from Admin||'
        ],
        [
            'id'=>'quick_services','type'=>'quick_services','enabled'=>1,'order'=>45,
            'title'=>'City Essentials',
            'subtitle'=>'Find important services faster',
            'content'=>'Popular Shahkot services and information, one click away.',
            'image'=>'',
            'items'=>'Hospitals|Healthcare & emergency services|/search.php?q=hospital|\nSchools & Education|Schools, academies and institutes|/search.php?q=school|\nRestaurants & Food|Discover places to eat|/search.php?category=restaurants|\nBanks & Finance|Banks and financial services|/search.php?q=bank|\nReal Estate|Property and real estate services|/search.php?q=real+estate|\nJobs & Careers|Local career resources|#|'
        ],
        [
            'id'=>'business_cta','type'=>'cta','enabled'=>1,'order'=>50,
            'title'=>'Put Your Business on ShahkotPK',
            'subtitle'=>'Reach more customers in your city',
            'content'=>'Create your business profile, showcase products and services, publish offers and grow your local visibility.',
            'image'=>'',
            'items'=>'Register Your Business||/signup.php|'
        ],
        [
            'id'=>'welcome','type'=>'content','enabled'=>1,'order'=>60,
            'title'=>'Welcome to ShahkotPK',
            'subtitle'=>'Online information center and business directory of Shahkot',
            'content'=>'ShahkotPK brings local businesses, services, city information, places, offers and community resources together in one modern city portal.',
            'image'=>'',
            'items'=>''
        ],
        [
            'id'=>'directory','type'=>'directory','enabled'=>1,'order'=>70,
            'title'=>'Shahkot Business Directory',
            'subtitle'=>'Browse popular categories',
            'content'=>'Find trusted businesses and services by category.',
            'image'=>'','items'=>''
        ],
        [
            'id'=>'featured','type'=>'featured','enabled'=>1,'order'=>80,
            'title'=>'Featured Businesses',
            'subtitle'=>'Local businesses worth discovering',
            'content'=>'Featured and verified businesses appear here.',
            'image'=>'','items'=>''
        ],
        [
            'id'=>'city_spotlights','type'=>'spotlights','enabled'=>1,'order'=>85,
            'title'=>'Discover Shahkot',
            'subtitle'=>'Explore the city beyond the directory',
            'content'=>'Highlight markets, places, community life and important parts of Shahkot.',
            'image'=>'',
            'items'=>'Local Markets|Shopping, business and everyday city life|/search.php|\nEducation Hub|Schools, academies and learning services|/search.php?q=school|\nHealth & Wellness|Clinics, hospitals, pharmacies and wellness|/search.php?q=health|'
        ],
        [
            'id'=>'gallery','type'=>'gallery','enabled'=>1,'order'=>90,
            'title'=>'Picture Gallery',
            'subtitle'=>'Explore Shahkot',
            'content'=>'Add city photos, landmarks, events and business highlights from the Admin Panel.',
            'image'=>'',
            'items'=>'Shahkot City|City life and community||\nLocal Markets|Explore local shopping||\nPlaces to Visit|Discover Shahkot||'
        ],
        [
            'id'=>'city_links','type'=>'links','enabled'=>1,'order'=>100,
            'title'=>'Explore Shahkot',
            'subtitle'=>'City information',
            'content'=>'',
            'image'=>'',
            'items'=>'About Shahkot||#|\nHospitals||/search.php?q=hospital|\nSchools & Education||/search.php?q=school|\nRestaurants & Food||/search.php?category=restaurants|\nReal Estate||/search.php?q=real+estate|\nJobs & Careers||#|\nEvents||#|\nTourism & Places||#|'
        ],
        [
            'id'=>'category_explorer','type'=>'category_explorer','enabled'=>1,'order'=>72,
            'title'=>'Explore Shahkot','subtitle'=>'Everything around you','content'=>'Browse popular categories with live business counts.','image'=>'','items'=>''
        ],
        [
            'id'=>'city_map','type'=>'city_map','enabled'=>1,'order'=>76,
            'title'=>'Explore Shahkot on Map','subtitle'=>'Interactive city discovery','content'=>'Search shops, businesses, events, property and useful city places on Google Maps.','image'=>'','items'=>''
        ],
        [
            'id'=>'news_portal_widgets','type'=>'news_widgets','enabled'=>1,'order'=>80,
            'title'=>'ShahkotPK Newsroom','subtitle'=>'Latest local updates','content'=>'Breaking, English, Urdu and video news from the city newsroom.','image'=>'','items'=>''
        ],
        [
            'id'=>'deals_offers','type'=>'deals','enabled'=>1,'order'=>82,
            'title'=>'Deals & Offers','subtitle'=>'Save locally','content'=>'Fresh promotions from local shops and businesses.','image'=>'','items'=>''
        ],
        [
            'id'=>'city_guide_portal','type'=>'city_guide','enabled'=>1,'order'=>92,
            'title'=>'Complete City Guide','subtitle'=>'Useful Shahkot information','content'=>'Hospitals, schools, public services, markets and important places.','image'=>'','items'=>''
        ],
        [
            'id'=>'city_events','type'=>'events','enabled'=>1,'order'=>94,
            'title'=>'Events & Announcements','subtitle'=>'What is happening','content'=>'Local events and community information.','image'=>'','items'=>''
        ],
        [
            'id'=>'local_jobs','type'=>'jobs','enabled'=>1,'order'=>96,
            'title'=>'Jobs in Shahkot','subtitle'=>'Local opportunities','content'=>'Vacancies from local employers.','image'=>'','items'=>''
        ],
        [
            'id'=>'property_rentals','type'=>'property','enabled'=>1,'order'=>98,
            'title'=>'Property & Rentals','subtitle'=>'Buy, sell or rent','content'=>'Explore local property listings.','image'=>'','items'=>''
        ],
        [
            'id'=>'footer','type'=>'footer','enabled'=>1,'order'=>110,
            'title'=>'ShahkotPK',
            'subtitle'=>'Your Digital City Guide',
            'content'=>'© 2026 ShahkotPK. All rights reserved.',
            'image'=>'',
            'items'=>'Privacy||#|\nTerms||#|\nContact||#|'
        ],
    ];
}

function homepage_sections(): array {
    $defaults=default_homepage_sections();

    if(function_exists('cms_home_page')){
        try{
            $page=cms_home_page();
            if($page){
                $data=cms_page_sections($page);
                if(is_array($data) && $data) return $data;
            }
        }catch(Throwable $e){}
    }

    try {
        $q=db()->prepare("SELECT setting_value FROM settings WHERE setting_key='homepage_sections' LIMIT 1");
        $q->execute();
        $json=$q->fetchColumn();

        if($json){
            $data=json_decode($json,true);
            if(is_array($data)){
                $layoutVersion=setting_int('homepage_layout_version',0);
                if($layoutVersion < 160){
                    $ids=[];
                    foreach($data as $section) $ids[(string)($section['id']??'')]=true;
                    foreach($defaults as $default){
                        if(in_array($default['id'],['quick_services','city_spotlights'],true) && empty($ids[$default['id']])) $data[]=$default;
                    }
                    usort($data,fn($a,$b)=>(int)($a['order']??0)<=>(int)($b['order']??0));
                    save_homepage_sections($data);
                    save_setting('homepage_layout_version','160');
                }
                return $data;
            }
        }
    } catch(Throwable $e) {}

    try{ save_setting('homepage_layout_version','160'); }catch(Throwable $e){}
    return $defaults;
}

function save_homepage_sections(array $sections): void {
    $json=json_encode(array_values($sections),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $q=db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('homepage_sections',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $q->execute([$json]);
    if(function_exists('cms_home_page')){
        try{
            $page=cms_home_page();
            if($page) db()->prepare("UPDATE cms_pages SET layout_json=?,updated_at=NOW() WHERE id=?")->execute([$json,$page['id']]);
        }catch(Throwable $e){}
    }
}

function section_items(string $raw): array {
    $items=[];
    foreach(preg_split('/\r\n|\r|\n/',trim($raw)) as $line){
        if(trim($line)==='') continue;
        $parts=array_pad(explode('|',$line,4),4,'');
        $items[]=[
            'title'=>trim($parts[0]),
            'text'=>trim($parts[1]),
            'link'=>trim($parts[2]),
            'image'=>trim($parts[3]),
        ];
    }
    return $items;
}

function upload_homepage_image(array $file): ?string {
    if(!feature_enabled('image_uploads_enabled',true)) throw new RuntimeException('Image uploads are disabled in Admin Settings.');
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return null;
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed.');
    $maxMb=max(1,setting_int('max_image_upload_mb',5));
    if(($file['size']??0)>$maxMb*1024*1024) throw new RuntimeException('Image must be '.$maxMb.'MB or smaller.');

    $allowed=[
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'webp',
        'image/gif'=>'gif',
    ];
    $finfo=new finfo(FILEINFO_MIME_TYPE);
    $mime=$finfo->file($file['tmp_name']);
    if(!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');

    $dir=__DIR__.'/../uploads/homepage';
    if(!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('Cannot create homepage upload directory.');

    $name=date('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$allowed[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) throw new RuntimeException('Could not save uploaded image.');
    return '/uploads/homepage/'.$name;
}

function safe_link(string $url): string {
    $url=trim($url);
    if($url==='') return '#';
    if(str_starts_with($url,'/') || str_starts_with($url,'#')) return $url;
    if(filter_var($url,FILTER_VALIDATE_URL)) return $url;
    return '#';
}
