-- ShahkotPK v12.3.0 — Commerce + Membership + Delivery Super Platform
CREATE TABLE IF NOT EXISTS commerce_settings_v1230 (
 tenant_id BIGINT NOT NULL, enabled TINYINT NOT NULL DEFAULT 1, guest_checkout TINYINT NOT NULL DEFAULT 1,
 cod_enabled TINYINT NOT NULL DEFAULT 1, advance_enabled TINYINT NOT NULL DEFAULT 1, wallet_enabled TINYINT NOT NULL DEFAULT 1,
 scheduled_orders TINYINT NOT NULL DEFAULT 1, returns_enabled TINYINT NOT NULL DEFAULT 1, disputes_enabled TINYINT NOT NULL DEFAULT 1,
 abandoned_cart_hours INT NOT NULL DEFAULT 12, default_delivery_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
 points_per_100 INT NOT NULL DEFAULT 1, updated_at DATETIME NULL, PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_carts_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, visitor_key CHAR(64) NOT NULL,
 status VARCHAR(24) NOT NULL DEFAULT 'active', currency CHAR(3) NOT NULL DEFAULT 'PKR', reminder_sent_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_cart_user(tenant_id,user_id,status,id), KEY idx_cart_visitor(tenant_id,visitor_key,status,id), KEY idx_cart_stale(tenant_id,status,updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_cart_items_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, cart_id BIGINT NOT NULL, tenant_id BIGINT NOT NULL, product_id BIGINT NOT NULL,
 business_id BIGINT NOT NULL, product_name VARCHAR(220) NOT NULL, image_url VARCHAR(700) NULL,
 unit_price DECIMAL(14,2) NOT NULL DEFAULT 0, qty INT NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_cart_product(cart_id,product_id), KEY idx_cart_items(cart_id,business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_addresses_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, label VARCHAR(60) NOT NULL DEFAULT 'Home',
 recipient_name VARCHAR(140) NOT NULL, phone VARCHAR(60) NOT NULL, address_line VARCHAR(500) NOT NULL, area VARCHAR(180) NULL,
 city VARCHAR(140) NOT NULL DEFAULT 'Shahkot', notes VARCHAR(500) NULL, is_default TINYINT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_address_user(tenant_id,user_id,is_default,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_orders_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_no VARCHAR(36) NOT NULL, public_token CHAR(48) NOT NULL,
 user_id BIGINT NULL, visitor_key CHAR(64) NOT NULL, customer_name VARCHAR(140) NOT NULL, customer_phone VARCHAR(60) NOT NULL,
 customer_email VARCHAR(190) NULL, address_text VARCHAR(700) NULL, area VARCHAR(180) NULL, fulfillment_type VARCHAR(24) NOT NULL DEFAULT 'delivery',
 scheduled_for DATETIME NULL, subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, delivery_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
 discount_total DECIMAL(14,2) NOT NULL DEFAULT 0, wallet_used DECIMAL(14,2) NOT NULL DEFAULT 0, grand_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 coupon_code VARCHAR(60) NULL, payment_method VARCHAR(30) NOT NULL DEFAULT 'cod', payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
 status VARCHAR(30) NOT NULL DEFAULT 'placed', customer_notes VARCHAR(1200) NULL, rewards_awarded_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_order_no(order_no), UNIQUE KEY uq_order_token(public_token), KEY idx_order_user(tenant_id,user_id,status,id), KEY idx_order_status(tenant_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_order_groups_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_id BIGINT NOT NULL, business_id BIGINT NOT NULL,
 subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, delivery_fee DECIMAL(14,2) NOT NULL DEFAULT 0, discount_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 total DECIMAL(14,2) NOT NULL DEFAULT 0, status VARCHAR(30) NOT NULL DEFAULT 'placed', prep_minutes INT NOT NULL DEFAULT 0,
 rider_id BIGINT NULL, accepted_at DATETIME NULL, ready_at DATETIME NULL, dispatched_at DATETIME NULL, delivered_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_order_business(order_id,business_id), KEY idx_group_business(tenant_id,business_id,status,id), KEY idx_group_rider(tenant_id,rider_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_order_items_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_id BIGINT NOT NULL, order_group_id BIGINT NOT NULL,
 product_id BIGINT NOT NULL, business_id BIGINT NOT NULL, product_name VARCHAR(220) NOT NULL, image_url VARCHAR(700) NULL,
 unit_price DECIMAL(14,2) NOT NULL DEFAULT 0, qty INT NOT NULL DEFAULT 1, line_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_order_items(order_id,order_group_id), KEY idx_order_product(tenant_id,product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_order_status_log_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_id BIGINT NOT NULL, order_group_id BIGINT NULL,
 status VARCHAR(30) NOT NULL, actor_user_id BIGINT NULL, note VARCHAR(700) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_status_order(order_id,id), KEY idx_status_group(order_group_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_delivery_zones_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, business_id BIGINT NOT NULL DEFAULT 0, area_name VARCHAR(180) NOT NULL,
 delivery_fee DECIMAL(12,2) NOT NULL DEFAULT 0, min_order DECIMAL(12,2) NOT NULL DEFAULT 0, free_over DECIMAL(12,2) NOT NULL DEFAULT 0,
 active TINYINT NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_zone(tenant_id,business_id,active,area_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_wallet_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, balance DECIMAL(14,2) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_wallet(tenant_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_wallet_ledger_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, amount DECIMAL(14,2) NOT NULL,
 event_key VARCHAR(50) NOT NULL, reference_type VARCHAR(40) NULL, reference_id BIGINT NULL, note VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_wallet_ledger(tenant_id,user_id,id), KEY idx_wallet_ref(tenant_id,reference_type,reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS membership_settings_v1230 (
 tenant_id BIGINT NOT NULL, enabled TINYINT NOT NULL DEFAULT 1, approval_required TINYINT NOT NULL DEFAULT 1,
 show_on_my_shahkot TINYINT NOT NULL DEFAULT 1, updated_at DATETIME NULL, PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS membership_plans_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, name VARCHAR(120) NOT NULL, description VARCHAR(700) NULL,
 monthly_price DECIMAL(12,2) NOT NULL DEFAULT 0, duration_days INT NOT NULL DEFAULT 30, free_delivery_min DECIMAL(12,2) NOT NULL DEFAULT 0,
 points_multiplier DECIMAL(6,2) NOT NULL DEFAULT 1.00, cashback_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
 benefits_json LONGTEXT NULL, active TINYINT NOT NULL DEFAULT 0, sort_order INT NOT NULL DEFAULT 10,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_plan_live(tenant_id,active,sort_order,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS membership_subscriptions_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, plan_id BIGINT NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'pending', starts_at DATETIME NULL, ends_at DATETIME NULL, approved_by BIGINT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_member_user(tenant_id,user_id,status,ends_at), KEY idx_member_plan(tenant_id,plan_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_riders_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, name VARCHAR(140) NOT NULL, phone VARCHAR(60) NOT NULL,
 vehicle_type VARCHAR(60) NULL, vehicle_no VARCHAR(80) NULL, zone_label VARCHAR(180) NULL, earning_per_delivery DECIMAL(12,2) NOT NULL DEFAULT 0, status VARCHAR(30) NOT NULL DEFAULT 'offline',
 current_lat DECIMAL(10,7) NULL, current_lng DECIMAL(10,7) NULL, location_updated_at DATETIME NULL, active TINYINT NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_rider_user(tenant_id,user_id), KEY idx_rider_status(tenant_id,active,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_assignments_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_group_id BIGINT NOT NULL, rider_id BIGINT NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'assigned', eta_minutes INT NOT NULL DEFAULT 0, assigned_by BIGINT NULL, assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 picked_at DATETIME NULL, delivered_at DATETIME NULL, proof_code VARCHAR(40) NULL, proof_note VARCHAR(500) NULL, updated_at DATETIME NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_delivery_group(order_group_id), KEY idx_assignment_rider(tenant_id,rider_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_rider_earnings_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, rider_id BIGINT NOT NULL, assignment_id BIGINT NOT NULL,
 order_group_id BIGINT NOT NULL, amount DECIMAL(12,2) NOT NULL DEFAULT 0, status VARCHAR(30) NOT NULL DEFAULT 'pending',
 paid_by BIGINT NULL, paid_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_rider_earning_assignment(assignment_id), KEY idx_rider_earnings(tenant_id,rider_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_cod_settlements_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, rider_id BIGINT NOT NULL, order_group_id BIGINT NOT NULL,
 amount DECIMAL(14,2) NOT NULL DEFAULT 0, status VARCHAR(30) NOT NULL DEFAULT 'pending', settled_by BIGINT NULL,
 settled_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_cod_group(order_group_id), KEY idx_cod_rider(tenant_id,rider_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_returns_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_id BIGINT NOT NULL, order_group_id BIGINT NULL, user_id BIGINT NULL,
 reason VARCHAR(120) NOT NULL, details VARCHAR(1200) NULL, status VARCHAR(30) NOT NULL DEFAULT 'requested', resolution_note VARCHAR(800) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_return_order(tenant_id,order_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_disputes_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, order_id BIGINT NOT NULL, user_id BIGINT NULL,
 subject VARCHAR(190) NOT NULL, details VARCHAR(1600) NOT NULL, status VARCHAR(30) NOT NULL DEFAULT 'open', admin_note VARCHAR(1000) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
 PRIMARY KEY(id), KEY idx_dispute(tenant_id,status,id), KEY idx_dispute_order(order_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_favorite_orders_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NOT NULL, source_order_id BIGINT NOT NULL,
 label VARCHAR(140) NOT NULL DEFAULT 'Favorite Order', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_favorite_order(tenant_id,user_id,source_order_id), KEY idx_favorite_user(tenant_id,user_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_notifications_v1230 (
 id BIGINT NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL, user_id BIGINT NULL, visitor_key CHAR(64) NOT NULL DEFAULT '',
 title VARCHAR(190) NOT NULL, body VARCHAR(700) NULL, target_url VARCHAR(700) NULL, event_key VARCHAR(60) NOT NULL,
 read_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_commerce_notice_user(tenant_id,user_id,read_at,id), KEY idx_commerce_notice_visitor(tenant_id,visitor_key,read_at,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO membership_plans_v1230(tenant_id,name,description,monthly_price,duration_days,free_delivery_min,points_multiplier,cashback_percent,benefits_json,active,sort_order)
SELECT id,'ShahkotPK Plus','Configure price and benefits before activating this plan.',0,30,0,1.00,0,'["Exclusive member deals","Delivery benefits","Reward multiplier"]',0,10 FROM tenants t
WHERE NOT EXISTS (SELECT 1 FROM membership_plans_v1230 p WHERE p.tenant_id=t.id);

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.3.0'),
('commerce_platform_version','12.3.0'),
('commerce_cart_url','/cart-v1230.php'),
('commerce_orders_url','/my-orders-v1230.php'),
('membership_club_url','/membership-v1230.php'),
('rider_dashboard_url','/rider-dashboard-v1230.php')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
