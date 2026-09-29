-- ShahkotPK v4.0.4 Logs & Location Admin Layout Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('activity_log_version','404'),
('admin_logs_layout_fix_version','404')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
