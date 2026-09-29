(()=>{
'use strict';
if(window.__skps1313diag)return; window.__skps1313diag=true;
const q=(s,r=document)=>Array.from(r.querySelectorAll(s));
const clean=s=>String(s??'').replace(/\s+/g,' ').trim();
const text=e=>clean(e?.textContent||'');
const pageOk=()=>/Platform Settings/i.test(text(document.querySelector('h1'))||text(document.body));
const safeAttr=(el,name)=>{try{return clean(el.getAttribute(name)||'').slice(0,500)}catch{return ''}};
const attrs=(el)=>{
  const out={};
  for(const a of Array.from(el.attributes||[])){
    const n=String(a.name||'');
    if(/^(value|data-csrf|data-token|nonce)$/i.test(n) || /secret|password|token|key/i.test(n)) continue;
    let v=clean(a.value||''); if(v.length>500)v=v.slice(0,500)+'…';
    out[n]=v;
  }
  return out;
};
const nodeSummary=(el)=>({
  tag:el.tagName||'', id:el.id||'', className:typeof el.className==='string'?el.className:'',
  text:text(el).slice(0,240), attrs:attrs(el),
  display:getComputedStyle(el).display, visibility:getComputedStyle(el).visibility,
  opacity:getComputedStyle(el).opacity, color:getComputedStyle(el).color,
  backgroundColor:getComputedStyle(el).backgroundColor
});
function ancestors(el,limit=5){const arr=[];let n=el;for(let i=0;n&&i<limit;i++,n=n.parentElement)arr.push(nodeSummary(n));return arr;}
function structuralHtml(el){
  if(!el)return '';
  const clone=el.cloneNode(true);
  q('input,textarea,select,option',clone).forEach(n=>{
    n.removeAttribute('value'); n.removeAttribute('selected');
    if(n.tagName==='TEXTAREA') n.textContent='';
  });
  q('[data-csrf],[data-token],[nonce]',clone).forEach(n=>{n.removeAttribute('data-csrf');n.removeAttribute('data-token');n.removeAttribute('nonce')});
  let s=clone.outerHTML||''; s=s.replace(/(csrf|token|password|secret|api[_-]?key)(\s*=\s*["'])[^"']*/ig,'$1$2[redacted]');
  return s.slice(0,8000);
}
function detectCards(){
  const configs=q('a,button,[role="button"]').filter(e=>/^Configure$/i.test(text(e)) || /configure/i.test(safeAttr(e,'aria-label')));
  return configs.slice(0,80).map((btn,i)=>{
    let card=btn;
    for(let k=0;k<6 && card?.parentElement;k++){
      const p=card.parentElement; const t=text(p);
      if(t.length>0 && t.length<600 && (/(ACTIVE|INACTIVE)/i.test(t)||p.querySelector('form,input,select,textarea'))) {card=p;break;}
      card=p;
    }
    return {index:i,button:nodeSummary(btn),ancestors:ancestors(btn,7),card:nodeSummary(card),cardHtml:structuralHtml(card)};
  });
}
function detectSidebar(){
  const search=q('input').find(i=>/Search settings module/i.test(i.getAttribute('placeholder')||''));
  const root=search?.parentElement?.parentElement || search?.closest('aside,nav') || document.querySelector('aside,nav');
  if(!root)return [];
  return q('a,button,[role="button"],li',root).filter(e=>text(e)||Object.keys(attrs(e)).length).slice(0,120).map(nodeSummary);
}
function detectPanel(){
  const save=q('button,input[type="submit"]').find(e=>/^Save$/i.test(text(e))||/save/i.test(safeAttr(e,'value')));
  const root=save?.closest('form') || save?.parentElement?.parentElement?.parentElement || null;
  return root?{summary:nodeSummary(root),html:structuralHtml(root),fields:q('input,select,textarea',root).slice(0,120).map(e=>({tag:e.tagName,type:safeAttr(e,'type'),name:safeAttr(e,'name'),id:e.id||'',className:typeof e.className==='string'?e.className:'',placeholder:safeAttr(e,'placeholder'),aria:safeAttr(e,'aria-label')}))}:null;
}
function collect(){
  const scripts=q('script[src]').map(s=>safeAttr(s,'src')).filter(s=>/platform|settings|admin|module/i.test(s));
  const styles=q('link[rel="stylesheet"][href]').map(s=>safeAttr(s,'href')).filter(s=>/platform|settings|admin|module/i.test(s));
  const globals=Object.keys(window).filter(k=>/platform|setting|module/i.test(k)).slice(0,150).map(k=>{let typ='unknown';try{typ=typeof window[k]}catch{}return {name:k,type:typ}});
  return {
    diagnostic_version:'13.1.3', generated_at:new Date().toISOString(),
    location:{pathname:location.pathname,search:location.search},
    heading:text(document.querySelector('h1')), bodyClass:document.body.className,
    cards:detectCards(), sidebar:detectSidebar(), detailPanel:detectPanel(),
    relatedScripts:scripts, relatedStyles:styles, relatedGlobals:globals,
    notes:'No input values, passwords, tokens, CSRF values or API keys are intentionally included.'
  };
}
function download(){
  const data=collect();
  const blob=new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
  const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='shahkotpk-platform-settings-debug-v13.1.3.json';
  document.body.appendChild(a); a.click(); setTimeout(()=>{URL.revokeObjectURL(a.href);a.remove()},500);
}
function addButton(){
  if(!pageOk()||document.getElementById('skps1313-export'))return;
  const native=q('button,a').find(e=>/Export Safe Settings JSON/i.test(text(e)));
  const b=document.createElement('button'); b.type='button'; b.id='skps1313-export'; b.textContent='Export Platform Debug JSON';
  b.style.cssText='margin-left:8px;padding:10px 14px;border:1px solid #cfd8e6;border-radius:12px;background:#fff;color:#15243a;font-weight:700;cursor:pointer;';
  b.addEventListener('click',download);
  if(native?.parentElement) native.parentElement.appendChild(b); else (document.querySelector('main')||document.body).prepend(b);
}
function init(){if(!pageOk())return;addButton();const mo=new MutationObserver(()=>addButton());mo.observe(document.body,{childList:true,subtree:true});}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
