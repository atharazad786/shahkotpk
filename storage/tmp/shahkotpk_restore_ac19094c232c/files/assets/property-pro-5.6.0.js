(function(){
 const key='shahkotpk_property_compare_v560';
 function ids(){try{return JSON.parse(localStorage.getItem(key)||'[]').map(Number).filter(Boolean).slice(0,4)}catch(e){return []}}
 function save(a){try{localStorage.setItem(key,JSON.stringify(a.slice(0,4)))}catch(e){} render()}
 function render(){const tray=document.querySelector('[data-pr-compare-tray]');if(!tray)return;const a=ids();tray.classList.toggle('show',a.length>0);const n=tray.querySelector('[data-pr-compare-count]');if(n)n.textContent=String(a.length);const go=tray.querySelector('[data-pr-compare-go]');if(go)go.onclick=function(){if(a.length>1)location.href='/property.php?compare='+a.join(',')};}
 document.addEventListener('click',async function(e){
   const cmp=e.target.closest('[data-pr-compare]');if(cmp){e.preventDefault();const id=Number(cmp.dataset.prCompare);let a=ids();if(a.includes(id))a=a.filter(x=>x!==id);else if(a.length<4)a.push(id);save(a);cmp.textContent=a.includes(id)?'✓ Compared':'⇄ Compare';return;}
   const fav=e.target.closest('[data-pr-favorite]');if(fav){e.preventDefault();const id=Number(fav.dataset.prFavorite);try{const body=new FormData();body.append('action','favorite');body.append('property_id',String(id));body.append('_csrf',document.querySelector('meta[name="csrf-token"]')?.content||'');const r=await fetch('/api/property-v560.php',{method:'POST',body,credentials:'same-origin'});const j=await r.json();if(j.login){location.href='/login.php?return='+encodeURIComponent(location.pathname+location.search);return}if(j.ok){fav.classList.toggle('is-active',!!j.active);fav.textContent=j.active?'♥':'♡'}}catch(err){}return;}
   const track=e.target.closest('[data-pr-track]');if(track){const id=Number(track.dataset.propertyId||0),type=track.dataset.prTrack;if(id&&type){try{navigator.sendBeacon('/api/property-v560.php',new URLSearchParams({action:'track',property_id:String(id),event_type:type}))}catch(err){}}}
 });
 document.querySelectorAll('[data-pr-gallery-thumb]').forEach(function(t){t.addEventListener('click',function(){const main=document.querySelector('[data-pr-gallery-main]');if(main)main.style.backgroundImage='url("'+t.dataset.prGalleryThumb.replace(/"/g,'')+'")'})});
 document.querySelectorAll('[data-pr-tab]').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('[data-pr-tab]').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.querySelectorAll('[data-pr-tab-panel]').forEach(x=>x.hidden=x.dataset.prTabPanel!==b.dataset.prTab)})});
 render();
})();
