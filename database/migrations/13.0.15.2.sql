-- ShahkotPK v13.0.15.2 final control-plane cleanup
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.0.15.2'),
('step15_production_hardening_version','13.0.15.2'),
('runtime_cache_contract_version','13.0.15.2'),
('public_data_cache_version','13.0.15.2'),
('stable_release_baseline','13.0.15.2'),
('stable_release_readiness','audit_required'),
('runtime_public_cache_alignment_fix','13.0.15.2'),
('seo_stale_audit_snapshot_fix','13.0.15.2'),
('integrity_sidebar_permission_map_fix','13.0.15.2')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_features_v1300
(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at)
VALUES
('admin.integrity.background','Background Jobs Integrity','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/background-integrity.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.data_consistency','Data Consistency Center','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/data-consistency.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.data_repair','Data Repair Center','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/data-repair.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.database','Database Integrity','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/database-integrity.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.media','Media Integrity Center','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/media-integrity.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.permissions','Permissions Integrity','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/permissions-integrity.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.production','Production Readiness','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/production-hardening.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.public_seo','Public SEO & Routing','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/public-seo-routing.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.regression','Full Regression Integrity','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/regression-integrity.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.runtime','Runtime Performance','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/runtime-integrity.php*',1,0,0,0,1,'core',NOW(),NOW()),
('admin.integrity.security','Security Integrity','Stable integrity/control-plane admin module','Staff','System Integrity','/admin/security-integrity.php*',1,0,0,0,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=1,active=1,updated_at=NOW();

-- Give only the tenant-admin system role these diagnostics by default. Existing explicit
-- mappings are preserved because INSERT IGNORE never flips an enabled/disabled choice.
INSERT IGNORE INTO access_role_features_v1300 (tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,f.feature_key,1,NOW()
FROM access_roles_v1300 r
JOIN access_features_v1300 f ON f.feature_key IN ('admin.integrity.background','admin.integrity.data_consistency','admin.integrity.data_repair','admin.integrity.database','admin.integrity.media','admin.integrity.permissions','admin.integrity.production','admin.integrity.public_seo','admin.integrity.regression','admin.integrity.runtime','admin.integrity.security')
WHERE r.role_key='tenant_admin' AND r.active=1;
