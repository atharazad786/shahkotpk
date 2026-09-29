-- ShahkotPK v12.5.1 — Combined City Portal + 3D Weather + Administration Officers
-- Safe additive migration. No existing city, business, customer, order, CRM or upload rows are deleted.
CREATE TABLE IF NOT EXISTS city_portal_settings_v1251 (
  tenant_id BIGINT UNSIGNED NOT NULL,
  weather_enabled TINYINT(1) NOT NULL DEFAULT 1,
  weather_title VARCHAR(160) NOT NULL DEFAULT 'Live Shahkot Weather',
  latitude DECIMAL(10,6) NOT NULL DEFAULT 31.570900,
  longitude DECIMAL(10,6) NOT NULL DEFAULT 73.485300,
  cards_3d_enabled TINYINT(1) NOT NULL DEFAULT 1,
  officers_enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_information_media_v1251 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  item_key VARCHAR(190) NOT NULL,
  media_type VARCHAR(30) NOT NULL DEFAULT 'image',
  file_url VARCHAR(500) NOT NULL,
  caption VARCHAR(240) NULL,
  sort_order INT NOT NULL DEFAULT 10,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_city_media_tenant_item (tenant_id,item_key,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_officers_v1251 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  designation VARCHAR(190) NOT NULL,
  vision_message TEXT NULL,
  photo_url VARCHAR(500) NULL,
  office_name VARCHAR(190) NULL,
  phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  sort_order INT NOT NULL DEFAULT 10,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_city_officer_tenant (tenant_id,enabled,featured,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO city_portal_settings_v1251(tenant_id,weather_enabled,weather_title,latitude,longitude,cards_3d_enabled,officers_enabled,updated_at)
VALUES(0,1,'Live Shahkot Weather',31.570900,73.485300,1,1,NOW())
ON DUPLICATE KEY UPDATE updated_at=updated_at;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.5.1'),
('city_information_version','12.5.1'),
('city_information_url','/city-guide.php'),
('city_content_studio_url','/admin/city-content-studio.php'),
('city_portal_version','12.5.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
