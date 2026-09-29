-- ShahkotPK v5.6.0 - Property Marketplace Pro
-- Non-destructive: extends legacy city_portal_items property records with isolated v5.6 metadata, leads and analytics.

CREATE TABLE IF NOT EXISTS property_projects_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  developer_user_id BIGINT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  project_type VARCHAR(80) NULL,
  description TEXT NULL,
  address VARCHAR(500) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  image_url VARCHAR(500) NULL,
  gallery_json LONGTEXT NULL,
  amenities_json LONGTEXT NULL,
  approval_status VARCHAR(40) NOT NULL DEFAULT 'approved',
  status VARCHAR(24) NOT NULL DEFAULT 'active',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_pr560_project_slug(slug), KEY idx_pr560_project_scope(tenant_id,city_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_listing_meta_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  portal_item_id BIGINT UNSIGNED NOT NULL,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  owner_user_id BIGINT UNSIGNED NULL,
  agent_user_id BIGINT UNSIGNED NULL,
  project_id BIGINT UNSIGNED NULL,
  purpose VARCHAR(20) NOT NULL DEFAULT 'sale',
  property_type VARCHAR(80) NOT NULL DEFAULT 'Property',
  subtype VARCHAR(100) NULL,
  condition_status VARCHAR(50) NULL,
  bedrooms INT NOT NULL DEFAULT 0,
  bathrooms INT NOT NULL DEFAULT 0,
  area_value DECIMAL(14,2) NOT NULL DEFAULT 0,
  area_unit VARCHAR(30) NOT NULL DEFAULT 'Marla',
  covered_area DECIMAL(14,2) NOT NULL DEFAULT 0,
  covered_area_unit VARCHAR(30) NULL,
  price_value DECIMAL(16,2) NOT NULL DEFAULT 0,
  price_period VARCHAR(30) NULL,
  price_on_call TINYINT(1) NOT NULL DEFAULT 0,
  price_negotiable TINYINT(1) NOT NULL DEFAULT 0,
  floor_no INT NOT NULL DEFAULT 0,
  total_floors INT NOT NULL DEFAULT 0,
  year_built INT NOT NULL DEFAULT 0,
  parking_spaces INT NOT NULL DEFAULT 0,
  furnishing VARCHAR(40) NULL,
  possession_status VARCHAR(50) NULL,
  possession_date DATE NULL,
  installment_available TINYINT(1) NOT NULL DEFAULT 0,
  down_payment DECIMAL(16,2) NOT NULL DEFAULT 0,
  monthly_installment DECIMAL(16,2) NOT NULL DEFAULT 0,
  features_json LONGTEXT NULL,
  utilities_json LONGTEXT NULL,
  gallery_json LONGTEXT NULL,
  video_url VARCHAR(500) NULL,
  virtual_tour_url VARCHAR(500) NULL,
  floor_plan_url VARCHAR(500) NULL,
  reference_no VARCHAR(100) NULL,
  listed_by VARCHAR(30) NOT NULL DEFAULT 'owner',
  verified TINYINT(1) NOT NULL DEFAULT 0,
  approval_status VARCHAR(30) NOT NULL DEFAULT 'approved',
  promoted_until DATETIME NULL,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(500) NULL,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  inquiry_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  favorite_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  contact_clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_pr560_portal_item(portal_item_id),
  KEY idx_pr560_scope(tenant_id,city_id,purpose,property_type), KEY idx_pr560_price(price_value), KEY idx_pr560_agent(agent_user_id), KEY idx_pr560_project(project_id), KEY idx_pr560_approval(approval_status,verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_agent_profiles_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  tenant_id BIGINT UNSIGNED NULL,
  agency_name VARCHAR(190) NULL,
  license_no VARCHAR(100) NULL,
  bio TEXT NULL,
  photo_url VARCHAR(500) NULL,
  service_areas VARCHAR(500) NULL,
  languages VARCHAR(300) NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  status VARCHAR(24) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_pr560_agent_user(user_id), KEY idx_pr560_agent_tenant(tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_leads_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  property_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  phone VARCHAR(80) NOT NULL,
  email VARCHAR(190) NULL,
  lead_type VARCHAR(40) NOT NULL DEFAULT 'inquiry',
  message TEXT NULL,
  budget DECIMAL(16,2) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  assigned_to BIGINT UNSIGNED NULL,
  source VARCHAR(80) NULL,
  admin_note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_pr560_lead_property(property_id,status), KEY idx_pr560_lead_assigned(assigned_to,status), KEY idx_pr560_lead_date(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_visits_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  property_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  phone VARCHAR(80) NOT NULL,
  scheduled_at DATETIME NOT NULL,
  note TEXT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'requested',
  assigned_to BIGINT UNSIGNED NULL,
  admin_note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_pr560_visit_property(property_id,status), KEY idx_pr560_visit_schedule(scheduled_at,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_favorites_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  property_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_pr560_favorite(user_id,property_id), KEY idx_pr560_fav_property(property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_saved_searches_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  filters_json LONGTEXT NOT NULL,
  alerts_enabled TINYINT(1) NOT NULL DEFAULT 0,
  last_checked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_pr560_search_user(user_id,alerts_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_activity_v560 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  property_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  visitor_hash CHAR(64) NULL,
  event_type VARCHAR(40) NOT NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_pr560_activity_property(property_id,event_type,created_at), KEY idx_pr560_activity_date(created_at,event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
