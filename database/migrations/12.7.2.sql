-- ShahkotPK v12.7.2 — Secure Google API Key Setup Hotfix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.2'),
('business_source_extractor_version','12.7.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
