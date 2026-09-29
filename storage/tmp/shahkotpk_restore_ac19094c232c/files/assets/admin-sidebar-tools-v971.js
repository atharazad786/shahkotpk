(() => {
  'use strict';
  const ITEMS = [
    {href:'/admin/database-manager.php', icon:'▦', label:'Database & Maintenance', key:'database-manager.php'},
    {href:'/admin/storage-manager.php', icon:'☁', label:'Storage Control Center', key:'storage-manager.php'}
  ];

  function esc(s){ return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function active(item){ return location.pathname.endsWith(item.key); }
  function targetSidebar(){
    const selectors=[
      '.admin-sidebar .sidebar-menu','.admin-sidebar nav','.admin-sidebar',
      '.sidebar .sidebar-menu','.sidebar nav','.sidebar',
      '#sidebar .sidebar-menu','#sidebar nav','#sidebar',
      'aside.admin-nav','aside.sidebar','nav.sidebar','[data-admin-sidebar]','[data-sidebar]'
    ];
    for(const s of selectors){ const el=document.querySelector(s); if(el) return el; }
    return null;
  }
  function already(sidebar){ return sidebar && sidebar.querySelector('[data-sk-admin-tools="971"]'); }
  function build(){
    const wrap=document.createElement('div');
    wrap.className='sk971-sidebar-tools'; wrap.dataset.skAdminTools='971';
    wrap.innerHTML=`<div class="sk971-side-label">ADMIN TOOLS</div>${ITEMS.map(i=>`<a class="sk971-side-link${active(i)?' is-active':''}" href="${esc(i.href)}"><span class="sk971-side-icon">${i.icon}</span><span>${esc(i.label)}</span><b>›</b></a>`).join('')}`;
    return wrap;
  }
  function inject(){
    const sb=targetSidebar();
    if(!sb || already(sb)) return false;
    sb.appendChild(build());
    document.documentElement.classList.add('sk971-sidebar-integrated');
    return true;
  }
  function fallback(){
    if(document.querySelector('[data-sk-admin-tools-fallback="971"]')) return;
    const dock=document.createElement('nav');
    dock.className='sk971-tools-dock'; dock.dataset.skAdminToolsFallback='971';
    dock.setAttribute('aria-label','Admin tools');
    dock.innerHTML=ITEMS.map(i=>`<a class="${active(i)?'is-active':''}" href="${esc(i.href)}" title="${esc(i.label)}"><span>${i.icon}</span><em>${esc(i.label)}</em></a>`).join('');
    document.body.appendChild(dock);
  }
  function start(){
    if(inject()) return;
    let tries=0;
    const timer=setInterval(()=>{ tries++; if(inject() || tries>=20){ clearInterval(timer); if(!document.documentElement.classList.contains('sk971-sidebar-integrated')) fallback(); } },150);
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',start,{once:true}); else start();
})();
