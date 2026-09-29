CREATE TABLE IF NOT EXISTS enterprise_tenant_settings_v800 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  settings_json LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voice_integrations_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  integration_type VARCHAR(60) NOT NULL DEFAULT 'grandstream_ucm',
  host VARCHAR(255) NULL,
  port INT NULL,
  api_base_url VARCHAR(500) NULL,
  username VARCHAR(190) NULL,
  secret_cipher LONGTEXT NULL,
  secret_hint VARCHAR(40) NULL,
  webhook_token_hash CHAR(64) NULL,
  webhook_token_hint VARCHAR(40) NULL,
  media_bridge_url VARCHAR(500) NULL,
  ai_agent_id BIGINT UNSIGNED NULL,
  stt_provider VARCHAR(80) NULL,
  tts_provider VARCHAR(80) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  inbound_enabled TINYINT(1) NOT NULL DEFAULT 1,
  outbound_enabled TINYINT(1) NOT NULL DEFAULT 0,
  recording_enabled TINYINT(1) NOT NULL DEFAULT 0,
  config_json LONGTEXT NULL,
  last_test_status VARCHAR(30) NULL,
  last_test_message VARCHAR(500) NULL,
  last_test_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_voice_integration (tenant_id,enabled,integration_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voice_extensions_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  integration_id BIGINT UNSIGNED NOT NULL,
  extension_no VARCHAR(40) NOT NULL,
  display_name VARCHAR(160) NULL,
  user_id BIGINT UNSIGNED NULL,
  ai_agent_id BIGINT UNSIGNED NULL,
  role_key VARCHAR(80) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  allow_ai_assist TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_extension (tenant_id,integration_id,extension_no),
  KEY ix_v800_extension_user (tenant_id,user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voice_queues_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  integration_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  queue_code VARCHAR(60) NOT NULL,
  strategy VARCHAR(40) NOT NULL DEFAULT 'ringall',
  extension_ids_json LONGTEXT NULL,
  ai_agent_id BIGINT UNSIGNED NULL,
  overflow_extension VARCHAR(40) NULL,
  max_wait_seconds INT NOT NULL DEFAULT 120,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_queue (tenant_id,integration_id,queue_code),
  KEY ix_v800_queue_enabled (tenant_id,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voice_call_events_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  integration_id BIGINT UNSIGNED NULL,
  external_call_id VARCHAR(190) NULL,
  direction VARCHAR(20) NOT NULL DEFAULT 'inbound',
  from_number VARCHAR(100) NULL,
  to_number VARCHAR(100) NULL,
  extension_no VARCHAR(40) NULL,
  queue_code VARCHAR(60) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'received',
  started_at DATETIME NULL,
  answered_at DATETIME NULL,
  ended_at DATETIME NULL,
  duration_seconds INT NOT NULL DEFAULT 0,
  recording_url VARCHAR(500) NULL,
  transcript_text LONGTEXT NULL,
  ai_summary LONGTEXT NULL,
  sentiment VARCHAR(30) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_call_scope (tenant_id,created_at,status),
  KEY ix_v800_call_external (tenant_id,integration_id,external_call_id),
  KEY ix_v800_call_extension (tenant_id,extension_no,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voice_campaigns_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  integration_id BIGINT UNSIGNED NOT NULL,
  ai_agent_id BIGINT UNSIGNED NULL,
  campaign_type VARCHAR(40) NOT NULL DEFAULT 'outbound_notification',
  source_list_json LONGTEXT NULL,
  script_text LONGTEXT NULL,
  schedule_at DATETIME NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  calls_total INT NOT NULL DEFAULT 0,
  calls_completed INT NOT NULL DEFAULT 0,
  calls_failed INT NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_campaign (tenant_id,status,schedule_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bi_dashboards_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description VARCHAR(500) NULL,
  audience VARCHAR(40) NOT NULL DEFAULT 'admin',
  layout_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_bi_dashboard (tenant_id,slug),
  KEY ix_v800_bi_enabled (tenant_id,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bi_widgets_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  dashboard_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  metric_key VARCHAR(120) NOT NULL,
  widget_type VARCHAR(40) NOT NULL DEFAULT 'kpi',
  source_config_json LONGTEXT NULL,
  display_config_json LONGTEXT NULL,
  sort_order INT NOT NULL DEFAULT 100,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_bi_widget (tenant_id,dashboard_id,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bi_report_schedules_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  report_key VARCHAR(120) NOT NULL,
  frequency VARCHAR(30) NOT NULL DEFAULT 'weekly',
  recipient_emails TEXT NULL,
  format VARCHAR(20) NOT NULL DEFAULT 'html',
  next_run_at DATETIME NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  last_run_at DATETIME NULL,
  last_status VARCHAR(30) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_report_schedule (tenant_id,enabled,next_run_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bi_forecast_snapshots_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  metric_key VARCHAR(120) NOT NULL,
  horizon_days INT NOT NULL DEFAULT 30,
  method VARCHAR(60) NOT NULL DEFAULT 'linear_trend',
  current_value DECIMAL(20,4) NOT NULL DEFAULT 0,
  forecast_value DECIMAL(20,4) NOT NULL DEFAULT 0,
  confidence_label VARCHAR(30) NOT NULL DEFAULT 'indicative',
  series_json LONGTEXT NULL,
  generated_by BIGINT UNSIGNED NULL,
  generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_forecast (tenant_id,metric_key,generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ops_job_queue_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  queue_name VARCHAR(80) NOT NULL DEFAULT 'default',
  job_type VARCHAR(120) NOT NULL,
  payload_json LONGTEXT NULL,
  priority INT NOT NULL DEFAULT 100,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  attempts INT NOT NULL DEFAULT 0,
  max_attempts INT NOT NULL DEFAULT 5,
  available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reserved_at DATETIME NULL,
  finished_at DATETIME NULL,
  last_error VARCHAR(1000) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_job_claim (status,available_at,priority,id),
  KEY ix_v800_job_tenant (tenant_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ops_cron_jobs_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  job_key VARCHAR(120) NOT NULL,
  schedule_expr VARCHAR(120) NOT NULL,
  handler_key VARCHAR(120) NOT NULL,
  payload_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  next_run_at DATETIME NULL,
  last_run_at DATETIME NULL,
  last_status VARCHAR(30) NULL,
  last_message VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_cron (tenant_id,job_key),
  KEY ix_v800_cron_due (enabled,next_run_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ops_health_checks_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  check_key VARCHAR(120) NOT NULL,
  label VARCHAR(180) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'unknown',
  latency_ms INT NULL,
  message VARCHAR(1000) NULL,
  meta_json LONGTEXT NULL,
  checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_health (tenant_id,check_key),
  KEY ix_v800_health_status (tenant_id,status,checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ops_incidents_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  incident_no VARCHAR(70) NOT NULL,
  source VARCHAR(80) NOT NULL DEFAULT 'system',
  severity VARCHAR(20) NOT NULL DEFAULT 'medium',
  title VARCHAR(220) NOT NULL,
  description LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  assigned_user_id BIGINT UNSIGNED NULL,
  opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  acknowledged_at DATETIME NULL,
  resolved_at DATETIME NULL,
  resolution_note LONGTEXT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_incident (tenant_id,incident_no),
  KEY ix_v800_incident_status (tenant_id,status,severity,opened_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ops_security_events_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  event_key VARCHAR(120) NOT NULL,
  severity VARCHAR(20) NOT NULL DEFAULT 'info',
  actor_label VARCHAR(190) NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  message VARCHAR(1000) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v800_security_event (tenant_id,severity,created_at),
  KEY ix_v800_security_user (tenant_id,user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enterprise_profiles_v800 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  legal_name VARCHAR(220) NULL,
  platform_name VARCHAR(220) NULL,
  support_email VARCHAR(190) NULL,
  support_phone VARCHAR(80) NULL,
  primary_domain VARCHAR(255) NULL,
  primary_color VARCHAR(20) NULL,
  secondary_color VARCHAR(20) NULL,
  email_from_name VARCHAR(190) NULL,
  white_label_enabled TINYINT(1) NOT NULL DEFAULT 1,
  powered_by_visible TINYINT(1) NOT NULL DEFAULT 0,
  enterprise_notes LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enterprise_domains_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  hostname VARCHAR(255) NOT NULL,
  domain_type VARCHAR(30) NOT NULL DEFAULT 'custom',
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  ssl_status VARCHAR(30) NOT NULL DEFAULT 'unknown',
  verification_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  verification_token VARCHAR(120) NULL,
  last_checked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_domain (hostname),
  KEY ix_v800_domain_tenant (tenant_id,is_primary,verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enterprise_feature_policies_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  feature_key VARCHAR(120) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  plan_key VARCHAR(100) NULL,
  limits_json LONGTEXT NULL,
  config_json LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_feature_policy (tenant_id,feature_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enterprise_api_clients_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  client_key VARCHAR(120) NOT NULL,
  secret_hash CHAR(64) NOT NULL,
  secret_hint VARCHAR(40) NULL,
  scopes_json LONGTEXT NULL,
  rate_limit_per_hour INT NOT NULL DEFAULT 1000,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  last_used_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_api_client (tenant_id,client_key),
  KEY ix_v800_api_client_enabled (tenant_id,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enterprise_usage_v800 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  usage_date DATE NOT NULL,
  metric_key VARCHAR(120) NOT NULL,
  metric_value DECIMAL(20,4) NOT NULL DEFAULT 0,
  meta_json LONGTEXT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v800_usage (tenant_id,usage_date,metric_key),
  KEY ix_v800_usage_metric (tenant_id,metric_key,usage_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO ops_cron_jobs_v800(tenant_id,name,job_key,schedule_expr,handler_key,payload_json,enabled,next_run_at,created_at,updated_at)
VALUES
(0,'Health check sweep','health-sweep','*/10 * * * *','health_sweep','{}',1,NOW(),NOW(),NOW()),
(0,'Queue cleanup','queue-cleanup','15 * * * *','queue_cleanup','{}',1,NOW(),NOW(),NOW()),
(0,'BI daily snapshot','bi-daily-snapshot','10 1 * * *','bi_snapshot','{}',1,NOW(),NOW(),NOW());
