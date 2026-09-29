-- ShahkotPK v9.6.0 — Pro Database & Maintenance Center
-- Adds audit storage and version settings only. It does not delete, truncate, optimize or alter project data automatically.

CREATE TABLE IF NOT EXISTS admin_db_audit_v960 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(100) NOT NULL,
  metadata_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_admin_db_audit_user (user_id),
  KEY idx_admin_db_audit_action (action_key),
  KEY idx_admin_db_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.6.0'),
('database_manager_version','9.6.0'),
('database_manager_enabled','1'),
('database_manager_url','/admin/database-manager.php'),
('maintenance_center_version','960')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
