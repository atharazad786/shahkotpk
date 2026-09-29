<?php
declare(strict_types=1);
/** ShahkotPK v9.5.0 backward-compatible v9.4 public UI helper. */
require_once __DIR__.'/public_ui_v950.php';
if(!function_exists('shahkotpk_public_ui_v940_assets')){
    function shahkotpk_public_ui_v940_assets(): void { shahkotpk_public_ui_v950_assets(); }
}
