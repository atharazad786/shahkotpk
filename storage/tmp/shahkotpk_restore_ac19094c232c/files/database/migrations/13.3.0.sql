-- ShahkotPK v13.3.0 — Unified Notifications & Alert Control Center
CREATE TABLE IF NOT EXISTS notification_channels_v1330 (
  tenant_id BIGINT UNSIGNED NOT NULL,
  channel_key VARCHAR(64) NOT NULL,
  label VARCHAR(120) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sender_name VARCHAR(190) NULL,
  sender_identity VARCHAR(190) NULL,
  quiet_hours_enabled TINYINT(1) NOT NULL DEFAULT 0,
  quiet_hours_start TIME NULL,
  quiet_hours_end TIME NULL,
  updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id, channel_key),
  KEY idx_nc1330_enabled (tenant_id, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_rules_v1330 (
  tenant_id BIGINT UNSIGNED NOT NULL,
  event_key VARCHAR(120) NOT NULL,
  label VARCHAR(190) NOT NULL,
  module_group VARCHAR(120) NOT NULL DEFAULT 'Platform',
  channel_key VARCHAR(64) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  delivery_mode ENUM('instant','digest') NOT NULL DEFAULT 'instant',
  updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id, event_key, channel_key),
  KEY idx_nr1330_module (tenant_id, module_group),
  KEY idx_nr1330_channel (tenant_id, channel_key, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Channel defaults are inserted lazily for the active tenant by app/notification_control_v1330.php.

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.3.0'),
('runtime_cache_contract_version','13.3.0'),
('public_data_cache_version','13.3.0'),
('notification_control_center_version','13.3.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.notification_control','Notifications & Alert Control','Tenant-level notification channel and event policy controls','Staff','Administration','/admin/notification-control.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.notification_control',1,NOW()
FROM access_roles_v1300 r
WHERE r.role_key='tenant_admin' AND r.active=1;
