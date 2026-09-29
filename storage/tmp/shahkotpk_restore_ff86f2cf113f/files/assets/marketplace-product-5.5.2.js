(function(){
  'use strict';
  var main=document.getElementById('mp552-main-image');
  document.querySelectorAll('.mp552-thumb').forEach(function(btn){btn.addEventListener('click',function(){if(main)main.src=btn.getAttribute('data-image')||main.src;document.querySelectorAll('.mp552-thumb').forEach(function(x){x.classList.remove('active')});btn.classList.add('active')})});
  var qty=document.getElementById('mp552-qty-input');
  function setQty(delta){if(!qty)return;var min=parseInt(qty.min||'1',10),max=parseInt(qty.max||'999999',10),v=parseInt(qty.value||String(min),10);qty.value=String(Math.max(min,Math.min(max,v+delta)))}
  var minus=document.querySelector('[data-qty-minus]'),plus=document.querySelector('[data-qty-plus]');if(minus)minus.addEventListener('click',function(){setQty(-1)});if(plus)plus.addEventListener('click',function(){setQty(1)});
  document.querySelectorAll('[data-copy]').forEach(function(btn){btn.addEventListener('click',async function(){var text=btn.getAttribute('data-copy')||'';try{await navigator.clipboard.writeText(text);var old=btn.textContent;btn.textContent='✓ Copied';setTimeout(function(){btn.textContent=old},1400)}catch(e){var t=document.createElement('textarea');t.value=text;document.body.appendChild(t);t.select();document.execCommand('copy');t.remove();}})});
})();
