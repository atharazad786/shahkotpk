-- ShahkotPK v5.5.0 - E-commerce, POS, Affiliate & Auction Pro
-- Non-destructive. Creates isolated v5.5 commerce tables and settings only.

CREATE TABLE IF NOT EXISTS marketplace_shops_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NULL,
  owner_user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  description TEXT NULL,
  logo_url VARCHAR(500) NULL,
  banner_url VARCHAR(500) NULL,
  phone VARCHAR(60) NULL,
  whatsapp VARCHAR(60) NULL,
  email VARCHAR(190) NULL,
  address VARCHAR(500) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'active',
  verified TINYINT(1) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  commission_mode VARCHAR(20) NOT NULL DEFAULT 'inherit',
  commission_percent DECIMAL(7,3) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_shop_slug (slug),
  KEY idx_mp550_shop_scope (tenant_id,city_id,status),
  KEY idx_mp550_shop_owner (owner_user_id,status),
  KEY idx_mp550_shop_business (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_shop_members_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'staff',
  status TINYINT(1) NOT NULL DEFAULT 1,
  can_products TINYINT(1) NOT NULL DEFAULT 0,
  can_orders TINYINT(1) NOT NULL DEFAULT 1,
  can_pos TINYINT(1) NOT NULL DEFAULT 0,
  can_auctions TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_shop_member (shop_id,user_id),
  KEY idx_mp550_member_user (user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_product_meta_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  shop_id BIGINT UNSIGNED NULL,
  sku VARCHAR(100) NULL,
  barcode VARCHAR(100) NULL,
  brand VARCHAR(120) NULL,
  compare_at_price DECIMAL(14,2) NULL,
  cost_price DECIMAL(14,2) NULL,
  low_stock_threshold INT NOT NULL DEFAULT 3,
  min_order_qty INT NOT NULL DEFAULT 1,
  max_order_qty INT NULL,
  warranty_text VARCHAR(300) NULL,
  return_days INT NOT NULL DEFAULT 0,
  gallery_json LONGTEXT NULL,
  tags_json LONGTEXT NULL,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_product_meta (product_id),
  KEY idx_mp550_product_shop (shop_id),
  KEY idx_mp550_product_sku (sku),
  KEY idx_mp550_product_barcode (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_discounts_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  shop_id BIGINT UNSIGNED NULL,
  product_id BIGINT UNSIGNED NULL,
  category_id BIGINT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  code VARCHAR(80) NULL,
  discount_type VARCHAR(20) NOT NULL DEFAULT 'percent',
  discount_value DECIMAL(14,3) NOT NULL DEFAULT 0,
  min_order_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  max_discount_amount DECIMAL(14,2) NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  usage_limit INT NULL,
  per_user_limit INT NULL,
  usage_count INT NOT NULL DEFAULT 0,
  automatic_apply TINYINT(1) NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_discount_code (code),
  KEY idx_mp550_discount_active (status,starts_at,ends_at),
  KEY idx_mp550_discount_scope (tenant_id,shop_id,product_id,category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_commission_rules_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  shop_id BIGINT UNSIGNED NULL,
  seller_user_id BIGINT UNSIGNED NULL,
  category_id BIGINT UNSIGNED NULL,
  product_id BIGINT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  percent_rate DECIMAL(7,3) NOT NULL DEFAULT 0,
  flat_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
  priority INT NOT NULL DEFAULT 100,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mp550_commission_match (status,tenant_id,shop_id,seller_user_id,category_id,product_id,priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_orders_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_number VARCHAR(80) NOT NULL,
  checkout_group VARCHAR(80) NULL,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  shop_id BIGINT UNSIGNED NULL,
  seller_user_id BIGINT UNSIGNED NOT NULL,
  buyer_user_id BIGINT UNSIGNED NULL,
  source VARCHAR(20) NOT NULL DEFAULT 'web',
  customer_name VARCHAR(190) NOT NULL,
  customer_phone VARCHAR(60) NULL,
  customer_email VARCHAR(190) NULL,
  shipping_address TEXT NULL,
  customer_note TEXT NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  shipping_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  grand_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  platform_commission DECIMAL(14,2) NOT NULL DEFAULT 0,
  affiliate_commission DECIMAL(14,2) NOT NULL DEFAULT 0,
  seller_net DECIMAL(14,2) NOT NULL DEFAULT 0,
  affiliate_user_id BIGINT UNSIGNED NULL,
  affiliate_code VARCHAR(80) NULL,
  coupon_code VARCHAR(80) NULL,
  payment_method VARCHAR(30) NOT NULL DEFAULT 'cod',
  payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',
  payment_order_id BIGINT UNSIGNED NULL,
  fulfillment_status VARCHAR(30) NOT NULL DEFAULT 'unfulfilled',
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  tracking_number VARCHAR(120) NULL,
  courier_name VARCHAR(120) NULL,
  placed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_order_number (order_number),
  KEY idx_mp550_order_seller (seller_user_id,status,placed_at),
  KEY idx_mp550_order_buyer (buyer_user_id,placed_at),
  KEY idx_mp550_order_shop (shop_id,status,placed_at),
  KEY idx_mp550_order_scope (tenant_id,city_id,placed_at),
  KEY idx_mp550_order_affiliate (affiliate_user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_order_items_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  shop_id BIGINT UNSIGNED NULL,
  seller_user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(100) NULL,
  image_url VARCHAR(500) NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  platform_commission DECIMAL(14,2) NOT NULL DEFAULT 0,
  affiliate_commission DECIMAL(14,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mp550_item_order (order_id),
  KEY idx_mp550_item_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_order_status_log_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  old_status VARCHAR(30) NULL,
  new_status VARCHAR(30) NOT NULL,
  note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mp550_order_log (order_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_affiliates_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(80) NOT NULL,
  commission_rate DECIMAL(7,3) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_affiliate_user (user_id),
  UNIQUE KEY uq_mp550_affiliate_code (code),
  KEY idx_mp550_affiliate_tenant (tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_affiliate_clicks_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  affiliate_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  session_key CHAR(64) NOT NULL,
  referrer VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_aff_click (affiliate_id,product_id,session_key),
  KEY idx_mp550_aff_click_time (affiliate_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_affiliate_conversions_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  affiliate_id BIGINT UNSIGNED NOT NULL,
  affiliate_user_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NOT NULL,
  order_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  commission_rate DECIMAL(7,3) NOT NULL DEFAULT 0,
  commission_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_at DATETIME NULL,
  paid_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_aff_order (order_id),
  KEY idx_mp550_aff_conversion (affiliate_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_pos_assignments_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  assigned_by BIGINT UNSIGNED NULL,
  terminal_name VARCHAR(120) NOT NULL DEFAULT 'Main Counter',
  permissions_json LONGTEXT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_pos_user_shop (shop_id,user_id),
  KEY idx_mp550_pos_user (user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_pos_shifts_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  assignment_id BIGINT UNSIGNED NOT NULL,
  shop_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at DATETIME NULL,
  opening_cash DECIMAL(14,2) NOT NULL DEFAULT 0,
  closing_cash DECIMAL(14,2) NULL,
  sales_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  order_count INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'open',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mp550_shift_user (user_id,status),
  KEY idx_mp550_shift_shop (shop_id,opened_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_payouts_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  payee_type VARCHAR(20) NOT NULL,
  payee_user_id BIGINT UNSIGNED NOT NULL,
  shop_id BIGINT UNSIGNED NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  method VARCHAR(40) NULL,
  account_title VARCHAR(190) NULL,
  account_reference VARCHAR(190) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'requested',
  note VARCHAR(500) NULL,
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  processed_at DATETIME NULL,
  processed_by BIGINT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY idx_mp550_payout_payee (payee_type,payee_user_id,status),
  KEY idx_mp550_payout_shop (shop_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_auctions_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  shop_id BIGINT UNSIGNED NULL,
  seller_user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  start_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  reserve_price DECIMAL(14,2) NULL,
  buy_now_price DECIMAL(14,2) NULL,
  current_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  min_increment DECIMAL(14,2) NOT NULL DEFAULT 100,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'scheduled',
  anti_snipe_seconds INT NOT NULL DEFAULT 120,
  max_extensions INT NOT NULL DEFAULT 5,
  extension_count INT NOT NULL DEFAULT 0,
  winner_user_id BIGINT UNSIGNED NULL,
  winning_bid DECIMAL(14,2) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_auction_product (product_id),
  KEY idx_mp550_auction_live (status,starts_at,ends_at),
  KEY idx_mp550_auction_shop (shop_id,status),
  KEY idx_mp550_auction_scope (tenant_id,city_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_auction_bids_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  auction_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  max_auto_bid DECIMAL(14,2) NOT NULL,
  effective_bid DECIMAL(14,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_auction_bidder (auction_id,user_id),
  KEY idx_mp550_auction_bid_rank (auction_id,status,max_auto_bid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_auction_events_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  auction_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(30) NOT NULL DEFAULT 'bid',
  amount DECIMAL(14,2) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mp550_auction_event (auction_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketplace_auction_watchers_v550 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  auction_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mp550_watch (auction_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- v5.5.1 recovery notes:
-- Runtime defaults keep Marketplace Pro enabled even when settings rows do not yet exist.
-- Hotfix tuning rows are intentionally not written during migration to avoid settings-table lock contention.
-- They can be created later by normal admin settings saves; runtime fallbacks are: retries=4, base_delay_ms=60, bootstrap_locking=1, auction_sync_batch=25.
