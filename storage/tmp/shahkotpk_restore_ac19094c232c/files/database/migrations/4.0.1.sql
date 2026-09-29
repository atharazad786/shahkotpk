-- ShahkotPK v4.0.1 Animated Admin Sidebar Navigation
INSERT INTO settings(setting_key,setting_value) VALUES
('admin_sidebar_navigation_version','401'),
('admin_sidebar_grouped_navigation','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
