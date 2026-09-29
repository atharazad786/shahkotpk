INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.8.3'),
('runtime_cache_contract_version','13.8.3'),
('public_data_cache_version','13.8.3'),
('full_project_audit_version','13.8.3'),
('full_project_audit_cleanup_pending','1'),
('full_project_audit_cleanup_status','pending-self-update'),
('dummy_data_autoload_enabled','0'),
('dummy_data_v543_replace_pending','0'),
('dummy_data_v910_replace_pending','0'),
('dummy_data_v920_replace_pending','0'),
('dummy_data_v930_replace_pending','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO settings(setting_key,setting_value)
VALUES ('map_provider','openfreemap'),('maps_provider','openfreemap')
ON DUPLICATE KEY UPDATE setting_value=
IF(LOWER(TRIM(setting_value)) IN ('openfreemap','leaflet','google'),LOWER(TRIM(setting_value)),'openfreemap');

INSERT INTO access_features_v1300(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at) VALUES
('admin.project_audit','Full Project Audit','Production audit snapshot for critical routes, schema contracts, version state and bounded cleanup','Staff','Administration','/admin/project-audit.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

INSERT IGNORE INTO access_role_features_v1300(tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.project_audit',1,NOW()
FROM access_roles_v1300 r
WHERE r.role_key='tenant_admin' AND r.active=1;
