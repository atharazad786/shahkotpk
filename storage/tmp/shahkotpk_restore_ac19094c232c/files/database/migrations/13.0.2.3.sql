-- ShahkotPK v13.0.2.3 — native sidebar recovery mode
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.0.2.3'),
('core_structure_sync_version','13.0.2.3'),
('admin_sidebar_renderer_version','native-13.0.2.3'),
('admin_sidebar_render_mode','native')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
