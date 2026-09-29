-- ShahkotPK v2.3.0 Theme Studio
INSERT INTO settings(setting_key,setting_value) VALUES
('admin_theme_slug','aurora-command'),
('landing_theme_slug','metro-portal'),
('admin_theme_animations','1'),
('landing_theme_animations','1')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
