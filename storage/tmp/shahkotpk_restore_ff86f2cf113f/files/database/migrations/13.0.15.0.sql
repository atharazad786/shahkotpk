CREATE TABLE IF NOT EXISTS production_hardening_runs_v1315 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  score INT NOT NULL DEFAULT 0,
  ready_flag TINYINT(1) NOT NULL DEFAULT 0,
  high_count INT UNSIGNED NOT NULL DEFAULT 0,
  medium_count INT UNSIGNED NOT NULL DEFAULT 0,
  manifest_checked INT UNSIGNED NOT NULL DEFAULT 0,
  manifest_mismatch INT UNSIGNED NOT NULL DEFAULT 0,
  summary_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_phr1315_tenant_created (tenant_id, created_at),
  KEY idx_phr1315_ready_created (ready_flag, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS production_hardening_actions_v1315 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(64) NOT NULL,
  result_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_pha1315_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.15.0'),
('step15_production_hardening_version','13.0.15.0'),
('step14_regression_integrity_version','13.0.14.0'),
('access_runtime_performance_hotfix','13.0.5.1'),
('runtime_cache_contract_version','13.0.15.0'),
('stable_release_channel','stable'),
('stable_release_baseline','13.0.15.0'),
('stable_release_readiness','audit_required')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
