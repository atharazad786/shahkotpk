-- ShahkotPK v2.9.0 Complete City Guide Portal

CREATE TABLE IF NOT EXISTS city_portal_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  content_type ENUM('guide','deal','event','job','property') NOT NULL,
  city_id BIGINT UNSIGNED NOT NULL,
  business_id BIGINT UNSIGNED NULL,
  category VARCHAR(100) NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  summary VARCHAR(500) NULL,
  description TEXT NULL,
  image_url VARCHAR(600) NULL,
  address VARCHAR(300) NULL,
  phone VARCHAR(40) NULL,
  whatsapp VARCHAR(40) NULL,
  website_url VARCHAR(600) NULL,
  map_url VARCHAR(600) NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  secondary_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published','expired') NOT NULL DEFAULT 'published',
  sort_order INT NOT NULL DEFAULT 10,
  meta_json LONGTEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_city_portal_type (content_type,status,featured),
  INDEX idx_city_portal_city (city_id,content_type,status),
  INDEX idx_city_portal_business (business_id),
  INDEX idx_city_portal_dates (starts_at,ends_at),
  CONSTRAINT fk_city_portal_city FOREIGN KEY (city_id) REFERENCES cities(id),
  CONSTRAINT fk_city_portal_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_city_portal_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_hours (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  weekday TINYINT UNSIGNED NOT NULL,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  open_time TIME NULL,
  close_time TIME NULL,
  UNIQUE KEY uq_business_weekday (business_id,weekday),
  INDEX idx_business_hours_business (business_id),
  CONSTRAINT fk_business_hours_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('city_portal_enabled','1'),
('city_weather_text','Weather updates available from Admin'),
('city_emergency_text','Emergency: Rescue 1122'),
('homepage_category_explorer_enabled','1'),
('homepage_deals_enabled','1'),
('homepage_city_guide_enabled','1'),
('homepage_events_enabled','1'),
('homepage_jobs_enabled','1'),
('homepage_property_enabled','1'),
('homepage_new_businesses_enabled','1'),
('homepage_nearby_enabled','1'),
('homepage_restaurants_enabled','1'),
('homepage_sponsored_spotlight_enabled','1'),
('homepage_advertise_cta_enabled','1'),
('homepage_deals_limit','6'),
('homepage_events_limit','4'),
('homepage_jobs_limit','5'),
('homepage_property_limit','6'),
('homepage_guide_limit','6'),
('homepage_new_business_limit','6'),
('homepage_nearby_limit','8'),
('homepage_restaurant_limit','6'),
('city_portal_homepage_version','0')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

UPDATE settings SET setting_value='city-guide-pro' WHERE setting_key='landing_theme_slug';
UPDATE cms_pages SET theme_slug='city-guide-pro' WHERE is_home=1;
