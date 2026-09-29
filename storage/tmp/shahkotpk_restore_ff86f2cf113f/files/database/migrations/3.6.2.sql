-- ShahkotPK v3.6.2 Commercial Operations Runtime Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('operations_version','362'),
('operations_runtime_fix_version','362')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
