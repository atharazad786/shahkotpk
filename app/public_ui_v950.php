<?php
declare(strict_types=1);
/** ShahkotPK v9.5.0 public UI asset helper. */
if(!function_exists('shahkotpk_public_ui_v950_assets')){
    function shahkotpk_public_ui_v950_assets(): void {
        static $done=false; if($done)return; $done=true;
        echo '<link rel="stylesheet" href="/assets/public-ui-v950.css?v=950">'."\n";
        echo '<script defer src="/assets/public-ui-v950.js?v=950"></script>'."\n";
    }
}
