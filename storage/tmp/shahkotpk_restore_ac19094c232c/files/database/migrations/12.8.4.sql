-- ShahkotPK v12.8.4 — Google geo auto-sync + map feed repair
CREATE TABLE IF NOT EXISTS business_geo_sync_v1284 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  business_id BIGINT NOT NULL,
  provider_place_id VARCHAR(255) NULL,
  latitude DECIMAL(11,8) NULL,
  longitude DECIMAL(11,8) NULL,
  sync_status VARCHAR(32) NOT NULL DEFAULT 'pending',
  last_error VARCHAR(500) NULL,
  last_synced_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_business_geo_v1284 (tenant_id,business_id),
  KEY idx_geo_v1284 (latitude,longitude),
  KEY idx_place_v1284 (provider_place_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.8.4'),
('business_geo_map_version','12.8.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
