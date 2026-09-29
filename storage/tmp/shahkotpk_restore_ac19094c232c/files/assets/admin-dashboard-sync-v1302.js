/* ShahkotPK v13.0.3.4 — conservative dashboard route repair; no visual badge injection. */
(()=>{'use strict';
if(!/^\/admin\/?(?:index\.php)?$/i.test(location.pathname))return;
const cfg=window.ShahkotPKAdminSyncV1302||{},routes=cfg.routes||{};
const clean=s=>String(s||'').replace(/\s+/g,' ').trim().toLowerCase();
const aliases=[['businesses','businesses'],['business manager','businesses'],['categories','categories'],['cities','cities'],['access control','access control'],['packages','packages'],['orders','orders'],['delivery','delivery'],['city content','city content'],['homepage','homepage'],['landing menu','landing menu'],['admin tools','admin tools'],['system integrity','system integrity']];
const knownLegacy=/\/(?:admin\/(?:business-manager|business-directory-manager|homepage-settings|landing-navigation|system-check)(?:\.php)?|#)$/i;
function routeFor(text){const t=clean(text);for(const [needle,key] of aliases)if(t===needle||t.startsWith(needle+' '))return routes[key]||'';return '';}
function shouldRepair(a){const raw=(a.getAttribute('href')||'').trim();return raw===''||raw==='#'||/^javascript:/i.test(raw)||knownLegacy.test(raw);}
function run(){const main=document.querySelector('main,.admin-content,.content-wrapper,[role="main"]');if(!main)return;for(const a of main.querySelectorAll('a[href]')){if(a.closest('aside,.sidebar,.admin-sidebar,#sidebar'))continue;if(!shouldRepair(a))continue;const text=(a.getAttribute('data-title')||a.getAttribute('aria-label')||a.textContent||'').slice(0,140);const url=routeFor(text);if(url){a.href=url;a.dataset.sk13034DashboardLink='1';}}}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',run,{once:true});else run();
})();
