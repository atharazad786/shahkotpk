INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.4.0'),
('step4_linkage_version','13.0.4.0'),
('module_linkage_audit_version','13.0.4.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
