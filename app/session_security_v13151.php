<?php
declare(strict_types=1);
/** ShahkotPK v13.0.15.1 — application-level session cookie hardening.
 * Safe with existing sessions: when PHP already started the session, the current
 * cookie is re-issued with secure attributes; authenticated sessions are rotated
 * once per hotfix version. If loaded before session_start(), strict/cookie INI
 * policy is applied pre-start as well.
 */
if (!function_exists('sk13151_apply_session_security')) {
function sk13151_https(): bool {
    $https=strtolower((string)($_SERVER['HTTPS']??''));
    if($https!==''&&$https!=='off'&&$https!=='0') return true;
    $xfp=strtolower(trim(explode(',',(string)($_SERVER['HTTP_X_FORWARDED_PROTO']??''))[0]??''));
    return $xfp==='https'||(int)($_SERVER['SERVER_PORT']??0)===443;
}
function sk13151_authenticated(): bool {
    try { if(function_exists('current_user')) { $u=current_user(); if(is_array($u)&&!empty($u['id'])) return true; if(is_object($u)&&!empty($u->id)) return true; } } catch(Throwable $e) {}
    if(session_status()!==PHP_SESSION_ACTIVE) return false;
    foreach(['user_id','admin_id','auth_user_id','uid'] as $k) if(!empty($_SESSION[$k])) return true;
    return false;
}
function sk13151_cookie_options(): array {
    $p=session_get_cookie_params();
    return [
        'expires'=>0,
        'path'=>(string)($p['path']??'/')!==''?(string)$p['path']:'/',
        'domain'=>(string)($p['domain']??''),
        'secure'=>sk13151_https(),
        'httponly'=>true,
        'samesite'=>'Lax',
    ];
}
function sk13151_reissue_cookie(): bool {
    if(session_status()!==PHP_SESSION_ACTIVE||headers_sent()) return false;
    $name=(string)session_name();$id=(string)session_id();
    if($name===''||$id==='') return false;
    try { return @setcookie($name,$id,sk13151_cookie_options()); } catch(Throwable $e) { return false; }
}
function sk13151_apply_session_security(bool $forceRotate=false): array {
    static $ran=false;
    $r=['pre_start'=>false,'active'=>session_status()===PHP_SESSION_ACTIVE,'cookie_reissued'=>false,'rotated'=>false,'https'=>sk13151_https(),'errors'=>[]];
    if(!$ran || session_status()!==PHP_SESSION_ACTIVE) {
        if(session_status()===PHP_SESSION_NONE) {
            $r['pre_start']=true;
            foreach([
                'session.use_strict_mode'=>'1',
                'session.use_only_cookies'=>'1',
                'session.cookie_httponly'=>'1',
                'session.cookie_samesite'=>'Lax',
            ] as $k=>$v) { try { @ini_set($k,$v); } catch(Throwable $e) {} }
            if($r['https']) { try { @ini_set('session.cookie_secure','1'); } catch(Throwable $e) {} }
            try { @session_set_cookie_params(sk13151_cookie_options()); } catch(Throwable $e) {}
        }
        $ran=true;
    }
    if(session_status()===PHP_SESSION_ACTIVE) {
        $shouldRotate=$forceRotate;
        if(!$shouldRotate && sk13151_authenticated() && empty($_SESSION['_sk13151_rotated'])) $shouldRotate=true;
        if($shouldRotate && !headers_sent()) {
            try { $r['rotated']=(bool)@session_regenerate_id(true); } catch(Throwable $e) { $r['errors'][]=$e->getMessage(); }
            if($r['rotated']) $_SESSION['_sk13151_rotated']='13.0.15.1';
        }
        $r['cookie_reissued']=sk13151_reissue_cookie();
        if($r['cookie_reissued']) $_SESSION['_sk13151_cookie_guard']='13.0.15.1';
    }
    $r['active']=session_status()===PHP_SESSION_ACTIVE;
    return $r;
}
function sk13151_status(): array {
    $https=sk13151_https();$active=session_status()===PHP_SESSION_ACTIVE;
    $guard=$active&&((string)($_SESSION['_sk13151_cookie_guard']??'')==='13.0.15.1');
    $rotated=$active&&((string)($_SESSION['_sk13151_rotated']??'')==='13.0.15.1');
    $same=trim((string)ini_get('session.cookie_samesite'));
    return [
        'version'=>'13.0.15.1','loaded'=>true,'active'=>$active,'https'=>$https,
        'cookie_guard'=>$guard,'rotated'=>$rotated,
        'server_strict'=>(string)ini_get('session.use_strict_mode')==='1',
        'server_httponly'=>(string)ini_get('session.cookie_httponly')==='1',
        'server_secure'=>(string)ini_get('session.cookie_secure')==='1',
        'server_samesite'=>$same,
        'effective_httponly'=>(string)ini_get('session.cookie_httponly')==='1'||$guard,
        'effective_secure'=>!$https||(string)ini_get('session.cookie_secure')==='1'||$guard,
        'effective_samesite'=>$same!==''?$same:($guard?'Lax':''),
        'fixation_mitigated'=>(string)ini_get('session.use_strict_mode')==='1'||$rotated,
    ];
}
}
sk13151_apply_session_security(false);
