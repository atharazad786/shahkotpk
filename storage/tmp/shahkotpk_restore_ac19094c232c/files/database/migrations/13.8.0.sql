-- ShahkotPK v13.8.0 — Service Provider Pro Marketplace
CREATE TABLE IF NOT EXISTS service_categories_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,slug VARCHAR(120) NOT NULL,name VARCHAR(160) NOT NULL,icon VARCHAR(24) NULL,description VARCHAR(500) NULL,requires_admin_note TINYINT(1) NOT NULL DEFAULT 0,sort_order INT NOT NULL DEFAULT 100,active TINYINT(1) NOT NULL DEFAULT 1,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_cat(tenant_id,slug),KEY idx_svc_cat(tenant_id,active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_provider_profiles_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,display_name VARCHAR(180) NOT NULL,business_name VARCHAR(200) NULL,bio TEXT NULL,phone_public VARCHAR(50) NULL,area VARCHAR(180) NULL,address_text VARCHAR(500) NULL,latitude DECIMAL(10,7) NULL,longitude DECIMAL(10,7) NULL,service_radius_km INT NOT NULL DEFAULT 10,years_experience INT NOT NULL DEFAULT 0,status VARCHAR(24) NOT NULL DEFAULT 'pending',verification_status VARCHAR(24) NOT NULL DEFAULT 'unverified',verification_note VARCHAR(1000) NULL,public_profile TINYINT(1) NOT NULL DEFAULT 1,fast_service_enabled TINYINT(1) NOT NULL DEFAULT 0,featured_until DATETIME NULL,rating_avg DECIMAL(4,2) NOT NULL DEFAULT 0,rating_count INT NOT NULL DEFAULT 0,completed_orders INT NOT NULL DEFAULT 0,approved_by BIGINT UNSIGNED NULL,approved_at DATETIME NULL,verified_at DATETIME NULL,last_active_at DATETIME NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_provider_user(tenant_id,user_id),KEY idx_svc_provider_search(tenant_id,status,fast_service_enabled,rating_avg),KEY idx_svc_provider_area(tenant_id,area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_provider_categories_v1380 (
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,provider_id BIGINT UNSIGNED NOT NULL,category_id BIGINT UNSIGNED NOT NULL,created_at DATETIME NOT NULL,PRIMARY KEY(tenant_id,provider_id,category_id),KEY idx_svc_pc_cat(tenant_id,category_id,provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_offerings_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,provider_id BIGINT UNSIGNED NOT NULL,category_id BIGINT UNSIGNED NOT NULL,title VARCHAR(200) NOT NULL,slug VARCHAR(180) NOT NULL,description TEXT NULL,base_price DECIMAL(12,2) NOT NULL DEFAULT 0,price_unit VARCHAR(30) NOT NULL DEFAULT 'job',estimated_minutes INT NULL,fast_available TINYINT(1) NOT NULL DEFAULT 0,active TINYINT(1) NOT NULL DEFAULT 1,featured_until DATETIME NULL,sort_order INT NOT NULL DEFAULT 100,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_offering_slug(tenant_id,slug),KEY idx_svc_offerings(tenant_id,category_id,active,provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_provider_availability_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,provider_id BIGINT UNSIGNED NOT NULL,weekday TINYINT UNSIGNED NOT NULL,start_time TIME NOT NULL,end_time TIME NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_avail(tenant_id,provider_id,weekday,active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_customer_addresses_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,label VARCHAR(80) NOT NULL,address_text VARCHAR(600) NOT NULL,area VARCHAR(180) NULL,latitude DECIMAL(10,7) NULL,longitude DECIMAL(10,7) NULL,is_default TINYINT(1) NOT NULL DEFAULT 0,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_address(tenant_id,user_id,is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_requests_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,customer_user_id BIGINT UNSIGNED NOT NULL,category_id BIGINT UNSIGNED NOT NULL,preferred_provider_id BIGINT UNSIGNED NULL,title VARCHAR(220) NOT NULL,description TEXT NOT NULL,address_text VARCHAR(600) NOT NULL,area VARCHAR(180) NULL,latitude DECIMAL(10,7) NULL,longitude DECIMAL(10,7) NULL,preferred_date DATE NULL,preferred_time VARCHAR(60) NULL,urgency VARCHAR(24) NOT NULL DEFAULT 'normal',budget_min DECIMAL(12,2) NULL,budget_max DECIMAL(12,2) NULL,status VARCHAR(24) NOT NULL DEFAULT 'open',quote_count INT NOT NULL DEFAULT 0,selected_quote_id BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_requests(tenant_id,status,category_id,created_at),KEY idx_svc_customer_req(tenant_id,customer_user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_quotes_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,request_id BIGINT UNSIGNED NOT NULL,provider_id BIGINT UNSIGNED NOT NULL,amount DECIMAL(12,2) NOT NULL,visit_fee DECIMAL(12,2) NOT NULL DEFAULT 0,message VARCHAR(1500) NULL,arrival_minutes INT NULL,valid_until DATETIME NULL,status VARCHAR(24) NOT NULL DEFAULT 'submitted',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_quote(tenant_id,request_id,provider_id),KEY idx_svc_quotes(tenant_id,provider_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_orders_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,customer_user_id BIGINT UNSIGNED NOT NULL,provider_id BIGINT UNSIGNED NOT NULL,request_id BIGINT UNSIGNED NULL,quote_id BIGINT UNSIGNED NULL,offering_id BIGINT UNSIGNED NULL,order_code VARCHAR(40) NOT NULL,service_title VARCHAR(220) NOT NULL,address_text VARCHAR(600) NOT NULL,area VARCHAR(180) NULL,latitude DECIMAL(10,7) NULL,longitude DECIMAL(10,7) NULL,scheduled_at DATETIME NULL,subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,visit_fee DECIMAL(12,2) NOT NULL DEFAULT 0,total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,payment_mode VARCHAR(24) NOT NULL DEFAULT 'cash',payment_status VARCHAR(24) NOT NULL DEFAULT 'unpaid',status VARCHAR(24) NOT NULL DEFAULT 'booked',customer_note VARCHAR(1200) NULL,provider_note VARCHAR(1200) NULL,completed_at DATETIME NULL,cancelled_at DATETIME NULL,cancel_reason VARCHAR(800) NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_order_code(tenant_id,order_code),KEY idx_svc_orders_customer(tenant_id,customer_user_id,status,created_at),KEY idx_svc_orders_provider(tenant_id,provider_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_order_events_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,order_id BIGINT UNSIGNED NOT NULL,actor_user_id BIGINT UNSIGNED NULL,status_code VARCHAR(40) NOT NULL,note VARCHAR(1200) NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_order_events(tenant_id,order_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_messages_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,order_id BIGINT UNSIGNED NOT NULL,sender_user_id BIGINT UNSIGNED NOT NULL,message VARCHAR(2000) NOT NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_messages(tenant_id,order_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_reviews_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,order_id BIGINT UNSIGNED NOT NULL,provider_id BIGINT UNSIGNED NOT NULL,customer_user_id BIGINT UNSIGNED NOT NULL,rating TINYINT UNSIGNED NOT NULL,review_text VARCHAR(1800) NULL,status VARCHAR(24) NOT NULL DEFAULT 'published',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_review_order(tenant_id,order_id),KEY idx_svc_reviews(tenant_id,provider_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_packages_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,package_key VARCHAR(80) NOT NULL,name VARCHAR(140) NOT NULL,description VARCHAR(800) NULL,price DECIMAL(12,2) NOT NULL DEFAULT 0,duration_days INT NOT NULL DEFAULT 30,entitlements_json TEXT NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,sort_order INT NOT NULL DEFAULT 100,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_svc_package(tenant_id,package_key),KEY idx_svc_packages(tenant_id,active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_package_orders_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,provider_id BIGINT UNSIGNED NOT NULL,package_id BIGINT UNSIGNED NOT NULL,amount_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0,payment_reference VARCHAR(180) NULL,payment_note VARCHAR(1000) NULL,status VARCHAR(24) NOT NULL DEFAULT 'pending',admin_note VARCHAR(1000) NULL,reviewed_by BIGINT UNSIGNED NULL,reviewed_at DATETIME NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_pkgorders(tenant_id,status,created_at),KEY idx_svc_pkgprovider(tenant_id,provider_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_subscriptions_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,provider_id BIGINT UNSIGNED NOT NULL,package_id BIGINT UNSIGNED NOT NULL,package_order_id BIGINT UNSIGNED NULL,status VARCHAR(24) NOT NULL DEFAULT 'active',starts_at DATETIME NOT NULL,ends_at DATETIME NULL,assigned_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_subs(tenant_id,provider_id,status,ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_promotions_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,provider_id BIGINT UNSIGNED NOT NULL,offering_id BIGINT UNSIGNED NULL,promotion_type VARCHAR(40) NOT NULL,amount_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0,payment_reference VARCHAR(180) NULL,status VARCHAR(24) NOT NULL DEFAULT 'pending',starts_at DATETIME NULL,ends_at DATETIME NULL,admin_note VARCHAR(1000) NULL,reviewed_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_promotions(tenant_id,status,promotion_type,created_at),KEY idx_svc_promoprovider(tenant_id,provider_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service_moderation_log_v1380 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,entity_type VARCHAR(40) NOT NULL,entity_id BIGINT UNSIGNED NOT NULL,actor_user_id BIGINT UNSIGNED NULL,action_key VARCHAR(60) NOT NULL,note VARCHAR(1200) NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_svc_modlog(tenant_id,entity_type,entity_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO service_categories_v1380(tenant_id,slug,name,icon,description,sort_order,active,created_at,updated_at) VALUES
(0,'electrician','Electrician','⚡','Home wiring, switches, fans, lights and electrical repair',10,1,NOW(),NOW()),
(0,'plumber','Plumber','🔧','Leaks, taps, washrooms, water lines and plumbing repair',20,1,NOW(),NOW()),
(0,'ac-refrigeration','AC & Refrigeration','❄','AC, refrigerator and cooling appliance service',30,1,NOW(),NOW()),
(0,'tailor','Tailor & Stitching','🧵','Clothing alteration, stitching and tailoring services',40,1,NOW(),NOW()),
(0,'carpenter','Carpenter','🪚','Furniture, doors, cabinets and wood repair',50,1,NOW(),NOW()),
(0,'painter','Painter','🎨','Home, shop and office painting',60,1,NOW(),NOW()),
(0,'cleaning','Cleaning Service','🧹','Home, office and deep-cleaning assistance',70,1,NOW(),NOW()),
(0,'computer-mobile','Computer & Mobile Repair','💻','Computer, laptop, network and mobile-device repair',80,1,NOW(),NOW()),
(0,'appliance-repair','Home Appliance Repair','🛠','Washing machine, iron, fan and household appliance repair',90,1,NOW(),NOW()),
(0,'gas-appliance','Gas Appliance Technician','🔥','Gas stove, geyser and appliance inspection/repair by approved technicians',100,1,NOW(),NOW()),
(0,'solar-ups','Solar & UPS Technician','☀','Solar, inverter, UPS and battery-system service',110,1,NOW(),NOW()),
(0,'cctv-security','CCTV & Security Technician','📹','CCTV installation, maintenance and network setup',120,1,NOW(),NOW()),
(0,'auto-mechanic','Auto & Bike Mechanic','🏍','Car and motorcycle maintenance and repair',130,1,NOW(),NOW()),
(0,'beauty-grooming','Beauty & Grooming','✂','At-home grooming and beauty appointments where offered',140,1,NOW(),NOW()),
(0,'professional-help','Professional Help','▣','Other approved local professional services',900,1,NOW(),NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name),icon=VALUES(icon),description=VALUES(description),active=1,updated_at=NOW();

INSERT INTO settings(setting_key,setting_value) VALUES
('services_enabled','1'),('services_auto_approve_providers','0'),('services_require_provider_approval','1'),('services_default_radius_km','10'),('services_fast_label','Fast Service'),('services_max_open_requests','10'),('services_promo_featured_provider_price','0'),('services_promo_priority_search_price','0'),('services_promo_featured_service_price','0'),('services_version','13.8.0'),('installed_app_version','13.8.0'),('runtime_cache_contract_version','13.8.0'),('public_data_cache_version','13.8.0')
ON DUPLICATE KEY UPDATE setting_value=IF(setting_key IN('services_version','installed_app_version','runtime_cache_contract_version','public_data_cache_version'),'13.8.0',setting_value);

INSERT INTO access_features_v1300(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at) VALUES
('public.service_marketplace','Local Service Marketplace','Browse approved local service providers and service offerings','Public','Services','/services.php*',0,1,1,1,1,'core',NOW(),NOW()),
('customer.service_requests','Service Requests & Bookings','Post service requests, compare quotes and manage bookings','Customer','Services','/request-service.php*',0,0,1,1,1,'core',NOW(),NOW()),
('provider.service_marketplace','Service Provider Portal','Approved provider dashboard, quotes, jobs, services and packages','Customer','Services','/provider/*.php*',0,0,0,0,1,'core',NOW(),NOW()),
('admin.service_marketplace','Service Marketplace Admin','Provider approval, orders, packages, promotions and service controls','Staff','Services','/admin/service*.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=VALUES(admin_only),default_guest=VALUES(default_guest),default_customer=VALUES(default_customer),default_shopkeeper=VALUES(default_shopkeeper),active=1,updated_at=NOW();
INSERT IGNORE INTO access_role_features_v1300(tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.service_marketplace',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='tenant_admin' AND r.active=1;
INSERT IGNORE INTO access_roles_v1300(tenant_id,role_key,name,description,is_system,active,sort_order,created_at,updated_at)
SELECT DISTINCT tenant_id,'service_provider','Service Provider','Approved local service professional with provider portal access',1,1,75,NOW(),NOW() FROM access_roles_v1300;
INSERT IGNORE INTO access_role_features_v1300(tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,'service_provider','provider.service_marketplace',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='service_provider';

-- Provider packages: Free is immediately usable; paid templates remain inactive until admin sets real pricing/terms.
INSERT INTO service_packages_v1380(tenant_id,package_key,name,description,price,duration_days,entitlements_json,active,sort_order,created_at,updated_at)
SELECT t.tenant_id,'free','Starter','For newly approved service providers',0,30,'{"service_limit":3,"monthly_quote_limit":20,"fast_service":0,"featured_slots":0,"priority_weight":0}',1,10,NOW(),NOW() FROM (SELECT 1 tenant_id UNION SELECT DISTINCT tenant_id FROM access_roles_v1300 WHERE tenant_id>0) t
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),entitlements_json=VALUES(entitlements_json),active=1,updated_at=NOW();
INSERT INTO service_packages_v1380(tenant_id,package_key,name,description,price,duration_days,entitlements_json,active,sort_order,created_at,updated_at)
SELECT t.tenant_id,'pro','Pro Provider','Admin-configurable paid package with Fast Service and stronger marketplace reach',0,30,'{"service_limit":15,"monthly_quote_limit":150,"fast_service":1,"featured_slots":2,"priority_weight":25}',0,20,NOW(),NOW() FROM (SELECT 1 tenant_id UNION SELECT DISTINCT tenant_id FROM access_roles_v1300 WHERE tenant_id>0) t
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),entitlements_json=VALUES(entitlements_json),updated_at=NOW();
INSERT INTO service_packages_v1380(tenant_id,package_key,name,description,price,duration_days,entitlements_json,active,sort_order,created_at,updated_at)
SELECT t.tenant_id,'business','Business Plus','Admin-configurable premium package for established multi-service providers',0,30,'{"service_limit":40,"monthly_quote_limit":500,"fast_service":1,"featured_slots":5,"priority_weight":50}',0,30,NOW(),NOW() FROM (SELECT 1 tenant_id UNION SELECT DISTINCT tenant_id FROM access_roles_v1300 WHERE tenant_id>0) t
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),entitlements_json=VALUES(entitlements_json),updated_at=NOW();
