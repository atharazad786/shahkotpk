-- ShahkotPK v2.8.1 Admin Readability Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('admin_readable_typography_enabled','1'),
('admin_font_scale','comfortable')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
