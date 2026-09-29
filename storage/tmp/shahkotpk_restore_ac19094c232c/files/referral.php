<?php
require __DIR__.'/app/bootstrap.php';$code=trim((string)($_GET['code']??''));if($code)growth_register_referral_click($code);header('Location: /signup.php',true,302);exit;
