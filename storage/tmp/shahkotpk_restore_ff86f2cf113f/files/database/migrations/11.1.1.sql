-- ShahkotPK v11.1.1 — Google Maps Authentication Guard
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.1'),
('maps_auth_guard_version','11.1.1'),
('maps_auth_fallback_enabled','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
