<?php
// pages/customer_delete.php
session_start();
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require '../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Customers WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, $_SESSION['user_id'], 'Deleted Customer', "Customer ID: $id");
        header("Location: customers.php?msg=Customer deleted successfully");
        exit();
    } catch(PDOException $e) {
        die("Error deleting customer: " . $e->getMessage());
    }
} else {
    header("Location: customers.php");
    exit();
}
