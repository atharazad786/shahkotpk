(function(){
  'use strict';
  var modules=[
    ['Overview','/admin/index.php','Dashboard'],['Businesses','/admin/businesses.php','Directory'],['Categories','/admin/categories.php','Directory'],['Cities / Tenants','/admin/cities.php','Directory'],['City Guide','/admin/city-guide.php','Content'],['Map & Location','/admin/location-discovery.php','Location'],['Shop','/admin/shop.php','Marketplace'],['Property','/admin/property.php','Marketplace'],['Deals','/admin/deals.php','Content'],['Events','/admin/events.php','Content'],['Jobs','/admin/jobs.php','Content'],['News Portal','/admin/news.php','Publishing'],['Blog','/admin/blog.php','Publishing'],['Users','/admin/users.php','Access'],['Plans & Subscriptions','/admin/plans.php','Finance'],['Payments','/admin/payments.php','Finance'],['Advertisements','/admin/ads.php','Marketing'],['Theme Studio','/admin/themes.php','Website'],['Homepage Builder 2.0','/admin/homepage.php','Website'],['Banner Manager','/admin/banners.php','Website'],['Dummy Data Manager','/admin/dummy-data.php','Tools'],['Regression Tests','/admin/regression-tests.php','Tools'],['Updates & Recovery','/admin/updates.php','System'],['Settings','/admin/settings.php','System']
  ];
  var input=document.getElementById('v544SearchInput'),results=document.getElementById('v544SearchResults');
  function search(q){
    if(!results)return;q=(q||'').trim().toLowerCase();
    if(!q){results.classList.remove('is-open');results.innerHTML='';return;}
    var found=modules.filter(function(x){return (x[0]+' '+x[2]).toLowerCase().indexOf(q)>-1;}).slice(0,9);
    results.innerHTML=found.length?found.map(function(x){return '<a href="'+x[1]+'"><span>'+x[0]+'</span><small>'+x[2]+' →</small></a>';}).join(''):'<div class="v544-search-empty">No matching admin module found.</div>';
    results.classList.add('is-open');
  }
  if(input){input.addEventListener('input',function(){search(input.value);});input.addEventListener('keydown',function(e){if(e.key==='Enter'){var a=results&&results.querySelector('a');if(a){e.preventDefault();location.href=a.href;}}if(e.key==='Escape'){results.classList.remove('is-open');input.blur();}});}
  document.addEventListener('keydown',function(e){if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();if(input){input.focus();input.select();}}});
  document.addEventListener('click',function(e){if(results&&!e.target.closest('#v544GlobalSearch'))results.classList.remove('is-open');});
  var quickBtn=document.getElementById('v544QuickAdd'),quickMenu=document.getElementById('v544QuickMenu');
  if(quickBtn&&quickMenu){quickBtn.addEventListener('click',function(e){e.stopPropagation();quickMenu.classList.toggle('is-open');});document.addEventListener('click',function(e){if(!e.target.closest('.v544-quick-wrap'))quickMenu.classList.remove('is-open');});}
  var fs=document.getElementById('v544Fullscreen');if(fs)fs.addEventListener('click',function(){if(!document.fullscreenElement){document.documentElement.requestFullscreen&&document.documentElement.requestFullscreen();}else{document.exitFullscreen&&document.exitFullscreen();}});
  document.querySelectorAll('[data-sidebar-toggle]').forEach(function(btn){btn.addEventListener('click',function(){var mob=window.innerWidth<=1080;if(mob)document.body.classList.toggle('spk-sidebar-mobile-open');else document.body.classList.toggle('spk-sidebar-collapsed');});});
  document.querySelectorAll('.v544-line-chart').forEach(function(chart){
    var vals=[];try{vals=JSON.parse(chart.getAttribute('data-values')||'[]');}catch(e){}
    var labels=[];try{labels=JSON.parse(chart.getAttribute('data-labels')||'[]');}catch(e){}
    if(!vals.length)return;var svg=chart.querySelector('svg'),line=chart.querySelector('.main-line'),ghost=chart.querySelector('.area-line'),dots=chart.querySelector('.dots'),lab=chart.querySelector('.v544-chart-labels');
    var w=520,h=190,pad=14,max=Math.max.apply(null,vals.concat([1])),min=Math.min.apply(null,vals.concat([0])),span=Math.max(1,max-min);var pts=[];
    vals.forEach(function(v,i){var x=pad+(w-pad*2)*(vals.length===1?0.5:i/(vals.length-1));var y=h-pad-((v-min)/span)*(h-pad*2-12);pts.push(x.toFixed(1)+','+y.toFixed(1));if(dots){var c=document.createElementNS('http://www.w3.org/2000/svg','circle');c.setAttribute('cx',x);c.setAttribute('cy',y);c.setAttribute('r','3.5');dots.appendChild(c);}});
    if(line)line.setAttribute('points',pts.join(' '));if(ghost){var prev=vals.map(function(v,i){return Math.max(0,v*(0.72+((i%3)*.05)));});var p2=prev.map(function(v,i){var x=pad+(w-pad*2)*(prev.length===1?0.5:i/(prev.length-1));var y=h-pad-((v-min)/span)*(h-pad*2-12);return x.toFixed(1)+','+y.toFixed(1);});ghost.setAttribute('points',p2.join(' '));}
    if(lab)lab.innerHTML=labels.map(function(x){return '<span>'+x+'</span>';}).join('');
  });
})();
