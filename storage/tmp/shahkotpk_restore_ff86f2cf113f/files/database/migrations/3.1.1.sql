
-- ShahkotPK v3.1.1 City Map rendering/configuration fix
INSERT INTO settings(setting_key,setting_value) VALUES
('google_maps_frontend_fix_version','311')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
