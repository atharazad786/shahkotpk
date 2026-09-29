(function(){
  'use strict';
  var body=document.body;
  if(!body || !body.classList.contains('landing-theme-city-listing-motion')) return;

  var reduce=window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var coarse=window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

  function clamp(n,min,max){return Math.max(min,Math.min(max,n));}

  /* Step 3: full hero parallax/3D motion on capable devices. */
  var hero=document.querySelector('.lt-hero.cgp-hero, .lt-hero');
  if(hero && !reduce && !coarse){
    var heroFrame=0;
    var resetHero=function(){
      hero.style.setProperty('--hero-rx','0deg');
      hero.style.setProperty('--hero-ry','0deg');
      hero.style.setProperty('--hero-mx','0px');
      hero.style.setProperty('--hero-my','0px');
      hero.style.setProperty('--hero-panel-x','0px');
      hero.style.setProperty('--hero-panel-y','0px');
      hero.style.setProperty('--hero-glow-x','74%');
      hero.style.setProperty('--hero-glow-y','18%');
    };
    resetHero();
    hero.addEventListener('pointermove',function(ev){
      if(heroFrame) cancelAnimationFrame(heroFrame);
      heroFrame=requestAnimationFrame(function(){
        var r=hero.getBoundingClientRect();
        if(!r.width || !r.height) return;
        var nx=clamp(((ev.clientX-r.left)/r.width-.5)*2,-1,1);
        var ny=clamp(((ev.clientY-r.top)/r.height-.5)*2,-1,1);
        hero.style.setProperty('--hero-rx',(-ny*3.5).toFixed(2)+'deg');
        hero.style.setProperty('--hero-ry',(nx*4.4).toFixed(2)+'deg');
        hero.style.setProperty('--hero-mx',(nx*10).toFixed(1)+'px');
        hero.style.setProperty('--hero-my',(ny*8).toFixed(1)+'px');
        hero.style.setProperty('--hero-panel-x',(-nx*9).toFixed(1)+'px');
        hero.style.setProperty('--hero-panel-y',(-ny*7).toFixed(1)+'px');
        hero.style.setProperty('--hero-glow-x',(((nx+1)/2)*100).toFixed(1)+'%');
        hero.style.setProperty('--hero-glow-y',(((ny+1)/2)*100).toFixed(1)+'%');
      });
    },{passive:true});
    hero.addEventListener('pointerleave',resetHero,{passive:true});
  }

  /* Steps 1 & 2: dynamic 3D card tilt + highlight; category color is CSS-driven. */
  var selector=[
    '.cgp-city-card','.cgp-stat-grid','.cgp-hero-shortcuts a',
    '.cgp-category-grid > a','.cgp-category-grid > article','.lt-category-grid > a','.lt-category-grid > article',
    '.lt-service-grid > a','.lt-service-grid > article','.lt-business-grid > article','.cgp-local-business-grid > article',
    '.cgp-deal-grid > article','.cgp-property-grid > article','.cgp-event-grid > article','.cgp-guide-grid > a','.cgp-guide-grid > article',
    '.cgp-job-list > a','.cgp-job-list > article','.lt-info-grid > article','.lt-sponsored-inner','.hb53-ad-card','.lt-custom-card',
    '.lt-spot-grid > a','.lt-spot-grid > article','.lt-gallery-grid > article','.lt-directory a','.lt-directory article',
    '.news-card-grid > article','.news-lead-card','.news-side-list > a','.news-video-grid > article','.blog-home-grid > article',
    '.cgp-sponsor-card','.cgp-advertise-card','.lt-cta-inner',
    '.growth-rated','.growth-quick-links > a','.growth-emergency-strip > a','.growth-empty'
  ].join(',');
  var cards=[].slice.call(document.querySelectorAll(selector));
  cards.forEach(function(card,index){
    card.classList.add('sp3d-card');
    card.style.setProperty('--sp-index',String(index));
    if(reduce || coarse) return;
    var frame=0;
    card.addEventListener('pointermove',function(ev){
      if(frame) cancelAnimationFrame(frame);
      frame=requestAnimationFrame(function(){
        var r=card.getBoundingClientRect();
        if(!r.width || !r.height) return;
        var px=clamp((ev.clientX-r.left)/r.width,0,1);
        var py=clamp((ev.clientY-r.top)/r.height,0,1);
        var ry=(px-.5)*9;
        var rx=(.5-py)*7;
        card.style.setProperty('--card-rx',rx.toFixed(2)+'deg');
        card.style.setProperty('--card-ry',ry.toFixed(2)+'deg');
        card.style.setProperty('--shine-x',(px*100).toFixed(1)+'%');
        card.style.setProperty('--shine-y',(py*100).toFixed(1)+'%');
      });
    },{passive:true});
    card.addEventListener('pointerleave',function(){
      card.style.setProperty('--card-rx','0deg');
      card.style.setProperty('--card-ry','0deg');
      card.style.setProperty('--shine-x','50%');
      card.style.setProperty('--shine-y','18%');
    },{passive:true});
  });

  /* v13.9.0: subtle section reveal keeps long home pages polished without hiding content when JS is unavailable. */
  if(!reduce && 'IntersectionObserver' in window){
    var revealTargets=[].slice.call(document.querySelectorAll('.lt-section,.growth-home,.news-home-section,.blog-home,.lt-featured,.cgp-deals,.cgp-property,.cgp-events,.cgp-jobs'));
    revealTargets.forEach(function(el){ el.classList.add('sp-reveal-ready'); });
    var io=new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){ entry.target.classList.add('sp-reveal-in'); io.unobserve(entry.target); }
      });
    },{rootMargin:'0px 0px -8% 0px',threshold:.08});
    revealTargets.forEach(function(el){ io.observe(el); });
  }
})();
