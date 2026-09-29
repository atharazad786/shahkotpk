(()=>{'use strict';
const SEL='a,button,[role="button"],div,span';
const norm=v=>(v||'').replace(/\s+/g,' ').trim();
const visible=el=>{const r=el.getBoundingClientRect(),cs=getComputedStyle(el);return r.width>28&&r.height>18&&r.height<96&&r.width<420&&cs.display!=='none'&&cs.visibility!=='hidden'&&Number(cs.opacity||1)>0;};
function findLiveChip(){
  const all=[...document.querySelectorAll(SEL)].filter(el=>{
    if(el.closest('[data-sk1241-city-info]'))return false;
    const t=norm(el.textContent).toLowerCase();
    return t.includes('watch shahkot')&&t.includes('live')&&visible(el);
  });
  all.sort((a,b)=>{const ar=a.getBoundingClientRect(),br=b.getBoundingClientRect();return ar.width*ar.height-br.width*br.height;});
  if(!all.length)return null;
  let el=all[0];
  const clickable=el.closest('a,button,[role="button"]');
  if(clickable&&visible(clickable))el=clickable;
  return el;
}
function addCityInfo(){
  if(document.querySelector('[data-sk1241-city-info]'))return true;
  const live=findLiveChip();if(!live)return false;
  const a=document.createElement('a');
  a.href='/city-information.php';
  a.className='sk1241-city-info-chip';
  a.dataset.sk1241CityInfo='1';
  a.setAttribute('aria-label','Open Shahkot City Information');
  const current=location.pathname.replace(/\/+$/,'')==='/city-information.php';
  if(current)a.classList.add('is-active');
  a.innerHTML='<span class="sk1241-city-icon" aria-hidden="true">i</span><span class="sk1241-city-copy"><b>CITY INFO</b><small>Shahkot Guide</small></span>';
  live.insertAdjacentElement('afterend',a);
  const parent=a.parentElement;
  if(parent){
    const r=parent.getBoundingClientRect();
    const kids=[...parent.children].filter(x=>visible(x));
    if(r.height<90&&kids.length<=6)parent.classList.add('sk1241-city-info-host');
  }
  return true;
}
function init(){
  if(addCityInfo())return;
  let tries=0;
  const timer=setInterval(()=>{tries++;if(addCityInfo()||tries>=20)clearInterval(timer);},250);
  const mo=new MutationObserver(()=>{if(addCityInfo())mo.disconnect();});
  mo.observe(document.documentElement,{subtree:true,childList:true});
  setTimeout(()=>mo.disconnect(),5500);
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
