-- ShahkotPK v11.4.1 — Professional Business Form Layout Hotfix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.4.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
