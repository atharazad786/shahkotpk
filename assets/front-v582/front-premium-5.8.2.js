(function(){
  const form=document.querySelector('[data-v582-smart-search]');
  const tabs=[...document.querySelectorAll('[data-v582-search-target]')];
  let target='/search.php';
  tabs.forEach(btn=>btn.addEventListener('click',()=>{
    tabs.forEach(x=>x.classList.remove('is-active'));
    btn.classList.add('is-active');
    target=btn.dataset.v582SearchTarget||'/search.php';
    if(form) form.action=target;
    const input=form?.querySelector('input[name="q"]');
    const type=btn.dataset.v582SearchType||'all';
    const placeholders={all:'Search doctors, shops, services, products, jobs...',business:'Search local businesses...',doctor:'Search doctor or specialty...',property:'Search property, area or project...',jobs:'Search jobs or companies...',shop:'Search products or stores...',blood:'Search blood services or information...'};
    if(input) input.placeholder=placeholders[type]||placeholders.all;
  }));
  if(form) form.addEventListener('submit',()=>{form.action=target});

  if('IntersectionObserver' in window){
    const blocks=[...document.querySelectorAll('.lt-section,.lt-slider,.news-home-section,.blog-home,.cgp-sponsor-spotlight')];
    blocks.forEach(x=>x.classList.add('v582-reveal'));
    const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('is-visible');io.unobserve(e.target)}}),{threshold:.08,rootMargin:'40px 0px'});
    blocks.forEach(x=>io.observe(x));
  }
})();
