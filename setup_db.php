<?php
require_once __DIR__.'/app/migrations.php';

try {
    $pdo = new PDO("mysql:host=localhost", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    function db() { global $pdo; return $pdo; }
    echo "Dropping and recreating DB...\n";
    db()->exec("DROP DATABASE IF EXISTS shahkotp_skt");
    db()->exec("CREATE DATABASE shahkotp_skt");
    db()->exec("USE shahkotp_skt");

    echo "Running schema.sql...\n";
    $schema = file_get_contents(__DIR__.'/database/schema.sql');
    $stmts = split_sql_statements($schema);
    foreach($stmts as $stmt) {
        db()->exec($stmt);
    }
    
    echo "Running packaged migrations...\n";
    $applied = run_packaged_migrations(true);
    echo "Applied: ".implode(', ', $applied)."\n";
    echo "Done.\n";
} catch (Exception $e) {
    echo "Error: ".$e->getMessage()."\n";
}
