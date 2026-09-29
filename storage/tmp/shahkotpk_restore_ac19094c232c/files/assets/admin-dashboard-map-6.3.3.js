/* ShahkotPK v13.8.3 — Admin Dashboard map delegates to global no-key MapLibre platform. */
(()=>{'use strict';
async function boot(){let api=null;for(let i=0;i<35;i++){api=window.ShahkotPKMap;if(api?.create)break;await new Promise(r=>setTimeout(r,120));}if(!api?.create)return;const sel=['[data-admin-dashboard-map]','#adminCityMap','#dashboardMap','.admin-dashboard-map','.dashboard-city-map','.dashboard-map-canvas'];let hosts=[];for(const s of sel)try{hosts.push(...document.querySelectorAll(s))}catch(e){}hosts=[...new Set(hosts)].filter(x=>x&&x.offsetWidth>160);if(!hosts.length){try{api.recoverAll?.()}catch(e){}return;}let markers=[];try{markers=await api.fetchMarkers?.()||[]}catch(e){}for(const el of hosts){if(el.dataset.sk1383DashboardMap==='1')continue;el.dataset.sk1383DashboardMap='1';try{await api.create(el,{markers})}catch(e){}}}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();setTimeout(boot,900);
})();
