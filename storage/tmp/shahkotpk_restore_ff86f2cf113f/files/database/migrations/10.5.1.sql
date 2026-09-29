-- ShahkotPK v10.5.1 — Public Navigation Registry
CREATE TABLE IF NOT EXISTS public_navigation_v1051 (
 id BIGINT NOT NULL AUTO_INCREMENT,
 tenant_id BIGINT NOT NULL,
 nav_key VARCHAR(100) NOT NULL,
 label VARCHAR(120) NOT NULL,
 url VARCHAR(500) NOT NULL,
 group_key VARCHAR(30) NOT NULL DEFAULT 'primary',
 priority VARCHAR(20) NOT NULL DEFAULT 'primary',
 sort_order INT NOT NULL DEFAULT 100,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 source_type VARCHAR(30) NOT NULL DEFAULT 'manifest',
 is_custom TINYINT(1) NOT NULL DEFAULT 0,
 updated_by BIGINT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_public_nav (tenant_id,nav_key), KEY idx_public_nav_render (tenant_id,group_key,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.5.1'),
('public_navigation_version','10.5.1'),
('public_navigation_registry_mode','manifest_auto'),
('future_customer_modules_public_nav','auto_if_relevant')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
