/* ShahkotPK v9.3.1 — lightweight public UI performance enhancer */
(function(){
  'use strict';
  var cardSelector='.product-card,.business-card,.listing-card,.post-card,.marketplace-card,.market-card,.property-card,.news-card,.featured-card,.deal-card,.event-card,.job-card,.city-card,.marketplace-grid > article,.business-grid > article,.cards-grid > article,.card-grid > article,[data-card="product"],[data-card="business"],[data-card="listing"]';
  var bannerSelector='.hero-slider,.hero-banner,.homepage-banner,.banner-slide,.banner-card,.home-banner,[data-banner],[data-placement*="homepage_hero"],[data-placement*="homepage_middle"],[data-placement*="homepage_bottom"]';
  function kind(el){var s=((el.className||'')+' '+(el.getAttribute('data-card')||'')).toLowerCase();return s.indexOf('product')>-1||s.indexOf('market')>-1?'product':'card';}
  function tuneImage(img,isBanner,index){
    if(!img||img.dataset.shk931Img==='1')return;
    img.dataset.shk931Img='1';
    img.decoding='async';
    if(isBanner && index===0){img.loading='eager';try{img.fetchPriority='high';}catch(e){}}
    else{
      var top=0;try{top=img.getBoundingClientRect().top;}catch(e){}
      if(top>window.innerHeight*1.05 || !isBanner){img.loading='lazy';try{img.fetchPriority='low';}catch(e){}}
    }
  }
  function card(el){
    if(!el||el.nodeType!==1||el.dataset.shk931Card==='1')return;
    el.dataset.shk931Card='1';el.classList.add('shk-v930-card');el.setAttribute('data-shk-kind',kind(el));
    var img=el.querySelector('img');if(img){img.classList.add('shk-v930-media');tuneImage(img,false,0);}
  }
  function banner(el,index){
    if(!el||el.nodeType!==1||el.dataset.shk931Banner==='1')return;
    el.dataset.shk931Banner='1';el.classList.add('shk-v930-banner');
    var imgs=el.querySelectorAll('img');for(var i=0;i<imgs.length;i++)tuneImage(imgs[i],true,index+i);
  }
  function enhanceRoot(root){
    if(!root||root.nodeType!==1)return;
    if(root.matches&&root.matches(cardSelector))card(root);
    if(root.matches&&root.matches(bannerSelector))banner(root,1);
    var cards=root.querySelectorAll?root.querySelectorAll(cardSelector):[];
    for(var i=0;i<cards.length;i++)card(cards[i]);
    var banners=root.querySelectorAll?root.querySelectorAll(bannerSelector):[];
    for(var j=0;j<banners.length;j++)banner(banners[j],j);
  }
  function initial(){
    var cards=document.querySelectorAll(cardSelector);for(var i=0;i<cards.length;i++)card(cards[i]);
    var banners=document.querySelectorAll(bannerSelector);for(var j=0;j<banners.length;j++)banner(banners[j],j);
  }
  function start(){
    initial();
    if(!('MutationObserver' in window))return;
    var pending=[],scheduled=false;
    var flush=function(){scheduled=false;var list=pending.splice(0,pending.length);for(var i=0;i<list.length;i++)enhanceRoot(list[i]);};
    var mo=new MutationObserver(function(records){
      for(var i=0;i<records.length;i++)for(var j=0;j<records[i].addedNodes.length;j++)if(records[i].addedNodes[j].nodeType===1)pending.push(records[i].addedNodes[j]);
      if(pending.length&&!scheduled){scheduled=true;(window.requestIdleCallback||function(cb){setTimeout(cb,80);})(flush,{timeout:350});}
    });
    mo.observe(document.body,{childList:true,subtree:true});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})();
