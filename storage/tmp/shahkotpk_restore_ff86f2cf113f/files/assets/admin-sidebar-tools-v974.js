/* ShahkotPK v9.7.4 — Global Admin Tools sidebar injector */
(() => {
  'use strict';
  if (!/^\/admin(?:\/|$)/i.test(location.pathname)) return;
  const MARK = 'sk974-admin-tools';
  const DEST = '/admin/admin-tools.php';
  function norm(s){ return (s || '').replace(/\s+/g,' ').trim().toLowerCase(); }
  function visible(el){
    if(!el) return false;
    const s=getComputedStyle(el), r=el.getBoundingClientRect();
    return s.display!=='none' && s.visibility!=='hidden' && r.width>0 && r.height>0;
  }
  function sidebarOf(el){
    return el && el.closest('.admin-sidebar,.sidebar,#sidebar,aside,[data-admin-sidebar],[data-sidebar],nav');
  }
  function candidates(){
    return [...document.querySelectorAll('a[href]')].filter(a=>visible(a) && sidebarOf(a));
  }
  function reference(){
    const links=candidates();
    for(const label of ['overview','dashboard widgets','advanced analytics']){
      const a=links.find(x=>norm(x.textContent).includes(label));
      if(a) return a;
    }
    return links.find(a=>(a.getAttribute('href')||'').startsWith('/admin/')) || null;
  }
  function menuRow(a){
    if(!a) return null;
    const root=sidebarOf(a); let row=a;
    while(row.parentElement && row.parentElement!==root){
      const p=row.parentElement;
      const direct=[...p.children].filter(c=>c.matches?.('a[href]') || c.querySelector?.(':scope > a[href]'));
      if(direct.length>1) break;
      row=p;
    }
    return row;
  }
  function wipeActive(root){
    [root,...root.querySelectorAll('*')].forEach(el=>{
      ['active','is-active','current','selected','open'].forEach(c=>el.classList?.remove(c));
      el.removeAttribute?.('aria-current');
    });
  }
  function relabel(clone){
    const leaves=[...clone.querySelectorAll('span,b,strong,em,p,div')].filter(el=>el.children.length===0);
    const target=leaves.find(el=>/overview|dashboard widgets|advanced analytics/i.test(el.textContent||''));
    if(target){ target.textContent='Admin Tools'; return; }
    const a=clone.matches('a[href]')?clone:clone.querySelector('a[href]');
    if(!a) return;
    for(const n of [...a.childNodes].reverse()){
      if(n.nodeType===Node.TEXT_NODE && norm(n.textContent)){ n.textContent=' Admin Tools '; return; }
    }
    const s=document.createElement('span'); s.textContent='Admin Tools'; a.appendChild(s);
  }
  function removeCounters(clone){
    clone.querySelectorAll('[class*="badge"],[class*="count"],[data-count]').forEach(el=>el.remove());
  }
  function install(){
    const existing=document.querySelector(`a[href="${DEST}"]`);
    if(existing){
      if(location.pathname===DEST || location.pathname.endsWith('/admin-tools.php')){
        existing.setAttribute('aria-current','page'); existing.classList.add('active');
      }
      return true;
    }
    const ref=reference(); if(!ref) return false;
    const row=menuRow(ref); if(!row || !row.parentElement) return false;
    const clone=row.cloneNode(true); clone.setAttribute(`data-${MARK}`,'1'); wipeActive(clone); removeCounters(clone);
    const a=clone.matches('a[href]')?clone:clone.querySelector('a[href]'); if(!a) return false;
    a.href=DEST; a.setAttribute('title','Admin Tools'); relabel(clone);
    if(location.pathname===DEST || location.pathname.endsWith('/admin-tools.php')){
      clone.classList.add('active'); a.classList.add('active'); a.setAttribute('aria-current','page');
    }
    row.parentElement.insertBefore(clone,row);
    return true;
  }
  function boot(){
    if(install()) return;
    let tries=0;
    const timer=setInterval(()=>{ if(install() || ++tries>=50) clearInterval(timer); },100);
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot,{once:true}); else boot();
})();
