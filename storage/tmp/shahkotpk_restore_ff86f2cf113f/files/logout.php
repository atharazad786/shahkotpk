<?php require __DIR__.'/app/bootstrap.php';if(function_exists('activity_log_event'))activity_log_event('logout','auth');$_SESSION=[];session_destroy();header('Location: /');exit;
