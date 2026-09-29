-- ShahkotPK v13.3.6 — verified Shahkot coordinates + single Map Control host
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.3.6'),
('runtime_cache_contract_version','13.3.6'),
('public_data_cache_version','13.3.6'),
('map_platform_settings_version','13.3.6'),
('map_provider','openfreemap'),
('maps_provider','openfreemap'),
('map_tile_provider','openfreemap'),
('map_no_key_mode','1'),
('map_duplicate_control_fix','13.3.6'),
('map_verified_location_dataset','13.3.6'),
('research_demo_dataset_version','13.3.6'),
('research_demo_dataset_status','verified_ready_to_apply')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT IGNORE INTO settings (setting_key,setting_value) VALUES
('map_default_latitude','31.5709'),
('map_default_longitude','73.48531'),
('map_default_zoom','14');

UPDATE settings SET setting_value='31.5709' WHERE setting_key='map_default_latitude' AND (setting_value IS NULL OR TRIM(setting_value)='' OR setting_value IN ('31.7397','31.739700','31.7397000'));
UPDATE settings SET setting_value='73.48531' WHERE setting_key='map_default_longitude' AND (setting_value IS NULL OR TRIM(setting_value)='' OR setting_value IN ('73.8643','73.864300','73.8643000'));
UPDATE settings SET setting_value='14' WHERE setting_key='map_default_zoom' AND (setting_value IS NULL OR TRIM(setting_value)='' OR setting_value='13');
