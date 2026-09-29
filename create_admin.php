<?php
require_once __DIR__.'/app/bootstrap.php';
$email = 'admin@shahkotpk.com';
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

$q = db()->prepare("SELECT id FROM users WHERE email=?");
$q->execute([$email]);
if (!$q->fetch()) {
    $q = db()->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES ('Admin', ?, ?, 'admin', 'active')");
    $q->execute([$email, $hash]);
    echo "Admin created: admin@shahkotpk.com / password\n";
} else {
    $q = db()->prepare("UPDATE users SET password_hash=? WHERE email=?");
    $q->execute([$hash, $email]);
    echo "Admin password updated.\n";
}
