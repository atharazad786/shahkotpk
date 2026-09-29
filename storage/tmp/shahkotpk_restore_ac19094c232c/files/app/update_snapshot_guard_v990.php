<?php
declare(strict_types=1);
if(!function_exists('sk990_pre_update_guard')){
function sk990_pre_update_guard(): void {
    if(PHP_SAPI==='cli'||($_SERVER['REQUEST_METHOD']??'GET')!=='POST')return;$path=strtolower((string)($_SERVER['SCRIPT_NAME']??$_SERVER['REQUEST_URI']??''));if(!str_contains($path,'/admin/')||!str_contains($path,'updat'))return;
    $root=realpath(__DIR__.'/..')?:dirname(__DIR__);$stamp=$root.'/storage/backups/.v990-preupdate-last';$last=is_file($stamp)?(int)@filemtime($stamp):0;if($last&&time()-$last<900)return;
    try{require_once __DIR__.'/backup_restore_v990.php';if(!is_dir(dirname($stamp)))@mkdir(dirname($stamp),0750,true);$r=sk990_create_snapshot('pre-update',true,true,false,sk990_uid(),false);@touch($stamp);sk990_audit('pre_update_snapshot','Automatic pre-update restore point created',['snapshot_id'=>$r['id']??null],sk990_uid());}
    catch(Throwable $e){try{sk990_audit('pre_update_failed','Automatic pre-update snapshot failed',['error'=>mb_substr($e->getMessage(),0,500)],sk990_uid(),'error');}catch(Throwable $ignored){}}
}}
?>