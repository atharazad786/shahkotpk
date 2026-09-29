(()=>{"use strict";
const path=(location.pathname||"/").toLowerCase();if(path!=="/"&&path!=="/index.php")return;
const txt=e=>(e?.textContent||"").replace(/\s+/g," ").trim();
function labelCandidates(){return [...document.querySelectorAll("body *")].filter(el=>el.children.length<=3&&/^city updates?\s*:?$/i.test(txt(el)));}
function fixOne(label){
 const host=label.parentElement;if(!host||host.dataset.sk1261UpdatesFixed)return false;
 const rect=host.getBoundingClientRect();if(rect.width<260||rect.height<20||rect.height>130)return false;
 let track=host.querySelector('[data-ticker-track],[data-marquee-track],[class*="ticker-track"],[class*="marquee-track"],[class*="scroll-track"]');
 if(track&&track!==host&&track!==label){host.classList.add("sk1261-city-update-host");track.classList.add("sk1261-city-update-native");host.dataset.sk1261UpdatesFixed="1";return true;}
 const siblings=[...host.children].filter(el=>el!==label&&txt(el)&&!el.matches("button,nav")&&!/^(live|watch shahkot|city info|alerts?|favorites?|compare)/i.test(txt(el)));
 if(!siblings.length)return false;
 const lane=document.createElement("div");lane.className="sk1261-update-lane";lane.setAttribute("aria-label","City updates");
 const moving=document.createElement("div");moving.className="sk1261-update-moving";
 const snippets=siblings.map(el=>el.innerHTML.trim()).filter(Boolean).slice(0,8);if(!snippets.length)return false;
 for(let repeat=0;repeat<2;repeat++)snippets.forEach((html,i)=>{const item=document.createElement("span");item.className="sk1261-update-item";item.innerHTML=html;moving.appendChild(item);const dot=document.createElement("i");dot.textContent="•";moving.appendChild(dot)});
 siblings.forEach(el=>{el.dataset.sk1261UpdateOriginal="1";el.classList.add("sk1261-update-original-hidden")});lane.appendChild(moving);label.insertAdjacentElement("afterend",lane);host.classList.add("sk1261-city-update-host");host.dataset.sk1261UpdatesFixed="1";return true;
}
function run(){for(const label of labelCandidates())if(fixOne(label))break;}
[0,700,1800,3600,6000].forEach(ms=>setTimeout(run,ms));
})();
