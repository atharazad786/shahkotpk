-- ShahkotPK v11.1.6 — Remove Global Floating Utility Actions
-- Prevents Alerts/Favorites/Compare from overlapping landing content.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.6'),
('landing_theme_parity_version','11.1.6'),
('public_runtime_version','11.1.6')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

UPDATE landing_theme_parity_v1110
SET floating_actions=0, updated_at=NOW();
