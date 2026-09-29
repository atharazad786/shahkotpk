(function(){
  const menu=document.querySelector('[data-v700-menu]'),nav=document.querySelector('[data-v700-nav]');
  if(menu&&nav)menu.addEventListener('click',()=>nav.classList.toggle('open'));
  if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/super-app-sw.js').catch(()=>{}));}
  let deferredPrompt=null;window.addEventListener('beforeinstallprompt',e=>{e.preventDefault();deferredPrompt=e;document.querySelectorAll('[data-v700-install]').forEach(b=>b.hidden=false)});
  document.addEventListener('click',async e=>{const b=e.target.closest('[data-v700-install]');if(!b||!deferredPrompt)return;deferredPrompt.prompt();try{await deferredPrompt.userChoice;}catch(_){}deferredPrompt=null;b.hidden=true;});
  document.querySelectorAll('[data-copy-ref]').forEach(b=>b.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(b.dataset.copyRef||'');b.textContent='Copied';}catch(_){}}));
})();
