-- ShahkotPK v13.0.1 — Core Structure & Cross-Page Sync Foundation
CREATE TABLE IF NOT EXISTS access_tenant_custom_features_v1301 (
 tenant_id BIGINT NOT NULL DEFAULT 0,
 feature_key VARCHAR(160) NOT NULL,
 label VARCHAR(180) NOT NULL,
 description VARCHAR(700) NULL,
 audience VARCHAR(32) NOT NULL DEFAULT 'public',
 module_group VARCHAR(100) NOT NULL DEFAULT 'Custom',
 route_pattern VARCHAR(500) NULL,
 admin_only TINYINT NOT NULL DEFAULT 0,
 default_guest TINYINT NOT NULL DEFAULT 0,
 default_customer TINYINT NOT NULL DEFAULT 0,
 default_shopkeeper TINYINT NOT NULL DEFAULT 0,
 active TINYINT NOT NULL DEFAULT 1,
 created_by BIGINT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(tenant_id,feature_key), KEY idx_custom_route(tenant_id,admin_only,active,route_pattern), KEY idx_custom_group(tenant_id,audience,module_group,active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS system_integrity_runs_v1301 (
 id BIGINT NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT NOT NULL DEFAULT 0,
 actor_user_id BIGINT NULL,
 action_key VARCHAR(80) NOT NULL,
 result_json LONGTEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_integrity_tenant(tenant_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO settings(setting_key,setting_value) VALUES ('installed_app_version','13.0.1') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO settings(setting_key,setting_value) VALUES ('core_structure_sync_version','13.0.1') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
