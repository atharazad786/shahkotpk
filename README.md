# ShahkotPK v13.9.2 — White Card Fix + 3D Surface Upgrade

Base: restored **v13.8.5+** production line.

This release continues the real active `city-listing-motion` / Shahkot Pulse front page and fixes the remaining front-page areas that were appearing white, faint or blank.

## Fixed in this release
- **Live City Portal hero card** is now a strong dark premium 3D card, so the heading and copy are readable instead of looking white-on-white.
- **No-image cards/media blocks** across the homepage now render as designed 3D placeholders instead of plain white blank surfaces.
- **Complete City Guide** blank card becomes a proper 3D discovery tile even when no section image exists.
- featured, deals, property, event, business, growth, news and blog media areas all get fallback 3D surfaces when an image is missing.
- richer overlays are also applied when images do exist, so cards feel more polished overall.

## Preserved
- native public menu routes/order
- database data/schema
- uploads/storage
- LMS
- Classifieds
- Service Provider Pro
- Maps
- backend business logic
- v13.8.6 admin-sidebar repair
- v13.9.0 Growth Suite stylesheet integration
- v13.9.1 Near Me + Trusted Local Discovery fixes

No SQL migration is required.
