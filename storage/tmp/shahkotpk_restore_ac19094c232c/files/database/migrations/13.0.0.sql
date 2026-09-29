-- ShahkotPK v13.0.0 — Tenant-aware Roles, Packages & Feature Access Control
-- Additive only. No existing user/business/customer data is deleted.

CREATE TABLE IF NOT EXISTS access_features_v1300 (
  id BIGINT NOT NULL AUTO_INCREMENT,
  feature_key VARCHAR(160) NOT NULL,
  label VARCHAR(180) NOT NULL,
  description VARCHAR(700) NULL,
  audience VARCHAR(32) NOT NULL DEFAULT 'public',
  module_group VARCHAR(100) NOT NULL DEFAULT 'General',
  route_pattern VARCHAR(500) NULL,
  admin_only TINYINT NOT NULL DEFAULT 0,
  default_guest TINYINT NOT NULL DEFAULT 0,
  default_customer TINYINT NOT NULL DEFAULT 0,
  default_shopkeeper TINYINT NOT NULL DEFAULT 0,
  active TINYINT NOT NULL DEFAULT 1,
  source VARCHAR(60) NOT NULL DEFAULT 'core',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_access_feature(feature_key), KEY idx_access_route(admin_only,active,route_pattern), KEY idx_access_group(module_group,audience,active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_tenant_settings_v1300 (
  tenant_id BIGINT NOT NULL,
  enabled TINYINT NOT NULL DEFAULT 1,
  inherit_global TINYINT NOT NULL DEFAULT 1,
  default_customer_package_code VARCHAR(80) NOT NULL DEFAULT 'customer_free',
  default_shopkeeper_package_code VARCHAR(80) NOT NULL DEFAULT 'shopkeeper_starter',
  new_guest_features TINYINT NOT NULL DEFAULT 0,
  new_customer_features TINYINT NOT NULL DEFAULT 0,
  new_shopkeeper_features TINYINT NOT NULL DEFAULT 0,
  new_staff_features TINYINT NOT NULL DEFAULT 0,
  updated_by BIGINT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_tenant_feature_overrides_v1300 (
  tenant_id BIGINT NOT NULL,
  feature_key VARCHAR(160) NOT NULL,
  enabled TINYINT NOT NULL DEFAULT 1,
  updated_by BIGINT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id,feature_key), KEY idx_tenant_feature_enabled(tenant_id,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_guest_policy_v1300 (
  tenant_id BIGINT NOT NULL,
  feature_key VARCHAR(160) NOT NULL,
  enabled TINYINT NOT NULL DEFAULT 0,
  updated_by BIGINT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id,feature_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_packages_v1300 (
  id BIGINT NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  audience VARCHAR(32) NOT NULL,
  package_code VARCHAR(80) NOT NULL,
  name VARCHAR(140) NOT NULL,
  description VARCHAR(700) NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  billing_cycle VARCHAR(32) NOT NULL DEFAULT 'monthly',
  duration_days INT NOT NULL DEFAULT 30,
  published TINYINT NOT NULL DEFAULT 0,
  is_default TINYINT NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 100,
  limits_json LONGTEXT NULL,
  legacy_plan_id BIGINT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_tenant_package(tenant_id,audience,package_code), KEY idx_package_live(tenant_id,audience,published,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_package_features_v1300 (
  tenant_id BIGINT NOT NULL DEFAULT 0,
  package_id BIGINT NOT NULL,
  feature_key VARCHAR(160) NOT NULL,
  enabled TINYINT NOT NULL DEFAULT 0,
  limit_value VARCHAR(120) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(package_id,feature_key), KEY idx_package_feature_tenant(tenant_id,feature_key,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_user_packages_v1300 (
  id BIGINT NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  user_id BIGINT NOT NULL,
  audience VARCHAR(32) NOT NULL,
  package_id BIGINT NOT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'active',
  source VARCHAR(40) NOT NULL DEFAULT 'admin',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  assigned_by BIGINT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_user_package(tenant_id,user_id,audience,status,ends_at), KEY idx_package_users(package_id,status,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_roles_v1300 (
  id BIGINT NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  role_key VARCHAR(80) NOT NULL,
  name VARCHAR(140) NOT NULL,
  description VARCHAR(700) NULL,
  is_system TINYINT NOT NULL DEFAULT 0,
  active TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 100,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_tenant_role(tenant_id,role_key), KEY idx_role_live(tenant_id,active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_role_features_v1300 (
  tenant_id BIGINT NOT NULL DEFAULT 0,
  role_key VARCHAR(80) NOT NULL,
  feature_key VARCHAR(160) NOT NULL,
  enabled TINYINT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id,role_key,feature_key), KEY idx_role_feature(feature_key,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_user_overrides_v1300 (
  tenant_id BIGINT NOT NULL DEFAULT 0,
  user_id BIGINT NOT NULL,
  feature_key VARCHAR(160) NOT NULL,
  effect VARCHAR(12) NOT NULL DEFAULT 'deny',
  note VARCHAR(500) NULL,
  updated_by BIGINT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id,user_id,feature_key), KEY idx_user_override(user_id,effect)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_package_requests_v1300 (
  id BIGINT NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  user_id BIGINT NOT NULL,
  audience VARCHAR(32) NOT NULL,
  package_id BIGINT NOT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'pending',
  payment_status VARCHAR(24) NOT NULL DEFAULT 'pending',
  payment_reference VARCHAR(180) NULL,
  note VARCHAR(700) NULL,
  reviewed_by BIGINT NULL,
  reviewed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_package_request(tenant_id,audience,status,created_at), KEY idx_package_request_user(user_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_audit_v1300 (
  id BIGINT NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL DEFAULT 0,
  actor_user_id BIGINT NULL,
  target_user_id BIGINT NULL,
  event_key VARCHAR(120) NOT NULL,
  feature_key VARCHAR(160) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_access_audit(tenant_id,created_at), KEY idx_access_target(target_user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','13.0.0'),
('access_control_version','13.0.0'),
('tenant_package_access_version','13.0.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
