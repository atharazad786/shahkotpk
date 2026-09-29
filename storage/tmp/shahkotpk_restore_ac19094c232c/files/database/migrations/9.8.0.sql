-- ShahkotPK v9.8.0 — Server & System Control Center
CREATE TABLE IF NOT EXISTS system_control_events_v980 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  event_key VARCHAR(120) NOT NULL,
  severity VARCHAR(20) NOT NULL DEFAULT 'info',
  summary VARCHAR(255) NOT NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_system_control_created (created_at),
  KEY idx_system_control_event (event_key),
  KEY idx_system_control_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','9.8.0'),
('system_control_version','980'),
('system_maintenance_enabled_v980','0'),
('system_maintenance_message_v980','Scheduled maintenance is in progress. Please try again shortly.'),
('system_maintenance_until_v980','')
ON DUPLICATE KEY UPDATE setting_value=CASE WHEN setting_key='installed_app_version' THEN '9.8.0' WHEN setting_key='system_control_version' THEN '980' ELSE setting_value END;
