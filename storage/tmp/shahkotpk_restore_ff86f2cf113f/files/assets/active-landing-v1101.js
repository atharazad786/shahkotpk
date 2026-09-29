(()=>{'use strict';
const C=window.ShahkotPKActiveLandingV1101||{};
const CANON={home:'/',businesses:'/businesses.php',marketplace:'/marketplace.php',property:'/property.php',health:'/doctor-online.php',events:'/discover-v1020.php?type=events'};
const ALIASES={home:['home'],businesses:['business','businesses'],marketplace:['marketplace','shop','shopping'],property:['property','properties'],health:['health','doctors','doctor'],events:['events','event']};
const norm=u=>{try{const x=new URL(u,location.origin);const q=new URLSearchParams(x.search);[...q.keys()].sort().forEach(()=>{});return (x.pathname.replace(/\/$/,'')||'/')+(x.search||'')}catch(e){return String(u||'').trim()}};
const pathOnly=u=>{try{return new URL(u,location.origin).pathname.replace(/\/$/,'')||'/'}catch(e){return String(u||'').split('?')[0].replace(/\/$/,'')||'/'}};
const cleanText=s=>String(s||'').replace(/[⌄∨▼▾˅›»↓↑⌃∧▲▴]+/g,' ').replace(/\s+/g,' ').trim().toLowerCase();
const label=a=>cleanText(a?.textContent||'');
const visible=e=>!!(e&&e.getClientRects().length&&getComputedStyle(e).visibility!=='hidden');
function logo(){const i=C.identity||{};if(!i.logo)return;const qs=['header a[href="/"] img','header .logo img','header .navbar-brand img','.site-header .logo img','nav .navbar-brand img','a.site-logo img'];for(const q of qs)document.querySelectorAll(q).forEach(img=>{img.src=i.logo;img.alt=i.name||'ShahkotPK';img.removeAttribute('srcset')});if(i.favicon){let l=document.querySelector('link[rel~="icon"]');if(!l){l=document.createElement('link');l.rel='icon';document.head.appendChild(l)}l.href=i.favicon}}
function menuScore(n){const labs=[...n.querySelectorAll('a[href]')].map(label);let s=0;for(const names of Object.values(ALIASES))if(labs.some(x=>names.includes(x)))s+=3;if(labs.includes('dashboard'))s+=1;return s}
function findMenu(){const selectors=['header nav','header .navbar-nav','header .main-menu','header .primary-menu','.site-header nav','.navbar .navbar-nav','.main-menu','.primary-menu'];const seen=new Set(),c=[];for(const sel of selectors)for(const n of document.querySelectorAll(sel)){if(seen.has(n)||!visible(n))continue;seen.add(n);const count=n.querySelectorAll('a[href]').length;if(count>=3)c.push([menuScore(n),count,n])}c.sort((a,b)=>b[0]-a[0]||b[1]-a[1]);return c.length&&c[0][0]>=6?c[0][2]:null}
function oldCleanup(n){
 n.querySelectorAll('.sk1101-submenu,.sk1102-submenu,.sk1101-caret,.sk1102-menu-toggle').forEach(x=>x.remove());
 n.querySelectorAll('.sk1101-has-submenu,.sk1102-has-submenu').forEach(x=>x.classList.remove('sk1101-has-submenu','sk1102-has-submenu','sk1102-open'));
 n.querySelectorAll('[data-sk1101-hidden="1"],[data-sk1102-hidden="1"]').forEach(x=>{x.style.removeProperty('display');x.removeAttribute('data-sk1101-hidden');x.removeAttribute('data-sk1102-hidden')});
 // unwrap wrappers from a prior failed v11.0.2 init if the browser re-runs the script.
 n.querySelectorAll('.sk1102-host').forEach(w=>{const a=w.querySelector(':scope > a');if(a&&w.parentNode){w.parentNode.insertBefore(a,w);w.remove()}});
}
function exactAnchor(n,key){const names=ALIASES[key]||[];const all=[...n.querySelectorAll('a[href]')].filter(a=>!a.closest('.sk1102-submenu'));return all.find(a=>names.includes(label(a)))||null}
function hostFor(a,n){let h=a.closest('li,.nav-item,.menu-item');if(h&&h!==n&&h.querySelectorAll(':scope > a[href], :scope > span > a[href]').length<=1)return h;const w=document.createElement('span');w.className='sk1102-host';a.parentNode.insertBefore(w,a);w.appendChild(a);return w}
function repairDashboard(n){for(const a of [...n.querySelectorAll('a[href]')]){if(label(a)!=='dashboard')continue;const h=a.closest('li,.nav-item,.menu-item')||a.parentElement;if(!h)continue;h.classList.remove('sk1101-has-submenu','sk1102-has-submenu','sk1102-open');h.querySelectorAll('.sk1101-submenu,.sk1102-submenu,.sk1101-caret,.sk1102-menu-toggle').forEach(x=>x.remove());
 // If old injections left two actual caret elements, keep the first native one only.
 const icons=[...h.querySelectorAll('span,i,svg')].filter(x=>/(caret|chevron|dropdown|arrow|angle)/i.test(String(x.className?.baseVal||x.className||'')));
 icons.slice(1).forEach(x=>{if(!x.closest('a')||x.parentElement===h)x.style.display='none'});
 // Clean duplicated arrow glyphs in text nodes without changing the Dashboard label.
 const tw=document.createTreeWalker(a,NodeFilter.SHOW_TEXT);let t;while(t=tw.nextNode())t.nodeValue=t.nodeValue.replace(/([⌄∨▼▾˅›»↓])(?:\s*\1)+/g,'$1');
 }}
function nav(){const items=(C.items||[]).filter(x=>x&&x.url&&x.key);const n=findMenu();if(!n)return;oldCleanup(n);n.classList.add('sk1102-nav-repaired');
 // 1) Repair the six canonical main links. Home can never point to My Bookings again.
 const anchors={};for(const key of Object.keys(CANON)){const a=exactAnchor(n,key);if(!a)continue;a.href=CANON[key];a.dataset.sk1102Main=key;anchors[key]=a}
 // 2) Remove stale standalone copies of pages that belong inside a main-menu dropdown.
 const children=items.filter(x=>x.parent&&CANON[x.parent]);const childPaths=new Set(children.map(x=>pathOnly(x.url)));
 for(const a of [...n.querySelectorAll('a[href]')]){if(a.dataset.sk1102Main||a.closest('.sk1102-submenu'))continue;const p=pathOnly(a.getAttribute('href'));if(!childPaths.has(p))continue;const h=a.closest('li,.nav-item,.menu-item')||a.parentElement;if(h&&h!==n){h.dataset.sk1102Hidden='1';h.style.display='none'}}
 // 3) Build dropdowns only for known canonical parents. Never attach our caret/submenu to Dashboard.
 for(const [key,a] of Object.entries(anchors)){const kids=children.filter(x=>x.parent===key).sort((x,y)=>(x.order||100)-(y.order||100));if(!kids.length)continue;const h=hostFor(a,n);h.classList.add('sk1102-has-submenu');const b=document.createElement('button');b.type='button';b.className='sk1102-menu-toggle';b.setAttribute('aria-label',`Open ${key} menu`);b.setAttribute('aria-expanded','false');b.textContent='⌄';const sub=document.createElement('div');sub.className='sk1102-submenu';sub.setAttribute('role','menu');for(const ch of kids){const l=document.createElement('a');l.href=ch.url;l.textContent=ch.label;l.setAttribute('role','menuitem');sub.appendChild(l)}a.insertAdjacentElement('afterend',b);h.appendChild(sub);b.addEventListener('click',e=>{e.preventDefault();e.stopPropagation();const on=!h.classList.contains('sk1102-open');n.querySelectorAll('.sk1102-open').forEach(x=>x.classList.remove('sk1102-open'));n.querySelectorAll('.sk1102-menu-toggle[aria-expanded="true"]').forEach(x=>x.setAttribute('aria-expanded','false'));h.classList.toggle('sk1102-open',on);b.setAttribute('aria-expanded',String(on))})}
 repairDashboard(n);
 document.addEventListener('click',e=>{if(!n.contains(e.target)){n.querySelectorAll('.sk1102-open').forEach(x=>x.classList.remove('sk1102-open'));n.querySelectorAll('.sk1102-menu-toggle').forEach(x=>x.setAttribute('aria-expanded','false'))}},{passive:true});
}
function responsive(){document.querySelectorAll('img').forEach((img,i)=>{if(!img.hasAttribute('decoding'))img.decoding='async';if(i>3&&!img.hasAttribute('loading'))img.loading='lazy'});document.querySelectorAll('iframe').forEach(x=>{if(!x.hasAttribute('loading'))x.loading='lazy'})}
function init(){logo();nav();responsive();document.documentElement.dataset.sk1102='ready'}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
