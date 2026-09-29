-- ShahkotPK v11.5.0 — Professional Business Website Studio
-- Presentation upgrade only. Existing businesses, shopkeepers, products, reviews,
-- deals, microsite profiles and AI copy remain intact.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.5.0'),
('business_microsite_version','11.5.0'),
('business_website_design_version','11.5.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
