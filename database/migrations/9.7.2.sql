-- ShahkotPK v9.7.2 — Admin Tools Sidebar Tab
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.7.2'),
('admin_tools_sidebar_version','972')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
