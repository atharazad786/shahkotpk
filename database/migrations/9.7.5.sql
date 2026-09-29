-- ShahkotPK v9.7.5 — Admin Tools Password Lock
-- Adds no destructive database changes. The password gate is implemented in PHP using a one-way password hash.
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.7.5'),
('admin_tools_lock_version','975'),
('admin_tools_password_lock_enabled','1'),
('admin_tools_unlock_timeout_seconds','1800')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
