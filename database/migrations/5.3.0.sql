-- ShahkotPK v5.3.0 — Homepage Builder 2.0 + Theme Integration
-- Additive only. Existing CMS pages, businesses, settings, uploads and v5.2 dummy-data registries are preserved.

CREATE TABLE IF NOT EXISTS homepage_builder_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  scope_key VARCHAR(80) NOT NULL,
  tenant_id BIGINT UNSIGNED NULL,
  theme_slug VARCHAR(100) NOT NULL,
  inherit_global TINYINT(1) NOT NULL DEFAULT 0,
  draft_layout_json LONGTEXT NULL,
  published_layout_json LONGTEXT NULL,
  draft_settings_json LONGTEXT NULL,
  published_settings_json LONGTEXT NULL,
  draft_updated_at DATETIME NULL,
  published_at DATETIME NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_hb53_scope_theme (scope_key,theme_slug),
  INDEX idx_hb53_tenant_theme (tenant_id,theme_slug),
  INDEX idx_hb53_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_builder_revisions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  profile_id BIGINT UNSIGNED NOT NULL,
  revision_no INT UNSIGNED NOT NULL DEFAULT 1,
  layout_json LONGTEXT NOT NULL,
  settings_json LONGTEXT NULL,
  action_key VARCHAR(50) NOT NULL DEFAULT 'publish_backup',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_hb53_revision (profile_id,revision_no),
  INDEX idx_hb53_revision_created (profile_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','5.3.0'),
('homepage_builder_engine_version','530'),
('homepage_builder53_seeded','0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
