
-- ShahkotPK v3.1.0 Google Maps City Discovery
ALTER TABLE city_portal_items
  ADD COLUMN latitude DECIMAL(10,7) NULL AFTER map_url,
  ADD COLUMN longitude DECIMAL(10,7) NULL AFTER latitude;

INSERT INTO settings(setting_key,setting_value) VALUES
('google_maps_enabled','1'),
('google_maps_api_key',''),
('google_maps_default_lat','31.5709000'),
('google_maps_default_lng','73.4853000'),
('google_maps_default_zoom','14'),
('google_maps_map_type','roadmap'),
('google_maps_home_enabled','1'),
('google_maps_vertical_pages_enabled','1'),
('google_maps_businesses_enabled','1'),
('google_maps_admin_enabled','1'),
('google_maps_show_businesses','1'),
('google_maps_show_guide','1'),
('google_maps_show_deals','1'),
('google_maps_show_events','1'),
('google_maps_show_jobs','1'),
('google_maps_show_property','1'),
('google_maps_height','560'),
('google_maps_fit_markers','1'),
('google_maps_places_search','1')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
