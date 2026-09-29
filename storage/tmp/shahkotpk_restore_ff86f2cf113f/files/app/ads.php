<?php
declare(strict_types=1);

function ad_placements(): array {
    return [
        'homepage_top'=>['label'=>'Homepage Top','size'=>'1200 × 360','hint'=>'Premium hero-area promotion'],
        'homepage_mid'=>['label'=>'Homepage Middle','size'=>'1200 × 300','hint'=>'Between homepage content sections'],
        'homepage_bottom'=>['label'=>'Homepage Bottom','size'=>'1200 × 300','hint'=>'Before footer / lower homepage'],
        'directory_top'=>['label'=>'Directory Top','size'=>'1200 × 260','hint'=>'Above business directory results'],
        'sidebar'=>['label'=>'Sidebar','size'=>'420 × 520','hint'=>'Tall sidebar / side-rail creative'],
        'business_page'=>['label'=>'Business Page','size'=>'900 × 260','hint'=>'Business profile page promotion'],
        'sponsored_spotlight'=>['label'=>'Sponsored Business Spotlight','size'=>'1200 × 420','hint'=>'Large premium homepage business showcase'],
        'category_sponsor'=>['label'=>'Category Sponsor','size'=>'1200 × 260','hint'=>'Sponsored placement around category discovery'],
        'search_sponsor'=>['label'=>'Search Sponsor','size'=>'1200 × 260','hint'=>'Sponsored placement on business search'],
        'city_guide'=>['label'=>'City Guide Sponsor','size'=>'1200 × 280','hint'=>'Promotion within City Guide content'],
        'deals'=>['label'=>'Deals Sponsor','size'=>'1200 × 280','hint'=>'Promotion around local deals'],
        'events'=>['label'=>'Events Sponsor','size'=>'1200 × 280','hint'=>'Promotion around city events'],
        'jobs'=>['label'=>'Jobs Sponsor','size'=>'1200 × 280','hint'=>'Promotion around jobs'],
        'property'=>['label'=>'Property Sponsor','size'=>'1200 × 280','hint'=>'Promotion around property listings'],
    ];
}
function ad_statuses(): array {return ['pending','scheduled','active','paused','completed','rejected'];}
function ad_pricing_models(): array {return ['flat'=>'Flat Campaign','cpc'=>'CPC — Cost Per Click','cpm'=>'CPM — Cost Per 1,000 Views'];}
function ad_billing_statuses(): array {return ['unpaid'=>'Unpaid','paid'=>'Paid','waived'=>'Waived','refunded'=>'Refunded'];}
function ad_device_targets(): array {return ['all'=>'All Devices','desktop'=>'Desktop','mobile'=>'Mobile','tablet'=>'Tablet'];}

function ad_datetime_input(?string $value): string {
    if(!$value)return '';
    $ts=strtotime($value);return $ts?date('Y-m-d\TH:i',$ts):'';
}
function ad_valid_target_url(string $url): bool {
    if($url==='')return true;
    if(str_starts_with($url,'/')&&!str_starts_with($url,'//'))return true;
    if(!filter_var($url,FILTER_VALIDATE_URL))return false;
    if(!setting_bool('ad_allow_external_urls',true))return false;
    return in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true);
}
function ad_detect_device(): string {
    $ua=strtolower((string)($_SERVER['HTTP_USER_AGENT']??''));
    if($ua==='')return 'desktop';
    if(preg_match('/ipad|tablet|kindle|silk|playbook/',$ua))return 'tablet';
    if(preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|iemobile/',$ua))return 'mobile';
    return 'desktop';
}
function ad_effective_spend(array $ad): float {
    $model=(string)($ad['pricing_model']??'flat');$rate=(float)($ad['rate']??0);$impressions=(int)($ad['impressions']??0);$clicks=(int)($ad['clicks']??0);$charge=(float)($ad['charge_amount']??0);
    if($model==='cpc')return round($clicks*$rate,2);
    if($model==='cpm')return round(($impressions/1000)*$rate,2);
    return $charge>0?$charge:(float)($ad['budget']??0);
}
function ad_ctr(array $ad): float {$i=(int)($ad['impressions']??0);return $i>0?round(((int)($ad['clicks']??0)/$i)*100,2):0.0;}
function ad_remaining_budget(array $ad): float {$b=(float)($ad['budget']??0);if($b<=0)return 0;return max(0,round($b-ad_effective_spend($ad),2));}
function ad_limit_reached(array $ad): bool {
    if((int)($ad['impression_limit']??0)>0 && (int)($ad['impressions']??0)>=(int)$ad['impression_limit'])return true;
    if((int)($ad['click_limit']??0)>0 && (int)($ad['clicks']??0)>=(int)$ad['click_limit'])return true;
    $budget=(float)($ad['budget']??0);if($budget>0 && in_array(($ad['pricing_model']??'flat'),['cpc','cpm'],true) && ad_effective_spend($ad)>=$budget)return true;
    return false;
}
function ad_sync_scheduled_statuses(): void {
    if(!setting_bool('advertisement_auto_schedule',true))return;
    try{
        db()->exec("UPDATE advertisements SET status='active' WHERE status='scheduled' AND (start_at IS NULL OR start_at<=NOW()) AND (end_at IS NULL OR end_at>=NOW())");
        db()->exec("UPDATE advertisements SET status='completed' WHERE status IN ('active','scheduled') AND end_at IS NOT NULL AND end_at<NOW()");
        db()->exec("UPDATE advertisements a JOIN advertisement_details d ON d.advertisement_id=a.id SET a.status='completed' WHERE a.status='active' AND ((d.impression_limit>0 AND d.impressions>=d.impression_limit) OR (d.click_limit>0 AND d.clicks>=d.click_limit) OR (a.budget>0 AND ((d.pricing_model='cpc' AND d.clicks*d.rate>=a.budget) OR (d.pricing_model='cpm' AND (d.impressions/1000)*d.rate>=a.budget))))");
    }catch(Throwable $e){}
}
function ad_frequency_allowed(array $ad): bool {
    $cap=(int)($ad['frequency_cap']??0);if($cap<=0)return true;
    if(session_status()!==PHP_SESSION_ACTIVE)@session_start();
    $key='adfreq_'.(int)$ad['id'].'_'.date('Ymd');return (int)($_SESSION[$key]??0)<$cap;
}
function ad_frequency_record(array $ad): void {
    $cap=(int)($ad['frequency_cap']??0);if($cap<=0)return;
    if(session_status()!==PHP_SESSION_ACTIVE)@session_start();
    $key='adfreq_'.(int)$ad['id'].'_'.date('Ymd');$_SESSION[$key]=(int)($_SESSION[$key]??0)+1;
}
function ad_placement_meta(string $placement): array {return ad_placements()[$placement]??['label'=>ucwords(str_replace('_',' ',$placement)),'size'=>'Flexible','hint'=>'Custom placement'];}
