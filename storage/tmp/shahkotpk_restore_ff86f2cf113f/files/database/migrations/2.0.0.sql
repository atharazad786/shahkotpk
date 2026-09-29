-- ShahkotPK v2.0.0 Business Platform
-- Automatically executed by System Updater.

CREATE TABLE IF NOT EXISTS city_details (
  city_id BIGINT UNSIGNED PRIMARY KEY,
  province VARCHAR(120) NULL,
  district VARCHAR(120) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  map_url VARCHAR(600) NULL,
  timezone VARCHAR(80) DEFAULT 'Asia/Karachi',
  description TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_city_details_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_details (
  business_id BIGINT UNSIGNED PRIMARY KEY,
  tagline VARCHAR(220) NULL,
  email VARCHAR(190) NULL,
  website VARCHAR(500) NULL,
  opening_hours TEXT NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  map_url VARCHAR(600) NULL,
  facebook_url VARCHAR(500) NULL,
  instagram_url VARCHAR(500) NULL,
  tiktok_url VARCHAR(500) NULL,
  keywords TEXT NULL,
  price_range VARCHAR(80) NULL,
  registration_no VARCHAR(120) NULL,
  approval_notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_business_details_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) UNIQUE NOT NULL,
  description VARCHAR(500) NULL,
  permissions_json LONGTEXT NULL,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  approval_status ENUM('pending','approved','rejected') DEFAULT 'approved',
  approval_source ENUM('auto','manual','package','payment') DEFAULT 'auto',
  custom_role_id BIGINT UNSIGNED NULL,
  package_plan_id BIGINT UNSIGNED NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_profile_approval (approval_status),
  CONSTRAINT fk_user_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_profile_role FOREIGN KEY (custom_role_id) REFERENCES system_roles(id) ON DELETE SET NULL,
  CONSTRAINT fk_user_profile_plan FOREIGN KEY (package_plan_id) REFERENCES subscription_plans(id) ON DELETE SET NULL,
  CONSTRAINT fk_user_profile_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_plan_details (
  plan_id BIGINT UNSIGNED PRIMARY KEY,
  duration_days INT UNSIGNED DEFAULT 365,
  business_limit INT UNSIGNED DEFAULT 1,
  featured_limit INT UNSIGNED DEFAULT 0,
  ad_credit DECIMAL(12,2) DEFAULT 0,
  description TEXT NULL,
  features_text TEXT NULL,
  approval_mode ENUM('auto','manual','payment') DEFAULT 'payment',
  recommended TINYINT(1) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_plan_details_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS advertisement_details (
  advertisement_id BIGINT UNSIGNED PRIMARY KEY,
  pricing_model ENUM('flat','cpc','cpm') DEFAULT 'flat',
  rate DECIMAL(12,2) DEFAULT 0,
  impressions BIGINT UNSIGNED DEFAULT 0,
  clicks BIGINT UNSIGNED DEFAULT 0,
  target_city_id BIGINT UNSIGNED NULL,
  target_category_id BIGINT UNSIGNED NULL,
  charge_amount DECIMAL(12,2) DEFAULT 0,
  notes TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ad_details_ad FOREIGN KEY (advertisement_id) REFERENCES advertisements(id) ON DELETE CASCADE,
  CONSTRAINT fk_ad_details_city FOREIGN KEY (target_city_id) REFERENCES cities(id) ON DELETE SET NULL,
  CONSTRAINT fk_ad_details_category FOREIGN KEY (target_category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS information_tickers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NULL,
  message VARCHAR(1000) NOT NULL,
  link_url VARCHAR(600) NULL,
  scope ENUM('public','admin','both') DEFAULT 'both',
  style ENUM('info','success','warning','danger') DEFAULT 'info',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  status TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ticker_status_scope (status,scope)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_gateways (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) UNIQUE NOT NULL,
  name VARCHAR(120) NOT NULL,
  gateway_type ENUM('manual','payfast') DEFAULT 'manual',
  enabled TINYINT(1) DEFAULT 0,
  instructions TEXT NULL,
  account_title VARCHAR(180) NULL,
  account_number VARCHAR(180) NULL,
  iban VARCHAR(180) NULL,
  merchant_id VARCHAR(255) NULL,
  secured_key VARCHAR(500) NULL,
  merchant_name VARCHAR(255) NULL,
  token_url VARCHAR(700) NULL,
  checkout_url VARCHAR(700) NULL,
  mode ENUM('sandbox','live') DEFAULT 'sandbox',
  sort_order INT DEFAULT 10,
  config_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  business_id BIGINT UNSIGNED NULL,
  plan_id BIGINT UNSIGNED NULL,
  advertisement_id BIGINT UNSIGNED NULL,
  purpose ENUM('subscription','user_package','advertisement','other') NOT NULL DEFAULT 'other',
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) DEFAULT 'PKR',
  status ENUM('pending','awaiting_payment','paid','failed','cancelled','refunded') DEFAULT 'pending',
  approval_effect ENUM('none','approve_user','activate_subscription') DEFAULT 'none',
  reference VARCHAR(80) UNIQUE NOT NULL,
  metadata_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_payment_order_user (user_id),
  INDEX idx_payment_order_status (status),
  CONSTRAINT fk_payment_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_payment_order_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_payment_order_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE SET NULL,
  CONSTRAINT fk_payment_order_ad FOREIGN KEY (advertisement_id) REFERENCES advertisements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  gateway_code VARCHAR(80) NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('initiated','pending','review','paid','failed','refunded') DEFAULT 'initiated',
  provider_reference VARCHAR(255) NULL,
  customer_reference VARCHAR(255) NULL,
  proof_url VARCHAR(600) NULL,
  payload_json LONGTEXT NULL,
  paid_at DATETIME NULL,
  approved_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_payment_tx_order (order_id),
  INDEX idx_payment_tx_status (status),
  CONSTRAINT fk_payment_tx_order FOREIGN KEY (order_id) REFERENCES payment_orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_payment_tx_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO user_profiles(user_id,approval_status,approval_source,approved_at)
SELECT id,'approved','auto',NOW() FROM users;

INSERT IGNORE INTO system_roles(name,slug,description,permissions_json,status) VALUES
('Content Manager','content-manager','Manage homepage, banners and content.','["dashboard.view","homepage.manage","banners.manage","ticker.manage"]',1),
('Business Manager','business-manager','Manage businesses, cities and categories.','["dashboard.view","businesses.manage","cities.manage","categories.manage"]',1),
('Finance Manager','finance-manager','Manage plans, advertisements and payments.','["dashboard.view","subscriptions.manage","ads.manage","payments.manage"]',1),
('User Manager','user-manager','Manage users and approval workflow.','["dashboard.view","users.manage","roles.manage"]',1);

INSERT IGNORE INTO payment_gateways(code,name,gateway_type,enabled,instructions,mode,sort_order) VALUES
('raast','Raast / Bank Transfer','manual',0,'Pay through your configured Raast ID or bank account, then submit the transaction reference.','live',10),
('jazzcash','JazzCash','manual',0,'Transfer payment to the configured JazzCash business/mobile account and submit the transaction reference.','live',20),
('easypaisa','Easypaisa','manual',0,'Transfer payment to the configured Easypaisa merchant/mobile account and submit the transaction reference.','live',30),
('bank_transfer','Bank Transfer','manual',0,'Transfer payment to the configured bank account / IBAN and submit proof or transaction reference.','live',40),
('payfast','PayFast Online Checkout','payfast',0,'Online checkout through PayFast. Merchant credentials are required.','sandbox',50);

UPDATE payment_gateways
SET token_url='https://ipguat.apps.net.pk/Ecommerce/api/Transaction/GetAccessToken',
    checkout_url='https://ipguat.apps.net.pk/Ecommerce/api/Transaction/PostTransaction'
WHERE code='payfast' AND (token_url IS NULL OR token_url='');

INSERT IGNORE INTO information_tickers(title,message,scope,style,status,sort_order)
VALUES ('Welcome','Welcome to ShahkotPK — your digital city guide and business directory.','both','info',1,10);
