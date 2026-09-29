-- ShahkotPK v11.4.2 — Complete Business Form UI Repair
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.4.2'),
('business_manager_version','1142')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
