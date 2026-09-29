(function(){
  'use strict';
  function ready(fn){document.readyState==='loading'?document.addEventListener('DOMContentLoaded',fn,{once:true}):fn();}
  ready(function(){
    const slider=document.querySelector('[data-bb-slider]');
    if(!slider)return;
    const slides=[...slider.querySelectorAll('.bb570-slide')];
    const dots=slider.querySelector('[data-bb-dots]');
    if(!slides.length||!dots)return;
    let i=0,t=null;
    slides.forEach((_,n)=>{
      const d=document.createElement('i');
      if(!n)d.className='active';
      d.addEventListener('click',()=>go(n));
      dots.appendChild(d);
    });
    const ds=[...dots.children];
    function go(n){
      i=(n+slides.length)%slides.length;
      slides.forEach((s,x)=>s.classList.toggle('active',x===i));
      ds.forEach((d,x)=>d.classList.toggle('active',x===i));
      restart();
    }
    function restart(){
      if(t)clearInterval(t);
      if(slider.dataset.autoplay==='1')t=setInterval(()=>go(i+1),Math.max(3,parseInt(slider.dataset.seconds||'6',10))*1000);
    }
    const prev=slider.querySelector('[data-bb-prev]'),next=slider.querySelector('[data-bb-next]');
    if(prev)prev.addEventListener('click',()=>go(i-1));
    if(next)next.addEventListener('click',()=>go(i+1));
    restart();
  });
})();
