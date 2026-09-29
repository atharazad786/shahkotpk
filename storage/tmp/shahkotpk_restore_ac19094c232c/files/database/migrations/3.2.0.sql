-- ShahkotPK v3.2.0 Complete News Portal

CREATE TABLE IF NOT EXISTS news_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(140) NOT NULL,
  name_ur VARCHAR(180) NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  description VARCHAR(500) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_news_category_status (status,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NULL,
  post_type ENUM('article','video') NOT NULL DEFAULT 'article',
  language ENUM('en','ur','bilingual') NOT NULL DEFAULT 'bilingual',
  title_en VARCHAR(300) NULL,
  title_ur VARCHAR(400) NULL,
  slug VARCHAR(340) NOT NULL UNIQUE,
  excerpt_en VARCHAR(800) NULL,
  excerpt_ur VARCHAR(1000) NULL,
  body_en LONGTEXT NULL,
  body_ur LONGTEXT NULL,
  image_url VARCHAR(700) NULL,
  video_url VARCHAR(700) NULL,
  source_name VARCHAR(180) NULL,
  source_url VARCHAR(700) NULL,
  reporter_name VARCHAR(180) NULL,
  is_breaking TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_news_status_date (status,published_at),
  INDEX idx_news_type (post_type,status),
  INDEX idx_news_language (language,status),
  INDEX idx_news_breaking (is_breaking,status,published_at),
  INDEX idx_news_featured (is_featured,status,published_at),
  INDEX idx_news_category (category_id,status),
  INDEX idx_news_city (city_id,status),
  CONSTRAINT fk_news_category FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_news_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
  CONSTRAINT fk_news_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_news_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news_widgets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  widget_key VARCHAR(80) NOT NULL UNIQUE,
  title_en VARCHAR(180) NOT NULL,
  title_ur VARCHAR(220) NULL,
  widget_type ENUM('breaking','featured','latest','video','category') NOT NULL DEFAULT 'latest',
  language ENUM('en','ur','bilingual') NOT NULL DEFAULT 'bilingual',
  category_id BIGINT UNSIGNED NULL,
  item_limit INT NOT NULL DEFAULT 6,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  settings_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_news_widgets_order (enabled,sort_order),
  CONSTRAINT fk_news_widget_category FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO news_categories(name_en,name_ur,slug,description,status,sort_order) VALUES
('Local','مقامی','local','City and community news from Shahkot.',1,10),
('Business','کاروبار','business','Local business, market and commercial news.',1,20),
('Education','تعلیم','education','Schools, colleges and education updates.',1,30),
('Health','صحت','health','Health, hospitals and wellness updates.',1,40),
('Sports','کھیل','sports','Local and regional sports coverage.',1,50),
('Community','کمیونٹی','community','Community announcements and public updates.',1,60),
('Technology','ٹیکنالوجی','technology','Technology and digital updates.',1,70);

INSERT IGNORE INTO news_widgets(widget_key,title_en,title_ur,widget_type,language,item_limit,enabled,sort_order) VALUES
('breaking','Breaking News','تازہ ترین خبریں','breaking','bilingual',6,1,10),
('featured','Featured Stories','نمایاں خبریں','featured','bilingual',5,1,20),
('urdu-news','Urdu News','اردو خبریں','latest','ur',4,1,30),
('english-news','English News','انگریزی خبریں','latest','en',4,1,40),
('video-news','Video News','ویڈیو خبریں','video','bilingual',4,1,50),
('latest','Latest News','تازہ خبریں','latest','bilingual',6,0,60);

INSERT INTO settings(setting_key,setting_value) VALUES
('news_portal_enabled','1'),
('news_home_enabled','1'),
('news_breaking_enabled','1'),
('news_video_enabled','1'),
('news_default_language','en'),
('news_items_per_page','12'),
('news_show_views','1'),
('news_allow_scheduled','1'),
('news_homepage_version','0')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
