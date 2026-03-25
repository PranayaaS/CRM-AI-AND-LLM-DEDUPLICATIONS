<?php
// pages/interactions.php
require '../config/db.php';
include '../includes/header.php';

$customer_id = $_GET['customer_id'] ?? 0;

try {
    if ($customer_id) {
        $stmt = $pdo->prepare("SELECT i.*, c.first_name, c.last_name FROM Interactions i JOIN Customers c ON i.customer_id = c.id WHERE i.customer_id = ? ORDER BY i.interaction_date DESC");
        $stmt->execute([$customer_id]);
        $interactions = $stmt->fetchAll();
        
        $cStmt = $pdo->prepare("SELECT first_name, last_name FROM Customers WHERE id = ?");
        $cStmt->execute([$customer_id]);
        $cust = $cStmt->fetch();
        $cust_name = $cust ? $cust['first_name'] . ' ' . $cust['last_name'] : '';
    } else {
        $stmt = $pdo->query("SELECT i.*, c.first_name, c.last_name FROM Interactions i JOIN Customers c ON i.customer_id = c.id ORDER BY i.interaction_date DESC");
        $interactions = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $interactions = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Interaction History <?= $customer_id ? 'for ' . htmlspecialchars($cust_name ?? '') : '' ?></h2>
    <div>
        <?php if($customer_id): ?>
            <a href="customers.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Customers</a>
            <a href="interaction_add.php?customer_id=<?= htmlspecialchars($customer_id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Log Interaction</a>
        <?php else: ?>
            <a href="interaction_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Log Interaction</a>
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
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($interactions): ?>
                        <?php foreach($interactions as $i): ?>
                        <tr>
                            <td class="text-nowrap"><?= date('M d, Y H:i', strtotime($i['interaction_date'])) ?></td>
                            <td><a href="customers.php"><?= htmlspecialchars($i['first_name'] . ' ' . $i['last_name']) ?></a></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($i['interaction_type'] == 'Call') $badge = 'primary';
                                if($i['interaction_type'] == 'Email') $badge = 'info';
                                if($i['interaction_type'] == 'Meeting') $badge = 'success';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $i['interaction_type'] ?></span>
                            </td>
                            <td><?= nl2br(htmlspecialchars($i['notes'] ?? '')) ?></td>
                            <td>
                                <a href="interaction_delete.php?id=<?= $i['id'] ?><?= $customer_id ? '&customer_id='.$customer_id : '' ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this interaction log?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No interactions logged.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
