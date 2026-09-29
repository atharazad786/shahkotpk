-- ShahkotPK v13.1.2 — Platform Settings full visual restore
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.1.2'),
('runtime_cache_contract_version','13.1.2'),
('public_data_cache_version','13.1.2'),
('platform_settings_visual_restore_version','13.1.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
