-- ShahkotPK v13.1.1 — Map Platform Settings renderer hotfix
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.1.1'),
('runtime_cache_contract_version','13.1.1'),
('public_data_cache_version','13.1.1'),
('map_platform_settings_version','13.1.1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.map_control','Map Control','Manage ShahkotPK map platform settings and map behavior','Staff','Administration','/admin/map*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();
