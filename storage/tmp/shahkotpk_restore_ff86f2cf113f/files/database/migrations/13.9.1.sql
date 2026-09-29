-- ShahkotPK v13.9.1 — City Listing Motion direct smart-search binding
INSERT INTO settings(setting_key,setting_value) VALUES
('homepage_smart_search_enabled','1'),
('installed_app_version','13.9.1'),
('runtime_cache_contract_version','13.9.1'),
('public_data_cache_version','13.9.1')
ON DUPLICATE KEY UPDATE setting_value=CASE
 WHEN setting_key IN ('installed_app_version','runtime_cache_contract_version','public_data_cache_version') THEN VALUES(setting_value)
 ELSE setting_value END;
