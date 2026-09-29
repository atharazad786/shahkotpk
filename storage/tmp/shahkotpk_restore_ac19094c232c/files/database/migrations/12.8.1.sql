-- ShahkotPK v12.8.1 — business visibility/status normalization hotfix.
-- No bulk activation is performed inside SQL because businesses.status may be numeric, enum or varchar.
-- Runtime repair is schema-aware and admin-confirmed through Professional Business Manager.
INSERT INTO settings (setting_key,setting_value) VALUES ('installed_app_version','12.8.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO settings (setting_key,setting_value) VALUES ('business_visibility_status_version','12.8.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
