-- ShahkotPK v13.1.4 — Platform Settings canonical root renderer
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.1.4'),
('runtime_cache_contract_version','13.1.4'),
('public_data_cache_version','13.1.4'),
('platform_settings_root_version','13.1.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
