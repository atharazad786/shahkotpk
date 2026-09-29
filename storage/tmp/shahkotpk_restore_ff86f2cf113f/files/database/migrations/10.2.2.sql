-- ShahkotPK v10.2.2 — Sidebar Manager Pro + Admin Tools placement
CREATE TABLE IF NOT EXISTS admin_navigation_overrides_v1022 (
 module_key VARCHAR(120) NOT NULL, enabled TINYINT(1) NULL, placement VARCHAR(24) NULL,
 label_override VARCHAR(160) NULL, icon_override VARCHAR(32) NULL,
 category_key VARCHAR(90) NULL, category_label VARCHAR(120) NULL, category_order INT NULL,
 subgroup_key VARCHAR(90) NULL, subgroup_label VARCHAR(120) NULL, subgroup_order INT NULL,
 parent_key VARCHAR(120) NULL, sort_order INT NULL, updated_by BIGINT NULL, updated_at DATETIME NULL,
 PRIMARY KEY(module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS admin_navigation_groups_v1022 (
 category_key VARCHAR(90) NOT NULL, category_label VARCHAR(120) NOT NULL, sort_order INT NOT NULL DEFAULT 80,
 enabled TINYINT(1) NOT NULL DEFAULT 1, collapsible TINYINT(1) NOT NULL DEFAULT 0, default_open TINYINT(1) NOT NULL DEFAULT 1,
 updated_by BIGINT NULL, updated_at DATETIME NULL, PRIMARY KEY(category_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS admin_navigation_native_v1022 (
 native_key VARCHAR(120) NOT NULL, url VARCHAR(500) NOT NULL, detected_label VARCHAR(160) NOT NULL,
 enabled TINYINT(1) NOT NULL DEFAULT 1, managed TINYINT(1) NOT NULL DEFAULT 0,
 label_override VARCHAR(160) NULL, icon_override VARCHAR(32) NULL,
 category_key VARCHAR(90) NULL, category_label VARCHAR(120) NULL, category_order INT NULL DEFAULT 75,
 subgroup_key VARCHAR(90) NULL, subgroup_label VARCHAR(120) NULL, subgroup_order INT NULL DEFAULT 0,
 parent_key VARCHAR(120) NULL, sort_order INT NULL DEFAULT 100, last_seen_at DATETIME NULL,
 updated_by BIGINT NULL, updated_at DATETIME NULL, PRIMARY KEY(native_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS admin_navigation_custom_v1022 (
 module_key VARCHAR(120) NOT NULL, label VARCHAR(160) NOT NULL, url VARCHAR(500) NOT NULL, icon VARCHAR(32) NOT NULL DEFAULT '•',
 enabled TINYINT(1) NOT NULL DEFAULT 1, placement VARCHAR(24) NOT NULL DEFAULT 'sidebar',
 category_key VARCHAR(90) NOT NULL DEFAULT 'extensions', category_label VARCHAR(120) NOT NULL DEFAULT 'EXTENSIONS', category_order INT NOT NULL DEFAULT 80,
 subgroup_key VARCHAR(90) NULL, subgroup_label VARCHAR(120) NULL, subgroup_order INT NOT NULL DEFAULT 0,
 parent_key VARCHAR(120) NULL, sort_order INT NOT NULL DEFAULT 100, permission VARCHAR(120) NULL, description VARCHAR(500) NULL,
 created_by BIGINT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compatibility safety: create the legacy override table only if a previous partial install did not create it.
CREATE TABLE IF NOT EXISTS admin_sidebar_overrides_v1021 (
 module_key VARCHAR(90) NOT NULL, enabled TINYINT(1) NULL DEFAULT NULL, sort_order INT NULL DEFAULT NULL,
 category_key VARCHAR(90) NULL DEFAULT NULL, category_label VARCHAR(120) NULL DEFAULT NULL, category_order INT NULL DEFAULT NULL,
 updated_by BIGINT NULL DEFAULT NULL, updated_at DATETIME NULL DEFAULT NULL, PRIMARY KEY(module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Carry forward v10.2.1 visibility/order/group overrides where available (v10.2.1 is the required base).
INSERT INTO admin_navigation_overrides_v1022(module_key,enabled,category_key,category_label,category_order,sort_order,updated_by,updated_at)
SELECT module_key,enabled,category_key,category_label,category_order,sort_order,updated_by,updated_at FROM admin_sidebar_overrides_v1021
ON DUPLICATE KEY UPDATE enabled=VALUES(enabled),category_key=VALUES(category_key),category_label=VALUES(category_label),category_order=VALUES(category_order),sort_order=VALUES(sort_order),updated_by=VALUES(updated_by),updated_at=VALUES(updated_at);

INSERT INTO settings(setting_key,setting_value) VALUES
('installed_app_version','10.2.2'),
('admin_sidebar_registry_version','10.2.2'),
('admin_sidebar_renderer','navigation_registry_pro'),
('admin_tools_hub_mode','placement_registry')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
