CREATE TABLE IF NOT EXISTS runtime_integrity_runs_v1308 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  score INT NOT NULL DEFAULT 0,
  db_latency_ms DECIMAL(10,3) NULL,
  session_bytes INT UNSIGNED NOT NULL DEFAULT 0,
  summary_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_ri1308_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS runtime_integrity_actions_v1308 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(64) NOT NULL,
  result_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_ria1308_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.8.0'),
('step8_runtime_integrity_version','13.0.8.0'),
('public_data_cache_version','13.0.8.0'),
('runtime_cache_contract_version','13.0.8.0'),
('step7_media_integrity_version','13.0.7.0'),
('access_runtime_performance_hotfix','13.0.5.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
