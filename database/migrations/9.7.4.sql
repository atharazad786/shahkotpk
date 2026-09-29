-- ShahkotPK v9.7.4 — Global Admin Tools Sidebar
-- Registers a best-effort global JS bridge and a one-time layout hook installer.
-- No database records/files are deleted.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.7.4'),
('admin_tools_sidebar_version','974'),
('admin_tools_global_sidebar_enabled','1'),
('admin_tools_global_sidebar_js','/assets/admin-sidebar-tools-v974.js'),
('admin_tools_global_sidebar_css','/assets/admin-sidebar-tools-v974.css'),
('public_ui_v930_js','/assets/public-ui-v974.js'),
('public_ui_v940_js','/assets/public-ui-v974.js'),
('public_ui_v950_js','/assets/public-ui-v974.js')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
