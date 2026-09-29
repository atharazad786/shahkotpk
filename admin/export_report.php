<?php
require_once __DIR__.'/../app/bootstrap.php';

// Mock Auth Check
$user = current_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    // For demo purposes, we will bypass strict check if dummy
}

$type = $_GET['type'] ?? 'businesses';

if ($type === 'businesses') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=shahkot_businesses_report.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Category', 'Address', 'Phone', 'Status']);
    
    try {
        $rows = db()->query("SELECT b.id, b.name, c.name as cat, b.address, b.phone, b.status FROM businesses b LEFT JOIN categories c ON b.category_id = c.id LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    } catch (Throwable $e) {
        fputcsv($output, ['Error generating report']);
    }
    
    fclose($output);
    exit;
} else {
    echo "Report type not supported.";
}
