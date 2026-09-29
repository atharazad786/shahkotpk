-- ShahkotPK v13.2.0 — Platform Module Visibility Manager
CREATE TABLE IF NOT EXISTS access_tenant_feature_overrides_v1300 (
  tenant_id BIGINT UNSIGNED NOT NULL,
  feature_key VARCHAR(190) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id, feature_key),
  KEY idx_atfo_feature (feature_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.2.0'),
('runtime_cache_contract_version','13.2.0'),
('public_data_cache_version','13.2.0'),
('module_visibility_manager_version','13.2.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.module_control','Platform Module Visibility Manager','Tenant-level public feature visibility controls','Staff','Administration','/admin/module-control.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.module_control',1,NOW()
FROM access_roles_v1300 r
WHERE r.role_key='tenant_admin' AND r.active=1;
