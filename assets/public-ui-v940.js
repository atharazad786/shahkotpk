/* ShahkotPK v9.4.0 — adaptive above-the-fold media loader */
(function(){
  'use strict';
  var cardSelector='.product-card,.business-card,.listing-card,.post-card,.marketplace-card,.market-card,.property-card,.news-card,.featured-card,.deal-card,.event-card,.job-card,.city-card,.marketplace-grid > article,.business-grid > article,.cards-grid > article,.card-grid > article,[data-card="product"],[data-card="business"],[data-card="listing"]';
  var bannerSelector='.hero-slider,.hero-banner,.homepage-banner,.banner-slide,.banner-card,.home-banner,[data-banner],[data-placement*="homepage_hero"],[data-placement*="homepage_middle"],[data-placement*="homepage_bottom"]';
  var seenCards=new WeakSet(),seenBanners=new WeakSet(),seenImages=new WeakSet();
  var priorityCards=0;
  var conn=navigator.connection||navigator.mozConnection||navigator.webkitConnection;
  var saveData=!!(conn&&conn.saveData);
  if(saveData)document.documentElement.classList.add('shk-v940-save-data');
  function inNearViewport(el,mult){try{var r=el.getBoundingClientRect();return r.top<innerHeight*(mult||1.15)&&r.bottom>-120;}catch(e){return false;}}
  function setPriority(img,mode){
    if(!img||seenImages.has(img))return;seenImages.add(img);img.decoding='async';
    if(mode==='hero'){img.loading='eager';try{img.fetchPriority='high';}catch(e){}}
    else if(mode==='visible'){
      img.loading='eager';
      try{img.fetchPriority=priorityCards<2?'high':'auto';}catch(e){}
      priorityCards++;
    }else{img.loading='lazy';try{img.fetchPriority='low';}catch(e){}}
  }
  function kind(el){var s=((el.className||'')+' '+(el.getAttribute('data-card')||'')).toLowerCase();return s.indexOf('product')>-1||s.indexOf('market')>-1?'product':'card';}
  function card(el){
    if(!el||el.nodeType!==1||seenCards.has(el))return;seenCards.add(el);el.classList.add('shk-v940-card');el.setAttribute('data-shk-kind',kind(el));
    var visible=!saveData&&inNearViewport(el,1.2);if(!visible)el.classList.add('shk-v940-deferred');
    var img=el.querySelector('img');if(img){img.classList.add('shk-v940-media');setPriority(img,visible?'visible':'lazy');}
  }
  function banner(el,index){
    if(!el||el.nodeType!==1||seenBanners.has(el))return;seenBanners.add(el);el.classList.add('shk-v940-banner');
    var visible=inNearViewport(el,1.1);if(!visible)el.classList.add('shk-v940-deferred');
    var imgs=el.querySelectorAll('img');for(var i=0;i<imgs.length;i++)setPriority(imgs[i],index===0&&i===0?'hero':(visible?'visible':'lazy'));
  }
  function processNode(root){
    if(!root||root.nodeType!==1)return;
    if(root.matches&&root.matches(cardSelector))card(root);
    if(root.matches&&root.matches(bannerSelector))banner(root,1);
    var cards=root.querySelectorAll?root.querySelectorAll(cardSelector):[];for(var i=0;i<cards.length;i++)card(cards[i]);
    var banners=root.querySelectorAll?root.querySelectorAll(bannerSelector):[];for(var j=0;j<banners.length;j++)banner(banners[j],j);
  }
  function runBatch(list,fn,start){
    var i=start||0,end=Math.min(i+24,list.length);for(;i<end;i++)fn(list[i],i);
    if(i<list.length){(window.requestIdleCallback||function(cb){setTimeout(cb,24);})(function(){runBatch(list,fn,i);},{timeout:180});}
  }
  function initial(){
    var banners=document.querySelectorAll(bannerSelector);for(var j=0;j<banners.length&&j<2;j++)banner(banners[j],j);
    var cards=document.querySelectorAll(cardSelector),above=[],below=[];
    for(var i=0;i<cards.length;i++)(inNearViewport(cards[i],1.2)?above:below).push(cards[i]);
    for(var a=0;a<above.length;a++)card(above[a]);
    runBatch(below,card,0);if(banners.length>2)runBatch(Array.prototype.slice.call(banners,2),banner,0);
  }
  function start(){
    initial();
    if(!('MutationObserver' in window))return;
    var queue=[],queued=new WeakSet(),scheduled=false;
    function flush(){scheduled=false;var list=queue.splice(0,queue.length);queued=new WeakSet();runBatch(list,processNode,0);}
    var mo=new MutationObserver(function(records){
      for(var i=0;i<records.length;i++)for(var j=0;j<records[i].addedNodes.length;j++){var n=records[i].addedNodes[j];if(n.nodeType===1&&!queued.has(n)){queued.add(n);queue.push(n);}}
      if(queue.length&&!scheduled){scheduled=true;(window.requestIdleCallback||function(cb){setTimeout(cb,60);})(flush,{timeout:260});}
    });
    mo.observe(document.body,{childList:true,subtree:true});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})();
