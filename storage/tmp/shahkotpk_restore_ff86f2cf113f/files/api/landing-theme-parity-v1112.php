<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/landing_theme_parity_v1110.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store, max-age=0');
$out=['ok'=>true,'version'=>'11.1.2','sections'=>[]];
try{
    $cfg=sk1110_config();
    if(empty($cfg['enabled'])||empty($cfg['inject_missing_sections'])){echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
    $tid=sk1110_tid();
    $sections=!empty($cfg['inherit_builder'])?shp1010_sections($tid):[];
    if(!$sections){foreach(shp1010_defaults() as $i=>$d)$sections[]=['id'=>$i+1,'section_key'=>$d[0],'title'=>$d[1],'subtitle'=>$d[2],'enabled'=>$d[3],'sort_order'=>$d[4],'display_mode'=>$d[5],'record_limit'=>$d[6]];}
    $sections=array_values(array_filter($sections,static fn($x)=>(int)($x['enabled']??0)===1));
    usort($sections,static fn($a,$b)=>(int)($a['sort_order']??0)<=>(int)($b['sort_order']??0));
    $core=['businesses','marketplace','property','health','news','jobs_events','deals'];
    foreach($sections as $s){
        $key=(string)($s['section_key']??''); if($key===''||$key==='hero')continue;
        if(empty($cfg['core_sections'])&&in_array($key,$core,true))continue;
        if(empty($cfg['smart_sections'])&&!in_array($key,$core,true))continue;
        try{$html=sk1110_section_html($s,$tid);}catch(Throwable $e){$html='';try{if(function_exists('runtime_log'))runtime_log('v11.1.2 parity section skipped: '.$key,$e);}catch(Throwable $ignored){}}
        if($html!=='')$out['sections'][]=['key'=>$key,'title'=>(string)($s['title']??''),'order'=>(int)($s['sort_order']??0),'html'=>$html];
    }
}catch(Throwable $e){$out=['ok'=>false,'version'=>'11.1.2','sections'=>[],'message'=>'Theme sections are temporarily unavailable.'];try{if(function_exists('runtime_log'))runtime_log('v11.1.2 parity API recovered',$e);}catch(Throwable $ignored){}}
echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
