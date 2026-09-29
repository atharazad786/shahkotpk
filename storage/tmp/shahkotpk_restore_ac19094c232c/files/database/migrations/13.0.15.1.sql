INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.15.1'),
('step15_production_hardening_version','13.0.15.1'),
('runtime_cache_contract_version','13.0.15.1'),
('stable_release_channel','stable'),
('stable_release_baseline','13.0.15.1'),
('stable_release_readiness','audit_required'),
('session_security_hotfix_version','13.0.15.1'),
('runtime_integrity_forward_contract_fix','13.0.15.1'),
('seo_route_audit_false_positive_fix','13.0.15.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
