-- ShahkotPK v12.7.8 unified business publish/map health index
CREATE TABLE IF NOT EXISTS business_publish_health_v1278 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NOT NULL,
 source_mode VARCHAR(40) NULL,
 public_ready TINYINT(1) NOT NULL DEFAULT 0,
 map_ready TINYINT(1) NOT NULL DEFAULT 0,
 city_id BIGINT NULL,
 latitude DECIMAL(10,7) NULL,
 longitude DECIMAL(10,7) NULL,
 geo_source VARCHAR(60) NULL,
 google_place_id VARCHAR(255) NULL,
 issues_json JSON NULL,
 last_error VARCHAR(500) NULL,
 last_checked_at DATETIME NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY (id),
 UNIQUE KEY uq_v1278_business (tenant_id,business_id),
 KEY idx_v1278_public_map (tenant_id,public_ready,map_ready),
 KEY idx_v1278_business_id (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES ('installed_app_version','12.7.8')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO settings (setting_key,setting_value) VALUES ('business_publish_index_version','12.7.8')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
