-- ShahkotPK v9.1.0 — Complete Realistic Demo Data Pack
-- Registry-managed demo rows are replaced by PHP on the first admin page after update.
-- Existing non-demo rows, tenant branding, API credentials, uploads and settings are preserved.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.1.0'),
('dummy_data_manager_version','910'),
('dummy_data_pack_version','9.1.0'),
('dummy_data_v910_replace_pending','1'),
('dummy_data_v543_replace_pending','1'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
