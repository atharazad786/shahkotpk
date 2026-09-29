SHAHKOTPK EMERGENCY BOOT RECOVERY 9.0.2
========================================
Purpose: Restore public/Admin boot after v9.0.0 HTTP 500 without deleting database data.

HOW TO USE (cPanel):
1. Backup public_html/app/layout.php and public_html/app/permissions.php if desired.
2. Upload this ZIP to public_html (the folder containing index.php).
3. Extract it directly there and OVERWRITE matching files.
4. Visit:
   https://shahkotpk.com/shahkot-emergency-902.php?key=e08e5d5a08b1d6d2f22a851a
5. Click Reset PHP OPcache once.
6. Test https://shahkotpk.com and /admin/login.php.
7. If still HTTP 500, refresh the main site once, then reload the diagnostic URL and send the newest fatal/error lines.
8. Delete shahkot-emergency-902.php after diagnosis.

WHAT THIS PACKAGE CHANGES:
- Restores app/layout.php and app/permissions.php to the last v8.0.0 shared-shell versions.
- Temporarily pauses cli/v800-worker.php and cli/v900-worker.php so existing cron jobs cannot overload MySQL during recovery.
- Does NOT run SQL.
- Does NOT delete v9 tables or records.
- Does NOT touch config/, storage/, uploads/, logos, API keys, users, tenants, or business data.
- v9-specific Admin menu items are temporarily hidden until the final root cause is repaired.

DIAGNOSTIC KEY:
e08e5d5a08b1d6d2f22a851a
