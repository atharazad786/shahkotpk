-- ShahkotPK v13.1.0 — Unified Admin Control Center
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.1.0'),
('admin_control_center_version','13.1.0'),
('runtime_cache_contract_version','13.1.0'),
('public_data_cache_version','13.1.0'),
('feature_release_channel','stable')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.control_center','Unified Admin Control Center','Read-only saved health snapshot and searchable admin module directory','Staff','Administration','/admin/control-center.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.control_center',1,NOW()
FROM access_roles_v1300 r
WHERE r.role_key='tenant_admin' AND r.active=1;
