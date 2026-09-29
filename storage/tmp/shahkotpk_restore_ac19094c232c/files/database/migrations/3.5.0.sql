-- ShahkotPK v3.5.0 Commercial Growth Suite

CREATE TABLE IF NOT EXISTS business_reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  title VARCHAR(180) NULL,
  body TEXT NULL,
  status ENUM('pending','published','rejected') NOT NULL DEFAULT 'pending',
  owner_reply TEXT NULL,
  replied_at DATETIME NULL,
  moderated_by BIGINT UNSIGNED NULL,
  moderated_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review_business_user (business_id,user_id),
  INDEX idx_review_business_status (business_id,status,created_at),
  INDEX idx_review_status_rating (status,rating),
  CONSTRAINT fk_review_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_moderator FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_verifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  submitted_by BIGINT UNSIGNED NOT NULL,
  verification_type ENUM('identity','business','physical','phone') NOT NULL DEFAULT 'business',
  document_path VARCHAR(700) NULL,
  notes TEXT NULL,
  status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  fee_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_order_id BIGINT UNSIGNED NULL,
  verified_by BIGINT UNSIGNED NULL,
  verified_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_verification_business (business_id,status),
  INDEX idx_verification_status (status,created_at),
  CONSTRAINT fk_verification_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CONSTRAINT fk_verification_submitter FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_verification_payment FOREIGN KEY (payment_order_id) REFERENCES payment_orders(id) ON DELETE SET NULL,
  CONSTRAINT fk_verification_admin FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  service_name VARCHAR(180) NOT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  customer_name VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(40) NOT NULL,
  customer_email VARCHAR(190) NULL,
  notes VARCHAR(900) NULL,
  status ENUM('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_booking_business (business_id,status,booking_date),
  INDEX idx_booking_user (user_id,status),
  CONSTRAINT fk_booking_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_leads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  lead_type ENUM('inquiry','quote','call','whatsapp','booking','service') NOT NULL DEFAULT 'inquiry',
  customer_name VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(40) NULL,
  customer_email VARCHAR(190) NULL,
  message TEXT NULL,
  status ENUM('new','contacted','qualified','won','lost','spam') NOT NULL DEFAULT 'new',
  estimated_value DECIMAL(14,2) NOT NULL DEFAULT 0,
  source VARCHAR(120) NULL,
  assigned_to BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lead_business (business_id,status,created_at),
  INDEX idx_lead_assigned (assigned_to,status),
  CONSTRAINT fk_lead_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CONSTRAINT fk_lead_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_lead_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_coupons (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  code VARCHAR(80) NOT NULL UNIQUE,
  discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  discount_value DECIMAL(14,2) NOT NULL DEFAULT 0,
  min_spend DECIMAL(14,2) NOT NULL DEFAULT 0,
  max_redemptions INT NOT NULL DEFAULT 0,
  per_user_limit INT NOT NULL DEFAULT 1,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  status ENUM('draft','active','paused','expired') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_coupon_business_status (business_id,status),
  CONSTRAINT fk_coupon_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_redemptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  coupon_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  amount_saved DECIMAL(14,2) NOT NULL DEFAULT 0,
  redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_coupon_redemption_user (coupon_id,user_id),
  CONSTRAINT fk_coupon_redemption_coupon FOREIGN KEY (coupon_id) REFERENCES business_coupons(id) ON DELETE CASCADE,
  CONSTRAINT fk_coupon_redemption_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_coupon_redemption_order FOREIGN KEY (order_id) REFERENCES store_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loyalty_wallets (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  points BIGINT NOT NULL DEFAULT 0,
  credit_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_loyalty_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loyalty_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  points_delta BIGINT NOT NULL DEFAULT 0,
  credit_delta DECIMAL(14,2) NOT NULL DEFAULT 0,
  transaction_type VARCHAR(80) NOT NULL,
  reference_type VARCHAR(80) NULL,
  reference_id BIGINT UNSIGNED NULL,
  description VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_loyalty_user (user_id,created_at),
  CONSTRAINT fk_loyalty_tx_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS referrals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  referrer_user_id BIGINT UNSIGNED NOT NULL,
  referred_user_id BIGINT UNSIGNED NULL UNIQUE,
  referral_code VARCHAR(80) NOT NULL,
  status ENUM('clicked','registered','qualified','rewarded','cancelled') NOT NULL DEFAULT 'clicked',
  reward_points BIGINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  qualified_at DATETIME NULL,
  rewarded_at DATETIME NULL,
  INDEX idx_referral_referrer (referrer_user_id,status),
  INDEX idx_referral_code (referral_code),
  CONSTRAINT fk_referral_referrer FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_referral_referred FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_templates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  template_key VARCHAR(100) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  message_template TEXT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_queue (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  template_key VARCHAR(100) NULL,
  recipient VARCHAR(50) NOT NULL,
  message_text TEXT NOT NULL,
  status ENUM('queued','sent','failed','cancelled') NOT NULL DEFAULT 'queued',
  attempts INT NOT NULL DEFAULT 0,
  provider_response TEXT NULL,
  scheduled_at DATETIME NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_whatsapp_status (status,scheduled_at,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS analytics_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  session_hash VARCHAR(128) NULL,
  event_type VARCHAR(100) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NULL,
  meta_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_analytics_event (event_type,created_at),
  INDEX idx_analytics_business (business_id,event_type,created_at),
  INDEX idx_analytics_entity (entity_type,entity_id,created_at),
  CONSTRAINT fk_analytics_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_analytics_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emergency_services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city_id BIGINT UNSIGNED NOT NULL,
  service_type VARCHAR(100) NOT NULL,
  name VARCHAR(180) NOT NULL,
  phone VARCHAR(50) NULL,
  whatsapp VARCHAR(50) NULL,
  address VARCHAR(300) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  map_url VARCHAR(700) NULL,
  is_24_7 TINYINT(1) NOT NULL DEFAULT 0,
  priority INT NOT NULL DEFAULT 10,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_emergency_city (city_id,status,priority),
  CONSTRAINT fk_emergency_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restaurant_menus (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL DEFAULT 'Main Menu',
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_menu_business_title (business_id,title),
  CONSTRAINT fk_restaurant_menu_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restaurant_menu_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  category_name VARCHAR(120) NULL,
  name VARCHAR(180) NOT NULL,
  description VARCHAR(500) NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  image_url VARCHAR(700) NULL,
  available TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_menu_item_menu (menu_id,available,sort_order),
  CONSTRAINT fk_menu_item_menu FOREIGN KEY (menu_id) REFERENCES restaurant_menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  city_id BIGINT UNSIGNED NOT NULL,
  category VARCHAR(120) NOT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT NULL,
  budget DECIMAL(14,2) NOT NULL DEFAULT 0,
  address VARCHAR(350) NULL,
  phone VARCHAR(50) NULL,
  status ENUM('open','assigned','completed','cancelled') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_service_request (city_id,status,created_at),
  CONSTRAINT fk_service_request_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_service_request_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_quotes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id BIGINT UNSIGNED NOT NULL,
  business_id BIGINT UNSIGNED NOT NULL,
  seller_user_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  message VARCHAR(700) NULL,
  status ENUM('sent','accepted','rejected','withdrawn') NOT NULL DEFAULT 'sent',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_service_quote_business (request_id,business_id),
  CONSTRAINT fk_service_quote_request FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
  CONSTRAINT fk_service_quote_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  CONSTRAINT fk_service_quote_user FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classifieds (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  business_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NOT NULL,
  category VARCHAR(120) NOT NULL,
  listing_type ENUM('sell','buy','wanted','service') NOT NULL DEFAULT 'sell',
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  description TEXT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  image_url VARCHAR(700) NULL,
  phone VARCHAR(50) NULL,
  whatsapp VARCHAR(50) NULL,
  status ENUM('pending','published','rejected','sold','expired') NOT NULL DEFAULT 'pending',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  expires_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_classified_city (city_id,status,created_at),
  INDEX idx_classified_user (user_id,status),
  CONSTRAINT fk_classified_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_classified_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_classified_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plan_entitlements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id BIGINT UNSIGNED NOT NULL,
  entitlement_key VARCHAR(120) NOT NULL,
  entitlement_value VARCHAR(255) NOT NULL DEFAULT '1',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plan_entitlement (plan_id,entitlement_key),
  CONSTRAINT fk_plan_entitlement_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_staff (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_user_id BIGINT UNSIGNED NOT NULL,
  staff_user_id BIGINT UNSIGNED NOT NULL,
  permissions_json LONGTEXT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seller_staff (owner_user_id,staff_user_id),
  INDEX idx_seller_staff_user (staff_user_id,status),
  CONSTRAINT fk_seller_staff_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_seller_staff_user FOREIGN KEY (staff_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS moderation_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_user_id BIGINT UNSIGNED NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(180) NOT NULL,
  details TEXT NULL,
  status ENUM('open','reviewing','resolved','dismissed') NOT NULL DEFAULT 'open',
  resolution TEXT NULL,
  resolved_by BIGINT UNSIGNED NULL,
  resolved_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_moderation_status (status,entity_type,created_at),
  CONSTRAINT fk_moderation_reporter FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_moderation_resolver FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seo_meta (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  meta_title VARCHAR(320) NULL,
  meta_description VARCHAR(700) NULL,
  canonical_url VARCHAR(700) NULL,
  og_image VARCHAR(700) NULL,
  schema_json LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seo_entity (entity_type,entity_id),
  CONSTRAINT fk_seo_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS push_subscriptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  endpoint VARCHAR(1000) NOT NULL,
  p256dh VARCHAR(500) NULL,
  auth_key VARCHAR(500) NULL,
  user_agent VARCHAR(500) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_push_endpoint (endpoint(190)),
  CONSTRAINT fk_push_subscription_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS push_campaigns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  body VARCHAR(800) NOT NULL,
  action_url VARCHAR(700) NULL,
  target_type ENUM('all','users','shopkeepers','role') NOT NULL DEFAULT 'all',
  target_role VARCHAR(80) NULL,
  status ENUM('draft','queued','sent','cancelled') NOT NULL DEFAULT 'draft',
  scheduled_at DATETIME NULL,
  sent_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_push_campaign_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_managers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  permissions_json LONGTEXT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_city_manager (city_id,user_id),
  CONSTRAINT fk_city_manager_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
  CONSTRAINT fk_city_manager_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO whatsapp_templates(template_key,title,message_template,status) VALUES
('booking_created','Booking Created','Your booking with {{business}} for {{service}} on {{date}} at {{time}} has been received.',1),
('booking_confirmed','Booking Confirmed','Your booking with {{business}} has been confirmed.',1),
('lead_received','New Business Inquiry','New inquiry for {{business}} from {{customer}}.',1),
('order_status','Order Status','Your order {{order}} status is now {{status}}.',1),
('subscription_expiry','Subscription Reminder','Your ShahkotPK subscription expires on {{date}}.',1);

INSERT IGNORE INTO accounting_accounts(code,name,account_type,system_key,description,status) VALUES
('4030','Verification Revenue','revenue','verification_revenue','Business verification fee revenue.',1),
('4040','Service Marketplace Revenue','revenue','service_marketplace_revenue','Revenue from service marketplace.',1),
('4050','Classified Revenue','revenue','classified_revenue','Revenue from classified listings.',1),
('1200','Loyalty Credit Liability','liability','loyalty_credit_liability','Platform loyalty credit obligations.',1);

INSERT INTO settings(setting_key,setting_value) VALUES
('growth_suite_enabled','1'),
('reviews_enabled','1'),('reviews_require_moderation','1'),('loyalty_points_per_review','20'),
('verification_enabled','1'),('verification_fee','0'),
('bookings_enabled','1'),('leads_enabled','1'),('coupons_enabled','1'),
('loyalty_enabled','1'),('referrals_enabled','1'),('referral_reward_points','100'),
('whatsapp_automation_enabled','0'),('whatsapp_api_endpoint',''),('whatsapp_access_token',''),
('analytics_enabled','1'),('emergency_portal_enabled','1'),('restaurant_menu_enabled','1'),
('service_marketplace_enabled','1'),('classifieds_enabled','1'),('subscription_entitlements_enabled','1'),
('seller_staff_enabled','1'),('moderation_center_enabled','1'),('seo_automation_enabled','1'),
('pwa_enabled','1'),('push_notifications_enabled','1'),('multi_city_management_enabled','1'),
('commercial_reports_enabled','1'),('growth_home_enabled','1'),('growth_homepage_version','0'),
('commercial_growth_version','350')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

INSERT IGNORE INTO system_roles(name,slug,description,permissions_json,status) VALUES
('Growth Manager','growth-manager','Manage trust, CRM, loyalty, referrals, analytics and moderation.','["dashboard.view","reviews.manage","verification.manage","bookings.manage","leads.manage","loyalty.manage","referrals.manage","whatsapp.manage","analytics.manage","moderation.manage"]',1),
('Operations Manager','operations-manager','Manage emergency, restaurants, services, classifieds and city operations.','["dashboard.view","emergency.manage","restaurants.manage","services.manage","classifieds.manage","multi_city.manage"]',1);
