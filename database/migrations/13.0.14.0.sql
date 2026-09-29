CREATE TABLE IF NOT EXISTS regression_integrity_runs_v1314 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  score INT NOT NULL DEFAULT 0,
  high_count INT UNSIGNED NOT NULL DEFAULT 0,
  medium_count INT UNSIGNED NOT NULL DEFAULT 0,
  files_checked INT UNSIGNED NOT NULL DEFAULT 0,
  manifests_checked INT UNSIGNED NOT NULL DEFAULT 0,
  summary_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_rir1314_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS regression_integrity_actions_v1314 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(64) NOT NULL,
  result_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_ria1314_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.14.0'),
('step14_regression_integrity_version','13.0.14.0'),
('step13_background_integrity_version','13.0.13.0'),
('access_runtime_performance_hotfix','13.0.5.1'),
('runtime_cache_contract_version','13.0.14.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
