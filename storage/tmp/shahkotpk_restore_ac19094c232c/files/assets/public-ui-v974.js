/* ShahkotPK v9.5.0 — smart render + low-overhead media runtime */
(function(){
  'use strict';
  var cardSelector='.product-card,.business-card,.listing-card,.post-card,.marketplace-card,.market-card,.property-card,.news-card,.featured-card,.deal-card,.event-card,.job-card,.city-card,.marketplace-grid > article,.business-grid > article,.cards-grid > article,.card-grid > article,[data-card="product"],[data-card="business"],[data-card="listing"]';
  var bannerSelector='.hero-slider,.hero-banner,.homepage-banner,.banner-slide,.banner-card,.home-banner,[data-banner],[data-placement*="homepage_hero"],[data-placement*="homepage_middle"],[data-placement*="homepage_bottom"]';
  var doneCards=new WeakSet(),doneBanners=new WeakSet(),doneImages=new WeakSet();
  var conn=navigator.connection||navigator.mozConnection||navigator.webkitConnection;
  var saveData=!!(conn&&conn.saveData), reduced=window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches;
  var weak=(navigator.deviceMemory&&navigator.deviceMemory<=4)||(navigator.hardwareConcurrency&&navigator.hardwareConcurrency<=4);
  if(saveData)document.documentElement.classList.add('shk-v950-save-data');
  if(reduced||weak)document.documentElement.classList.add('shk-v950-lite');

  function near(el,ratio){
    try{var r=el.getBoundingClientRect(),h=window.innerHeight||document.documentElement.clientHeight;return r.top<h*(ratio||1.12)&&r.bottom>-100;}catch(e){return false;}
  }
  function reserve(img,type){
    if(!img||img.hasAttribute('width')||img.hasAttribute('height'))return;
    if(type==='banner'){img.setAttribute('width','1600');img.setAttribute('height','700');}
    else{img.setAttribute('width','800');img.setAttribute('height','600');}
  }
  function tuneImage(img,mode,type){
    if(!img||doneImages.has(img))return;doneImages.add(img);
    reserve(img,type);img.decoding='async';
    if(img.srcset&&!img.sizes)img.sizes=type==='banner'?'100vw':'(max-width: 600px) 92vw, (max-width: 1000px) 46vw, 320px';
    if(mode==='hero'){img.loading='eager';try{img.fetchPriority='high';}catch(e){}}
    else if(mode==='visible'){img.loading='eager';try{img.fetchPriority='auto';}catch(e){}}
    else{img.loading='lazy';try{img.fetchPriority='low';}catch(e){}}
  }
  function cardKind(el){var s=((el.className||'')+' '+(el.getAttribute('data-card')||'')).toLowerCase();return s.indexOf('product')>-1||s.indexOf('market')>-1?'product':'card';}
  function activateCard(el,visible){
    if(!el||el.nodeType!==1||doneCards.has(el))return;doneCards.add(el);
    el.classList.add('shk-v950-card');el.setAttribute('data-shk-kind',cardKind(el));
    if(!visible)el.classList.add('shk-v950-deferred');
    var imgs=el.querySelectorAll('img');for(var i=0;i<imgs.length;i++)tuneImage(imgs[i],visible?'visible':'lazy','card');
  }
  function activateBanner(el,index,visible){
    if(!el||el.nodeType!==1||doneBanners.has(el))return;doneBanners.add(el);
    el.classList.add('shk-v950-banner');if(!visible)el.classList.add('shk-v950-deferred');
    var imgs=el.querySelectorAll('img');for(var i=0;i<imgs.length;i++)tuneImage(imgs[i],index===0&&i===0?'hero':(visible?'visible':'lazy'),'banner');
  }

  var io=('IntersectionObserver' in window)?new IntersectionObserver(function(entries){
    for(var i=0;i<entries.length;i++)if(entries[i].isIntersecting){var el=entries[i].target;io.unobserve(el);if(el.matches(cardSelector))activateCard(el,true);else if(el.matches(bannerSelector))activateBanner(el,1,true);}
  },{rootMargin:'260px 0px'}):null;

  function queueElement(el,index){
    if(!el||el.nodeType!==1)return;
    if(el.matches&&el.matches(cardSelector)){
      if(near(el,1.18)||!io)activateCard(el,true);else{el.classList.add('shk-v950-pending');io.observe(el);}
    }else if(el.matches&&el.matches(bannerSelector)){
      if(index===0||near(el,1.1)||!io)activateBanner(el,index||1,true);else{el.classList.add('shk-v950-pending');io.observe(el);}
    }
  }
  function scan(root){
    if(!root||root.nodeType!==1)return;
    queueElement(root,1);
    var banners=root.querySelectorAll?root.querySelectorAll(bannerSelector):[];for(var b=0;b<banners.length;b++)queueElement(banners[b],b);
    var cards=root.querySelectorAll?root.querySelectorAll(cardSelector):[];for(var c=0;c<cards.length;c++)queueElement(cards[c],1);
  }
  function initial(){
    var banners=document.querySelectorAll(bannerSelector);for(var b=0;b<banners.length;b++)queueElement(banners[b],b);
    var cards=document.querySelectorAll(cardSelector);for(var c=0;c<cards.length;c++)queueElement(cards[c],1);
  }
  function start(){
    initial();
    if(!('MutationObserver' in window))return;
    var pending=[],scheduled=false;
    function flush(){scheduled=false;var list=pending.splice(0,pending.length);for(var i=0;i<list.length;i++)scan(list[i]);}
    var mo=new MutationObserver(function(records){
      for(var i=0;i<records.length;i++)for(var j=0;j<records[i].addedNodes.length;j++){var n=records[i].addedNodes[j];if(n&&n.nodeType===1)pending.push(n);}
      if(pending.length&&!scheduled){scheduled=true;(window.requestIdleCallback||function(cb){setTimeout(cb,80);})(flush,{timeout:300});}
    });
    mo.observe(document.body,{childList:true,subtree:true});
    /* Stop broad DOM observation after the initial dynamic-render window. */
    setTimeout(function(){try{mo.disconnect();}catch(e){}},15000);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})();


/* ShahkotPK v9.7.4 — Global Admin Tools sidebar injector */
(() => {
  'use strict';
  if (!/^\/admin(?:\/|$)/i.test(location.pathname)) return;
  const MARK = 'sk974-admin-tools';
  const DEST = '/admin/admin-tools.php';
  function norm(s){ return (s || '').replace(/\s+/g,' ').trim().toLowerCase(); }
  function visible(el){
    if(!el) return false;
    const s=getComputedStyle(el), r=el.getBoundingClientRect();
    return s.display!=='none' && s.visibility!=='hidden' && r.width>0 && r.height>0;
  }
  function sidebarOf(el){
    return el && el.closest('.admin-sidebar,.sidebar,#sidebar,aside,[data-admin-sidebar],[data-sidebar],nav');
  }
  function candidates(){
    return [...document.querySelectorAll('a[href]')].filter(a=>visible(a) && sidebarOf(a));
  }
  function reference(){
    const links=candidates();
    for(const label of ['overview','dashboard widgets','advanced analytics']){
      const a=links.find(x=>norm(x.textContent).includes(label));
      if(a) return a;
    }
    return links.find(a=>(a.getAttribute('href')||'').startsWith('/admin/')) || null;
  }
  function menuRow(a){
    if(!a) return null;
    const root=sidebarOf(a); let row=a;
    while(row.parentElement && row.parentElement!==root){
      const p=row.parentElement;
      const direct=[...p.children].filter(c=>c.matches?.('a[href]') || c.querySelector?.(':scope > a[href]'));
      if(direct.length>1) break;
      row=p;
    }
    return row;
  }
  function wipeActive(root){
    [root,...root.querySelectorAll('*')].forEach(el=>{
      ['active','is-active','current','selected','open'].forEach(c=>el.classList?.remove(c));
      el.removeAttribute?.('aria-current');
    });
  }
  function relabel(clone){
    const leaves=[...clone.querySelectorAll('span,b,strong,em,p,div')].filter(el=>el.children.length===0);
    const target=leaves.find(el=>/overview|dashboard widgets|advanced analytics/i.test(el.textContent||''));
    if(target){ target.textContent='Admin Tools'; return; }
    const a=clone.matches('a[href]')?clone:clone.querySelector('a[href]');
    if(!a) return;
    for(const n of [...a.childNodes].reverse()){
      if(n.nodeType===Node.TEXT_NODE && norm(n.textContent)){ n.textContent=' Admin Tools '; return; }
    }
    const s=document.createElement('span'); s.textContent='Admin Tools'; a.appendChild(s);
  }
  function removeCounters(clone){
    clone.querySelectorAll('[class*="badge"],[class*="count"],[data-count]').forEach(el=>el.remove());
  }
  function install(){
    const existing=document.querySelector(`a[href="${DEST}"]`);
    if(existing){
      if(location.pathname===DEST || location.pathname.endsWith('/admin-tools.php')){
        existing.setAttribute('aria-current','page'); existing.classList.add('active');
      }
      return true;
    }
    const ref=reference(); if(!ref) return false;
    const row=menuRow(ref); if(!row || !row.parentElement) return false;
    const clone=row.cloneNode(true); clone.setAttribute(`data-${MARK}`,'1'); wipeActive(clone); removeCounters(clone);
    const a=clone.matches('a[href]')?clone:clone.querySelector('a[href]'); if(!a) return false;
    a.href=DEST; a.setAttribute('title','Admin Tools'); relabel(clone);
    if(location.pathname===DEST || location.pathname.endsWith('/admin-tools.php')){
      clone.classList.add('active'); a.classList.add('active'); a.setAttribute('aria-current','page');
    }
    row.parentElement.insertBefore(clone,row);
    return true;
  }
  function boot(){
    if(install()) return;
    let tries=0;
    const timer=setInterval(()=>{ if(install() || ++tries>=50) clearInterval(timer); },100);
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot,{once:true}); else boot();
})();
