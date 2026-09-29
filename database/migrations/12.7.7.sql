-- ShahkotPK v12.7.7 — imported business public publish / geo / photo-sync health
CREATE TABLE IF NOT EXISTS business_source_health_v1277 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NOT NULL,
 geo_status VARCHAR(32) NOT NULL DEFAULT 'pending',
 latitude DECIMAL(11,8) NULL,
 longitude DECIMAL(11,8) NULL,
 photo_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 photo_status VARCHAR(32) NOT NULL DEFAULT 'pending',
 public_status VARCHAR(32) NOT NULL DEFAULT 'pending',
 last_geo_sync_at DATETIME NULL,
 last_photo_sync_at DATETIME NULL,
 last_repair_at DATETIME NULL,
 last_error VARCHAR(500) NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_bs1277_health_business (tenant_id,business_id),
 KEY idx_bs1277_health_geo (tenant_id,geo_status),
 KEY idx_bs1277_health_public (tenant_id,public_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.7'),
('business_source_extractor_version','12.7.7')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
