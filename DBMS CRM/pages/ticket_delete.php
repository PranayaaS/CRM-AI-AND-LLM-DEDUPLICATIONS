<?php
// pages/ticket_delete.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM SupportTickets WHERE id = ?");
    $stmt->execute([$id]);
    log_audit($pdo, $_SESSION['user_id'], 'Deleted Case', "Case ID: $id");
    header("Location: tickets.php?msg=Ticket deleted successfully");
    exit();
} else {
    header("Location: tickets.php");
    exit();
}
