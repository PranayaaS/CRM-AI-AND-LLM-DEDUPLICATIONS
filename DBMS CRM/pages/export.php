<?php
// pages/export.php
require '../config/db.php';

// Force download as CSV
header('Content-Type: text/csv; charset=utf-8');

$type = $_GET['type'] ?? '';

if ($type === 'customers') {
    header('Content-Disposition: attachment; filename=Customers_Export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Address', 'Added On']);
    
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, phone, company, address, created_at FROM Customers ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

if ($type === 'leads') {
    header('Content-Disposition: attachment; filename=Leads_Export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Description', 'Status', 'Created At']);
    
    $stmt = $pdo->query("SELECT id, title, description, status, created_at FROM Leads ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

if ($type === 'deals') {
    header('Content-Disposition: attachment; filename=Opportunities_Export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Customer ID', 'Title', 'Value', 'Stage', 'Expected Close', 'Created At']);
    
    $stmt = $pdo->query("SELECT id, customer_id, title, value, stage, expected_close_date, created_at FROM Deals ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Fallback if no valid type
header('Location: ../dashboard.php');
exit();
