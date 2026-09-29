<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
require_once __DIR__.'/../app/maps_guard_v1111.php';
$me=current_user();if(!$me||!(has_permission('settings.manage',$me)||has_permission('updates.manage',$me))){http_response_code(403);exit('Forbidden');}
$key=sk1111_maps_browser_key();$source=sk1111_maps_key_source();$origin=sk1111_maps_origin();
page_start('Maps Authentication Diagnostics',true);
?>
<style>.mdg{max-width:1050px;margin:0 auto;padding:22px}.mdg .hero{background:linear-gradient(135deg,#10274d,#1769e0);color:white;padding:24px;border-radius:20px}.mdg .panel{margin-top:16px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px}.mdg code{background:#f3f6fa;padding:4px 7px;border-radius:7px}.mdg .ok{color:#067647}.mdg .bad{color:#b42318}.mdg button{padding:10px 14px;border:0;border-radius:10px;background:#155fd7;color:#fff;font-weight:800}.mdg #mapTest{height:260px;border:1px solid #dbe3ee;border-radius:14px;display:grid;place-items:center;background:#f7f9fc}</style>
<div class="mdg"><div class="hero"><small>GOOGLE MAPS</small><h2>Authentication Diagnostics</h2><p>Checks the Browser API key used by ShahkotPK. This does not change your key or Google Cloud restrictions.</p></div>
<div class="panel"><h3>Current configuration</h3><p>Browser key: <b class="<?=$key!==''?'ok':'bad'?>"><?=htmlspecialchars(sk1111_maps_mask($key))?></b></p><p>Detected setting: <code><?=htmlspecialchars($source?:'none')?></code></p><p>Current origin: <code><?=htmlspecialchars($origin?:'unknown')?></code></p><p>Recommended HTTP referrer:</p><p><code><?=htmlspecialchars(($origin?:'https://your-domain.example').'/*')?></code></p></div>
<div class="panel"><h3>Browser test</h3><div id="mapTest">Press Test Maps API</div><p><button type="button" id="runMapTest" <?=$key===''?'disabled':''?>>Test Maps API</button></p><p id="mapResult"></p></div>
<div class="panel"><h3>Google Cloud checklist</h3><ol><li>Use a valid <b>Browser API key</b>.</li><li>Enable <b>Maps JavaScript API</b> in the same project.</li><li>Attach active billing.</li><li>Set application restriction to <b>Websites (HTTP referrers)</b>.</li><li>Add the current HTTPS domain/referrer.</li><li>API restrictions must allow <b>Maps JavaScript API</b> and any extra APIs Map Control uses.</li></ol></div></div>
<?php if($key!==''):?>
<script>
(()=>{const btn=document.getElementById('runMapTest'),out=document.getElementById('mapResult'),box=document.getElementById('mapTest');btn.addEventListener('click',()=>{btn.disabled=true;out.textContent='Loading Google Maps JavaScript API…';let failed=false;window.gm_authFailure=()=>{failed=true;out.textContent='Authentication failed. Check key, referrer, Maps JavaScript API and billing.';out.className='bad';btn.disabled=false};window.__skMapOk=()=>{if(failed)return;try{new google.maps.Map(box,{center:{lat:31.5709,lng:73.4851},zoom:13});out.textContent='Maps JavaScript API authenticated successfully.';out.className='ok'}catch(e){out.textContent='API loaded but map initialization failed: '+e.message;out.className='bad'}btn.disabled=false};const s=document.createElement('script');s.async=true;s.defer=true;s.src='https://maps.googleapis.com/maps/api/js?key=<?=rawurlencode($key)?>&callback=__skMapOk&v=weekly';s.onerror=()=>{out.textContent='Could not load Google Maps script.';out.className='bad';btn.disabled=false};document.head.appendChild(s)})})();
</script>
<?php endif; require __DIR__.'/../app/end.php';?>
