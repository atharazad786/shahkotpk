<?php
require __DIR__.'/app/bootstrap.php';
$stmt = db()->query("DESCRIBE classified_ads_v1370");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
