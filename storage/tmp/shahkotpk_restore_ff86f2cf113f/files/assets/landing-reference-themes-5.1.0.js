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
