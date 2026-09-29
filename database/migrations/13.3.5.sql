-- ShahkotPK v13.3.5 — research-based Shahkot demo dataset manager
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.3.5'),
('runtime_cache_contract_version','13.3.5'),
('public_data_cache_version','13.3.5'),
('research_demo_dataset_version','13.3.5'),
('research_demo_dataset_status','ready_to_apply')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.research_demo_data','Research Demo Data','Safe schema-adaptive Shahkot research demo dataset replacement','Staff','Administration','/admin/realistic-demo-data.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.research_demo_data',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='tenant_admin' AND r.active=1;
