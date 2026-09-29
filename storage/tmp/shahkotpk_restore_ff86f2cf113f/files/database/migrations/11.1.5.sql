-- ShahkotPK v11.1.5 — Utility Overlap Layout Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.5'),
('landing_theme_parity_version','11.1.5'),
('public_runtime_version','11.1.5')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
