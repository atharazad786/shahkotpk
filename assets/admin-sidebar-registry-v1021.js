/* ShahkotPK v10.2.1 — one sidebar renderer for all extension modules. */
(()=>{'use strict';
if(!/^\/admin(?:\/|$)/i.test(location.pathname))return;
const ITEMS=Array.isArray(window.ShahkotPKSidebarRegistryV1021)?window.ShahkotPKSidebarRegistryV1021:[];
const ROOT_ID='sk-sidebar-registry-v1021';
const LEGACY_URLS=new Set(ITEMS.map(x=>String(x.url||'')));['/admin/admin-tools.php','/admin/homepage-builder.php','/admin/growth-engagement.php'].forEach(x=>LEGACY_URLS.add(x));
const norm=s=>(s||'').replace(/\s+/g,' ').trim().toLowerCase();
function visible(el){if(!el)return false;const s=getComputedStyle(el),r=el.getBoundingClientRect();return s.display!=='none'&&s.visibility!=='hidden'&&r.width>0&&r.height>0}
function sidebarCandidates(){return [...document.querySelectorAll('.admin-sidebar,.sidebar,#sidebar,aside,[data-admin-sidebar],[data-sidebar],nav')].filter(visible)}
function adminLinkCount(el){return el.querySelectorAll('a[href^="/admin/"],a[href*="/admin/"]').length}
function findSidebar(){const c=sidebarCandidates().map(el=>[el,adminLinkCount(el)]).filter(x=>x[1]>=3).sort((a,b)=>b[1]-a[1]);return c[0]?.[0]||null}
function cleanLegacy(side){
  side.querySelectorAll('[data-sk974-admin-tools],[data-sk971-tools],[data-sk972-tools]').forEach(n=>n.remove());
  [...side.querySelectorAll('a[href]')].forEach(a=>{
    const u=(a.getAttribute('href')||'').split('?')[0];if(!LEGACY_URLS.has(u)||a.closest('#'+ROOT_ID))return;
    let n=a;for(let i=0;i<3&&n.parentElement&&n.parentElement!==side;i++){
      const p=n.parentElement,links=p.querySelectorAll('a[href]').length,txt=norm(p.textContent);
      if(links===1 && txt.length<120){n=p;continue}break;
    }
    n.remove();
  });
}
function deepestMenuContainer(side){
  const links=[...side.querySelectorAll('a[href^="/admin/"],a[href*="/admin/"]')].filter(a=>!a.closest('#'+ROOT_ID)&&visible(a));
  if(!links.length)return side;
  const score=new Map();
  for(const a of links){let p=a.parentElement,depth=0;while(p&&p!==side&&depth++<10){score.set(p,(score.get(p)||0)+1);p=p.parentElement}}
  const min=Math.max(3,Math.ceil(links.length*.55));
  let best=null,bestDepth=-1;
  for(const [el,count] of score){if(count<min)continue;let d=0,p=el;while(p&&p!==side){d++;p=p.parentElement}if(d>bestDepth){best=el;bestDepth=d}}
  return best||side;
}
function groupItems(){const m=new Map();for(const x of ITEMS){if(!x||!x.url||!x.label)continue;const key=String(x.category_key||'extensions');if(!m.has(key))m.set(key,{label:String(x.category_label||'EXTENSIONS'),order:Number(x.category_order||80),items:[]});m.get(key).items.push(x)}return [...m.values()].sort((a,b)=>a.order-b.order||a.label.localeCompare(b.label))}
function render(side){
  cleanLegacy(side);document.getElementById(ROOT_ID)?.remove();
  const root=document.createElement('section');root.id=ROOT_ID;root.setAttribute('aria-label','ShahkotPK extension navigation');
  for(const cat of groupItems()){
    const section=document.createElement('div');section.className='sk1021-cat';
    const title=document.createElement('div');title.className='sk1021-cat-title';title.textContent=cat.label;section.appendChild(title);
    cat.items.sort((a,b)=>Number(a.sort_order||100)-Number(b.sort_order||100)||String(a.label).localeCompare(String(b.label)));
    for(const item of cat.items){
      const a=document.createElement('a');a.className='sk1021-link';a.href=item.url;a.title=item.label;a.dataset.sk1021Module=item.module_key||'';
      const path=(new URL(a.href,location.origin)).pathname.replace(/\/+$/,'')||'/';const here=location.pathname.replace(/\/+$/,'')||'/';if(path===here){a.classList.add('active');a.setAttribute('aria-current','page')}
      const icon=document.createElement('span');icon.className='sk1021-icon';icon.textContent=item.icon||'•';
      const label=document.createElement('span');label.className='sk1021-label';label.textContent=item.label;
      const arrow=document.createElement('span');arrow.className='sk1021-arrow';arrow.textContent='›';
      a.append(icon,label,arrow);section.appendChild(a);
    }
    root.appendChild(section);
  }
  const target=deepestMenuContainer(side);target.appendChild(root);document.documentElement.classList.add('sk1021-sidebar-ready');
}
function boot(){let tries=0;const run=()=>{const side=findSidebar();if(side){render(side);return true}return false};if(run())return;const t=setInterval(()=>{if(run()||++tries>=40)clearInterval(t)},125)}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
})();
