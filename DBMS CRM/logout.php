<?php
// logout.php
session_start();
if (isset($_SESSION['user_id'])) {
    require 'config/db.php';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $action = 'User Logout';
    $details = "User successfully logged out.";
    try {
        $log_stmt = $pdo->prepare("INSERT INTO AuditLogs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$_SESSION['user_id'], $action, $details, $ip_address]);
    } catch (PDOException $e) {
        // Silently ignore log failure
    }
}
session_unset();
session_destroy();
header("Location: index.php");
exit();
