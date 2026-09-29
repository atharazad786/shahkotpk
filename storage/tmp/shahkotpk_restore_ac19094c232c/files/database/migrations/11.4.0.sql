-- ShahkotPK v11.4.0 — Professional Business Management
CREATE TABLE IF NOT EXISTS business_admin_profiles_v1140 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NOT NULL,
 shop_id BIGINT UNSIGNED NULL,
 owner_user_id BIGINT UNSIGNED NULL,
 services_json LONGTEXT NULL,
 gallery_json LONGTEXT NULL,
 hours_json LONGTEXT NULL,
 social_json LONGTEXT NULL,
 seo_json LONGTEXT NULL,
 internal_notes TEXT NULL,
 completeness_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 created_by BIGINT UNSIGNED NULL,
 updated_by BIGINT UNSIGNED NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_business_admin_profile_v1140 (tenant_id,business_id),
 KEY idx_business_admin_owner_v1140 (owner_user_id),
 KEY idx_business_admin_shop_v1140 (shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.4.0'),
('business_manager_version','11.4.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
