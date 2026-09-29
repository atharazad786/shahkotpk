-- ShahkotPK v13.1.3 — Platform Settings diagnostic collector
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.1.3'),
('runtime_cache_contract_version','13.1.3'),
('public_data_cache_version','13.1.3'),
('platform_settings_diagnostic_version','13.1.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
