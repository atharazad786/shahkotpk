<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
$u=function_exists('current_user')?current_user():null;if(!$u){header('Location: /login.php?return=/dashboard.php');exit;}
$role=strtolower((string)($u['role']??''));if(in_array($role,['admin','administrator','super_admin','superadmin','root'],true)){header('Location: /admin/index.php');exit;}
if(is_file(__DIR__.'/app/services_v1380.php')){require_once __DIR__.'/app/services_v1380.php';try{if(function_exists('sk1380_provider')&&sk1380_provider()){header('Location: /provider/services.php');exit;}}catch(Throwable $e){}}
foreach(['/customer-dashboard.php','/account.php'] as $route){if(is_file(__DIR__.$route)){header('Location: '.$route);exit;}}header('Location: /');exit;
