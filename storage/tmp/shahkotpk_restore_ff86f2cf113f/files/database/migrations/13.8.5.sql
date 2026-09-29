-- ShahkotPK v13.8.5 — native landing menu recovery.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.8.5'),
('runtime_cache_contract_version','13.8.5'),
('public_data_cache_version','13.8.5'),
('native_front_menu_contract_version','13.8.5')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
