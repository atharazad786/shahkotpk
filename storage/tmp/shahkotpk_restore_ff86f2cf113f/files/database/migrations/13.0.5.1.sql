INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.5.1'),
('step4_linkage_version','13.0.5.1'),
('module_linkage_audit_version','13.0.5.1'),
('access_runtime_performance_hotfix','13.0.5.1'),
('step5_data_consistency_version','13.0.5.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
