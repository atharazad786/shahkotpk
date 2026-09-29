-- ShahkotPK v9.5.0 — Smart Render + Cache Hardening
-- Lightweight runtime-only update. Does not delete or reload demo/real data.
-- Keeps above-the-fold media eager, defers only below-fold cards, stops broad DOM observation after 15 seconds,
-- reserves media aspect ratios to reduce layout shift, and switches to a lite mode on low-memory/low-core devices.

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.5.0'),
('dummy_data_manager_version','950'),
('public_ui_version','9.5.0'),
('public_ui_performance_mode','3'),
('public_ui_v930_enabled','1'),
('public_ui_v930_css','/assets/public-ui-v950.css'),
('public_ui_v930_js','/assets/public-ui-v950.js'),
('public_ui_v940_enabled','1'),
('public_ui_v940_css','/assets/public-ui-v950.css'),
('public_ui_v940_js','/assets/public-ui-v950.js'),
('public_ui_v950_enabled','1'),
('public_ui_v950_css','/assets/public-ui-v950.css'),
('public_ui_v950_js','/assets/public-ui-v950.js')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
