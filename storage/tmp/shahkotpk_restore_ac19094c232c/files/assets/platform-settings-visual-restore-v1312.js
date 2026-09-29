(()=>{
'use strict';
if(window.__skps1312)return;window.__skps1312=true;
const txt=n=>String(n?.textContent||'').replace(/\s+/g,' ').trim();
const pageOk=()=>/Platform Settings/i.test(txt(document.querySelector('h1'))||txt(document.body))&&(/System Modules/i.test(txt(document.body))||/Platform Modules/i.test(txt(document.body)));
function rgba(v){const m=String(v||'').match(/rgba?\(([^)]+)\)/i);if(!m)return null;const a=m[1].split(',').map(x=>parseFloat(x.trim()));return {r:a[0]||0,g:a[1]||0,b:a[2]||0,a:Number.isFinite(a[3])?a[3]:1};}
function lum(c){const f=x=>{x/=255;return x<=.03928?x/12.92:Math.pow((x+.055)/1.055,2.4)};return .2126*f(c.r)+.7152*f(c.g)+.0722*f(c.b);}
function contrast(a,b){const x=lum(a),y=lum(b);return (Math.max(x,y)+.05)/(Math.min(x,y)+.05);}
function bgFor(el){let n=el;for(let i=0;n&&i<8;i++,n=n.parentElement){const c=rgba(getComputedStyle(n).backgroundColor);if(c&&c.a>.72)return c;}return {r:255,g:255,b:255,a:1};}
function ownText(el){return [...el.childNodes].filter(n=>n.nodeType===3).map(n=>n.nodeValue||'').join(' ').replace(/\s+/g,' ').trim();}
function shouldSkip(el){if(!el||el.closest('svg,button,input,select,textarea,option'))return true;const t=txt(el);if(!t||t.length>180)return true;if(/^(ACTIVE|INACTIVE|ENABLED|DISABLED|Configure|Save|Reset Module)$/i.test(t))return true;return false;}
function repairText(){
 const root=document.querySelector('main')||document.body;
 root.querySelectorAll('h1,h2,h3,h4,h5,h6,p,span,label,strong,small,a,div').forEach(el=>{
   if(shouldSkip(el))return;
   const direct=ownText(el);if(!direct && !/^H[1-6]$/.test(el.tagName) && !['P','LABEL','SMALL','STRONG','A','SPAN'].includes(el.tagName))return;
   const cs=getComputedStyle(el),rect=el.getBoundingClientRect();if(rect.width<2||rect.height<2)return;
   const fg=rgba(cs.color),bg=bgFor(el);if(!fg)return;
   const badAlpha=fg.a<.35, badContrast=contrast(fg,bg)<2.35, hiddenish=parseFloat(cs.opacity||'1')<.3 || cs.visibility==='hidden';
   if(hiddenish)el.classList.add('skps1312-readable');
   if(badAlpha||badContrast){
      if(lum(bg)>.58){
        if(['P','SMALL'].includes(el.tagName)||/description|subtitle|helper|meta|hint/i.test(String(el.className)))el.classList.add('skps1312-force-muted');
        else el.classList.add('skps1312-force-dark');
      } else el.classList.add('skps1312-force-light');
   }
 });
}
function recoverFromAttrs(){
 const root=document.querySelector('main')||document.body;
 const triggers=[...root.querySelectorAll('a,button,[role="button"],[data-module],[data-key],[data-id]')];
 triggers.forEach(el=>{
   if(el.querySelector('.skps1312-recovered-label'))return;
   const visible=txt(el).replace(/^(Configure|ACTIVE|INACTIVE)$/ig,'').trim();
   if(visible.length>2)return;
   const vals=['data-label','data-name','data-title','aria-label','title','data-module','data-key','data-id'].map(k=>el.getAttribute?.(k)).filter(Boolean);
   let label=vals.find(v=>/[a-z]/i.test(v)&&!/^\d+$/.test(v));if(!label)return;
   label=String(label).replace(/[_-]+/g,' ').replace(/\b\w/g,c=>c.toUpperCase()).trim();
   if(!label||/^Configure$/i.test(label))return;
   const s=document.createElement('span');s.className='skps1312-recovered-label';s.textContent=label;el.appendChild(s);
 });
}
function removeOldMapRecovery(){document.querySelectorAll('#sk1311-map-recovery,[data-sk1311-map-recovery]').forEach(n=>n.remove());}
function apply(){if(!pageOk())return;document.body.classList.add('skps1312-page');removeOldMapRecovery();repairText();recoverFromAttrs();}
let timer=0;const schedule=()=>{clearTimeout(timer);timer=setTimeout(apply,40)};
function init(){if(!pageOk())return;apply();const mo=new MutationObserver(schedule);mo.observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['class','style','aria-selected','aria-current']});document.addEventListener('click',schedule,true);}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
