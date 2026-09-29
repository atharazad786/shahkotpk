-- ShahkotPK v12.4.1 — LIVE Watch Shahkot + City Information header integration.
-- Presentation-only hotfix. No user/business/city-information rows are altered or deleted.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.4.1'),
('city_information_version','12.4.1'),
('city_information_header_companion','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
