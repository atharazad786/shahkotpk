-- ShahkotPK v11.0.0 — Unified Experience + AI Copilot
CREATE TABLE IF NOT EXISTS public_navigation_rules_v1100 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, nav_key VARCHAR(100) NOT NULL,
 label VARCHAR(120) NOT NULL, url VARCHAR(500) NOT NULL, group_key VARCHAR(30) NOT NULL DEFAULT 'primary',
 parent_key VARCHAR(100) NOT NULL DEFAULT '', priority VARCHAR(20) NOT NULL DEFAULT 'secondary', sort_order INT NOT NULL DEFAULT 100,
 enabled TINYINT(1) NOT NULL DEFAULT 1, updated_by BIGINT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_pubnav1100(tenant_id,nav_key), KEY idx_pubnav1100(tenant_id,group_key,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_copilot_settings_v1100 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, provider VARCHAR(40) NOT NULL DEFAULT 'local', model VARCHAR(120) NOT NULL DEFAULT 'gemini-3.7-flash',
 api_key_cipher TEXT NULL, public_enabled TINYINT(1) NOT NULL DEFAULT 1, admin_enabled TINYINT(1) NOT NULL DEFAULT 1,
 voice_enabled TINYINT(1) NOT NULL DEFAULT 1, tts_enabled TINYINT(1) NOT NULL DEFAULT 1, auto_train TINYINT(1) NOT NULL DEFAULT 1,
 training_interval_minutes INT NOT NULL DEFAULT 360, public_system_prompt TEXT NULL, admin_system_prompt TEXT NULL, updated_by BIGINT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_ai_settings_tenant(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_knowledge_v1100 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, scope VARCHAR(20) NOT NULL DEFAULT 'public', source_type VARCHAR(50) NOT NULL,
 source_key VARCHAR(190) NOT NULL, title VARCHAR(250) NOT NULL, content MEDIUMTEXT NOT NULL, url VARCHAR(700) NULL, checksum CHAR(64) NOT NULL,
 indexed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id), UNIQUE KEY uq_ai_knowledge(tenant_id,scope,source_type,source_key),
 KEY idx_ai_scope(tenant_id,scope), KEY idx_ai_title(tenant_id,title(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_knowledge_meta_v1100 (
 tenant_id BIGINT NOT NULL, last_built_at DATETIME NULL, last_version VARCHAR(40) NULL, item_count INT NOT NULL DEFAULT 0,
 PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_chat_messages_v1100 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, scope VARCHAR(20) NOT NULL DEFAULT 'public',
 question TEXT NOT NULL, answer MEDIUMTEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_ai_chat_tenant(tenant_id,scope,created_at), KEY idx_ai_chat_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.0.0'),
('unified_experience_version','11.0.0'),
('ai_copilot_version','11.0.0'),
('public_navigation_version','11.0.0'),
('sidebar_registry_mode','canonical_dedupe'),
('future_customer_modules_public_nav','manifest_grouped'),
('future_normal_admin_modules_sidebar','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
