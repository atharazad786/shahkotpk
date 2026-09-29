-- ShahkotPK v9.1.1 — Dummy Data Loader Hotfix
-- Fixes the v9.1.0 malformed AI-policy demo insert that passed an array as the table argument.
-- No live/demo rows are deleted automatically by this migration.
-- Use Admin -> Dummy Data Manager -> Reload Current Tenant to replace a partially loaded batch safely.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.1.1'),
('dummy_data_manager_version','911'),
('dummy_data_pack_version','9.1.1'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
