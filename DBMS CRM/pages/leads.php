<?php
// pages/leads.php
require '../config/db.php';
include '../includes/header.php';

// Fetch leads with assigned user name
try {
    $stmt = $pdo->query("SELECT l.*, u.name as assigned_to FROM Leads l LEFT JOIN Users u ON l.user_id = u.id ORDER BY l.created_at DESC");
    $leads = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $leads = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Leads Management</h2>
    <div>
        <a href="export.php?type=leads" class="btn btn-outline-success me-2"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</a>
        <a href="lead_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Lead</a>
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
                        <th>Title</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leads): ?>
                        <?php foreach($leads as $l): ?>
                        <tr>
                            <td><?= $l['id'] ?></td>
                            <td><?= htmlspecialchars($l['title']) ?></td>
                            <td><?= htmlspecialchars($l['assigned_to'] ?? 'Unassigned') ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($l['status'] == 'New') $badge = 'primary';
                                if($l['status'] == 'Contacted') $badge = 'info';
                                if($l['status'] == 'Interested') $badge = 'warning text-dark';
                                if($l['status'] == 'Converted') $badge = 'success';
                                if($l['status'] == 'Closed') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $l['status'] ?></span>
                            </td>
                            <td><?= date('M d, Y', strtotime($l['created_at'])) ?></td>
                            <td>
                                <a href="followups.php?lead_id=<?= $l['id'] ?>" class="btn btn-sm btn-secondary" title="Follow Ups"><i class="bi bi-calendar-check"></i></a>
                                <a href="lead_edit.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-info text-white" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                                <a href="lead_delete.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this lead? Follow-ups will be deleted too.')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No leads found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
