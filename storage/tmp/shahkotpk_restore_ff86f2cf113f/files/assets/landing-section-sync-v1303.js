(()=>{'use strict';
const active=window.ShahkotPKActiveLandingV1101||{};
const cfg=active.landingSync||window.ShahkotPKLandingSyncV1303;
if(!active.home||!cfg||!cfg.sections)return;
document.documentElement.classList.add('sk1303-landing-sync');
const norm=s=>String(s||'').replace(/\s+/g,' ').trim().toLowerCase();
const sectionKeys=['hero','search','categories','businesses','city_updates','deals','services','map'];
const rules=[
 ['categories',/browse by category|popular categories|categories near you|all categories/],
 ['businesses',/featured businesses|local businesses|business directory|popular businesses/],
 ['city_updates',/city updates|latest from shahkot|latest updates|city news/],
 ['deals',/deals|offers|coupons|discounts/],
 ['services',/customer services|local services|need a service|services near you/],
 ['map',/explore shahkot on map|nearby results|location discovery|explore.*map/]
];
const main=()=>document.querySelector('main');
function mark(e,key){if(e&&key&&sectionKeys.includes(key)&&!e.dataset.sk1303Section)e.dataset.sk1303Section=key;}
function directSection(node,m){if(!node||!m)return null;let p=node;while(p&&p.parentElement&&p.parentElement!==m){if(p.matches('section,[data-section],[class*="section"],[class*="block"],[class*="widget"],[class*="module"]'))return p;p=p.parentElement;}if(p&&p.parentElement===m&&p.matches('section,[data-section],[class*="section"],[class*="block"],[class*="widget"],[class*="module"]'))return p;return null;}
function tagKnown(){
 const m=main();if(!m)return;
 for(const e of m.querySelectorAll('[data-sk1303-section]')){const k=e.dataset.sk1303Section||'';if(!sectionKeys.includes(k))delete e.dataset.sk1303Section;}
 let hero=m.querySelector(':scope > .hero,:scope > [class*="hero"],:scope > section[class*="hero"]');
 if(!hero){const first=m.querySelector(':scope > section');if(first&&/hero|welcome|discover shahkot|search shahkot/i.test(first.textContent||''))hero=first;}
 mark(hero,'hero');
 for(const n of m.querySelectorAll('form[action*="search" i],[data-smart-search],[data-sk1110-search],input[type="search"],input[name="q"],input[name="query"]')){
  let h=n.closest('[data-smart-search],[data-sk1110-search],form,[class*="search"]')||directSection(n,m);
  if(h&&h!==hero)mark(h,'search');
 }
 for(const h of m.querySelectorAll('h1,h2,h3,h4,[data-section-title]')){
  const t=norm(h.textContent).slice(0,240);if(!t)continue;
  for(const [k,re] of rules){if(!re.test(t))continue;const s=directSection(h,m);if(s&&s!==hero)mark(s,k);break;}
 }
 // Explicit class/data fallbacks only; generic wrapper text is intentionally not used.
 const classHints={categories:'[data-categories-section],[class*="categories-section"],[class*="category-section"]',businesses:'[data-businesses-section],[class*="businesses-section"],[class*="business-section"]',city_updates:'[data-city-updates],[class*="city-updates"]',deals:'[data-deals-section],[class*="deals-section"],[class*="offers-section"]',services:'[data-services-section],[class*="services-section"]',map:'[data-map-section],[class*="map-section"]'};
 for(const [k,sel] of Object.entries(classHints)){for(const e of m.querySelectorAll(sel)){const s=directSection(e,m)||e;if(s&&s!==hero)mark(s,k);}}
}
function applyVisibility(){
 for(const [k,v] of Object.entries(cfg.sections||{})){
  for(const e of document.querySelectorAll('[data-sk1303-section="'+k+'"]')){
   if(Number(v?.enabled)===0){e.dataset.sk1303Hidden='1';e.setAttribute('aria-hidden','true');}
   else{delete e.dataset.sk1303Hidden;e.removeAttribute('aria-hidden');}
  }
 }
}
function applyOrder(){
 const m=main();if(!m)return;
 const nodes=sectionKeys.flatMap(k=>[...m.querySelectorAll(':scope > [data-sk1303-section="'+k+'"]')]).filter((e,i,a)=>a.indexOf(e)===i);
 if(nodes.length<2)return;
 const sorted=[...nodes].sort((a,b)=>(Number(cfg.sections?.[a.dataset.sk1303Section]?.order)||500)-(Number(cfg.sections?.[b.dataset.sk1303Section]?.order)||500));
 const same=nodes.length===sorted.length&&nodes.every((n,i)=>n===sorted[i]);if(same)return;
 const first=nodes[0];const anchor=document.createComment('sk1303-order');m.insertBefore(anchor,first);let cursor=anchor;for(const n of sorted){m.insertBefore(n,cursor.nextSibling);cursor=n;}anchor.remove();
}
function syncCounts(){
 const cats=cfg.business?.categories||{};
 for(const card of document.querySelectorAll('[data-sk1303-section="categories"] a,[data-sk1303-section="categories"] article,[data-sk1303-section="categories"] [class*="card"]')){
  const t=norm(card.textContent);for(const [label,n] of Object.entries(cats)){if(!label||!t.includes(norm(label)))continue;const count=[...card.querySelectorAll('small,span,p,b')].find(x=>/\d+\s+(?:local\s+)?business(?:es)?/i.test(x.textContent||''));if(count)count.textContent=n+' local '+(Number(n)===1?'business':'businesses');break;}
 }
 for(const e of document.querySelectorAll('[data-business-count],[data-live-business-count]'))e.textContent=String(cfg.business?.active??cfg.business?.total??0);
}
let running=false,timer=0,mutations=0;
function run(){if(running)return;running=true;try{tagKnown();applyVisibility();applyOrder();syncCounts();}finally{running=false;}}
function schedule(){clearTimeout(timer);timer=setTimeout(run,100);}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',run,{once:true});else run();
window.addEventListener('load',run,{once:true});setTimeout(run,500);setTimeout(run,1800);
const root=main()||document.body;if(root&&'MutationObserver'in window){const mo=new MutationObserver(()=>{mutations++;schedule();if(mutations>=30)mo.disconnect();});mo.observe(root,{childList:true,subtree:true});setTimeout(()=>mo.disconnect(),8000);}
})();
