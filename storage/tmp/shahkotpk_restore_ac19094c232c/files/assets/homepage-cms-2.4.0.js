
(function(){
    const canvas=document.getElementById('cmsCanvas');
    if(!canvas)return;

    const template=document.getElementById('cmsBlockTemplate');
    const dropZone=document.getElementById('cmsDropZone');
    const search=document.getElementById('cmsBlockSearch');
    const preview=document.getElementById('cmsPreviewFrame');
    let dragging=null;
    let libraryType=null;

    const labels={hero:'Hero / Search',content:'Content',cta:'Call to Action',info_cards:'Info Cards',quick_services:'Services',directory:'Business Directory',featured:'Featured Businesses',spotlights:'Spotlights',gallery:'Gallery',links:'Links',testimonials:'Testimonials',faq:'FAQ',stats:'Stats',newsletter:'Newsletter',spacer:'Spacer',custom:'Custom Section',topbar:'Top Bar',header:'Header',slider:'Banner Slider',footer:'Footer'};

    function uid(type){return 'cms_'+type+'_'+Date.now().toString(36)+'_'+Math.random().toString(36).slice(2,7)}
    function blocks(){return [...canvas.querySelectorAll('[data-cms-block]')]}
    function renumber(){blocks().forEach((b,i)=>{const o=b.querySelector('.cms-order');if(o)o.value=(i+1)*10})}

    function bindBlock(block){
        block.addEventListener('dragstart',e=>{
            if(e.target.closest('input,textarea,select,button,details')){e.preventDefault();return}
            dragging=block;libraryType=null;block.classList.add('dragging');e.dataTransfer.effectAllowed='move';
        });
        block.addEventListener('dragend',()=>{block.classList.remove('dragging');dragging=null;renumber();blocks().forEach(x=>x.classList.remove('drag-over'))});
        block.addEventListener('dragover',e=>{
            e.preventDefault();
            if(libraryType){block.classList.add('drag-over');return}
            if(!dragging||dragging===block)return;
            const r=block.getBoundingClientRect(),after=e.clientY>r.top+r.height/2;
            canvas.insertBefore(dragging,after?block.nextSibling:block);block.classList.add('drag-over');
        });
        block.addEventListener('dragleave',()=>block.classList.remove('drag-over'));
        block.addEventListener('drop',e=>{
            e.preventDefault();block.classList.remove('drag-over');
            if(libraryType){
                const newBlock=createBlock(libraryType);
                canvas.insertBefore(newBlock,block);libraryType=null;renumber();
            }
        });

        block.querySelector('[data-delete]')?.addEventListener('click',()=>{if(confirm('Remove this block from the page?')){block.remove();renumber()}});
        block.querySelector('[data-toggle]')?.addEventListener('click',()=>block.classList.toggle('is-collapsed'));
        block.querySelector('[data-duplicate]')?.addEventListener('click',()=>{
            const type=block.dataset.type||'custom',newId=uid(type);
            let html=block.outerHTML;
            const oldId=block.dataset.id;
            html=html.replaceAll(oldId,newId).replace('class="cms-builder-block"','class="cms-builder-block is-new"');
            const holder=document.createElement('div');holder.innerHTML=html;
            const clone=holder.firstElementChild;block.after(clone);bindBlock(clone);renumber();
        });
    }

    function createBlock(type){
        const id=uid(type),label=labels[type]||'Custom Section';
        let html=template.innerHTML.replaceAll('__ID__',id).replaceAll('__TYPE__',type).replaceAll('__TITLE__',label).replaceAll('__ICON__',(type[0]||'C').toUpperCase());
        const holder=document.createElement('div');holder.innerHTML=html;
        const block=holder.firstElementChild;bindBlock(block);return block;
    }

    blocks().forEach(bindBlock);

    document.querySelectorAll('[data-block-type]').forEach(btn=>{
        btn.addEventListener('dragstart',e=>{libraryType=btn.dataset.blockType;e.dataTransfer.effectAllowed='copy';e.dataTransfer.setData('text/plain',libraryType)});
        btn.addEventListener('dragend',()=>{libraryType=null;dropZone?.classList.remove('is-over')});
        btn.addEventListener('click',()=>{const b=createBlock(btn.dataset.blockType);canvas.insertBefore(b,dropZone);renumber();b.scrollIntoView({behavior:'smooth',block:'center'})});
    });

    dropZone?.addEventListener('dragover',e=>{e.preventDefault();dropZone.classList.add('is-over')});
    dropZone?.addEventListener('dragleave',()=>dropZone.classList.remove('is-over'));
    dropZone?.addEventListener('drop',e=>{
        e.preventDefault();dropZone.classList.remove('is-over');
        if(libraryType){canvas.insertBefore(createBlock(libraryType),dropZone);libraryType=null;renumber()}
        else if(dragging){canvas.insertBefore(dragging,dropZone);renumber()}
    });

    search?.addEventListener('input',()=>{
        const q=search.value.trim().toLowerCase();
        document.querySelectorAll('[data-block-type]').forEach(b=>b.style.display=(b.dataset.blockLabel||'').toLowerCase().includes(q)?'flex':'none');
    });

    document.getElementById('cmsCollapseAll')?.addEventListener('click',()=>blocks().forEach(b=>b.classList.add('is-collapsed')));
    document.getElementById('cmsReloadPreview')?.addEventListener('click',()=>{if(preview)preview.src=preview.src.split('&_r=')[0]+'&_r='+Date.now()});
    document.getElementById('cmsRenderMode')?.addEventListener('change',e=>{
        const hint=document.getElementById('cmsModeHint');
        if(hint)hint.textContent=e.target.value==='manual'?'Manual mode: section order controls the live page.':'Theme mode: theme controls primary section arrangement.';
    });

    renumber();
})();
