-- ShahkotPK v3.4.1 Emergency Runtime / HTTP 500 hardening
INSERT INTO settings(setting_key,setting_value) VALUES
('runtime_guard_enabled','1'),
('runtime_guard_version','341')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
