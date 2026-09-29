-- ShahkotPK v3.4.3 Runtime Compatibility Repair
INSERT INTO settings(setting_key,setting_value) VALUES
('runtime_compat_fix_version','343')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
