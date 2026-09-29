-- ShahkotPK v7.0.5 Adaptive Brand Assets & Favicon Sync
CREATE TABLE IF NOT EXISTS tenant_brand_assets_v705 (
  tenant_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  desktop_dark_logo_path VARCHAR(700) NULL,
  desktop_light_logo_path VARCHAR(700) NULL,
  mobile_dark_logo_path VARCHAR(700) NULL,
  mobile_light_logo_path VARCHAR(700) NULL,
  favicon_dark_path VARCHAR(700) NULL,
  favicon_light_path VARCHAR(700) NULL,
  auto_adapt TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO tenant_brand_assets_v705(tenant_id)
SELECT id FROM tenants;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','7.0.5'),
('adaptive_brand_assets_version','705')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
