(function(){
  'use strict';
  const STORE={collapsed:'shahkotpk_admin_sidebar_collapsed_v543',groups:'shahkotpk_admin_sidebar_groups_v543',pins:'shahkotpk_admin_sidebar_pins_v543'};
  const GROUPS=[
    {id:'dashboard',title:'Dashboard',icon:'⌂',paths:['/admin/index.php','/admin/widgets.php','/admin/analytics.php','/admin/operations.php']},
    {id:'city',title:'City & Listings',icon:'⌖',paths:['/admin/businesses.php','/admin/categories.php','/admin/cities.php','/admin/maps.php','/admin/location-discovery.php','/admin/city-guide.php','/admin/deals.php','/admin/events.php','/admin/jobs.php','/admin/property.php','/admin/emergency.php','/admin/restaurants.php','/admin/services.php','/admin/classifieds.php']},
    {id:'healthcare',title:'Health & Care',icon:'✚',paths:['/admin/doctor-online.php','/admin/blood-bank.php']},
    {id:'market',title:'Marketplace',icon:'◈',paths:['/admin/shop.php','/admin/bookings.php','/admin/delivery.php','/admin/inventory.php','/admin/purchasing.php','/admin/seller-staff.php','/admin/payouts.php','/admin/invoices-commerce.php','/admin/disputes.php']},
    {id:'content',title:'Content',icon:'▤',paths:['/admin/news.php','/admin/blog.php','/admin/live.php','/admin/ticker.php']},
    {id:'marketing',title:'Marketing',icon:'✦',paths:['/admin/ads.php','/admin/ad-marketplace.php','/admin/banners.php','/admin/banner-pack.php','/admin/boosts.php','/admin/campaigns.php','/admin/engagement.php','/admin/loyalty.php','/admin/referrals.php','/admin/whatsapp.php','/admin/push.php','/admin/inbox.php','/admin/leads.php','/admin/reviews.php']},
    {id:'builder',title:'Website Builder',icon:'◫',paths:['/admin/themes.php','/admin/homepage.php','/admin/seo.php','/admin/seo-console.php','/admin/media-library.php','/admin/qr-center.php']},
    {id:'ai_tools',title:'AI Command Center',icon:'✨',paths:['/admin/ai.php','/admin/ai-content-generator.php','/admin/ai-copilot.php']},
    {id:'users',title:'Users & Access',icon:'◎',paths:['/admin/users.php','/admin/roles.php','/admin/verification.php','/admin/moderation.php']},
    {id:'finance',title:'Finance',icon:'₨',paths:['/admin/plans.php','/admin/payments.php','/admin/accounts.php','/admin/monetization.php','/admin/commission-engine.php','/admin/renewals.php','/admin/franchise-settlements.php','/admin/entitlements.php','/admin/reports.php']},
    {id:'tools',title:'Tools',icon:'⚒',paths:['/admin/dummy-data.php','/admin/bulk-tools.php','/admin/regression-tests.php','/admin/support.php']},
    {id:'enterprise',title:'Enterprise v8/v9',icon:'◈',paths:['/admin/enterprise.php','/admin/operations-v9.php']},
    {id:'system',title:'System',icon:'⚙',paths:['/admin/settings.php','/admin/updates.php','/admin/security.php','/admin/system-health.php','/admin/activity-logs.php','/admin/audit.php','/admin/backups.php','/admin/queues.php','/admin/franchise.php','/admin/tenants.php','/admin/multi-city.php','/admin/mobile-api.php','/admin/pos.php']}
  ];
  const ICONS={
    'index.php':'⌂','widgets.php':'▦','analytics.php':'▥','operations.php':'◈','businesses.php':'▤','categories.php':'▦','cities.php':'⌖','maps.php':'◎','location-discovery.php':'⌖','city-guide.php':'◇','deals.php':'%','events.php':'◆','jobs.php':'▣','property.php':'⌂','blood-bank.php':'♥','doctor-online.php':'✚','emergency.php':'+','restaurants.php':'☰','services.php':'⚒','classifieds.php':'🏷','shop.php':'◈','bookings.php':'◷','delivery.php':'🚚','inventory.php':'▦','purchasing.php':'▣','seller-staff.php':'👥','payouts.php':'₨','invoices-commerce.php':'▤','disputes.php':'⚖','news.php':'▰','blog.php':'✎','live.php':'◉','ticker.php':'≋','ads.php':'◫','ad-marketplace.php':'▧','banners.php':'▧','banner-pack.php':'▰','boosts.php':'↑','campaigns.php':'✉','engagement.php':'🔔','loyalty.php':'◆','referrals.php':'∞','whatsapp.php':'WA','push.php':'◉','inbox.php':'☏','leads.php':'↗','reviews.php':'★','themes.php':'✦','homepage.php':'◈','seo.php':'S','seo-console.php':'↗','media-library.php':'▧','qr-center.php':'▦','users.php':'◉','roles.php':'◆','verification.php':'✓','moderation.php':'⚑','plans.php':'▱','payments.php':'₨','accounts.php':'▦','monetization.php':'◆','commission-engine.php':'%','renewals.php':'↻','franchise-settlements.php':'₨','entitlements.php':'🔐','reports.php':'▥','dummy-data.php':'◈','bulk-tools.php':'⇄','regression-tests.php':'✓','support.php':'☏','settings.php':'⚙','updates.php':'⇧','security.php':'🛡','system-health.php':'♥','activity-logs.php':'⌖','audit.php':'☷','backups.php':'⛁','queues.php':'⚙','ai.php':'AI','ai-content-generator.php':'✨','ai-copilot.php':'🤖','enterprise.php':'◈','operations-v9.php':'◆','franchise.php':'⌖','tenants.php':'◫','multi-city.php':'⌖','mobile-api.php':'▣','pos.php':'P'};
  const BADGES={'/admin/homepage.php':'2.0','/admin/location-discovery.php':'5.4','/admin/banner-pack.php':'3','/admin/tenants.php':'v5','/admin/regression-tests.php':'TEST','/admin/blood-bank.php':'NEW','/admin/doctor-online.php':'NEW','/admin/enterprise.php':'v8','/admin/operations-v9.php':'v9','/admin/ai-content-generator.php':'NEW','/admin/ai-copilot.php':'NEW'};
  const LABEL_OVERRIDES={'/admin/shop.php':'E-commerce & Marketplace','/admin/blood-bank.php':'Blood Bank & Donation','/admin/doctor-online.php':'Doctor Online','/admin/location-discovery.php':'Location Discovery','/admin/homepage.php':'Homepage Builder','/admin/banner-pack.php':'Landing Banner Pack','/admin/system-health.php':'System Health','/admin/activity-logs.php':'Logs & Location','/admin/pos.php':'Seller POS','/admin/ai.php':'AI Control Center','/admin/ai-content-generator.php':'AI Content Generator','/admin/ai-copilot.php':'Admin Copilot','/admin/enterprise.php':'Enterprise v8 Command Center','/admin/operations-v9.php':'Enterprise Operations v9'};
  function safeJSON(key,fallback){try{const v=localStorage.getItem(key);return v?JSON.parse(v):fallback}catch(e){return fallback}}
  function saveJSON(key,v){try{localStorage.setItem(key,JSON.stringify(v))}catch(e){}}
  function pathOf(href){try{return new URL(href,location.origin).pathname}catch(e){return href}}
  function fileOf(path){return path.split('/').pop()||''}
  function cleanLabel(text,path){
    if(LABEL_OVERRIDES[path]) return LABEL_OVERRIDES[path];
    let t=(text||'').replace(/\s+/g,' ').trim();
    t=t.replace(/^(AI|SEO|POS|WA)\s+/i,'');
    t=t.replace(/^[^A-Za-z0-9]+\s*/,'');
    return t||path;
  }
  function init(){
    const sidebar=document.querySelector('body.admin-body .sidebar');
    if(!sidebar||sidebar.dataset.v543==='1')return;
    sidebar.dataset.v543='1';sidebar.classList.add('spk-sidebar-v543');
    const body=document.body, brand=sidebar.querySelector('.brand-block');
    const children=Array.from(sidebar.children);
    const sourceLinks=children.filter(el=>el.tagName==='A').map(a=>({href:a.getAttribute('href')||'',text:a.textContent||'',target:a.getAttribute('target')||'',active:a.classList.contains('active')}));
    children.forEach(el=>{if(el.classList.contains('nav-section')||el.tagName==='A')el.remove();});
    const logout=sourceLinks.find(x=>pathOf(x.href)==='/logout.php');
    const profile=sourceLinks.find(x=>pathOf(x.href)==='/admin/profile.php');
    const nav=sourceLinks.filter(x=>{const p=pathOf(x.href);return p.startsWith('/admin/')&&p!=='/admin/profile.php';}).map(x=>{const p=pathOf(x.href);return {...x,path:p,label:cleanLabel(x.text,p),icon:ICONS[fileOf(p)]||'•',badge:BADGES[p]||''};});
    const current=location.pathname;
    nav.forEach(x=>x.active=x.active||x.path===current);
    let unassigned=nav.slice();
    const grouped=GROUPS.map(g=>{const items=[];g.paths.forEach(p=>{const i=unassigned.findIndex(x=>x.path===p);if(i>=0)items.push(unassigned.splice(i,1)[0]);});return {...g,items};}).filter(g=>g.items.length);
    if(unassigned.length)grouped.push({id:'more',title:'More Platform',icon:'•••',items:unassigned});
    const shell=document.createElement('div');shell.className='spk-side-shell';
    const toolbar=document.createElement('div');toolbar.className='spk-side-toolbar';
    toolbar.innerHTML='<button class="spk-side-tool spk-collapse-btn" type="button" title="Collapse sidebar" aria-label="Collapse sidebar">‹</button><a class="spk-side-tool spk-viewsite-btn" href="/" target="_blank"><span>↗</span><span>View Website</span></a>';
    shell.appendChild(toolbar);
    const tenant=document.createElement('div');tenant.className='spk-tenant-card';
    const city=(brand&&brand.querySelector('small')?brand.querySelector('small').textContent:'City Portal').replace(/\s*Portal Admin\s*/i,'').trim();
    tenant.innerHTML='<span class="spk-tenant-dot"></span><span class="spk-tenant-copy"><b>'+escapeHTML(city||'Current City')+'</b><span>Active tenant context</span></span>';
    shell.appendChild(tenant);
    const sw=document.createElement('div');sw.className='spk-nav-search-wrap';sw.innerHTML='<span class="spk-nav-search-icon">⌕</span><input class="spk-nav-search" type="search" placeholder="Search admin menu…" autocomplete="off" aria-label="Search admin menu"><span class="spk-nav-search-kbd">/</span>';
    shell.appendChild(sw);
    const scroll=document.createElement('div');scroll.className='spk-nav-scroll';shell.appendChild(scroll);
    const footer=document.createElement('div');footer.className='spk-side-footer';shell.appendChild(footer);
    const scrollControls=document.createElement('div');scrollControls.className='spk-scroll-controls';
    scrollControls.innerHTML='<button type="button" data-spk-scroll-up title="Scroll menu up" aria-label="Scroll menu up">▲</button><button type="button" data-spk-scroll-down title="Scroll menu down" aria-label="Scroll menu down">▼</button>';
    shell.appendChild(scrollControls);
    sidebar.appendChild(shell);
    const groupState=safeJSON(STORE.groups,{});const pins=safeJSON(STORE.pins,[]);
    const activeGroup=(grouped.find(g=>g.items.some(i=>i.active))||{}).id;
    const pinnedBlock=document.createElement('div');pinnedBlock.className='spk-pinned-block';scroll.appendChild(pinnedBlock);
    function renderPinned(){
      pinnedBlock.innerHTML='<div class="spk-pinned-title">★ Pinned</div>';
      const items=nav.filter(i=>pins.includes(i.path));
      pinnedBlock.style.display=items.length?'block':'none';
      items.forEach(i=>pinnedBlock.appendChild(makeItem(i,true)));
    }
    function makeItem(item,isPinnedClone){
      const row=document.createElement('div');row.className='spk-nav-item-row';row.dataset.label=(item.label+' '+item.path).toLowerCase();
      const a=document.createElement('a');a.href=item.href;a.className='spk-nav-link'+(item.active?' active':'');a.title=item.label;if(item.target)a.target=item.target;
      a.innerHTML='<span class="spk-nav-item-icon">'+escapeHTML(item.icon)+'</span><span class="spk-nav-label">'+escapeHTML(item.label)+'</span>'+(item.badge?'<span class="spk-nav-badge">'+escapeHTML(item.badge)+'</span>':'');
      row.appendChild(a);
      const pin=document.createElement('button');pin.type='button';pin.className='spk-pin-btn'+(pins.includes(item.path)?' is-pinned':'');pin.title=pins.includes(item.path)?'Unpin menu':'Pin menu';pin.setAttribute('aria-label',pin.title);pin.textContent='★';
      pin.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();const idx=pins.indexOf(item.path);if(idx>=0)pins.splice(idx,1);else pins.push(item.path);saveJSON(STORE.pins,pins);document.querySelectorAll('.spk-pin-btn[data-path="'+cssEscape(item.path)+'"]').forEach(b=>b.classList.toggle('is-pinned',pins.includes(item.path)));renderPinned();applySearch();});
      pin.dataset.path=item.path;row.appendChild(pin);
      return row;
    }
    grouped.forEach(g=>{
      const section=document.createElement('section');section.className='spk-nav-group';section.dataset.group=g.id;
      const open=groupState[g.id]!==undefined?!!groupState[g.id]:(g.id===activeGroup||g.id==='dashboard'||g.id==='city');if(open)section.classList.add('is-open');
      const head=document.createElement('button');head.type='button';head.className='spk-nav-group-head';head.setAttribute('aria-expanded',open?'true':'false');head.title=g.title;
      head.innerHTML='<span class="spk-nav-group-icon">'+escapeHTML(g.icon)+'</span><span class="spk-nav-group-title">'+escapeHTML(g.title)+'</span><span class="spk-nav-count">'+g.items.length+'</span><span class="spk-nav-chevron">›</span>';
      const bodyEl=document.createElement('div');bodyEl.className='spk-nav-group-body';const inner=document.createElement('div');inner.className='spk-nav-group-body-inner';g.items.forEach(i=>inner.appendChild(makeItem(i,false)));bodyEl.appendChild(inner);
      head.addEventListener('click',()=>{const now=section.classList.toggle('is-open');head.setAttribute('aria-expanded',now?'true':'false');groupState[g.id]=now;saveJSON(STORE.groups,groupState)});
      section.append(head,bodyEl);scroll.appendChild(section);
    });
    renderPinned();
    const topUser=document.querySelector('.admin-user-trigger');const topAvatar=topUser?topUser.querySelector('.admin-user-avatar'):null;const topMeta=topUser?topUser.querySelector('.admin-user-meta'):null;
    const profileCard=document.createElement('a');profileCard.className='spk-profile-card';profileCard.href=profile?profile.href:'/admin/profile.php';
    let avatar='<span>A</span>';if(topAvatar){const img=topAvatar.querySelector('img');const txt=topAvatar.textContent.trim();avatar=img?'<img src="'+escapeAttr(img.getAttribute('src')||'')+'" alt="Admin">':'<span>'+escapeHTML(txt||'A')+'</span>'}
    const name=topMeta&&topMeta.querySelector('b')?topMeta.querySelector('b').textContent:'Staff';const role=topMeta&&topMeta.querySelector('small')?topMeta.querySelector('small').textContent:'Administrator';
    profileCard.innerHTML='<span class="spk-profile-avatar">'+avatar+'</span><span class="spk-profile-copy"><b>'+escapeHTML(name)+'</b><span>'+escapeHTML(role)+'</span></span>';
    const fActions=document.createElement('div');fActions.className='spk-footer-actions';fActions.innerHTML='<a href="/admin/profile.php">Profile & Settings</a><a href="'+escapeAttr(logout?logout.href:'/logout.php')+'" title="Logout">↩</a>';footer.append(profileCard,fActions);
    const collapse=toolbar.querySelector('.spk-collapse-btn');const savedCollapsed=localStorage.getItem(STORE.collapsed)==='1';if(savedCollapsed&&innerWidth>1080)body.classList.add('spk-sidebar-collapsed');
    collapse.addEventListener('click',()=>{if(innerWidth<=1080){body.classList.remove('spk-sidebar-mobile-open');return}body.classList.toggle('spk-sidebar-collapsed');try{localStorage.setItem(STORE.collapsed,body.classList.contains('spk-sidebar-collapsed')?'1':'0')}catch(e){}});
    const input=sw.querySelector('.spk-nav-search');
    function applySearch(){const q=(input.value||'').trim().toLowerCase();let shown=0;scroll.querySelectorAll('.spk-nav-group').forEach(group=>{let local=0;group.querySelectorAll('.spk-nav-item-row').forEach(row=>{const ok=!q||row.dataset.label.includes(q);row.style.display=ok?'flex':'none';if(ok)local++});group.style.display=local?'block':'none';if(q&&local)group.classList.add('is-open');});pinnedBlock.style.display=(!q&&pins.length)?'block':'none';let empty=scroll.querySelector('.spk-empty-search');if(q&&shown===0){shown=Array.from(scroll.querySelectorAll('.spk-nav-group')).filter(g=>g.style.display!=='none').length}if(q&&!shown){if(!empty){empty=document.createElement('div');empty.className='spk-empty-search';empty.textContent='No menu item found.';scroll.appendChild(empty)}}else if(empty)empty.remove()}
    input.addEventListener('input',applySearch);
    document.addEventListener('keydown',e=>{if((e.key==='/'&&!/input|textarea|select/i.test((e.target||{}).tagName))||(e.key.toLowerCase()==='k'&&(e.ctrlKey||e.metaKey))){e.preventDefault();body.classList.remove('spk-sidebar-collapsed');input.focus();input.select()}if(e.key==='Escape'&&document.activeElement===input){input.value='';input.blur();applySearch()}});
    const scrollUp=scrollControls.querySelector('[data-spk-scroll-up]'),scrollDown=scrollControls.querySelector('[data-spk-scroll-down]');
    function updateScrollControls(){
      const can=scroll.scrollHeight>scroll.clientHeight+4;
      scrollControls.classList.toggle('is-visible',can);
      if(scrollUp)scrollUp.disabled=scroll.scrollTop<=2;
      if(scrollDown)scrollDown.disabled=scroll.scrollTop+scroll.clientHeight>=scroll.scrollHeight-2;
    }
    if(scrollUp)scrollUp.addEventListener('click',()=>scroll.scrollBy({top:-Math.max(180,scroll.clientHeight*.55),behavior:'smooth'}));
    if(scrollDown)scrollDown.addEventListener('click',()=>scroll.scrollBy({top:Math.max(180,scroll.clientHeight*.55),behavior:'smooth'}));
    scroll.addEventListener('scroll',updateScrollControls,{passive:true});
    scroll.addEventListener('wheel',()=>requestAnimationFrame(updateScrollControls),{passive:true});
    setTimeout(updateScrollControls,0);setTimeout(updateScrollControls,250);
    const topbar=document.querySelector('.admin-topbar');if(topbar){const mobile=document.createElement('button');mobile.type='button';mobile.className='spk-mobile-sidebar-btn';mobile.setAttribute('aria-label','Open admin menu');mobile.textContent='☰';topbar.insertBefore(mobile,topbar.firstChild);mobile.addEventListener('click',()=>body.classList.toggle('spk-sidebar-mobile-open'))}
    const backdrop=document.createElement('div');backdrop.className='spk-sidebar-backdrop';document.body.appendChild(backdrop);backdrop.addEventListener('click',()=>body.classList.remove('spk-sidebar-mobile-open'));
    sidebar.addEventListener('click',e=>{const a=e.target.closest('a.spk-nav-link');if(a&&innerWidth<=1080)body.classList.remove('spk-sidebar-mobile-open')});
    window.addEventListener('resize',()=>{if(innerWidth>1080)body.classList.remove('spk-sidebar-mobile-open');requestAnimationFrame(updateScrollControls)});
  }
  function escapeHTML(s){return String(s).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]))}
  function escapeAttr(s){return escapeHTML(s)}
  function cssEscape(s){return window.CSS&&CSS.escape?CSS.escape(s):s.replace(/(["\\])/g,'\\$1')}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
