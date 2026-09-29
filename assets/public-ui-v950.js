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
