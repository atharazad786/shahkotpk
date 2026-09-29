-- ShahkotPK v9.3.0 — Premium Card Animations + Banner UI
-- Keeps registry-safe demo data replacement and registers the v9.3 public UI asset paths.
-- No .htaccess/PHP handler changes. Real data/config/uploads remain preserved.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.3.0'),
('dummy_data_manager_version','930'),
('dummy_data_pack_version','9.3.0'),
('dummy_data_v930_replace_pending','1'),
('public_ui_v930_enabled','1'),
('public_ui_v930_css','/assets/public-ui-v930.css'),
('public_ui_v930_js','/assets/public-ui-v930.js'),
('dummy_data_v52_autoload','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
