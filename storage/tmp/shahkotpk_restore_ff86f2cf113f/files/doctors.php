<?php
declare(strict_types=1);
/** ShahkotPK v13.0.15.1 — safe compatibility route. */
$root=__DIR__;
$candidates=['doctor-online.php','book-doctor.php','services.php','city-guide.php','index.php'];
foreach($candidates as $target){if($target===basename(__FILE__))continue;if(is_file($root.'/'.$target)){header('Location: /'.ltrim($target,'/'),true,302);exit;}}
http_response_code(404);header('Content-Type: text/plain; charset=UTF-8');echo 'This ShahkotPK module is not enabled on this installation.';
