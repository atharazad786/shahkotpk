-- ShahkotPK v13.9.2 — restore the v13.8.1 landing-page visual contract.
-- This is presentation-only: later LMS/Classifieds/Services/audit/data features stay installed.
INSERT INTO settings(setting_key,setting_value) VALUES
('homepage_smart_search_enabled','0'),
('landing_visual_contract_version','13.8.1'),
('landing_visual_restore_version','13.9.2'),
('installed_app_version','13.9.2'),
('runtime_cache_contract_version','13.9.2'),
('public_data_cache_version','13.9.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
