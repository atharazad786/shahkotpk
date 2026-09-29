<?php
declare(strict_types=1);
/** ShahkotPK v9.5.0 backward-compatible public UI helper. */
require_once __DIR__.'/public_ui_v950.php';
if(!function_exists('shahkotpk_public_ui_v930_assets')){
    function shahkotpk_public_ui_v930_assets(): void { shahkotpk_public_ui_v950_assets(); }
}
