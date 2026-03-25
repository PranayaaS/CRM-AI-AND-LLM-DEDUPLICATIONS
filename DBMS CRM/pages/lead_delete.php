<?php
// pages/lead_delete.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require '../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Leads WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, $_SESSION['user_id'], 'Deleted Lead', "Lead ID: $id");
        header("Location: leads.php?msg=Lead deleted successfully");
        exit();
    } catch(PDOException $e) {
        die("Error deleting lead: " . $e->getMessage());
    }
} else {
    header("Location: leads.php");
    exit();
}
