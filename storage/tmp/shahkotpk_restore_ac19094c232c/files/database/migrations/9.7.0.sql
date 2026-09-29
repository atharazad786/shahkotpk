-- ShahkotPK v9.7.0 — Storage Control Center
CREATE TABLE IF NOT EXISTS storage_profiles_v970 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  driver VARCHAR(32) NOT NULL DEFAULT 'local',
  endpoint VARCHAR(500) NULL,
  region VARCHAR(80) NULL,
  bucket VARCHAR(190) NULL,
  base_path VARCHAR(500) NULL,
  public_base_url VARCHAR(700) NULL,
  secret_cipher MEDIUMTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  last_test_status VARCHAR(32) NULL,
  last_test_message VARCHAR(500) NULL,
  last_test_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_storage_profiles_driver (driver), KEY idx_storage_profiles_enabled (enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS storage_routes_v970 (
  category VARCHAR(64) NOT NULL,
  profile_id BIGINT UNSIGNED NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 0,
  mode VARCHAR(24) NOT NULL DEFAULT 'copy',
  path_prefix VARCHAR(255) NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (category), KEY idx_storage_routes_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS storage_objects_v970 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(64) NOT NULL,
  profile_id BIGINT UNSIGNED NOT NULL,
  source_path VARCHAR(1000) NOT NULL,
  source_checksum CHAR(64) NULL,
  remote_key VARCHAR(1200) NOT NULL,
  public_url VARCHAR(1600) NULL,
  size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(32) NOT NULL DEFAULT 'copied',
  migrated_by BIGINT UNSIGNED NULL,
  migrated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_storage_objects_profile (profile_id), KEY idx_storage_objects_cat (category), KEY idx_storage_objects_source (source_path(190))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS storage_jobs_v970 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(64) NOT NULL,
  profile_id BIGINT UNSIGNED NOT NULL,
  mode VARCHAR(24) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  processed_files INT UNSIGNED NOT NULL DEFAULT 0,
  uploaded_files INT UNSIGNED NOT NULL DEFAULT 0,
  failed_files INT UNSIGNED NOT NULL DEFAULT 0,
  bytes_processed BIGINT UNSIGNED NOT NULL DEFAULT 0,
  meta_json MEDIUMTEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.7.0'),('storage_center_version','970'),('external_storage_enabled','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
