CREATE TABLE IF NOT EXISTS background_integrity_runs_v1313 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  score INT NOT NULL DEFAULT 0,
  high_count INT UNSIGNED NOT NULL DEFAULT 0,
  medium_count INT UNSIGNED NOT NULL DEFAULT 0,
  tables_scanned INT UNSIGNED NOT NULL DEFAULT 0,
  files_scanned INT UNSIGNED NOT NULL DEFAULT 0,
  summary_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_bir1313_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS background_integrity_actions_v1313 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(64) NOT NULL,
  result_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_bia1313_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.13.0'),
('step13_background_integrity_version','13.0.13.0'),
('step12_public_seo_routing_version','13.0.12.0'),
('access_runtime_performance_hotfix','13.0.5.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
