-- ShahkotPK v12.7.4 — Dual Google Key + 403 Diagnostics
ALTER TABLE business_source_settings_v1270
 ADD COLUMN server_google_key_cipher LONGTEXT NULL AFTER google_key_cipher,
 ADD COLUMN browser_google_key_cipher LONGTEXT NULL AFTER server_google_key_cipher;

UPDATE business_source_settings_v1270
SET server_google_key_cipher=google_key_cipher
WHERE (server_google_key_cipher IS NULL OR server_google_key_cipher='')
  AND google_key_cipher IS NOT NULL AND google_key_cipher<>'';

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.4'),
('business_source_extractor_version','12.7.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
