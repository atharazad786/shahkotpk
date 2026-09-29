/* ShahkotPK v5.8.3 — final DOM safety net for terminal footer placement. */
(function(){
  'use strict';
  function repairFooter(){
    var body=document.body;
    if(!body||!body.classList.contains('cms-public-page'))return;
    var footers=Array.prototype.slice.call(document.querySelectorAll('footer.lt-footer'));
    if(!footers.length)return;
    var footer=footers.shift();
    footers.forEach(function(extra){extra.remove();});
    var children=Array.prototype.slice.call(body.children);
    var ignored=new Set(['SCRIPT','STYLE','LINK']);
    var lastContent=null;
    children.forEach(function(node){
      if(node===footer||ignored.has(node.tagName))return;
      if(node.classList&&node.classList.contains('mobile-sticky-cta'))return;
      lastContent=node;
    });
    if(lastContent&&lastContent.nextElementSibling!==footer){
      lastContent.insertAdjacentElement('afterend',footer);
    }
    footer.setAttribute('data-v583-terminal-footer','1');
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',repairFooter,{once:true});
  else repairFooter();
})();
