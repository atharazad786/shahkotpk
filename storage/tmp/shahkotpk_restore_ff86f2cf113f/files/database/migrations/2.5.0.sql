-- ShahkotPK v2.5.0 Recovery Center / System Updater
CREATE TABLE IF NOT EXISTS system_backups (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  backup_key CHAR(32) NOT NULL UNIQUE,
  file_name VARCHAR(255) NOT NULL,
  backup_path VARCHAR(700) NOT NULL,
  source_version VARCHAR(50) NOT NULL,
  backup_type VARCHAR(40) NOT NULL DEFAULT 'manual_system',
  includes_database TINYINT(1) NOT NULL DEFAULT 1,
  includes_uploads TINYINT(1) NOT NULL DEFAULT 0,
  includes_config TINYINT(1) NOT NULL DEFAULT 1,
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  checksum_sha256 CHAR(64) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'ready',
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  restored_at DATETIME NULL,
  INDEX idx_system_backups_created (created_at),
  INDEX idx_system_backups_type (backup_type),
  INDEX idx_system_backups_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('updater_auto_backup','1'),
('updater_auto_rollback','1'),
('updater_backup_retention','15'),
('updater_backup_database','1'),
('updater_full_backup_uploads','1')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
