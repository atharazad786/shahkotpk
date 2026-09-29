(()=>{
'use strict';
if(window.__sk1311MapFix)return;window.__sk1311MapFix=true;
const API='/admin/map-platform-settings-api.php';
const csrf=()=>String((window.SKAI1100Admin&&window.SKAI1100Admin.csrf)||document.querySelector('meta[name="csrf-token"]')?.content||'');
const text=n=>String(n?.textContent||'').replace(/\s+/g,' ').trim();
const pageLooksRight=()=>/Platform Settings/i.test(text(document.querySelector('h1'))||text(document.body))&&/Platform Modules/i.test(text(document.body));
function mapTrigger(el){if(!el)return false;const s=[el.getAttribute?.('data-module'),el.getAttribute?.('data-key'),el.getAttribute?.('data-id'),el.getAttribute?.('aria-label'),el.getAttribute?.('title'),text(el)].filter(Boolean).join(' ').toLowerCase();return /(^|\W)(map|maps)(\W|$)/.test(s);}
function findPanel(){
 const nodes=[...document.querySelectorAll('[data-module-panel],[data-settings-panel],.module-panel,.settings-panel,.module-content,.settings-content,section,article,div')];
 const scored=nodes.map(n=>{const t=text(n);let s=0;if(/PLATFORM MODULE/i.test(t))s+=6;if(n.querySelector('button')&&/Reset Module|Save/i.test(t))s+=3;if(n.getBoundingClientRect().width>500)s+=1;if(n.children.length<18)s+=1;return [s,n];}).filter(x=>x[0]>=6).sort((a,b)=>b[0]-a[0]);
 return scored[0]?.[1]||document.querySelector('main')||null;
}
function nativeHasFields(panel){return !!panel?.querySelector('input:not([type="hidden"]),select,textarea,[contenteditable="true"]');}
function normalize(v){return v===true||v===1||v==='1'||String(v).toLowerCase()==='true';}
function formHtml(s){const pub=s.public_map_url?`<a class="sk1311-map-btn secondary" href="${s.public_map_url}" target="_blank" rel="noopener">Open Public Map</a>`:'';return `
<div id="sk1311-map-recovery" data-sk1311-map-recovery>
 <div class="sk1311-map-title"><div class="pin">⌖</div><div><h2>Map Platform</h2><p>Recovered settings panel · v13.1.1</p></div></div>
 <form id="sk1311-map-form">
  <div class="sk1311-map-grid">
   <div class="sk1311-map-field"><label>Map Provider</label><select name="provider"><option value="auto">Auto / Existing Provider</option><option value="google">Google Maps</option><option value="leaflet">Leaflet / OpenStreetMap</option></select></div>
   <div class="sk1311-map-field"><label>Map Style</label><select name="style"><option value="light">Light</option><option value="standard">Standard</option><option value="dark">Dark</option><option value="satellite">Satellite</option></select></div>
   <div class="sk1311-map-field"><label>Default Latitude</label><input name="latitude" type="number" step="0.000001" min="-90" max="90" required></div>
   <div class="sk1311-map-field"><label>Default Longitude</label><input name="longitude" type="number" step="0.000001" min="-180" max="180" required></div>
   <div class="sk1311-map-field"><label>Default Zoom Level</label><input name="zoom" type="number" min="1" max="20" required></div>
   <div class="sk1311-map-field"><label>Default Search Radius (KM)</label><input name="search_radius_km" type="number" step="0.5" min="1" max="100" required></div>
   <div class="sk1311-map-field"><label>Maximum Search Radius (KM)</label><input name="max_search_radius_km" type="number" step="0.5" min="1" max="250" required></div>
   <div class="sk1311-map-field full"><div class="sk1311-map-checks">
    <label><input type="checkbox" name="marker_clustering"> Enable Marker Clustering</label><label><input type="checkbox" name="heatmap"> Enable Heatmap</label>
    <label><input type="checkbox" name="category_icons"> Show Category Icons</label><label><input type="checkbox" name="current_location"> Current Location Button</label>
    <label><input type="checkbox" name="directions"> Enable Directions</label>
   </div></div>
  </div>
  <div class="sk1311-map-actions"><button class="sk1311-map-btn" type="submit">Save Map Settings</button>${pub}<span class="sk1311-map-status" aria-live="polite">Recovered from blank Map Platform renderer.</span></div>
  <div class="sk1311-map-note">This recovery UI activates only when the native Map Platform detail panel is blank. It does not replace working module forms or change settings until you press Save.</div>
 </form>
</div>`;}
function fill(root,s){const f=root.querySelector('#sk1311-map-form');if(!f)return;for(const k of ['provider','style','latitude','longitude','zoom','search_radius_km','max_search_radius_km'])if(f.elements[k])f.elements[k].value=s[k]??'';for(const k of ['marker_clustering','heatmap','category_icons','current_location','directions'])if(f.elements[k])f.elements[k].checked=normalize(s[k]);}
async function loadInto(panel){
 if(!panel||panel.dataset.sk1311MapDone==='1'||nativeHasFields(panel))return;
 panel.dataset.sk1311MapDone='1';
 let data;try{const r=await fetch(API,{credentials:'same-origin',headers:{Accept:'application/json'}});data=await r.json();if(!r.ok||!data.ok)throw new Error(data.error||'Map settings API failed');}catch(e){panel.dataset.sk1311MapDone='';return;}
 // Preserve the native module header/footer if present, but replace the empty middle with the recovery panel.
 const footer=[...panel.querySelectorAll('button')].find(b=>/Reset Module/i.test(text(b)))?.closest('div');
 const header=[...panel.querySelectorAll('*')].find(n=>/^PLATFORM MODULE$/i.test(text(n)))?.parentElement;
 [...panel.children].forEach(ch=>{if(ch!==header&&ch!==footer)ch.remove();});
 const wrap=document.createElement('div');wrap.innerHTML=formHtml(data.settings||{});const recovered=wrap.firstElementChild;if(footer)panel.insertBefore(recovered,footer);else panel.appendChild(recovered);fill(recovered,data.settings||{});
 const form=recovered.querySelector('#sk1311-map-form'),status=recovered.querySelector('.sk1311-map-status');
 form?.addEventListener('submit',async ev=>{ev.preventDefault();status.className='sk1311-map-status';status.textContent='Saving…';const fd=new FormData(form);const payload={_csrf:csrf()};for(const [k,v] of fd.entries())payload[k]=v;for(const k of ['marker_clustering','heatmap','category_icons','current_location','directions'])payload[k]=form.elements[k]?.checked?1:0;try{const r=await fetch(API,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf(),'X-CSRF':csrf()},body:JSON.stringify(payload)});const j=await r.json();if(!r.ok||!j.ok)throw new Error(j.error||'Save failed');fill(recovered,j.settings||payload);status.className='sk1311-map-status good';status.textContent='Map settings saved successfully.';}catch(e){status.className='sk1311-map-status bad';status.textContent=e?.message||'Could not save map settings.';}});
}
function recoverIfNeeded(trigger){if(!pageLooksRight()||!mapTrigger(trigger))return;setTimeout(()=>{const p=findPanel();if(p&&!nativeHasFields(p))loadInto(p);},180);setTimeout(()=>{const p=findPanel();if(p&&!nativeHasFields(p))loadInto(p);},650);}
document.addEventListener('click',e=>{const el=e.target?.closest?.('button,a,[role="button"],[data-module],[data-key]');if(mapTrigger(el))recoverIfNeeded(el);},true);
// Also recover if Map is already active on initial load.
function init(){if(!pageLooksRight())return;const active=[...document.querySelectorAll('[aria-current="page"],.active,.is-active,[data-active="1"]')].find(mapTrigger);if(active)recoverIfNeeded(active);}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
