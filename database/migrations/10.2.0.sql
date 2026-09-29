-- ShahkotPK v10.2.0 — Visitor Growth & Engagement Suite
-- Adds opt-in growth/engagement infrastructure without deleting existing project data.

CREATE TABLE IF NOT EXISTS growth_settings_v1020 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  personalization_enabled TINYINT(1) NOT NULL DEFAULT 1,
  trending_enabled TINYINT(1) NOT NULL DEFAULT 1,
  near_you_enabled TINYINT(1) NOT NULL DEFAULT 1,
  deals_enabled TINYINT(1) NOT NULL DEFAULT 1,
  rewards_enabled TINYINT(1) NOT NULL DEFAULT 1,
  referral_enabled TINYINT(1) NOT NULL DEFAULT 1,
  favorites_enabled TINYINT(1) NOT NULL DEFAULT 1,
  follow_enabled TINYINT(1) NOT NULL DEFAULT 1,
  alerts_enabled TINYINT(1) NOT NULL DEFAULT 1,
  share_enabled TINYINT(1) NOT NULL DEFAULT 1,
  compare_enabled TINYINT(1) NOT NULL DEFAULT 1,
  voice_search_enabled TINYINT(1) NOT NULL DEFAULT 1,
  community_enabled TINYINT(1) NOT NULL DEFAULT 1,
  campaigns_enabled TINYINT(1) NOT NULL DEFAULT 1,
  analytics_enabled TINYINT(1) NOT NULL DEFAULT 1,
  pwa_prompt_enabled TINYINT(1) NOT NULL DEFAULT 1,
  return_visitor_enabled TINYINT(1) NOT NULL DEFAULT 1,
  guest_sync_enabled TINYINT(1) NOT NULL DEFAULT 1,
  seo_enabled TINYINT(1) NOT NULL DEFAULT 1,
  ab_testing_enabled TINYINT(1) NOT NULL DEFAULT 0,
  points_welcome INT NOT NULL DEFAULT 50,
  points_daily INT NOT NULL DEFAULT 5,
  points_referral INT NOT NULL DEFAULT 100,
  points_share INT NOT NULL DEFAULT 2,
  hero_b_heading VARCHAR(220) NULL,
  hero_b_subtitle VARCHAR(500) NULL,
  config_json LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_config_snapshots_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  config_json LONGTEXT NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_gcs_tenant (tenant_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitor_events_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  session_key CHAR(64) NULL,
  event_type VARCHAR(48) NOT NULL,
  entity_type VARCHAR(40) NULL,
  entity_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  search_query VARCHAR(255) NULL,
  result_count INT NULL,
  url VARCHAR(500) NULL,
  referrer VARCHAR(500) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ve_tenant_time (tenant_id,created_at),
  KEY idx_ve_event_time (tenant_id,event_type,created_at),
  KEY idx_ve_entity (tenant_id,entity_type,entity_id,created_at),
  KEY idx_ve_visitor (tenant_id,visitor_key,created_at),
  KEY idx_ve_user (tenant_id,user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitor_favorites_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vf_guest (tenant_id,visitor_key,entity_type,entity_id),
  KEY idx_vf_user (tenant_id,user_id,entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitor_follows_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vfollow_guest (tenant_id,visitor_key,entity_type,entity_id),
  KEY idx_vfollow_user (tenant_id,user_id,entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitor_recent_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  view_count INT NOT NULL DEFAULT 1,
  last_viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vrecent (tenant_id,visitor_key,entity_type,entity_id),
  KEY idx_vrecent_time (tenant_id,visitor_key,last_viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_saved_searches_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  search_type VARCHAR(40) NOT NULL DEFAULT 'all',
  query_text VARCHAR(255) NOT NULL,
  filters_json LONGTEXT NULL,
  alerts_enabled TINYINT(1) NOT NULL DEFAULT 1,
  last_checked_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_gss_user (tenant_id,user_id), KEY idx_gss_guest (tenant_id,visitor_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_alerts_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  alert_type VARCHAR(40) NOT NULL,
  channel VARCHAR(24) NOT NULL DEFAULT 'in_app',
  target VARCHAR(255) NULL,
  last_value DECIMAL(18,2) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_growth_alert (tenant_id,visitor_key,entity_type,entity_id,alert_type,channel),
  KEY idx_growth_alert_run (tenant_id,enabled,alert_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_notifications_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  notification_type VARCHAR(40) NOT NULL DEFAULT 'info',
  title VARCHAR(190) NOT NULL,
  body VARCHAR(800) NULL,
  url VARCHAR(500) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  PRIMARY KEY (id), KEY idx_gn_user (tenant_id,user_id,is_read,created_at), KEY idx_gn_guest (tenant_id,visitor_key,is_read,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_rewards_accounts_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  points_balance INT NOT NULL DEFAULT 0,
  lifetime_points INT NOT NULL DEFAULT 0,
  streak_days INT NOT NULL DEFAULT 0,
  last_checkin_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_gra_user (tenant_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_rewards_ledger_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  event_key VARCHAR(48) NOT NULL,
  points INT NOT NULL,
  reference_key VARCHAR(190) NULL,
  note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_grl_user (tenant_id,user_id,created_at), KEY idx_grl_ref (tenant_id,user_id,event_key,reference_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_referral_codes_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  referral_code VARCHAR(40) NOT NULL,
  clicks INT NOT NULL DEFAULT 0,
  conversions INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_grc_user (tenant_id,user_id), UNIQUE KEY uq_grc_code (referral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_referral_events_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  referral_code VARCHAR(40) NOT NULL,
  referrer_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  referred_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NULL,
  event_type VARCHAR(32) NOT NULL DEFAULT 'click',
  points_awarded INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_gre_code (tenant_id,referral_code,created_at), KEY idx_gre_referred (tenant_id,referred_user_id,event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_campaigns_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(190) NOT NULL,
  title VARCHAR(220) NOT NULL,
  body VARCHAR(800) NULL,
  image_url VARCHAR(500) NULL,
  cta_text VARCHAR(80) NULL,
  cta_url VARCHAR(500) NULL,
  audience VARCHAR(24) NOT NULL DEFAULT 'all',
  placement VARCHAR(48) NOT NULL DEFAULT 'homepage_top',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'draft',
  impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
  clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_gc_active (tenant_id,status,starts_at,ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_questions_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  question VARCHAR(1000) NOT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'pending',
  helpful_count INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_gq_entity (tenant_id,entity_type,entity_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_answers_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  question_id BIGINT UNSIGNED NOT NULL,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  answer VARCHAR(1600) NOT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'pending',
  helpful_count INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_ga_question (question_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_reports_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(120) NOT NULL,
  details VARCHAR(1200) NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_grep_status (tenant_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_business_claims_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  business_id BIGINT UNSIGNED NOT NULL,
  visitor_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  claimant_name VARCHAR(190) NOT NULL,
  claimant_phone VARCHAR(60) NULL,
  claimant_email VARCHAR(190) NULL,
  note VARCHAR(1200) NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_gbc_business (tenant_id,business_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_collections_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_gcol_slug (tenant_id,slug), KEY idx_gcol_user (tenant_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS growth_collection_items_v1020 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  collection_id BIGINT UNSIGNED NOT NULL,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_gcoli (collection_id,entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.2.0'),
('visitor_growth_suite_version','10.2.0'),
('visitor_growth_suite_enabled','1'),
('visitor_growth_privacy_mode','first_party')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
