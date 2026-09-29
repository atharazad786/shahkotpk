# ShahkotPK v13.9.4 Validation Report

## Input/source validation
- Production manual-full backup inspected: `ShahkotPK_20260831_224135_manual_full_v13.9.3_7486377a.zip`.
- Backup manifest format: `ShahkotPK-System-Backup`.
- Backup source version: **13.9.3**.
- Input backup SHA-256: `c165d5e9473d500113daa06ae1ed839523769b6b9d970b9b2f8ccb4a2a5b71f7`.

## Source-of-truth chain traced
- Active `/index.php` obtains the CMS home row and trusts its `theme_slug`.
- Production database CMS home row is `theme_slug=city-listing-motion` and `render_mode=theme`.
- `settings.landing_theme_slug=city-guide-pro`, but that setting is not authoritative once the CMS home row already contains a theme slug.
- Current generic `cms_theme_css_url()` does not include `city-listing-motion` in the built-in theme catalog.
- `cms_themes` has no saved custom-theme rows, so the CSS resolver falls back to `metro-portal.css`.
- Current generic `cms_render_page_body()` also falls back to `themes/landing/metro-portal.php` when a matching reference-theme template file is absent.
- The actual City Listing Motion implementation is still present through `app/landing_reference_themes_v51.php`, `app/homepage_builder_v53.php`, `assets/reference-theme-structure-5.1.1.css`, `assets/themes/landing/city-listing-motion.css`, and the reference/builder JS assets.
- Homepage Builder has a published global `city-listing-motion` profile preserved in the production database.

## Historical source validation
- Recovery Center database record found for exact source backup:
  `ShahkotPK_20260831_011650_pre_update_v13.8.1_611f0ad7.zip`
- Recorded source version: **13.8.1**.
- Recorded state: `ready`.
- Recorded server path: `/home/shahkotp/public_html/storage/backups/ShahkotPK_20260831_011650_pre_update_v13.8.1_611f0ad7.zip`.
- The manual full backup excludes `storage/backups`, so the historical ZIP bytes are intentionally not duplicated inside the uploaded archive. v13.9.4 validates the server-side backup manifest and restores only `files/index.php` from it at the first homepage request.
- Database snapshot contains 14 automatic restore-point records before post-backup pruning. Configured automatic retention is 10; v13.8.1 remains within the retained recent set for this update sequence.

## Preservation scope
The update payload does **not** contain LMS, Classifieds, Service Provider Pro, map/runtime backend, upload, or config files. It does not overwrite CMS home/theme data. Primary exact restore scope is only `/index.php`.

## Static validation
- Full uploaded v13.9.3 source PHP lint: **631 files / 0 syntax errors**.
- Full uploaded v13.9.3 JavaScript syntax check: **118 files / 0 syntax errors**.
- v13.9.4 package PHP lint: **2 files / 0 syntax errors**.
- Required City Listing Motion reference-theme/Builder dependencies: **PASS**.
- Reference structural CSS is loaded before individual City Listing Motion CSS in fallback path: **PASS**.
- Migration is non-destructive: no `DELETE`, `TRUNCATE`, or `DROP`: **PASS**.
- Update manifest target version `13.9.4` is higher than uploaded current source `13.9.3`: **PASS**.

## Runtime restore harness
A synthetic ShahkotPK root with a validated v13.8.1 Recovery Center ZIP was executed through `sk1394_restore_exact_landing_index()`.
- First run: `status=restored`: **PASS**.
- Restored target SHA-256 equals historical source SHA-256: **PASS**.
- Restore report written under `storage/logs/landing-exact-restore-v1394.json`: **PASS**.
- Second run: `status=already_exact` (idempotent): **PASS**.
- Local CLI lacked PHP ZipArchive, so the helper's PharData compatibility path was also exercised successfully. Production Recovery Center itself requires ZipArchive and therefore can use the native ZIP path.

## Production-runtime boundary
The package has been statically validated against the uploaded production backup and the exact-restore mechanism has been runtime-tested in an isolated harness. The final browser rendering against the live production database/server can only be verified after installation. On first homepage load, v13.9.4 validates the historical v13.8.1 server backup, atomically restores only the exact historical `index.php`, verifies SHA-256, writes a restore report, and reloads the homepage once.
