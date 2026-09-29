-- ShahkotPK v10.5.0 — Local Deals, Coupons & Loyalty Wallet
CREATE TABLE IF NOT EXISTS local_deal_settings_v1050 (
 tenant_id BIGINT NOT NULL,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 merchant_self_service TINYINT(1) NOT NULL DEFAULT 1,
 approval_required TINYINT(1) NOT NULL DEFAULT 1,
 loyalty_enabled TINYINT(1) NOT NULL DEFAULT 1,
 homepage_section_enabled TINYINT(1) NOT NULL DEFAULT 1,
 expiry_alerts_enabled TINYINT(1) NOT NULL DEFAULT 1,
 points_per_redemption INT NOT NULL DEFAULT 10,
 updated_by BIGINT NULL, updated_at DATETIME NULL,
 PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_deals_v1050 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, business_id BIGINT NOT NULL DEFAULT 0,
 owner_user_id BIGINT NOT NULL DEFAULT 0, title VARCHAR(190) NOT NULL, description TEXT NULL,
 coupon_code VARCHAR(40) NULL, discount_type VARCHAR(24) NOT NULL DEFAULT 'percent', discount_value DECIMAL(12,2) NOT NULL DEFAULT 0,
 minimum_spend DECIMAL(12,2) NOT NULL DEFAULT 0, image_url VARCHAR(500) NULL, terms_text TEXT NULL,
 starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, usage_limit INT NOT NULL DEFAULT 0, per_user_limit INT NOT NULL DEFAULT 1,
 claimed_count INT NOT NULL DEFAULT 0, redeemed_count INT NOT NULL DEFAULT 0, featured TINYINT(1) NOT NULL DEFAULT 0,
 status VARCHAR(30) NOT NULL DEFAULT 'pending', created_by BIGINT NULL, approved_by BIGINT NULL, approved_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_deals_live (tenant_id,status,starts_at,ends_at,featured), KEY idx_deals_owner (tenant_id,owner_user_id,status),
 KEY idx_deals_business (tenant_id,business_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_deal_claims_v1050 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, deal_id BIGINT NOT NULL, user_id BIGINT NOT NULL,
 claim_code VARCHAR(32) NOT NULL, status VARCHAR(24) NOT NULL DEFAULT 'saved', reward_points INT NOT NULL DEFAULT 0,
 claimed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, redeemed_at DATETIME NULL, redeemed_by BIGINT NULL, expires_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_deal_claim_code (claim_code), KEY idx_deal_claim_user (tenant_id,user_id,status,claimed_at),
 KEY idx_deal_claim_deal (tenant_id,deal_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_loyalty_accounts_v1050 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, points_balance INT NOT NULL DEFAULT 0,
 lifetime_points INT NOT NULL DEFAULT 0, updated_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_local_loyalty_user (tenant_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_loyalty_ledger_v1050 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, event_key VARCHAR(48) NOT NULL,
 points INT NOT NULL, reference_type VARCHAR(40) NULL, reference_id BIGINT NULL, note VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_local_loyalty_user (tenant_id,user_id,created_at), KEY idx_local_loyalty_ref (tenant_id,reference_type,reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO local_deal_settings_v1050(tenant_id) SELECT id FROM tenants;
INSERT IGNORE INTO homepage_sections_v1010(tenant_id,section_key,title,subtitle,enabled,sort_order,display_mode,record_limit,featured_only,config_json)
SELECT id,'deal_wallet','Deals & Rewards','Save local offers, claim coupons and earn loyalty points.',1,26,'slider',6,0,'{}' FROM tenants;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.5.0'),
('local_deals_loyalty_version','10.5.0'),
('local_deals_public_url','/deals-v1050.php'),
('local_loyalty_wallet_url','/my-wallet-v1050.php'),
('new_module_default_placement','sidebar')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
