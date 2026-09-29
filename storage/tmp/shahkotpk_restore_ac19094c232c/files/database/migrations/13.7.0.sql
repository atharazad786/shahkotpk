-- ShahkotPK v13.7.0 — Local Classifieds Marketplace Pro
CREATE TABLE IF NOT EXISTS classified_categories_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,parent_id BIGINT UNSIGNED NULL,slug VARCHAR(180) NOT NULL,name VARCHAR(190) NOT NULL,icon VARCHAR(32) NULL,description VARCHAR(500) NULL,sort_order INT NOT NULL DEFAULT 100,active TINYINT(1) NOT NULL DEFAULT 1,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_classified_cat(tenant_id,slug),KEY idx_classified_cat_parent(tenant_id,parent_id,active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_category_fields_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,category_id BIGINT UNSIGNED NOT NULL,field_key VARCHAR(100) NOT NULL,label VARCHAR(160) NOT NULL,field_type VARCHAR(24) NOT NULL DEFAULT 'text',options_json LONGTEXT NULL,required TINYINT(1) NOT NULL DEFAULT 0,filterable TINYINT(1) NOT NULL DEFAULT 0,sort_order INT NOT NULL DEFAULT 100,active TINYINT(1) NOT NULL DEFAULT 1,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_classified_field(category_id,field_key),KEY idx_classified_fields(category_id,active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_ads_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,category_id BIGINT UNSIGNED NOT NULL,title VARCHAR(160) NOT NULL,slug VARCHAR(180) NOT NULL,description MEDIUMTEXT NOT NULL,price DECIMAL(14,2) NOT NULL DEFAULT 0,price_type VARCHAR(24) NOT NULL DEFAULT 'fixed',currency VARCHAR(8) NOT NULL DEFAULT 'PKR',condition_code VARCHAR(32) NOT NULL DEFAULT 'used',city VARCHAR(120) NOT NULL DEFAULT 'Shahkot',area VARCHAR(190) NOT NULL,address_hint VARCHAR(255) NULL,latitude DECIMAL(10,7) NULL,longitude DECIMAL(10,7) NULL,phone VARCHAR(32) NULL,show_phone TINYINT(1) NOT NULL DEFAULT 0,contact_preference VARCHAR(24) NOT NULL DEFAULT 'chat',status VARCHAR(24) NOT NULL DEFAULT 'pending',moderation_status VARCHAR(24) NOT NULL DEFAULT 'pending',rejection_reason VARCHAR(500) NULL,featured_until DATETIME NULL,urgent_until DATETIME NULL,bump_at DATETIME NULL,expires_at DATETIME NULL,views_count BIGINT UNSIGNED NOT NULL DEFAULT 0,favorites_count BIGINT UNSIGNED NOT NULL DEFAULT 0,inquiries_count BIGINT UNSIGNED NOT NULL DEFAULT 0,published_at DATETIME NULL,sold_at DATETIME NULL,source_table VARCHAR(120) NULL,source_id VARCHAR(120) NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_classified_slug(tenant_id,slug),KEY idx_classified_feed(tenant_id,status,moderation_status,bump_at),KEY idx_classified_category(tenant_id,category_id,status),KEY idx_classified_seller(tenant_id,user_id,status),KEY idx_classified_price(tenant_id,price),KEY idx_classified_expiry(tenant_id,status,expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_ad_media_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,ad_id BIGINT UNSIGNED NOT NULL,file_path VARCHAR(800) NOT NULL,media_type VARCHAR(24) NOT NULL DEFAULT 'image',sort_order INT NOT NULL DEFAULT 0,is_cover TINYINT(1) NOT NULL DEFAULT 0,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_classified_media(tenant_id,ad_id,is_cover,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_ad_field_values_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,ad_id BIGINT UNSIGNED NOT NULL,field_id BIGINT UNSIGNED NOT NULL,value_text VARCHAR(1000) NULL,value_num DECIMAL(18,4) NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_classified_value(tenant_id,ad_id,field_id),KEY idx_classified_value_field(tenant_id,field_id,value_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_favorites_v1370 (
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,ad_id BIGINT UNSIGNED NOT NULL,created_at DATETIME NOT NULL,PRIMARY KEY(tenant_id,user_id,ad_id),KEY idx_classified_fav_ad(tenant_id,ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_conversations_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,ad_id BIGINT UNSIGNED NOT NULL,buyer_user_id BIGINT UNSIGNED NOT NULL,seller_user_id BIGINT UNSIGNED NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'open',last_message_at DATETIME NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_classified_conv(tenant_id,ad_id,buyer_user_id,seller_user_id),KEY idx_classified_conv_buyer(tenant_id,buyer_user_id,last_message_at),KEY idx_classified_conv_seller(tenant_id,seller_user_id,last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_messages_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,conversation_id BIGINT UNSIGNED NOT NULL,sender_user_id BIGINT UNSIGNED NOT NULL,body TEXT NOT NULL,created_at DATETIME NOT NULL,read_at DATETIME NULL,deleted_at DATETIME NULL,PRIMARY KEY(id),KEY idx_classified_messages(tenant_id,conversation_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_reports_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,ad_id BIGINT UNSIGNED NOT NULL,reporter_user_id BIGINT UNSIGNED NULL,reason VARCHAR(100) NOT NULL,note VARCHAR(1000) NULL,status VARCHAR(24) NOT NULL DEFAULT 'open',resolved_by BIGINT UNSIGNED NULL,resolved_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_classified_reports(tenant_id,status,created_at),KEY idx_classified_report_ad(tenant_id,ad_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_promotions_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,ad_id BIGINT UNSIGNED NOT NULL,user_id BIGINT UNSIGNED NOT NULL,promotion_type VARCHAR(24) NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'requested',requested_at DATETIME NOT NULL,approved_by BIGINT UNSIGNED NULL,approved_until DATETIME NULL,updated_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_classified_promotions(tenant_id,status,requested_at),KEY idx_classified_promo_ad(tenant_id,ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_saved_searches_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,name VARCHAR(120) NOT NULL,query_string VARCHAR(1500) NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_classified_saved_user(tenant_id,user_id,updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS classified_moderation_log_v1370 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,ad_id BIGINT UNSIGNED NOT NULL,admin_user_id BIGINT UNSIGNED NULL,action_key VARCHAR(50) NOT NULL,note VARCHAR(1000) NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_classified_mod_ad(tenant_id,ad_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO classified_categories_v1370(tenant_id,parent_id,slug,name,icon,description,sort_order,active,created_at,updated_at) VALUES
(0,NULL,'mobiles','Mobiles','📱','Phones, tablets and mobile accessories',10,1,NOW(),NOW()),
(0,NULL,'vehicles','Vehicles','🚗','Cars, motorcycles, bicycles and vehicle parts',20,1,NOW(),NOW()),
(0,NULL,'property','Property','🏠','Houses, plots, shops and rentals',30,1,NOW(),NOW()),
(0,NULL,'electronics','Electronics & Appliances','💻','Computers, TVs, appliances and gadgets',40,1,NOW(),NOW()),
(0,NULL,'home-living','Home & Living','🛋','Furniture, decor and household items',50,1,NOW(),NOW()),
(0,NULL,'fashion','Fashion','👕','Clothing, footwear and accessories',60,1,NOW(),NOW()),
(0,NULL,'books-education','Books & Education','📚','Books, study material and educational items',70,1,NOW(),NOW()),
(0,NULL,'jobs-services','Jobs & Services','🧰','Local jobs and professional services',80,1,NOW(),NOW()),
(0,NULL,'sports-hobbies','Sports & Hobbies','⚽','Sports, fitness, musical and hobby items',90,1,NOW(),NOW()),
(0,NULL,'kids','Kids & Baby','🧸','Toys, kids items and baby essentials',100,1,NOW(),NOW()),
(0,NULL,'agriculture','Agriculture & Tools','🌾','Farm equipment, tools and local agricultural items',110,1,NOW(),NOW()),
(0,NULL,'other','Other','▣','Other allowed local classifieds',999,1,NOW(),NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name),icon=VALUES(icon),description=VALUES(description),sort_order=VALUES(sort_order),active=1,updated_at=NOW();

INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'brand','Brand','text',NULL,0,1,10,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='mobiles'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'model','Model','text',NULL,0,1,20,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='mobiles'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'storage','Storage','select','["32 GB","64 GB","128 GB","256 GB","512 GB","1 TB"]',0,1,30,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='mobiles'
ON DUPLICATE KEY UPDATE label=VALUES(label),options_json=VALUES(options_json),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'vehicle_make','Make','text',NULL,0,1,10,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='vehicles'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'vehicle_model','Model','text',NULL,0,1,20,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='vehicles'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'model_year','Model Year','number',NULL,0,1,30,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='vehicles'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'mileage','Mileage (km)','number',NULL,0,1,40,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='vehicles'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'property_type','Property Type','select','["House","Plot","Shop","Office","Apartment","Land","Other"]',1,1,10,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='property'
ON DUPLICATE KEY UPDATE label=VALUES(label),options_json=VALUES(options_json),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'purpose','Purpose','select','["For Sale","For Rent"]',1,1,20,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='property'
ON DUPLICATE KEY UPDATE label=VALUES(label),options_json=VALUES(options_json),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'area_size','Area / Size','text',NULL,0,1,30,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='property'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'brand','Brand','text',NULL,0,1,10,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='electronics'
ON DUPLICATE KEY UPDATE label=VALUES(label),active=1,updated_at=NOW();
INSERT INTO classified_category_fields_v1370(category_id,field_key,label,field_type,options_json,required,filterable,sort_order,active,created_at,updated_at)
SELECT id,'job_type','Job Type','select','["Full-time","Part-time","Contract","Internship","Daily Wage","Service Offered"]',1,1,10,1,NOW(),NOW() FROM classified_categories_v1370 WHERE tenant_id=0 AND slug='jobs-services'
ON DUPLICATE KEY UPDATE label=VALUES(label),options_json=VALUES(options_json),active=1,updated_at=NOW();

INSERT INTO settings(setting_key,setting_value) VALUES
('classifieds_enabled','1'),('classifieds_auto_approve','0'),('classifieds_auto_approve_edits','0'),('classifieds_expiry_days','30'),('classifieds_daily_post_limit','10'),('classifieds_max_images','10'),('classifieds_max_image_mb','8'),('classifieds_per_page','24'),('classifieds_version','13.7.0')
ON DUPLICATE KEY UPDATE setting_value=IF(setting_key='classifieds_version','13.7.0',setting_value);

INSERT INTO access_features_v1300(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at) VALUES
('public.classifieds','Local Classifieds','Browse local buy/sell classifieds and listing details','Public','Marketplace','/classified*.php*',0,1,1,1,1,'core',NOW(),NOW()),
('customer.classifieds_sell','Classifieds Seller','Post, manage, favorite and message on local classifieds','Customer','Marketplace','/post-ad.php*',0,0,1,1,1,'core',NOW(),NOW()),
('admin.classifieds','Classifieds Marketplace Admin','Moderation, categories, reports and promotions','Staff','Marketplace','/admin/classifieds*.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=VALUES(admin_only),default_guest=VALUES(default_guest),default_customer=VALUES(default_customer),default_shopkeeper=VALUES(default_shopkeeper),active=1,updated_at=NOW();
INSERT IGNORE INTO access_role_features_v1300(tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.classifieds',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='tenant_admin' AND r.active=1;
