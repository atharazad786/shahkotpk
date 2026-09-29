<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit("CLI only\n");}
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/backup_restore_v990.php';
try{$r=sk990_run_scheduled();echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit(0);}catch(Throwable $e){fwrite(STDERR,'Backup failed: '.$e->getMessage().PHP_EOL);exit(1);}