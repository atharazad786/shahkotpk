# v13.8.5 Release Audit

## Root cause
The visible duplicate/overlapping navigation matches the historical managed Step-3 menu (`#sk1302-public-nav`). v13.8.4 corrected map sizing but did not overwrite the stale `public-header-sync-v1302` asset itself, so a server/browser could continue executing a managed-menu build from an older release.

## Repair
- authoritative `public-header-sync-v1302.js/css` restored from v13.0.3.4 native-menu cleanup;
- dedicated v13.8.5 hard-lock CSS/JS added;
- public runtime cache-bust changed to `?v=1385`;
- inline critical rule hides the legacy managed navigation before deferred JS;
- native hidden markers/classes are reverted;
- no menu-generation code is introduced.

## Safety
No DB menu rows are rewritten. No native menu links are generated, reordered or deleted. No map functionality is removed.
