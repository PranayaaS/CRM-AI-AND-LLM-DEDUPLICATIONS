<?php
// pages/audit_log.php
require '../config/db.php';
include '../includes/header.php';

// Strict Admin-Only Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    die("<div class='alert alert-danger m-4'><h2>Access Denied</h2><p>Only Administrators can view the Security Audit Log.</p><a href='../dashboard.php' class='btn btn-primary'>Go Back</a></div>");
}

try {
    $stmt = $pdo->query("SELECT a.*, u.name as user_name, u.role FROM AuditLogs a JOIN Users u ON a.user_id = u.id ORDER BY a.created_at DESC");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Error loading audit logs: " . $e->getMessage() . "</div>");
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shield-lock-fill text-danger me-2"></i> Security Audit Log</h2>
    <a href="../dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="alert alert-warning shadow-sm border-start border-warning border-4 mb-4">
    <i class="bi bi-info-circle-fill me-2"></i> <strong>Admin Only:</strong> This log securely tracks all constructive and destructive actions taken by users across the system.
</div>

<div class="card shadow-sm border-top border-danger border-4 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase" style="font-size:0.8rem;">
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($logs): ?>
                        <?php foreach($logs as $l): ?>
                        <tr>
                            <td class="text-nowrap"><?= date('M d, Y H:i:s', strtotime($l['created_at'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($l['user_name']) ?></strong> 
                                <span class="badge bg-<?= ($l['role'] == 'Admin') ? 'danger' : 'secondary' ?> ms-1"><?= $l['role'] ?></span>
                            </td>
                            <td>
                                <?php
                                $action_color = 'primary';
                                if (strpos(strtolower($l['action']), 'delete') !== false) $action_color = 'danger';
                                if (strpos(strtolower($l['action']), 'login') !== false) $action_color = 'success';
                                ?>
                                <span class="badge bg-<?= $action_color ?>"><i class="bi bi-activity me-1"></i> <?= htmlspecialchars($l['action']) ?></span>
                            </td>
                            <td><small class="text-muted"><?= htmlspecialchars($l['details']) ?></small></td>
                            <td class="font-monospace text-muted small"><?= htmlspecialchars($l['ip_address']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-5">No security logs recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
