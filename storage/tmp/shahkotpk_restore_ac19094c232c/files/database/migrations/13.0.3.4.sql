INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.3.4'),
('structure_stabilization_version','13.0.3.4'),
('step3_repair_version','13.0.3.4'),
('step3_cleanup_version','13.0.3.4'),
('public_header_repair_version','13.0.3.4'),
('public_header_mode','native')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
