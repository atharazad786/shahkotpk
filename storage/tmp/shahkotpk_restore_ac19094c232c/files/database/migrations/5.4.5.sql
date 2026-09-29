-- ShahkotPK v5.4.5 - Live Broadcast Center Pro
-- Non-destructive: creates isolated v5.4.5 live tables and adds settings only.

CREATE TABLE IF NOT EXISTS live_broadcasts_v545 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT NULL,
  source_type VARCHAR(30) NOT NULL DEFAULT 'embed',
  source_url TEXT NOT NULL,
  fallback_url TEXT NULL,
  poster_url VARCHAR(500) NULL,
  category VARCHAR(80) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  show_on_home TINYINT(1) NOT NULL DEFAULT 1,
  chat_enabled TINYINT(1) NOT NULL DEFAULT 1,
  analytics_enabled TINYINT(1) NOT NULL DEFAULT 1,
  autoplay_muted TINYINT(1) NOT NULL DEFAULT 1,
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  viewer_count_cached INT UNSIGNED NOT NULL DEFAULT 0,
  peak_viewers INT UNSIGNED NOT NULL DEFAULT 0,
  total_views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  legacy_source_key VARCHAR(190) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_live_v545_slug (slug),
  UNIQUE KEY uq_live_v545_legacy (legacy_source_key),
  KEY idx_live_v545_scope (tenant_id,city_id,status),
  KEY idx_live_v545_home (show_on_home,status,is_featured,sort_order),
  KEY idx_live_v545_start (start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS live_view_sessions_v545 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  broadcast_id BIGINT UNSIGNED NOT NULL,
  tenant_id BIGINT UNSIGNED NULL,
  session_key CHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  first_seen DATETIME NOT NULL,
  last_seen DATETIME NOT NULL,
  watch_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  device VARCHAR(30) NOT NULL DEFAULT 'desktop',
  referrer VARCHAR(500) NULL,
  ip_hash CHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_live_v545_session (broadcast_id,session_key),
  KEY idx_live_v545_online (broadcast_id,last_seen),
  KEY idx_live_v545_view_created (created_at),
  KEY idx_live_v545_view_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS live_chat_messages_v545 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  broadcast_id BIGINT UNSIGNED NOT NULL,
  tenant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  session_key CHAR(64) NULL,
  display_name VARCHAR(60) NOT NULL,
  message VARCHAR(500) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'approved',
  ip_hash CHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_live_v545_chat_broadcast (broadcast_id,status,id),
  KEY idx_live_v545_chat_tenant (tenant_id),
  KEY idx_live_v545_chat_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('live_portal_enabled','1'),
('live_home_enabled','1'),
('live_items_limit','4'),
('live_autoplay_muted','1'),
('live_chat_enabled','1'),
('live_chat_guest_enabled','1'),
('live_chat_moderation','0'),
('live_analytics_enabled','1'),
('live_show_viewer_count','1'),
('live_homepage_version','0')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
