-- ShahkotPK v10.0.0 — Security & Performance Suite
-- Non-destructive migration: creates isolated telemetry/security tables and settings only.

CREATE TABLE IF NOT EXISTS security_events_v1000 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  ip_hash CHAR(64) NOT NULL,
  event_key VARCHAR(120) NOT NULL,
  severity VARCHAR(20) NOT NULL DEFAULT 'info',
  route VARCHAR(255) NULL,
  method VARCHAR(10) NULL,
  status_code SMALLINT UNSIGNED NULL,
  summary VARCHAR(255) NOT NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY idx_sec1000_created(created_at),
  KEY idx_sec1000_event(event_key),
  KEY idx_sec1000_ip(ip_hash),
  KEY idx_sec1000_user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS security_rate_buckets_v1000 (
  bucket_key CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  bucket_type VARCHAR(20) NOT NULL,
  window_start DATETIME NOT NULL,
  request_count INT UNSIGNED NOT NULL DEFAULT 0,
  last_route VARCHAR(255) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(bucket_key),
  KEY idx_rate1000_updated(updated_at),
  KEY idx_rate1000_ip(ip_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS security_ip_blocks_v1000 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_hash CHAR(64) NOT NULL,
  ip_hint VARCHAR(40) NOT NULL,
  reason VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  expires_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  disabled_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  disabled_at DATETIME NULL,
  PRIMARY KEY(id),
  KEY idx_block1000_hash(ip_hash),
  KEY idx_block1000_active(active,expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_sessions_v1000 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_hash CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  tenant_id BIGINT UNSIGNED NULL,
  ip_hash CHAR(64) NOT NULL,
  ip_hint VARCHAR(40) NOT NULL,
  user_agent_hash CHAR(64) NOT NULL,
  last_route VARCHAR(255) NULL,
  last_seen_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  revoked TINYINT(1) NOT NULL DEFAULT 0,
  revoked_at DATETIME NULL,
  revoked_by BIGINT UNSIGNED NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_session1000_hash(session_hash),
  KEY idx_session1000_user(user_id),
  KEY idx_session1000_seen(last_seen_at),
  KEY idx_session1000_revoked(revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS performance_requests_v1000 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  route VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  status_code SMALLINT UNSIGNED NOT NULL DEFAULT 200,
  duration_ms DECIMAL(12,2) NOT NULL DEFAULT 0,
  memory_peak_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY idx_perf1000_created(created_at),
  KEY idx_perf1000_route(route),
  KEY idx_perf1000_duration(duration_ms),
  KEY idx_perf1000_status(status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS performance_alerts_v1000 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  alert_key VARCHAR(120) NOT NULL,
  severity VARCHAR(20) NOT NULL DEFAULT 'warning',
  title VARCHAR(160) NOT NULL,
  message VARCHAR(500) NOT NULL,
  state VARCHAR(20) NOT NULL DEFAULT 'open',
  meta_json LONGTEXT NULL,
  first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME NULL,
  resolved_by BIGINT UNSIGNED NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_alert1000_key(alert_key),
  KEY idx_alert1000_state(state,last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.0.0'),
('security_performance_version','1000'),
('security_rate_enforce_v1000','0'),
('security_public_rate_monitor_v1000','0'),
('security_admin_rpm_v1000','180'),
('security_api_rpm_v1000','300'),
('security_public_rpm_v1000','900'),
('performance_slow_ms_v1000','1500'),
('performance_public_sample_percent_v1000','2'),
('performance_retention_days_v1000','14'),
('performance_disk_alert_percent_v1000','85'),
('performance_5xx_alert_count_v1000','5')
ON DUPLICATE KEY UPDATE setting_value=CASE WHEN setting_key='installed_app_version' THEN '10.0.0' WHEN setting_key='security_performance_version' THEN '1000' ELSE setting_value END;
