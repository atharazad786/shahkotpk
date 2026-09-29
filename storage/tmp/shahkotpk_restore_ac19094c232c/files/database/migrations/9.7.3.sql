-- ShahkotPK v9.7.3 — Admin Sidebar Overlap Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.7.3'),
('admin_tools_sidebar_version','973')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
