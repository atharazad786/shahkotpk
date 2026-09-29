-- ShahkotPK / CityHub Database Schema
-- Version: 1.0.0
-- Charset: utf8mb4
-- This SQL matches the v3 cPanel Repair Installer.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) UNIQUE NULL,
  phone VARCHAR(30) UNIQUE NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','shopkeeper','admin') NOT NULL DEFAULT 'customer',
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(150) UNIQUE NOT NULL,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(150) UNIQUE NOT NULL,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS businesses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id BIGINT UNSIGNED NOT NULL,
  city_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) UNIQUE NOT NULL,
  description TEXT NULL,
  phone VARCHAR(30) NULL,
  whatsapp VARCHAR(30) NULL,
  address VARCHAR(255) NULL,
  image VARCHAR(500) NULL,
  verification_status ENUM('pending','verified','rejected','suspended') DEFAULT 'pending',
  is_featured TINYINT(1) DEFAULT 0,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_business_city (city_id),
  INDEX idx_business_category (category_id),
  INDEX idx_business_status (status, verification_status),
  CONSTRAINT fk_business_owner FOREIGN KEY (owner_id) REFERENCES users(id),
  CONSTRAINT fk_business_city FOREIGN KEY (city_id) REFERENCES cities(id),
  CONSTRAINT fk_business_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_plans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) UNIQUE NOT NULL,
  price DECIMAL(12,2) DEFAULT 0,
  billing_period ENUM('monthly','yearly','custom') DEFAULT 'yearly',
  features_json JSON NULL,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('pending','active','expired','cancelled','suspended') DEFAULT 'pending',
  amount DECIMAL(12,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_subscription_business (business_id),
  INDEX idx_subscription_plan (plan_id),
  INDEX idx_subscription_status (status),
  CONSTRAINT fk_subscription_business FOREIGN KEY (business_id) REFERENCES businesses(id),
  CONSTRAINT fk_subscription_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS advertisements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  placement VARCHAR(80) NOT NULL,
  image_url VARCHAR(500) NULL,
  target_url VARCHAR(500) NULL,
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  status ENUM('pending','scheduled','active','paused','completed','rejected') DEFAULT 'pending',
  budget DECIMAL(12,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ad_business (business_id),
  INDEX idx_ad_status (status),
  CONSTRAINT fk_ad_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token VARCHAR(120) UNIQUE NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_prt_user (user_id),
  INDEX idx_prt_token (token),
  CONSTRAINT fk_prt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




CREATE TABLE IF NOT EXISTS favorite_businesses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  business_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_favorite_user_business (user_id,business_id),
  INDEX idx_favorite_business (business_id),
  CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_favorite_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) UNIQUE NOT NULL,
  setting_value TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_updates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  version VARCHAR(50) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  status ENUM('success','failed') NOT NULL,
  backup_path VARCHAR(500) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default city
INSERT IGNORE INTO cities (name, slug)
VALUES ('Shahkot', 'shahkot');

-- Default categories
INSERT IGNORE INTO categories (name, slug) VALUES
('Restaurants','restaurants'),
('Mobile Shops','mobile-shops'),
('Electronics','electronics'),
('Clothing','clothing'),
('Services','services');

-- Default subscription plans
INSERT IGNORE INTO subscription_plans (name, slug, price, billing_period) VALUES
('Free','free',0,'yearly'),
('Business','business',3500,'yearly'),
('Premium','premium',7500,'yearly');

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name','ShahkotPK'),
('default_city','Shahkot'),
('timezone','Asia/Karachi'),
('charset','utf8mb4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- NOTE:
-- The admin account is created/updated by install.php because its password
-- is stored using PHP password_hash(). This avoids placing an unsafe plain
-- password or incompatible static hash in the SQL file.

SET FOREIGN_KEY_CHECKS=1;


-- ShahkotPK v1.4.0 default platform settings
INSERT INTO settings(setting_key,setting_value) VALUES
('site_name','ShahkotPK'),
('site_tagline','Your Digital City Guide'),
('default_city','Shahkot'),
('timezone','Asia/Karachi'),
('language','en'),
('currency','PKR'),
('support_phone',''),
('support_email',''),
('support_whatsapp',''),
('business_address',''),
('logo_url',''),
('favicon_url',''),
('brand_primary_color','#176b46'),
('brand_secondary_color','#0f3d29'),
('seo_title','ShahkotPK — Shahkot Business Directory & City Guide'),
('seo_description','Discover Shahkot businesses, services, places, offers and useful city information.'),
('seo_keywords','Shahkot, Shahkot businesses, Shahkot directory, Shahkot city guide'),
('og_image',''),
('robots_index','1'),
('login_enabled','1'),
('registration_enabled','1'),
('customer_signup_enabled','1'),
('shopkeeper_signup_enabled','1'),
('email_required','0'),
('phone_required','0'),
('business_registration_enabled','1'),
('directory_enabled','1'),
('search_enabled','1'),
('featured_businesses_enabled','1'),
('verified_badge_enabled','1'),
('subscriptions_enabled','1'),
('free_plan_enabled','1'),
('paid_plans_enabled','1'),
('advertisements_enabled','1'),
('sponsored_listings_enabled','1'),
('homepage_search_enabled','1'),
('homepage_directory_enabled','1'),
('homepage_featured_enabled','1'),
('homepage_gallery_enabled','1'),
('homepage_city_info_enabled','1'),
('image_uploads_enabled','1'),
('max_image_upload_mb','5'),
('allowed_image_types','jpg,jpeg,png,webp,gif'),
('update_system_enabled','1'),
('maintenance_mode','0'),
('debug_mode','0'),
('csrf_protection_enabled','1'),
('session_regenerate_on_login','1')
ON DUPLICATE KEY UPDATE setting_value=setting_value;


-- ShahkotPK v1.6.0 homepage experience settings
INSERT INTO settings(setting_key,setting_value) VALUES
('homepage_slider_enabled','1'),
('homepage_slider_interval_ms','5500'),
('homepage_animations_enabled','1'),
('mobile_menu_enabled','1'),
('mobile_sticky_cta_enabled','1')
ON DUPLICATE KEY UPDATE setting_value=setting_value;


-- ShahkotPK v1.8.0 SMTP and recovery defaults
INSERT INTO settings(setting_key,setting_value) VALUES
('forgot_password_enabled','1'),
('password_reset_expiry_minutes','60'),
('smtp_enabled','0'),
('smtp_host',''),
('smtp_port','587'),
('smtp_encryption','tls'),
('smtp_username',''),
('smtp_password',''),
('smtp_from_email',''),
('smtp_from_name','ShahkotPK'),
('smtp_timeout','15')
ON DUPLICATE KEY UPDATE setting_value=setting_value;


-- ShahkotPK v2.0.0 tables are installed through automatic migrations in database/migrations/2.0.0.sql.
