-- ShahkotPK v10.2.1 — Central Sidebar Registry + Glitch Fix
CREATE TABLE IF NOT EXISTS admin_sidebar_overrides_v1021 (
  module_key VARCHAR(90) NOT NULL,
  enabled TINYINT(1) NULL DEFAULT NULL,
  sort_order INT NULL DEFAULT NULL,
  category_key VARCHAR(90) NULL DEFAULT NULL,
  category_label VARCHAR(120) NULL DEFAULT NULL,
  category_order INT NULL DEFAULT NULL,
  updated_by BIGINT NULL DEFAULT NULL,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.2.1'),
('admin_sidebar_registry_version','10.2.1'),
('admin_sidebar_renderer','central_manifest_registry')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
