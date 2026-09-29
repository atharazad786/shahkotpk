-- ShahkotPK v12.7.1 — Business Importer UI / Sidebar / Sync Hotfix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.1'),
('business_source_extractor_version','12.7.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
