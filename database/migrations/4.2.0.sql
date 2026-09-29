-- ShahkotPK v4.2.0 Mobile App + Seller App Readiness

CREATE TABLE IF NOT EXISTS mobile_app_releases (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 app_type ENUM('customer','seller') NOT NULL,
 platform ENUM('android','ios') NOT NULL,
 version_name VARCHAR(60) NOT NULL,
 build_number INT NOT NULL DEFAULT 1,
 min_supported_version VARCHAR(60) NULL,
 force_update TINYINT(1) NOT NULL DEFAULT 0,
 store_url VARCHAR(900) NULL,
 release_notes TEXT NULL,
 status ENUM('draft','active','retired') NOT NULL DEFAULT 'active',
 published_at DATETIME NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_mar_app (app_type,platform,status,build_number),
 CONSTRAINT fk_mar_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_app_devices (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 device_id VARCHAR(190) NOT NULL,
 app_type ENUM('customer','seller') NOT NULL DEFAULT 'customer',
 platform ENUM('android','ios','web') NOT NULL DEFAULT 'android',
 device_name VARCHAR(190) NULL,
 os_version VARCHAR(90) NULL,
 app_version VARCHAR(60) NULL,
 build_number INT NULL,
 locale VARCHAR(30) NULL,
 timezone VARCHAR(80) NULL,
 push_token VARCHAR(900) NULL,
 last_ip VARCHAR(64) NULL,
 last_seen_at DATETIME NULL,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_mad_user_device (user_id,device_id,app_type),
 INDEX idx_mad_seen (app_type,status,last_seen_at),
 CONSTRAINT fk_mad_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_api_refresh_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 device_id VARCHAR(190) NOT NULL,
 app_type ENUM('customer','seller') NOT NULL DEFAULT 'customer',
 token_hash CHAR(64) NOT NULL UNIQUE,
 token_prefix VARCHAR(20) NOT NULL,
 expires_at DATETIME NOT NULL,
 last_used_at DATETIME NULL,
 revoked_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_mart_user (user_id,device_id,revoked_at,expires_at),
 CONSTRAINT fk_mart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_app_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 device_id VARCHAR(190) NULL,
 app_type ENUM('customer','seller') NOT NULL DEFAULT 'customer',
 event_key VARCHAR(100) NOT NULL,
 screen_name VARCHAR(120) NULL,
 app_version VARCHAR(60) NULL,
 meta_json LONGTEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_mae_event (app_type,event_key,created_at),
 INDEX idx_mae_user (user_id,created_at),
 CONSTRAINT fk_mae_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_api_idempotency (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 idempotency_key VARCHAR(120) NOT NULL,
 endpoint VARCHAR(120) NOT NULL,
 response_json LONGTEXT NOT NULL,
 expires_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_mobile_idem (user_id,idempotency_key,endpoint),
 INDEX idx_mobile_idem_expiry (expires_at),
 CONSTRAINT fk_mobile_idem_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('mobile_app_suite_version','420'),
('mobile_api_v2_enabled','1'),
('api_v2_enabled','1'),
('customer_app_enabled','1'),
('seller_app_enabled','1'),
('customer_app_name','ShahkotPK'),
('seller_app_name','ShahkotPK Seller'),
('customer_app_package','com.shahkotpk.app'),
('seller_app_package','com.shahkotpk.seller'),
('mobile_access_token_days','7'),
('mobile_refresh_token_days','90'),
('mobile_api_rate_limit_per_minute','120'),
('mobile_app_maintenance','0'),
('mobile_app_support_url','/support.php'),
('mobile_deep_link_base','https://shahkotpk.com'),
('mobile_seller_products_write_enabled','1'),
('mobile_seller_orders_write_enabled','1'),
('mobile_seller_inventory_write_enabled','1'),
('mobile_seller_payout_request_enabled','1'),
('public_header_version','420')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
