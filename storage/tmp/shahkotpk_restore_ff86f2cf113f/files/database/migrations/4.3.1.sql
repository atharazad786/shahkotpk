-- ShahkotPK v4.3.1 — public menu layout correction
INSERT INTO settings(setting_key,setting_value) VALUES
('public_header_version','431')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
