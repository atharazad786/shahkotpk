-- ShahkotPK v4.0.3 Admin Sidebar Layout Geometry Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('admin_sidebar_navigation_version','403'),
('admin_sidebar_layout_geometry_fix','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
