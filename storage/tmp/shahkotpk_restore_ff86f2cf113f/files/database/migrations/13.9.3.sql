-- ShahkotPK v13.9.3 — exact v13.8.1 landing runtime restore.
-- Presentation/runtime only. Later application modules and business data stay installed.
INSERT INTO settings(setting_key,setting_value) VALUES
('homepage_smart_search_enabled','0'),
('landing_visual_contract_version','13.8.1-exact-runtime'),
('landing_visual_restore_version','13.9.3'),
('installed_app_version','13.9.3'),
('runtime_cache_contract_version','13.9.3'),
('public_data_cache_version','13.9.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
