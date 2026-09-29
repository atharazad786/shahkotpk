-- ShahkotPK v13.0.2 — Step 2 structure synchronization
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.0.2'),
('core_structure_sync_version','13.0.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
