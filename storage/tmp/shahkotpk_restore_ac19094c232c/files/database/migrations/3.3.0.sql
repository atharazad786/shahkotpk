-- ShahkotPK v3.3.0 Commercial CMS, Blog & RBAC Hardening

ALTER TABLE users
  MODIFY role ENUM('customer','user','shopkeeper','blogger','editor','admin') NOT NULL DEFAULT 'customer';

CREATE TABLE IF NOT EXISTS blog_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  description VARCHAR(700) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blog_category_status (status,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NULL,
  language ENUM('en','ur','bilingual') NOT NULL DEFAULT 'en',
  post_type ENUM('article','video') NOT NULL DEFAULT 'article',
  title_en VARCHAR(320) NULL,
  title_ur VARCHAR(420) NULL,
  slug VARCHAR(360) NOT NULL UNIQUE,
  excerpt_en VARCHAR(900) NULL,
  excerpt_ur VARCHAR(1100) NULL,
  body_en LONGTEXT NULL,
  body_ur LONGTEXT NULL,
  cover_image VARCHAR(700) NULL,
  video_url VARCHAR(700) NULL,
  meta_title VARCHAR(320) NULL,
  meta_description VARCHAR(700) NULL,
  canonical_url VARCHAR(700) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  comments_enabled TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blog_author_status (author_id,status),
  INDEX idx_blog_status_date (status,published_at),
  INDEX idx_blog_featured (is_featured,status,published_at),
  INDEX idx_blog_category (category_id,status),
  INDEX idx_blog_city (city_id,status),
  CONSTRAINT fk_blog_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_blog_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_blog_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
  CONSTRAINT fk_blog_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_tags (
  post_id BIGINT UNSIGNED NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY(post_id,tag_id),
  CONSTRAINT fk_blog_post_tag_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_blog_post_tag_tag FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  author_name VARCHAR(160) NOT NULL,
  author_email VARCHAR(190) NULL,
  comment_text TEXT NOT NULL,
  status ENUM('pending','approved','spam','rejected') NOT NULL DEFAULT 'pending',
  ip_hash VARCHAR(128) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  moderated_at DATETIME NULL,
  moderated_by BIGINT UNSIGNED NULL,
  INDEX idx_blog_comment_post_status (post_id,status,created_at),
  CONSTRAINT fk_blog_comment_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_blog_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_blog_comment_moderator FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_widgets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  widget_key VARCHAR(80) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  widget_type ENUM('featured','latest','category','author') NOT NULL DEFAULT 'latest',
  category_id BIGINT UNSIGNED NULL,
  author_id BIGINT UNSIGNED NULL,
  item_limit INT NOT NULL DEFAULT 4,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 10,
  settings_json LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blog_widgets_enabled (enabled,sort_order),
  CONSTRAINT fk_blog_widget_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_blog_widget_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO blog_categories(name,slug,description,status,sort_order) VALUES
('City Life','city-life','Stories, guides and perspectives about local city life.',1,10),
('Business Stories','business-stories','Local business stories, entrepreneurship and market insights.',1,20),
('Education','education','Learning, schools, students and education resources.',1,30),
('Lifestyle','lifestyle','Lifestyle, culture, food and community articles.',1,40),
('Technology','technology','Technology, apps and digital transformation.',1,50);

INSERT IGNORE INTO blog_widgets(widget_key,title,widget_type,item_limit,enabled,sort_order) VALUES
('featured-blogs','Featured from the Blog','featured',3,1,10),
('latest-blogs','Latest Articles','latest',4,1,20);

INSERT INTO settings(setting_key,setting_value) VALUES
('blog_portal_enabled','1'),
('blog_home_enabled','1'),
('blog_comments_enabled','1'),
('blog_comments_moderation','1'),
('blog_video_enabled','1'),
('blog_scheduled_publishing','1'),
('blog_items_per_page','12'),
('blog_show_views','1'),
('blog_homepage_version','0'),
('commercial_rbac_version','330')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

INSERT IGNORE INTO system_roles(name,slug,description,permissions_json,status) VALUES
('Blogger','blogger','Write and manage own blog posts without access to system/finance administration.','["blog.dashboard","blog.posts.create","blog.posts.edit_own","blog.posts.delete_own"]',1),
('Editor','editor','Editorial staff role for blog, news, city content and homepage content.','["dashboard.view","blog.dashboard","blog.posts.create","blog.posts.edit_all","blog.posts.delete_all","blog.posts.publish","blog.categories.manage","blog.tags.manage","blog.comments.manage","blog.widgets.manage","news.manage","homepage.manage","banners.manage","ticker.manage","city_guide.manage","deals.manage","events.manage","jobs.manage","property.manage"]',1),
('News Editor','news-editor','Manage the News Portal without access to finance or system settings.','["news.manage"]',1),
('City Content Editor','city-content-editor','Manage City Guide, Deals, Events, Jobs and Property content.','["city_guide.manage","deals.manage","events.manage","jobs.manage","property.manage"]',1);
