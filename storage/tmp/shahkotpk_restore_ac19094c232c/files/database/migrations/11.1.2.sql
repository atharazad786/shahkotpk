-- ShahkotPK v11.1.2 — White Screen / Landing Theme Parity Recovery
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.2'),
('landing_theme_parity_version','11.1.2'),
('public_runtime_version','11.1.2'),
('landing_theme_parity_render_mode','async-safe')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
