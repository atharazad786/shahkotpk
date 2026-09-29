-- ShahkotPK v9.2.0 — Cards + Banners HD Demo Refresh
-- On the first admin page after installation, the PHP demo loader removes ONLY registry-managed demo batches
-- and loads one fresh v9.2.0 HD demo batch for the active tenant. Real/non-registry data is not selected.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.2.0'),
('dummy_data_manager_version','920'),
('dummy_data_pack_version','9.2.0'),
('dummy_data_v920_replace_pending','1'),
('dummy_data_v910_replace_pending','1'),
('dummy_data_v543_replace_pending','1'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
