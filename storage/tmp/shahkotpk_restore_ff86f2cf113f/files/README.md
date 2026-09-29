# ShahkotPK v13.9.3 — Exact v13.8.1 Active Landing Restore

This release directly replaces the active `/index.php` landing entry point and restores the public presentation runtime that was in effect through v13.8.1.

Restored:
- `/index.php` active landing entry chain from the pre-v13.8.3 full-repair lineage.
- `app/public_runtime_v1101.php` from v13.5.0, the last public-runtime revision before v13.8.1 (v13.6.0–v13.8.2 did not modify it).
- Native header sync and landing-section sync from stable v13.0.3.4.
- MapLibre/OpenFreeMap v13.4 public assets that remained active through v13.8.1.

Preserved:
- v13.8.3 central compatibility bootstrap only (no later visual/menu injections).
- LMS, Classifieds, Service Provider Pro, Location data, users, uploads, orders and DB data.
- No database content rollback or deletion.
- v13.9 Smart Search homepage presentation is disabled.
