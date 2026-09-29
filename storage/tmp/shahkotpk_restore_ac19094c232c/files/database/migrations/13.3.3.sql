-- ShahkotPK v13.3.3 — global no-key map replacement
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.3.3'),
('runtime_cache_contract_version','13.3.3'),
('public_data_cache_version','13.3.3'),
('map_platform_settings_version','13.3.3'),
('map_provider','openfreemap'),
('maps_provider','openfreemap'),
('map_tile_provider','openfreemap'),
('map_no_key_mode','1'),
('map_global_compatibility_bridge','13.3.3'),
('google_maps_legacy_replacement','13.3.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
