<?php
// Standalone boot test. It intentionally catches bootstrap errors and shows a sanitized message.
@ini_set('display_errors','0');
header('Content-Type: text/html; charset=UTF-8');
$root=__DIR__;$result='';$ok=false;
register_shutdown_function(function(){
    $e=error_get_last();
    if(!$e)return;
    $fatal=array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR);
    if(in_array((int)$e['type'],$fatal,true)){
        echo '<div style="font-family:Arial;max-width:950px;margin:30px auto;padding:22px;border:2px solid #c62828;border-radius:16px"><h2>Uncaught PHP fatal</h2><pre style="white-space:pre-wrap">'.htmlspecialchars(($e['message']??'Unknown').' @ '.($e['file']??'unknown').':'.($e['line']??0),ENT_QUOTES,'UTF-8').'</pre></div>';
    }
});
try{
    require $root.'/app/bootstrap.php';
    $ok=true;$result='Bootstrap loaded successfully. Database/settings/core application boot completed.';
}catch(Throwable $e){$result=get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine();}
catch(Exception $e){$result=get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine();}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ShahkotPK Boot Check</title><style>body{font-family:Arial;background:#f6f8f7;color:#14251c}.w{max-width:950px;margin:45px auto}.c{background:#fff;border:1px solid #dce5df;border-radius:18px;padding:26px}.ok{color:#137548}.bad{color:#b42318}pre{white-space:pre-wrap;word-break:break-word;background:#111b16;color:#eafff3;padding:18px;border-radius:12px}</style></head><body><div class="w"><div class="c"><h1>Application Boot Check</h1><h2 class="<?=$ok?'ok':'bad'?>"><?=$ok?'PASS':'FAILED'?></h2><pre><?=htmlspecialchars($result,ENT_QUOTES,'UTF-8')?></pre><p><a href="/server-check.php">Server Check</a> · <a href="/">Home</a></p></div></div></body></html>
