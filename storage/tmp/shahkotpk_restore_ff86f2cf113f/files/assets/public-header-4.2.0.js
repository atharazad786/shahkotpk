(function(){
'use strict';
const groups=[...document.querySelectorAll('[data-nav-group]')];
const nav=document.querySelector('[data-lt-nav]');
const menu=document.querySelector('[data-lt-menu]');
function close(g){g.classList.remove('is-open');const b=g.querySelector('[data-nav-toggle]');if(b)b.setAttribute('aria-expanded','false');}
function closeAll(except){groups.forEach(g=>{if(g!==except)close(g);});}
groups.forEach(g=>{const b=g.querySelector('[data-nav-toggle]');if(!b)return;b.addEventListener('click',e=>{e.preventDefault();e.stopPropagation();const willOpen=!g.classList.contains('is-open');closeAll(g);g.classList.toggle('is-open',willOpen);b.setAttribute('aria-expanded',willOpen?'true':'false');});});
if(menu&&nav){menu.addEventListener('click',()=>{const open=!nav.classList.contains('is-open');nav.classList.toggle('is-open',open);menu.setAttribute('aria-expanded',open?'true':'false');if(!open)closeAll();});}
document.addEventListener('click',e=>{if(!e.target.closest('[data-nav-group]'))closeAll();if(nav&&menu&&window.innerWidth<=1120&&!e.target.closest('[data-lt-nav]')&&!e.target.closest('[data-lt-menu]')){nav.classList.remove('is-open');menu.setAttribute('aria-expanded','false');}});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeAll();if(nav)nav.classList.remove('is-open');if(menu)menu.setAttribute('aria-expanded','false');}});
window.addEventListener('resize',()=>{if(window.innerWidth>1120&&nav){nav.classList.remove('is-open');if(menu)menu.setAttribute('aria-expanded','false');closeAll();}});
})();
