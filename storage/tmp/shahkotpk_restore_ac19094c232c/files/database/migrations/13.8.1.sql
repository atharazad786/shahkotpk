-- ShahkotPK v13.8.1 — Classifieds Admin Blank Page Hotfix
-- Non-destructive metadata update only.
INSERT INTO settings(setting_key,setting_value) VALUES
('classifieds_admin_hotfix_version','13.8.1'),
('installed_app_version','13.8.1'),
('runtime_cache_contract_version','13.8.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
