<?php
// pages/customers.php
require '../config/db.php';
include '../includes/header.php';

$search = $_GET['search'] ?? '';

// Fetch customers
try {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM Customers WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $customers = $stmt->fetchAll();
    } else {
        $customers = $pdo->query("SELECT * FROM Customers ORDER BY created_at DESC")->fetchAll();
    }
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $customers = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customers Management</h2>
    <div>
        <a href="export.php?type=customers" class="btn btn-outline-success me-2"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</a>
        <a href="customer_add.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add Customer</a>
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
        <form method="GET" action="customers.php" class="d-flex mb-3">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by name, email, or phone..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
            <?php if($search): ?>
                <a href="customers.php" class="btn btn-outline-secondary ms-2">Clear</a>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers): ?>
                        <?php foreach($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td class="fw-bold"><a href="customer_view.php?id=<?= $c['id'] ?>" class="text-decoration-none text-primary"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></a></td>
                            <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a></td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= htmlspecialchars(substr($c['address'], 0, 30)) ?>...</td>
                            <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="customer_view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary shadow-sm fw-bold"><i class="bi bi-person-badge me-1"></i> View 360-Profile</a>
                                <a href="customer_edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info text-white ms-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                                <a href="customer_delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this customer? All related deals and tickets will also be deleted.')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
