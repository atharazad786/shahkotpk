-- ShahkotPK v9.1.2 — HD Demo Image Refresh
-- Replaces dummy demo assets with sharper HD versions and updates package metadata.
-- Existing real data, tenant config, storage and uploads remain preserved.
-- If browser cache still shows older thumbnails, hard refresh and use Admin -> Dummy Data Manager -> Reload Current Tenant only if you want a fresh batch.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.1.2'),
('dummy_data_manager_version','912'),
('dummy_data_pack_version','9.1.2'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
