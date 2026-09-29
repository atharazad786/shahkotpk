<?php
require __DIR__.'/app/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('POST required.');}
$slug=trim((string)($_POST['slug']??''));$businessId=(int)($_POST['business_id']??0);$redirect='/business.php?slug='.urlencode($slug);$u=current_user();
try{csrf_check();$action=(string)($_POST['action']??'');
 if($action==='review'){$u=require_login();growth_submit_review($businessId,(int)$u['id'],(int)$_POST['rating'],trim((string)$_POST['title']),trim((string)$_POST['body']));$status='review-submitted';}
 elseif($action==='booking'){growth_create_booking($businessId,$u['id']??null,trim((string)$_POST['service_name']),trim((string)$_POST['booking_date']),trim((string)$_POST['booking_time']),trim((string)$_POST['customer_name']),trim((string)$_POST['customer_phone']),trim((string)$_POST['customer_email']),trim((string)$_POST['notes']));$status='booking-submitted';}
 elseif($action==='lead'){growth_create_lead($businessId,$u['id']??null,(string)($_POST['lead_type']??'inquiry'),trim((string)$_POST['customer_name']),trim((string)$_POST['customer_phone']),trim((string)$_POST['customer_email']),trim((string)$_POST['message']));$status='inquiry-submitted';}
 elseif($action==='report'){$u=require_login();growth_moderation_report((int)$u['id'],'business',$businessId,trim((string)$_POST['reason']),trim((string)$_POST['details']));$status='report-submitted';}
 else throw new RuntimeException('Unknown action.');
 header('Location: '.$redirect.'&success='.urlencode($status),true,302);exit;
}catch(Throwable $e){header('Location: '.$redirect.'&error='.urlencode($e->getMessage()),true,302);exit;}
