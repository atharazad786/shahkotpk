-- ShahkotPK v11.1.0 — Landing Page Themes / Smart Homepage Feature Parity
CREATE TABLE IF NOT EXISTS landing_theme_parity_v1110 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  inherit_builder TINYINT(1) NOT NULL DEFAULT 1,
  smart_search TINYINT(1) NOT NULL DEFAULT 1,
  enhance_native_cards TINYINT(1) NOT NULL DEFAULT 1,
  inject_missing_sections TINYINT(1) NOT NULL DEFAULT 1,
  floating_actions TINYINT(1) NOT NULL DEFAULT 1,
  ai_chatbot TINYINT(1) NOT NULL DEFAULT 1,
  auto_future_sections TINYINT(1) NOT NULL DEFAULT 1,
  core_sections TINYINT(1) NOT NULL DEFAULT 1,
  smart_sections TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.1.0'),
('landing_theme_parity_version','11.1.0'),
('smart_homepage_theme_sync','1'),
('public_runtime_version','11.1.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
