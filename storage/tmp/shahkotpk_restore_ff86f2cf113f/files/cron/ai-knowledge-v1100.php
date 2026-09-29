<?php
declare(strict_types=1);require __DIR__.'/../app/bootstrap.php';require_once __DIR__.'/../app/ai_copilot_v1100.php';try{$r=sk1100ai_rebuild_knowledge();echo json_encode(['ok'=>true]+$r,JSON_UNESCAPED_SLASHES).PHP_EOL;}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
