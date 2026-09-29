-- ShahkotPK v5.4.3 — Clean Sidebar Rebuild + Map-Ready Demo Pack
-- No destructive schema/data statements. Old demo rows are removed by the PHP
-- registry manager using exact recorded table + row IDs on the first admin page.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','5.4.3'),
('admin_sidebar_engine_version','543'),
('dummy_data_manager_version','543'),
('dummy_data_pack_version','5.4.3'),
('dummy_data_v543_replace_pending','1'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
