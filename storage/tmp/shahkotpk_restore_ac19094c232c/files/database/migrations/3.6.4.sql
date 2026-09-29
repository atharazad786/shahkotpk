-- ShahkotPK v3.6.4 UI / Layout Recovery
INSERT INTO settings(setting_key,setting_value) VALUES
('ui_layout_recovery_version','364'),
('public_navigation_version','364')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
