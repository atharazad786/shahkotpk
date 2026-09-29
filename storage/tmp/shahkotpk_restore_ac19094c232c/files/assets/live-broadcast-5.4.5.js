(function(){
  'use strict';
  function loadHls(){
    if(window.Hls)return Promise.resolve(window.Hls);
    return new Promise(function(resolve,reject){
      var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';s.async=true;s.onload=function(){window.Hls?resolve(window.Hls):reject(new Error('HLS unavailable'));};s.onerror=reject;document.head.appendChild(s);
    });
  }
  document.querySelectorAll('video.live545-hls[data-hls]').forEach(function(video){
    var src=video.getAttribute('data-hls');if(!src)return;
    if(video.canPlayType('application/vnd.apple.mpegurl')){video.src=src;return;}
    loadHls().then(function(Hls){if(Hls.isSupported()){var h=new Hls({enableWorker:true,lowLatencyMode:true,backBufferLength:30});h.loadSource(src);h.attachMedia(video);video._shahkotHls=h;}else{throw new Error('HLS not supported');}}).catch(function(){var fb=video.parentElement&&video.parentElement.querySelector('.live545-player-fallback');if(fb)fb.hidden=false;});
  });

  var cfg=window.ShahkotLive545||null;if(!cfg||!cfg.broadcastId)return;
  var online=document.querySelector('[data-live545-online]');
  var feed=document.querySelector('[data-live545-chat-feed]');
  var form=document.querySelector('[data-live545-chat-form]');
  var statusEl=document.querySelector('[data-live545-chat-status]');
  var seen=Object.create(null);
  if(feed)feed.querySelectorAll('[data-message-id]').forEach(function(el){seen[el.getAttribute('data-message-id')]=true;});

  function api(action,opts){opts=opts||{};var url='/api/live.php?action='+encodeURIComponent(action)+'&broadcast_id='+encodeURIComponent(cfg.broadcastId);return fetch(url,opts).then(function(r){return r.json().then(function(j){if(!r.ok)throw new Error(j.message||'Request failed');return j;});});}
  function updateOnline(n){if(online&&typeof n!=='undefined')online.textContent=String(n);}
  function appendMessage(m){if(!feed||seen[m.id])return;seen[m.id]=true;var empty=feed.querySelector('.live545-chat-empty');if(empty)empty.remove();var article=document.createElement('article');article.setAttribute('data-message-id',m.id);var avatar=document.createElement('span');avatar.className='avatar';avatar.textContent=(m.display_name||'G').charAt(0).toUpperCase();var body=document.createElement('div');var name=document.createElement('b');name.textContent=m.display_name||'Guest';var p=document.createElement('p');p.textContent=m.message||'';var small=document.createElement('small');var d=new Date(String(m.created_at||'').replace(' ','T'));small.textContent=isNaN(d.getTime())?'Now':d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});body.append(name,p,small);article.append(avatar,body);feed.appendChild(article);feed.scrollTop=feed.scrollHeight;}
  function pollChat(){if(!cfg.chatEnabled)return;api('chat_list').then(function(j){(j.messages||[]).forEach(appendMessage);updateOnline(j.online);}).catch(function(){});}
  function heartbeat(){if(!cfg.analyticsEnabled){api('heartbeat',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'seconds=0'}).then(function(j){updateOnline(j.online);}).catch(function(){});return;}api('heartbeat',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'seconds=15'}).then(function(j){updateOnline(j.online);}).catch(function(){});}
  if(form){form.addEventListener('submit',function(ev){ev.preventDefault();var fd=new FormData(form);var body=new URLSearchParams();body.set('_csrf',cfg.csrf||'');body.set('name',fd.get('name')||'');body.set('message',fd.get('message')||'');if(statusEl)statusEl.textContent='Sending…';api('chat_send',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()}).then(function(j){var input=form.querySelector('input[name="message"]');if(input)input.value='';if(statusEl)statusEl.textContent=j.message||'Sent';setTimeout(pollChat,250);}).catch(function(e){if(statusEl)statusEl.textContent=e.message||'Unable to send.';});});}
  pollChat();heartbeat();setInterval(pollChat,4000);setInterval(heartbeat,15000);
})();
