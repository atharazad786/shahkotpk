-- ShahkotPK v10.4.0 — Smart Local Assistant + Unified Booking Center
CREATE TABLE IF NOT EXISTS local_assistant_settings_v1040 (
 tenant_id BIGINT NOT NULL,
 assistant_enabled TINYINT(1) NOT NULL DEFAULT 1,
 booking_enabled TINYINT(1) NOT NULL DEFAULT 1,
 guest_booking_enabled TINYINT(1) NOT NULL DEFAULT 1,
 provider_inbox_enabled TINYINT(1) NOT NULL DEFAULT 1,
 homepage_section_enabled TINYINT(1) NOT NULL DEFAULT 1,
 search_logging_enabled TINYINT(1) NOT NULL DEFAULT 1,
 booking_reminders_enabled TINYINT(1) NOT NULL DEFAULT 1,
 max_results INT NOT NULL DEFAULT 18,
 booking_min_notice_minutes INT NOT NULL DEFAULT 30,
 updated_by BIGINT NULL, updated_at DATETIME NULL,
 PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_assistant_suggestions_v1040 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL,
 label VARCHAR(120) NOT NULL, query_text VARCHAR(250) NOT NULL, icon VARCHAR(16) NULL,
 sort_order INT NOT NULL DEFAULT 100, enabled TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_assistant_suggestion (tenant_id,label), KEY idx_assistant_suggestion (tenant_id,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_assistant_searches_v1040 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL,
 visitor_hash CHAR(64) NOT NULL, query_text VARCHAR(250) NOT NULL, intent_json TEXT NULL,
 result_count INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_assistant_search (tenant_id,created_at), KEY idx_assistant_user (user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unified_bookings_v1040 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, booking_no VARCHAR(40) NOT NULL,
 user_id BIGINT NULL, visitor_hash CHAR(64) NOT NULL,
 entity_type VARCHAR(30) NOT NULL, entity_id BIGINT NOT NULL, entity_title VARCHAR(190) NOT NULL,
 provider_user_id BIGINT NOT NULL DEFAULT 0, business_id BIGINT NOT NULL DEFAULT 0,
 customer_name VARCHAR(120) NOT NULL, customer_phone VARCHAR(50) NOT NULL, customer_whatsapp VARCHAR(50) NULL,
 requested_at DATETIME NOT NULL, party_size INT NOT NULL DEFAULT 1, notes TEXT NULL,
 provider_note VARCHAR(800) NULL, status VARCHAR(40) NOT NULL DEFAULT 'pending', confirmed_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_booking_no (booking_no),
 KEY idx_booking_customer (tenant_id,user_id,status,requested_at),
 KEY idx_booking_provider (tenant_id,provider_user_id,status,requested_at),
 KEY idx_booking_entity (tenant_id,entity_type,entity_id,requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unified_booking_status_log_v1040 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, booking_id BIGINT NOT NULL,
 status VARCHAR(40) NOT NULL, note VARCHAR(500) NULL, actor_user_id BIGINT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_booking_log (tenant_id,booking_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_availability_v1040 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, provider_user_id BIGINT NOT NULL,
 entity_type VARCHAR(30) NOT NULL, entity_id BIGINT NOT NULL, day_of_week TINYINT NOT NULL,
 start_time TIME NOT NULL, end_time TIME NOT NULL, slot_minutes INT NOT NULL DEFAULT 30,
 enabled TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_availability (tenant_id,provider_user_id,entity_type,entity_id,day_of_week),
 KEY idx_availability_entity (tenant_id,entity_type,entity_id,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO local_assistant_settings_v1040(tenant_id) SELECT id FROM tenants;
INSERT IGNORE INTO local_assistant_suggestions_v1040(tenant_id,label,query_text,icon,sort_order,enabled)
SELECT id,'Find a doctor','doctor appointment today','✚',10,1 FROM tenants;
INSERT IGNORE INTO local_assistant_suggestions_v1040(tenant_id,label,query_text,icon,sort_order,enabled)
SELECT id,'Book a restaurant','family restaurant dinner','◉',20,1 FROM tenants;
INSERT IGNORE INTO local_assistant_suggestions_v1040(tenant_id,label,query_text,icon,sort_order,enabled)
SELECT id,'Find a service','home service electrician','⌂',30,1 FROM tenants;
INSERT IGNORE INTO local_assistant_suggestions_v1040(tenant_id,label,query_text,icon,sort_order,enabled)
SELECT id,'Compare a product','mobile phone price','⇄',40,1 FROM tenants;
INSERT IGNORE INTO local_assistant_suggestions_v1040(tenant_id,label,query_text,icon,sort_order,enabled)
SELECT id,'Find property','house for rent','▦',50,1 FROM tenants;
INSERT IGNORE INTO local_assistant_suggestions_v1040(tenant_id,label,query_text,icon,sort_order,enabled)
SELECT id,'Book a hotel','hotel room stay','◇',60,1 FROM tenants;

INSERT IGNORE INTO homepage_sections_v1010(tenant_id,section_key,title,subtitle,enabled,sort_order,display_mode,record_limit,featured_only,config_json)
SELECT id,'local_assistant','Ask ShahkotPK','Tell us what you need. Search local options and book from one place.',1,24,'feature',6,0,'{}' FROM tenants;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.4.0'),
('local_assistant_booking_version','10.4.0'),
('local_assistant_public_url','/assistant-v1040.php'),
('unified_booking_public_url','/my-bookings-v1040.php'),
('new_module_default_placement','sidebar')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
