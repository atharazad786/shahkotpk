-- ShahkotPK v12.6.0 — Smart City Weather + Greeting + Officer Sync
-- Additive migration only. No existing records are deleted.
CREATE TABLE IF NOT EXISTS city_portal_settings_v1251 (
  tenant_id BIGINT UNSIGNED NOT NULL,
  weather_enabled TINYINT(1) NOT NULL DEFAULT 1,
  weather_title VARCHAR(160) NOT NULL DEFAULT 'Live Shahkot Weather',
  latitude DECIMAL(10,6) NOT NULL DEFAULT 31.570900,
  longitude DECIMAL(10,6) NOT NULL DEFAULT 73.485300,
  cards_3d_enabled TINYINT(1) NOT NULL DEFAULT 1,
  officers_enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_information_media_v1251 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  item_key VARCHAR(190) NOT NULL,
  media_type VARCHAR(30) NOT NULL DEFAULT 'image',
  file_url VARCHAR(500) NOT NULL,
  caption VARCHAR(240) NULL,
  sort_order INT NOT NULL DEFAULT 10,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_city_media_tenant_item (tenant_id,item_key,enabled,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_officers_v1251 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  designation VARCHAR(190) NOT NULL,
  vision_message TEXT NULL,
  photo_url VARCHAR(500) NULL,
  office_name VARCHAR(190) NULL,
  phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  sort_order INT NOT NULL DEFAULT 10,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_city_officer_tenant (tenant_id,enabled,featured,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO city_portal_settings_v1251(tenant_id,weather_enabled,weather_title,latitude,longitude,cards_3d_enabled,officers_enabled,updated_at)
VALUES(0,1,'Live Shahkot Weather',31.570900,73.485300,1,1,NOW())
ON DUPLICATE KEY UPDATE updated_at=updated_at;

CREATE TABLE IF NOT EXISTS city_smart_settings_v1260 (
  tenant_id BIGINT UNSIGNED NOT NULL,
  greeting_enabled TINYINT(1) NOT NULL DEFAULT 1,
  weather_clock_enabled TINYINT(1) NOT NULL DEFAULT 1,
  weather_motion_enabled TINYINT(1) NOT NULL DEFAULT 1,
  official_sync_enabled TINYINT(1) NOT NULL DEFAULT 1,
  scheduled_sync_enabled TINYINT(1) NOT NULL DEFAULT 0,
  last_sync_at DATETIME NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_officer_sync_v1260 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  role_key VARCHAR(80) NOT NULL,
  officer_id BIGINT UNSIGNED NULL,
  official_title VARCHAR(190) NOT NULL,
  office_name VARCHAR(190) NULL,
  source_label VARCHAR(190) NULL,
  source_url VARCHAR(500) NULL,
  parser_key VARCHAR(80) NULL,
  auto_sync TINYINT(1) NOT NULL DEFAULT 0,
  last_status VARCHAR(40) NOT NULL DEFAULT 'pending',
  last_message VARCHAR(500) NULL,
  source_verified_at DATETIME NULL,
  last_checked_at DATETIME NULL,
  last_changed_at DATETIME NULL,
  sort_order INT NOT NULL DEFAULT 10,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_city_officer_role (tenant_id,role_key),
  KEY idx_city_officer_sync (tenant_id,auto_sync,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS city_sync_runs_v1260 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  run_type VARCHAR(30) NOT NULL DEFAULT 'manual',
  status VARCHAR(30) NOT NULL DEFAULT 'ok',
  checked_count INT NOT NULL DEFAULT 0,
  changed_count INT NOT NULL DEFAULT 0,
  failed_count INT NOT NULL DEFAULT 0,
  details_json LONGTEXT NULL,
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finished_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_city_sync_runs (tenant_id,started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO city_smart_settings_v1260(tenant_id,greeting_enabled,weather_clock_enabled,weather_motion_enabled,official_sync_enabled,scheduled_sync_enabled,updated_at)
VALUES(0,1,1,1,1,0,NOW())
ON DUPLICATE KEY UPDATE updated_at=updated_at;

-- Seed currently supported, official-source-backed profiles only when a matching designation does not already exist.
INSERT INTO city_officers_v1251(tenant_id,name,designation,vision_message,photo_url,office_name,phone,email,sort_order,featured,enabled,updated_by,created_at,updated_at)
SELECT 0,'Rana Hameed','Deputy Commissioner, Nankana Sahib','','','District Administration Nankana Sahib','','',10,1,1,NULL,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Deputy Commissioner, Nankana Sahib');

INSERT INTO city_officers_v1251(tenant_id,name,designation,vision_message,photo_url,office_name,phone,email,sort_order,featured,enabled,updated_by,created_at,updated_at)
SELECT 0,'Sana Sharafat','Assistant Commissioner, Shahkot','','','Assistant Commissioner Office Shahkot','','',20,1,1,NULL,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Assistant Commissioner, Shahkot');

INSERT INTO city_officers_v1251(tenant_id,name,designation,vision_message,photo_url,office_name,phone,email,sort_order,featured,enabled,updated_by,created_at,updated_at)
SELECT 0,'Rana Muhammad Arshad','Member Provincial Assembly (MPA)','','','PP-133 / Shahkot area','','',70,0,1,NULL,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Member Provincial Assembly (MPA)');

INSERT INTO city_officers_v1251(tenant_id,name,designation,vision_message,photo_url,office_name,phone,email,sort_order,featured,enabled,updated_by,created_at,updated_at)
SELECT 0,'Muhammad Arshad Sahi','Member National Assembly (MNA)','','','NA-111 Nankana Sahib-I','','',80,0,1,NULL,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Member National Assembly (MNA)');

-- Required role registry. Unknown/currently-unparseable roles stay unlinked instead of publishing guessed names.
INSERT INTO city_officer_sync_v1260(tenant_id,role_key,officer_id,official_title,office_name,source_label,source_url,parser_key,auto_sync,last_status,last_message,source_verified_at,sort_order)
VALUES
(0,'dc_nankana',(SELECT id FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Deputy Commissioner, Nankana Sahib' ORDER BY id LIMIT 1),'Deputy Commissioner, Nankana Sahib','District Administration Nankana Sahib','District Nankana Sahib official portal','https://nankana.punjab.gov.pk/dc_message','dc_nankana',1,'seeded','Official district portal currently identifies Rana Hameed as Deputy Commissioner.','2026-08-29 00:00:00',10),
(0,'ac_shahkot',(SELECT id FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Assistant Commissioner, Shahkot' ORDER BY id LIMIT 1),'Assistant Commissioner, Shahkot','Assistant Commissioner Office Shahkot','District Nankana Sahib official core team','https://nankana.punjab.gov.pk/core_team','ac_shahkot',1,'seeded','Official district core-team page currently lists Sana Sharafat for Shahkot.','2026-08-29 00:00:00',20),
(0,'chief_officer_mc_shahkot',NULL,'Chief Officer, Municipal Committee Shahkot','Municipal Committee Shahkot','Punjab Local Government & Community Development','https://lgcd.punjab.gov.pk/district-nankana-sahib','manual',0,'needs_verification','Role added. Current officer name is not auto-published until a current official source is verified.',NULL,30),
(0,'ms_thq_shahkot',NULL,'Medical Superintendent, THQ Hospital Shahkot','THQ Hospital Shahkot','Punjab Health & Population Department','https://pshealthpunjab.gov.pk/Home/ViewSystemOrders?Id=107893&Type=2','ms_thq',1,'needs_verification','Latest supported official reference will be checked; manual review is required if the source is older or ambiguous.',NULL,40),
(0,'sho_sadar_shahkot',NULL,'SHO Sadar Shahkot','Punjab Police','Punjab Police Nankana Sahib directory','https://www.punjabpolice.gov.pk/nankana_directory','manual',0,'needs_verification','Official directory currently exposes station contacts but not a reliably parseable current SHO name for this role.',NULL,50),
(0,'sho_city_shahkot',NULL,'SHO City Shahkot','Punjab Police','Punjab Police Nankana Sahib directory','https://www.punjabpolice.gov.pk/nankana_directory','manual',0,'needs_verification','Official directory currently exposes station contacts but not a reliably parseable current SHO name for this role.',NULL,60),
(0,'mpa_shahkot',(SELECT id FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Member Provincial Assembly (MPA)' ORDER BY id LIMIT 1),'Member Provincial Assembly (MPA)','PP-133 / Shahkot area','Provincial Assembly of the Punjab / ECP','https://www.pap.gov.pk/uploads/short_list.pdf.pdf','manual',0,'seeded','Rana Muhammad Arshad is seeded from official Punjab Assembly/ECP records; use Sync status/manual review for changes.','2026-08-29 00:00:00',70),
(0,'mna_shahkot',(SELECT id FROM city_officers_v1251 WHERE tenant_id=0 AND designation='Member National Assembly (MNA)' ORDER BY id LIMIT 1),'Member National Assembly (MNA)','NA-111 Nankana Sahib-I','National Assembly of Pakistan','https://www.na.gov.pk/en/all-members.php','mna_na111',1,'seeded','National Assembly current member listing identifies Muhammad Arshad Sahi for NA-111.','2026-08-29 00:00:00',80)
ON DUPLICATE KEY UPDATE officer_id=COALESCE(officer_id,VALUES(officer_id)),official_title=VALUES(official_title),office_name=VALUES(office_name),source_label=VALUES(source_label),source_url=VALUES(source_url),parser_key=VALUES(parser_key),auto_sync=VALUES(auto_sync),sort_order=VALUES(sort_order),updated_at=NOW();

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','12.6.0'),
('city_information_version','12.6.0'),
('city_portal_version','12.6.0'),
('city_information_url','/city-guide.php'),
('city_content_studio_url','/admin/city-content-studio.php')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
