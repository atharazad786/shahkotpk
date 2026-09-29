<?php
require_once __DIR__.'/../app/bootstrap.php';

echo "Starting Database Optimization...\n";

try {
    $tablesQuery = db()->query("SHOW TABLES");
    $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "Optimizing table: $table... ";
        db()->query("OPTIMIZE TABLE `$table`");
        echo "Done.\n";
    }

    echo "\nDatabase optimization completed successfully.\n";
} catch (Throwable $e) {
    echo "Error during database optimization: " . $e->getMessage() . "\n";
}
