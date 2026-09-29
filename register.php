<?php
declare(strict_types=1);
$target=__DIR__.'/signup.php';if(is_file($target)){require $target;return;}header('Location: /login.php');exit;
