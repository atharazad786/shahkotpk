-- ShahkotPK v4.3.0 AI Automation + Recommendation Intelligence

CREATE TABLE IF NOT EXISTS ai_search_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  query_text VARCHAR(500) NOT NULL,
  intent_json MEDIUMTEXT NULL,
  result_count INT NOT NULL DEFAULT 0,
  duration_ms INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ai_search_created (created_at),
  INDEX idx_ai_search_user (user_id,created_at),
  CONSTRAINT fk_ai_search_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_user_profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  profile_json LONGTEXT NOT NULL,
  last_event_at DATETIME NULL,
  refreshed_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ai_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_recommendation_cache (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  cache_key VARCHAR(120) NOT NULL DEFAULT 'default',
  payload_json LONGTEXT NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ai_rec_cache (user_id,cache_key),
  INDEX idx_ai_rec_cache_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_automation_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  run_type ENUM('scheduled','manual') NOT NULL DEFAULT 'scheduled',
  status ENUM('ok','partial','failed') NOT NULL DEFAULT 'ok',
  processed_count INT NOT NULL DEFAULT 0,
  duration_ms INT NOT NULL DEFAULT 0,
  summary_json MEDIUMTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ai_automation_created (created_at,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('ai_platform_version','430'),
('ai_personalization_enabled','1'),
('ai_learning_enabled','1'),
('ai_search_learning_enabled','1'),
('ai_automation_enabled','1'),
('ai_recommendation_window_days','45'),
('ai_recommendation_cache_minutes','20'),
('ai_trending_weight','20'),
('ai_affinity_weight','35'),
('ai_recommendation_diversity_enabled','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
