<?php
declare(strict_types=1);require __DIR__.'/app/bootstrap.php';if(is_file(__DIR__.'/app/access_control_v1300.php'))require_once __DIR__.'/app/access_control_v1300.php';require_once __DIR__.'/app/services_v1380.php';if(!sk1380_bool('services_enabled',true)){http_response_code(503);exit('Service marketplace is temporarily unavailable.');}$f=['q'=>trim((string)($_GET['q']??'')),'category'=>(int)($_GET['category']??0),'area'=>trim((string)($_GET['area']??'')),'fast'=>!empty($_GET['fast'])];$cats=sk1380_categories();$providers=sk1380_public_providers($f);sk1380_shell('Local Service Marketplace','marketplace');?><style>
/* Yelp Style Services Directory - Green Theme */
body.sps1380 { background-color: #f5f6f5; font-family: 'Inter', -apple-system, sans-serif; color: #333; }
.sk1380-header { background: #fff !important; border-bottom: 1px solid #ebebeb; }
.sk1380-header .brand { color: #173228 !important; }
.sk1380-header nav a { color: #666; font-weight: bold; }
.sk1380-header nav a:hover { color: #176b46; }

.svc-hero { background: #fff; padding: 60px 20px; border-bottom: 1px solid #ebebeb; display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; margin-bottom: 40px; border-radius: 0 0 8px 8px; }
@media (max-width: 900px) { .svc-hero { flex-direction: column; } }
.svc-hero > div { flex: 1; }
.svc-hero .eyebrow { display: block; color: #176b46; font-weight: bold; font-size: 13px; letter-spacing: 1px; margin-bottom: 15px; }
.svc-hero h1 { font-size: 42px; font-weight: 900; color: #173228; margin: 0 0 15px 0; line-height: 1.1; letter-spacing: -1px; }
.svc-hero p { font-size: 18px; color: #555; line-height: 1.6; margin-bottom: 30px; }
.svc-hero form { display: flex; flex-wrap: wrap; gap: 15px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ebebeb; align-items: center; }
.svc-hero input:not([type="checkbox"]), .svc-hero select { flex: 1; min-width: 200px; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
.svc-hero button { background: #176b46; color: #fff; border: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 15px; }
.svc-hero button:hover { background: #125235; }

.svc-hero aside { width: 320px; background: #e9f5ee; padding: 30px; border-radius: 8px; text-align: center; display: flex; flex-direction: column; justify-content: center; }
.svc-hero aside b { font-size: 20px; color: #173228; margin-bottom: 10px; display: block; font-weight: 800; }
.svc-hero aside p { font-size: 15px; color: #176b46; line-height: 1.5; margin-bottom: 20px; }
.svc-hero aside a { background: #173228; color: #fff; font-weight: bold; padding: 12px; border-radius: 4px; text-decoration: none; }

.section-title { max-width: 1200px; margin: 0 auto; margin-bottom: 30px; padding: 0 20px; }
.section-title span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.section-title h2 { font-size: 28px; font-weight: 800; color: #173228; margin: 0; }

.svc-categories { max-width: 1200px; margin: 0 auto 40px auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; padding: 0 20px; }
.svc-categories a { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 20px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; text-align: center; display: flex; flex-direction: column; align-items: center; }
.svc-categories a:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.svc-categories span { font-size: 32px; margin-bottom: 15px; background: #e9f5ee; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
.svc-categories b { color: #173228; font-size: 18px; font-weight: 800; margin-bottom: 8px; }
.svc-categories small { color: #666; font-size: 13px; line-height: 1.4; }

.provider-results { max-width: 1200px; margin: 0 auto 60px auto; padding: 0 20px; }
.provider-results header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
.provider-results header span { display: block; color: #666; font-weight: bold; font-size: 12px; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
.provider-results header h2 { font-size: 28px; font-weight: 800; color: #173228; margin: 0; }
.provider-results header a { color: #176b46; font-weight: bold; text-decoration: none; }

.provider-list { display: grid; grid-template-columns: 1fr; gap: 20px; }
.sk1380-provider-card { background: #fff; border: 1px solid #ebebeb; border-radius: 8px; padding: 25px; display: flex; gap: 25px; align-items: center; transition: box-shadow 0.2s; }
.sk1380-provider-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.sk1380-provider-card .avatar { width: 80px; height: 80px; background: #e9f5ee; color: #176b46; font-size: 28px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 50%; flex-shrink: 0; overflow: hidden; }
.sk1380-provider-card .avatar img { width: 100%; height: 100%; object-fit: cover; }
.sk1380-provider-card .info { flex: 1; }
.sk1380-provider-card .info h3 { font-size: 20px; font-weight: 800; color: #173228; margin: 0 0 5px 0; }
.sk1380-provider-card .info p { margin: 0 0 10px 0; color: #666; font-size: 14px; }
.sk1380-provider-card .info .meta { display: flex; gap: 15px; font-size: 13px; color: #555; }
.sk1380-provider-card .info .meta b { color: #176b46; background: #e9f5ee; padding: 2px 6px; border-radius: 4px; }
.sk1380-provider-card .actions { display: flex; flex-direction: column; gap: 10px; }
.sk1380-provider-card .actions a { background: #f5f6f5; color: #173228; border: 1px solid #ccc; padding: 10px 20px; border-radius: 4px; font-weight: bold; text-decoration: none; text-align: center; font-size: 14px; transition: background 0.2s; min-width: 140px; }
.sk1380-provider-card .actions a:hover { background: #e9f5ee; }
.sk1380-provider-card .actions a.primary { background: #176b46; color: #fff; border-color: #176b46; }
.sk1380-provider-card .actions a.primary:hover { background: #125235; }
</style><section class="svc-hero"><div><span class="eyebrow">LOCAL SERVICE MARKETPLACE</span><h1>Ghar se service book karein.</h1><p>Electrician, plumber, tailor, appliance technician aur approved local professionals ko search karein, direct booking karein ya apni requirement post karke quotes receive karein.</p><form><input name="q" value="<?=sk1380_h($f['q'])?>" placeholder="What service do you need?"><select name="category"><option value="0">All services</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$f['category']==$c['id']?'selected':''?>><?=sk1380_h($c['name'])?></option><?php endforeach;?></select><input name="area" value="<?=sk1380_h($f['area'])?>" placeholder="Area in Shahkot"><label class="fastcheck"><input type="checkbox" name="fast" value="1" <?=$f['fast']?'checked':''?>> Fast Service</label><button>Find Providers</button></form></div><aside><b>Describe the job once</b><p>Post your requirement and let matching approved providers respond with quotes.</p><a href="/request-service.php">Post Service Request</a></aside></section><section><div class="section-title"><div><span>POPULAR NEEDS</span><h2>Choose a service</h2></div></div><div class="svc-categories"><?php foreach($cats as $c):?><a href="?category=<?=$c['id']?>"><span><?=sk1380_h($c['icon'])?></span><b><?=sk1380_h($c['name'])?></b><small><?=sk1380_h($c['description'])?></small></a><?php endforeach;?></div></section><section class="provider-results"><header><div><span>APPROVED LOCAL PROVIDERS</span><h2><?=number_format(count($providers))?> providers found</h2></div><a href="/service-provider-register.php">Become a Service Provider →</a></header><div class="provider-list"><?php if(!$providers):?><div class="empty"><b>No approved providers match these filters yet.</b><p>Post a service request so providers in that category can respond when available.</p><a class="btn" href="/request-service.php">Post a Request</a></div><?php else:foreach($providers as $p)sk1380_provider_card($p);endif;?></div></section><?php sk1380_end();