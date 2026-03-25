<?php
// pages/deals.php
require '../config/db.php';
include '../includes/header.php';

$customer_id = $_GET['customer_id'] ?? 0;

// Fetch deals joining with customer names
try {
    if ($customer_id) {
        $stmt = $pdo->prepare("SELECT d.*, c.first_name, c.last_name FROM Deals d JOIN Customers c ON d.customer_id = c.id WHERE d.customer_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$customer_id]);
        $deals = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT d.*, c.first_name, c.last_name FROM Deals d JOIN Customers c ON d.customer_id = c.id ORDER BY d.created_at DESC");
        $deals = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $deals = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Opportunities Pipeline <?= $customer_id ? 'for Account #' . htmlspecialchars($customer_id) : '' ?></h2>
    <div>
        <a href="export.php?type=deals" class="btn btn-outline-success me-2"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</a>
        <?php if($customer_id): ?>
            <a href="customers.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Accounts</a>
            <a href="deal_add.php?customer_id=<?= htmlspecialchars($customer_id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Opportunity</a>
        <?php else: ?>
            <a href="deal_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Opportunity</a>
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
                        <th>Title</th>
                        <th>Customer</th>
                        <th>Value</th>
                        <th>Stage</th>
                        <th>Exp. Close Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($deals): ?>
                        <?php foreach($deals as $d): ?>
                        <tr>
                            <td><?= $d['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($d['title']) ?></td>
                            <td><a href="customers.php"><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></a></td>
                            <td class="text-success">$<?= number_format($d['value'], 2) ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($d['stage'] == 'Prospect') $badge = 'info text-dark';
                                if($d['stage'] == 'Negotiation') $badge = 'warning text-dark';
                                if($d['stage'] == 'Closed Won') $badge = 'success';
                                if($d['stage'] == 'Closed Lost') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $d['stage'] ?></span>
                            </td>
                            <td><?= $d['expected_close_date'] ? date('M d, Y', strtotime($d['expected_close_date'])) : 'Unknown' ?></td>
                            <td>
                                <a href="deal_edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-info text-white" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                                <a href="deal_delete.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this deal?')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No deals found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
