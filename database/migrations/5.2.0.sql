-- ShahkotPK v5.2.0 — Advanced Landing Themes + Safe Dummy Data Manager

CREATE TABLE IF NOT EXISTS dummy_data_batches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  batch_key VARCHAR(120) NOT NULL UNIQUE,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'loading',
  created_by BIGINT UNSIGNED NULL,
  record_count INT UNSIGNED NOT NULL DEFAULT 0,
  meta_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  INDEX idx_dummy_batch_tenant (tenant_id,status,created_at),
  INDEX idx_dummy_batch_city (city_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dummy_data_registry (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  batch_id BIGINT UNSIGNED NOT NULL,
  sequence_no INT UNSIGNED NOT NULL DEFAULT 0,
  table_name VARCHAR(64) NOT NULL,
  record_id BIGINT UNSIGNED NOT NULL,
  record_label VARCHAR(190) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_dummy_registry (batch_id,table_name,record_id),
  INDEX idx_dummy_registry_batch_sequence (batch_id,sequence_no,id),
  CONSTRAINT fk_dummy_registry_batch FOREIGN KEY (batch_id) REFERENCES dummy_data_batches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','5.2.0'),
('landing_theme_engine_version','520'),
('dummy_data_manager_enabled','1'),
('dummy_data_v52_autoload','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
