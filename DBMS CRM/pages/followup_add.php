<?php
// pages/followup_add.php
require '../config/db.php';
include '../includes/header.php';

$error = '';
$pre_lead_id = $_GET['lead_id'] ?? '';
$leads = $pdo->query("SELECT id, title FROM Leads ORDER BY title ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lead_id = $_POST['lead_id'] ?? '';
    $follow_up_date = $_POST['follow_up_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $status = $_POST['status'] ?? 'Pending';

    if ($lead_id && $follow_up_date) {
        try {
            $insert = $pdo->prepare("INSERT INTO FollowUps (lead_id, follow_up_date, notes, status) VALUES (?, ?, ?, ?)");
            $insert->execute([$lead_id, $follow_up_date, $notes, $status]);
            header("Location: followups.php?lead_id=" . $lead_id . "&msg=Follow-up added successfully");
            exit();
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Lead and Date are required!';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Schedule Follow-Up</h2>
    <a href="followups.php<?= $pre_lead_id ? '?lead_id='.$pre_lead_id : '' ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<div class="card shadow-sm max-w-sm" style="max-width: 600px;">
    <div class="card-body">
        <?php if($error): ?>
            <div class="alert alert-danger p-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Related Lead *</label>
                <select name="lead_id" class="form-select" required>
                    <option value="">-- Select Lead --</option>
                    <?php foreach($leads as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= ($pre_lead_id == $l['id'] || (isset($_POST['lead_id']) && $_POST['lead_id'] == $l['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['title']) ?> (ID: <?= $l['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Follow-Up Date *</label>
                <input type="date" name="follow_up_date" class="form-control" required value="<?= htmlspecialchars($_POST['follow_up_date'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Pending" <?= (isset($_POST['status']) && $_POST['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Completed" <?= (isset($_POST['status']) && $_POST['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= (isset($_POST['status']) && $_POST['status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Follow-Up</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
