-- ShahkotPK v9.9.0 — Backup, Restore & Rollback Center
CREATE TABLE IF NOT EXISTS backup_snapshots_v990 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  snapshot_key VARCHAR(120) NOT NULL,
  snapshot_type VARCHAR(40) NOT NULL DEFAULT 'manual',
  status VARCHAR(30) NOT NULL DEFAULT 'completed',
  app_version VARCHAR(40) NULL,
  db_path VARCHAR(500) NULL,
  files_path VARCHAR(500) NULL,
  db_checksum CHAR(64) NULL,
  files_checksum CHAR(64) NULL,
  db_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  files_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  remote_profile_id BIGINT UNSIGNED NULL,
  remote_status VARCHAR(40) NULL,
  remote_meta_json LONGTEXT NULL,
  meta_json LONGTEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_backup_snapshot_key_v990 (snapshot_key),
  KEY idx_backup_created_v990 (created_at),
  KEY idx_backup_type_v990 (snapshot_type),
  KEY idx_backup_status_v990 (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.9.0'),
('backup_restore_version','990'),
('backup_schedule_enabled_v990','0'),
('backup_schedule_frequency_v990','daily'),
('backup_schedule_files_v990','1'),
('backup_schedule_uploads_v990','0'),
('backup_schedule_remote_v990','1'),
('backup_schedule_last_run_v990',''),
('backup_retention_days_v990','30'),
('backup_retention_count_v990','10')
ON DUPLICATE KEY UPDATE setting_value=CASE
 WHEN setting_key='installed_app_version' THEN '9.9.0'
 WHEN setting_key='backup_restore_version' THEN '990'
 ELSE setting_value END;