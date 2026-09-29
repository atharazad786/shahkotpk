-- ShahkotPK v12.6.1 — City Experience Repair & Visual Upgrade
-- Additive metadata update only. Existing city/officer/media rows are preserved.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.6.1'),
('city_information_version','12.6.1'),
('city_portal_version','12.6.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
