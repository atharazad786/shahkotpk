
(function(){
 const buttons=document.querySelectorAll('[data-near-me]');
 function distance(lat1,lon1,lat2,lon2){const r=6371,dLat=(lat2-lat1)*Math.PI/180,dLon=(lon2-lon1)*Math.PI/180,a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;return 2*r*Math.asin(Math.sqrt(a))}
 function locate(){if(!navigator.geolocation){alert('Location is not supported by this browser.');return}buttons.forEach(b=>{b.disabled=true;b.textContent='◎ Finding nearby businesses...'});navigator.geolocation.getCurrentPosition(pos=>{const cards=[...document.querySelectorAll('[data-near-business]')];cards.forEach(c=>{const lat=Number(c.dataset.lat),lng=Number(c.dataset.lng);c.dataset.distance=(lat&&lng)?distance(pos.coords.latitude,pos.coords.longitude,lat,lng):999999});cards.sort((a,b)=>Number(a.dataset.distance)-Number(b.dataset.distance));const grid=document.querySelector('#nearbySection .cgp-local-business-grid');if(grid)cards.forEach(c=>grid.appendChild(c));buttons.forEach(b=>{b.disabled=false;b.textContent='✓ Nearby results sorted by your location'});document.getElementById('nearbySection')?.scrollIntoView({behavior:'smooth'})},()=>buttons.forEach(b=>{b.disabled=false;b.textContent='◎ Location unavailable — showing popular businesses'}),{enableHighAccuracy:false,timeout:8000,maximumAge:300000})}
 buttons.forEach(b=>b.addEventListener('click',locate));
})();
