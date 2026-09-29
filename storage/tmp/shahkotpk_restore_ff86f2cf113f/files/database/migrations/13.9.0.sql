-- ShahkotPK v13.9.0 — Native Homepage Smart Utility Pack
INSERT INTO settings(setting_key,setting_value) VALUES
('homepage_smart_search_enabled','1'),
('homepage_smart_search_min_chars','2'),
('homepage_smart_search_max_suggestions','10'),
('homepage_smart_search_recent_enabled','1'),
('installed_app_version','13.9.0'),
('runtime_cache_contract_version','13.9.0'),
('public_data_cache_version','13.9.0')
ON DUPLICATE KEY UPDATE setting_value=CASE
 WHEN setting_key IN ('installed_app_version','runtime_cache_contract_version','public_data_cache_version') THEN VALUES(setting_value)
 ELSE setting_value END;
