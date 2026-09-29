<?php
$pdo = new PDO("mysql:host=localhost;dbname=shahkotp_skt", "root", "");
$stmt = $pdo->query("SELECT * FROM schema_migrations");
if ($stmt) {
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "No schema_migrations table";
}
