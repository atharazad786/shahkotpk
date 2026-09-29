-- ShahkotPK v3.4.0 Live + Commerce Platform

ALTER TABLE payment_orders MODIFY purpose ENUM('subscription','user_package','advertisement','ecommerce','other') NOT NULL DEFAULT 'other';

CREATE TABLE IF NOT EXISTS live_streams (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_user_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  category VARCHAR(120) NULL,
  description TEXT NULL,
  source_type ENUM('youtube','hls','embed','rtmp','rtsp','ip') NOT NULL DEFAULT 'youtube',
  source_url VARCHAR(1000) NULL,
  playback_url VARCHAR(1000) NULL,
  poster_url VARCHAR(700) NULL,
  is_live TINYINT(1) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','scheduled','live','offline','archived') NOT NULL DEFAULT 'draft',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 10,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_live_status (status,is_live,featured),
  INDEX idx_live_business (business_id),
  INDEX idx_live_schedule (starts_at,ends_at),
  CONSTRAINT fk_live_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_live_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_live_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS live_widgets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL DEFAULT 'Live Now',
  stream_id BIGINT UNSIGNED NULL,
  widget_type ENUM('featured','latest','grid') NOT NULL DEFAULT 'featured',
  item_limit INT NOT NULL DEFAULT 4,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_live_widget_stream FOREIGN KEY (stream_id) REFERENCES live_streams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS popup_campaigns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  body TEXT NULL,
  image_url VARCHAR(700) NULL,
  action_label VARCHAR(120) NULL,
  action_url VARCHAR(700) NULL,
  display_type ENUM('modal','banner','toast') NOT NULL DEFAULT 'modal',
  audience ENUM('all','guests','users','role') NOT NULL DEFAULT 'all',
  target_role VARCHAR(80) NULL,
  repeat_policy ENUM('once_session','once_day','every_visit') NOT NULL DEFAULT 'once_session',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  priority INT NOT NULL DEFAULT 10,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_popup_active (enabled,starts_at,ends_at,priority),
  CONSTRAINT fk_popup_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  body TEXT NULL,
  notification_type ENUM('info','success','warning','promotion') NOT NULL DEFAULT 'info',
  target_type ENUM('all','user','role') NOT NULL DEFAULT 'all',
  target_user_id BIGINT UNSIGNED NULL,
  target_role VARCHAR(80) NULL,
  action_url VARCHAR(700) NULL,
  icon VARCHAR(100) NULL,
  starts_at DATETIME NULL,
  expires_at DATETIME NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_notification_target (status,target_type,target_role),
  CONSTRAINT fk_notification_target_user FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_notification_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_reads (
  notification_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(notification_id,user_id),
  CONSTRAINT fk_notification_read_notification FOREIGN KEY (notification_id) REFERENCES site_notifications(id) ON DELETE CASCADE,
  CONSTRAINT fk_notification_read_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  description VARCHAR(500) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_user_id BIGINT UNSIGNED NOT NULL,
  business_id BIGINT UNSIGNED NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  city_id BIGINT UNSIGNED NULL,
  product_type ENUM('new','used','digital') NOT NULL DEFAULT 'new',
  sale_mode ENUM('fixed','auction') NOT NULL DEFAULT 'fixed',
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  short_description VARCHAR(500) NULL,
  description LONGTEXT NULL,
  image_url VARCHAR(700) NULL,
  digital_file_path VARCHAR(700) NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  compare_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 1,
  condition_notes VARCHAR(700) NULL,
  shipping_charge DECIMAL(14,2) NOT NULL DEFAULT 0,
  allow_cod TINYINT(1) NOT NULL DEFAULT 1,
  allow_advance TINYINT(1) NOT NULL DEFAULT 1,
  location_text VARCHAR(300) NULL,
  status ENUM('draft','pending','published','rejected','soldout','archived') NOT NULL DEFAULT 'pending',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_store_product_seller (seller_user_id,status),
  INDEX idx_store_product_type (product_type,sale_mode,status),
  INDEX idx_store_product_category (category_id,status),
  CONSTRAINT fk_store_product_seller FOREIGN KEY (seller_user_id) REFERENCES users(id),
  CONSTRAINT fk_store_product_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_store_product_category FOREIGN KEY (category_id) REFERENCES store_categories(id),
  CONSTRAINT fk_store_product_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_auctions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL UNIQUE,
  start_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  current_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  min_increment DECIMAL(14,2) NOT NULL DEFAULT 100,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  status ENUM('scheduled','live','ended','cancelled') NOT NULL DEFAULT 'scheduled',
  winner_user_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_store_auction_status (status,starts_at,ends_at),
  CONSTRAINT fk_store_auction_product FOREIGN KEY (product_id) REFERENCES store_products(id) ON DELETE CASCADE,
  CONSTRAINT fk_store_auction_winner FOREIGN KEY (winner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_auction_bids (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  auction_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  bid_amount DECIMAL(14,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_bid_auction (auction_id,bid_amount),
  INDEX idx_store_bid_user (user_id),
  CONSTRAINT fk_store_bid_auction FOREIGN KEY (auction_id) REFERENCES store_auctions(id) ON DELETE CASCADE,
  CONSTRAINT fk_store_bid_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(80) NOT NULL UNIQUE,
  buyer_user_id BIGINT UNSIGNED NOT NULL,
  seller_user_id BIGINT UNSIGNED NOT NULL,
  auction_id BIGINT UNSIGNED NULL UNIQUE,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  shipping_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  grand_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_method ENUM('cod','advance') NOT NULL DEFAULT 'cod',
  payment_status ENUM('unpaid','awaiting_review','paid','refunded') NOT NULL DEFAULT 'unpaid',
  payment_order_id BIGINT UNSIGNED NULL UNIQUE,
  status ENUM('pending','confirmed','processing','shipped','delivered','completed','cancelled') NOT NULL DEFAULT 'pending',
  customer_name VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(40) NOT NULL,
  customer_email VARCHAR(190) NULL,
  shipping_address VARCHAR(500) NULL,
  city_text VARCHAR(160) NULL,
  notes VARCHAR(700) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_store_order_buyer (buyer_user_id,status),
  INDEX idx_store_order_seller (seller_user_id,status),
  CONSTRAINT fk_store_order_buyer FOREIGN KEY (buyer_user_id) REFERENCES users(id),
  CONSTRAINT fk_store_order_seller FOREIGN KEY (seller_user_id) REFERENCES users(id),
  CONSTRAINT fk_store_order_auction FOREIGN KEY (auction_id) REFERENCES store_auctions(id) ON DELETE SET NULL,
  CONSTRAINT fk_store_order_payment FOREIGN KEY (payment_order_id) REFERENCES payment_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  product_type ENUM('new','used','digital') NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL,
  line_total DECIMAL(14,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_order_item_order (order_id),
  CONSTRAINT fk_store_order_item_order FOREIGN KEY (order_id) REFERENCES store_orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_store_order_item_product FOREIGN KEY (product_id) REFERENCES store_products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO live_widgets(title,widget_type,item_limit,enabled,sort_order) VALUES ('Live Now','featured',4,1,10);
INSERT IGNORE INTO store_categories(name,slug,description,status,sort_order) VALUES
('Electronics','electronics','Mobiles, computers and electronics.',1,10),
('Vehicles & Parts','vehicles-parts','Vehicles, bikes and spare parts.',1,20),
('Home & Living','home-living','Home, furniture and living products.',1,30),
('Fashion','fashion','Clothing, shoes and accessories.',1,40),
('Digital Products','digital-products','Downloadable digital products.',1,50),
('Other','other','Other marketplace listings.',1,99);

INSERT IGNORE INTO accounting_accounts(code,name,account_type,system_key,description,status) VALUES
('4020','Marketplace Revenue','revenue','store_revenue','Revenue recorded from marketplace/e-commerce transactions.',1);

INSERT INTO settings(setting_key,setting_value) VALUES
('live_portal_enabled','1'),('live_home_enabled','1'),('live_items_limit','4'),('live_autoplay_muted','1'),
('engagement_popups_enabled','1'),('engagement_notifications_enabled','1'),('engagement_browser_alerts_enabled','0'),
('store_enabled','1'),('store_home_enabled','1'),('store_items_limit','8'),('store_seller_approval_required','1'),
('store_cod_enabled','1'),('store_advance_enabled','1'),('store_auction_enabled','1'),('store_digital_enabled','1'),('store_homepage_version','0'),('live_homepage_version','0')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

INSERT IGNORE INTO system_roles(name,slug,description,permissions_json,status) VALUES
('Live Producer','live-producer','Manage authorized live broadcasts and live widgets','["dashboard.view","live.manage"]',1),
('Commerce Manager','commerce-manager','Manage marketplace products, orders and auctions','["dashboard.view","shop.manage","shop.orders.manage","shop.auctions.manage"]',1),
('Engagement Manager','engagement-manager','Manage popups and in-app notifications','["dashboard.view","engagement.manage"]',1);
