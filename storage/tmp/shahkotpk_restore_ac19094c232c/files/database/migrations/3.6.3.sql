-- ShahkotPK v3.6.3 Operations Layout Runtime Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('operations_version','363'),
('operations_layout_fix_version','363')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
