-- ShahkotPK v11.1.3 — Top Utility Actions Position Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.3'),
('landing_theme_parity_version','11.1.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
