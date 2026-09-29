-- ShahkotPK v11.0.1 — Active Landing Runtime Hotfix
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.0.1'),
('unified_experience_version','1101'),
('active_landing_v1101_sync_pending','1'),
('active_landing_v1101_runtime_status','pending')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
