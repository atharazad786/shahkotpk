CREATE TABLE IF NOT EXISTS media_integrity_runs_v1307 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  score INT NOT NULL DEFAULT 0,
  summary_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_mi1307_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_integrity_actions_v1307 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  batch_key VARCHAR(64) NOT NULL,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  module_key VARCHAR(64) NOT NULL,
  table_name VARCHAR(64) NOT NULL,
  record_id VARCHAR(191) NOT NULL,
  field_name VARCHAR(64) NOT NULL,
  before_value TEXT NULL,
  after_value TEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_mia1307_tenant_created (tenant_id, created_at),
  KEY idx_mia1307_batch (batch_key),
  KEY idx_mia1307_record (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.7.0'),
('step7_media_integrity_version','13.0.7.0'),
('public_data_cache_version','13.0.7.0'),
('step6_data_repair_version','13.0.6.0'),
('access_runtime_performance_hotfix','13.0.5.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
