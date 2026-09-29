-- ShahkotPK v7.0.2 Header, Main Menu & Ticker Control Center
CREATE TABLE IF NOT EXISTS tenant_header_settings_v702 (
  tenant_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  header_style VARCHAR(40) NOT NULL DEFAULT 'premium',
  sticky TINYINT(1) NOT NULL DEFAULT 1,
  show_tagline TINYINT(1) NOT NULL DEFAULT 1,
  show_topbar TINYINT(1) NOT NULL DEFAULT 1,
  logo_path VARCHAR(700) NULL,
  mobile_logo_path VARCHAR(700) NULL,
  logo_width INT NOT NULL DEFAULT 148,
  menu_align VARCHAR(20) NOT NULL DEFAULT 'right',
  header_bg VARCHAR(24) NOT NULL DEFAULT '#ffffff',
  header_text VARCHAR(24) NOT NULL DEFAULT '#0f2e28',
  ticker_enabled TINYINT(1) NOT NULL DEFAULT 1,
  ticker_speed INT NOT NULL DEFAULT 38,
  ticker_label VARCHAR(80) NOT NULL DEFAULT 'CITY UPDATE',
  ticker_position VARCHAR(30) NOT NULL DEFAULT 'below_topbar',
  ticker_pause_hover TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_menu_items_v702 (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  item_key VARCHAR(100) NOT NULL,
  parent_key VARCHAR(100) NULL,
  label VARCHAR(120) NOT NULL,
  url VARCHAR(700) NOT NULL DEFAULT '#',
  icon VARCHAR(40) NULL,
  description VARCHAR(300) NULL,
  location VARCHAR(30) NOT NULL DEFAULT 'main',
  sort_order INT NOT NULL DEFAULT 100,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  visibility VARCHAR(20) NOT NULL DEFAULT 'all',
  target VARCHAR(20) NOT NULL DEFAULT '_self',
  is_system TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_hm702_menu_key (tenant_id,item_key),
  INDEX idx_hm702_menu_location (tenant_id,location,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_ticker_items_v702 (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  text VARCHAR(500) NOT NULL,
  link_url VARCHAR(700) NULL,
  tone VARCHAR(30) NOT NULL DEFAULT 'info',
  sort_order INT NOT NULL DEFAULT 100,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_hm702_ticker_active (tenant_id,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO tenant_header_settings_v702(tenant_id)
SELECT id FROM tenants;

INSERT IGNORE INTO tenant_menu_items_v702(tenant_id,item_key,parent_key,label,url,icon,description,location,sort_order,enabled,visibility,target,is_system)
SELECT t.id,m.item_key,m.parent_key,m.label,m.url,m.icon,m.description,m.location,m.sort_order,1,m.visibility,m.target,1
FROM tenants t JOIN (
 SELECT 'home' item_key,NULL parent_key,'Home' label,'/' url,'⌂' icon,'' description,'main' location,10 sort_order,'all' visibility,'_self' target
 UNION ALL SELECT 'shop',NULL,'Shop','/shop.php','◇','Marketplace & local products','main',20,'all','_self'
 UNION ALL SELECT 'property',NULL,'Property','/property.php','⌂','Buy, rent and sell property','main',30,'all','_self'
 UNION ALL SELECT 'doctor',NULL,'Doctor Online','/doctor-online.php','✚','Appointments & online consultation','main',40,'all','_self'
 UNION ALL SELECT 'blood',NULL,'Blood Bank','/blood-bank.php','♥','Donate and request blood','main',50,'all','_self'
 UNION ALL SELECT 'health',NULL,'Health','#','✦','Healthcare network','main',60,'all','_self'
 UNION ALL SELECT 'health-network','health','Health Network','/health-network.php','✦','Pharmacy, labs & ambulance','main',61,'all','_self'
 UNION ALL SELECT 'pharmacies','health','Pharmacies','/pharmacies.php','▣','Verified local pharmacies','main',62,'all','_self'
 UNION ALL SELECT 'labs','health','Diagnostic Labs','/labs.php','◫','Tests, bookings & reports','main',63,'all','_self'
 UNION ALL SELECT 'ambulance','health','Ambulance','/ambulance.php','●','Emergency transport desk','main',64,'all','_self'
 UNION ALL SELECT 'smart',NULL,'Smart City','#','◈','Citizen services & wallet','main',70,'all','_self'
 UNION ALL SELECT 'smart-city','smart','Citizen Super App','/smart-city.php','◈','One dashboard for city life','main',71,'all','_self'
 UNION ALL SELECT 'complaints','smart','City Services','/complaints.php','◫','Complaints & municipal requests','main',72,'all','_self'
 UNION ALL SELECT 'wallet','smart','Wallet & Loyalty','/wallet.php','₨','Credits, rewards & ledger','main',73,'logged_in','_self'
 UNION ALL SELECT 'assistant','smart','AI Assistant','/smart-assistant.php','AI','Smart search & recommendations','main',74,'all','_self'
 UNION ALL SELECT 'citylife',NULL,'City Life','#','◉','Education, transport & tourism','main',80,'all','_self'
 UNION ALL SELECT 'education','citylife','Education','/education.php','▤','Schools, academies & admissions','main',81,'all','_self'
 UNION ALL SELECT 'transport','citylife','Transport','/transport.php','↔','Trips, rides & delivery','main',82,'all','_self'
 UNION ALL SELECT 'tourism','citylife','Tourism','/tourism.php','✦','Hotels, restaurants & experiences','main',83,'all','_self'
 UNION ALL SELECT 'super-app','citylife','Full Super App','/app.php','◉','Installable ShahkotPK app','main',84,'all','_self'
 UNION ALL SELECT 'discover',NULL,'Discover','#','⌖','Explore Shahkot','main',90,'all','_self'
 UNION ALL SELECT 'city-guide','discover','City Guide','/city-guide.php','⌖','Places & useful city information','main',91,'all','_self'
 UNION ALL SELECT 'businesses','discover','Businesses','/businesses.php','▦','Local business directory','main',92,'all','_self'
 UNION ALL SELECT 'city-map','discover','City Map','/city-map.php','◎','Map-based local discovery','main',93,'all','_self'
 UNION ALL SELECT 'smart-search','discover','Smart Search','/smart-search.php','AI','Natural-language discovery','main',94,'all','_self'
 UNION ALL SELECT 'updates',NULL,'Updates','#','◆','News, events & offers','main',100,'all','_self'
 UNION ALL SELECT 'deals','updates','Deals & Offers','/deals.php','%','Local discounts and offers','main',101,'all','_self'
 UNION ALL SELECT 'events','updates','Events','/events.php','◆','What is happening in the city','main',102,'all','_self'
 UNION ALL SELECT 'news','updates','News','/news.php','▤','Local news portal','main',103,'all','_self'
 UNION ALL SELECT 'blog','updates','Blog','/blog.php','✎','Stories, guides & articles','main',104,'all','_self'
 UNION ALL SELECT 'local',NULL,'Local','#','☰','Food, services & jobs','main',110,'all','_self'
 UNION ALL SELECT 'food','local','Food','/food.php','☰','Restaurants & menus','main',111,'all','_self'
 UNION ALL SELECT 'services','local','Services','/services.php','⚙','Local service providers','main',112,'all','_self'
 UNION ALL SELECT 'classifieds','local','Classifieds','/classifieds.php','▣','Buy & sell locally','main',113,'all','_self'
 UNION ALL SELECT 'jobs','local','Jobs','/jobs.php','▥','Local jobs & opportunities','main',114,'all','_self'
 UNION ALL SELECT 'business',NULL,'Business','#','▦','Business tools','main',120,'all','_self'
 UNION ALL SELECT 'business-directory','business','Business Directory','/businesses.php','▦','Discover & compare businesses','main',121,'all','_self'
 UNION ALL SELECT 'plans','business','Plans','/pricing.php','★','Subscriptions & promotion plans','main',122,'all','_self'
 UNION ALL SELECT 'signup','business','List Your Business','/signup.php','＋','Create a business account','main',123,'guest','_self'
 UNION ALL SELECT 'account',NULL,'Login','@account','↗','Account / dashboard','main',200,'all','_self'
) m ON 1=1;

INSERT INTO tenant_ticker_items_v702(tenant_id,text,link_url,tone,sort_order,enabled)
SELECT id,CONCAT('Welcome to ',site_name,' — your complete city platform.'),'/', 'info',100,1 FROM tenants t
WHERE NOT EXISTS (SELECT 1 FROM tenant_ticker_items_v702 x WHERE x.tenant_id=t.id);

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','7.0.2'),
('header_menu_manager_version','702')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
