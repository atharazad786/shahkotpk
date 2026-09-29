-- ShahkotPK v13.3.4 — full-widget maps + marker-data recovery
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.3.4'),
('runtime_cache_contract_version','13.3.4'),
('public_data_cache_version','13.3.4'),
('map_platform_settings_version','13.3.4'),
('map_provider','openfreemap'),
('maps_provider','openfreemap'),
('map_tile_provider','openfreemap'),
('map_no_key_mode','1'),
('map_global_compatibility_bridge','13.3.4'),
('google_maps_legacy_replacement','13.3.4'),
('map_full_widget_fix','13.3.4'),
('map_marker_data_bridge','13.3.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
