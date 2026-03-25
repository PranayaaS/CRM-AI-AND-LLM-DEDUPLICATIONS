<?php
// pages/deal_edit.php
require '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$error = '';

if (!$id) {
    header("Location: deals.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Deals WHERE id = ?");
$stmt->execute([$id]);
$deal = $stmt->fetch();

if (!$deal) {
    echo "<div class='alert alert-danger'>Deal not found!</div>";
    include '../includes/footer.php';
    exit();
}

$customers = $pdo->query("SELECT id, first_name, last_name FROM Customers ORDER BY first_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $value = $_POST['value'] ?? 0;
    $stage = $_POST['stage'] ?? 'Prospect';
    $expected_close_date = $_POST['expected_close_date'] ? $_POST['expected_close_date'] : null;

    if ($customer_id && $title && $value !== '') {
        try {
            $update = $pdo->prepare("UPDATE Deals SET customer_id=?, title=?, value=?, stage=?, expected_close_date=? WHERE id=?");
            $update->execute([$customer_id, $title, $value, $stage, $expected_close_date, $id]);
            header("Location: deals.php?msg=Deal updated successfully");
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
    <h2>Edit Deal</h2>
    <a href="deals.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<?php
$stages = ['Prospect', 'Negotiation', 'Closed Won', 'Closed Lost'];
$current_status_index = array_search($deal['stage'] ?? 'Prospect', $stages);
?>
<div class="sales-path mb-4 shadow-sm">
    <?php foreach($stages as $index => $stage): 
        $class = '';
        if ($deal['stage'] == 'Closed Lost' && $stage == 'Closed Lost') $class = 'lost';
        elseif ($deal['stage'] == 'Closed Won' && $index <= array_search('Closed Won', $stages)) $class = 'completed';
        elseif ($index < $current_status_index && $deal['stage'] != 'Closed Lost') $class = 'completed';
        elseif ($index == $current_status_index) $class = 'active';
    ?>
    <div class="sales-path-step <?= $class ?>"><?= $stage ?></div>
    <?php endforeach; ?>
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
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($deal['customer_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Deal Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? $deal['title']) ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Value ($) *</label>
                    <input type="number" step="0.01" name="value" class="form-control" required value="<?= htmlspecialchars($_POST['value'] ?? $deal['value']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Stage</label>
                    <?php $d_stage = $_POST['stage'] ?? $deal['stage']; ?>
                    <select name="stage" class="form-select">
                        <option value="Prospect" <?= ($d_stage == 'Prospect') ? 'selected' : '' ?>>Prospect</option>
                        <option value="Negotiation" <?= ($d_stage == 'Negotiation') ? 'selected' : '' ?>>Negotiation</option>
                        <option value="Closed Won" <?= ($d_stage == 'Closed Won') ? 'selected' : '' ?>>Closed Won</option>
                        <option value="Closed Lost" <?= ($d_stage == 'Closed Lost') ? 'selected' : '' ?>>Closed Lost</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Expected Close Date</label>
                <input type="date" name="expected_close_date" class="form-control" value="<?= htmlspecialchars($_POST['expected_close_date'] ?? $deal['expected_close_date']) ?>">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Deal</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
