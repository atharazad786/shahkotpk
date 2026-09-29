-- ShahkotPK v12.7.5 — Candidate Bulk Verify + Import Workflow
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.5'),
('business_source_extractor_version','12.7.5')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
