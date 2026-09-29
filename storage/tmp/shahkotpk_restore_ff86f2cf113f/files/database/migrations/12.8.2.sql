-- ShahkotPK v12.8.2 — Sidebar integrity + professional business website repair.
-- Runtime/UI-only repair. No destructive changes.
INSERT INTO settings (setting_key,setting_value) VALUES ('installed_app_version','12.8.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO settings (setting_key,setting_value) VALUES ('sidebar_integrity_business_website_version','12.8.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
