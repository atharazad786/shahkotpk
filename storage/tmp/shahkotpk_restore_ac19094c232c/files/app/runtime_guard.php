<?php
declare(strict_types=1);

/**
 * ShahkotPK runtime safety helpers.
 * Keeps optional modules from taking the whole public site down and records
 * enough diagnostics for Recovery Center / cPanel troubleshooting.
 */
function runtime_log(string $message, ?Throwable $e=null): void {
    $root=realpath(__DIR__.'/..') ?: dirname(__DIR__);
    $dir=$root.'/storage/logs';
    if(!is_dir($dir)) @mkdir($dir,0755,true);
    $line='['.date('c').'] '.$message;
    if($e){
        $line.=' | '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine();
    }
    @file_put_contents($dir.'/runtime-errors.log',$line.PHP_EOL,FILE_APPEND|LOCK_EX);
}

function runtime_safe_require(string $file,string $module='module'): bool {
    try{
        if(!is_file($file)){
            runtime_log('Missing '.$module.' file: '.$file);
            return false;
        }
        require_once $file;
        return true;
    }catch(Throwable $e){
        runtime_log('Unable to load '.$module,$e);
        return false;
    }
}

function runtime_call(string $function,array $args=[],$fallback=null){
    if(!function_exists($function)) return $fallback;
    try{return $function(...$args);}catch(Throwable $e){runtime_log('Runtime call failed: '.$function,$e);return $fallback;}
}

function runtime_excerpt(string $text,int $width=120,string $trim='…'): string {
    $text=trim(preg_replace('/\s+/u',' ',$text) ?? $text);
    if(function_exists('mb_strimwidth')) return mb_strimwidth($text,0,$width,$trim,'UTF-8');
    if(strlen($text)<=$width) return $text;
    return rtrim(substr($text,0,max(0,$width-strlen($trim)))).$trim;
}

// Some LiteSpeed/cPanel setups can route different requests through an older PHP handler.
// These PHP 8 string helpers are polyfilled so the application remains functional on PHP 7.4.
if(!function_exists('str_contains')){function str_contains(string $haystack,string $needle): bool{return $needle==='' || strpos($haystack,$needle)!==false;}}
if(!function_exists('str_starts_with')){function str_starts_with(string $haystack,string $needle): bool{return $needle==='' || strncmp($haystack,$needle,strlen($needle))===0;}}
if(!function_exists('str_ends_with')){function str_ends_with(string $haystack,string $needle): bool{if($needle==='')return true;$len=strlen($needle);return $len<=strlen($haystack) && substr($haystack,-$len)===$needle;}}

// cPanel installations occasionally have mbstring disabled. The application
// historically called mb_* directly, so safe UTF-8-ish fallbacks avoid HTTP 500.
if(!function_exists('mb_strlen')){function mb_strlen(string $s,?string $enc=null): int{return strlen($s);}}
if(!function_exists('mb_substr')){function mb_substr(string $s,int $start,?int $length=null,?string $enc=null): string{return $length===null?substr($s,$start):substr($s,$start,$length);}}
if(!function_exists('mb_strtolower')){function mb_strtolower(string $s,?string $enc=null): string{return strtolower($s);}}
if(!function_exists('mb_strtoupper')){function mb_strtoupper(string $s,?string $enc=null): string{return strtoupper($s);}}
if(!function_exists('mb_strimwidth')){function mb_strimwidth(string $s,int $start,int $width,string $trim_marker='',?string $enc=null): string{$v=substr($s,$start,$width);if(strlen(substr($s,$start))>$width&&$trim_marker!=='')$v=rtrim(substr($v,0,max(0,$width-strlen($trim_marker)))).$trim_marker;return $v;}}

register_shutdown_function(static function(): void {
    $e=error_get_last();
    if(!$e || !in_array((int)$e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR],true)) return;
    $root=realpath(__DIR__.'/..') ?: dirname(__DIR__);
    $dir=$root.'/storage/logs';
    if(!is_dir($dir)) @mkdir($dir,0755,true);
    @file_put_contents($dir.'/runtime-errors.log','['.date('c').'] FATAL: '.($e['message']??'Unknown').' @ '.($e['file']??'unknown').':'.($e['line']??0).PHP_EOL,FILE_APPEND|LOCK_EX);
});
