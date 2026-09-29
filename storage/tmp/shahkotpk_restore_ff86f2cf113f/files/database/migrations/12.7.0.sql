-- ShahkotPK v12.7.0 — Shahkot Business Data Extractor & Verified Importer
CREATE TABLE IF NOT EXISTS business_source_settings_v1270 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 google_key_cipher LONGTEXT NULL,
 auto_publish_verified TINYINT(1) NOT NULL DEFAULT 1,
 create_shopkeeper TINYINT(1) NOT NULL DEFAULT 1,
 create_marketplace_shop TINYINT(1) NOT NULL DEFAULT 1,
 create_business_website TINYINT(1) NOT NULL DEFAULT 1,
 official_website_sync TINYINT(1) NOT NULL DEFAULT 1,
 scheduled_sync TINYINT(1) NOT NULL DEFAULT 0,
 max_results SMALLINT UNSIGNED NOT NULL DEFAULT 20,
 shahkot_radius_m INT UNSIGNED NOT NULL DEFAULT 7000,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_bs1270_settings_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_source_candidates_v1270 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 provider VARCHAR(32) NOT NULL DEFAULT 'google_places',
 provider_place_id VARCHAR(255) NOT NULL,
 category_id BIGINT UNSIGNED NULL,
 query_key VARCHAR(190) NULL,
 status VARCHAR(32) NOT NULL DEFAULT 'discovered',
 confidence SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 website_verified TINYINT(1) NOT NULL DEFAULT 0,
 distance_m INT UNSIGNED NULL,
 matched_business_id BIGINT UNSIGNED NULL,
 last_error VARCHAR(500) NULL,
 discovered_at DATETIME NULL,
 last_checked_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_bs1270_candidate (tenant_id,provider,provider_place_id),
 KEY idx_bs1270_candidate_status (tenant_id,status),
 KEY idx_bs1270_candidate_category (tenant_id,category_id),
 KEY idx_bs1270_candidate_business (matched_business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_source_links_v1270 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NOT NULL,
 provider VARCHAR(32) NOT NULL DEFAULT 'google_places',
 provider_place_id VARCHAR(255) NOT NULL,
 verification_mode VARCHAR(32) NOT NULL DEFAULT 'official_website',
 last_source_check_at DATETIME NULL,
 last_website_sync_at DATETIME NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_bs1270_business_provider (tenant_id,business_id,provider),
 UNIQUE KEY uq_bs1270_place (tenant_id,provider,provider_place_id),
 KEY idx_bs1270_link_business (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_source_runs_v1270 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 category_id BIGINT UNSIGNED NULL,
 query_key VARCHAR(190) NULL,
 found_count INT UNSIGNED NOT NULL DEFAULT 0,
 new_count INT UNSIGNED NOT NULL DEFAULT 0,
 existing_count INT UNSIGNED NOT NULL DEFAULT 0,
 imported_count INT UNSIGNED NOT NULL DEFAULT 0,
 error_count INT UNSIGNED NOT NULL DEFAULT 0,
 status VARCHAR(32) NOT NULL DEFAULT 'complete',
 started_by BIGINT UNSIGNED NULL,
 started_at DATETIME NULL,
 finished_at DATETIME NULL,
 PRIMARY KEY(id),
 KEY idx_bs1270_runs_tenant (tenant_id,started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_source_credentials_v1270 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 credential_cipher LONGTEXT NULL,
 created_at DATETIME NULL,
 revealed_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_bs1270_cred_user (tenant_id,user_id),
 KEY idx_bs1270_cred_business (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_source_audit_v1270 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NULL,
 candidate_id BIGINT UNSIGNED NULL,
 event_key VARCHAR(64) NOT NULL,
 detail_json LONGTEXT NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at DATETIME NULL,
 PRIMARY KEY(id),
 KEY idx_bs1270_audit_tenant (tenant_id,created_at),
 KEY idx_bs1270_audit_business (business_id),
 KEY idx_bs1270_audit_candidate (candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO business_source_settings_v1270(tenant_id,enabled,auto_publish_verified,create_shopkeeper,create_marketplace_shop,create_business_website,official_website_sync,scheduled_sync,max_results,shahkot_radius_m,created_at,updated_at)
SELECT 0,1,1,1,1,1,1,0,20,7000,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM business_source_settings_v1270 WHERE tenant_id=0);

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.0'),
('business_source_extractor_version','12.7.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
