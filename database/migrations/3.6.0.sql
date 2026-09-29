-- ShahkotPK v3.6.0 Commercial Operations & Automation Suite

CREATE TABLE IF NOT EXISTS commission_rules (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(160) NOT NULL,
 seller_user_id BIGINT UNSIGNED NULL,
 product_type ENUM('all','new','used','digital','auction') NOT NULL DEFAULT 'all',
 commission_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
 commission_value DECIMAL(14,2) NOT NULL DEFAULT 10,
 priority INT NOT NULL DEFAULT 100,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_commission_lookup (seller_user_id,product_type,status,priority),
 CONSTRAINT fk_commission_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_wallets (
 seller_user_id BIGINT UNSIGNED PRIMARY KEY,
 available_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
 pending_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
 lifetime_earnings DECIMAL(14,2) NOT NULL DEFAULT 0,
 lifetime_paid_out DECIMAL(14,2) NOT NULL DEFAULT 0,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_seller_wallet_user FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_wallet_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seller_user_id BIGINT UNSIGNED NOT NULL,
 transaction_type ENUM('sale_credit','payout_debit','refund_debit','adjustment_credit','adjustment_debit') NOT NULL,
 amount DECIMAL(14,2) NOT NULL,
 balance_after DECIMAL(14,2) NOT NULL,
 reference_type VARCHAR(60) NULL,
 reference_id BIGINT UNSIGNED NULL,
 description VARCHAR(500) NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_wallet_tx_seller (seller_user_id,id),
 INDEX idx_wallet_tx_ref (reference_type,reference_id),
 CONSTRAINT fk_wallet_tx_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_wallet_tx_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_payout_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 payout_number VARCHAR(40) NOT NULL UNIQUE,
 seller_user_id BIGINT UNSIGNED NOT NULL,
 amount DECIMAL(14,2) NOT NULL,
 fee_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 net_amount DECIMAL(14,2) NOT NULL,
 payout_method VARCHAR(40) NOT NULL,
 payout_account VARCHAR(255) NOT NULL,
 status ENUM('requested','approved','processing','paid','rejected','cancelled') NOT NULL DEFAULT 'requested',
 gateway_reference VARCHAR(190) NULL,
 notes VARCHAR(600) NULL,
 requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 processed_at DATETIME NULL,
 processed_by BIGINT UNSIGNED NULL,
 INDEX idx_payout_status (status,requested_at),
 INDEX idx_payout_seller (seller_user_id,status),
 CONSTRAINT fk_payout_seller FOREIGN KEY (seller_user_id) REFERENCES users(id),
 CONSTRAINT fk_payout_processor FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_order_settlements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL UNIQUE,
 seller_user_id BIGINT UNSIGNED NOT NULL,
 gross_amount DECIMAL(14,2) NOT NULL,
 commission_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 seller_amount DECIMAL(14,2) NOT NULL,
 status ENUM('pending','settled','reversed') NOT NULL DEFAULT 'pending',
 settled_at DATETIME NULL,
 reversed_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_settlement_order FOREIGN KEY (order_id) REFERENCES store_orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_settlement_seller FOREIGN KEY (seller_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_invoices (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 invoice_number VARCHAR(40) NOT NULL UNIQUE,
 order_id BIGINT UNSIGNED NULL UNIQUE,
 payment_order_id BIGINT UNSIGNED NULL,
 buyer_user_id BIGINT UNSIGNED NULL,
 seller_user_id BIGINT UNSIGNED NULL,
 issue_date DATE NOT NULL,
 due_date DATE NULL,
 subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
 shipping_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('draft','issued','partially_paid','paid','refunded','void') NOT NULL DEFAULT 'issued',
 billing_name VARCHAR(190) NULL,
 billing_phone VARCHAR(60) NULL,
 billing_email VARCHAR(190) NULL,
 billing_address VARCHAR(600) NULL,
 notes TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_ci_status (status,issue_date),
 CONSTRAINT fk_ci_order FOREIGN KEY (order_id) REFERENCES store_orders(id) ON DELETE SET NULL,
 CONSTRAINT fk_ci_payment FOREIGN KEY (payment_order_id) REFERENCES payment_orders(id) ON DELETE SET NULL,
 CONSTRAINT fk_ci_buyer FOREIGN KEY (buyer_user_id) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT fk_ci_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_invoice_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 invoice_id BIGINT UNSIGNED NOT NULL,
 description VARCHAR(255) NOT NULL,
 quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
 unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
 line_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 product_id BIGINT UNSIGNED NULL,
 CONSTRAINT fk_cii_invoice FOREIGN KEY (invoice_id) REFERENCES commerce_invoices(id) ON DELETE CASCADE,
 CONSTRAINT fk_cii_product FOREIGN KEY (product_id) REFERENCES store_products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_tickets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ticket_number VARCHAR(40) NOT NULL UNIQUE,
 user_id BIGINT UNSIGNED NOT NULL,
 business_id BIGINT UNSIGNED NULL,
 order_id BIGINT UNSIGNED NULL,
 department ENUM('general','orders','payments','seller','technical','verification','ads') NOT NULL DEFAULT 'general',
 subject VARCHAR(220) NOT NULL,
 priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
 status ENUM('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
 assigned_to BIGINT UNSIGNED NULL,
 last_message_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_ticket_user (user_id,status), INDEX idx_ticket_queue (status,priority,last_message_at),
 CONSTRAINT fk_ticket_user FOREIGN KEY (user_id) REFERENCES users(id),
 CONSTRAINT fk_ticket_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
 CONSTRAINT fk_ticket_order FOREIGN KEY (order_id) REFERENCES store_orders(id) ON DELETE SET NULL,
 CONSTRAINT fk_ticket_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ticket_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NULL,
 message TEXT NOT NULL,
 is_staff TINYINT(1) NOT NULL DEFAULT 0,
 is_internal TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_support_msg_ticket (ticket_id,id),
 CONSTRAINT fk_support_msg_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
 CONSTRAINT fk_support_msg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_disputes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 dispute_number VARCHAR(40) NOT NULL UNIQUE,
 order_id BIGINT UNSIGNED NOT NULL,
 opened_by BIGINT UNSIGNED NOT NULL,
 reason ENUM('not_received','not_as_described','damaged','digital_access','payment','other') NOT NULL DEFAULT 'other',
 description TEXT NOT NULL,
 requested_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('open','reviewing','seller_response','resolved_buyer','resolved_seller','closed') NOT NULL DEFAULT 'open',
 resolution_notes TEXT NULL,
 resolved_by BIGINT UNSIGNED NULL,
 resolved_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_dispute_status (status,created_at),
 CONSTRAINT fk_dispute_order FOREIGN KEY (order_id) REFERENCES store_orders(id),
 CONSTRAINT fk_dispute_opener FOREIGN KEY (opened_by) REFERENCES users(id),
 CONSTRAINT fk_dispute_resolver FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_refunds (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 refund_number VARCHAR(40) NOT NULL UNIQUE,
 order_id BIGINT UNSIGNED NOT NULL,
 dispute_id BIGINT UNSIGNED NULL,
 amount DECIMAL(14,2) NOT NULL,
 method VARCHAR(60) NOT NULL DEFAULT 'original',
 status ENUM('requested','approved','processing','refunded','rejected') NOT NULL DEFAULT 'requested',
 reference VARCHAR(190) NULL,
 notes VARCHAR(700) NULL,
 created_by BIGINT UNSIGNED NULL,
 processed_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 processed_at DATETIME NULL,
 INDEX idx_refund_status (status,created_at),
 CONSTRAINT fk_refund_order FOREIGN KEY (order_id) REFERENCES store_orders(id),
 CONSTRAINT fk_refund_dispute FOREIGN KEY (dispute_id) REFERENCES commerce_disputes(id) ON DELETE SET NULL,
 CONSTRAINT fk_refund_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT fk_refund_processor FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_zones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(160) NOT NULL,
 city_id BIGINT UNSIGNED NULL,
 postal_codes VARCHAR(600) NULL,
 delivery_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
 free_over DECIMAL(14,2) NOT NULL DEFAULT 0,
 eta_text VARCHAR(120) NULL,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_delivery_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 shipment_number VARCHAR(40) NOT NULL UNIQUE,
 order_id BIGINT UNSIGNED NOT NULL UNIQUE,
 zone_id BIGINT UNSIGNED NULL,
 carrier VARCHAR(120) NULL,
 tracking_number VARCHAR(190) NULL,
 rider_name VARCHAR(160) NULL,
 rider_phone VARCHAR(60) NULL,
 status ENUM('pending','packed','dispatched','out_for_delivery','delivered','failed','returned') NOT NULL DEFAULT 'pending',
 proof_url VARCHAR(500) NULL,
 dispatched_at DATETIME NULL,
 delivered_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_shipment_order FOREIGN KEY (order_id) REFERENCES store_orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_shipment_zone FOREIGN KEY (zone_id) REFERENCES delivery_zones(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipment_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 shipment_id BIGINT UNSIGNED NOT NULL,
 status VARCHAR(50) NOT NULL,
 note VARCHAR(500) NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_shipment_event (shipment_id,id),
 CONSTRAINT fk_shipment_event_ship FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
 CONSTRAINT fk_shipment_event_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_locations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seller_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(160) NOT NULL,
 location_type ENUM('warehouse','shop','virtual') NOT NULL DEFAULT 'shop',
 address VARCHAR(500) NULL,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_inventory_location_seller (seller_user_id,status),
 CONSTRAINT fk_inventory_location_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 product_id BIGINT UNSIGNED NOT NULL,
 variant_name VARCHAR(180) NOT NULL,
 sku VARCHAR(120) NULL UNIQUE,
 barcode VARCHAR(120) NULL UNIQUE,
 price_adjustment DECIMAL(14,2) NOT NULL DEFAULT 0,
 stock_qty INT NOT NULL DEFAULT 0,
 status TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_variant_product (product_id,status),
 CONSTRAINT fk_variant_product FOREIGN KEY (product_id) REFERENCES store_products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_movements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seller_user_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NOT NULL,
 variant_id BIGINT UNSIGNED NULL,
 location_id BIGINT UNSIGNED NULL,
 movement_type ENUM('opening','purchase','sale','return_in','return_out','adjustment_in','adjustment_out','damage') NOT NULL,
 quantity INT NOT NULL,
 reference_type VARCHAR(60) NULL,
 reference_id BIGINT UNSIGNED NULL,
 note VARCHAR(500) NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_inventory_product (product_id,id), INDEX idx_inventory_seller (seller_user_id,id),
 CONSTRAINT fk_inventory_seller FOREIGN KEY (seller_user_id) REFERENCES users(id),
 CONSTRAINT fk_inventory_product FOREIGN KEY (product_id) REFERENCES store_products(id),
 CONSTRAINT fk_inventory_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
 CONSTRAINT fk_inventory_location FOREIGN KEY (location_id) REFERENCES inventory_locations(id) ON DELETE SET NULL,
 CONSTRAINT fk_inventory_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vendors (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 vendor_code VARCHAR(40) NOT NULL UNIQUE,
 name VARCHAR(190) NOT NULL,
 contact_person VARCHAR(160) NULL,
 phone VARCHAR(60) NULL,
 email VARCHAR(190) NULL,
 address VARCHAR(600) NULL,
 tax_number VARCHAR(120) NULL,
 opening_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
 status TINYINT(1) NOT NULL DEFAULT 1,
 notes TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 po_number VARCHAR(40) NOT NULL UNIQUE,
 vendor_id BIGINT UNSIGNED NOT NULL,
 status ENUM('draft','ordered','partial','received','cancelled') NOT NULL DEFAULT 'draft',
 subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
 tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 shipping_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 expected_date DATE NULL,
 notes TEXT NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_po_vendor (vendor_id,status),
 CONSTRAINT fk_po_vendor FOREIGN KEY (vendor_id) REFERENCES vendors(id),
 CONSTRAINT fk_po_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 purchase_order_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NOT NULL,
 variant_id BIGINT UNSIGNED NULL,
 quantity INT NOT NULL,
 received_qty INT NOT NULL DEFAULT 0,
 unit_cost DECIMAL(14,2) NOT NULL,
 line_total DECIMAL(14,2) NOT NULL,
 CONSTRAINT fk_poi_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_poi_product FOREIGN KEY (product_id) REFERENCES store_products(id),
 CONSTRAINT fk_poi_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_gateway_webhook_keys (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 gateway_slug VARCHAR(80) NOT NULL UNIQUE,
 token_hash VARCHAR(255) NOT NULL,
 status TINYINT(1) NOT NULL DEFAULT 1,
 last_used_at DATETIME NULL,
 rotated_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_webhook_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 gateway_slug VARCHAR(80) NOT NULL,
 event_id VARCHAR(190) NULL,
 payload_json LONGTEXT NULL,
 headers_json TEXT NULL,
 status ENUM('received','processed','ignored','failed') NOT NULL DEFAULT 'received',
 message VARCHAR(700) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 processed_at DATETIME NULL,
 UNIQUE KEY uq_gateway_event (gateway_slug,event_id),
 INDEX idx_webhook_status (status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_reconciliation (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 payment_order_id BIGINT UNSIGNED NOT NULL,
 expected_amount DECIMAL(14,2) NOT NULL,
 received_amount DECIMAL(14,2) NOT NULL,
 gateway VARCHAR(80) NULL,
 gateway_reference VARCHAR(190) NULL,
 status ENUM('matched','underpaid','overpaid','missing','review') NOT NULL DEFAULT 'review',
 reconciled_by BIGINT UNSIGNED NULL,
 reconciled_at DATETIME NULL,
 notes VARCHAR(700) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_recon_payment (payment_order_id),
 CONSTRAINT fk_recon_payment FOREIGN KEY (payment_order_id) REFERENCES payment_orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_recon_user FOREIGN KEY (reconciled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 action VARCHAR(100) NOT NULL,
 entity_type VARCHAR(100) NULL,
 entity_id BIGINT UNSIGNED NULL,
 old_json LONGTEXT NULL,
 new_json LONGTEXT NULL,
 ip_address VARCHAR(64) NULL,
 user_agent VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_entity (entity_type,entity_id), INDEX idx_audit_user (user_id,created_at), INDEX idx_audit_action (action,created_at),
 CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS job_queue (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 queue_name VARCHAR(80) NOT NULL DEFAULT 'default',
 job_type VARCHAR(120) NOT NULL,
 payload_json LONGTEXT NULL,
 status ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
 attempts INT NOT NULL DEFAULT 0,
 max_attempts INT NOT NULL DEFAULT 3,
 available_at DATETIME NOT NULL,
 locked_at DATETIME NULL,
 locked_by VARCHAR(120) NULL,
 last_error TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 completed_at DATETIME NULL,
 INDEX idx_job_ready (status,available_at,queue_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS backup_records (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 backup_type ENUM('database','files','full') NOT NULL DEFAULT 'database',
 file_name VARCHAR(255) NOT NULL,
 file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
 checksum_sha256 VARCHAR(64) NULL,
 status ENUM('running','completed','failed','deleted') NOT NULL DEFAULT 'running',
 notes VARCHAR(700) NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 completed_at DATETIME NULL,
 CONSTRAINT fk_backup_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_checks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 check_type VARCHAR(100) NOT NULL,
 status ENUM('ok','warning','critical') NOT NULL,
 value_text VARCHAR(700) NULL,
 meta_json TEXT NULL,
 checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_health_type (check_type,checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO commission_rules(name,product_type,commission_type,commission_value,priority,status)
VALUES ('Default Marketplace Commission','all','percent',10,999,1);

INSERT IGNORE INTO accounting_accounts(code,name,account_type,system_key,description,status) VALUES
('2110','Seller Payable','liability','seller_payable','Amounts earned by marketplace sellers but not yet paid out.',1),
('2120','Vendor Payable','liability','vendor_payable','Amounts due to suppliers/vendors.',1),
('4030','Marketplace Commission','revenue','marketplace_commission','Platform commission from marketplace orders.',1),
('4040','Shipping Revenue','revenue','shipping_revenue','Delivery and shipping revenue.',1),
('4050','Payout Fee Revenue','revenue','payout_fee_revenue','Fees retained by the platform from seller payout requests.',1),
('1130','Inventory Asset','asset','inventory_asset','Marketplace/purchased inventory asset value.',1),
('5110','Refunds & Adjustments','expense','refunds_expense','Commercial refunds and customer adjustments.',1),
('5120','Gateway Fees','expense','gateway_fees','Payment gateway and settlement charges.',1);

INSERT INTO settings(setting_key,setting_value) VALUES
('commercial_operations_enabled','1'),('marketplace_default_commission_percent','10'),('seller_min_payout','1000'),('seller_payout_fee','0'),
('support_center_enabled','1'),('disputes_enabled','1'),('delivery_management_enabled','1'),('inventory_management_enabled','1'),('vendor_purchasing_enabled','1'),
('queue_enabled','1'),('queue_batch_size','10'),('backup_retention_days','14'),('operations_health_enabled','1'),('operations_version','360')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT IGNORE INTO system_roles(name,slug,description,permissions_json,status) VALUES
('Commercial Operations Manager','commercial-operations-manager','Manage operational dashboards, delivery, support and inventory','["dashboard.view","operations.dashboard","support.manage","disputes.manage","delivery.manage","inventory.manage","purchasing.manage"]',1),
('Settlement Manager','settlement-manager','Manage payments, marketplace settlements, invoices and seller payouts','["dashboard.view","payments.manage","payouts.manage","commerce_invoices.manage","accounts.manage"]',1),
('Support Manager','support-manager','Manage support tickets and disputes','["dashboard.view","support.manage","disputes.manage"]',1),
('Warehouse Manager','warehouse-manager','Manage inventory, delivery and purchasing','["dashboard.view","inventory.manage","delivery.manage","purchasing.manage"]',1);
