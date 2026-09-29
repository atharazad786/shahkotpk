-- ShahkotPK v13.3.2 — map provider UI/runtime loader correction
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.3.2'),
('runtime_cache_contract_version','13.3.2'),
('public_data_cache_version','13.3.2'),
('map_platform_settings_version','13.3.2'),
('map_provider','openfreemap'),
('maps_provider','openfreemap'),
('map_tile_provider','openfreemap'),
('map_no_key_mode','1'),
('map_provider_loader_fix','13.3.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
