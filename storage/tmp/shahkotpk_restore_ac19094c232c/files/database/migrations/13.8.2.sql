-- ShahkotPK v13.8.2 — Runtime SQL audit hotfix (no schema/data destructive changes)
INSERT INTO settings(setting_key,setting_value) VALUES
('services_version','13.8.2'),
('services_runtime_audit_version','13.8.2'),
('classifieds_runtime_audit_version','13.8.2'),
('installed_app_version','13.8.2'),
('runtime_cache_contract_version','13.8.2'),
('public_data_cache_version','13.8.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
