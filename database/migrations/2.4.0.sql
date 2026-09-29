-- ShahkotPK v2.4.0 Homepage CMS / Visual Site Builder

CREATE TABLE IF NOT EXISTS cms_pages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) NOT NULL UNIQUE,
  title VARCHAR(220) NOT NULL,
  page_type ENUM('home','landing','page') NOT NULL DEFAULT 'page',
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  is_home TINYINT(1) NOT NULL DEFAULT 0,
  render_mode ENUM('theme','manual') NOT NULL DEFAULT 'theme',
  theme_slug VARCHAR(120) NOT NULL DEFAULT 'metro-portal',
  layout_json LONGTEXT NULL,
  custom_css LONGTEXT NULL,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(700) NULL,
  seo_keywords VARCHAR(700) NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  published_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cms_pages_home (is_home,status),
  INDEX idx_cms_pages_type_status (page_type,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_revisions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id BIGINT UNSIGNED NOT NULL,
  version_no INT NOT NULL DEFAULT 1,
  snapshot_json LONGTEXT NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cms_revision_page (page_id,version_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_media (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  file_url VARCHAR(600) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NULL,
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  alt_text VARCHAR(255) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cms_media_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_themes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name VARCHAR(180) NOT NULL,
  description VARCHAR(700) NULL,
  css_url VARCHAR(600) NOT NULL,
  preview_url VARCHAR(600) NULL,
  layout_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
