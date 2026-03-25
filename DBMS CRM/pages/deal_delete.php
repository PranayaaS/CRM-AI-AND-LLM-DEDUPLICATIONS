<?php
// pages/deal_delete.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Deals WHERE id = ?");
    $stmt->execute([$id]);
    log_audit($pdo, $_SESSION['user_id'], 'Deleted Opportunity', "Opportunity ID: $id");
    header("Location: deals.php?msg=Deal deleted successfully");
    exit();
} else {
    header("Location: deals.php");
    exit();
}
