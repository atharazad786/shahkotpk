CREATE TABLE IF NOT EXISTS business_page_media_v1279 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  business_id BIGINT NOT NULL,
  logo_path VARCHAR(500) NULL,
  banner_json LONGTEXT NULL,
  gallery_json LONGTEXT NULL,
  template_key VARCHAR(32) NOT NULL DEFAULT 'premium',
  updated_by BIGINT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_business_page_media_v1279 (tenant_id,business_id),
  KEY idx_business_page_media_business (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (`setting_key`,`setting_value`) VALUES ('installed_app_version','12.7.9') ON DUPLICATE KEY UPDATE `setting_value`='12.7.9';
