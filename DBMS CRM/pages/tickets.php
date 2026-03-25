<?php
// pages/tickets.php
require '../config/db.php';
include '../includes/header.php';

$customer_id = $_GET['customer_id'] ?? 0;

try {
    if ($customer_id) {
        // Use the Stored Procedure for specific customer
        $stmt = $pdo->prepare("CALL get_customer_tickets(?)");
        $stmt->execute([$customer_id]);
        $tickets = $stmt->fetchAll();
        $stmt->closeCursor(); // Free the connection for next query
        
        // Fetch customer name for UI
        $cStmt = $pdo->prepare("SELECT first_name, last_name FROM Customers WHERE id = ?");
        $cStmt->execute([$customer_id]);
        $cust = $cStmt->fetch();
        $cust_name = $cust ? $cust['first_name'] . ' ' . $cust['last_name'] : '';
    } else {
        $stmt = $pdo->query("SELECT t.*, c.first_name, c.last_name FROM SupportTickets t JOIN Customers c ON t.customer_id = c.id ORDER BY t.created_at DESC");
        $tickets = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $tickets = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Support Tickets <?= $customer_id ? 'for ' . htmlspecialchars($cust_name ?? '') : '' ?></h2>
    <div>
        <?php if($customer_id): ?>
            <a href="customers.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Customers</a>
            <a href="ticket_add.php?customer_id=<?= htmlspecialchars($customer_id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Ticket</a>
        <?php else: ?>
            <a href="ticket_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Ticket</a>
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
                        <th>Subject</th>
                        <?php if(!$customer_id): ?><th>Customer</th><?php endif; ?>
                        <th>Status</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tickets): ?>
                        <?php foreach($tickets as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($t['subject']) ?></td>
                            <?php if(!$customer_id): ?>
                                <td><a href="customers.php"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></a></td>
                            <?php endif; ?>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($t['status'] == 'Open') $badge = 'danger';
                                if($t['status'] == 'In Progress') $badge = 'warning text-dark';
                                if($t['status'] == 'Resolved') $badge = 'success';
                                if($t['status'] == 'Closed') $badge = 'secondary';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $t['status'] ?></span>
                            </td>
                            <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                            <td>
                                <a href="ticket_edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info text-white" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="ticket_delete.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this ticket?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No tickets found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
