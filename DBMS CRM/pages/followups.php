<?php
// pages/followups.php
require '../config/db.php';
include '../includes/header.php';

$lead_id = $_GET['lead_id'] ?? 0;

try {
    if ($lead_id) {
        $stmt = $pdo->prepare("SELECT f.*, l.title as lead_title FROM FollowUps f JOIN Leads l ON f.lead_id = l.id WHERE f.lead_id = ? ORDER BY f.follow_up_date ASC");
        $stmt->execute([$lead_id]);
        $followups = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT f.*, l.title as lead_title FROM FollowUps f JOIN Leads l ON f.lead_id = l.id ORDER BY f.follow_up_date ASC");
        $followups = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $followups = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Follow-Ups <?= $lead_id ? 'for Lead #' . htmlspecialchars($lead_id) : '' ?></h2>
    <div>
        <?php if($lead_id): ?>
            <a href="leads.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Leads</a>
            <a href="followup_add.php?lead_id=<?= htmlspecialchars($lead_id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Follow-Up</a>
        <?php else: ?>
            <a href="followup_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Follow-Up</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lead Title</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($followups): ?>
                        <?php foreach($followups as $f): ?>
                        <tr>
                            <td><?= $f['id'] ?></td>
                            <td><a href="leads.php"><?= htmlspecialchars($f['lead_title']) ?></a></td>
                            <td class="<?= (strtotime($f['follow_up_date']) < time() && $f['status'] == 'Pending') ? 'text-danger fw-bold' : '' ?>">
                                <?= date('M d, Y', strtotime($f['follow_up_date'])) ?>
                            </td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($f['status'] == 'Pending') $badge = 'warning text-dark';
                                if($f['status'] == 'Completed') $badge = 'success';
                                if($f['status'] == 'Cancelled') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $f['status'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($f['notes'] ?? '') ?></td>
                            <td>
                                <a href="followup_edit.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-info text-white" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="followup_delete.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this follow-up?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No follow-ups found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
