<?php
declare(strict_types=1);
// v12.4.2 canonical route repair: city-guide.php is the one public City Guide URL.
$query=$_GET?('?'.http_build_query($_GET)) : '';
header('X-Robots-Tag: noindex, follow', true);
header('Location: /city-guide.php'.$query, true, 301);
exit;
