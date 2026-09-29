-- ShahkotPK v13.0.2.2 — sidebar single-renderer layout repair
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.0.2.2'),
('core_structure_sync_version','13.0.2.2'),
('admin_sidebar_renderer_version','13.0.2.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
