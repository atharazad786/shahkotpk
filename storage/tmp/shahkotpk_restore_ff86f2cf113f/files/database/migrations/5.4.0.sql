-- ShahkotPK v5.4.0 — Advanced Map & Location Discovery + Landing Banner Pack
-- Additive / non-destructive migration.

CREATE TABLE IF NOT EXISTS `location_search_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `query_text` VARCHAR(100) NOT NULL DEFAULT '',
  `latitude` DECIMAL(10,7) NOT NULL,
  `longitude` DECIMAL(10,7) NOT NULL,
  `radius_km` DECIMAL(8,2) NOT NULL DEFAULT 5.00,
  `types_csv` VARCHAR(180) NOT NULL DEFAULT '',
  `result_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_location_search_tenant_created` (`tenant_id`,`created_at`),
  KEY `idx_location_search_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`setting_key`,`setting_value`) VALUES
('location_discovery_enabled','1'),
('location_default_radius_km','5'),
('location_max_radius_km','50'),
('location_results_limit','30'),
('location_live_results','1'),
('location_auto_nearby','0'),
('location_show_distance','1'),
('location_directions_enabled','1'),
('location_analytics_enabled','1'),
('v54_banner_pack_enabled','1'),
('v54_banner_visual_only','1'),
('v54_banner_pack_position','prepend'),
('v54_banner_1_enabled','1'),
('v54_banner_2_enabled','1'),
('v54_banner_3_enabled','1'),
('v54_banner_1_url','/search.php'),
('v54_banner_2_url','/businesses.php'),
('v54_banner_3_url','/city-discovery.php');
