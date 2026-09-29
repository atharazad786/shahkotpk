-- ShahkotPK v11.0.2 — Public Menu / Dropdown Navigation Repair
DELETE FROM public_navigation_rules_v1100 WHERE nav_key IN (
'home','businesses','marketplace','property','health','events',
'my_shahkot','ask_shahkotpk','business_leads','deals','compare','wallet','favorites','referrals','my_bookings','notifications','community'
);
INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','11.0.2'),
('unified_experience_version','1102'),
('public_navigation_version','11.0.2'),
('public_navigation_repair_v1102','1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
