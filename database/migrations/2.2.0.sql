-- ShahkotPK v2.2.0 Admin Experience
-- Automatically executed by System Updater.

CREATE TABLE IF NOT EXISTS dashboard_widgets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  widget_key VARCHAR(120) UNIQUE NOT NULL,
  title VARCHAR(180) NOT NULL,
  widget_type ENUM('metric','progress','quick_link','note') DEFAULT 'metric',
  metric_source VARCHAR(120) NULL,
  icon VARCHAR(30) NULL,
  target_url VARCHAR(600) NULL,
  note_text VARCHAR(1000) NULL,
  accent VARCHAR(30) DEFAULT 'blue',
  width ENUM('small','medium','large','full') DEFAULT 'small',
  enabled TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 10,
  settings_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_dashboard_widget_active_order (enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO dashboard_widgets
(widget_key,title,widget_type,metric_source,icon,target_url,accent,width,enabled,sort_order)
VALUES
('widget-users','Total Users','metric','total_users','U','/admin/users.php','blue','small',1,10),
('widget-businesses','Businesses','metric','total_businesses','B','/admin/businesses.php','green','small',1,20),
('widget-revenue','Recorded Revenue','metric','recorded_revenue','₨','/admin/payments.php','violet','small',1,30),
('widget-pending','Pending Businesses','metric','pending_businesses','!','/admin/businesses.php?status=pending','amber','small',1,40),
('widget-subscriptions','Active Subscriptions','metric','active_subscriptions','S','/admin/plans.php','indigo','small',1,50),
('widget-payments','Payment Review','metric','payment_review','P','/admin/payments.php','red','small',1,60);
