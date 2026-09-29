-- ShahkotPK v10.3.0 — Customer Utility & Local Services Hub
CREATE TABLE IF NOT EXISTS customer_utility_settings_v1030 (
 tenant_id BIGINT NOT NULL,
 hub_enabled TINYINT(1) NOT NULL DEFAULT 1,
 smart_request_enabled TINYINT(1) NOT NULL DEFAULT 1,
 price_compare_enabled TINYINT(1) NOT NULL DEFAULT 1,
 nearby_enabled TINYINT(1) NOT NULL DEFAULT 1,
 daily_cards_enabled TINYINT(1) NOT NULL DEFAULT 1,
 emergency_enabled TINYINT(1) NOT NULL DEFAULT 1,
 reminders_enabled TINYINT(1) NOT NULL DEFAULT 1,
 lost_found_enabled TINYINT(1) NOT NULL DEFAULT 1,
 homepage_section_enabled TINYINT(1) NOT NULL DEFAULT 1,
 requests_per_hour INT NOT NULL DEFAULT 5,
 offers_per_request INT NOT NULL DEFAULT 8,
 nearby_radius_km DECIMAL(8,2) NOT NULL DEFAULT 20,
 request_auto_route_limit INT NOT NULL DEFAULT 5,
 updated_by BIGINT NULL, updated_at DATETIME NULL,
 PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_daily_cards_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL,
 card_key VARCHAR(90) NOT NULL, icon VARCHAR(16) NULL, title VARCHAR(120) NOT NULL,
 value_text VARCHAR(190) NOT NULL, subtitle VARCHAR(255) NULL,
 source_label VARCHAR(120) NULL, source_url VARCHAR(500) NULL,
 target_url VARCHAR(500) NULL, sort_order INT NOT NULL DEFAULT 100, enabled TINYINT(1) NOT NULL DEFAULT 1,
 updated_by BIGINT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_daily_tenant (tenant_id,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_emergency_contacts_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL,
 category_key VARCHAR(60) NOT NULL DEFAULT 'emergency', icon VARCHAR(16) NULL,
 label VARCHAR(140) NOT NULL, phone VARCHAR(50) NOT NULL, whatsapp VARCHAR(50) NULL,
 address VARCHAR(255) NULL, note VARCHAR(255) NULL, sort_order INT NOT NULL DEFAULT 100,
 enabled TINYINT(1) NOT NULL DEFAULT 1, updated_by BIGINT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_emergency_tenant (tenant_id,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_service_requests_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL,
 requester_hash CHAR(64) NOT NULL, category_key VARCHAR(60) NOT NULL DEFAULT 'general',
 need_text TEXT NOT NULL, budget_min DECIMAL(14,2) NOT NULL DEFAULT 0, budget_max DECIMAL(14,2) NOT NULL DEFAULT 0,
 area VARCHAR(160) NULL, contact_name VARCHAR(120) NOT NULL, contact_phone VARCHAR(50) NOT NULL,
 contact_whatsapp VARCHAR(50) NULL, status VARCHAR(30) NOT NULL DEFAULT 'open',
 assigned_note VARCHAR(500) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_request_tenant_status (tenant_id,status,id), KEY idx_request_user (user_id,id), KEY idx_request_hash (requester_hash,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_request_routes_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, request_id BIGINT NOT NULL, business_id BIGINT NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'sent', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, viewed_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_request_business (request_id,business_id), KEY idx_route_business (tenant_id,business_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_request_offers_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, request_id BIGINT NOT NULL, business_id BIGINT NOT NULL,
 offered_by BIGINT NOT NULL, price DECIMAL(14,2) NOT NULL DEFAULT 0, message TEXT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_offer_request_business (request_id,business_id), KEY idx_offer_request (tenant_id,request_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_saved_utilities_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL,
 provider VARCHAR(100) NOT NULL, label VARCHAR(120) NOT NULL, reference_last4 VARCHAR(8) NULL,
 due_day TINYINT NOT NULL DEFAULT 1, reminder_days TINYINT NOT NULL DEFAULT 3, enabled TINYINT(1) NOT NULL DEFAULT 1,
 last_alert_month CHAR(7) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_utility_user (tenant_id,user_id,enabled,due_day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_utility_alerts_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL,
 alert_type VARCHAR(60) NOT NULL, title VARCHAR(190) NOT NULL, body VARCHAR(700) NULL, target_url VARCHAR(500) NULL,
 read_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_alert_user (tenant_id,user_id,read_at,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_lost_found_v1030 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, requester_hash CHAR(64) NOT NULL,
 item_type VARCHAR(20) NOT NULL, title VARCHAR(160) NOT NULL, description TEXT NOT NULL, area VARCHAR(160) NULL,
 contact_phone VARCHAR(50) NULL, public_contact TINYINT(1) NOT NULL DEFAULT 0, status VARCHAR(30) NOT NULL DEFAULT 'pending',
 moderated_by BIGINT NULL, moderated_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_lostfound_tenant (tenant_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO customer_utility_settings_v1030(tenant_id)
SELECT id FROM tenants;

-- Extend the active Smart Homepage Builder when present. The admin can hide/reorder this section.
INSERT IGNORE INTO homepage_sections_v1010(tenant_id,section_key,title,subtitle,enabled,sort_order,display_mode,record_limit,featured_only,config_json)
SELECT id,'daily_utility','What do you need today?','Daily utilities, nearby help, price comparison and smart service requests.',1,22,'feature',6,0,'{}' FROM tenants;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.3.0'),
('customer_utility_version','10.3.0'),
('customer_utility_public_url','/my-shahkot.php'),
('new_module_default_placement','sidebar')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
