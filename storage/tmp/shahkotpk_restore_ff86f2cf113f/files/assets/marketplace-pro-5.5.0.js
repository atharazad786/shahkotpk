(function(){
  function tick(){document.querySelectorAll('[data-countdown]').forEach(function(el){var raw=el.getAttribute('data-countdown');if(!raw)return;var t=new Date(raw.replace(' ','T')).getTime();if(!isFinite(t))return;var d=Math.max(0,t-Date.now()),s=Math.floor(d/1000),days=Math.floor(s/86400);s%=86400;var h=Math.floor(s/3600);s%=3600;var m=Math.floor(s/60),sec=s%60;el.textContent=(days?days+'d ':'')+String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0');if(d<=0)el.textContent='Ended';});}
  tick();setInterval(tick,1000);
  document.querySelectorAll('input[readonly]').forEach(function(i){i.addEventListener('dblclick',function(){this.select();try{navigator.clipboard.writeText(this.value);}catch(e){}})});
})();
