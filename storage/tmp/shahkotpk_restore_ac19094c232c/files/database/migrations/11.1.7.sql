-- ShahkotPK v11.1.7 — Duplicate Smart Search Fix
-- Current Landing Page Theme search becomes the Smart Search host; no second search block is injected.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.7'),
('landing_theme_parity_version','11.1.7'),
('public_runtime_version','11.1.7')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
