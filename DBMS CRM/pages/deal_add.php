<?php
// pages/deal_add.php
require '../config/db.php';
include '../includes/header.php';

$error = '';
$pre_cust_id = $_GET['customer_id'] ?? '';
$customers = $pdo->query("SELECT id, first_name, last_name FROM Customers ORDER BY first_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $value = $_POST['value'] ?? 0;
    $stage = $_POST['stage'] ?? 'Prospect';
    $expected_close_date = $_POST['expected_close_date'] ? $_POST['expected_close_date'] : null;

    if ($customer_id && $title && $value !== '') {
        try {
            $insert = $pdo->prepare("INSERT INTO Deals (customer_id, title, value, stage, expected_close_date) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$customer_id, $title, $value, $stage, $expected_close_date]);
            header("Location: deals.php?msg=Deal added successfully");
            exit();
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Customer, Title, and Value are required!';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Deal</h2>
    <a href="deals.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<div class="card shadow-sm max-w-sm" style="max-width: 600px;">
    <div class="card-body">
        <?php if($error): ?>
            <div class="alert alert-danger p-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Related Customer *</label>
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
                <label class="form-label">Deal Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g. Enterprise License">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Value ($) *</label>
                    <input type="number" step="0.01" name="value" class="form-control" required value="<?= htmlspecialchars($_POST['value'] ?? '') ?>" placeholder="5000.00">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Stage</label>
                    <select name="stage" class="form-select">
                        <option value="Prospect" <?= (isset($_POST['stage']) && $_POST['stage'] == 'Prospect') ? 'selected' : '' ?>>Prospect</option>
                        <option value="Negotiation" <?= (isset($_POST['stage']) && $_POST['stage'] == 'Negotiation') ? 'selected' : '' ?>>Negotiation</option>
                        <option value="Closed Won" <?= (isset($_POST['stage']) && $_POST['stage'] == 'Closed Won') ? 'selected' : '' ?>>Closed Won</option>
                        <option value="Closed Lost" <?= (isset($_POST['stage']) && $_POST['stage'] == 'Closed Lost') ? 'selected' : '' ?>>Closed Lost</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Expected Close Date</label>
                <input type="date" name="expected_close_date" class="form-control" value="<?= htmlspecialchars($_POST['expected_close_date'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Deal</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
