-- ShahkotPK v7.0.6 Independent Brand Assets Hotfix
-- Freeze any inherited/fallback brand assets into their own slots once, so future edits are independent.

UPDATE tenant_brand_assets_v705 b
LEFT JOIN tenant_header_settings_v702 h ON h.tenant_id=b.tenant_id
SET b.desktop_dark_logo_path = COALESCE(NULLIF(b.desktop_dark_logo_path,''), NULLIF(h.logo_path,''))
WHERE b.desktop_dark_logo_path IS NULL OR b.desktop_dark_logo_path='';

UPDATE tenant_brand_assets_v705 b
LEFT JOIN tenant_header_settings_v702 h ON h.tenant_id=b.tenant_id
SET b.desktop_light_logo_path = COALESCE(NULLIF(b.desktop_light_logo_path,''), NULLIF(b.desktop_dark_logo_path,''), NULLIF(h.logo_path,''))
WHERE b.desktop_light_logo_path IS NULL OR b.desktop_light_logo_path='';

UPDATE tenant_brand_assets_v705 b
LEFT JOIN tenant_header_settings_v702 h ON h.tenant_id=b.tenant_id
SET b.mobile_dark_logo_path = COALESCE(NULLIF(b.mobile_dark_logo_path,''), NULLIF(h.mobile_logo_path,''), NULLIF(b.desktop_dark_logo_path,''), NULLIF(h.logo_path,''))
WHERE b.mobile_dark_logo_path IS NULL OR b.mobile_dark_logo_path='';

UPDATE tenant_brand_assets_v705 b
LEFT JOIN tenant_header_settings_v702 h ON h.tenant_id=b.tenant_id
SET b.mobile_light_logo_path = COALESCE(NULLIF(b.mobile_light_logo_path,''), NULLIF(b.desktop_light_logo_path,''), NULLIF(b.mobile_dark_logo_path,''), NULLIF(h.mobile_logo_path,''), NULLIF(b.desktop_dark_logo_path,''), NULLIF(h.logo_path,''))
WHERE b.mobile_light_logo_path IS NULL OR b.mobile_light_logo_path='';

UPDATE tenant_brand_assets_v705
SET favicon_dark_path = COALESCE(NULLIF(favicon_dark_path,''), NULLIF(mobile_dark_logo_path,''), NULLIF(desktop_dark_logo_path,''))
WHERE favicon_dark_path IS NULL OR favicon_dark_path='';

UPDATE tenant_brand_assets_v705
SET favicon_light_path = COALESCE(NULLIF(favicon_light_path,''), NULLIF(mobile_light_logo_path,''), NULLIF(desktop_light_logo_path,''), NULLIF(favicon_dark_path,''))
WHERE favicon_light_path IS NULL OR favicon_light_path='';

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','7.0.6'),
('independent_brand_assets_version','706')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
