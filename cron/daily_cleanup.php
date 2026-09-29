<?php
require_once __DIR__.'/../app/bootstrap.php';

echo "Running daily cleanup cron job...\n";

// 1. Cleanup old search cache files
$tempDir = sys_get_temp_dir();
$files = glob($tempDir . '/ai_search_*.json');
$now = time();
$deleted = 0;
foreach ($files as $file) {
    if (is_file($file) && ($now - filemtime($file)) > 86400) {
        @unlink($file);
        $deleted++;
    }
}
echo "- Deleted $deleted old AI search cache files.\n";

// 2. Clean expired user sessions / OTPs if applicable
try {
    $stmt = db()->prepare("DELETE FROM users WHERE (email_verified=0 OR phone_verified=0) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    echo "- Cleaned up " . $stmt->rowCount() . " unverified legacy users.\n";
} catch (Throwable $e) {
    echo "- DB cleanup skipped.\n";
}

// 3. Delete old log files
$logFiles = glob(__DIR__.'/../storage/logs/*.log');
$logsDeleted = 0;
foreach ($logFiles as $l) {
    if (is_file($l) && ($now - filemtime($l)) > (86400 * 30)) { // 30 days old
        @unlink($l);
        $logsDeleted++;
    }
}
echo "- Deleted $logsDeleted old log files.\n";

echo "Cleanup finished successfully.\n";
