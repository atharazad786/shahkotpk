-- ShahkotPK v13.8.4 — landing page regression containment.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.8.4'),
('runtime_cache_contract_version','13.8.4'),
('public_data_cache_version','13.8.4'),
('landing_map_widget_contract_version','13.8.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
