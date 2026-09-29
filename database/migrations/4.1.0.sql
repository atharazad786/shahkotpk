-- ShahkotPK v4.1.0 Commercial Growth & Monetization Suite

CREATE TABLE IF NOT EXISTS self_service_ad_campaigns (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 advertisement_id BIGINT UNSIGNED NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 business_id BIGINT UNSIGNED NULL,
 title VARCHAR(190) NOT NULL,
 placement VARCHAR(90) NOT NULL DEFAULT 'homepage',
 city_id BIGINT UNSIGNED NULL,
 category_id BIGINT UNSIGNED NULL,
 target_url VARCHAR(700) NULL,
 image_url VARCHAR(700) NULL,
 daily_budget DECIMAL(14,2) NOT NULL DEFAULT 0,
 total_budget DECIMAL(14,2) NOT NULL DEFAULT 0,
 spent_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
 clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
 starts_at DATETIME NULL,
 ends_at DATETIME NULL,
 status ENUM('draft','pending','approved','active','paused','completed','rejected') NOT NULL DEFAULT 'draft',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_ss_ad_user (user_id,status), INDEX idx_ss_ad_dates (status,starts_at,ends_at),
 CONSTRAINT fk_ss_ad_delivery FOREIGN KEY (advertisement_id) REFERENCES advertisements(id) ON DELETE SET NULL,
 CONSTRAINT fk_ss_ad_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_ss_ad_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
 CONSTRAINT fk_ss_ad_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
 CONSTRAINT fk_ss_ad_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS listing_boosts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 entity_type ENUM('business','product','property','classified','job','deal') NOT NULL,
 entity_id BIGINT UNSIGNED NOT NULL,
 boost_type ENUM('featured','top','highlight','homepage') NOT NULL DEFAULT 'featured',
 amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 starts_at DATETIME NOT NULL,
 ends_at DATETIME NOT NULL,
 status ENUM('pending','active','expired','cancelled','rejected') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_boost_entity (entity_type,entity_id,status), INDEX idx_boost_user (user_id,status), INDEX idx_boost_dates (status,starts_at,ends_at),
 CONSTRAINT fk_boost_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_renewal_profiles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 subscription_id BIGINT UNSIGNED NOT NULL UNIQUE,
 auto_renew TINYINT(1) NOT NULL DEFAULT 0,
 grace_days INT NOT NULL DEFAULT 7,
 reminder_days INT NOT NULL DEFAULT 14,
 payment_method VARCHAR(60) NULL,
 last_reminder_at DATETIME NULL,
 last_attempt_at DATETIME NULL,
 next_action_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_renew_profile_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_renewal_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 subscription_id BIGINT UNSIGNED NOT NULL,
 event_type VARCHAR(60) NOT NULL,
 amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 status VARCHAR(40) NOT NULL DEFAULT 'recorded',
 message VARCHAR(700) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_renew_event_sub (subscription_id,created_at),
 CONSTRAINT fk_renew_event_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS monetization_commission_rules (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(160) NOT NULL,
 product_type ENUM('any','new','used','digital','auction') NOT NULL DEFAULT 'any',
 category_id BIGINT UNSIGNED NULL,
 seller_user_id BIGINT UNSIGNED NULL,
 plan_id BIGINT UNSIGNED NULL,
 percent_rate DECIMAL(7,3) NOT NULL DEFAULT 5,
 flat_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
 priority INT NOT NULL DEFAULT 100,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_mcr_priority (status,priority),
 CONSTRAINT fk_mcr_cat FOREIGN KEY (category_id) REFERENCES store_categories(id) ON DELETE SET NULL,
 CONSTRAINT fk_mcr_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_mcr_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qr_assets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 asset_type ENUM('business','product','coupon','event','invoice','custom') NOT NULL DEFAULT 'custom',
 entity_id BIGINT UNSIGNED NULL,
 title VARCHAR(190) NOT NULL,
 target_url VARCHAR(900) NOT NULL,
 foreground VARCHAR(20) NOT NULL DEFAULT '111827',
 background VARCHAR(20) NOT NULL DEFAULT 'ffffff',
 scan_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_qr_owner (user_id,asset_type,status),
 CONSTRAINT fk_qr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS franchise_settlements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 franchise_id BIGINT UNSIGNED NOT NULL,
 period_start DATE NOT NULL,
 period_end DATE NOT NULL,
 gross_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 platform_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 franchise_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('draft','approved','paid','void') NOT NULL DEFAULT 'draft',
 notes VARCHAR(700) NULL,
 approved_by BIGINT UNSIGNED NULL,
 paid_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_franchise_period (franchise_id,period_start,period_end),
 CONSTRAINT fk_fs_franchise FOREIGN KEY (franchise_id) REFERENCES city_franchises(id) ON DELETE CASCADE,
 CONSTRAINT fk_fs_admin FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bulk_import_jobs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 job_type ENUM('businesses','products','customers','properties','classifieds') NOT NULL,
 original_name VARCHAR(255) NULL,
 file_path VARCHAR(700) NULL,
 total_rows INT NOT NULL DEFAULT 0,
 processed_rows INT NOT NULL DEFAULT 0,
 success_rows INT NOT NULL DEFAULT 0,
 failed_rows INT NOT NULL DEFAULT 0,
 status ENUM('uploaded','processing','completed','failed') NOT NULL DEFAULT 'uploaded',
 error_text MEDIUMTEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_bulk_job (user_id,status,created_at),
 CONSTRAINT fk_bulk_job_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_library (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 file_name VARCHAR(255) NOT NULL,
 stored_path VARCHAR(700) NOT NULL,
 mime_type VARCHAR(120) NULL,
 file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
 width INT NULL,
 height INT NULL,
 checksum CHAR(64) NULL,
 alt_text VARCHAR(300) NULL,
 title VARCHAR(300) NULL,
 folder VARCHAR(190) NOT NULL DEFAULT 'general',
 usage_count INT NOT NULL DEFAULT 0,
 status ENUM('active','archived') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_media_folder (folder,status,created_at), INDEX idx_media_checksum (checksum),
 CONSTRAINT fk_media_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS redirect_rules (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 source_path VARCHAR(700) NOT NULL UNIQUE,
 target_url VARCHAR(900) NOT NULL,
 status_code SMALLINT NOT NULL DEFAULT 301,
 hit_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_redirect_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS not_found_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 path VARCHAR(900) NOT NULL,
 referer VARCHAR(900) NULL,
 ip_address VARCHAR(64) NULL,
 user_agent VARCHAR(500) NULL,
 hit_count BIGINT UNSIGNED NOT NULL DEFAULT 1,
 first_seen_at DATETIME NOT NULL,
 last_seen_at DATETIME NOT NULL,
 UNIQUE KEY uq_404_path (path(190)), INDEX idx_404_last (last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seo_health_scans (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 score INT NOT NULL DEFAULT 0,
 checked_pages INT NOT NULL DEFAULT 0,
 missing_titles INT NOT NULL DEFAULT 0,
 missing_descriptions INT NOT NULL DEFAULT 0,
 broken_routes INT NOT NULL DEFAULT 0,
 details_json LONGTEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_seo_scan (created_at),
 CONSTRAINT fk_seo_scan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS outbound_campaigns (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 created_by BIGINT UNSIGNED NOT NULL,
 channel ENUM('email','whatsapp','notification') NOT NULL,
 title VARCHAR(220) NOT NULL,
 message_text LONGTEXT NOT NULL,
 audience_type ENUM('all','role','city','business','custom') NOT NULL DEFAULT 'all',
 audience_value VARCHAR(255) NULL,
 scheduled_at DATETIME NULL,
 status ENUM('draft','scheduled','running','completed','cancelled','failed') NOT NULL DEFAULT 'draft',
 total_recipients INT NOT NULL DEFAULT 0,
 sent_count INT NOT NULL DEFAULT 0,
 failed_count INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_campaign_status (status,scheduled_at),
 CONSTRAINT fk_campaign_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_rules (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(190) NOT NULL,
 event_key VARCHAR(100) NOT NULL,
 channel ENUM('notification','email','whatsapp','multi') NOT NULL DEFAULT 'notification',
 audience_role VARCHAR(60) NULL,
 template_text VARCHAR(1200) NOT NULL,
 cooldown_minutes INT NOT NULL DEFAULT 60,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 last_run_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_notify_rule (event_key,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unified_inbox_threads (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 business_id BIGINT UNSIGNED NULL,
 subject VARCHAR(250) NOT NULL,
 source ENUM('support','lead','business_message','whatsapp','system') NOT NULL DEFAULT 'support',
 priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
 status ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
 assigned_to BIGINT UNSIGNED NULL,
 last_message_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_inbox_status (status,priority,last_message_at), INDEX idx_inbox_user (user_id),
 CONSTRAINT fk_inbox_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT fk_inbox_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
 CONSTRAINT fk_inbox_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unified_inbox_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 thread_id BIGINT UNSIGNED NOT NULL,
 sender_user_id BIGINT UNSIGNED NULL,
 sender_type ENUM('user','staff','system','external') NOT NULL DEFAULT 'user',
 message_text LONGTEXT NOT NULL,
 attachment_url VARCHAR(700) NULL,
 is_internal TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_inbox_msg (thread_id,created_at),
 CONSTRAINT fk_inbox_msg_thread FOREIGN KEY (thread_id) REFERENCES unified_inbox_threads(id) ON DELETE CASCADE,
 CONSTRAINT fk_inbox_msg_user FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS regression_test_runs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 app_version VARCHAR(30) NOT NULL,
 total_tests INT NOT NULL DEFAULT 0,
 passed_tests INT NOT NULL DEFAULT 0,
 failed_tests INT NOT NULL DEFAULT 0,
 duration_ms INT NOT NULL DEFAULT 0,
 results_json LONGTEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_regression_run (created_at),
 CONSTRAINT fk_regression_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_push_devices_v2 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 device_id VARCHAR(190) NOT NULL,
 platform ENUM('android','ios','web') NOT NULL DEFAULT 'web',
 push_token VARCHAR(900) NULL,
 app_version VARCHAR(60) NULL,
 last_seen_at DATETIME NULL,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_push_device (user_id,device_id),
 CONSTRAINT fk_push_v2_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_receipt_profiles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seller_user_id BIGINT UNSIGNED NOT NULL UNIQUE,
 business_name VARCHAR(190) NULL,
 footer_text VARCHAR(500) NULL,
 show_qr TINYINT(1) NOT NULL DEFAULT 1,
 show_barcode TINYINT(1) NOT NULL DEFAULT 1,
 paper_width ENUM('58mm','80mm','a4') NOT NULL DEFAULT '80mm',
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_receipt_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO notification_rules(name,event_key,channel,template_text,cooldown_minutes,enabled) VALUES
('Auction ending soon','auction.ending','notification','An auction you follow is ending soon.',60,1),
('Subscription expiring','subscription.expiring','multi','Your ShahkotPK subscription is expiring soon.',1440,1),
('New business lead','lead.created','notification','You have received a new business inquiry.',5,1),
('Low stock','inventory.low','notification','A product is below its low-stock threshold.',60,1),
('Payment failed','payment.failed','multi','A payment needs your attention.',30,1);

INSERT INTO settings(setting_key,setting_value) VALUES
('commercial_growth_version','410'),
('self_service_ads_enabled','1'),('listing_boosts_enabled','1'),('subscription_auto_renew_enabled','1'),('subscription_grace_days','7'),('subscription_renewal_reminder_days','14'),
('monetization_commission_enabled','1'),('qr_center_enabled','1'),('qr_provider_base','https://quickchart.io/qr'),
('bulk_import_enabled','1'),('media_library_enabled','1'),('media_max_upload_mb','12'),('redirect_manager_enabled','1'),('seo_health_enabled','1'),
('campaign_center_enabled','1'),('notification_rules_enabled','1'),('unified_inbox_enabled','1'),('api_v2_enabled','1'),('regression_center_enabled','1'),
('seller_growth_dashboard_enabled','1'),('customer_super_dashboard_enabled','1'),('franchise_settlement_enabled','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
