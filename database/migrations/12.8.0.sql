CREATE TABLE IF NOT EXISTS recovery_cleanup_runs_v1280 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  user_id BIGINT NULL,
  mode VARCHAR(32) NOT NULL DEFAULT 'manual',
  selected_count INT NOT NULL DEFAULT 0,
  deleted_count INT NOT NULL DEFAULT 0,
  bytes_freed BIGINT NOT NULL DEFAULT 0,
  result_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_cleanup_created_v1280 (created_at),
  KEY idx_cleanup_tenant_v1280 (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (`setting_key`,`setting_value`) VALUES ('installed_app_version','12.8.0')
ON DUPLICATE KEY UPDATE `setting_value`='12.8.0';
