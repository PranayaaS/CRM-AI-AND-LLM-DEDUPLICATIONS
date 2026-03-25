<?php
// pages/followup_edit.php
require '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$error = '';

if (!$id) {
    header("Location: followups.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM FollowUps WHERE id = ?");
$stmt->execute([$id]);
$followup = $stmt->fetch();

if (!$followup) {
    echo "<div class='alert alert-danger'>Follow-up not found!</div>";
    include '../includes/footer.php';
    exit();
}

$leads = $pdo->query("SELECT id, title FROM Leads ORDER BY title ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lead_id = $_POST['lead_id'] ?? '';
    $follow_up_date = $_POST['follow_up_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $status = $_POST['status'] ?? 'Pending';

    if ($lead_id && $follow_up_date) {
        try {
            $update = $pdo->prepare("UPDATE FollowUps SET lead_id=?, follow_up_date=?, notes=?, status=? WHERE id=?");
            $update->execute([$lead_id, $follow_up_date, $notes, $status, $id]);
            header("Location: followups.php?msg=Follow-up updated successfully");
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
    <h2>Edit Follow-Up</h2>
    <a href="followups.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
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
                    <?php foreach($leads as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= (($followup['lead_id'] ?? '') == $l['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['title']) ?> (ID: <?= $l['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Follow-Up Date *</label>
                <input type="date" name="follow_up_date" class="form-control" required value="<?= htmlspecialchars($_POST['follow_up_date'] ?? $followup['follow_up_date']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <?php $f_status = $_POST['status'] ?? $followup['status']; ?>
                <select name="status" class="form-select">
                    <option value="Pending" <?= ($f_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Completed" <?= ($f_status == 'Completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= ($f_status == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($_POST['notes'] ?? $followup['notes']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Follow-Up</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
