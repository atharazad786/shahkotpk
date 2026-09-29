(function(){
  'use strict';
  const groups=[...document.querySelectorAll('[data-nav-group]')];
  function closeGroup(group){group.classList.remove('is-open');const b=group.querySelector('[data-nav-toggle]');if(b)b.setAttribute('aria-expanded','false');}
  function closeAll(except){groups.forEach(g=>{if(g!==except)closeGroup(g);});}
  groups.forEach(group=>{
    const btn=group.querySelector('[data-nav-toggle]');
    if(!btn)return;
    btn.addEventListener('click',function(e){
      e.preventDefault();e.stopPropagation();
      const open=!group.classList.contains('is-open');
      closeAll(group);
      group.classList.toggle('is-open',open);
      btn.setAttribute('aria-expanded',open?'true':'false');
    });
  });
  document.addEventListener('click',function(e){if(!e.target.closest('[data-nav-group]'))closeAll();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeAll();});
  const menu=document.querySelector('[data-lt-menu]'),nav=document.querySelector('[data-lt-nav]');
  if(menu&&nav){
    menu.addEventListener('click',function(){setTimeout(()=>menu.setAttribute('aria-expanded',nav.classList.contains('is-open')?'true':'false'),0);});
  }
  window.addEventListener('resize',function(){if(window.innerWidth>1180)closeAll();});
})();
