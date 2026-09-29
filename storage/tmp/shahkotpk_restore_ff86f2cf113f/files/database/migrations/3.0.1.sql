-- ShahkotPK v3.0.1 Landing & City Vertical Dashboards
INSERT INTO settings(setting_key,setting_value) VALUES
('city_verticals_version','301'),
('city_landing_frontend_fix_version','301')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
