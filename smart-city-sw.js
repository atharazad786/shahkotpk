const CACHE='shahkotpk-smart-city-v630';
const CORE=['/smart-city.php','/assets/smart-city-6.3.0.css','/assets/smart-city-6.3.0.js'];
const PRIVATE_PREFIXES=['/citizen.php','/complaints.php','/wallet.php','/notifications.php','/health-report.php','/my-health.php','/health-network-account.php','/my-orders.php','/my-properties.php','/my-blood.php','/admin/','/api/'];
self.addEventListener('install',e=>e.waitUntil(caches.open(CACHE).then(c=>c.addAll(CORE)).catch(()=>{})));
self.addEventListener('activate',e=>e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k.startsWith('shahkotpk-smart-city-')&&k!==CACHE).map(k=>caches.delete(k))))));
self.addEventListener('fetch',e=>{if(e.request.method!=='GET')return;const u=new URL(e.request.url);if(u.origin!==location.origin||PRIVATE_PREFIXES.some(p=>u.pathname.startsWith(p)))return;const cacheable=u.pathname==='/smart-city.php'||u.pathname.startsWith('/assets/smart-city-');if(!cacheable)return;e.respondWith(fetch(e.request).then(r=>{if(r.ok){const copy=r.clone();caches.open(CACHE).then(c=>c.put(e.request,copy)).catch(()=>{})}return r}).catch(()=>caches.match(e.request).then(r=>r||caches.match('/smart-city.php'))))});
