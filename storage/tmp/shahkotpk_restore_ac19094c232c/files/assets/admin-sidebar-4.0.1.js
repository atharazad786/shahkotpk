/* ShahkotPK v4.0.1 — Animated Admin Sidebar Navigation */
(function(){
  'use strict';
  function ready(fn){document.readyState==='loading'?document.addEventListener('DOMContentLoaded',fn):fn();}
  ready(function(){
    var sidebar=document.querySelector('.sidebar');
    if(!sidebar || sidebar.dataset.enhancedNav==='1') return;
    sidebar.dataset.enhancedNav='1';
    sidebar.classList.add('sidebar-nav-enhanced');

    var path=(window.location.pathname||'').replace(/\/+$/,'')||'/';
    var directLinks=Array.prototype.slice.call(sidebar.children).filter(function(el){return el.tagName==='A';});
    var oldSections=Array.prototype.slice.call(sidebar.querySelectorAll(':scope > .nav-section'));

    var groups=[
      {id:'dashboard',label:'Dashboard',icon:'▣',paths:['/admin/index.php','/admin/widgets.php','/admin/themes.php','/admin/homepage.php','/admin/banners.php','/admin/ticker.php']},
      {id:'directory',label:'Directory',icon:'⌖',paths:['/admin/businesses.php','/admin/categories.php','/admin/cities.php','/admin/maps.php']},
      {id:'city-content',label:'City Content',icon:'◆',paths:['/admin/city-guide.php','/admin/deals.php','/admin/events.php','/admin/jobs.php','/admin/property.php']},
      {id:'publishing',label:'Publishing & News',icon:'✎',paths:['/admin/news.php','/admin/blog.php']},
      {id:'live',label:'Live & Engagement',icon:'◉',paths:['/admin/live.php','/admin/engagement.php']},
      {id:'access',label:'Users & Access',icon:'♙',paths:['/admin/users.php','/admin/roles.php']},
      {id:'commerce',label:'Commerce',icon:'▱',paths:['/admin/shop.php']},
      {id:'growth',label:'Growth & Trust',icon:'★',paths:['/admin/reviews.php','/admin/verification.php','/admin/bookings.php','/admin/leads.php','/admin/analytics.php','/admin/moderation.php']},
      {id:'retention',label:'Retention',icon:'∞',paths:['/admin/loyalty.php','/admin/referrals.php','/admin/whatsapp.php','/admin/push.php']},
      {id:'local',label:'Local Services',icon:'✚',paths:['/admin/emergency.php','/admin/restaurants.php','/admin/services.php','/admin/classifieds.php','/admin/seller-staff.php']},
      {id:'commercial',label:'Commercial Platform',icon:'◈',paths:['/admin/entitlements.php','/admin/seo.php','/admin/multi-city.php','/admin/reports.php']},
      {id:'operations',label:'Commercial Operations',icon:'⚙',paths:['/admin/operations.php','/admin/payouts.php','/admin/invoices-commerce.php','/admin/support.php','/admin/disputes.php','/admin/delivery.php','/admin/inventory.php','/admin/purchasing.php','/admin/audit.php','/admin/backups.php','/admin/queues.php','/admin/operations-diagnostic.php']},
      {id:'unified',label:'Unified Platform v4',icon:'◇',paths:['/admin/security.php','/admin/system-health.php','/admin/ai.php','/admin/franchise.php','/admin/mobile-api.php','/admin/pos.php']},
      {id:'revenue',label:'Revenue',icon:'₨',paths:['/admin/plans.php','/admin/ads.php','/admin/payments.php','/admin/accounts.php']},
      {id:'system',label:'System',icon:'⚙',paths:['/admin/updates.php','/admin/settings.php','/admin/profile.php','/logout.php']}
    ];

    function hrefPath(a){
      try{return new URL(a.href,window.location.origin).pathname.replace(/\/+$/,'')||'/';}catch(e){return a.getAttribute('href')||'';}
    }
    directLinks.forEach(function(a){
      var hp=hrefPath(a);
      var active=(hp===path)||(path==='/admin/'&&hp==='/admin/index.php');
      a.classList.toggle('active',active);
      a.dataset.navPath=hp;
    });

    var brand=sidebar.querySelector(':scope > .brand-block');
    var toolbox=document.createElement('div');
    toolbox.className='sidebar-toolbox';
    toolbox.innerHTML='<div class="sidebar-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg><input id="sidebarModuleSearch" type="search" autocomplete="off" placeholder="Search modules..." aria-label="Search admin modules"><button class="sidebar-search-clear" type="button" aria-label="Clear search">×</button></div><div class="sidebar-quick-row"><a href="/admin/index.php">▣ Overview</a><a href="/" target="_blank" rel="noopener">↗ Website</a></div>';
    if(brand && brand.nextSibling) sidebar.insertBefore(toolbox,brand.nextSibling); else sidebar.appendChild(toolbox);

    oldSections.forEach(function(el){el.remove();});
    directLinks.forEach(function(a){a.remove();});

    var assigned={};
    var navHost=document.createElement('div');
    navHost.className='sidebar-accordion-host';
    sidebar.appendChild(navHost);

    groups.forEach(function(g,index){
      var links=directLinks.filter(function(a){return g.paths.indexOf(a.dataset.navPath)>=0;});
      if(!links.length) return;
      links.forEach(function(a){assigned[a.dataset.navPath]=true;});
      var wrap=document.createElement('section');
      wrap.className='nav-accordion';
      wrap.dataset.group=g.id;
      var hasActive=links.some(function(a){return a.classList.contains('active');});
      if(hasActive) wrap.classList.add('has-active');
      var toggle=document.createElement('button');
      toggle.type='button';
      toggle.className='nav-group-toggle';
      toggle.setAttribute('aria-expanded','false');
      toggle.innerHTML='<span class="nav-group-icon">'+g.icon+'</span><span class="nav-group-label">'+g.label+'</span><span class="nav-group-count">'+links.length+'</span><span class="nav-group-chevron">⌄</span>';
      var panel=document.createElement('div');
      panel.className='nav-group-panel';
      var box=document.createElement('div');
      box.className='nav-group-links';
      links.forEach(function(a){box.appendChild(a);});
      panel.appendChild(box);wrap.appendChild(toggle);wrap.appendChild(panel);navHost.appendChild(wrap);

      var saved=null;try{saved=localStorage.getItem('shahkotpk_admin_nav_'+g.id);}catch(e){}
      var shouldOpen=hasActive || saved==='1' || (saved===null && index===0);
      setGroup(wrap,shouldOpen,false);

      toggle.addEventListener('click',function(){
        var open=!wrap.classList.contains('is-open');
        setGroup(wrap,open,true);
      });
    });

    var leftovers=directLinks.filter(function(a){return !assigned[a.dataset.navPath];});
    if(leftovers.length){
      var other=document.createElement('section');other.className='nav-accordion';other.dataset.group='other';
      var tog=document.createElement('button');tog.type='button';tog.className='nav-group-toggle';tog.innerHTML='<span class="nav-group-icon">••</span><span class="nav-group-label">More</span><span class="nav-group-count">'+leftovers.length+'</span><span class="nav-group-chevron">⌄</span>';
      var pnl=document.createElement('div');pnl.className='nav-group-panel';var bx=document.createElement('div');bx.className='nav-group-links';leftovers.forEach(function(a){bx.appendChild(a);});pnl.appendChild(bx);other.appendChild(tog);other.appendChild(pnl);navHost.appendChild(other);setGroup(other,leftovers.some(function(a){return a.classList.contains('active');}),false);tog.addEventListener('click',function(){setGroup(other,!other.classList.contains('is-open'),true);});
    }

    var empty=document.createElement('div');empty.className='sidebar-empty-search';empty.textContent='No admin module found.';navHost.appendChild(empty);

    function setGroup(group,open,persist){
      group.classList.toggle('is-open',!!open);
      var b=group.querySelector('.nav-group-toggle');if(b)b.setAttribute('aria-expanded',open?'true':'false');
      if(persist){try{localStorage.setItem('shahkotpk_admin_nav_'+group.dataset.group,open?'1':'0');}catch(e){}}
    }

    var search=toolbox.querySelector('#sidebarModuleSearch');
    var clear=toolbox.querySelector('.sidebar-search-clear');
    function doSearch(){
      var q=(search.value||'').trim().toLowerCase();
      clear.classList.toggle('visible',!!q);
      var visibleGroups=0;
      Array.prototype.forEach.call(navHost.querySelectorAll('.nav-accordion'),function(group){
        var hits=0;
        Array.prototype.forEach.call(group.querySelectorAll('.nav-group-links>a'),function(a){
          var match=!q || (a.textContent||'').toLowerCase().indexOf(q)>=0;
          a.style.display=match?'':'none';
          a.classList.toggle('search-match',!!q&&match);if(match)hits++;
        });
        group.style.display=hits?'':'none';
        if(hits){visibleGroups++;if(q)setGroup(group,true,false);}
      });
      empty.classList.toggle('visible',!!q && visibleGroups===0);
    }
    search.addEventListener('input',doSearch);
    search.addEventListener('keydown',function(e){if(e.key==='Escape'){search.value='';doSearch();search.blur();}});
    clear.addEventListener('click',function(){search.value='';doSearch();search.focus();});

    /* Mobile drawer */
    var topbar=document.querySelector('.admin-topbar');
    if(topbar){
      var mobileBtn=document.createElement('button');
      mobileBtn.type='button';mobileBtn.className='sidebar-mobile-toggle';mobileBtn.setAttribute('aria-label','Open admin navigation');mobileBtn.setAttribute('aria-expanded','false');mobileBtn.innerHTML='☰';
      var first=topbar.firstElementChild;if(first)topbar.insertBefore(mobileBtn,first);else topbar.appendChild(mobileBtn);
      var overlay=document.createElement('div');overlay.className='sidebar-mobile-overlay';document.body.appendChild(overlay);
      function mobile(open){document.body.classList.toggle('sidebar-mobile-open',open);overlay.classList.toggle('is-open',open);mobileBtn.setAttribute('aria-expanded',open?'true':'false');mobileBtn.innerHTML=open?'×':'☰';}
      mobileBtn.addEventListener('click',function(){mobile(!document.body.classList.contains('sidebar-mobile-open'));});
      overlay.addEventListener('click',function(){mobile(false);});
      document.addEventListener('keydown',function(e){if(e.key==='Escape')mobile(false);});
      Array.prototype.forEach.call(navHost.querySelectorAll('a'),function(a){a.addEventListener('click',function(){if(window.innerWidth<=1100)mobile(false);});});
      window.addEventListener('resize',function(){if(window.innerWidth>1100)mobile(false);});
    }
  });
})();
