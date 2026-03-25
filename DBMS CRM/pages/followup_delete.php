<?php
// pages/followup_delete.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM FollowUps WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: followups.php?msg=Follow-up deleted successfully");
    exit();
} else {
    header("Location: followups.php");
    exit();
}
