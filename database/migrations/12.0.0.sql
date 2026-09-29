-- ShahkotPK v12.0.0 — Customer Super App + Personal AI Concierge
CREATE TABLE IF NOT EXISTS customer_superapp_settings_v1200 (
 tenant_id BIGINT NOT NULL, enabled TINYINT NOT NULL DEFAULT 1, ai_enabled TINYINT NOT NULL DEFAULT 1, guest_mode TINYINT NOT NULL DEFAULT 1,
 do_it_for_me TINYINT NOT NULL DEFAULT 1, max_business_matches INT NOT NULL DEFAULT 5, show_unified_inbox TINYINT NOT NULL DEFAULT 1,
 show_wallet TINYINT NOT NULL DEFAULT 1, show_recent TINYINT NOT NULL DEFAULT 1, show_bookings TINYINT NOT NULL DEFAULT 1,
 offer_expiry_hours INT NOT NULL DEFAULT 72, privacy_days INT NOT NULL DEFAULT 180, updated_at DATETIME NULL,
 PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS customer_superapp_preferences_v1200 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, visitor_key CHAR(64) NOT NULL DEFAULT '', area VARCHAR(160) NULL,
 interests_json LONGTEXT NULL, notification_preferences_json LONGTEXT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_pref_user(tenant_id,user_id), KEY idx_pref_visitor(tenant_id,visitor_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS customer_intents_v1200 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, visitor_key CHAR(64) NOT NULL, public_token CHAR(48) NOT NULL,
 category_key VARCHAR(60) NOT NULL DEFAULT 'general', need_text TEXT NOT NULL, budget_min DECIMAL(14,2) NOT NULL DEFAULT 0, budget_max DECIMAL(14,2) NOT NULL DEFAULT 0,
 area VARCHAR(160) NULL, urgency VARCHAR(30) NOT NULL DEFAULT 'normal', contact_name VARCHAR(120) NOT NULL, contact_phone VARCHAR(60) NOT NULL,
 matched_count INT NOT NULL DEFAULT 0, status VARCHAR(30) NOT NULL DEFAULT 'open', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_intent_token(public_token), KEY idx_intent_customer(tenant_id,user_id,status,id), KEY idx_intent_visitor(tenant_id,visitor_key,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS customer_intent_matches_v1200 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, intent_id BIGINT NOT NULL, business_id BIGINT NOT NULL, match_score INT NOT NULL DEFAULT 0,
 status VARCHAR(30) NOT NULL DEFAULT 'matched', offer_price DECIMAL(14,2) NOT NULL DEFAULT 0, offer_message TEXT NULL, offered_by BIGINT NULL,
 responded_at DATETIME NULL, expires_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_intent_business(intent_id,business_id), KEY idx_match_business(tenant_id,business_id,status,id), KEY idx_match_intent(intent_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS customer_reminders_v1200 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, title VARCHAR(190) NOT NULL, body VARCHAR(800) NULL,
 target_url VARCHAR(700) NULL, remind_at DATETIME NOT NULL, status VARCHAR(30) NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_reminder_due(tenant_id,status,remind_at), KEY idx_reminder_user(tenant_id,user_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO settings(setting_key,setting_value) VALUES ('installed_app_version','12.0.0'),('customer_superapp_version','12.0.0') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);