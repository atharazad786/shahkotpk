-- ShahkotPK v6.3.0 Smart City Super App Suite
-- Combined v6.0.0 + v6.1.0 + v6.2.0 + v6.3.0 additive/idempotent migration.
-- No DROP/TRUNCATE statements.

CREATE TABLE IF NOT EXISTS citizen_addresses_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(80) NOT NULL DEFAULT 'Home',
  recipient_name VARCHAR(190) NULL,
  phone VARCHAR(80) NULL,
  address_line TEXT NOT NULL,
  area VARCHAR(190) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_sc630_address_user(tenant_id,user_id,is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS citizen_emergency_contacts_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  relation VARCHAR(100) NULL,
  phone VARCHAR(80) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_sc630_emergency_user(tenant_id,user_id,is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS municipal_categories_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  icon VARCHAR(30) NULL,
  description VARCHAR(500) NULL,
  default_department VARCHAR(160) NULL,
  target_hours INT NOT NULL DEFAULT 72,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_mcat(tenant_id,slug), KEY ix_sc630_mcat_status(tenant_id,status,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO municipal_categories_v630(tenant_id,name,slug,icon,description,default_department,target_hours,status,sort_order) VALUES
(0,'Roads & Streets','roads-streets','▰','Potholes, damaged roads and street access issues.','Public Works',120,1,10),
(0,'Street Lights','street-lights','✦','Non-working or damaged public street lighting.','Electrical Services',72,1,20),
(0,'Sanitation & Waste','sanitation-waste','♻','Waste collection, dumping and public cleanliness.','Sanitation',48,1,30),
(0,'Water & Drainage','water-drainage','◉','Public water supply, sewerage and drainage issues.','Water & Drainage',48,1,40),
(0,'Parks & Public Spaces','parks-public-spaces','♧','Public parks, playgrounds and shared spaces.','Parks',120,1,50),
(0,'Traffic & Signage','traffic-signage','◇','Road signs, markings and non-emergency traffic infrastructure.','Traffic Services',96,1,60),
(0,'Lost & Found','lost-found','⌕','Report or locate lost items through the city portal.','Citizen Desk',72,1,70),
(0,'Other Citizen Service','other-service','◫','General non-emergency citizen service request.','Citizen Desk',120,1,80);

CREATE TABLE IF NOT EXISTS municipal_requests_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  reference_no VARCHAR(60) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  request_type ENUM('complaint','service_request','lost_found') NOT NULL DEFAULT 'complaint',
  category_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT NOT NULL,
  address TEXT NULL,
  area VARCHAR(190) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  priority ENUM('normal','urgent') NOT NULL DEFAULT 'normal',
  status ENUM('submitted','acknowledged','assigned','in_progress','resolved','closed','rejected') NOT NULL DEFAULT 'submitted',
  department VARCHAR(160) NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  resolution_note TEXT NULL,
  resolved_at DATETIME NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_mref(reference_no), KEY ix_sc630_mqueue(tenant_id,status,priority,created_at), KEY ix_sc630_muser(user_id,created_at), KEY ix_sc630_mcat(category_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS municipal_request_updates_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  request_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  status VARCHAR(40) NULL,
  note TEXT NULL,
  public_note TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_sc630_mupdate(request_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS citizen_wallet_accounts_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  balance DECIMAL(16,2) NOT NULL DEFAULT 0,
  loyalty_points BIGINT NOT NULL DEFAULT 0,
  status ENUM('active','frozen','closed') NOT NULL DEFAULT 'active',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_wallet_user(tenant_id,user_id), KEY ix_sc630_wallet_status(tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS citizen_wallet_transactions_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  transaction_no VARCHAR(80) NOT NULL,
  direction ENUM('credit','debit') NOT NULL,
  amount DECIMAL(16,2) NOT NULL DEFAULT 0,
  points_delta BIGINT NOT NULL DEFAULT 0,
  category VARCHAR(80) NOT NULL,
  reference_type VARCHAR(80) NULL,
  reference_id BIGINT UNSIGNED NULL,
  reference_key VARCHAR(190) NULL,
  note VARCHAR(500) NULL,
  balance_after DECIMAL(16,2) NOT NULL DEFAULT 0,
  points_after BIGINT NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_wallet_no(transaction_no), UNIQUE KEY uq_sc630_wallet_ref(tenant_id,user_id,reference_key), KEY ix_sc630_wallet_user(user_id,created_at), KEY ix_sc630_wallet_cat(tenant_id,category,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loyalty_rules_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  event_key VARCHAR(80) NOT NULL,
  name VARCHAR(190) NOT NULL,
  points INT NOT NULL DEFAULT 0,
  max_per_day INT NOT NULL DEFAULT 1,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_lrule(tenant_id,event_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO loyalty_rules_v630(tenant_id,event_key,name,points,max_per_day,status) VALUES
(0,'profile_complete','Complete Citizen Profile',100,1,1),
(0,'municipal_request','Citizen Service Participation',15,3,1),
(0,'marketplace_order','Marketplace Purchase',25,5,1),
(0,'health_booking','Healthcare Booking',20,3,1),
(0,'property_inquiry','Property Inquiry',10,3,1),
(0,'daily_portal_visit','Daily Portal Visit',5,1,1);

CREATE TABLE IF NOT EXISTS loyalty_events_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  event_key VARCHAR(80) NOT NULL,
  event_ref VARCHAR(190) NOT NULL,
  points INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_levent(tenant_id,user_id,event_key,event_ref), KEY ix_sc630_levent_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smart_notifications_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  audience VARCHAR(60) NOT NULL DEFAULT 'user',
  channel VARCHAR(40) NOT NULL DEFAULT 'in_app',
  title VARCHAR(220) NOT NULL,
  message TEXT NOT NULL,
  action_url VARCHAR(600) NULL,
  icon VARCHAR(40) NULL,
  priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_sc630_notify_user(tenant_id,user_id,created_at), KEY ix_sc630_notify_audience(tenant_id,audience,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smart_notification_reads_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  notification_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_nread(notification_id,user_id), KEY ix_sc630_nread_user(user_id,read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smart_assistant_messages_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  session_key VARCHAR(100) NOT NULL,
  role ENUM('user','assistant') NOT NULL,
  intent VARCHAR(80) NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_sc630_ai_session(tenant_id,session_key,created_at), KEY ix_sc630_ai_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smart_recommendation_events_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  visitor_hash CHAR(64) NULL,
  module VARCHAR(80) NOT NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_sc630_rec_user(user_id,created_at), KEY ix_sc630_rec_module(tenant_id,module,action,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smart_city_staff_v630 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  access_role VARCHAR(80) NOT NULL DEFAULT 'citizen_service',
  permissions_json LONGTEXT NULL,
  department VARCHAR(160) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_sc630_staff(tenant_id,user_id,access_role), KEY ix_sc630_staff_status(tenant_id,status,access_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
