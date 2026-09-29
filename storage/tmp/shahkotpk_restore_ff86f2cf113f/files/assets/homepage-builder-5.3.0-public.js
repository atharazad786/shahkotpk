(function(){
  const nodes=[...document.querySelectorAll('.hb53-section-wrap[class*="hb53-anim-"]')];
  if(!nodes.length)return;
  if(!('IntersectionObserver' in window)){nodes.forEach(n=>n.classList.add('hb53-visible'));return;}
  const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('hb53-visible');io.unobserve(e.target)}}),{threshold:.08,rootMargin:'0px 0px -30px'});
  nodes.forEach(n=>io.observe(n));
})();
