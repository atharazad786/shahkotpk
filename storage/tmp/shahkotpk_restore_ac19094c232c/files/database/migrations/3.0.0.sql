
-- ShahkotPK v3.0.0 Premium City Verticals
INSERT INTO settings(setting_key,setting_value) VALUES
('city_verticals_version','300'),
('business_directory_public_page_enabled','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
