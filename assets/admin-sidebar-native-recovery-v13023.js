/* ShahkotPK v13.0.2.3 — native sidebar recovery mode. */
(()=>{'use strict';
if(!/^\/admin(?:\/|$)/i.test(location.pathname))return;
const ROOT='sk-sidebar-registry-v1022';
const HIDE_CLASSES=['sk1302-legacy-hidden','sk1022-native-hidden','sk13022-native-hidden','sk13022-native-anchor-hidden'];
function restoreNative(){
  const injected=document.getElementById(ROOT);if(injected)injected.remove();
  for(const cls of HIDE_CLASSES){for(const el of document.querySelectorAll('.'+cls)){el.classList.remove(cls);el.removeAttribute('aria-hidden');if(el.hasAttribute('tabindex')&&el.getAttribute('tabindex')==='-1')el.removeAttribute('tabindex')}}
  for(const el of document.querySelectorAll('[data-sk974-admin-tools]')){el.style.removeProperty('display');el.style.removeProperty('visibility');el.style.removeProperty('pointer-events')}
  for(const side of document.querySelectorAll('[data-admin-sidebar],.admin-sidebar,#sidebar,aside.sidebar,.sidebar')){
    side.classList.remove('sk1302-sidebar-safe','sk13022-sidebar-single');
    side.removeAttribute('data-sk1302-sidebar-safe');
  }
  document.documentElement.classList.remove('sk1022-sidebar-ready','sk13022-sidebar-ready');
  document.documentElement.classList.add('sk13023-native-sidebar');
}
function normalizeClicks(){
  for(const a of document.querySelectorAll('[data-admin-sidebar] a[href],.admin-sidebar a[href],#sidebar a[href],aside.sidebar a[href],.sidebar a[href]')){
    if(!a.href)continue;
    a.style.removeProperty('pointer-events');
    for(const child of a.querySelectorAll('*'))child.style.removeProperty('pointer-events');
  }
}
function boot(){restoreNative();normalizeClicks();
  // Short observer only: removes stale roots inserted by cached legacy code, then stops.
  const obs=new MutationObserver(()=>{if(document.getElementById(ROOT)){restoreNative();normalizeClicks()}});obs.observe(document.body,{childList:true,subtree:true});setTimeout(()=>obs.disconnect(),8000);
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
})();
