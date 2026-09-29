-- ShahkotPK v5.4.4 — Neon Command Admin Dashboard Theme
-- Non-destructive: only adds/updates dashboard presentation settings.
INSERT INTO settings(setting_key,setting_value) VALUES
('admin_dashboard_theme_v544','neon-command'),
('admin_dashboard_version','544'),
('installed_app_version','5.4.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
