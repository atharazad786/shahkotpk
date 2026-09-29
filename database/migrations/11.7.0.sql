-- ShahkotPK v11.7.0 — Business Website Builder + Leads & Analytics
CREATE TABLE IF NOT EXISTS business_site_builder_v1170 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  business_id BIGINT NOT NULL,
  section_order_json LONGTEXT NULL,
  hidden_sections_json LONGTEXT NULL,
  hero_title VARCHAR(220) NOT NULL DEFAULT '',
  hero_subtitle TEXT NULL,
  hero_cta_label VARCHAR(80) NOT NULL DEFAULT '',
  hero_cta_url VARCHAR(500) NOT NULL DEFAULT '',
  slider_autoplay TINYINT(1) NOT NULL DEFAULT 1,
  slider_speed_ms INT NOT NULL DEFAULT 6500,
  lead_form_enabled TINYINT(1) NOT NULL DEFAULT 1,
  show_phone TINYINT(1) NOT NULL DEFAULT 1,
  show_whatsapp TINYINT(1) NOT NULL DEFAULT 1,
  show_directions TINYINT(1) NOT NULL DEFAULT 1,
  show_booking TINYINT(1) NOT NULL DEFAULT 1,
  updated_by BIGINT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_tenant_business (tenant_id,business_id),
  KEY idx_builder_business (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS business_site_leads_v1170 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  business_id BIGINT NOT NULL,
  user_id BIGINT NULL,
  customer_name VARCHAR(160) NOT NULL DEFAULT '',
  phone VARCHAR(60) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  subject VARCHAR(190) NOT NULL DEFAULT '',
  message TEXT NULL,
  source VARCHAR(50) NOT NULL DEFAULT 'website',
  status VARCHAR(30) NOT NULL DEFAULT 'new',
  visitor_hash CHAR(64) NOT NULL DEFAULT '',
  owner_note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_business_status (tenant_id,business_id,status,created_at),
  KEY idx_lead_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.7.0'),
('business_website_builder_version','11.7.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
