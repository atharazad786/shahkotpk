-- ShahkotPK v11.1.4 — Literal Newline / Head Markup Cleanup
-- Runtime-only public rendering hotfix. No user data is modified.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.4'),
('public_runtime_version','11.1.4')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
