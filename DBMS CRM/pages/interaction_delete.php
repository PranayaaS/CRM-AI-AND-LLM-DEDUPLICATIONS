<?php
// pages/interaction_delete.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';

$id = $_GET['id'] ?? 0;
$c_id = $_GET['customer_id'] ?? '';

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Interactions WHERE id = ?");
    $stmt->execute([$id]);
    $redirect = $c_id ? "interactions.php?customer_id=".$c_id."&msg=Deleted" : "interactions.php?msg=Deleted";
    header("Location: " . $redirect);
    exit();
} else {
    header("Location: interactions.php");
    exit();
}
