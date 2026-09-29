-- ShahkotPK v9.3.1 — Performance Hotfix
-- No demo rows are deleted/reloaded by this migration.
-- Optimized assets and lighter public UI code are applied by file replacement.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.3.1'),
('dummy_data_manager_version','931'),
('dummy_data_pack_version','9.3.1'),
('public_ui_version','9.3.1'),
('public_ui_performance_mode','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
