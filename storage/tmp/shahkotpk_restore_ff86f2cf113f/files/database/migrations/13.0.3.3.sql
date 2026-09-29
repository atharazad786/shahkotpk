INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.3.3'),
('structure_stabilization_version','13.0.3.3'),
('step3_repair_version','13.0.3.3'),
('public_header_repair_version','13.0.3.3'),
('public_header_mode','native')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
