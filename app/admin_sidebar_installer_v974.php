<?php
declare(strict_types=1);
/** ShahkotPK v10.2.2 — backwards compatible global navigation hook installer. */
if(!function_exists('shahkotpk_install_admin_sidebar_v974')){
function shahkotpk_install_admin_sidebar_v974(): array {
 $layout=__DIR__.'/layout.php';$marker='SHAHKOTPK_ADMIN_SIDEBAR_V1022_BOOTSTRAP';
 if(!is_file($layout))return ['ok'=>false,'status'=>'layout_missing','message'=>'app/layout.php was not found.'];$src=@file_get_contents($layout);if(!is_string($src)||$src==='')return ['ok'=>false,'status'=>'layout_unreadable','message'=>'app/layout.php could not be read.'];
 if(strpos($src,"require_once __DIR__.'/admin_sidebar_global_v974.php'")!==false)return ['ok'=>true,'status'=>'already_installed','message'=>'Navigation Registry Pro global hook is installed.'];if(!is_writable($layout))return ['ok'=>false,'status'=>'layout_not_writable','message'=>'app/layout.php is not writable by PHP.'];
 $line="\n/* {$marker} */\nrequire_once __DIR__.'/admin_sidebar_global_v974.php';\n";$patched='';if(preg_match('/declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;/',$src,$m,PREG_OFFSET_CAPTURE)){$end=$m[0][1]+strlen($m[0][0]);$patched=substr($src,0,$end).$line.substr($src,$end);}else{$pos=strpos($src,'<?php');if($pos===false)return ['ok'=>false,'status'=>'layout_format','message'=>'app/layout.php has an unexpected format.'];$end=$pos+5;$patched=substr($src,0,$end).$line.substr($src,$end);}
 $backup=$layout.'.pre-v1022.bak';if(!is_file($backup)&&@file_put_contents($backup,$src)===false)return ['ok'=>false,'status'=>'backup_failed','message'=>'Could not create layout backup. No changes were made.'];$tmp=$layout.'.v1022.tmp';if(@file_put_contents($tmp,$patched,LOCK_EX)===false){@unlink($tmp);return ['ok'=>false,'status'=>'write_failed','message'=>'Could not write patched layout file.'];}@chmod($tmp,fileperms($layout)&0777);if(!@rename($tmp,$layout)){@unlink($tmp);return ['ok'=>false,'status'=>'rename_failed','message'=>'Could not activate patched layout file.'];}return ['ok'=>true,'status'=>'installed','message'=>'Navigation Registry Pro global hook installed.','backup'=>basename($backup)];
}}
