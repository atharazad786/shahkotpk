/* ShahkotPK v12.7.6 — Business Importer */
(()=>{'use strict';const root=document.querySelector('.bs1270');if(!root)return;const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));async function load(btn){const id=btn.dataset.loadLive;if(!id)return;const box=btn.closest('.candidate-live');if(!box)return;btn.disabled=true;btn.textContent='Loading live…';try{const r=await fetch('/api/business-source-extractor-v1270.php?action=detail&id='+encodeURIComponent(id),{credentials:'same-origin',headers:{Accept:'application/json'}});const j=await r.json();if(!j.ok)throw new Error(j.error||'Unable to load');box.innerHTML=`${j.photo_url?`<img src="${esc(j.photo_url)}" alt="Google Maps live business photo">`:''}<b>${esc(j.name||'Google place')}</b><small>${esc(j.address||'')}</small><small>${esc(j.phone||'')}</small><small>Confidence ${Number(j.confidence||0)}% · ${Number(j.distance_m||0).toLocaleString()} m</small><small>Official site: ${j.website_verified?'verified':'review required'} · ${j.shahkot_ok?'Shahkot match':'blocked'}</small>${j.photo_author?`<small>Photo: ${esc(j.photo_author)} · Google Maps</small>`:''}${j.google_maps_uri?`<a href="${esc(j.google_maps_uri)}" target="_blank" rel="noopener">Google Maps source ↗</a>`:''}`;}catch(e){btn.disabled=false;btn.textContent='Retry live details';let er=box.querySelector('.bs1270-live-error');if(!er){er=document.createElement('small');er.className='bs1270-live-error';er.style.color='#b4232d';box.appendChild(er)}er.textContent=e.message||'Live lookup failed';}}document.addEventListener('click',e=>{const b=e.target.closest('[data-load-live]');if(b)load(b);});})();


/* v12.7.3 managed-import lifecycle UI */
document.addEventListener('DOMContentLoaded',()=>{
 const root=document.querySelector('.bs1270'); if(!root)return;
 const all=root.querySelector('[data-select-all]'); if(all)all.addEventListener('change',()=>root.querySelectorAll('[data-row-check]').forEach(x=>x.checked=all.checked));
 const bulk=root.querySelector('[data-bulk-form]'); if(bulk)bulk.addEventListener('submit',(e)=>{
   const btn=e.submitter;
   if(btn&&btn.dataset.singleDelete){
     e.preventDefault();
     if(!confirm('Delete this extractor-created business listing? This is blocked automatically if products, leads, CRM or orders exist.'))return;
     const f=document.createElement('form');f.method='post';
     [[' _csrf'.trim(),root.dataset.csrf],['action','managed_delete'],['business_id',btn.dataset.singleDelete]].forEach(([n,v])=>{const i=document.createElement('input');i.type='hidden';i.name=n;i.value=v;f.appendChild(i)});document.body.appendChild(f);f.submit();return;
   }
   const action=bulk.querySelector('[name=bulk_action]')?.value||'';
   const checked=bulk.querySelectorAll('[data-row-check]:checked').length;
   if(!checked){e.preventDefault();alert('Select at least one business.');return;}
   if(action==='delete'&&!confirm('Delete selected extractor-created listings? Existing matched Directory records cannot be deleted, and records with operational data will be blocked.'))e.preventDefault();
 });
});


/* v12.7.6 candidate bulk verify/add/link — max 20 */
document.addEventListener('DOMContentLoaded',()=>{
 const root=document.querySelector('.bs1270');if(!root)return;
 const form=root.querySelector('[data-candidate-bulk-form]');if(!form)return;
 const all=form.querySelector('[data-candidate-select-all]');
 if(all)all.addEventListener('change',()=>root.querySelectorAll('[data-candidate-check]').forEach(x=>x.checked=all.checked));
 form.addEventListener('submit',e=>{
   const selected=root.querySelectorAll('[data-candidate-check]:checked');
   const action=form.querySelector('[name=candidate_bulk_action]')?.value||'';
   if(!selected.length){e.preventDefault();alert('Select at least one candidate.');return;}
   if((action==='verify'||action==='import')&&selected.length>20){e.preventDefault();alert('Select up to 20 candidates per Verify/Add run.');return;}
   if(action==='import'&&!confirm('Add/link the selected verified Shahkot businesses to the existing Business Directory? New records may also create shopkeeper accounts according to your importer settings.'))e.preventDefault();
   if(action==='reject'&&!confirm('Reject the selected candidates? No business records will be deleted.'))e.preventDefault();
 });
});
