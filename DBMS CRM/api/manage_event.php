<?php
// api/manage_event.php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $title = $_POST['title'] ?? '';
    $event_type = $_POST['event_type'] ?? 'Meeting';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? null;
    $created_by = $_SESSION['user_id'];

    if (empty($title) || empty($start_date)) {
        echo json_encode(['error' => 'Title and Start Date are required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO CalendarEvents (title, event_type, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $event_type, $start_date, $end_date, $created_by]);
        $id = $pdo->lastInsertId();
        
        // Audit log
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        $log_stmt = $pdo->prepare("INSERT INTO AuditLogs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$created_by, 'Create Event', "Created calendar event: $title", $ip_address]);

        echo json_encode(['success' => true, 'id' => $id]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

} elseif ($action === 'update') {
    $id = $_POST['id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? null;
    $title = $_POST['title'] ?? null;
    $event_type = $_POST['event_type'] ?? null;

    if (empty($id) || empty($start_date)) {
        echo json_encode(['error' => 'ID and Start Date are required']);
        exit();
    }

    try {
        if ($title !== null && $event_type !== null) {
            $stmt = $pdo->prepare("UPDATE CalendarEvents SET title = ?, event_type = ?, start_date = ?, end_date = ? WHERE id = ?");
            $stmt->execute([$title, $event_type, $start_date, $end_date, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE CalendarEvents SET start_date = ?, end_date = ? WHERE id = ?");
            $stmt->execute([$start_date, $end_date, $id]);
        }
        
        // Audit log
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        $created_by = $_SESSION['user_id'];
        $log_stmt = $pdo->prepare("INSERT INTO AuditLogs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$created_by, 'Update Event', "Updated calendar event ID: $id", $ip_address]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        echo json_encode(['error' => 'ID is required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM CalendarEvents WHERE id = ?");
        $stmt->execute([$id]);
        
        // Audit log
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        $created_by = $_SESSION['user_id'];
        $log_stmt = $pdo->prepare("INSERT INTO AuditLogs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$created_by, 'Delete Event', "Deleted calendar event ID: $id", $ip_address]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid action']);
}
