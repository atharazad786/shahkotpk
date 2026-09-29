(() => {
  'use strict';
  const MARK = 'sk973-admin-tools';
  const DEST = '/admin/admin-tools.php';

  function norm(s){ return (s || '').replace(/\s+/g,' ').trim().toLowerCase(); }
  function isVisible(el){
    if(!el) return false;
    const r=el.getBoundingClientRect();
    const cs=getComputedStyle(el);
    return r.width>0 && r.height>0 && cs.display!=='none' && cs.visibility!=='hidden';
  }
  function sidebarRoot(el){
    return el && el.closest('.admin-sidebar,.sidebar,#sidebar,aside,[data-admin-sidebar],[data-sidebar]');
  }
  function findReference(){
    const anchors=[...document.querySelectorAll('a[href]')].filter(a=>isVisible(a) && sidebarRoot(a));
    const preferred=['overview','dashboard widgets','advanced analytics'];
    for(const wanted of preferred){
      const found=anchors.find(a=>norm(a.textContent).includes(wanted));
      if(found) return found;
    }
    return anchors.find(a=>/^\/admin\//.test(a.getAttribute('href')||'')) || null;
  }
  function topRow(anchor){
    if(!anchor) return null;
    const root=sidebarRoot(anchor);
    let row=anchor;
    while(row.parentElement && row.parentElement!==root){
      const p=row.parentElement;
      // Stop when the parent contains multiple sibling menu entries; row is then one menu row.
      const linked=[...p.children].filter(c=>c.querySelector && c.querySelector('a[href]'));
      if(linked.length>1) break;
      row=p;
    }
    return row;
  }
  function setLabel(clone){
    const candidates=[...clone.querySelectorAll('span,b,strong,em,div,p')]
      .filter(el=>el.children.length===0 && /overview|dashboard widgets|advanced analytics/i.test(el.textContent||''));
    if(candidates[0]) { candidates[0].textContent='Admin Tools'; return; }
    const a=clone.matches('a[href]') ? clone : clone.querySelector('a[href]');
    if(!a) return;
    const textNodes=[...a.childNodes].filter(n=>n.nodeType===Node.TEXT_NODE && norm(n.textContent));
    if(textNodes.length){ textNodes[textNodes.length-1].textContent=' Admin Tools '; return; }
    const span=document.createElement('span'); span.textContent='Admin Tools'; a.appendChild(span);
  }
  function clearActive(clone){
    const activeNames=['active','is-active','current','selected','open'];
    [clone,...clone.querySelectorAll('*')].forEach(el=>{
      activeNames.forEach(c=>el.classList && el.classList.remove(c));
      el.removeAttribute && el.removeAttribute('aria-current');
    });
  }
  function setActive(clone){
    if(!location.pathname.endsWith('/admin/admin-tools.php') && !location.pathname.endsWith('admin-tools.php')) return;
    const a=clone.matches('a[href]') ? clone : clone.querySelector('a[href]');
    if(a){ a.setAttribute('aria-current','page'); a.classList.add('active'); }
    clone.classList.add('active');
  }
  function inject(){
    if(document.querySelector(`[data-${MARK}]`) || document.querySelector(`a[href="${DEST}"]`)) return true;
    const ref=findReference();
    if(!ref) return false;
    const root=sidebarRoot(ref); if(!root) return false;
    const row=topRow(ref); if(!row || !row.parentElement) return false;
    const clone=row.cloneNode(true);
    clone.setAttribute(`data-${MARK}`,'1');
    clearActive(clone);
    const a=clone.matches('a[href]') ? clone : clone.querySelector('a[href]');
    if(!a) return false;
    a.setAttribute('href',DEST);
    a.setAttribute('title','Admin Tools');
    setLabel(clone);
    setActive(clone);
    // Remove counters/badges from the cloned row so it looks like a simple menu item.
    [...clone.querySelectorAll('[class*="badge"],[class*="count"],[data-count]')].forEach(el=>el.remove());
    row.parentElement.insertBefore(clone,row);
    return true;
  }
  function start(){
    if(inject()) return;
    let tries=0;
    const timer=setInterval(()=>{
      tries++;
      if(inject() || tries>=25) clearInterval(timer);
    },120);
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',start,{once:true}); else start();
})();
