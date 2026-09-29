<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/security_performance_v1000.php';
try{$alerts=sk1000_generate_alerts();$cleanup=sk1000_cleanup(0);echo json_encode(['ok'=>true,'alerts'=>$alerts,'cleanup'=>$cleanup,'time'=>date('c')],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit(0);}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}