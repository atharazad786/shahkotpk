-- ShahkotPK v2.9.1 Landing Layout Fix
INSERT INTO settings(setting_key,setting_value) VALUES
('city_portal_frontend_fix_version','291')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
