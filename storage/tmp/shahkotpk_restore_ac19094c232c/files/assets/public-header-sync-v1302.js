/* ShahkotPK v13.0.3.4 — native public header/menu cleanup only. */
(()=>{'use strict';
function restoreNode(el){if(!el)return;el.classList.remove('sk1302-native-nav-hidden','sk13032-legacy-link-hidden','sk13032-empty-legacy-item');if(el.dataset){delete el.dataset.sk13032Legacy;delete el.dataset.sk13032Managed;}if(el.getAttribute('aria-hidden')==='true')el.removeAttribute('aria-hidden');}
function restore(){
 for(const generated of document.querySelectorAll('#sk1302-public-nav,[data-sk13032-managed="1"]'))generated.remove();
 for(const el of document.querySelectorAll('.sk1302-native-nav-hidden,[data-sk13032-legacy],.sk13032-legacy-link-hidden,.sk13032-empty-legacy-item'))restoreNode(el);
 document.documentElement.classList.remove('sk13032-managed-header');
 document.documentElement.classList.add('sk13034-native-menu');
 document.documentElement.dataset.skPublicHeader='native-13.0.3.4';
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',restore,{once:true});else restore();window.addEventListener('load',restore,{once:true});setTimeout(restore,400);setTimeout(restore,1400);
})();
