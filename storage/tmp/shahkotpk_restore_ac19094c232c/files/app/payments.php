<?php
declare(strict_types=1);
require_once __DIR__.'/accounting.php';
function payment_reference(): string {$prefix=strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',(string)setting('payment_reference_prefix','SHP'))?:'SHP',0,10));return $prefix.'-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(4)));}
function get_payment_gateways(bool $enabledOnly=true): array {
    try{return db()->query("SELECT * FROM payment_gateways".($enabledOnly?" WHERE enabled=1":"")." ORDER BY sort_order,id")->fetchAll();}catch(Throwable $e){return [];}
}
function create_payment_order(int $uid,float $amount,string $purpose='other',?int $business=null,?int $plan=null,?int $ad=null,string $effect='none',array $meta=[]): int {
    $ref=payment_reference();
    $q=db()->prepare("INSERT INTO payment_orders(user_id,business_id,plan_id,advertisement_id,purpose,amount,currency,status,approval_effect,reference,metadata_json) VALUES(?,?,?,?,?,?,?,'pending',?,?,?)");
    $q->execute([$uid,$business,$plan,$ad,$purpose,$amount,'PKR',$effect,$ref,json_encode($meta,JSON_UNESCAPED_SLASHES)]);
    return (int)db()->lastInsertId();
}
function get_payment_order(int $id): ?array {
    $q=db()->prepare("SELECT po.*,u.name user_name,u.email user_email,u.phone user_phone,b.name business_name,sp.name plan_name FROM payment_orders po JOIN users u ON u.id=po.user_id LEFT JOIN businesses b ON b.id=po.business_id LEFT JOIN subscription_plans sp ON sp.id=po.plan_id WHERE po.id=? LIMIT 1");
    $q->execute([$id]); return $q->fetch()?:null;
}
function plan_duration_days(int $plan): int {
    try{$q=db()->prepare("SELECT duration_days FROM subscription_plan_details WHERE plan_id=?");$q->execute([$plan]);return max(1,(int)($q->fetchColumn()?:365));}catch(Throwable $e){return 365;}
}
function activate_order_effects(array $o,?int $by=null): void {
    if(($o['approval_effect']??'none')==='approve_user'){
        approve_user_account((int)$o['user_id'],'payment',$by,$o['plan_id']?(int)$o['plan_id']:null,'Approved after payment.');
    }
    if(($o['approval_effect']??'none')==='activate_subscription' && $o['plan_id'] && $o['business_id']){
        $days=plan_duration_days((int)$o['plan_id']);$start=date('Y-m-d');$end=date('Y-m-d',strtotime('+'.$days.' days'));
        db()->prepare("INSERT INTO subscriptions(business_id,plan_id,start_date,end_date,status,amount) VALUES(?,?,?,?,'active',?)")->execute([$o['business_id'],$o['plan_id'],$start,$end,$o['amount']]);
    }
    if(($o['purpose']??'')==='ecommerce'){
        $meta=json_decode((string)($o['metadata_json']??'{}'),true);$storeOrderId=(int)($meta['store_order_id']??0);
        if($storeOrderId){db()->prepare("UPDATE store_orders SET payment_status='paid',status=IF(status='pending','confirmed',status) WHERE id=?")->execute([$storeOrderId]);}
    }
}
function mark_payment_order_paid(int $orderId,string $gateway,string $providerRef='',array $payload=[],?int $by=null): void {
    $o=get_payment_order($orderId); if(!$o)throw new RuntimeException('Payment order not found.'); if($o['status']==='paid')return;
    db()->beginTransaction();
    try{
        db()->prepare("UPDATE payment_orders SET status='paid' WHERE id=?")->execute([$orderId]);
        $q=db()->prepare("SELECT id FROM payment_transactions WHERE order_id=? ORDER BY id DESC LIMIT 1");$q->execute([$orderId]);$tx=$q->fetchColumn();
        if($tx)db()->prepare("UPDATE payment_transactions SET status='paid',provider_reference=?,payload_json=?,paid_at=NOW(),approved_by=? WHERE id=?")->execute([$providerRef,json_encode($payload,JSON_UNESCAPED_SLASHES),$by,$tx]);
        else db()->prepare("INSERT INTO payment_transactions(order_id,gateway_code,amount,status,provider_reference,payload_json,paid_at,approved_by) VALUES(?,?,?,'paid',?,?,NOW(),?)")->execute([$orderId,$gateway,$o['amount'],$providerRef,json_encode($payload,JSON_UNESCAPED_SLASHES),$by]);
        activate_order_effects($o,$by);
        accounting_handle_paid_order($o,$gateway,$by);
        db()->commit();
        if(function_exists('operations_after_payment_paid')) operations_after_payment_paid($o,$by);
    }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();throw $e;}
}
function submit_manual_payment(int $orderId,string $gateway,string $ref,string $proof=''): void {
    $o=get_payment_order($orderId);if(!$o)throw new RuntimeException('Payment order not found.');
    db()->prepare("UPDATE payment_orders SET status='awaiting_payment' WHERE id=?")->execute([$orderId]);
    db()->prepare("INSERT INTO payment_transactions(order_id,gateway_code,amount,status,customer_reference,proof_url) VALUES(?,?,?,'review',?,?)")->execute([$orderId,$gateway,$o['amount'],$ref,$proof]);
}
function upload_payment_proof(array $file): ?string {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK)throw new RuntimeException('Payment proof upload failed.');
    $maxMb=max(1,min(50,setting_int('payment_proof_max_mb',5)));if(($file['size']??0)>$maxMb*1024*1024)throw new RuntimeException('Proof must be '.$maxMb.'MB or smaller.');
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);if(!isset($allowed[$mime]))throw new RuntimeException('Use JPG, PNG, WEBP or PDF proof.');
    $dir=__DIR__.'/../uploads/payments';if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Unable to create payment folder.');
    $name=date('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$allowed[$mime];if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name))throw new RuntimeException('Unable to save proof.');
    return '/uploads/payments/'.$name;
}
