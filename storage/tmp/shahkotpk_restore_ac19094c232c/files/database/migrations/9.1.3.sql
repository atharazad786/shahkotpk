-- ShahkotPK v9.1.3 — Auto-Replace HD Demo Data
-- On the first admin page after installation, the PHP demo loader removes ONLY registry-managed demo batches
-- and loads one fresh v9.1.3 HD demo batch for the active tenant. Real/non-registry data is not selected.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.1.3'),
('dummy_data_manager_version','913'),
('dummy_data_pack_version','9.1.3'),
('dummy_data_v910_replace_pending','1'),
('dummy_data_v543_replace_pending','1'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
