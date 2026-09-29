CREATE TABLE IF NOT EXISTS data_repair_actions_v1306 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  batch_key VARCHAR(64) NOT NULL,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  module_key VARCHAR(64) NOT NULL,
  table_name VARCHAR(64) NOT NULL,
  record_id VARCHAR(191) NOT NULL,
  action_key VARCHAR(64) NOT NULL,
  before_json LONGTEXT NULL,
  after_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_dr1306_tenant_created (tenant_id, created_at),
  KEY idx_dr1306_batch (batch_key),
  KEY idx_dr1306_record (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.6.0'),
('step6_data_repair_version','13.0.6.0'),
('public_data_cache_version','13.0.6.0'),
('step5_data_consistency_version','13.0.5.0'),
('access_runtime_performance_hotfix','13.0.5.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
