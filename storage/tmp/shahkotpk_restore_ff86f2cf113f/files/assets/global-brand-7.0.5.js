(function(){
  'use strict';
  const transparent=['transparent','rgba(0, 0, 0, 0)','rgba(0,0,0,0)'];
  function rgb(c){const m=String(c||'').match(/rgba?\((\d+)[, ]+(\d+)[, ]+(\d+)/i);return m?[+m[1],+m[2],+m[3]]:null;}
  function darkRgb(v){if(!v)return null;const [r,g,b]=v;const lum=(0.2126*r+0.7152*g+0.0722*b)/255;return lum<0.52;}
  function ancestorDark(node){
    let el=node,steps=0;
    while(el&&steps++<9){
      const st=getComputedStyle(el);const c=st.backgroundColor;
      if(c&&!transparent.includes(c)){const d=darkRgb(rgb(c));if(d!==null)return d;}
      const bi=st.backgroundImage||'';
      if(bi&&bi!=='none'){
        const colors=[...bi.matchAll(/rgba?\([^\)]+\)/g)].map(x=>darkRgb(rgb(x[0]))).filter(x=>x!==null);
        if(colors.length)return colors.filter(Boolean).length>=Math.ceil(colors.length/2);
      }
      el=el.parentElement;
    }
    return null;
  }
  function adminDark(){return document.documentElement.getAttribute('data-admin-theme')==='dark';}
  function preferredDark(){return window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;}
  function pickDarkSurface(pic){
    const ctx=pic.dataset.gbContext||'';
    if(ctx==='admin-sidebar')return true;
    if(ctx.indexOf('admin-')===0)return adminDark();
    const found=ancestorDark(pic.parentElement||pic);if(found!==null)return found;
    return preferredDark();
  }
  function updatePicture(pic){
    const img=pic.querySelector('img');if(!img)return;
    const mobile=window.innerWidth<=680;const auto=pic.dataset.gbAuto!=='0';
    const onDark=auto?pickDarkSurface(pic):false;
    let src='';
    if(mobile)src=onDark?img.dataset.gbMobileLight:img.dataset.gbMobileDark;
    else src=onDark?img.dataset.gbDesktopLight:img.dataset.gbDesktopDark;
    src=src||img.dataset.gbDesktopDark||img.dataset.gbDesktopLight||img.src;
    if(src&&img.dataset.gbCurrent!==src){pic.classList.add('gb705-switching');img.dataset.gbCurrent=src;img.src=src;requestAnimationFrame(()=>pic.classList.remove('gb705-switching'));}
  }
  function updateFavicon(){const link=document.getElementById('gb705Favicon');if(!link)return;const onDark=document.body&&document.body.classList.contains('admin-body')?adminDark():preferredDark();const src=onDark?link.dataset.gbFaviconLight:link.dataset.gbFaviconDark;if(src&&link.href!==src)link.href=src;}
  function updateAll(){document.querySelectorAll('.gb705-adaptive').forEach(updatePicture);updateFavicon();}
  let timer=0;function schedule(){clearTimeout(timer);timer=setTimeout(updateAll,30);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',updateAll,{once:true});else updateAll();
  window.addEventListener('resize',schedule,{passive:true});
  try{const mq=window.matchMedia('(prefers-color-scheme: dark)');mq.addEventListener?mq.addEventListener('change',schedule):mq.addListener&&mq.addListener(schedule);}catch(e){}
  try{new MutationObserver(schedule).observe(document.documentElement,{attributes:true,attributeFilter:['class','data-admin-theme','style']});if(document.body)new MutationObserver(schedule).observe(document.body,{attributes:true,attributeFilter:['class','style']});}catch(e){}
})();
