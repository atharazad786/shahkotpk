/* ShahkotPK v13.8.5 — native front-menu recovery guard.
 * Bounded compatibility cleanup only. It does not build/reorder native navigation.
 */
(()=>{'use strict';
const legacySelector='#sk1302-public-nav,[data-sk13032-managed="1"]';
const hiddenSelector='.sk1302-native-nav-hidden,[data-sk13032-legacy],.sk13032-legacy-link-hidden,.sk13032-empty-legacy-item';
function restoreNode(el){
  if(!el)return;
  el.classList.remove('sk1302-native-nav-hidden','sk13032-legacy-link-hidden','sk13032-empty-legacy-item');
  if(el.dataset){delete el.dataset.sk13032Legacy;delete el.dataset.sk13032Managed;}
  if(el.getAttribute('aria-hidden')==='true')el.removeAttribute('aria-hidden');
}
function recover(){
  for(const el of document.querySelectorAll(legacySelector))el.remove();
  for(const el of document.querySelectorAll(hiddenSelector))restoreNode(el);
  document.documentElement.classList.remove('sk13032-managed-header');
  document.documentElement.classList.add('sk1385-native-menu-lock','sk13034-native-menu');
}
recover();
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',recover,{once:true});
window.addEventListener('load',recover,{once:true});
// Bounded delayed passes beat an older cached defer script without a permanent observer.
setTimeout(recover,150);
setTimeout(recover,650);
setTimeout(recover,1600);
})();
