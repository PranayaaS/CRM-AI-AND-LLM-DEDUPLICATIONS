<?php
// pages/ticket_edit.php
require '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$error = '';

if (!$id) {
    header("Location: tickets.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM SupportTickets WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "<div class='alert alert-danger'>Ticket not found!</div>";
    include '../includes/footer.php';
    exit();
}

$customers = $pdo->query("SELECT id, first_name, last_name FROM Customers ORDER BY first_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'Open';

    if ($customer_id && $subject && $description) {
        try {
            $update = $pdo->prepare("UPDATE SupportTickets SET customer_id=?, subject=?, description=?, status=? WHERE id=?");
            $update->execute([$customer_id, $subject, $description, $status, $id]);
            header("Location: tickets.php?msg=Ticket updated successfully");
            exit();
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Customer, Subject, and Description are required!';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Ticket</h2>
    <a href="tickets.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<div class="card shadow-sm max-w-sm" style="max-width: 600px;">
    <div class="card-body">
        <?php if($error): ?>
            <div class="alert alert-danger p-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Customer *</label>
                <select name="customer_id" class="form-select" required>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($ticket['customer_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control" required value="<?= htmlspecialchars($_POST['subject'] ?? $ticket['subject']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <?php $t_status = $_POST['status'] ?? $ticket['status']; ?>
                <select name="status" class="form-select">
                    <option value="Open" <?= ($t_status == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= ($t_status == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                    <option value="Resolved" <?= ($t_status == 'Resolved') ? 'selected' : '' ?>>Resolved</option>
                    <option value="Closed" <?= ($t_status == 'Closed') ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($_POST['description'] ?? $ticket['description']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Ticket</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
