(()=>{
  'use strict';
  const state=window.ShahkotPKMapsGuard||{};
  const mapSelector='[data-google-map], [data-map], .google-map, .map-canvas, .map-container, #map, [id^="map-"]';
  const looksLikeMap=(el)=>{
    if(!(el instanceof HTMLElement)) return false;
    if(el.closest('[data-sk1111-map-fallback]')) return false;
    const id=(el.id||'').toLowerCase(), cls=String(el.className||'').toLowerCase();
    return el.matches?.(mapSelector)||id==='map'||id.startsWith('map-')||cls.includes('google-map')||cls.includes('map-canvas');
  };
  const addressFor=(el)=>{
    const host=el.closest('[data-address],article,.card,.business-card,.listing-card,.property-card,.sk-card')||el.parentElement;
    return (host?.getAttribute('data-address')||host?.querySelector?.('[data-address],.address,.location,.listing-location')?.textContent||'').trim();
  };
  const fallback=(el)=>{
    if(!el||el.dataset.sk1111MapsFallback==='1') return;
    el.dataset.sk1111MapsFallback='1';
    const address=addressFor(el);
    const wrap=document.createElement('div');wrap.className='sk1111-map-fallback';wrap.setAttribute('data-sk1111-map-fallback','1');
    const text=document.createElement('div');text.innerHTML='<b>Map temporarily unavailable</b><small>Google Maps authentication needs attention in Admin → Map Control.</small>';
    wrap.append(text);
    if(address){
      const a=document.createElement('a');a.target='_blank';a.rel='noopener';a.textContent='Open directions ↗';
      a.href='https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(address);wrap.append(a);
    }
    el.replaceChildren(wrap);el.classList.add('sk1111-map-auth-fallback');
  };
  const applyFallbacks=()=>document.querySelectorAll(mapSelector).forEach(el=>{if(looksLikeMap(el))fallback(el)});
  const fail=(reason='auth')=>{
    document.documentElement.classList.add('sk1111-maps-auth-failed');
    state.failed=true;state.reason=reason;applyFallbacks();
    window.dispatchEvent(new CustomEvent('shahkotpk:maps-auth-failure',{detail:{reason}}));
  };
  const prior=window.gm_authFailure;
  window.gm_authFailure=()=>{try{if(typeof prior==='function')prior()}catch(_e){} fail('google_auth_failure')};
  window.ShahkotPKMapsGuard=state;
  window.ShahkotPKMapsGuard.fail=fail;
  if(!state.configured){
    document.addEventListener('DOMContentLoaded',()=>fail('missing_browser_key'),{once:true});
  }
})();
