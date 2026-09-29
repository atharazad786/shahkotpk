# ShahkotPK v4.0.0 — Unified Commercial Platform

One updater combining the planned v3.7 Security & Reliability Suite, v3.8 AI Search/Content/Recommendations, and v4.0 Multi-City Franchise + Mobile API + Seller POS.

## Minimum version
3.6.4

## New control centers
- Admin → Security Center
- Admin → System Health
- Admin → AI Studio
- Admin → Franchise Control
- Admin → Mobile API
- Admin → Seller POS

## Public / seller additions
- `/smart-search.php`
- `/recommendations.php`
- `/seller-pos.php`
- `/two-factor.php`
- `/api/v1/`

2FA is OFF by default to avoid administrator lockout before SMTP is configured. External AI is `local` by default and will not send data to a third party unless an administrator explicitly configures an HTTPS provider endpoint/model/API key.
