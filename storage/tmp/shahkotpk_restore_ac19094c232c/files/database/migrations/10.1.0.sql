-- ShahkotPK v10.1.0 — Smart Homepage Experience & Builder
CREATE TABLE IF NOT EXISTS homepage_settings_v1010 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  theme_key VARCHAR(64) NOT NULL DEFAULT 'premium-light',
  hero_ad_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  hero_heading VARCHAR(220) NOT NULL DEFAULT 'Discover Shahkot. Everything you need, one city.',
  hero_subtitle VARCHAR(500) NOT NULL DEFAULT 'Businesses, shopping, property, healthcare, jobs and local services — beautifully organized in one place.',
  hero_cta_text VARCHAR(80) NOT NULL DEFAULT 'Explore Shahkot',
  hero_cta_url VARCHAR(500) NOT NULL DEFAULT '/businesses.php',
  search_enabled TINYINT(1) NOT NULL DEFAULT 1,
  quick_actions_enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  config_json LONGTEXT NULL,
  activated_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_sections_v1010 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  section_key VARCHAR(64) NOT NULL,
  title VARCHAR(190) NOT NULL,
  subtitle VARCHAR(500) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 50,
  display_mode VARCHAR(24) NOT NULL DEFAULT 'grid',
  record_limit INT NOT NULL DEFAULT 6,
  featured_only TINYINT(1) NOT NULL DEFAULT 0,
  config_json LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_home_section_tenant (tenant_id,section_key),
  KEY idx_home_section_order (tenant_id,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.1.0'),
('homepage_builder_version','10.1.0'),
('homepage_v1010_reversible','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
