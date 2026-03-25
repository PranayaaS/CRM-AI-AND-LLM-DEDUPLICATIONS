<?php
// pages/interaction_add.php
require '../config/db.php';
include '../includes/header.php';

$error = '';
$pre_cust_id = $_GET['customer_id'] ?? '';
$customers = $pdo->query("SELECT id, first_name, last_name FROM Customers ORDER BY first_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $interaction_type = $_POST['interaction_type'] ?? 'Call';
    $notes = $_POST['notes'] ?? '';

    if ($customer_id && $interaction_type) {
        try {
            $insert = $pdo->prepare("INSERT INTO Interactions (customer_id, interaction_type, notes) VALUES (?, ?, ?)");
            $insert->execute([$customer_id, $interaction_type, $notes]);
            header("Location: interactions.php?customer_id=".$customer_id."&msg=Interaction logged successfully");
            exit();
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Customer and Interaction Type are required!';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Log Interaction</h2>
    <a href="interactions.php<?= $pre_cust_id ? '?customer_id='.$pre_cust_id : '' ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
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
                <label class="form-label">Interaction Type</label>
                <select name="interaction_type" class="form-select">
                    <option value="Call" <?= (isset($_POST['interaction_type']) && $_POST['interaction_type'] == 'Call') ? 'selected' : '' ?>>Call</option>
                    <option value="Email" <?= (isset($_POST['interaction_type']) && $_POST['interaction_type'] == 'Email') ? 'selected' : '' ?>>Email</option>
                    <option value="Meeting" <?= (isset($_POST['interaction_type']) && $_POST['interaction_type'] == 'Meeting') ? 'selected' : '' ?>>Meeting</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Interaction</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
