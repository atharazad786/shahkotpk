<?php
require __DIR__.'/app/bootstrap.php';require __DIR__.'/app/growth_public.php';
$slug=trim((string)($_GET['slug']??''));$bw=['b.slug=?','b.status=1'];$bp=[$slug];if(function_exists('tenant_apply_city_filter'))tenant_apply_city_filter($bw,$bp,'b.city_id');$b=growth_row("SELECT b.*,c.name city_name,cat.name category_name,bd.tagline,bd.email,bd.website,bd.opening_hours,bd.map_url,bd.facebook_url,bd.instagram_url,bd.price_range FROM businesses b JOIN cities c ON c.id=b.city_id JOIN categories cat ON cat.id=b.category_id LEFT JOIN business_details bd ON bd.business_id=b.id WHERE ".implode(' AND ',$bw)." LIMIT 1",$bp);if(!$b){http_response_code(404);exit('Business not found.');}
$bid=(int)$b['id'];growth_track('business_view','business',$bid,$bid);$reviewStats=growth_review_stats($bid);$reviews=growth_reviews($bid,20);$verify=growth_verification_status($bid);$menu=growth_restaurant_menu($bid);$coupon=growth_row("SELECT * FROM business_coupons WHERE business_id=? AND status='active' AND (starts_at IS NULL OR starts_at<=NOW()) AND (ends_at IS NULL OR ends_at>=NOW()) ORDER BY id DESC LIMIT 1",[$bid]);$hours=business_hours_for($bid);$open=business_open_status($bid);$owner=(int)$b['owner_id'];$bookingsAllowed=growth_entitled($owner,'bookings',true);$leadsAllowed=growth_entitled($owner,'leads',true);$u=current_user();$success=(string)($_GET['success']??'');$error=(string)($_GET['error']??'');
$seo=growth_render_seo('business',$bid,['title'=>$b['name'].' — '.$b['category_name'].' in '.$b['city_name'],'description'=>$b['tagline']?:mb_strimwidth((string)$b['description'],0,155,'…'),'image'=>$b['image']]);
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><?=$seo?:'<title>'.e($b['name']).' — '.e(setting('site_name','ShahkotPK')).'</title>'?><link rel="stylesheet" href="/assets/themes/landing/base.css?v=240"><link rel="stylesheet" href="/assets/growth-suite-3.5.0.css?v=350">
<style>
/* Yelp Style Business Profile - Green Theme */
body.growth-public { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; color: #333; }
.growth-public-header { background: #fff !important; border-bottom: 1px solid #ebebeb; padding: 15px 0; }
.growth-brand { color: #173228 !important; }
.growth-brand small { color: #666; }
.growth-public-nav a { color: #666; font-weight: bold; }
.growth-public-nav a:hover { color: #176b46; }

/* Hero Section */
.growth-profile-hero { background: #fff; border-bottom: 1px solid #ebebeb; padding-bottom: 30px; margin-bottom: 30px; }
.growth-profile-grid { display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; }
.growth-profile-main { flex: 1; position: relative; }
.growth-profile-side { width: 350px; flex-shrink: 0; }
@media (max-width: 900px) { .growth-profile-grid { flex-direction: column; } .growth-profile-side { width: 100%; } }

.growth-profile-cover { height: 380px; width: 100%; border-radius: 8px; margin-bottom: 25px; margin-top: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background-size: cover; background-position: center; background-color: #e9f5ee; }
.growth-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
.growth-badge { background: #f5f6f5; color: #173228; padding: 6px 12px; border-radius: 4px; font-size: 13px; font-weight: 800; border: 1px solid #ebebeb; }
.growth-badge.rating { background: #176b46; color: #fff; border-color: #176b46; font-size: 14px; }
.growth-profile-main h1 { font-size: 42px; font-weight: 900; color: #173228; margin: 0 0 10px 0; line-height: 1.1; letter-spacing: -1px; }
.growth-profile-main p { font-size: 16px; color: #555; line-height: 1.6; }
.growth-profile-main p b { color: #173228; }

.growth-actions { display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap; border-top: 1px solid #ebebeb; padding-top: 30px; }
.growth-actions a { background: #fff; color: #173228; border: 1px solid #ccc; padding: 12px 24px; border-radius: 6px; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; font-size: 15px; min-width: 140px; }
.growth-actions a:hover { background: #f5f6f5; border-color: #aaa; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.growth-actions a[href^="tel"] { background: #e00707; color: #fff; border-color: #e00707; }
.growth-actions a[href^="tel"]:hover { background: #c90606; color: #fff; border-color: #c90606; box-shadow: 0 4px 10px rgba(224,7,7,0.2); }

/* Sidebar */
.growth-profile-side { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-top: 25px; align-self: flex-start; }
.growth-profile-side h3 { font-size: 20px; font-weight: 800; color: #173228; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #ebebeb; padding-bottom: 10px; }
.growth-profile-side p { margin-bottom: 20px; font-size: 15px; color: #555; line-height: 1.5; }
.growth-profile-side p b { color: #173228; font-size: 16px; display: block; margin-bottom: 4px; }
.growth-profile-side h4 { font-size: 16px; font-weight: bold; color: #173228; margin: 25px 0 15px 0; }
.growth-menu-item { display: flex; justify-content: space-between; border-bottom: 1px solid #f5f6f5; padding: 10px 0; font-size: 14px; }
.growth-menu-item span { color: #666; font-weight: 600; }
.growth-menu-item b { color: #173228; }

/* Sections */
.growth-section { background: #fff; border-top: 1px solid #ebebeb; border-bottom: 1px solid #ebebeb; padding: 50px 0; margin-bottom: 30px; }
.growth-section.alt { background: transparent; border: none; }
.growth-section-head { margin-bottom: 30px; }
.growth-section-head span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.growth-section-head h2 { font-size: 28px; font-weight: 800; color: #173228; margin: 0; }

.growth-form-card { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 20px; width: 100%; box-sizing: border-box; }
.growth-form-card h2, .growth-form-card h3 { font-size: 22px; font-weight: 800; color: #173228; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #ebebeb; padding-bottom: 10px; }

/* Reviews */
.growth-review { border-bottom: 1px solid #ebebeb; padding: 25px 0; }
.growth-review:first-child { padding-top: 0; }
.growth-review:last-child { border-bottom: none; padding-bottom: 0; }
.growth-review-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.growth-review-head b { font-size: 16px; color: #173228; font-weight: 800; }
.growth-review-head strong { background: #176b46; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; font-weight: bold; }
.growth-review h4 { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 8px; }
.growth-review p { font-size: 15px; color: #555; line-height: 1.6; }
.growth-owner-reply { background: #e9f5ee; border-left: 4px solid #176b46; padding: 15px; border-radius: 0 4px 4px 0; margin-top: 15px; font-size: 14px; color: #173228; }
.growth-owner-reply b { font-weight: 800; }

/* Forms */
.growth-input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; font-family: inherit; font-size: 15px; background: #fff; box-sizing: border-box; }
.growth-input:focus { border-color: #176b46; outline: none; box-shadow: 0 0 0 3px rgba(23,107,70,0.1); }
.growth-btn { background: #176b46; color: #fff; border: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s; display: inline-block; text-align: center; font-size: 15px; width: 100%; box-sizing: border-box; }
.growth-btn:hover { background: #125235; }
.growth-btn.dark { background: #e00707; }
.growth-btn.dark:hover { background: #c90606; }
.growth-two { display: flex; gap: 15px; }
.growth-two > div { flex: 1; }
details.growth-form-card summary { cursor: pointer; color: #666; font-weight: bold; }
</style>
</head><body class="growth-public">
<header class="growth-public-header"><div class="lt-shell"><a class="growth-brand" href="/"><?=e(setting('site_name','ShahkotPK'))?><small>Verified Local Business Directory</small></a><nav class="growth-public-nav"><a href="/businesses.php">Businesses</a><a href="/food.php">Food</a><a href="/services.php">Services</a><a href="/classifieds.php">Classifieds</a><a href="/city-map.php">Map</a></nav></div></header>
<section class="growth-profile-hero"><div class="lt-shell growth-profile-grid"><main class="growth-profile-main"><div class="growth-profile-cover" <?=$b['image']?'style="background-image:url(\''.e($b['image']).'\')"':''?>></div><div class="growth-badges"><span class="growth-badge"><?=e($b['category_name'])?></span><?php if($b['verification_status']==='verified'||($verify['status']??'')==='verified'):?><span class="growth-badge">✓ Verified Business</span><?php endif;?><span class="growth-badge rating">★ <?=e(number_format((float)$reviewStats['average_rating'],1))?> · <?=e($reviewStats['total'])?> reviews</span><span class="growth-badge"><?=e($open['label'])?></span></div><h1><?=e($b['name'])?></h1><p><b><?=e($b['tagline']?:$b['category_name'])?></b> · <?=e($b['city_name'])?></p><p><?=nl2br(e((string)$b['description']))?></p><?php if($coupon):?><div class="growth-owner-reply"><b>Local Offer: <?=e($coupon['title'])?></b><br>Code <code><?=e($coupon['code'])?></code> · <?=$coupon['discount_type']==='percent'?e($coupon['discount_value']).'% OFF':'Rs '.e(number_format((float)$coupon['discount_value'])).' OFF'?></div><?php endif;?><div class="growth-actions"><?php if($b['phone']):?><a href="tel:<?=e($b['phone'])?>" onclick="navigator.sendBeacon('/track.php?event=call_click&business_id=<?=e($bid)?>')">Call Now</a><?php endif;?><?php if($b['whatsapp']):?><a target="_blank" href="https://wa.me/<?=e(preg_replace('/\D/','',$b['whatsapp']))?>">WhatsApp</a><?php endif;?><?php if($b['map_url']):?><a target="_blank" href="<?=e($b['map_url'])?>">Directions</a><?php endif;?><?php if($b['website']):?><a target="_blank" href="<?=e($b['website'])?>">Website</a><?php endif;?></div></main><aside class="growth-profile-side"><h3>Business Information</h3><p><b>Address</b><br><?=e($b['address'])?></p><p><b>Phone</b><br><?=e($b['phone']?:'Not provided')?></p><p><b>Price Range</b><br><?=e($b['price_range']?:'Contact business')?></p><p><b>Today</b><br><?=e($open['label'])?> <?=e($open['hours']??'')?></p><h4>Weekly Hours</h4><?php foreach($hours as $h):?><div class="growth-menu-item"><span><?=e($h['day'])?></span><b><?=$h['is_closed']?'Closed':e(($h['open_time']?:'—').' – '.($h['close_time']?:'—'))?></b></div><?php endforeach;?></aside></div></section>
<?php if($success):?><div class="lt-shell success" style="margin-top:15px">Thank you — <?=e(str_replace('-',' ',$success))?>.</div><?php endif;?><?php if($error):?><div class="lt-shell error" style="margin-top:15px"><?=e($error)?></div><?php endif;?>
<?php if($menu):?><section class="growth-section alt"><div class="lt-shell"><div class="growth-section-head"><div><span>RESTAURANT MENU</span><h2>Menu & Prices</h2></div></div><div class="growth-profile-grid"><div class="growth-form-card"><div class="growth-menu-list"><?php foreach($menu as $m):?><div class="growth-menu-item"><div><b><?=e($m['name'])?></b><small><?=e(($m['category_name']?$m['category_name'].' · ':'').$m['description'])?></small></div><strong>Rs <?=e(number_format((float)$m['price']))?></strong></div><?php endforeach;?></div></div><aside class="growth-form-card"><h3>Order / Contact</h3><p>Contact this restaurant directly using call or WhatsApp. Marketplace ordering can be used where products are listed in Shop.</p></aside></div></div></section><?php endif;?>
<section class="growth-section"><div class="lt-shell growth-profile-grid"><main><div class="growth-section-head"><div><span>CUSTOMER FEEDBACK</span><h2>Ratings & Reviews</h2></div></div><div style="display:grid;gap:9px"><?php foreach($reviews as $r):?><article class="growth-review"><div class="growth-review-head"><b><?=e($r['user_name'])?></b><strong>★ <?=e($r['rating'])?>/5</strong></div><h4><?=e($r['title']?:'Customer Review')?></h4><p><?=e($r['body'])?></p><?php if($r['owner_reply']):?><div class="growth-owner-reply"><b>Business reply</b><br><?=e($r['owner_reply'])?></div><?php endif;?></article><?php endforeach;?><?php if(!$reviews):?><div class="growth-empty">No published customer reviews yet.</div><?php endif;?></div></main><aside class="growth-form-card"><h3>Write a Review</h3><?php if(!$u):?><p>Login to rate this business.</p><a class="growth-btn" href="/login.php">Login</a><?php elseif(in_array($u['role'],['user','customer'],true)):?><form method="post" action="/business-engage.php"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="review"><input type="hidden" name="business_id" value="<?=e($bid)?>"><input type="hidden" name="slug" value="<?=e($slug)?>"><label>Rating</label><select class="growth-input" name="rating"><option value="5">5 — Excellent</option><option value="4">4 — Very Good</option><option value="3">3 — Good</option><option value="2">2 — Fair</option><option value="1">1 — Poor</option></select><label>Title</label><input class="growth-input" name="title"><label>Review</label><textarea class="growth-input" rows="4" name="body"></textarea><button class="growth-btn">Submit Review</button></form><?php else:?><p>Customer/user accounts can submit public reviews.</p><?php endif;?></aside></div></section>
<section class="growth-section alt"><div class="lt-shell growth-profile-grid"><div class="growth-form-card"><h2>Send Inquiry / Get Quote</h2><?php if(!$leadsAllowed):?><p>Lead forms are not included in this business's current subscription.</p><?php else:?><form method="post" action="/business-engage.php"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="lead"><input type="hidden" name="business_id" value="<?=e($bid)?>"><input type="hidden" name="slug" value="<?=e($slug)?>"><label>Inquiry Type</label><select class="growth-input" name="lead_type"><option value="inquiry">General Inquiry</option><option value="quote">Get a Quote</option></select><div class="growth-two"><div><label>Name</label><input class="growth-input" name="customer_name" value="<?=e($u['name']??'')?>" required></div><div><label>Phone</label><input class="growth-input" name="customer_phone" value="<?=e($u['phone']??'')?>"></div></div><label>Email</label><input class="growth-input" type="email" name="customer_email" value="<?=e($u['email']??'')?>"><label>Message</label><textarea class="growth-input" rows="4" name="message"></textarea><button class="growth-btn">Send Inquiry</button></form><?php endif;?></div><div class="growth-form-card"><h2>Book Appointment</h2><?php if(!$bookingsAllowed):?><p>Online booking is not included in this business's current subscription.</p><?php else:?><form method="post" action="/business-engage.php"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="booking"><input type="hidden" name="business_id" value="<?=e($bid)?>"><input type="hidden" name="slug" value="<?=e($slug)?>"><label>Service</label><input class="growth-input" name="service_name" placeholder="Appointment / Consultation / Service" required><div class="growth-two"><div><label>Date</label><input class="growth-input" type="date" min="<?=e(date('Y-m-d'))?>" name="booking_date" required></div><div><label>Time</label><input class="growth-input" type="time" name="booking_time" required></div></div><label>Name</label><input class="growth-input" name="customer_name" value="<?=e($u['name']??'')?>" required><label>Phone</label><input class="growth-input" name="customer_phone" value="<?=e($u['phone']??'')?>" required><label>Email</label><input class="growth-input" type="email" name="customer_email" value="<?=e($u['email']??'')?>"><label>Notes</label><textarea class="growth-input" name="notes"></textarea><button class="growth-btn">Request Booking</button></form><?php endif;?></div></div></section>
<section class="growth-section"><div class="lt-shell"><details class="growth-form-card"><summary><b>Report this business</b></summary><?php if($u):?><form method="post" action="/business-engage.php" style="margin-top:12px"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="report"><input type="hidden" name="business_id" value="<?=e($bid)?>"><input type="hidden" name="slug" value="<?=e($slug)?>"><label>Reason</label><input class="growth-input" name="reason" required><label>Details</label><textarea class="growth-input" name="details"></textarea><button class="growth-btn dark">Submit Report</button></form><?php else:?><p>Login to report a listing.</p><?php endif;?></details></div></section>
<?php growth_public_footer(); ?>
