CREATE TABLE IF NOT EXISTS health_pharmacies_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, owner_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(180) NOT NULL, slug VARCHAR(190) NOT NULL, phone VARCHAR(80) NULL, email VARCHAR(190) NULL, address VARCHAR(255) NULL, area VARCHAR(120) NULL,
 lat DECIMAL(10,7) NULL, lng DECIMAL(10,7) NULL, map_url VARCHAR(500) NULL, logo_url VARCHAR(500) NULL, delivery_available TINYINT(1) NOT NULL DEFAULT 1,
 pickup_available TINYINT(1) NOT NULL DEFAULT 1, verified TINYINT(1) NOT NULL DEFAULT 0, featured TINYINT(1) NOT NULL DEFAULT 0,
 status ENUM('pending','active','suspended','closed') NOT NULL DEFAULT 'pending', subscription_status VARCHAR(40) NOT NULL DEFAULT 'none', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_hn590_pharmacy_slug (tenant_id,slug), KEY idx_hn590_pharmacy_status (tenant_id,status,verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_pharmacy_products_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, pharmacy_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(190) NOT NULL, generic_name VARCHAR(190) NULL, category VARCHAR(120) NULL, form VARCHAR(80) NULL, strength VARCHAR(80) NULL,
 price DECIMAL(12,2) NOT NULL DEFAULT 0, stock_qty INT NOT NULL DEFAULT 0, prescription_required TINYINT(1) NOT NULL DEFAULT 0,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 KEY idx_hn590_product_pharmacy (tenant_id,pharmacy_id,status), KEY idx_hn590_product_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_pharmacy_orders_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, order_no VARCHAR(60) NOT NULL, user_id BIGINT UNSIGNED NOT NULL,
 pharmacy_id BIGINT UNSIGNED NOT NULL, prescription_id BIGINT UNSIGNED NULL, total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 status ENUM('pending','accepted','preparing','ready','out_for_delivery','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
 payment_status ENUM('unpaid','pending','paid','waived','refunded') NOT NULL DEFAULT 'unpaid', fulfillment ENUM('delivery','pickup') NOT NULL DEFAULT 'pickup',
 delivery_address VARCHAR(500) NULL, notes TEXT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_hn590_order_no (tenant_id,order_no), KEY idx_hn590_order_user (tenant_id,user_id,created_at), KEY idx_hn590_order_pharmacy (tenant_id,pharmacy_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_pharmacy_order_items_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, product_id BIGINT UNSIGNED NOT NULL, item_name VARCHAR(190) NOT NULL,
 unit_price DECIMAL(12,2) NOT NULL DEFAULT 0, qty INT NOT NULL DEFAULT 1, total DECIMAL(12,2) NOT NULL DEFAULT 0, KEY idx_hn590_order_items (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_labs_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, owner_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(180) NOT NULL, slug VARCHAR(190) NOT NULL, phone VARCHAR(80) NULL, email VARCHAR(190) NULL, address VARCHAR(255) NULL, area VARCHAR(120) NULL,
 lat DECIMAL(10,7) NULL, lng DECIMAL(10,7) NULL, map_url VARCHAR(500) NULL, logo_url VARCHAR(500) NULL, home_collection TINYINT(1) NOT NULL DEFAULT 0,
 verified TINYINT(1) NOT NULL DEFAULT 0, featured TINYINT(1) NOT NULL DEFAULT 0, status ENUM('pending','active','suspended','closed') NOT NULL DEFAULT 'pending',
 subscription_status VARCHAR(40) NOT NULL DEFAULT 'none', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_hn590_lab_slug (tenant_id,slug), KEY idx_hn590_lab_status (tenant_id,status,verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_lab_tests_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, lab_id BIGINT UNSIGNED NOT NULL, name VARCHAR(190) NOT NULL,
 category VARCHAR(120) NULL, sample_type VARCHAR(100) NULL, preparation_text VARCHAR(500) NULL, price DECIMAL(12,2) NOT NULL DEFAULT 0,
 turnaround_hours INT NOT NULL DEFAULT 24, home_collection TINYINT(1) NOT NULL DEFAULT 0, status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, KEY idx_hn590_test_lab (tenant_id,lab_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_lab_bookings_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, booking_no VARCHAR(60) NOT NULL, user_id BIGINT UNSIGNED NOT NULL,
 lab_id BIGINT UNSIGNED NOT NULL, test_id BIGINT UNSIGNED NOT NULL, scheduled_at DATETIME NOT NULL, home_collection TINYINT(1) NOT NULL DEFAULT 0,
 collection_address VARCHAR(500) NULL, amount DECIMAL(12,2) NOT NULL DEFAULT 0, status ENUM('requested','confirmed','sample_collected','processing','report_ready','completed','cancelled','rejected') NOT NULL DEFAULT 'requested',
 payment_status ENUM('unpaid','pending','paid','waived','refunded') NOT NULL DEFAULT 'unpaid', notes TEXT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_hn590_booking_no (tenant_id,booking_no), KEY idx_hn590_booking_user (tenant_id,user_id,scheduled_at), KEY idx_hn590_booking_lab (tenant_id,lab_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_lab_reports_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, booking_id BIGINT UNSIGNED NOT NULL, report_file VARCHAR(500) NULL,
 report_text TEXT NULL, status ENUM('draft','published','replaced') NOT NULL DEFAULT 'draft', uploaded_by BIGINT UNSIGNED NULL, uploaded_at DATETIME NOT NULL,
 UNIQUE KEY uq_hn590_report_booking (booking_id), KEY idx_hn590_reports_tenant (tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_ambulances_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, organization_id BIGINT UNSIGNED NULL, driver_user_id BIGINT UNSIGNED NULL,
 vehicle_no VARCHAR(80) NOT NULL, vehicle_type VARCHAR(100) NULL, phone VARCHAR(80) NULL, base_location VARCHAR(255) NULL, lat DECIMAL(10,7) NULL, lng DECIMAL(10,7) NULL,
 availability ENUM('available','assigned','offline','maintenance') NOT NULL DEFAULT 'offline', status ENUM('active','disabled') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE KEY uq_hn590_vehicle (tenant_id,vehicle_no), KEY idx_hn590_ambulance_available (tenant_id,availability,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_ambulance_requests_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, request_no VARCHAR(60) NOT NULL, user_id BIGINT UNSIGNED NOT NULL,
 pickup_address VARCHAR(500) NOT NULL, destination VARCHAR(500) NULL, contact_phone VARCHAR(80) NOT NULL, request_note TEXT NULL, pickup_lat DECIMAL(10,7) NULL, pickup_lng DECIMAL(10,7) NULL,
 ambulance_id BIGINT UNSIGNED NULL, status ENUM('requested','accepted','dispatched','arrived','transporting','completed','cancelled','rejected') NOT NULL DEFAULT 'requested',
 requested_at DATETIME NULL, accepted_at DATETIME NULL, arrived_at DATETIME NULL, completed_at DATETIME NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_hn590_amb_req_no (tenant_id,request_no), KEY idx_hn590_amb_req_user (tenant_id,user_id,created_at), KEY idx_hn590_amb_req_status (tenant_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_network_plans_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, audience ENUM('patient','pharmacy','lab','ambulance_operator') NOT NULL,
 name VARCHAR(160) NOT NULL, description TEXT NULL, price DECIMAL(12,2) NOT NULL DEFAULT 0, duration_days INT NOT NULL DEFAULT 30, features_json JSON NULL,
 status TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, KEY idx_hn590_plan (tenant_id,audience,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_network_subscriptions_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, plan_id BIGINT UNSIGNED NOT NULL, audience VARCHAR(40) NOT NULL,
 user_id BIGINT UNSIGNED NULL, pharmacy_id BIGINT UNSIGNED NULL, lab_id BIGINT UNSIGNED NULL, amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 payment_status ENUM('unpaid','pending','paid','waived','refunded') NOT NULL DEFAULT 'unpaid', status ENUM('pending','active','expired','cancelled') NOT NULL DEFAULT 'pending',
 starts_at DATETIME NULL, ends_at DATETIME NULL, notes TEXT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 KEY idx_hn590_subscription (tenant_id,audience,status), KEY idx_hn590_subscription_user (tenant_id,user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_network_staff_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, access_role VARCHAR(60) NOT NULL,
 pharmacy_id BIGINT UNSIGNED NULL, lab_id BIGINT UNSIGNED NULL, ambulance_id BIGINT UNSIGNED NULL, permissions_json JSON NULL, status TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, KEY idx_hn590_staff_user (tenant_id,user_id,status), KEY idx_hn590_staff_scope (tenant_id,pharmacy_id,lab_id,ambulance_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS health_network_activity_v590 (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NULL, visitor_hash CHAR(64) NULL,
 event_type VARCHAR(80) NOT NULL, entity_type VARCHAR(80) NULL, entity_id BIGINT UNSIGNED NULL, meta_json JSON NULL, created_at DATETIME NOT NULL,
 KEY idx_hn590_activity (tenant_id,created_at,event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO health_network_plans_v590(tenant_id,audience,name,description,price,duration_days,features_json,status,created_at,updated_at)
SELECT 0,'patient','Community Health Access','Unified healthcare-network member plan.',0,30,JSON_ARRAY('My Health Network dashboard','Digital service history'),1,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM health_network_plans_v590 WHERE tenant_id=0 AND audience='patient' AND name='Community Health Access');
