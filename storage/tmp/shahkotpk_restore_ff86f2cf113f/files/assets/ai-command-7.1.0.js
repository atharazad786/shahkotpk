(()=>{
  const root=document.querySelector('.ai710'); if(!root)return;
  const csrf=document.querySelector('meta[name="csrf-token"]')?.content||root.dataset.csrf||'';
  const chatForm=root.querySelector('[data-ai710-chat-form]');
  const chatInput=root.querySelector('[data-ai710-chat-input]');
  const chatLog=root.querySelector('[data-ai710-chat-log]');
  const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  if(chatForm&&chatInput&&chatLog){
    chatForm.addEventListener('submit',async e=>{
      e.preventDefault(); const msg=chatInput.value.trim(); if(msg.length<2)return;
      chatLog.insertAdjacentHTML('beforeend',`<div class="ai710-msg user">${esc(msg)}</div><div class="ai710-msg ai" data-thinking>Thinking…</div>`);
      chatInput.value=''; chatInput.disabled=true;
      const fd=new FormData(); fd.append('_csrf',csrf);fd.append('action','chat');fd.append('message',msg);
      try{
        const r=await fetch('/api/ai-v710.php',{method:'POST',body:fd,credentials:'same-origin'}); const j=await r.json(); if(!j.ok)throw new Error(j.error||'AI request failed');
        const think=chatLog.querySelector('[data-thinking]'); if(think){think.removeAttribute('data-thinking');think.innerHTML=esc(j.reply)+(j.provider?`<div class="ai710-meta"><span>${esc(j.provider)}</span><span>${esc(j.model||'')}</span><span>${esc(j.mode||'')}</span></div>`:'');}
      }catch(err){const think=chatLog.querySelector('[data-thinking]');if(think){think.removeAttribute('data-thinking');think.textContent=err.message||'AI unavailable.';}}
      finally{chatInput.disabled=false;chatInput.focus();chatLog.scrollTop=chatLog.scrollHeight;}
    });
  }
  root.querySelectorAll('[data-ai710-confirm]').forEach(btn=>btn.addEventListener('click',e=>{if(!confirm(btn.dataset.ai710Confirm||'Continue?'))e.preventDefault();}));
  root.querySelectorAll('[data-ai710-copy]').forEach(btn=>btn.addEventListener('click',async()=>{const val=btn.dataset.ai710Copy||'';try{await navigator.clipboard.writeText(val);btn.textContent='Copied ✓';setTimeout(()=>btn.textContent='Copy',1200)}catch(e){}}));
})();
