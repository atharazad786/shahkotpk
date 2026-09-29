<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
$u=current_user();if(!$u){header('Location: /admin/login.php');exit;}
header('Location: /admin/city-content-studio.php',true,302);exit;
