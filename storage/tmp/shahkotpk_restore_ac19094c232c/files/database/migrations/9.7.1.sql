-- ShahkotPK v9.7.1 — Admin Sidebar Tools
-- Adds version metadata only. Sidebar UI is delivered by the updater assets/pages.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.7.1'),
('admin_tools_version','971'),
('database_maintenance_center_version','9.6.0'),
('storage_control_center_version','9.7.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
