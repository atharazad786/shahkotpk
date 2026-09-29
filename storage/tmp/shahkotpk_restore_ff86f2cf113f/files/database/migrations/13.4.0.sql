-- ShahkotPK v13.4.0 — Location Intelligence & Nearby Discovery
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.4.0'),
('runtime_cache_contract_version','13.4.0'),
('public_data_cache_version','13.4.0'),
('map_platform_settings_version','13.4.0'),
('location_intelligence_version','13.4.0'),
('map_provider','openfreemap'),
('maps_provider','openfreemap'),
('map_tile_provider','openfreemap'),
('map_no_key_mode','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT IGNORE INTO settings (setting_key,setting_value) VALUES
('map_location_discovery','1'),('map_marker_clustering','1'),('map_search_radius_km','5'),('map_max_search_radius_km','25'),('map_nearby_list_limit','20'),('map_discovery_max_results','300');

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.location_intelligence','Location Intelligence','Map-ready data health, clustering, Near Me radius and location discovery controls','Staff','Administration','/admin/location-intelligence.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.location_intelligence',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='tenant_admin' AND r.active=1;
