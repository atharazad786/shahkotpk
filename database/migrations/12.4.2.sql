-- ShahkotPK v12.4.2 — Canonical Route & Duplicate Link Repair
-- /city-guide.php is canonical; /city-information.php remains as a 301 compatibility alias.
-- No business, customer, city-information, commerce or upload rows are deleted.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.4.2'),
('city_information_version','12.4.2'),
('city_information_url','/city-guide.php'),
('public_route_policy_version','12.4.2'),
('public_route_city_guide_canonical','/city-guide.php')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
