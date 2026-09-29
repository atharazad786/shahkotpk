-- ShahkotPK v9.4.0 — Adaptive Media Performance
-- Does not delete/reload dummy data. Switches the registered public UI assets to the lighter v9.4 loader.
-- Above-the-fold images are no longer forced to lazy-load; only off-screen media is deferred.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.4.0'),
('dummy_data_manager_version','940'),
('public_ui_version','9.4.0'),
('public_ui_performance_mode','2'),
('public_ui_v930_enabled','1'),
('public_ui_v930_css','/assets/public-ui-v940.css'),
('public_ui_v930_js','/assets/public-ui-v940.js'),
('public_ui_v940_enabled','1'),
('public_ui_v940_css','/assets/public-ui-v940.css'),
('public_ui_v940_js','/assets/public-ui-v940.js')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
