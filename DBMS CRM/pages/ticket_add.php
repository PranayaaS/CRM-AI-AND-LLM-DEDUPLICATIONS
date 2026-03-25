<?php
// pages/ticket_add.php
require '../config/db.php';
include '../includes/header.php';

$error = '';
$pre_cust_id = $_GET['customer_id'] ?? '';
$customers = $pdo->query("SELECT id, first_name, last_name FROM Customers ORDER BY first_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'Open';

    if ($customer_id && $subject && $description) {
        try {
            $insert = $pdo->prepare("INSERT INTO SupportTickets (customer_id, subject, description, status) VALUES (?, ?, ?, ?)");
            $insert->execute([$customer_id, $subject, $description, $status]);
            header("Location: tickets.php?msg=Ticket created successfully");
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
    <h2>Create Ticket</h2>
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
                    <option value="">-- Select Customer --</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($pre_cust_id == $c['id'] || (isset($_POST['customer_id']) && $_POST['customer_id'] == $c['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Open" <?= (isset($_POST['status']) && $_POST['status'] == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= (isset($_POST['status']) && $_POST['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                    <option value="Resolved" <?= (isset($_POST['status']) && $_POST['status'] == 'Resolved') ? 'selected' : '' ?>>Resolved</option>
                    <option value="Closed" <?= (isset($_POST['status']) && $_POST['status'] == 'Closed') ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Ticket</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
