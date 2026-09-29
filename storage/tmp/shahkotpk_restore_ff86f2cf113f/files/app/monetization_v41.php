<?php
declare(strict_types=1);

function v41_enabled(string $key='commercial_growth_v41_enabled', bool $default=true): bool {
    return function_exists('setting_bool') ? setting_bool($key,$default) : $default;
}
function v41_table_exists(string $table): bool {
    static $cache=[];
    if(array_key_exists($table,$cache)) return $cache[$table];
    if(!preg_match('/^[a-zA-Z0-9_]+$/',$table)) return false;
    try{
        $q=db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');
        $q->execute([$table]);
        return $cache[$table]=(bool)$q->fetchColumn();
    }catch(Throwable $e){
        try{$quoted=db()->quote($table);$q=db()->query('SHOW TABLES LIKE '.$quoted);return $cache[$table]=(bool)$q->fetchColumn();}
        catch(Throwable $e2){if(function_exists('runtime_log'))runtime_log('Regression table check failed for '.$table,$e2);return $cache[$table]=false;}
    }
}
function v41_scalar(string $sql,array $params=[],$default=0){try{$q=db()->prepare($sql);$q->execute($params);$v=$q->fetchColumn();return $v===false?$default:$v;}catch(Throwable $e){return $default;}}
function v41_rows(string $sql,array $params=[]): array {try{$q=db()->prepare($sql);$q->execute($params);return $q->fetchAll()?:[];}catch(Throwable $e){return [];}}
function v41_dashboard_stats(): array {
    $tables=['self_service_ad_campaigns','listing_boosts','subscription_renewal_profiles','monetization_commission_rules','qr_assets','bulk_import_jobs','media_library','redirect_rules','outbound_campaigns','notification_rules','unified_inbox_threads','regression_test_runs'];
    $out=[];foreach($tables as $t)$out[$t]=v41_table_exists($t)?(int)v41_scalar("SELECT COUNT(*) FROM `$t`",[],0):0;
    $out['active_ads']=v41_table_exists('self_service_ad_campaigns')?(int)v41_scalar("SELECT COUNT(*) FROM self_service_ad_campaigns WHERE status='active'",[],0):0;
    $out['active_boosts']=v41_table_exists('listing_boosts')?(int)v41_scalar("SELECT COUNT(*) FROM listing_boosts WHERE status='active' AND ends_at>=NOW()",[],0):0;
    $out['open_inbox']=v41_table_exists('unified_inbox_threads')?(int)v41_scalar("SELECT COUNT(*) FROM unified_inbox_threads WHERE status IN('open','pending')",[],0):0;
    $out['media_bytes']=v41_table_exists('media_library')?(int)v41_scalar("SELECT COALESCE(SUM(file_size),0) FROM media_library WHERE status='active'",[],0):0;
    return $out;
}
function v41_admin_modules(): array {
    return [
        'overview'=>['label'=>'Growth Command Center','permission'=>'monetization.manage','icon'=>'◆','url'=>'/admin/monetization.php'],
        'ads'=>['label'=>'Self-Service Ads','permission'=>'ad_marketplace.manage','icon'=>'◫','url'=>'/admin/ad-marketplace.php'],
        'boosts'=>['label'=>'Listing Boosts','permission'=>'boosts.manage','icon'=>'↑','url'=>'/admin/boosts.php'],
        'renewals'=>['label'=>'Renewal Center','permission'=>'renewals.manage','icon'=>'↻','url'=>'/admin/renewals.php'],
        'commissions'=>['label'=>'Commission Engine','permission'=>'commissions.manage','icon'=>'%','url'=>'/admin/commission-engine.php'],
        'qr'=>['label'=>'QR Center','permission'=>'qr.manage','icon'=>'▦','url'=>'/admin/qr-center.php'],
        'franchise'=>['label'=>'Franchise Settlements','permission'=>'franchise.manage','icon'=>'₨','url'=>'/admin/franchise-settlements.php'],
        'bulk'=>['label'=>'Bulk Import / Export','permission'=>'bulk.manage','icon'=>'⇄','url'=>'/admin/bulk-tools.php'],
        'media'=>['label'=>'Media Library','permission'=>'media.manage','icon'=>'▧','url'=>'/admin/media-library.php'],
        'seo'=>['label'=>'SEO & Redirect Console','permission'=>'redirects.manage','icon'=>'↗','url'=>'/admin/seo-console.php'],
        'campaigns'=>['label'=>'Campaign Center','permission'=>'campaigns.manage','icon'=>'✉','url'=>'/admin/campaigns.php'],
        'inbox'=>['label'=>'Unified Inbox','permission'=>'inbox.manage','icon'=>'☏','url'=>'/admin/inbox.php'],
        'regression'=>['label'=>'Regression Test Center','permission'=>'regression.manage','icon'=>'✓','url'=>'/admin/regression-tests.php'],
    ];
}
function v41_money($n): string {return 'Rs '.number_format((float)$n,0);}
function v41_bytes(int $bytes): string {$units=['B','KB','MB','GB'];$i=0;$n=(float)$bytes;while($n>=1024&&$i<count($units)-1){$n/=1024;$i++;}return number_format($n,$i?1:0).' '.$units[$i];}

