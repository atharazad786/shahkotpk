-- ShahkotPK v13.5.0 — City Discovery Content Engine
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.5.0'),('runtime_cache_contract_version','13.5.0'),('public_data_cache_version','13.5.0'),('city_discovery_version','13.5.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT IGNORE INTO settings (setting_key,setting_value) VALUES
('city_discovery_enabled','1'),('city_discovery_home_enabled','1'),('city_discovery_verified_first','1'),('city_discovery_item_limit','12'),('city_discovery_category_limit','8'),('city_discovery_title','Explore Shahkot'),('city_discovery_subtitle','Verified and map-ready local places from ShahkotPK.');
INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES ('admin.city_discovery','City Discovery','Homepage discovery cards and category highlights powered by map-ready Shahkot data','Staff','Administration','/admin/city-discovery.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();
INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.city_discovery',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='tenant_admin' AND r.active=1;
