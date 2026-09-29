<?php
declare(strict_types=1);
/** ShahkotPK v11.1.2 — Smart Homepage parity layer. Section rendering is API-side; public output-buffer callbacks never nest output buffers. */
require_once __DIR__.'/homepage_front_v1010.php';

function sk1110_tid(): int { return function_exists('shp1010_tenant_id') ? shp1010_tenant_id() : 0; }
function sk1110_table_exists(string $t): bool { return function_exists('shp1010_table_exists') && shp1010_table_exists($t); }
function sk1110_defaults(): array { return [
    'enabled'=>1,'inherit_builder'=>1,'smart_search'=>1,'enhance_native_cards'=>1,'inject_missing_sections'=>1,
    'floating_actions'=>0,'ai_chatbot'=>1,'auto_future_sections'=>1,'core_sections'=>1,'smart_sections'=>1
]; }
function sk1110_ensure(int $tid): void {
    if(!sk1110_table_exists('landing_theme_parity_v1110')) return;
    try{db()->prepare('INSERT IGNORE INTO landing_theme_parity_v1110(tenant_id,enabled,inherit_builder,smart_search,enhance_native_cards,inject_missing_sections,floating_actions,ai_chatbot,auto_future_sections,core_sections,smart_sections) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$tid,1,1,1,1,1,0,1,1,1,1]);}catch(Throwable $e){}
}
function sk1110_config(?int $tid=null): array {
    $tid=$tid??sk1110_tid();$d=sk1110_defaults();sk1110_ensure($tid);
    if(!sk1110_table_exists('landing_theme_parity_v1110')) return $d;
    try{$q=db()->prepare('SELECT * FROM landing_theme_parity_v1110 WHERE tenant_id=? LIMIT 1');$q->execute([$tid]);$r=$q->fetch()?:[];return array_merge($d,$r);}catch(Throwable $e){return $d;}
}
function sk1110_save(int $tid,array $in): void {
    sk1110_ensure($tid);if(!sk1110_table_exists('landing_theme_parity_v1110'))return;
    $keys=['enabled','inherit_builder','smart_search','enhance_native_cards','inject_missing_sections','floating_actions','ai_chatbot','auto_future_sections','core_sections','smart_sections'];
    $vals=[];foreach($keys as $k)$vals[$k]=!empty($in[$k])?1:0;
    $q=db()->prepare('UPDATE landing_theme_parity_v1110 SET enabled=?,inherit_builder=?,smart_search=?,enhance_native_cards=?,inject_missing_sections=?,floating_actions=?,ai_chatbot=?,auto_future_sections=?,core_sections=?,smart_sections=?,updated_at=NOW() WHERE tenant_id=?');
    $q->execute([$vals['enabled'],$vals['inherit_builder'],$vals['smart_search'],$vals['enhance_native_cards'],$vals['inject_missing_sections'],$vals['floating_actions'],$vals['ai_chatbot'],$vals['auto_future_sections'],$vals['core_sections'],$vals['smart_sections'],$tid]);
}
function sk1110_is_smart_home_html(string $html): bool { return stripos($html,'class="shp-body')!==false || stripos($html,"class='shp-body")!==false || stripos($html,'SHAHKOTPK_HOMEPAGE_V1010_ACTIVE')!==false; }
function sk1110_seen_title(string $html,string $title): bool {
    if($title==='')return false;$q=preg_quote(trim($title),'~');
    return (bool)preg_match('~<h[1-6][^>]*>\s*(?:<[^>]+>\s*)*'.$q.'(?:\s*</[^>]+>)*\s*</h[1-6]>~iu',$html);
}
function sk1110_seen_key(string $html,string $key,string $title=''): bool {
    if(stripos($html,'data-sk1110-section="'.$key.'"')!==false||stripos($html,"data-sk1110-section='".$key."'")!==false)return true;
    $markers=[
      'quick_actions'=>['shp-quick-grid'], 'daily_utility'=>['sk1030-home-utility'], 'local_assistant'=>['sk1040-home'],
      'deal_wallet'=>['deal-grid'], 'trending'=>['sk1020-trending-badge'], 'near_you'=>['data-near-grid'],
      'recently_viewed'=>['id="continue-exploring"'], 'local_feed'=>['sk1020-feed'], 'rewards'=>['sk1020-reward'],
      'seo_discovery'=>['sk1020-discover-links']
    ];
    foreach($markers[$key]??[] as $m)if(stripos($html,$m)!==false)return true;
    return sk1110_seen_title($html,$title);
}
function sk1110_search_html(): string {
    $csrf=function_exists('csrf_token')?(string)csrf_token():'';
    $e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
    return '<div class="sk1110-search-wrap" data-sk1110-search><form class="shp-search sk1110-search" action="/search.php" method="get"><div><span>What are you looking for?</span><div class="shp-search-main"><input data-smart-search name="q" autocomplete="off" placeholder="Restaurant, doctor, property, phone…"><button type="button" class="sk1020-voice" data-voice-search hidden title="Voice search">🎙</button></div></div><select name="type"><option value="">Everything</option><option value="business">Businesses</option><option value="product">Products</option><option value="property">Property</option><option value="health">Health</option><option value="job">Jobs</option></select><button>Search</button><button type="button" class="sk1020-save-search" data-save-search title="Save this search for alerts">☆ Save Search</button><div class="sk1020-suggest" data-search-suggestions hidden></div></form><input type="hidden" data-sk1110-csrf value="'.$e($csrf).'"></div>';
}
function sk1110_floating_html(): string { return ''; }
function sk1110_missing_sections_html(string $html): string {
    $cfg=sk1110_config();if(empty($cfg['enabled'])||empty($cfg['inject_missing_sections'])||sk1110_is_smart_home_html($html))return '';
    $tid=sk1110_tid();if(!empty($cfg['inherit_builder'])){$sections=shp1010_sections($tid);}else{$sections=[];foreach(shp1010_defaults() as $i=>$d)$sections[]=['id'=>$i+1,'section_key'=>$d[0],'title'=>$d[1],'subtitle'=>$d[2],'enabled'=>$d[3],'sort_order'=>$d[4],'display_mode'=>$d[5],'record_limit'=>$d[6]];}$enabled=array_values(array_filter($sections,fn($x)=>(int)($x['enabled']??0)===1));usort($enabled,fn($a,$b)=>(int)$a['sort_order']<=>(int)$b['sort_order']);$core=['businesses','marketplace','property','health','news','jobs_events','deals'];$out='';
    foreach($enabled as $s){$key=(string)$s['section_key'];if($key==='hero')continue;if(empty($cfg['core_sections'])&&in_array($key,$core,true))continue;if(empty($cfg['smart_sections'])&&!in_array($key,$core,true))continue;if(sk1110_seen_key($html,$key,(string)($s['title']??'')))continue;$frag=sk1110_section_html($s,$tid);if($frag!=='')$out.=$frag;}
    return $out===''?'':'<div class="sk1110-parity-zone" data-sk1110-parity-zone>'.$out.'</div>';
}
function sk1110_runtime_config(): array {$c=sk1110_config();return ['enabled'=>(bool)$c['enabled'],'enhanceCards'=>(bool)$c['enhance_native_cards'],'smartSearch'=>(bool)$c['smart_search'],'floating'=>(bool)$c['floating_actions'],'ai'=>(bool)$c['ai_chatbot'],'version'=>'11.1.7'];}