function v41_create_ad(array $d,int $userId): int {
    $q=db()->prepare("INSERT INTO self_service_ad_campaigns(user_id,business_id,title,placement,city_id,category_id,target_url,image_url,daily_budget,total_budget,starts_at,ends_at,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $q->execute([$userId,$d['business_id']?:null,trim((string)$d['title']),$d['placement']?:'homepage',$d['city_id']?:null,$d['category_id']?:null,trim((string)$d['target_url']),trim((string)$d['image_url']),(float)$d['daily_budget'],(float)$d['total_budget'],$d['starts_at']?:null,$d['ends_at']?:null,$d['status']?:'draft']);
    return (int)db()->lastInsertId();
}
function v41_sync_ad_to_delivery(int $campaignId): int {
    $rows=v41_rows("SELECT * FROM self_service_ad_campaigns WHERE id=? LIMIT 1",[$campaignId]);$c=$rows[0]??null;if(!$c)return 0;
    $status=(string)$c['status'];$adStatus='pending';
    if($status==='active')$adStatus='active';elseif($status==='approved')$adStatus=(!empty($c['starts_at'])&&strtotime((string)$c['starts_at'])>time())?'scheduled':'active';elseif($status==='paused')$adStatus='paused';elseif($status==='completed')$adStatus='completed';elseif($status==='rejected')$adStatus='rejected';
    $adId=(int)($c['advertisement_id']??0);
    if($adId<=0){
        db()->prepare("INSERT INTO advertisements(business_id,title,placement,image_url,target_url,start_at,end_at,status,budget) VALUES(?,?,?,?,?,?,?,?,?)")->execute([$c['business_id']?:null,$c['title'],$c['placement'],$c['image_url']?:null,$c['target_url']?:null,$c['starts_at']?:null,$c['ends_at']?:null,$adStatus,$c['total_budget']]);$adId=(int)db()->lastInsertId();
        db()->prepare("INSERT INTO advertisement_details(advertisement_id,pricing_model,rate,target_city_id,target_category_id,charge_amount) VALUES(?,'flat',0,?,?,0) ON DUPLICATE KEY UPDATE target_city_id=VALUES(target_city_id),target_category_id=VALUES(target_category_id)")->execute([$adId,$c['city_id']?:null,$c['category_id']?:null]);
        db()->prepare("UPDATE self_service_ad_campaigns SET advertisement_id=? WHERE id=?")->execute([$adId,$campaignId]);
    }else{
        db()->prepare("UPDATE advertisements SET business_id=?,title=?,placement=?,image_url=?,target_url=?,start_at=?,end_at=?,status=?,budget=? WHERE id=?")->execute([$c['business_id']?:null,$c['title'],$c['placement'],$c['image_url']?:null,$c['target_url']?:null,$c['starts_at']?:null,$c['ends_at']?:null,$adStatus,$c['total_budget'],$adId]);
        db()->prepare("INSERT INTO advertisement_details(advertisement_id,pricing_model,rate,target_city_id,target_category_id,charge_amount) VALUES(?,'flat',0,?,?,0) ON DUPLICATE KEY UPDATE target_city_id=VALUES(target_city_id),target_category_id=VALUES(target_category_id)")->execute([$adId,$c['city_id']?:null,$c['category_id']?:null]);
    }
    return $adId;
}
function v41_create_boost(array $d,int $userId): int {
    $q=db()->prepare("INSERT INTO listing_boosts(user_id,entity_type,entity_id,boost_type,amount,starts_at,ends_at,status) VALUES(?,?,?,?,?,?,?,?)");
    $q->execute([$userId,$d['entity_type'],$d['entity_id'],$d['boost_type'],(float)$d['amount'],$d['starts_at'],$d['ends_at'],$d['status']?:'pending']);return (int)db()->lastInsertId();
}
function v41_resolve_commission(array $context): array {
    $default=(float)setting('marketplace_default_commission_percent','10');$best=['percent_rate'=>$default,'flat_fee'=>0,'name'=>'Platform default'];
    if(!v41_table_exists('monetization_commission_rules')||!setting_bool('monetization_commission_enabled',true))return $best;
    try{
        $sql="SELECT * FROM monetization_commission_rules WHERE status=1 AND (product_type='any' OR product_type=?) AND (category_id IS NULL OR category_id=?) AND (seller_user_id IS NULL OR seller_user_id=?) AND (plan_id IS NULL OR plan_id=?) ORDER BY priority ASC,id ASC LIMIT 1";
        $q=db()->prepare($sql);$q->execute([$context['product_type']??'new',$context['category_id']??0,$context['seller_user_id']??0,$context['plan_id']??0]);$r=$q->fetch();if($r)$best=$r;
    }catch(Throwable $e){}
    return $best;
}
function v41_public_base_url(): string {
    $host=preg_replace('/[^a-zA-Z0-9.:-]/','',(string)($_SERVER['HTTP_HOST']??''));
    if($host==='')return '';
    $https=(!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off')||((string)($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https');
    return ($https?'https':'http').'://'.$host;
}
function v41_qr_tracking_url(int $id): string {return v41_public_base_url().'/qr.php?id='.$id;}
function v41_qr_url(string $text,int $size=320,string $fg='111827',string $bg='ffffff'): string {
    $base=(string)setting('qr_provider_base','https://quickchart.io/qr');
    return rtrim($base,'?').'?' . http_build_query(['text'=>$text,'size'=>$size,'dark'=>$fg,'light'=>$bg,'margin'=>2]);
}
function v41_media_upload(array $file,int $userId,string $folder='general'): array {
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new RuntimeException('Choose a valid media file.');
    $max=max(1,(int)setting('media_max_upload_mb','12'))*1024*1024;if(($file['size']??0)>$max)throw new RuntimeException('File exceeds the Media Library upload limit.');
    $finfo=new finfo(FILEINFO_MIME_TYPE);$mime=(string)$finfo->file($file['tmp_name']);
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','application/pdf'=>'pdf','video/mp4'=>'mp4'];if(!isset($allowed[$mime]))throw new RuntimeException('Unsupported media type.');
    $checksum=hash_file('sha256',$file['tmp_name'])?:'';
    if($checksum!==''&&v41_table_exists('media_library')){$old=v41_rows("SELECT id,stored_path,mime_type FROM media_library WHERE checksum=? AND status='active' ORDER BY id LIMIT 1",[$checksum]);if($old)return ['id'=>(int)$old[0]['id'],'url'=>$old[0]['stored_path'],'mime'=>$old[0]['mime_type'],'duplicate'=>true];}
    $dir=__DIR__.'/../uploads/media/'.date('Y/m');if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('Unable to create media folder.');
    $name=date('YmdHis').'-'.bin2hex(random_bytes(7)).'.'.$allowed[$mime];$dest=$dir.'/'.$name;if(!move_uploaded_file($file['tmp_name'],$dest))throw new RuntimeException('Unable to save media file.');
    $relative='/uploads/media/'.date('Y/m').'/'.$name;$w=$h=null;if(str_starts_with($mime,'image/')){$dim=@getimagesize($dest);if(is_array($dim)){$w=$dim[0];$h=$dim[1];}}
    $q=db()->prepare("INSERT INTO media_library(user_id,file_name,stored_path,mime_type,file_size,width,height,checksum,folder) VALUES(?,?,?,?,?,?,?,?,?)");$q->execute([$userId,(string)$file['name'],$relative,$mime,(int)(filesize($dest)?:$file['size']),$w,$h,$checksum,preg_replace('/[^a-zA-Z0-9_-]/','-',trim($folder))?:'general']);
    return ['id'=>(int)db()->lastInsertId(),'url'=>$relative,'mime'=>$mime,'duplicate'=>false];
}

function v41_seller_stats(int $sellerId): array {
    return [
        'products'=>(int)v41_scalar("SELECT COUNT(*) FROM store_products WHERE seller_user_id=?",[$sellerId]),
        'published'=>(int)v41_scalar("SELECT COUNT(*) FROM store_products WHERE seller_user_id=? AND status='published'",[$sellerId]),
        'orders'=>(int)v41_scalar("SELECT COUNT(*) FROM store_orders WHERE seller_user_id=?",[$sellerId]),
        'revenue'=>(float)v41_scalar("SELECT COALESCE(SUM(grand_total),0) FROM store_orders WHERE seller_user_id=? AND payment_status='paid'",[$sellerId],0),
        'leads'=>(int)v41_scalar("SELECT COUNT(*) FROM business_leads bl JOIN businesses b ON b.id=bl.business_id WHERE b.owner_id=?",[$sellerId]),
        'reviews'=>(int)v41_scalar("SELECT COUNT(*) FROM business_reviews br JOIN businesses b ON b.id=br.business_id WHERE b.owner_id=? AND br.status='published'",[$sellerId]),
        'ads'=>v41_table_exists('self_service_ad_campaigns')?(int)v41_scalar("SELECT COUNT(*) FROM self_service_ad_campaigns WHERE user_id=?",[$sellerId]):0,
        'boosts'=>v41_table_exists('listing_boosts')?(int)v41_scalar("SELECT COUNT(*) FROM listing_boosts WHERE user_id=? AND status='active'",[$sellerId]):0,
    ];
}
function v41_customer_stats(int $userId): array {
    return [
        'orders'=>(int)v41_scalar("SELECT COUNT(*) FROM store_orders WHERE buyer_user_id=?",[$userId]),
        'bookings'=>(int)v41_scalar("SELECT COUNT(*) FROM business_bookings WHERE user_id=?",[$userId]),
        'bids'=>(int)v41_scalar("SELECT COUNT(*) FROM store_auction_bids WHERE user_id=?",[$userId]),
        'wallet'=>(float)v41_scalar("SELECT COALESCE(credit_balance,0) FROM loyalty_wallets WHERE user_id=? LIMIT 1",[$userId],0),
        'points'=>(int)v41_scalar("SELECT COALESCE(points,0) FROM loyalty_wallets WHERE user_id=? LIMIT 1",[$userId],0),
        'notifications'=>(int)v41_scalar("SELECT COUNT(*) FROM site_notifications n WHERE n.status='published' AND (n.target_type='all' OR (n.target_type='user' AND n.target_user_id=?))",[$userId]),
    ];
}
function v41_run_regression_tests(int $userId=0): array {
    $start=microtime(true);$tests=[];
    $add=function(string $name,bool $ok,string $detail='')use(&$tests){$tests[]=['name'=>$name,'ok'=>$ok,'detail'=>$detail];};
    $files=['index.php','admin/index.php','admin/operations.php','admin/activity-logs.php','shop.php','live.php','city-map.php','news.php','blog.php','seller-pos.php','app/bootstrap.php','app/layout.php','app/permissions.php','admin/tenants.php','tenant-manifest.php','api/v2/tenant.php'];
    foreach($files as $f)$add('Route/file: '.$f,is_file(__DIR__.'/../'.$f),is_file(__DIR__.'/../'.$f)?'present':'missing');
    foreach(['users','businesses','store_products','store_orders','activity_logs','self_service_ad_campaigns','listing_boosts','media_library','regression_test_runs','ai_search_events','ai_user_profiles','ai_recommendation_cache','ai_automation_runs','tenants','tenant_domains','tenant_members','tenant_features','tenant_revenue_ledger','tenant_audit_logs'] as $t){$exists=v41_table_exists($t);$add('DB table: '.$t,$exists,$exists?'ready':'missing');}
    foreach(['page_start','has_permission','current_user','operations_module_loaded','current_tenant','tenant_apply_city_filter','tenant_feature_enabled'] as $fn)$add('Function: '.$fn,function_exists($fn),function_exists($fn)?'loaded':'not loaded');
    $add('Storage writable',is_writable(__DIR__.'/../storage'),is_writable(__DIR__.'/../storage')?'writable':'not writable');
    $add('Uploads writable',is_dir(__DIR__.'/../uploads')?is_writable(__DIR__.'/../uploads'):is_writable(__DIR__.'/..'),'upload path');
    try{db()->query('SELECT 1');$add('Database connection',true,'connected');}catch(Throwable $e){$add('Database connection',false,$e->getMessage());}
    $passed=count(array_filter($tests,fn($t)=>$t['ok']));$failed=count($tests)-$passed;$ms=(int)round((microtime(true)-$start)*1000);
    if(v41_table_exists('regression_test_runs')){try{$q=db()->prepare("INSERT INTO regression_test_runs(user_id,app_version,total_tests,passed_tests,failed_tests,duration_ms,results_json) VALUES(?,?,?,?,?,?,?)");$q->execute([$userId?:null,(string)setting('installed_app_version','4.3.0'),count($tests),$passed,$failed,$ms,json_encode($tests,JSON_UNESCAPED_SLASHES)]);}catch(Throwable $e){}}
    return ['tests'=>$tests,'passed'=>$passed,'failed'=>$failed,'total'=>count($tests),'duration_ms'=>$ms];
}
function v41_handle_redirects(): void {
    if(PHP_SAPI==='cli'||!setting_bool('redirect_manager_enabled',true)||!v41_table_exists('redirect_rules'))return;
    $uri=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/';
    if(str_starts_with($uri,'/admin/')||str_starts_with($uri,'/api/'))return;
    try{$q=db()->prepare("SELECT id,target_url,status_code FROM redirect_rules WHERE source_path=? AND enabled=1 LIMIT 1");$q->execute([$uri]);$r=$q->fetch();if($r){db()->prepare("UPDATE redirect_rules SET hit_count=hit_count+1 WHERE id=?")->execute([$r['id']]);$code=in_array((int)$r['status_code'],[301,302,307,308],true)?(int)$r['status_code']:301;header('Location: '.$r['target_url'],true,$code);exit;}}catch(Throwable $e){}
}
function v41_template_text(string $text,array $context=[]): string {foreach($context as $k=>$v){if(is_scalar($v))$text=str_replace('{'.$k.'}',(string)$v,$text);}return $text;}
function v41_emit_event(string $eventKey,array $context=[]): array {
    $out=['rules'=>0,'sent'=>0,'failed'=>0];if(!setting_bool('notification_rules_enabled',true)||!v41_table_exists('notification_rules'))return $out;
    $rules=v41_rows("SELECT * FROM notification_rules WHERE enabled=1 AND event_key=? AND (last_run_at IS NULL OR last_run_at<=DATE_SUB(NOW(),INTERVAL cooldown_minutes MINUTE)) ORDER BY id",[$eventKey]);
    foreach($rules as $r){$out['rules']++;$users=[];$target=(int)($context['user_id']??0);if($target>0)$users=v41_rows("SELECT id,name,email,phone,role FROM users WHERE id=? AND status='active' LIMIT 1",[$target]);elseif(!empty($r['audience_role']))$users=v41_rows("SELECT id,name,email,phone,role FROM users WHERE role=? AND status='active' ORDER BY id LIMIT 500",[$r['audience_role']]);if(!$users)continue;$channels=$r['channel']==='multi'?['notification','email','whatsapp']:[$r['channel']];
        foreach($users as $u){$ctx=array_merge($context,['name'=>$u['name']??'','user_id'=>$u['id']]);$msg=v41_template_text((string)$r['template_text'],$ctx);foreach($channels as $channel){try{if($channel==='notification'){db()->prepare("INSERT INTO site_notifications(title,body,notification_type,target_type,target_user_id,status,starts_at) VALUES(?,?,'info','user',?,'published',NOW())")->execute([(string)$r['name'],$msg,$u['id']]);}elseif($channel==='email'&&!empty($u['email'])){require_once __DIR__.'/mailer.php';smtp_send_mail((string)$u['email'],(string)$r['name'],nl2br(e($msg)),$msg);}elseif($channel==='whatsapp'&&!empty($u['phone'])){$phone=preg_replace('/[^0-9+]/','',(string)$u['phone']);if($phone!=='')db()->prepare("INSERT INTO whatsapp_queue(template_key,recipient,message_text,status,scheduled_at) VALUES(NULL,?,?,'queued',NOW())")->execute([$phone,$msg]);else throw new RuntimeException('No phone');}else{throw new RuntimeException('Recipient channel unavailable');}$out['sent']++;}catch(Throwable $e){$out['failed']++;}}}
        db()->prepare("UPDATE notification_rules SET last_run_at=NOW() WHERE id=?")->execute([$r['id']]);
    }return $out;
}
function v41_campaign_recipients(array $campaign): array {
    $type=(string)($campaign['audience_type']??'all');$value=trim((string)($campaign['audience_value']??''));
    if($type==='role'&&$value!=='')return v41_rows("SELECT id,name,email,phone,role FROM users WHERE status='active' AND role=? ORDER BY id LIMIT 500",[$value]);
    if($type==='business'&&(int)$value>0)return v41_rows("SELECT u.id,u.name,u.email,u.phone,u.role FROM businesses b JOIN users u ON u.id=b.owner_id WHERE b.id=? AND u.status='active' LIMIT 1",[(int)$value]);
    if($type==='city'&&(int)$value>0)return v41_rows("SELECT DISTINCT u.id,u.name,u.email,u.phone,u.role FROM businesses b JOIN users u ON u.id=b.owner_id WHERE b.city_id=? AND u.status='active' ORDER BY u.id LIMIT 500",[(int)$value]);
    if($type==='custom'){$ids=array_values(array_filter(array_map('intval',preg_split('/[^0-9]+/',$value))));if(!$ids)return [];$ids=array_slice($ids,0,500);$ph=implode(',',array_fill(0,count($ids),'?'));return v41_rows("SELECT id,name,email,phone,role FROM users WHERE id IN ($ph) AND status='active'",$ids);}
    return v41_rows("SELECT id,name,email,phone,role FROM users WHERE status='active' ORDER BY id LIMIT 500");
}
function v41_dispatch_campaign(int $campaignId): array {
    $c=v41_rows("SELECT * FROM outbound_campaigns WHERE id=? LIMIT 1",[$campaignId]);$c=$c[0]??null;if(!$c)return ['sent'=>0,'failed'=>0,'error'=>'not found'];
    if(!in_array($c['status'],['scheduled','running','draft'],true))return ['sent'=>(int)$c['sent_count'],'failed'=>(int)$c['failed_count'],'error'=>'not dispatchable'];
    db()->prepare("UPDATE outbound_campaigns SET status='running' WHERE id=?")->execute([$campaignId]);$rec=v41_campaign_recipients($c);$sent=0;$failed=0;
    foreach($rec as $u){try{
        if($c['channel']==='notification'){
            db()->prepare("INSERT INTO site_notifications(title,body,notification_type,target_type,target_user_id,status,created_by,starts_at) VALUES(?,?,'promotion','user',?,'published',?,NOW())")->execute([$c['title'],$c['message_text'],$u['id'],$c['created_by']]);
        }elseif($c['channel']==='email'){
            if(empty($u['email']))throw new RuntimeException('No email');require_once __DIR__.'/mailer.php';smtp_send_mail((string)$u['email'],(string)$c['title'],nl2br(e((string)$c['message_text'])),(string)$c['message_text']);
        }elseif($c['channel']==='whatsapp'){
            $phone=preg_replace('/[^0-9+]/','',(string)($u['phone']??''));if($phone==='')throw new RuntimeException('No phone');db()->prepare("INSERT INTO whatsapp_queue(template_key,recipient,message_text,status,scheduled_at) VALUES(NULL,?,?,'queued',NOW())")->execute([$phone,$c['message_text']]);
        }
        $sent++;
    }catch(Throwable $e){$failed++;}}
    db()->prepare("UPDATE outbound_campaigns SET status='completed',total_recipients=?,sent_count=?,failed_count=? WHERE id=?")->execute([count($rec),$sent,$failed,$campaignId]);return ['sent'=>$sent,'failed'=>$failed,'total'=>count($rec)];
}
function v41_process_renewals(): array {
    $out=['reminders'=>0,'invoices'=>0];if(!v41_table_exists('subscription_renewal_profiles'))return $out;
    $rows=v41_rows("SELECT r.*,s.business_id,s.plan_id,s.end_date,s.amount,s.status,b.owner_id,b.name business_name,p.name plan_name,p.price plan_price FROM subscription_renewal_profiles r JOIN subscriptions s ON s.id=r.subscription_id JOIN businesses b ON b.id=s.business_id JOIN subscription_plans p ON p.id=s.plan_id WHERE s.status IN('active','expired') ORDER BY s.end_date ASC LIMIT 300");
    foreach($rows as $r){try{$end=strtotime((string)$r['end_date']);$days=(int)floor(($end-strtotime(date('Y-m-d')))/86400);$rem=(int)$r['reminder_days'];
        if($days<=$rem&&$days>=0){$exists=(int)v41_scalar("SELECT COUNT(*) FROM subscription_renewal_events WHERE subscription_id=? AND event_type='reminder' AND created_at>=CURDATE()",[$r['subscription_id']]);if(!$exists){db()->prepare("INSERT INTO subscription_renewal_events(subscription_id,event_type,status,message) VALUES(?,'reminder','recorded',?)")->execute([$r['subscription_id'],'Subscription expires on '.$r['end_date']]);db()->prepare("INSERT INTO site_notifications(title,body,notification_type,target_type,target_user_id,status,starts_at) VALUES(?,?,'warning','user',?,'published',NOW())")->execute(['Subscription Renewal','Your '.$r['plan_name'].' plan for '.$r['business_name'].' expires on '.$r['end_date'],$r['owner_id']]);db()->prepare("UPDATE subscription_renewal_profiles SET last_reminder_at=NOW() WHERE id=?")->execute([$r['id']]);try{v41_emit_event('subscription.expiring',['user_id'=>(int)$r['owner_id'],'business'=>$r['business_name'],'plan'=>$r['plan_name'],'expiry'=>$r['end_date']]);}catch(Throwable $e){}$out['reminders']++;}}
        if(!empty($r['auto_renew'])&&$days<=0){require_once __DIR__.'/accounting.php';if(function_exists('accounting_create_subscription_invoice')){$has=(int)v41_scalar("SELECT COUNT(*) FROM subscription_invoices WHERE subscription_id=? AND status IN('unpaid','partial','overdue')",[$r['subscription_id']]);if(!$has){$amount=(float)($r['plan_price']??$r['amount']??0);accounting_create_subscription_invoice((int)$r['subscription_id'],(int)$r['business_id'],(int)$r['plan_id'],$amount,null,date('Y-m-d',time()+max(1,(int)$r['grace_days'])*86400),'Auto-renewal invoice generated by v4.1',null);db()->prepare("INSERT INTO subscription_renewal_events(subscription_id,event_type,amount,status,message) VALUES(?,'invoice_created',?,'recorded','Renewal invoice generated')")->execute([$r['subscription_id'],$amount]);$out['invoices']++;}}}
    }catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Renewal automation skipped',$e);}}
    return $out;
}
function v41_run_automation_tick(): array {
    $out=['expired_boosts'=>0,'activated_ads'=>0,'completed_ads'=>0,'campaigns'=>0,'renewals'=>[]];
    try{if(v41_table_exists('listing_boosts')){$out['expired_boosts']=db()->exec("UPDATE listing_boosts SET status='expired' WHERE status='active' AND ends_at<NOW()");}}catch(Throwable $e){}
    try{if(v41_table_exists('self_service_ad_campaigns')){$out['activated_ads']=db()->exec("UPDATE self_service_ad_campaigns SET status='active' WHERE status='approved' AND (starts_at IS NULL OR starts_at<=NOW()) AND (ends_at IS NULL OR ends_at>=NOW())");$out['completed_ads']=db()->exec("UPDATE self_service_ad_campaigns SET status='completed' WHERE status IN('approved','active','paused') AND ends_at IS NOT NULL AND ends_at<NOW()");$sync=v41_rows("SELECT id FROM self_service_ad_campaigns WHERE status IN('approved','active','paused','completed','rejected') ORDER BY id DESC LIMIT 500");foreach($sync as $ad){try{v41_sync_ad_to_delivery((int)$ad['id']);}catch(Throwable $e){}}}}catch(Throwable $e){}
    try{if(v41_table_exists('outbound_campaigns')){$ids=v41_rows("SELECT id FROM outbound_campaigns WHERE status='scheduled' AND (scheduled_at IS NULL OR scheduled_at<=NOW()) ORDER BY id LIMIT 5");foreach($ids as $r){v41_dispatch_campaign((int)$r['id']);$out['campaigns']++;}}}catch(Throwable $e){if(function_exists('runtime_log'))runtime_log('Campaign automation failed',$e);}
    try{$out['renewals']=v41_process_renewals();}catch(Throwable $e){}
    return $out;
}

if(function_exists('setting_bool')) v41_handle_redirects();
