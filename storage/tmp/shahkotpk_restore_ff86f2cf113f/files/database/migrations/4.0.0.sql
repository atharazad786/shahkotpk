-- ShahkotPK v4.0.0 Unified Commercial Platform
-- v3.7 Security & Reliability + v3.8 AI + v4.0 Multi-City/Mobile API/Seller POS

CREATE TABLE IF NOT EXISTS security_login_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 login_identifier VARCHAR(190) NULL,
 source VARCHAR(40) NOT NULL DEFAULT 'web',
 ip_address VARCHAR(64) NULL,
 user_agent VARCHAR(500) NULL,
 success TINYINT(1) NOT NULL DEFAULT 0,
 failure_reason VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_sle_ip (ip_address,created_at), INDEX idx_sle_user (user_id,created_at), INDEX idx_sle_success (success,created_at),
 CONSTRAINT fk_sle_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_otp_codes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 purpose VARCHAR(50) NOT NULL DEFAULT 'login',
 code_hash VARCHAR(255) NOT NULL,
 attempts INT NOT NULL DEFAULT 0,
 expires_at DATETIME NOT NULL,
 consumed_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_otp_user (user_id,purpose,expires_at),
 CONSTRAINT fk_otp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_sessions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 session_hash CHAR(64) NOT NULL UNIQUE,
 device_name VARCHAR(160) NULL,
 ip_address VARCHAR(64) NULL,
 user_agent VARCHAR(500) NULL,
 last_seen_at DATETIME NOT NULL,
 revoked_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_sec_session_user (user_id,revoked_at,last_seen_at),
 CONSTRAINT fk_sec_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_ip_blocks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ip_address VARCHAR(64) NOT NULL UNIQUE,
 reason VARCHAR(255) NULL,
 expires_at DATETIME NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_sec_block_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_health_snapshots (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 status VARCHAR(30) NOT NULL DEFAULT 'ok',
 php_version VARCHAR(60) NULL,
 db_status VARCHAR(60) NULL,
 disk_free_mb BIGINT NULL,
 queue_pending INT NOT NULL DEFAULT 0,
 queue_failed INT NOT NULL DEFAULT 0,
 runtime_log_kb BIGINT NOT NULL DEFAULT 0,
 meta_text MEDIUMTEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_health_created (created_at,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scheduled_backup_rules (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(160) NOT NULL,
 backup_type ENUM('database','files','both') NOT NULL DEFAULT 'database',
 frequency ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
 run_hour TINYINT UNSIGNED NOT NULL DEFAULT 3,
 retention_days INT NOT NULL DEFAULT 14,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 last_run_at DATETIME NULL,
 next_run_at DATETIME NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_backup_rule_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_request_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 feature VARCHAR(60) NOT NULL,
 provider VARCHAR(60) NOT NULL DEFAULT 'local',
 model VARCHAR(120) NULL,
 input_excerpt VARCHAR(700) NULL,
 success TINYINT(1) NOT NULL DEFAULT 1,
 latency_ms INT NOT NULL DEFAULT 0,
 error_text VARCHAR(700) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_ai_log (feature,created_at),
 CONSTRAINT fk_ai_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_content_drafts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 content_type VARCHAR(60) NOT NULL,
 language VARCHAR(20) NOT NULL DEFAULT 'en',
 topic VARCHAR(300) NOT NULL,
 generated_title VARCHAR(300) NULL,
 generated_body LONGTEXT NULL,
 meta_text MEDIUMTEXT NULL,
 status ENUM('draft','used','archived') NOT NULL DEFAULT 'draft',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_ai_draft_user (user_id,status,created_at),
 CONSTRAINT fk_ai_draft_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_recommendation_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 entity_type VARCHAR(40) NOT NULL,
 entity_id BIGINT UNSIGNED NOT NULL,
 event_type VARCHAR(40) NOT NULL DEFAULT 'impression',
 score DECIMAL(9,4) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_ai_rec_user (user_id,created_at), INDEX idx_ai_rec_entity (entity_type,entity_id,created_at),
 CONSTRAINT fk_ai_rec_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_franchises (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 city_id BIGINT UNSIGNED NOT NULL UNIQUE,
 manager_user_id BIGINT UNSIGNED NULL,
 franchise_name VARCHAR(190) NULL,
 public_slug VARCHAR(190) NULL,
 custom_domain VARCHAR(255) NULL,
 revenue_share_percent DECIMAL(6,2) NOT NULL DEFAULT 20,
 marketplace_share_percent DECIMAL(6,2) NOT NULL DEFAULT 20,
 ad_share_percent DECIMAL(6,2) NOT NULL DEFAULT 20,
 subscription_share_percent DECIMAL(6,2) NOT NULL DEFAULT 20,
 support_email VARCHAR(190) NULL,
 support_phone VARCHAR(60) NULL,
 status ENUM('draft','active','suspended') NOT NULL DEFAULT 'draft',
 settings_text MEDIUMTEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_franchise_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
 CONSTRAINT fk_franchise_manager FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS franchise_revenue_ledger (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 franchise_id BIGINT UNSIGNED NOT NULL,
 source_type VARCHAR(50) NOT NULL,
 source_id BIGINT UNSIGNED NULL,
 gross_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 platform_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 franchise_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 reference VARCHAR(120) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_fr_rev (franchise_id,created_at,source_type),
 CONSTRAINT fk_fr_rev_franchise FOREIGN KEY (franchise_id) REFERENCES city_franchises(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_api_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 token_prefix VARCHAR(20) NOT NULL,
 device_name VARCHAR(160) NULL,
 scopes VARCHAR(700) NOT NULL DEFAULT 'profile,catalog,orders',
 last_used_at DATETIME NULL,
 expires_at DATETIME NULL,
 revoked_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_mobile_token_user (user_id,revoked_at,expires_at),
 CONSTRAINT fk_mobile_token_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_api_challenges (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 challenge_hash CHAR(64) NOT NULL UNIQUE,
 device_name VARCHAR(160) NULL,
 expires_at DATETIME NOT NULL,
 consumed_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_mobile_challenge_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_api_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 endpoint VARCHAR(190) NOT NULL,
 method VARCHAR(12) NOT NULL,
 status_code INT NOT NULL DEFAULT 200,
 ip_address VARCHAR(64) NULL,
 duration_ms INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_api_log (created_at,endpoint,status_code),
 CONSTRAINT fk_api_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_shifts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seller_user_id BIGINT UNSIGNED NOT NULL,
 opened_by BIGINT UNSIGNED NOT NULL,
 closed_by BIGINT UNSIGNED NULL,
 opening_cash DECIMAL(14,2) NOT NULL DEFAULT 0,
 closing_cash DECIMAL(14,2) NULL,
 status ENUM('open','closed') NOT NULL DEFAULT 'open',
 opened_at DATETIME NOT NULL,
 closed_at DATETIME NULL,
 INDEX idx_pos_shift (seller_user_id,status,opened_at),
 CONSTRAINT fk_pos_shift_seller FOREIGN KEY (seller_user_id) REFERENCES users(id),
 CONSTRAINT fk_pos_shift_open FOREIGN KEY (opened_by) REFERENCES users(id),
 CONSTRAINT fk_pos_shift_close FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_sales (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sale_number VARCHAR(60) NOT NULL UNIQUE,
 seller_user_id BIGINT UNSIGNED NOT NULL,
 staff_user_id BIGINT UNSIGNED NOT NULL,
 shift_id BIGINT UNSIGNED NULL,
 customer_name VARCHAR(160) NULL,
 customer_phone VARCHAR(60) NULL,
 subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
 discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 payment_method ENUM('cash','bank','card','wallet','other') NOT NULL DEFAULT 'cash',
 status ENUM('completed','void','refunded') NOT NULL DEFAULT 'completed',
 notes VARCHAR(700) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_pos_sale_seller (seller_user_id,created_at,status),
 CONSTRAINT fk_pos_sale_seller FOREIGN KEY (seller_user_id) REFERENCES users(id),
 CONSTRAINT fk_pos_sale_staff FOREIGN KEY (staff_user_id) REFERENCES users(id),
 CONSTRAINT fk_pos_sale_shift FOREIGN KEY (shift_id) REFERENCES pos_shifts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_sale_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sale_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(220) NOT NULL,
 quantity INT NOT NULL DEFAULT 1,
 unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
 line_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 CONSTRAINT fk_pos_item_sale FOREIGN KEY (sale_id) REFERENCES pos_sales(id) ON DELETE CASCADE,
 CONSTRAINT fk_pos_item_product FOREIGN KEY (product_id) REFERENCES store_products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO accounting_accounts(code,name,account_type,system_key,description,status) VALUES
('1050','Seller POS Clearing','asset','seller_pos_clearing','Cash/bank/card value collected by sellers through local POS sales.',1),
('4050','Seller POS Sales Revenue','revenue','pos_sales_revenue','Gross local point-of-sale revenue recorded for seller reporting.',1);

INSERT INTO settings(setting_key,setting_value) VALUES
('unified_platform_version','400'),
('security_suite_enabled','1'),('security_2fa_enabled','0'),('security_2fa_roles','admin,editor,shopkeeper'),('security_otp_minutes','10'),('security_login_window_minutes','15'),('security_login_max_failures','8'),('security_headers_enabled','1'),('security_csp_report_only','1'),('security_session_tracking_enabled','1'),('security_backup_scheduler_enabled','1'),('security_backup_hour','3'),('security_backup_retention_days','14'),
('ai_suite_enabled','1'),('ai_provider','local'),('ai_endpoint',''),('ai_api_key',''),('ai_model',''),('ai_timeout_seconds','25'),('ai_smart_search_enabled','1'),('ai_content_enabled','1'),('ai_recommendations_enabled','1'),
('franchise_suite_enabled','1'),('mobile_api_enabled','1'),('mobile_api_token_days','90'),('seller_pos_enabled','1'),('pos_receipt_footer','Thank you for shopping local with ShahkotPK.')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
