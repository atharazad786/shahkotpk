# ShahkotPK v4.1.0 Commercial Release Audit

Base: **v4.0.4**  
Target: **v4.1.0**

## Validation results

- Full PHP files checked: **242**
- PHP syntax errors: **0**
- JavaScript files checked: **10**
- JavaScript syntax errors: **0**
- Literal `__DIR__` include references checked: **358**
- Missing literal includes: **0**
- Permission keys registered: **92**
- Permission keys referenced: **92**
- Unknown permission keys: **0**
- App-level functions indexed: **588**
- Duplicate app-level function names: **0**
- Required v4.1 routes checked: **24**
- Missing required v4.1 routes: **0**
- New v4.1 database tables: **19**
- Parsed migration statements: **21**
- Compatibility-sensitive union/match constructs in changed PHP: **0**
- `4.1.0.sql` present in updater payload: **PASS**
- Files added/changed versus v4.0.4: **42**

## New database tables

- `bulk_import_jobs`
- `franchise_settlements`
- `listing_boosts`
- `media_library`
- `mobile_push_devices_v2`
- `monetization_commission_rules`
- `not_found_logs`
- `notification_rules`
- `outbound_campaigns`
- `pos_receipt_profiles`
- `qr_assets`
- `redirect_rules`
- `regression_test_runs`
- `self_service_ad_campaigns`
- `seo_health_scans`
- `subscription_renewal_events`
- `subscription_renewal_profiles`
- `unified_inbox_messages`
- `unified_inbox_threads`

## Static audit outcome

**PASS**

This is a static/package validation. Payment providers, SMTP/WhatsApp providers, cron execution, external network APIs and live production data still require post-install environment testing.
