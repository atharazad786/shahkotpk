-- ShahkotPK v12.8.3 — Imported shopkeeper authentication repair / credential manager
-- No destructive schema changes. Existing users are repaired at runtime schema-aware.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.8.3'),
('business_source_extractor_version','12.8.3'),
('shopkeeper_auth_manager_version','12.8.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
