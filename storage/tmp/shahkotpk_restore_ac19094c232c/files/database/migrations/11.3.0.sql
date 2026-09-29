-- ShahkotPK v11.3.0 — AI Business Microsite Studio
CREATE TABLE IF NOT EXISTS business_microsite_settings_v1130 (
  tenant_id BIGINT NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  directory_links TINYINT(1) NOT NULL DEFAULT 1,
  animations TINYINT(1) NOT NULL DEFAULT 1,
  analytics TINYINT(1) NOT NULL DEFAULT 1,
  ai_copy_enabled TINYINT(1) NOT NULL DEFAULT 1,
  ai_auto_refresh TINYINT(1) NOT NULL DEFAULT 1,
  ai_batch_size INT NOT NULL DEFAULT 1,
  default_theme_mode VARCHAR(40) NOT NULL DEFAULT 'auto',
  show_ai_concierge TINYINT(1) NOT NULL DEFAULT 1,
  show_products TINYINT(1) NOT NULL DEFAULT 1,
  show_deals TINYINT(1) NOT NULL DEFAULT 1,
  show_reviews TINYINT(1) NOT NULL DEFAULT 1,
  show_map TINYINT(1) NOT NULL DEFAULT 1,
  show_gallery TINYINT(1) NOT NULL DEFAULT 1,
  show_services TINYINT(1) NOT NULL DEFAULT 1,
  show_contact_bar TINYINT(1) NOT NULL DEFAULT 1,
  updated_by BIGINT NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS business_microsites_v1130 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  business_id BIGINT NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  theme_mode VARCHAR(30) NOT NULL DEFAULT 'auto',
  theme_key VARCHAR(40) NOT NULL DEFAULT 'auto',
  layout_variant VARCHAR(40) NOT NULL DEFAULT 'auto',
  hero_style VARCHAR(40) NOT NULL DEFAULT '',
  ai_enabled TINYINT(1) NOT NULL DEFAULT 1,
  ai_copy_json LONGTEXT NULL,
  source_checksum CHAR(64) NOT NULL DEFAULT '',
  last_ai_at DATETIME NULL,
  updated_by BIGINT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_tenant_business (tenant_id,business_id),
  KEY idx_business (business_id),
  KEY idx_ai (tenant_id,ai_enabled,last_ai_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS business_microsite_events_v1130 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  business_id BIGINT NOT NULL,
  user_id BIGINT NULL,
  event_key VARCHAR(40) NOT NULL,
  visitor_hash CHAR(64) NOT NULL DEFAULT '',
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_business_event (tenant_id,business_id,event_key,created_at),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.3.0'),
('business_microsite_version','11.3.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
