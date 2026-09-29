INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.3'),
('structure_stabilization_version','13.0.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
