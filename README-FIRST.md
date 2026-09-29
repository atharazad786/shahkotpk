# ShahkotPK v3.4.3 Runtime Compatibility Repair

Production diagnostics showed:
- `app/store.php:14` ParseError around `int|string`
- `app/accounting.php:72` ParseError around PHP 8 `match`
- historical `json_encode()` / PDO handler mismatch messages

This patch removes the PHP 8-only parser points from the affected runtime paths, adds safe string helper polyfills, hardens CMS JSON fallback, and provides `repair-finish.php` to reset OPcache after direct cPanel replacement.

For a broken site, extract the Direct Repair ZIP first, visit `/repair-finish.php`, then use the normal updater after Admin is reachable.
