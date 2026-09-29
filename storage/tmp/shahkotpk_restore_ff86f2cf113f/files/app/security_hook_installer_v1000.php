<?php
declare(strict_types=1);
require_once __DIR__.'/security_performance_v1000.php';
// Compatibility wrapper kept separate so future v10.x hook updates can replace installer logic without changing the runtime helper.
function sk1000_protection_hook_status(): array {return ['installed'=>sk1000_hook_installed(),'bootstrap'=>sk1000_root().'/app/bootstrap.php','backup'=>sk1000_root().'/app/bootstrap.php.pre-v1000.bak'];}
?>