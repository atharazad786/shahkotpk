-- ShahkotPK v12.7.3 — Imported Business Lifecycle Management
CREATE TABLE IF NOT EXISTS business_source_lifecycle_v1273 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 business_id BIGINT UNSIGNED NOT NULL,
 imported_by_extractor TINYINT(1) NOT NULL DEFAULT 0,
 owner_user_id BIGINT UNSIGNED NULL,
 source_tag VARCHAR(40) NOT NULL DEFAULT 'google_linked',
 managed_status VARCHAR(40) NOT NULL DEFAULT 'linked',
 last_admin_edit_at DATETIME NULL,
 last_owner_edit_at DATETIME NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY uq_bs1273_business (tenant_id,business_id),
 KEY idx_bs1273_owner (tenant_id,owner_user_id),
 KEY idx_bs1273_imported (tenant_id,imported_by_extractor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO business_source_lifecycle_v1273(tenant_id,business_id,imported_by_extractor,source_tag,managed_status,created_at,updated_at)
SELECT l.tenant_id,l.business_id,
 CASE WHEN EXISTS(SELECT 1 FROM business_source_candidates_v1270 c WHERE c.tenant_id=l.tenant_id AND c.provider=l.provider AND c.provider_place_id=l.provider_place_id AND c.matched_business_id=l.business_id AND c.status='imported') THEN 1 ELSE 0 END,
 CASE WHEN EXISTS(SELECT 1 FROM business_source_candidates_v1270 c2 WHERE c2.tenant_id=l.tenant_id AND c2.provider=l.provider AND c2.provider_place_id=l.provider_place_id AND c2.matched_business_id=l.business_id AND c2.status='imported') THEN 'google_imported' ELSE 'google_linked' END,
 'linked',NOW(),NOW()
FROM business_source_links_v1270 l
WHERE l.provider='google_places';

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.7.3'),
('business_source_extractor_version','12.7.3'),
('business_source_lifecycle_version','12.7.3')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
