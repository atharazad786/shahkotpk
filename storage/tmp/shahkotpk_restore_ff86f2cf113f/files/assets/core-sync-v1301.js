(()=>{'use strict';
const C=window.ShahkotPKCoreSyncV1301||window.ShahkotPKActiveLandingV1101||{};const id=C.identity||{},th=C.theme||{},root=document.documentElement;
const vars={primary:'--sk-brand-primary',accent:'--sk-brand-accent',background:'--sk-page-background',text:'--sk-text-color'};for(const[k,v]of Object.entries(vars))if(th[k])root.style.setProperty(v,th[k]);if(th.primary){root.style.setProperty('--primary',th.primary);root.style.setProperty('--brand-primary',th.primary);}if(th.accent){root.style.setProperty('--accent',th.accent);root.style.setProperty('--brand-accent',th.accent);}
function syncIdentity(){
 if(id.favicon){let l=document.querySelector('link[rel~="icon"]');if(!l){l=document.createElement('link');l.rel='icon';document.head.appendChild(l);}if(l.getAttribute('href')!==id.favicon)l.href=id.favicon;}
 if(id.logo){const sels=['[data-site-logo]','header .site-logo img','header .brand-logo img','header .navbar-brand img','header img[class*="logo"]','aside .site-logo img','aside .brand-logo img','.admin-sidebar .logo img','.sidebar .logo img','.sidebar img[class*="logo"]'];for(const img of document.querySelectorAll(sels.join(','))){if(img.tagName!=='IMG'||img.closest('[data-business-logo]'))continue;if(img.getAttribute('src')!==id.logo)img.src=id.logo;img.alt=id.name||'ShahkotPK';img.dataset.siteLogo='1';}}
 for(const el of document.querySelectorAll('[data-site-name]'))el.textContent=id.name||'ShahkotPK';
 root.dataset.skCoreSync='13.0.3.4';
}
let timer=0;const schedule=()=>{clearTimeout(timer);timer=setTimeout(syncIdentity,100);};
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',syncIdentity,{once:true});else syncIdentity();window.addEventListener('load',syncIdentity,{once:true});
if('MutationObserver'in window){const mo=new MutationObserver(schedule);mo.observe(document.documentElement,{childList:true,subtree:true});setTimeout(()=>mo.disconnect(),5000);}
})();
