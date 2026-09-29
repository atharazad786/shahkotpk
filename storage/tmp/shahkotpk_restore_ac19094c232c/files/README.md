# ShahkotPK v13.8.5 — Native Front Menu Recovery

Targeted landing-menu hotfix after v13.8.4.

Confirmed regression signature: the legacy Step-3 managed navigation (`#sk1302-public-nav`) was rendering on top of the native ShahkotPK menu. This package:

- overwrites `public-header-sync-v1302.js/css` with the known-good native-only cleanup contract;
- never builds, reorders, replaces or hides the native ShahkotPK navigation;
- removes legacy managed navigation and restores native elements hidden by old Step-3 classes/ARIA markers;
- adds a bounded `native-front-menu-lock-v1385` guard with no MutationObserver;
- cache-busts menu assets to v13.8.5;
- preserves v13.8.4 bounded OpenFreeMap behavior and all v13.8.3 audit fixes;
- does not alter users, content, uploads, map marker data or menu definitions in the database.
