<?php
declare(strict_types=1);
function active_ad_for_placement(string $placement): ?array {
    try{
        ad_sync_scheduled_statuses();$device=ad_detect_device();
        $q=db()->prepare("SELECT a.*,d.pricing_model,d.rate,d.impressions,d.clicks,d.target_city_id,d.target_category_id,d.charge_amount,d.notes,d.description,d.alt_text,d.device_target,d.priority,d.impression_limit,d.click_limit,d.frequency_cap,d.audience_note,d.billing_status,b.name business_name FROM advertisements a LEFT JOIN advertisement_details d ON d.advertisement_id=a.id LEFT JOIN businesses b ON b.id=a.business_id WHERE a.status='active' AND a.placement=? AND (a.start_at IS NULL OR a.start_at<=NOW()) AND (a.end_at IS NULL OR a.end_at>=NOW()) AND (d.device_target IS NULL OR d.device_target='all' OR d.device_target=?) ORDER BY COALESCE(d.priority,10) DESC,RAND() LIMIT 20");
        $q->execute([$placement,$device]);
        foreach($q->fetchAll() as $ad){
            if(ad_limit_reached($ad)||!ad_frequency_allowed($ad))continue;
            db()->prepare("UPDATE advertisement_details SET impressions=impressions+1 WHERE advertisement_id=?")->execute([$ad['id']]);ad_frequency_record($ad);$ad['impressions']=(int)$ad['impressions']+1;return $ad;
        }
        return null;
    }catch(Throwable $e){return null;}
}
