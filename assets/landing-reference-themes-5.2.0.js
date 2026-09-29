(function(){
  var body=document.body;
  if(!body||!body.className.match(/landing-theme-(city-listing-motion|townhub-explorer|urbango-spots)/))return;
  var animate=body.getAttribute('data-theme-animations')==='1'&&!window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(animate&&'IntersectionObserver' in window){
    var io=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('ref51-visible');io.unobserve(e.target);}});},{threshold:.08,rootMargin:'0px 0px -30px 0px'});
    document.querySelectorAll('.lt-section,.news-home-section,.blog-home,.lt-slider').forEach(function(el){el.classList.add('ref51-reveal');io.observe(el);});
    var style=document.createElement('style');style.textContent='.ref51-reveal{opacity:0;transform:translateY(18px);transition:opacity .55s ease,transform .55s ease}.ref51-visible{opacity:1;transform:none}';document.head.appendChild(style);
  }
  var header=document.querySelector('.lt-header');
  if(header){var sync=function(){header.classList.toggle('ref51-scrolled',window.scrollY>28)};sync();window.addEventListener('scroll',sync,{passive:true});}
})();
(function(){
  var body=document.body;if(!body||!body.matches('.landing-theme-city-listing-motion,.landing-theme-townhub-explorer,.landing-theme-urbango-spots'))return;
  var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(!reduce&&body.getAttribute('data-theme-animations')==='1'){
    document.querySelectorAll('.cgp-stat-grid b').forEach(function(el){
      var raw=(el.textContent||'').replace(/,/g,'').trim();if(!/^\d+$/.test(raw))return;var target=parseInt(raw,10);if(target<2)return;var start=null,duration=700;
      function frame(ts){if(start===null)start=ts;var p=Math.min(1,(ts-start)/duration);var n=Math.round(target*(1-Math.pow(1-p,3)));el.textContent=n.toLocaleString();if(p<1)requestAnimationFrame(frame)}
      requestAnimationFrame(frame);
    });
  }
  document.querySelectorAll('img[loading="lazy"]').forEach(function(img){img.addEventListener('load',function(){img.classList.add('sk52-img-ready')},{once:true});});
})();
