-- ShahkotPK v5.0.0 Multi-Tenant / White-Label City Franchise Platform

CREATE TABLE IF NOT EXISTS tenants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city_id BIGINT UNSIGNED NOT NULL UNIQUE,
  code VARCHAR(80) NOT NULL UNIQUE,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  primary_domain VARCHAR(255) NULL UNIQUE,
  status ENUM('draft','active','suspended') NOT NULL DEFAULT 'draft',
  is_master TINYINT(1) NOT NULL DEFAULT 0,
  site_name VARCHAR(190) NOT NULL,
  tagline VARCHAR(300) NULL,
  logo_url VARCHAR(700) NULL,
  favicon_url VARCHAR(700) NULL,
  primary_color VARCHAR(24) NOT NULL DEFAULT '#0f766e',
  secondary_color VARCHAR(24) NOT NULL DEFAULT '#0f172a',
  accent_color VARCHAR(24) NOT NULL DEFAULT '#22c55e',
  timezone VARCHAR(80) NOT NULL DEFAULT 'Asia/Karachi',
  currency VARCHAR(12) NOT NULL DEFAULT 'PKR',
  locale VARCHAR(20) NOT NULL DEFAULT 'en-PK',
  support_email VARCHAR(190) NULL,
  support_phone VARCHAR(60) NULL,
  revenue_share_percent DECIMAL(6,2) NOT NULL DEFAULT 20.00,
  settings_json LONGTEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tenant_status (status,is_master),
  CONSTRAINT fk_tenant_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
  CONSTRAINT fk_tenant_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_domains (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  domain VARCHAR(255) NOT NULL UNIQUE,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('pending','verified','disabled') NOT NULL DEFAULT 'pending',
  verification_token VARCHAR(96) NOT NULL,
  verified_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tenant_domain_tenant (tenant_id,status,is_primary),
  CONSTRAINT fk_tenant_domain_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_members (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  member_role ENUM('owner','admin','manager','editor','finance','support') NOT NULL DEFAULT 'manager',
  permissions_json LONGTEXT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tenant_member (tenant_id,user_id),
  INDEX idx_tenant_member_user (user_id,status),
  CONSTRAINT fk_tenant_member_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_tenant_member_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_features (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  feature_key VARCHAR(100) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  config_json LONGTEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tenant_feature (tenant_id,feature_key),
  CONSTRAINT fk_tenant_feature_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_revenue_ledger (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  source_type VARCHAR(70) NOT NULL,
  source_id BIGINT UNSIGNED NULL,
  gross_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tenant_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  platform_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  reference VARCHAR(160) NULL,
  meta_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tenant_revenue (tenant_id,source_type,created_at),
  CONSTRAINT fk_tenant_revenue_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  description VARCHAR(700) NULL,
  ip_address VARCHAR(64) NULL,
  meta_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tenant_audit (tenant_id,created_at,action_key),
  CONSTRAINT fk_tenant_audit_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_tenant_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing Shahkot installation becomes the master tenant without changing existing city data.
INSERT INTO tenants(city_id,code,name,slug,primary_domain,status,is_master,site_name,tagline,timezone,currency,locale,revenue_share_percent)
SELECT c.id,'master','ShahkotPK','shahkotpk','shahkotpk.com','active',1,'ShahkotPK','Complete City Guide','Asia/Karachi','PKR','en-PK',0
FROM cities c ORDER BY c.id LIMIT 1
ON DUPLICATE KEY UPDATE is_master=1,status='active',site_name=VALUES(site_name);

INSERT IGNORE INTO tenant_domains(tenant_id,domain,is_primary,status,verification_token,verified_at)
SELECT id,'shahkotpk.com',1,'verified',SHA2(CONCAT('shahkotpk.com-',id),256),NOW() FROM tenants WHERE is_master=1 LIMIT 1;
INSERT IGNORE INTO tenant_domains(tenant_id,domain,is_primary,status,verification_token,verified_at)
SELECT id,'www.shahkotpk.com',0,'verified',SHA2(CONCAT('www.shahkotpk.com-',id),256),NOW() FROM tenants WHERE is_master=1 LIMIT 1;

-- Upgrade existing v4 city franchises into tenants.
INSERT INTO tenants(city_id,code,name,slug,primary_domain,status,is_master,site_name,tagline,support_email,support_phone,revenue_share_percent)
SELECT cf.city_id,CONCAT('city-',cf.city_id),COALESCE(NULLIF(cf.franchise_name,''),CONCAT(c.name,' City Portal')),CONCAT('city-',cf.city_id),NULLIF(LOWER(TRIM(cf.custom_domain)),''),
       IF(cf.status='active','active',IF(cf.status='suspended','suspended','draft')),0,
       COALESCE(NULLIF(cf.franchise_name,''),CONCAT(c.name,' City Portal')),CONCAT('Complete local guide for ',c.name),cf.support_email,cf.support_phone,cf.revenue_share_percent
FROM city_franchises cf JOIN cities c ON c.id=cf.city_id
LEFT JOIN tenants t ON t.city_id=cf.city_id
WHERE t.id IS NULL;

INSERT IGNORE INTO tenant_domains(tenant_id,domain,is_primary,status,verification_token,verified_at)
SELECT t.id,LOWER(TRIM(cf.custom_domain)),1,'verified',SHA2(CONCAT(LOWER(TRIM(cf.custom_domain)),'-',t.id),256),NOW()
FROM city_franchises cf JOIN tenants t ON t.city_id=cf.city_id
WHERE TRIM(COALESCE(cf.custom_domain,''))<>'';

INSERT INTO tenant_members(tenant_id,user_id,member_role,status)
SELECT t.id,cf.manager_user_id,'owner',1 FROM city_franchises cf JOIN tenants t ON t.city_id=cf.city_id
WHERE cf.manager_user_id IS NOT NULL
ON DUPLICATE KEY UPDATE member_role='owner',status=1;

-- Default module entitlements for every tenant. Admin can disable any feature per tenant.
INSERT IGNORE INTO tenant_features(tenant_id,feature_key,enabled)
SELECT t.id,f.feature_key,1 FROM tenants t JOIN (
 SELECT 'directory' feature_key UNION ALL SELECT 'city_content' UNION ALL SELECT 'news' UNION ALL SELECT 'blog' UNION ALL
 SELECT 'live' UNION ALL SELECT 'marketplace' UNION ALL SELECT 'property' UNION ALL SELECT 'jobs' UNION ALL SELECT 'classifieds' UNION ALL
 SELECT 'ads' UNION ALL SELECT 'ai' UNION ALL SELECT 'mobile_api' UNION ALL SELECT 'seller_pos' UNION ALL SELECT 'maps' UNION ALL SELECT 'services'
) f;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','5.0.0'),
('multi_tenant_enabled','1'),
('tenant_isolation_version','500'),
('tenant_unknown_host_mode','master'),
('tenant_domain_verification_mode','manual'),
('tenant_white_label_pwa','1'),
('tenant_master_domain','shahkotpk.com')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
