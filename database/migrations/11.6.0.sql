-- ShahkotPK v11.6.0 — Full Website Slider Redesign
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.6.0'),
('business_website_version','11.6.0'),
('business_website_design','full_slider')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
