-- ShahkotPK v3.6.1 Grouped Public Navigation
INSERT INTO settings(setting_key,setting_value) VALUES
('public_navigation_version','361')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
